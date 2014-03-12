<?php

namespace LinguaLeo\Config;

class CompilerTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaults()
    {
        $raw = [
            "currency" => [
                [[], "ruble"],
            ],
            "social_networks" =>
                [
                    [[], ["fb", "gp"]]
                ],
            "api_host" => [
                [[], "api.com"]
            ],
        ];

        $schema = ["env", "lang", "user"];

        $compiler = new Compiler();
        $compiled = $compiler->compile($schema, $raw)->getRaw();
        $data = $compiled['data'];

        $this->assertEquals("ruble", $data["*.*.*"]["currency"]);
        $this->assertEquals(["fb", "gp"], $data["*.*.*"]["social_networks"]);
        $this->assertEquals("api.com", $data["*.*.*"]["api_host"]);
    }

    public function testNodes()
    {
        $raw = [
            "currency" => [
                [["env" => "dev", "lang" => "ru"], "dollar"],
                [["env" => "dev"], "frank"],
                [["lang" => "ru"], "real"],
                [[], "ruble"],
            ],
            "api_host" => [
                [["env" => "dev", "lang" => "br", "user" => 1024], "br.api.com"],
                [[], "api.com"]
            ],
        ];

        $schema = ["env", "lang", "user"];

        $compiler = new Compiler();
        $compiled = $compiler->compile($schema, $raw)->getRaw();
        $data = $compiled['data'];

        $this->assertEquals("dollar", $data["dev.ru.*"]["currency"]);
        $this->assertEquals("frank", $data["dev.*.*"]["currency"]);
        $this->assertEquals("real", $data["*.ru.*"]["currency"]);
        $this->assertEquals("ruble", $data["*.*.*"]["currency"]);
        $this->assertEquals("api.com", $data["*.*.*"]["api_host"]);
        $this->assertEquals("br.api.com", $data["dev.br.1024"]["api_host"]);
    }

    public function testRedundantNodes()
    {
        $raw = [
            "currency" => [
                [["env" => "dev"], "frank"],
                [["lang" => "ru"], "real"],
                [[], "ruble"],
            ],
            "api_host" => [
                [["env" => "prod", "lang" => "br", "user" => 1024], "br.api.com"],
                [[], "api.com"]
            ],
        ];

        $schema = ["env", "lang", "user"];

        $compiler = new Compiler();
        $compiled = $compiler->compile($schema, $raw)->getRaw();
        $data = $compiled['data'];

        $this->assertEquals("frank", $data["dev.*.*"]["currency"]);
        $this->assertEquals("real", $data["*.ru.*"]["currency"]);
        $this->assertEquals("ruble", $data["*.*.*"]["currency"]);
        $this->assertEquals("br.api.com", $data["prod.br.1024"]["api_host"]);
        $this->assertEquals(4, count($data));
    }

    public function testPathMap()
    {
        $raw = [
            "currency" => [
                [["env" => "dev", "lang" => "ru"], "dollar"],
                [["env" => "dev"], "frank"],
                [["lang" => "ru"], "real"],
                [[], "ruble"],
            ],
            "api_host" => [
                [["env" => "dev", "lang" => "ru", "user" => 1024], "br.api.com"],
                [[], "api.com"]
            ],
        ];

        $schema = ["env", "lang", "user"];

        $compiler = new Compiler();
        $compiled = $compiler->compile($schema, $raw)->getRaw();
        $pathMap = $compiled['pathMap'];

        //var_dump($pathMap);

        $this->assertEquals(["lang=ru" => ["user=1024" => []]], $pathMap["env=dev"]);
        $this->assertEquals([], $pathMap["lang=ru"]);
        $this->assertEquals(2, count($pathMap));
    }

//    function testCompile()
//    {
//        $configData = [
//            "currency" => [
//                [["user" => "1024"], "frank"],
//                [["lang" => "br"], "real"],
//                [["lang" => "ru"], "ruble"],
//            ],
//            "social_networks" =>
//                [
//                    [["user" => "1024", "lang" => "ru", "env" => "dev"], ["no"]],
//                    [["user" => "1024", "lang" => "ru"], ["fb", "vk"]],
//                    [["user" => "1024", "lang" => "br"], ["fb", "vk", "ok", "fb", "bs"]],
//                    [["user" => "1024", "env" => "dev"], ["fb", "vk", "ok"]],
//                    [["user" => "1024"], ["fb"]],
//                    [["lang" => "ru"], ["fb", "vk", "ok"]],
//                    [["lang" => "br"], ["fb", "bs"]],
//                    [[], ["fb", "gp"]]
//                ],
//            "api_host" => [
//                [["env" => "dev"], "sandbox.api.com"],
//                [[], "api.com"]
//            ],
//        ];
//
//        $compiler = new Compiler();
//        $compiled = $compiler->compile(["env", "lang", "user"], $configData);
//
//        $registry = new Registry($compiled);
//
//        $config = $registry->getConfig(["env" => "dev", "lang" => "ru", "user" => "10000"]);
//        $this->assertEquals("sandbox.api.com", $config["api_host"]);
//        $this->assertEquals("ruble", $config["currency"]);
//        $this->assertEquals(["fb", "vk", "ok"], $config["social_networks"]);
//
//        $config = $registry->getConfig(["env" => "prod", "lang" => "ru", "user" => "10000"]);
//        $this->assertEquals("api.com", $config["api_host"]);
//        $this->assertEquals("ruble", $config["currency"]);
//        $this->assertEquals(["fb", "vk", "ok"], $config["social_networks"]);
//
//        $config = $registry->getConfig(["env" => "prod", "lang" => "br", "user" => "10000"]);
//        $this->assertEquals("api.com", $config["api_host"]);
//        $this->assertEquals("real", $config["currency"]);
//        $this->assertEquals(["fb", "bs"], $config["social_networks"]);
//
//        $config = $registry->getConfig(["env" => "prod", "lang" => "br", "user" => "1024"]);
//        $this->assertEquals("api.com", $config["api_host"]);
//        $this->assertEquals("frank", $config["currency"]);
//        $this->assertEquals(["fb", "vk", "ok", "fb", "bs"], $config["social_networks"]);
//
//        $config = $registry->getConfig(["env" => "prod", "lang" => "ru", "user" => "1024"]);
//        $this->assertEquals(["fb", "vk"], $config["social_networks"]);
//        $this->assertEquals("frank", $config["currency"]);
//
//        $config = $registry->getConfig(["env" => "dev", "lang" => "uk", "user" => "1024"]);
//        $this->assertEquals(["fb", "vk", "ok"], $config["social_networks"]);
//
//        $config = $registry->getConfig(["env" => "stage", "lang" => "uk", "user" => "2048"]);
//        $this->assertArrayNotHasKey("currency", $config);
//    }
//
//    public function testEmpty()
//    {
//        $schema = ["env"];
//        $data = [
//            "key" => [
//                [[], "value"]
//            ],
//        ];
//
//        $compiler = new Compiler();
//        $compiled = $compiler->compile($schema, $data);
//
//        $registry = new Registry($compiled);
//        $config = $registry->getConfig(["env" => "dev"]);
//
//        $this->assertEquals("value", $config["key"]);
//    }
} 