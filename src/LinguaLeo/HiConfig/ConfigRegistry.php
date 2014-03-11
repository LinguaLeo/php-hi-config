<?php
namespace LinguaLeo\HiConfig;

class ConfigRegistry
{
    protected $compiledData;

    public function __construct($compiledData)
    {
        $this->compiledData = $compiledData;
    }

    public function getConfig($namespace)
    {
        $pointer = $this->compiledData;
        foreach ($namespace as $key) {
            if (!isset($pointer['nodes'][$key])) {
                $pointer = $pointer['nodes']['*'];
            } else {
                $pointer = $pointer['nodes'][$key];
            }
        }

        return $pointer['config'];
    }
}