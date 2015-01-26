<?php
namespace LinguaLeo\Config\DataReader;

use LinguaLeo\Config\DataReader;

abstract class BaseDataReaderTest  extends \PHPUnit_Framework_TestCase
{
    public function createDataReader()
    {
        $defaultPath = [
            'env' => '*',
            'user' => '*',
            'country' => '*',
        ];
        $schema = ['env', 'user', 'country'];
        return new DataReader($schema, $defaultPath);
    }
}
