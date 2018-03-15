<?php

namespace qooapp\helpers;

use ZipArchive;

/**
 * Zip 压缩操作类
 *
 * @package qooapp\helpers
 */
class Zip
{
    /**
     * Zip a folder
     *
     * @param string $sourcePath Path of directory to be zip.
     * @param string $outZipPath Path of output zip file.
     */
    public static function dir($sourcePath, $outZipPath)
    {
        $sourcePath = rtrim($sourcePath, "/");
        $zip = new ZipArchive();
        $zip->open($outZipPath, ZipArchive::CREATE);
        self::folderToZip($sourcePath, $zip, strlen($sourcePath . "/"));
        $zip->close();
    }

    /**
     * Add files and sub-directories in a folder to zip file.
     *
     * @param string     $folder
     * @param ZipArchive $zipFile
     * @param int        $exclusiveLength Number of text to be exclusived from the file path.
     */
    private static function folderToZip($folder, ZipArchive &$zipFile, $exclusiveLength)
    {
        $handle = opendir($folder);
        while(false !== $filename = readdir($handle)){
            if($filename != '.' && $filename != '..'){
                $filePath = $folder . '/' . $filename;
                // Remove prefix from file path before add to zip.
                $localPath = substr($filePath, $exclusiveLength);
                if(is_file($filePath)){
                    $zipFile->addFile($filePath, $localPath);
                }else if(is_dir($filePath)){
                    // Add sub-directory.
                    $zipFile->addEmptyDir($localPath);
                    self::folderToZip($filePath, $zipFile, $exclusiveLength);
                }
            }
        }
        closedir($handle);
    }
}