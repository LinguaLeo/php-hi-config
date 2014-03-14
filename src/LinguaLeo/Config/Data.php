<?php
namespace LinguaLeo\Config;

class Data
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
            Enum::KEY_MERGE_TREE => $this->mergeTree,
            Enum::KEY_SCHEMA => $this->schema,
            Enum::KEY_PATH_MAP => $this->pathMap
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
        return new self($array[Enum::KEY_SCHEMA], $array[Enum::KEY_MERGE_TREE], $array[Enum::KEY_PATH_MAP]);
    }
}