<?php
namespace LinguaLeo\Config;

class Compiler
{
    protected $schema;
    protected $schemaPriorities;
    protected $defaultOptions = ['treeClass' => '\LinguaLeo\Config\CompiledData'];
    protected $options = [];
    protected $pathMap = [];

    function __construct($options = [])
    {
        $this->options = $options + $this->defaultOptions;
    }

    protected function getNodeValues($raw)
    {
        $result = [];

        foreach ($this->schema as $key) {
            $result[$key] = [];
            foreach ($raw as $levels) {
                foreach ($levels as $data) {
                    list($levelValues) = $data;

                    if (isset($levelValues[$key])) {
                        $result[$key][$levelValues[$key]] = 1;
                    }
                }
            }
        }

        return $result;
    }

    protected function addPathToMap($path)
    {
        $node = & $this->pathMap;
        foreach ($path as $key => $value) {
            $key = $key . "=" . $value;
            if ($value != "*") {
                if (!isset($node[$key])) {
                    $node[$key] = [];
                }

                $node = & $node[$key];
            }
        }
    }

    protected function buildNode(&$tree, $path, $nodeValues, $raw)
    {
        $translatedPath = [];

        foreach ($path as $key => $step) {
            $step--;
            if ($step >= 0) {
                $keys = array_keys($nodeValues[$key]);
                $translatedPath[$key] = $keys[$step];
            } else {
                $translatedPath[$key] = "*";
            }
        }

        $values = $this->getConfigValues($translatedPath, $raw);

        if (count($values) > 0) {
            $tree[implode(".", $translatedPath)] = $values;
            $this->addPathToMap($translatedPath);
        }
    }

    protected function getConfigValues($path, $raw)
    {
        $config = [];
        foreach ($raw as $valueName => $levels) {
            foreach ($levels as $level) {
                list($levelData, $configValue) = $level;

                $test = $path;
                foreach ($levelData as $key => $value) {
                    if (isset($path[$key]) && $path[$key] == $value) {
                        $test[$key] = "*";
                    } else {
                        $test[$key] = null;
                    }
                }

                $shouldAdd = true;
                foreach ($test as $value) {
                    if ($value != "*") {
                        $shouldAdd = false;
                        break;
                    }
                }
                if ($shouldAdd) {
                    $config[$valueName] = $configValue;
                }
            }
        }

        return $config;
    }

    protected function getMergeTree($raw, $nodeValues)
    {
        $tree = [];
        $pointers = [];
        foreach ($this->schema as $key) {
            $pointers[$key] = 0;
        }

        $lastIndex = count($this->schema) - 1;
        $lastKey = $this->schema[$lastIndex];
        while ($pointers[$lastKey] <= count($nodeValues[$lastKey])) {
            $this->buildNode($tree, $pointers, $nodeValues, $raw);
            $pointers[$this->schema[0]]++;

            $currentPointer = 0;
            while (($pointers[$this->schema[$currentPointer]] > count($nodeValues[$this->schema[$currentPointer]]))) {
                if ($currentPointer + 1 > count($this->schema) - 1) {
                    break;
                }
                $pointers[$this->schema[$currentPointer]] = 0;
                $currentPointer++;
                $pointers[$this->schema[$currentPointer]]++;
            }
        }

        return $tree;
    }

    protected function setSchema($schema)
    {
        $this->schema = $schema;

        $index = 0;
        $multiplier = 1;
        foreach ($this->schema as $key) {
            $this->schemaPriorities[$key] = $multiplier;
            $multiplier *= 2;
            $index++;
        }
    }


    /**
     * @param $schema
     * @param $raw
     * @return CompiledData
     */
    public function compile($schema, $raw)
    {
        $this->pathMap = [];
        $this->setSchema($schema);
        $nodeValues = $this->getNodeValues($raw);
        $mergeTree = $this->getMergeTree($raw, $nodeValues);

        return new $this->options['treeClass']($this->schema, $mergeTree, $this->pathMap);
    }
}