<?php

namespace ijony\helpers;

include_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Boostrap.php';

use Yii;
use yii\helpers\Html;

/**
 * 工具集
 *
 * @author jony <jonneyless@163.com>
 * @since 1.0
 */
class Utils
{

    /**
     * 用于调试的变量输出
     *
     * @param      $data
     * @param bool $end
     */
    public static function dump($data, $end = true)
    {
        echo '<pre>' . var_export($data, true) . '</pre>';

        if($end){
            Yii::$app->end();
        }
    }

    /**
     * 生成随机字符串
     *
     * @param int  $len
     * @param bool $onlyNum
     *
     * @return string
     */
    public static function getRand($len = 12, $onlyNum = false)
    {
        $chars = $onlyNum ? '0123456789' : 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        mt_srand((double)microtime() * 1000000 * getmypid());
        $return = '';
        while(strlen($return) < $len){
            $return .= substr($chars, (mt_rand() % strlen($chars)), 1);
        }

        return $return;
    }

    /**
     * UTF8 字符串截取
     *
     * @param     $str
     * @param int $start
     * @param int $len
     *
     * @return string
     */
    public static function substr($str, $start = 0, $len = 50)
    {
        return mb_strlen($str) > $len ? mb_substr($str, $start, $len, 'UTF-8') . "…" : $str;
    }

    /**
     * 字符串中间部分星号加密
     * 如果是邮箱地址，则只加密位于 @ 前的字串
     *
     * @param $str
     *
     * @return mixed|string
     */
    public static function starcode($str)
    {
        $suffix = '';

        if(filter_var($str, FILTER_VALIDATE_EMAIL)){
            list($str, $suffix) = explode("@", $str);
        }

        $len = intval(strlen($str) / 2);
        $str = substr_replace($str, str_repeat('*', $len), ceil(($len) / 2), $len);

        return $suffix ? $str . '@' . $suffix : $str;
    }

    /**
     * 检查手机号码
     *
     * @param $mobile
     *
     * @return bool|int
     */
    public static function checkMobile($mobile)
    {
        if(is_string($mobile)){
            return preg_match('/^1[0-9]{10}$/', $mobile);
        }

        return false;
    }

    /**
     * 数组转字符串
     *
     * @param $array
     *
     * @return string
     */
    public static function arrayToStr($array)
    {
        $params = [];

        foreach($array as $key => $val){
            $params[] = $key . '=' . $val;
        }

        return join("&", $params);
    }

    /**
     * 标签
     *
     * @param $text
     * @param $class
     *
     * @return string
     */
    public static function label($text, $class = '')
    {
        return Html::tag('span', $text, ['class' => 'label ' . $class]);
    }

    /**
     * 正负标签
     *
     * @param $boolen
     *
     * @return string
     */
    public static function boolenLabel($boolen)
    {
        if($boolen){
            return self::label('是', 'label-primary');
        }

        return self::label('否', 'label-danger');
    }

    /**
     * 判断是否是手机登录
     *
     * @return bool
     */
    public static function isMobile()
    {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if(isset ($_SERVER['HTTP_X_WAP_PROFILE'])){
            return true;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if(isset ($_SERVER['HTTP_VIA'])){
            // 找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高
        if(isset ($_SERVER['HTTP_X_REQUESTED_WITH'])){
            $clientkeywords = [
                'nokia',
                'sony',
                'ericsson',
                'mot',
                'samsung',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'sie-',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'iphone',
                'ipod',
                'blackberry',
                'meizu',
                'android',
                'netfront',
                'symbian',
                'ucweb',
                'windowsce',
                'palm',
                'operamini',
                'operamobi',
                'openwave',
                'nexusone',
                'cldc',
                'midp',
                'wap',
                'mobile',
            ];
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
                if(preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_X_REQUESTED_WITH']))){
                    return true;
                }
            }
        }
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高
        if(isset ($_SERVER['HTTP_USER_AGENT'])){
            $clientkeywords = [
                'nokia',
                'sony',
                'ericsson',
                'mot',
                'samsung',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'sie-',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'iphone',
                'ipod',
                'blackberry',
                'meizu',
                'android',
                'netfront',
                'symbian',
                'ucweb',
                'windowsce',
                'palm',
                'operamini',
                'operamobi',
                'openwave',
                'nexusone',
                'cldc',
                'midp',
                'wap',
                'mobile',
            ];
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if(isset($_SERVER['HTTP_USER_AGENT'])){
                if(preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))){
                    return true;
                }
            }
        }
        // 协议法，因为有可能不准确，放到最后判断
        if(isset ($_SERVER['HTTP_ACCEPT'])){
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if(
                (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) &&
                (
                    strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false ||
                    (
                        strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')
                    )
                )
            ){
                return true;
            }
        }

        return false;
    }
}
