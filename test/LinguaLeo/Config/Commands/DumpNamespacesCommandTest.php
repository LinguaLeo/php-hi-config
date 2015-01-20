<?php
namespace LinguaLeo\Config\Commands;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Application;

class DumpNamespacesCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $application = new Application();
        $application->add(new DumpNamespacesCommand());

        $command = $application->find('cfg:dump-namespaces');
        $commandTester = new CommandTester($command);
        $outputFile =  __DIR__ .'/../DataReader/data/output.php';
        $commandTester->execute(
            array(
                '--source-path'    => __DIR__ . '/../DataReader/data/namespaces',
                '--output-file'  => $outputFile
            )
        );

        $data = include $outputFile;
        unlink($outputFile);
        $this->assertEquals(
             [
                 'namespace1' => [
                     'schema' => ['env', 'subenv', 'protocol', 'host', 'app','nativeLang', 'targetLang',
                         'interfaceLang','country'],
                     'mergeTree' => [
                         'dev.*.*.*.*.*.*.*.*' => [
                             'obj' => [
                                 'attr1' => 'namespace1',
                                 'attr2' => 'test1'
                             ]
                         ]
                     ],
                     'pathMap' => [
                         'env=dev' => []
                     ]
                 ],
                 'namespace2' => [
                     'schema' => ['env', 'subenv', 'protocol', 'host', 'app','nativeLang', 'targetLang',
                         'interfaceLang','country'],
                     'mergeTree' => [
                         'test.*.*.*.*.*.*.*.*' => [
                             'obj' => [
                                 'attr1' => 'namespace2',
                                 'attr2' => 'test2'
                             ]
                         ]
                     ],
                     'pathMap' => [
                         'env=test' => []
                     ]
                 ]
             ],
             $data
         );
    }
}
