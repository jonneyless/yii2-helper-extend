<?php

namespace ijony\helpers;

require_once '../Boostrap.php';

use Yii;

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
}
