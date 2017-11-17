<?php

namespace ijony\helpers;

include_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Boostrap.php';

use Yii;

/**
 * 网址处理方法
 *
 * @inheritdoc
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
            $path = STATIC_URL . '/' . ltrim($path, '/');
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
            $staticLen = strlen(STATIC_URL . "/");
        }
        
        if($url){
            $url = substr($url, $staticLen);
        }
        
        return $url;
    }

    /**
     * 生成完整访问地址
     *
     * @param null $path
     *
     * @return null|string
     */
    public static function getFull($path = null)
    {
        if($path){
            $path = Yii::$app->request->getHostInfo() . '/' . ltrim($path, '/');
        }

        return $path;
    }
}
