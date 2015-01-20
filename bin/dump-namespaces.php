<?php
/**
 * This is script readData form source-path(path of namespaces) and write in output-file
 * Example
 * php CreateNamespacesFile.php --source-path "../namespaces" --output-file "namespaces.php"
 */

use \LinguaLeo\Config\DataReader;
use LinguaLeo\Config\DataDumper;

use Symfony\Component\Console\Application;

$app = new Application();
$app->run();

include __DIR__ . '/../vendor/autoload.php';

$options = getopt('', ['source-path:', 'output-file:']);
$sourcePath = $options['source-path'];
$outputFile = $options['output-file'];
$defaultPath = [
    'env' => '*',
    'subenv' => '*',
    'protocol' => '*',
    'host' => '*',
    'app' => '*',
    'nativeLang' => '*',
    'targetLang' => '*',
    'interfaceLang' => '*',
    'country' => '*',
];
$schema = ['env', 'subenv', 'protocol', 'host', 'app', 'nativeLang', 'targetLang', 'interfaceLang', 'country'];
$dataReader = new DataReader($schema, $defaultPath);
$data = $dataReader->getNamespacesData($sourcePath);
DataDumper::dumpData($outputFile, $data);
?>

