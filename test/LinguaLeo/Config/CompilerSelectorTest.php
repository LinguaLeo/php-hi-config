<?php

namespace LinguaLeo\Config;

class CompilerSelectorTest extends \PHPUnit_Framework_TestCase
{
    protected function getRegistry()
    {
        $configData = [
            "currency" => [
                [["user" => "1024"], "frank"],
                [["lang" => "br"], "real"],
                [["lang" => "ru"], "ruble"],
            ],
            "social_networks" =>
                [
                    [["user" => "1024", "lang" => "ru", "env" => "dev"], ["no"]],
                    [["user" => "1024", "lang" => "ru"], ["fb", "vk"]],
                    [["user" => "1024", "lang" => "br"], ["fb", "vk", "ok", "fb", "bs"]],
                    [["user" => "1024", "env" => "dev"], ["fb", "vk", "ok"]],
                    [["user" => "1024"], ["fb"]],
                    [["lang" => "ru"], ["fb", "vk", "ok"]],
                    [["lang" => "br"], ["fb", "bs"]],
                    [[], ["fb", "gp"]]
                ],
            "api_host" => [
                [["env" => "dev"], "sandbox.api.com"],
                [[], "api.com"]
            ],
        ];

        $compiler = new Compiler();
        $compiled = $compiler->compile(["env", "lang", "user"], $configData);

        return new Selector($compiled);
    }

    public function testNoUserCompile()
    {
        $selector = $this->getRegistry();

        $config = $selector->getConfig(["dev", "ru", "10000"]);
        $this->assertEquals("sandbox.api.com", $config["api_host"]);
        $this->assertEquals("ruble", $config["currency"]);
        $this->assertEquals(["fb", "vk", "ok"], $config["social_networks"]);

        $config = $selector->getConfig(["prod", "ru", "10000"]);
        $this->assertEquals("api.com", $config["api_host"]);
        $this->assertEquals("ruble", $config["currency"]);
        $this->assertEquals(["fb", "vk", "ok"], $config["social_networks"]);

        $config = $selector->getConfig(["prod", "br", "10000"]);
        $this->assertEquals("api.com", $config["api_host"]);
        $this->assertEquals("real", $config["currency"]);
        $this->assertEquals(["fb", "bs"], $config["social_networks"]);
    }

    public function testUser()
    {
        $selector = $this->getRegistry();

        $config = $selector->getConfig(["prod", "br", "1024"]);
        $this->assertEquals("api.com", $config["api_host"]);
        $this->assertEquals("frank", $config["currency"]);
        $this->assertEquals(["fb", "vk", "ok", "fb", "bs"], $config["social_networks"]);

        $config = $selector->getConfig(["prod", "ru", "1024"]);
        $this->assertEquals(["fb", "vk"], $config["social_networks"]);
        $this->assertEquals("frank", $config["currency"]);
    }

    public function testNoLangKey()
    {
        $selector = $this->getRegistry();

        $config = $selector->getConfig(["dev", "uk", "1024"]);
        $this->assertEquals(["fb", "vk", "ok"], $config["social_networks"]);
    }

    public function testHasNotKey()
    {
        $selector = $this->getRegistry();

        $config = $selector->getConfig(["stage", "uk", "2048"]);
        $this->assertArrayNotHasKey("currency", $config);
    }

    public function testEmpty()
    {
        $schema = ["env"];
        $data = [
            "key" => [
                [[], "value"]
            ],
        ];

        $compiler = new Compiler();
        $compiled = $compiler->compile($schema, $data);

        $selector = new Selector($compiled);
        $config = $selector->getConfig(["dev"]);

        $this->assertEquals("value", $config["key"]);
    }
} 