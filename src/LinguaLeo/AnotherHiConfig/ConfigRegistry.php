<?php
namespace LinguaLeo\AnotherHiConfig;

class ConfigRegistry
{
    protected $compiledData;
    protected $schema;

    public function __construct($compiledData, $schema)
    {
        $this->compiledData = $compiledData;
        $this->schema = $schema;
    }

    public function getConfig($namespace)
    {
        $i = 0;
        $result = [];
        while ($i < pow(2, count($this->schema))) {
            $path = $this->getPath($i, $namespace);

            $node = & $this->compiledData;
            foreach ($path as $step) {
                $node = & $node[$step];
            }

            if (is_array($node)) {
                $result = $node + $result;
            }
            $i++;
        }

        return $result;
    }

    protected function getPath($pathIndex, $namespace)
    {
        $i = 1;
        $index = 0;
        $path = [];
        foreach ($this->schema as $key) {
            if (($pathIndex & $i) >> $index == 1) {
                $path[$key] = $namespace[$key];
            } else {
                $path[$key] = "*";
            }
            $i *= 2;
            $index++;
        }

        return $path;
    }
}