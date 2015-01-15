<?php

class DataReaderTest extends \PHPUnit_Framework_TestCase
{
    public function testNamespaceFeatures()
    {
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
        $dataReader = new \LinguaLeo\Config\DataReader($schema, $defaultPath);
        $data = $dataReader->getNamespaceData(__DIR__ . '/data/features');
        $this->assertEquals($schema, $data['schema']);
        $mergeTree = [
            'dev.*.*.*.*.tr.*.*.*' => Array
            (
                'lexicGlossaryLevels' => Array
                (
                    70 => 0,
                    71 => 985,
                    72 => 1971,
                    73 => 2957,
                    74 => 3942,
                    75 => 4928,
                    76 => 5914
                )
            ),
            'dev.*.*.*.*.*.*.*.*' => Array
            (
                'analytics' => Array
                (
                    'snowplow' => 1,
                    'google' => 1,
                    'banners' => Array
                    (
                        'tedVideos' => Array
                        (
                            'type' => 'tedVideo'
                        )
                    )
                ),
                'contentImportMapping' => Array
                (
                    'ted-talks' => Array
                    (
                        'author' => 1913810,
                        'collection' => 68503
                    )
                ),
                'contentImportSourceList' => Array
                (
                    0 => 'dumpSource',
                    1 => 'tedTalksSource',
                    2 => 'tedTalksRssSource'
                )
            ),
            'test.*.*.*.*.*.*.*.*' => Array
            (
                'userSearchEngineConfig' => Array
                (
                    'search_engine_name' => 'Sphinx',
                    'index_engines' => Array
                    (
                        0 => 'Sphinx'
                    )

                )
            )
        ];
        $this->assertEquals($mergeTree, $data['mergeTree']);
        $pathMap = Array
        (
            'env=dev' => Array
            (
                'nativeLang=tr' => Array
                ()
            ),
            'env=test' => Array
            ()
        );
        $this->assertEquals($pathMap, $data['pathMap']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNotExistingDirectory()
    {
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
        $dataReader = new \LinguaLeo\Config\DataReader($schema, $defaultPath);
        $dataReader->getNamespaceData(__DIR__ . '/data/notExist');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testFilesNotReadable()
    {
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
        $dataReader = new \LinguaLeo\Config\DataReader($schema, $defaultPath);
        $dataReader->getNamespaceData(__DIR__ . '/data/notAccessed');
    }

}
