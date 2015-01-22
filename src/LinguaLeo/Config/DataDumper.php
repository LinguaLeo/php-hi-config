<?php
namespace LinguaLeo\Config;

class DataDumper
{
    /**
     * @param string $outputFile
     * @param array $data
     * @param int $mode
     * @return bool
     */
    public static function dumpData($outputFile, array $data, $mode = 0644)
    {
        $dir = dirname($outputFile);
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0755, true)) {
                throw new \InvalidArgumentException(
                    sprintf('Can\'t create $dir "%s" of output file "%s"', $dir, $outputFile)
                );
            }
        }
        $tmpName = tempnam($dir, basename($outputFile));
        if (false !== file_put_contents($tmpName, '<?php return ' . var_export($data, 1) . ';')) {
            if (@rename($tmpName, $outputFile) && @chmod($outputFile, $mode))
                return true;
        }
        return false;
    }
}
