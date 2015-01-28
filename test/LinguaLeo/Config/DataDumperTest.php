<?php


namespace LinguaLeo\Config;


class DataDumperTest extends \PHPUnit_Framework_TestCase
{
    public function testNotExistingFolder()
    {
        $res = DataDumper::dumpData(__DIR__ . '/notExistFolder/output.php', []);
        unlink(__DIR__ . '/notExistFolder/output.php');
        rmdir(__DIR__ . '/notExistFolder');
        $this->assertTrue($res);
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
        $data = json_decode(file_get_contents($filePath), true);
        unlink($filePath);
        $this->assertEquals($data, ['test' => 'value']);
    }
}
