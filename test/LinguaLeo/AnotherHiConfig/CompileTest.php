<?php

namespace LinguaLeo\AnotherHiConfig;

class CompileTest extends \PHPUnit_Framework_TestCase
{
    function testCompile()
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

        $compiler = new Compiler(["env", "lang", "user"]);

        $configRegistry = $compiler->compile($configData);

        $config = $configRegistry->getConfig(["env" => "dev", "lang" => "ru", "user" => "10000"]);
        $this->assertEquals("sandbox.api.com", $config["api_host"]);
        $this->assertEquals("ruble", $config["currency"]);
        $this->assertEquals(["fb", "vk", "ok"], $config["social_networks"]);

        $config = $configRegistry->getConfig(["env" => "prod", "lang" => "ru", "user" => "10000"]);
        $this->assertEquals("api.com", $config["api_host"]);
        $this->assertEquals("ruble", $config["currency"]);
        $this->assertEquals(["fb", "vk", "ok"], $config["social_networks"]);

        $config = $configRegistry->getConfig(["env" => "prod", "lang" => "br", "user" => "10000"]);
        $this->assertEquals("api.com", $config["api_host"]);
        $this->assertEquals("real", $config["currency"]);
        $this->assertEquals(["fb", "bs"], $config["social_networks"]);

        $config = $configRegistry->getConfig(["env" => "prod", "lang" => "br", "user" => "1024"]);
        $this->assertEquals("api.com", $config["api_host"]);
        $this->assertEquals("frank", $config["currency"]);
        $this->assertEquals(["fb", "vk", "ok", "fb", "bs"], $config["social_networks"]);

        $config = $configRegistry->getConfig(["env" => "prod", "lang" => "ru", "user" => "1024"]);
        $this->assertEquals(["fb", "vk"], $config["social_networks"]);
        $this->assertEquals("frank", $config["currency"]);

        $config = $configRegistry->getConfig(["env" => "dev", "lang" => "uk", "user" => "1024"]);
        $this->assertEquals(["fb", "vk", "ok"], $config["social_networks"]);

        $config = $configRegistry->getConfig(["env" => "stage", "lang" => "uk", "user" => "2048"]);
        $this->assertArrayNotHasKey("currency", $config);
    }
} 