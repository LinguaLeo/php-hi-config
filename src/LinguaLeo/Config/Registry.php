<?php
namespace LinguaLeo\Config;

class Registry
{
    protected $data;
    protected $schema;
    protected $pathMap;
    protected $mergeKeyTemplate;

    /**
     * @param CompiledData $compiledData
     */
    public function __construct($compiledData)
    {
        $this->data = $compiledData->getData();
        $this->schema = $compiledData->getSchema();
        $this->pathMap = $compiledData->getPathMap();

        foreach ($this->schema as $_) {
            $this->mergeKeyTemplate[] = "*";
        }
    }

    public function getConfig($namespace)
    {
        $mergeKey = $this->mergeKeyTemplate;
        $mergeResult = $this->data[join(".", $mergeKey)];
        $priority = 1;
        $priorityTree = [];
        foreach ($namespace as $index => $value) {
            $key = $this->schema[$index] . '=' . $value;
            if (isset($this->pathMap[$key])) {
                $branchMergeKey = $mergeKey;
                $branchMergeKey[$index] = $value;
                $this->attachBranchToPriorityTree($priorityTree, $this->pathMap[$key], $namespace, $index, $branchMergeKey, $priority, $priority);
            }
            $priority *= 2;
        }

        ksort($priorityTree);
        foreach ($priorityTree as $data) {
            $mergeResult = $data + $mergeResult;
        }

        return $mergeResult;
    }

    public function attachBranchToPriorityTree(&$result, &$node, $namespace, $index, &$mergeKey, $priority, $pow)
    {
        $joinedKey = join(".", $mergeKey);
        while (++$index < count($namespace)) {
            $value = $namespace[$index];
            $key = $this->schema[$index] . '=' . $value;
            $pow *= 2;
            if (isset($node[$key])) {
                $mergeKey[$index] = $value;
                $this->attachBranchToPriorityTree($result, $node[$key], $namespace, $index, $mergeKey, $priority + $pow, $pow);
                $mergeKey[$index] = "*";
            }
        }

        if (isset($this->data[$joinedKey])) {
            $result[$priority] = $this->data[$joinedKey];
        }
    }
}