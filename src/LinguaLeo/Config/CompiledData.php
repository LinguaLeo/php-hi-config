<?php
namespace LinguaLeo\Config;

class CompiledData
{
    protected $schema;
    protected $mergeTree;
    protected $pathMap;

    public function __construct($schema, $mergeTree, $pathMap)
    {
        $this->schema = $schema;
        $this->mergeTree = $mergeTree;
        $this->pathMap = $pathMap;
    }

    public function getAsArray()
    {
        return [
            'mergeTree' => $this->mergeTree,
            'schema' => $this->schema,
            'pathMap' => $this->pathMap
        ];
    }

    /**
     * @return mixed
     */
    public function getMergeTree()
    {
        return $this->mergeTree;
    }

    /**
     * @return mixed
     */
    public function getPathMap()
    {
        return $this->pathMap;
    }

    /**
     * @return mixed
     */
    public function getSchema()
    {
        return $this->schema;
    }

    static public function fromArray($array)
    {
        return new self($array['schema'], $array['mergeTree'], $array['pathMap']);
    }
}