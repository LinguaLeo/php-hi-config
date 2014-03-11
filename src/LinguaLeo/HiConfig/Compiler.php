<?php
namespace LinguaLeo\HiConfig;

class Compiler
{
    protected $schema;
    protected $defaults;

    function __construct($schema, $defaults)
    {
        $this->schema = $schema;
        $this->defaults = $defaults;
    }

    public function compile($raw)
    {
        $result = [];
        $this->compileRecursion($raw, $this->schema, $result, $this->defaults);

        return new ConfigRegistry($result);
    }

    protected function compileRecursion($raw, $schema, &$result, $parentConfig)
    {
        if (!isset($schema[0])) {
            return;
        }

        $currentLevel = $schema[0];

        $schema = array_slice($schema, 1);
        foreach ($raw[$currentLevel] as $levelKey => $config) {
            $result['nodes'][$levelKey]['config'] = $config + $parentConfig;
            $this->compileRecursion($raw, $schema, $result['nodes'][$levelKey], $result['nodes'][$levelKey]['config']);
        }

        $result['nodes']['*']['config'] = $parentConfig;
        $this->compileRecursion($raw, $schema, $result['nodes']['*'], $parentConfig);
    }
} 