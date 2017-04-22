<?php

namespace ijony\helpers;

use Yii;

defined('STATIC_URL') or define('STATIC_URL', Yii::getAlias('@web'));

/**
 * 网址处理方法
 *
 * @author jony <jonneyless@163.com>
 * @since 1.0
 */
class Url
{

    /**
     * 生成静态文件的访问路径
     *
     * 当没有设置 STATIC_URL 全局常量时，以 @web 作为根
     *
     * @param null $path
     *
     * @return bool|null|string
     */
    public static function getStatic($path = NULL)
    {
        if($path){
            $path = Url::to(STATIC_URL . '/' . ltrim($path, '/'));
        }

        return $path;
    }
}
