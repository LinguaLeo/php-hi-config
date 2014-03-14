<?php
namespace LinguaLeo\Config;

class Registry
{
    protected $mergeTree;
    protected $schema;
    protected $pathMap;

    protected $mergeKeyTemplate;
    protected $isMergeKeyTemplateCreated = false;

    /**
     * @param CompiledData $compiledData
     */
    public function __construct($compiledData)
    {
        $this->mergeTree = $compiledData->getMergeTree();
        $this->schema = $compiledData->getSchema();
        $this->pathMap = $compiledData->getPathMap();
    }

    protected function createMergeKeyTemplate()
    {
        foreach ($this->schema as $_) {
            $this->mergeKeyTemplate[] = "*";
        }

        $this->isMergeKeyTemplateCreated = true;
    }

    public function getConfig($schemaPath)
    {
        if (!$this->isMergeKeyTemplateCreated) {
            $this->createMergeKeyTemplate();
        }

        $mergeKey = $this->mergeKeyTemplate;
        $mergeResult = $this->mergeTree[join(".", $mergeKey)];
        $priority = 1;
        $priorityTree = [];
        foreach ($schemaPath as $index => $value) {
            $pathKey = $this->schema[$index] . '=' . $value;
            if (isset($this->pathMap[$pathKey])) {
                $branchMergeKey = $mergeKey;
                $branchMergeKey[$index] = $value;
                $this->attachBranchToPriorityTree(
                    $priorityTree,
                    $this->pathMap[$pathKey],
                    $schemaPath,
                    $index,
                    $branchMergeKey,
                    $priority,
                    $priority
                );
            }
            $priority *= 2;
        }

        ksort($priorityTree);
        foreach ($priorityTree as $data) {
            $mergeResult = $data + $mergeResult;
        }

        return $mergeResult;
    }

    public function attachBranchToPriorityTree(&$result, &$pathMapNode, $schemaPath, $index, &$mergeKey, $priority, $pow)
    {
        $joinedKey = join(".", $mergeKey);
        while (++$index < count($schemaPath)) {
            $value = $schemaPath[$index];
            $pathKey = $this->schema[$index] . '=' . $value;
            $pow *= 2;
            if (isset($pathMapNode[$pathKey])) {
                $mergeKey[$index] = $value;
                $this->attachBranchToPriorityTree(
                    $result,
                    $pathMapNode[$pathKey],
                    $schemaPath,
                    $index,
                    $mergeKey,
                    $priority + $pow,
                    $pow
                );
                $mergeKey[$index] = "*";
            }
        }

        if (isset($this->mergeTree[$joinedKey])) {
            $result[$priority] = $this->mergeTree[$joinedKey];
        }
    }
}