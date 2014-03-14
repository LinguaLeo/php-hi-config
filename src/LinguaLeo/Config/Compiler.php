<?php
namespace LinguaLeo\Config;

class Compiler
{
    protected $schema;
    protected $pathMap = [];
    protected $defaultOptions = ['compiledDataClass' => '\LinguaLeo\Config\CompiledData'];
    protected $options = [];

    public function __construct($options = [])
    {
        $this->options = $options + $this->defaultOptions;
    }

    /**
     * @param $schema
     * @param $rawData
     * @return CompiledData
     */
    public function compile($schema, $rawData)
    {
        $this->pathMap = [];
        $this->schema = $schema;
        $schemaKeysValues = $this->getSchemaKeysValues($rawData);
        $mergeTree = $this->getMergeTree($rawData, $schemaKeysValues);

        return call_user_func(
            [$this->options['compiledDataClass'], 'fromArray'],
            [
                'schema' => $this->schema,
                'mergeTree' => $mergeTree,
                'pathMap' => $this->pathMap
            ]
        );
    }

    protected function getSchemaKeysValues($rawData)
    {
        $result = [];
        foreach ($this->schema as $schemaKey) {
            $result[$schemaKey] = [];
            foreach ($rawData as $levels) {
                foreach ($levels as $levelData) {
                    list($levelValues) = $levelData;

                    if (isset($levelValues[$schemaKey])) {
                        $result[$schemaKey][$levelValues[$schemaKey]] = 1;
                    }
                }
            }
        }

        return $result;
    }

    protected function getMergeTree($rawData, $schemaKeysValues)
    {
        $tree = [];
        $pointers = [];
        foreach ($this->schema as $key) {
            $pointers[$key] = 0;
        }

        $lastIndex = count($this->schema) - 1;
        $lastKey = $this->schema[$lastIndex];
        while ($pointers[$lastKey] <= count($schemaKeysValues[$lastKey])) {
            $this->buildNode($tree, $pointers, $schemaKeysValues, $rawData);

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

        return $tree;
    }

    protected function buildNode(&$tree, $path, $schemaKeysValues, $rawData)
    {
        $translatedPath = [];

        foreach ($path as $key => $step) {
            $step--;
            if ($step >= 0) {
                $keys = array_keys($schemaKeysValues[$key]);
                $translatedPath[$key] = $keys[$step];
            } else {
                $translatedPath[$key] = "*";
            }
        }

        $values = $this->getConfigValues($translatedPath, $rawData);

        if (count($values) > 0) {
            $tree[implode(".", $translatedPath)] = $values;
            $this->addPathToMap($translatedPath);
        }
    }

    protected function getConfigValues($translatedPath, $rawData)
    {
        $result = [];
        foreach ($rawData as $valueName => $levels) {
            foreach ($levels as $level) {
                list($levelData, $configValue) = $level;

                $testPattern = $translatedPath;
                foreach ($levelData as $key => $value) {
                    if (isset($translatedPath[$key]) && $translatedPath[$key] == $value) {
                        $testPattern[$key] = "*";
                    } else {
                        $testPattern[$key] = null;
                    }
                }

                $shouldAdd = true;
                foreach ($testPattern as $value) {
                    if ($value != "*") {
                        $shouldAdd = false;
                        break;
                    }
                }

                if ($shouldAdd) {
                    $result[$valueName] = $configValue;
                }
            }
        }

        return $result;
    }

    protected function addPathToMap($translatedPath)
    {
        $pathNode = & $this->pathMap;
        foreach ($translatedPath as $pathKey => $value) {
            $pathKey = $pathKey . "=" . $value;
            if ($value != "*") {
                if (!isset($pathNode[$pathKey])) {
                    $pathNode[$pathKey] = [];
                }

                $pathNode = & $pathNode[$pathKey];
            }
        }
    }
}