<?php


namespace LinguaLeo\Config;

use LinguaLeo\Config;


class DataDumperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNotExistingFile()
    {
        DataDumper::dumpData(__DIR__ . '/notExistFolder/output.php', []);
    }

    public function testResultOperation()
    {
        $filePath = __DIR__ . '/DataReader/data/test.php';
        $res = DataDumper::dumpData($filePath, ['test' => 'value']);
        $this->assertTrue($res);
        unlink($filePath);
    }
    public function testReadSavedData()
    {
        $filePath = __DIR__ . '/DataReader/data/test.php';
        DataDumper::dumpData($filePath, ['test' => 'value']);
        $data = include $filePath;
        unlink($filePath);
        $this->assertEquals($data, ['test' => 'value']);
    }
}
