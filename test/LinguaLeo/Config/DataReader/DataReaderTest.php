<?php
namespace LinguaLeo\Config\DataReader;

use LinguaLeo\Config\DataReader;
use LinguaLeo\Config\Enum;

class DataReaderTest extends \PHPUnit_Framework_TestCase
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

    public function testSchema()
    {
        $data = $this->createDataReader()->getNamespaceData(__DIR__ . '/data/features');
        $this->assertEquals(['env', 'user', 'country'], $data[Enum::KEY_SCHEMA]);
    }

    public function testTree()
    {
        $data = $this->createDataReader()->getNamespaceData(__DIR__ . '/data/features');
        $this->assertEquals(
            [
                'dev.test.*' => [
                    'obj1' => [
                        'attr1' => 1,
                        'attr2' => 2
                    ]
                ],
                'dev.*.*' => [
                    'obj2' => [
                        'attr1' => true,
                        'attr2' => true
                    ],
                    'obj2_1' => [
                        'attr1',
                        'attr2'
                    ]
                ],
                'test.*.*' => [
                    'obj3' => [
                        'attr1' => 'text',
                        'attr2' => [
                            'elem'
                        ]
                    ]
                ]
            ],
            $data[Enum::KEY_MERGE_TREE]
        );
    }


    public function testPathMap()
    {
        $data = $this->createDataReader()->getNamespaceData(__DIR__ . '/data/features');
        $this->assertEquals(
            [
                'env=dev' => [
                        'user=test' => []
                    ],
                'env=test' => []
            ],
            $data[Enum::KEY_PATH_MAP]
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNotExistingDirectory()
    {
        $this->createDataReader()->getNamespaceData(__DIR__ . '/data/notExist');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testFilesNotReadable()
    {
        $this->createDataReader()->getNamespaceData(__DIR__ . '/data/notAccessed');
    }
}
