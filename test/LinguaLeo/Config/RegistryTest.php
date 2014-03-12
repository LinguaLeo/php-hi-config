<?php

namespace LinguaLeo\Config;

class RegistryTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaults()
    {
        $compiledData = new CompiledData(
            ["env", "lang", "user"],
            ["*.*.*" => ["value" => "default"]],
            []
        );
        $registry = new Registry($compiledData);

        $config = $registry->getConfig(["prod"]);
        $this->assertEquals("default", $config["value"]);

        $config = $registry->getConfig(["dev", "ru"]);
        $this->assertEquals("default", $config["value"]);
    }

    public function testSimplePriorities()
    {
        $compiledData = new CompiledData(
            ["env", "lang", "user"],
            [
                "*.*.*" => ["value" => "default"],
                "*.ru.*" => ["value" => "ru"],
            ],
            ["lang=ru" => []]
        );
        $registry = new Registry($compiledData);

        $config = $registry->getConfig(["prod", "br"]);
        $this->assertEquals("default", $config["value"]);

        $config = $registry->getConfig(["prod", "ru"]);
        $this->assertEquals("ru", $config["value"]);
    }

    public function testPrioritiesWithHole()
    {
        $compiledData = new CompiledData(
            ["env", "lang", "user"],
            [
                "*.*.*" => ["value" => "default"],
                "prod.*.1024" => ["value" => "prod*1024"],
                "*.*.1024" => ["value" => "**1024"],
            ],
            [
                "env=prod" => ["user=1024" => []],
                "user=1024" => [],
            ]
        );
        $registry = new Registry($compiledData);

        $config = $registry->getConfig(["prod", "br", "1024"]);
        $this->assertEquals("prod*1024", $config["value"]);

        $config = $registry->getConfig(["prod", "ru", "1024"]);
        $this->assertEquals("prod*1024", $config["value"]);

        $config = $registry->getConfig(["dev", "ru", "1024"]);
        $this->assertEquals("**1024", $config["value"]);
    }
}