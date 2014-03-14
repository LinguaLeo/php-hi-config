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
        $compiled = $compiler->compile($schema, $raw)->getAsArray();
        $data = $compiled['mergeTree'];

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
        $compiled = $compiler->compile($schema, $raw)->getAsArray();
        $data = $compiled['mergeTree'];

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
        $compiled = $compiler->compile($schema, $raw)->getAsArray();
        $data = $compiled['mergeTree'];

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
        $compiled = $compiler->compile($schema, $raw)->getAsArray();
        $pathMap = $compiled['pathMap'];

        $this->assertEquals(["lang=ru" => ["user=1024" => []]], $pathMap["env=dev"]);
        $this->assertEquals([], $pathMap["lang=ru"]);
        $this->assertEquals(2, count($pathMap));
    }
}