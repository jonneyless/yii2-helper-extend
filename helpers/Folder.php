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
class Folder
{

    /**
     * 生成静态文件的绝对物理路径
     *
     * 当没有设置 @static 全局常量时，以 @webroot 作为根
     *
     * @param null $path
     *
     * @return bool|null|string
     */
    public static function getStatic($path = NULL)
    {
        if($path){
            if(isset(Yii::$aliases['@static'])){
                $path = Yii::getAlias('@static/' . ltrim($path, '/'));
            }else{
                $path = Yii::getAlias('@webroot/' . ltrim($path, '/'));
            }
        }

        return $path;
    }

    /**
     * 遍历生成目录
     *
     * @param string $dirpath
     *
     * @return string
     */
    public static function mkdir($dirpath)
    {
        if(isset(Yii::$aliases['@static'])){
            $root = Yii::getAlias('@static') . DIRECTORY_SEPARATOR;
        }else{
            $root = Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR;
        }

        $root = preg_replace('/[\\\\\/]/', DIRECTORY_SEPARATOR, $root);
        $dirpath = preg_replace('/[\\\\\/]/', DIRECTORY_SEPARATOR, $dirpath);

        if($dirpath != $root && !file_exists($dirpath)){
            $path = explode(DIRECTORY_SEPARATOR, str_replace($root, '', $dirpath));

            $dirpath = $root . array_shift($path);

            if(!file_exists($dirpath)){
                @mkdir($dirpath);
                @chmod($dirpath, 0777);
            }

            foreach($path as $dir){
                $dirpath .= DIRECTORY_SEPARATOR . $dir;

                if($dir != '.' && $dir != '..'){
                    if(!file_exists($dirpath)){
                        @mkdir($dirpath);
                        @chmod($dirpath, 0777);
                    }
                }
            }
        }

        return $dirpath;
    }

    /**
     * 遍历删除目录以及其所有子目录和文件
     *
     * @param string $folder 要删除的目录路径
     *
     * @return bool
     */
    public static function rmdir($folder)
    {
        set_time_limit(0);

        if(!file_exists($folder)){
            return false;
        }

        $files = array_diff(scandir($folder), ['.', '..']);

        foreach ($files as $file) {
            $file = $folder . DIRECTORY_SEPARATOR . $file;
            (is_dir($file) && !is_link($folder)) ? self::rmdir($file) : unlink($file);
        }

        return rmdir($folder);
    }

    /**
     * 清理用户临时图片
     */
    public static function clearBuffer($folder)
    {
        $bufferFolder = BUFFER_FOLDER . '/' . ltrim($folder, '/') . '/';

        self::rmdir(self::getStatic($bufferFolder));
    }

}
