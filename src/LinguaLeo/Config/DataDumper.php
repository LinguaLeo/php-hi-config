<?php


namespace LinguaLeo\Config;


class DataDumper
{
    /**
     * @param $outputFile
     * @param array $data
     */
    public static function dumpData($outputFile, array $data)
    {
        $dir = dirname($outputFile);
        if (!file_exists($dir)) {
            throw new \InvalidArgumentException(
                sprintf('dir "%s" of output file "%s"  doesn`t exist"', $dir, $outputFile)
            );
        }
        $tmpName = tempnam($dir, basename($outputFile));
        if (false !== file_put_contents($tmpName, '<?php return ' . var_export($data, 1) . ';')) {
            return @rename($tmpName, $outputFile);
        }
        return null;
    }
}
