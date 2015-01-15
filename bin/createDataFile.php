<?php
/**
 * This is script readData form source-path and write in output-file
 * Example
 * php CreateDataFile.php --source-path "../features" --output-file "cfg.features.php"
 */

use \LinguaLeo\Config\DataReader;
use LinguaLeo\Config\DataDumper;

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
$schema = [
    0 => 'env',
    1 => 'subenv',
    2 => 'protocol',
    3 => 'host',
    4 => 'app',
    5 => 'nativeLang',
    6 => 'targetLang',
    7 => 'interfaceLang',
    8 => 'country',
];
$dataReader = new DataReader($schema, $defaultPath);
$data = $dataReader->getNamespaceData($sourcePath);
DataDumper::dumpData($outputFile, $data);
?>

