<?php

namespace ijony\helpers;

include_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Boostrap.php';

use Yii;

/**
 * 网址处理方法
 *
 * {@inheritdoc}
 *
 * @author jony <jonneyless@163.com>
 * @since 1.0
 */
class Url extends \yii\helpers\Url
{

    /**
     * 生成静态文件的访问路径
     *
     * @param null $path
     *
     * @return bool|null|string
     */
    public static function getStatic($path = NULL)
    {
        if($path){
            $path = self::getFull('/' . ltrim($path, '/'), 'static');
        }

        return $path;
    }

    /**
     * 剔除静态文件
     * 
     * @param null $url
     *
     * @return mixed|null
     */
    public static function trimStatic($url = NULL)
    {
        static $staticLen;
        
        if($staticLen === NULL){
            $staticLen = strlen(self::getFull('/' . ltrim($path, '/'), 'static') . "/");
        }
        
        if($url){
            $url = substr($url, $staticLen);
        }
        
        return $url;
    }

    /**
     * 生成完整访问地址
     *
     * @param string   $path
     * @param string $front
     *
     * @return null|string
     */
    public static function getFull($path = NULL, $front = '')
    {
        if($path){
            $hostInfo = Yii::$app->request->getHostInfo();
            $hostInfo = parse_url($hostInfo);
            $scheme =  $hostInfo['scheme'];
            $host =  $hostInfo['host'];
            if($front){
                $host = explode(".", $host);
                $host[0] = $front;
                $host = join(".", $host);
            }
            $path = $scheme . '://' . $host . '/' . ltrim($path, '/');
        }

        return $path;
    }
}
