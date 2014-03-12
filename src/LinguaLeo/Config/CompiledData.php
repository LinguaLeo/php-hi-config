<?php
namespace LinguaLeo\Config;

class CompiledData
{
    protected $schema;
    protected $data;
    protected $pathMap;

    public function __construct($schema, $data, $pathMap)
    {
        $this->schema = $schema;
        $this->data = $data;
        $this->pathMap = $pathMap;
    }

    public function getRaw()
    {
        return [
            'data' => $this->data,
            'schema' => $this->schema,
            'pathMap' => $this->pathMap
        ];
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
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
}