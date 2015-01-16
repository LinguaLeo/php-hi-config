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

    public function testGetNamespacesData()
    {
        $data = $this->createDataReader()->getNamespacesData(__DIR__ . '/data/namespaces');
        $this->assertEquals(
            [
                'namespace1' => [
                    'schema' => ['env', 'user', 'country'],
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
                    'schema' => ['env', 'user', 'country'],
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


    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNotExistingNamespace()
    {
        $this->createDataReader()->getNamespaceData(__DIR__ . '/data/notExist');
    }

    /**
    * @expectedException \InvalidArgumentException
    */
    public function testNotExistingNamespaces()
    {
        $this->createDataReader()->getNamespacesData(__DIR__ . '/data/notExist');
    }
}
