<?php
namespace LinguaLeo\Config\Commands;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Application;

class DumpNamespacesCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecuteFileData()
    {
        $application = new Application();
        $application->add(new DumpNamespacesCommand());

        $command = $application->find('cfg:dump-namespaces');
        $commandTester = new CommandTester($command);
        $outputFile =  __DIR__ .'/../DataReader/data/output.php';
        $commandTester->execute(
            array(
                '--source-path'    => __DIR__ . '/../DataReader/data/namespaces',
                '--output-file'  => $outputFile,
                '--schema' => 'env,lang,user'
            )
        );

        $data = json_decode(file_get_contents($outputFile), true);

        unlink($outputFile);
        $this->assertEquals(
             [
                 'namespace1' => [
                     'schema' => ['env', 'lang', 'user'],
                     'mergeTree' => [
                         'dev.*.*' => [
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
                     'schema' => ['env', 'lang', 'user'],
                     'mergeTree' => [
                         'test.*.*' => [
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
    public function testExecuteOutputStratus()
    {
        $application = new Application();
        $application->add(new DumpNamespacesCommand());

        $command = $application->find('cfg:dump-namespaces');
        $commandTester = new CommandTester($command);
        $outputFile =  __DIR__ .'/../DataReader/data/output.php';
        $commandTester->execute(
            array(
                '--source-path'    => __DIR__ . '/../DataReader/data/namespaces',
                '--output-file'  => $outputFile,
                '--schema' => 'env,lang,user'
            )
        );
        unlink($outputFile);
        $this->assertEquals('Dump SUCCESS'."\n", $commandTester->getDisplay());
    }
}
