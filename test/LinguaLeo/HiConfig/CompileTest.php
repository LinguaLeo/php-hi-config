<?php

namespace LinguaLeo\HiConfig;

class CompileTest extends \PHPUnit_Framework_TestCase
{
    function testCompile()
    {
        $defaults = [
            "currency" => "dollar",
            "social_networks" => ["fb", "gp"],
            "api_host" => "api.com",
        ];

        $env = [
            "dev" => [
                "api_host" => "sandbox.api.com"
            ],
        ];

        $lang = [
            "ru" => [
                "currency" => "ruble",
                "social_networks" => ["fb", "vk", "ok"]
            ],
            "br" => [
                "currency" => "real",
                "social_networks" => ["fb", "bs"]
            ]
        ];

        $user = [
            "1024" => [
                "currency" => "frank",
                "social_networks" => ["fb", "vk", "ok", "fb", "bs"]
            ]
        ];

        $compiler = new Compiler(["env", "lang", "user"], $defaults);

        $configRegistry = $compiler->compile([
            "env" => $env,
            "lang" => $lang,
            "user" => $user
        ]);

        $config = $configRegistry->getConfig(["dev", "ru", "10000"]);
        $this->assertEquals("sandbox.api.com", $config["api_host"]);
        $this->assertEquals("ruble", $config["currency"]);
        $this->assertEquals(["fb", "vk", "ok"], $config["social_networks"]);

        $config = $configRegistry->getConfig(["prod", "ru", "10000"]);
        $this->assertEquals("api.com", $config["api_host"]);
        $this->assertEquals("ruble", $config["currency"]);
        $this->assertEquals(["fb", "vk", "ok"], $config["social_networks"]);

        $config = $configRegistry->getConfig(["prod", "br", "10000"]);
        $this->assertEquals("api.com", $config["api_host"]);
        $this->assertEquals("real", $config["currency"]);
        $this->assertEquals(["fb", "bs"], $config["social_networks"]);

        $config = $configRegistry->getConfig(["prod", "br", "1024"]);
        $this->assertEquals("api.com", $config["api_host"]);
        $this->assertEquals("frank", $config["currency"]);
        $this->assertEquals(["fb", "vk", "ok", "fb", "bs"], $config["social_networks"]);
    }
} 