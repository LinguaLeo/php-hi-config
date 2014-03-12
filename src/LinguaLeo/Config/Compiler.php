<?php
namespace LinguaLeo\Config;

class Compiler
{
    protected $schema;
    protected $schemaPriorities;

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

    protected function buildNode(&$tree, $path, $nodeValues, $raw)
    {
        $translatedPath = [];

        foreach ($path as $key => $step) {
            $keys = array_keys($nodeValues[$key]);
            if (isset($keys[$step])) {
                $translatedPath[$key] = $keys[$step];
            } else {
                $translatedPath[$key] = "*";
            }
        }

        $currentNode = & $tree;
        foreach ($translatedPath as $node) {
            if (!isset($currentNode[$node])) {
                $currentNode[$node] = [];
            }
            $currentNode = & $currentNode[$node];
        }

        $currentNode = $this->getConfigValues($translatedPath, $raw);
    }

    protected function getConfigValues($path, $raw)
    {
        $config = [];
        foreach ($raw as $valueName => $levels) {
            $maxPriority = -1;
            $varValue = "";
            foreach ($levels as $level) {
                list($levelData, $configValue) = $level;

                $priority = 0;
                $shouldAdd = true;
                foreach ($levelData as $key => $value) {
                    if ($path[$key] == $value) {
                        $priority += $this->schemaPriorities[$key];
                    } else {
                        $shouldAdd = false;
                    }
                }

                if ($shouldAdd && ($priority > $maxPriority)) {
                    $maxPriority = $priority;
                    $varValue = $configValue;
                }
            }

            if ($maxPriority != -1) {
                $config[$valueName] = $varValue;
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

    public function compile($schema, $raw)
    {
        $this->schema = $schema;

        $index = 0;
        $multiplier = 1;
        foreach ($this->schema as $key) {
            $this->schemaPriorities[$key] = $multiplier;
            $multiplier *= 2;
            $index++;
        }

        $nodeValues = $this->getNodeValues($raw);
        $mergeTree = $this->getMergeTree($raw, $nodeValues);

        return ["tree" => $mergeTree, "schema" => $this->schema];
    }
}