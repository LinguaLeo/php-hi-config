Awesome hierarchical configs
=============

Usage example
-------------
```php
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
$registry = new Registry($compiled);

$config = $registry->getConfig(["dev", "ru", "10000"]);
var_dump($config["api_host"]);

$config = $registry->getConfig(["prod", "ru", "1024"]);
var_dump($config["api_host"]);
```