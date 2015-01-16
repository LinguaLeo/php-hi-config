<?php
namespace LinguaLeo\Config\DataReader;

use LinguaLeo\Config\DataReader;

class DataReaderPermissionTest extends \PHPUnit_Framework_TestCase
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
    public function testFilesNotReadable()
    {
        $data = $this->createDataReader()->getNamespaceData(__DIR__ . '/data/features');
    }


}
