<?php
namespace LinguaLeo\Config\DataReader;

use LinguaLeo\Config\DataReader;

class DataReaderPermissionNamespaceTest extends \PHPUnit_Framework_TestCase
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

    private function getFileName()
    {
        return __DIR__ . '/data/features/cantRead.yaml';
    }


    protected function setUp()
    {
        $file = fopen($this->getFileName($this->getFileName()), 'w');
        fclose($file);
        chmod($this->getFileName(), 0200);
    }

    protected function tearDown()
    {
        unlink($this->getFileName());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testFileNotReadable()
    {
        $this->createDataReader()->getNamespaceData(__DIR__ . '/data/features');
    }


}
