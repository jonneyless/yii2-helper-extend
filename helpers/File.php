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
     *
     * @return string
     */
    public static function genName($ext)
    {
        list($usec, $sec) = explode(" ", microtime());

        $fix = substr($usec, 2, 4);

        return date('YmdHis') . $fix . "." . ltrim($ext, ".");
    }

    /**
     * 根据后缀生成上传文件相对路径
     *
     * @param        $ext
     * @param string $folder
     *
     * @return string
     */
    public static function newFile($ext, $folder = '')
    {
        if($folder){
            $folder = '/' . ltrim($folder, '/');
        }

        $folder = UPLOAD_FOLDER . $folder . '/' . date('Ym') . '/' . date('d') . '/'. date('H') . '/';

        Folder::mkdir(Folder::getStatic($folder));

        $newFile = $folder . self::genName($ext);

        while(file_exists(Folder::getStatic($newFile))){
            $newFile = $folder . self::genName($ext);
        }

        return $newFile;
    }

    /**
     * 根据后缀生成上传文件相对路径
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
