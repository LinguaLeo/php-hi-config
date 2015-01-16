<?php
namespace LinguaLeo\Config\DataReader;

use LinguaLeo\Config\DataReader;

class DataReaderPermissionNamespacesTest extends \PHPUnit_Framework_TestCase
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

    private function getFolderName()
    {
        return __DIR__ . '/data/cantRead';
    }


    protected function setUp()
    {
        mkdir($this->getFolderName(), 0200);
    }

    protected function tearDown()
    {
        rmdir($this->getFolderName());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testNamespacesFolderNotReadable()
    {
       $this->createDataReader()->getNamespacesData($this->getFolderName());
    }


}
