<?php
namespace LinguaLeo\Config;

use Symfony\Component\Yaml\Yaml;

class DataReader
{
    /**
     * @var array
     */
    private $schema;

    /**
     * @var array
     */
    private $defaultPath;

    /**
     * @param array $schema
     * @param array $defaultPath
     */
    public function __construct(array $schema, array $defaultPath)
    {
        $this->schema = $schema;
        $this->defaultPath = $defaultPath;
    }

    /**
     * @param string $namespaceDirectory
     * @return array
     */
    public function getNamespaceData($namespaceDirectory)
    {
        $mergeTree = [];
        $pathMap = [];

        if (!is_dir($namespaceDirectory)) {
            throw new \InvalidArgumentException(sprintf('namespace "%s" doesn`t exist"', $namespaceDirectory));
        }

        $directoryIterator = new \DirectoryIterator($namespaceDirectory);
        foreach ($directoryIterator as $file) {
            $fileInfo = $file->getFileInfo();
            if ($fileInfo->isFile()) {
                if (!$fileInfo->isReadable()) {
                    throw new \RuntimeException(sprintf('file "%s" isn`t readable', $fileInfo->getPathname()));
                }
                $configPath = $this->getPath($fileInfo);
                $this->populatePathMap($configPath, $pathMap);
                $data = Yaml::parse(file_get_contents($fileInfo->getPathname()));

                $mergeTree[implode('.', $configPath)] = $data;
            }
        }

        return [
            Enum::KEY_SCHEMA => $this->schema,
            Enum::KEY_MERGE_TREE => $mergeTree,
            Enum::KEY_PATH_MAP => $pathMap
        ];
    }

    /**
     * @param $namespaceDirectory
     */
    public function getNamespacesData($namespacesDirectory)
    {
        if (!is_dir($namespacesDirectory)) {
            throw new \InvalidArgumentException(
                sprintf('The directory of namespaces "%s" doesn`t exist"', $namespacesDirectory)
            );
        }

        $directoryIterator = new \DirectoryIterator($namespacesDirectory);
        $namespacesData = [];
        foreach ($directoryIterator as $folderInfo) {
            if ($folderInfo->isDir() && !$folderInfo->isDot()) {
                $namespace = $folderInfo->getFilename();
                $namespacesData[$namespace] = $this->getNamespaceData($folderInfo->getPathname());
            }
        }
        return $namespacesData;
    }

    /**
     * @param \SplFileInfo $fileInfo
     * @return array
     */
    protected function getPath(\SplFileInfo $fileInfo)
    {
        $path = $this->defaultPath;
        $rawPath = str_replace('.' . $fileInfo->getExtension(), '', $fileInfo->getBasename());
        if ($rawPath !== 'default') {
            $pathArgumentPairs = explode(',', $rawPath);
            foreach ($pathArgumentPairs as $pathArgumentPair) {
                $argument = explode('=', $pathArgumentPair);
                $path[$argument[0]] = $argument[1];
            }
        }
        return $path;
    }

    /**
     * @param array $configPath
     * @param array &$pathMap
     */
    protected function populatePathMap($configPath, &$pathMap)
    {
        foreach ($configPath as $argumentName => $argumentValue) {
            if ($argumentValue === '*') {
                continue;
            }
            $branchName = $argumentName . '=' . $argumentValue;
            if (!isset($pathMap[$branchName])) {
                $pathMap[$branchName] = [];
            }

            $pathMap = &$pathMap[$branchName];
        }
    }
}
