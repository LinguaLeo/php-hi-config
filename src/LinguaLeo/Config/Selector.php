<?php
namespace LinguaLeo\Config;

class Selector
{
    protected $mergeTree;
    protected $schema;
    protected $pathMap;

    protected $mergeKeyTemplate;
    protected $isMergeKeyTplCreated = false;

    /**
     * @param Data $compiledData
     */
    public function __construct($compiledData)
    {
        $this->mergeTree = $compiledData->getMergeTree();
        $this->schema = $compiledData->getSchema();
        $this->pathMap = $compiledData->getPathMap();
    }

    public function getConfig($selectPath)
    {
        if (!$this->isMergeKeyTplCreated) {
            $this->createMergeKeyTemplate();
        }

        $mergeKey = $this->mergeKeyTemplate;
        $joinedKey = join(".", $mergeKey);
        $mergeResult = isset($this->mergeTree[$joinedKey]) ? $this->mergeTree[$joinedKey] : [];
        $priority = 1;
        $priorityTree = [];
        foreach ($selectPath as $index => $value) {
            $pathKey = $this->schema[$index] . '=' . $value;
            if (isset($this->pathMap[$pathKey])) {
                $branchMergeKey = $mergeKey;
                $branchMergeKey[$index] = $value;
                $this->attachBranchToPriorityTree(
                    $priorityTree,
                    $this->pathMap[$pathKey],
                    $selectPath,
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

    protected function createMergeKeyTemplate()
    {
        $this->mergeKeyTemplate = array_fill(0, count($this->schema), Enum::BLANK);
        $this->isMergeKeyTplCreated = true;
    }

    public function attachBranchToPriorityTree(
        &$result,
        &$pathMapNode,
        $schemaPath,
        $index,
        &$mergeKey,
        $priority,
        $pow
    ) {
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
                $mergeKey[$index] = Enum::BLANK;
            }
        }

        if (isset($this->mergeTree[$joinedKey])) {
            $result[$priority] = $this->mergeTree[$joinedKey];
        }
    }
}
