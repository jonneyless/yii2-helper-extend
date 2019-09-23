<?php

namespace ijony\helpers;

include_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Boostrap.php';

use Yii;

/**
 * 文件夹处理方法
 *
 * @author jony <jonneyless@163.com>
 * @since 1.0
 */
class File
{

    /**
     * 生成文件名
     *
     * @param string $ext
     * @param array $options
     *
     * @return string
     */
    public static function genName($ext, $options = [])
    {
        if ($ext) {
            $ext = "." . ltrim($ext, ".");
        }

        $onlyNum = false;
        $lowercase = false;
        $len = 32;

        if (is_array($options)) {
            if (isset($options['onlyNum'])) {
                $onlyNum = $options['onlyNum'];
            }

            if (isset($options['lowercase'])) {
                $lowercase = $options['lowercase'];
            }

            if (isset($options['len'])) {
                $len = $options['len'];
            }

            if (isset($options['md5'])) {
                return md5($options['md5']) . $ext;
            }
        }

        return Utils::getRand($len, $onlyNum, $lowercase) . $ext;
    }

    /**
     * 根据后缀生成上传文件相对路径
     *
     * @param        $ext
     * @param array $options
     *
     * @return string
     */
    public static function newFile($ext, $options = [])
    {

        $folder = UPLOAD_FOLDER . '/' . date('Ym') . '/' . date('d') . '/' . date('H');

        if (is_string($options)) {
            $folder = $options;
        } else if (is_array($options) && isset($options['folder'])) {
            $folder = $options['folder'];
            unset($options['folder']);
        }

        $folder = rtrim($folder, "/") . "/";

        Folder::mkdir(Folder::getStatic($folder));

        if (is_array($options) && isset($options['filename'])) {
            $newFile = $folder . $options['filename'] . '.' . $ext;

            if (!isset($options['cover'])) {
                $options['cover'] = true;
            }

            if ($options['cover'] && file_exists(Folder::getStatic($newFile))) {
                self::delFile($newFile);
            }
        } else {
            $newFile = $folder . self::genName($ext, $options);

            while (file_exists(Folder::getStatic($newFile))) {
                $newFile = $folder . self::genName($ext, $options);
            }
        }

        return $newFile;
    }

    /**
     * 生成暂存目录下的文件名
     *
     * @param        $ext
     * @param string $folder
     *
     * @return string
     */
    public static function newBufferFile($ext, $folder)
    {
        $folder = BUFFER_FOLDER . '/' . ltrim($folder, '/') . '/';

        Folder::mkdir(Folder::getStatic($folder));

        $newFile = $folder . self::genName($ext);

        while(file_exists(Folder::getStatic($newFile))){
            $newFile = $folder . self::genName($ext);
        }

        return $newFile;
    }

    /**
     * 判断是否为暂存目录下的文件
     *
     * @param $filePath
     */
    public static function isBufferFile($filePath)
    {
        $bufferFolder = BUFFER_FOLDER;

        return substr($filePath, 0, strlen($bufferFolder)) == $bufferFolder;
    }

    /**
     * 将文件保存
     *
     * @param string $file 目标文件
     * @param string $source 源文件
     *
     * @return boolean
     */
    public static function saveFile($file, $source)
    {
        if(@copy($source, $file)){
            return true;
        }else{
            if(function_exists('move_uploaded_file') && @move_uploaded_file($source, $file)){
                return true;
            }else{
                if(@is_readable($source) && (@$fp_s = fopen($source, 'rb')) && (@$fp_t = fopen($file, 'wb'))){

                    while(!feof($fp_s)){
                        $s = @fread($fp_s, 1024 * 512);
                        @fwrite($fp_t, $s);
                    }

                    fclose($fp_s);
                    fclose($fp_t);

                    return true;
                }else{
                    return false;
                }
            }
        }
    }

    /**
     * 将文件删除，第二个参数用于清理缩略图
     *
     * @param      $file
     * @param bool $image
     */
    public static function delFile($file, $image = false)
    {
        $fileStatic = Folder::getStatic($file);

        if(!file_exists($fileStatic)){
            return;
        }

        if(file_exists($fileStatic) && !is_dir($fileStatic)){
            @unlink($fileStatic);
        }

        if($image){
            $pathInfo = pathinfo($file);

            if($pathInfo['dirname'] == UPLOAD_FOLDER){
                $thumbFolder = THUMB_FOLDER;
            }else{
                $thumbFolder = str_replace(UPLOAD_FOLDER . '/', THUMB_FOLDER . '/', $pathInfo['dirname']);
            }

            $thumbs = glob(Folder::getStatic($thumbFolder . '/' . $pathInfo['filename'] . '_*.' . $pathInfo['extension']));

            if($thumbs){
                foreach($thumbs as $thumb){
                    @unlink($thumb);
                }
            }
        }
    }
}
