<?php
namespace LinguaLeo\Config;

class Compiler
{
    protected $schema;

    protected $options = [];
    protected $defaultOptions = [
        Enum::OPTION_COMPILED_DATA_CLASS => '\LinguaLeo\Config\Data'
    ];

    public function __construct($options = [])
    {
        $this->options = $options + $this->defaultOptions;
    }

    /**
     * @param $schema
     * @param $rawData
     * @param $prefix
     * @return Data
     */
    public function compile($schema, $rawData, $prefix = '')
    {
        $this->setSchema($schema);
        $schemaKeysValues = $this->getSchemaKeysValues($rawData);
        $mergeData = $this->getMergeData($rawData, $schemaKeysValues, $prefix);

        return call_user_func(
            [$this->options[Enum::OPTION_COMPILED_DATA_CLASS], 'fromArray'],
            [
                Enum::KEY_SCHEMA => $this->schema,
                Enum::KEY_MERGE_TREE => $mergeData[Enum::KEY_MERGE_TREE],
                Enum::KEY_PATH_MAP => $mergeData[Enum::KEY_PATH_MAP]
            ]
        );
    }

    protected function setSchema($schema)
    {
        if (!is_array($schema)) {
            throw new Exception("Schema must be an array");
        }

        $this->schema = $schema;
    }

    protected function getSchemaKeysValues($rawData)
    {
        $result = [];
        foreach ($this->schema as $schemaKey) {
            $result[$schemaKey] = [];
            foreach ($rawData as $levels) {
                foreach ($levels as $levelData) {
                    if (count($levelData) != 2) {
                        throw new Exception("Wrong config data format");
                    }

                    list($levelValues) = $levelData;
                    if (!is_array($levelValues)) {
                        throw new Exception("Wrong config data format");
                    }

                    if (isset($levelValues[$schemaKey])) {
                        $result[$schemaKey][$levelValues[$schemaKey]] = 1;
                    }
                }
            }
        }

        return $result;
    }

    protected function getMergeData($rawData, $schemaKeysValues, $prefix)
    {
        $mergeTree = [];
        $pathMap = [];
        $pointers = [];
        foreach ($this->schema as $key) {
            $pointers[$key] = 0;
        }

        $lastIndex = count($this->schema) - 1;
        $lastKey = $this->schema[$lastIndex];
        while ($pointers[$lastKey] <= count($schemaKeysValues[$lastKey])) {
            $this->buildNode($mergeTree, $pathMap, $pointers, $schemaKeysValues, $rawData, $prefix);

            $pointers[$this->schema[0]]++;
            $currentPointer = 0;
            $pointerKey = $this->schema[$currentPointer];
            while ($pointers[$pointerKey] > count($schemaKeysValues[$pointerKey])) {
                if ($currentPointer + 1 > count($this->schema) - 1) {
                    break;
                }

                $pointers[$pointerKey] = 0;
                $currentPointer++;
                $pointerKey = $this->schema[$currentPointer];
                $pointers[$pointerKey]++;
            }
        }

        return [Enum::KEY_MERGE_TREE => $mergeTree, Enum::KEY_PATH_MAP => $pathMap];
    }

    protected function buildNode(&$tree, &$pathMap, $path, $schemaKeysValues, $rawData, $prefix)
    {
        $translatedPath = [];

        foreach ($path as $key => $step) {
            $step--;
            if ($step >= 0) {
                $keys = array_keys($schemaKeysValues[$key]);
                $translatedPath[$key] = $keys[$step];
            } else {
                $translatedPath[$key] = Enum::BLANK;
            }
        }

        $values = $this->getConfigValues($translatedPath, $rawData, $prefix);

        if (count($values) > 0) {
            $tree[implode(".", $translatedPath)] = $values;
            $this->addPathToMap($pathMap, $translatedPath);
        }
    }

    protected function getConfigValues($translatedPath, $rawData, $prefix)
    {
        $result = [];
        foreach ($rawData as $valueName => $levels) {
            foreach ($levels as $level) {
                list($levelData, $configValue) = $level;

                $testPattern = $translatedPath;
                foreach ($levelData as $key => $value) {
                    if (isset($translatedPath[$key]) && $translatedPath[$key] == $value) {
                        $testPattern[$key] = Enum::BLANK;
                    } else {
                        $testPattern[$key] = null;
                    }
                }

                $shouldAdd = true;
                foreach ($testPattern as $value) {
                    if ($value != Enum::BLANK) {
                        $shouldAdd = false;
                        break;
                    }
                }

                if ($shouldAdd) {
                    $result[$prefix . $valueName] = $configValue;
                }
            }
        }

        return $result;
    }

    protected function addPathToMap(&$pathMap, $translatedPath)
    {
        $pathNode = & $pathMap;
        foreach ($translatedPath as $pathKey => $value) {
            $pathKey = $pathKey . "=" . $value;
            if ($value != Enum::BLANK) {
                if (!isset($pathNode[$pathKey])) {
                    $pathNode[$pathKey] = [];
                }

                $pathNode = & $pathNode[$pathKey];
            }
        }
    }
}