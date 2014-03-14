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
        $data = $compiled[Enum::KEY_MERGE_TREE];

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
        $data = $compiled[Enum::KEY_MERGE_TREE];

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
        $data = $compiled[Enum::KEY_MERGE_TREE];

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
        $pathMap = $compiled[Enum::KEY_PATH_MAP];

        $this->assertEquals(["lang=ru" => ["user=1024" => []]], $pathMap["env=dev"]);
        $this->assertEquals([], $pathMap["lang=ru"]);
        $this->assertEquals(2, count($pathMap));
    }
    public function testPrefix()
    {
        $raw = [
            "currency" => [
                [[], "ruble"],
            ],
            "api_host" => [
                [[], "api.com"]
            ],
        ];

        $compiler = new Compiler();
        $compiled = $compiler->compile(["env"], $raw, 'app_')->getAsArray();
        $mergeTree = $compiled[Enum::KEY_MERGE_TREE];

        $this->assertArrayHasKey("*", $mergeTree);
        $this->assertArrayHasKey("app_currency", $mergeTree["*"]);
        $this->assertArrayHasKey("app_api_host", $mergeTree["*"]);
    }

    /**
     * @expectedException \LinguaLeo\Config\Exception
     */
    public function testSchemaException()
    {
        $raw = [
            "currency" => [
                [[], "ruble"],
            ],
        ];

        $compiler = new Compiler();
        $compiler->compile('not_schema', $raw);
    }

    /**
     * @expectedException \LinguaLeo\Config\Exception
     */
    public function testLevelDataFormatException()
    {
        $raw = [
            "currency" => [
                ["env=>dev", "ruble"],
            ],
        ];

        $compiler = new Compiler();
        $compiler->compile(['env'], $raw);
    }

    /**
     * @expectedException \LinguaLeo\Config\Exception
     */
    public function testEmptyLevelDataFormatException()
    {
        $raw = [
            "currency" => [
                ["ruble"],
            ],
        ];

        $compiler = new Compiler();
        $compiler->compile(['env'], $raw);
    }
}