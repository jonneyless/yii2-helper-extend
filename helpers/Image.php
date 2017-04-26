<?php

namespace ijony\helpers;

require_once '../Boostrap.php';

use Imagine\Image\ManipulatorInterface;
use Yii;
use yii\imagine\Image as YiiImage;

/**
 * 图片处理方法
 *
 * @author jony <jonneyless@163.com>
 * @since 1.0
 */
class Image
{

    const THUMB_MODE_CUT = 1;
    const THUMB_MODE_ADAPT = 2;
    const THUMB_MODE_FILL = 3;

    /**
     * 当原图不存在时用默认图替换
     *
     * @param      $original
     * @param int  $width
     * @param int  $height
     * @param bool $default
     * @param      $mode
     *
     * @return mixed
     */
    public static function getImg($original, $width = 0, $height = 0, $default = true, $mode = self::THUMB_MODE_CUT)
    {
        if(substr($original, 0, 4) == 'http'){
            return $original;
        }

        $originalStatic = Folder::getStatic($original);

        if(!file_exists($originalStatic) || is_dir($originalStatic)){
            if($default){
                if($default === true){
                    return Url::getStatic('upload/default.jpg');
                }else{
                    return Url::getStatic($default);
                }
            }else{
                return '';
            }
        }

        list($oWidth, $oHeight) = getimagesize($oldImgFull);

        if($width >= $oWidth && $height >= $oHeight){
            return self::staticUrl($oldImg);
        }

        if($mode == self::THUMB_MODE_ADAPT){
            if($width > 0 && $height > 0){
                $scale = min($width / $oWidth, $height / $oHeight);
            }else if($width == 0){
                $scale = $height / $oHeight;
            }else if($height == 0){
                $scale = $width / $oWidth;
            }

            $width = intval($oWidth * $scale);
            $height = intval($oHeight * $scale);

            $mode = ManipulatorInterface::THUMBNAIL_OUTBOUND;
        }else{
            if($width == 0){
                return self::staticUrl($oldImg);
            }

            if($height == 0){
                $height = intval(($oHeight * $width) / $oWidth);
            }

            $mode = $mode == self::THUMB_MODE_CUT ? ManipulatorInterface::THUMBNAIL_OUTBOUND : ManipulatorInterface::THUMBNAIL_INSET;
        }

        $pathInfo = pathinfo($original);

        if($pathInfo['dirname'] == UPLOAD_FOLDER){
            $thumbFolder = THUMB_FOLDER;
        }else{
            $thumbFolder = str_replace(UPLOAD_FOLDER . '/', THUMB_FOLDER . '/', $pathInfo['dirname']);
        }

        $thumb = $thumbFolder . '/' . $pathInfo['filename'] . '_' . $width . 'x' . $height . '.' . $pathInfo['extension'];

        $thumbStatic = Folder::getStatic($thumb);

        if(!file_exists($thumbStatic)){
            Folder::mkdir(Folder::getStatic($thumbStatic));

            YiiImage::thumbnail($originalStatic, $width, $height, $mode)->save($thumbStatic, ['quality' => 90]);
        }

        return Url::getStatic($thumb);
    }

    /**
     * 复制图片
     *
     * @param string  $oldImg
     * @param string  $newImg
     * @param boolean $returnStatic
     *
     * @return string
     */
    public static function copyImg($oldImg, $newImg = '', $returnStatic = false)
    {
        if(substr($oldImg, 0, 4) != 'http'){
            $oldImgStatic = Folder::getStatic($oldImg);

            if(!$oldImg || !file_exists($oldImgStatic)){
                return $oldImg;
            }
        }else{
            $oldImgStatic = $oldImg;
        }

        if(!$newImg){
            $newImg = File::new(pathinfo($oldImgStatic, PATHINFO_EXTENSION), 'image');
        }

        $newImgStatic = Folder::getStatic($newImg);

        Folder::mkdir(pathinfo($newImgStatic, PATHINFO_DIRNAME));

        if(File::save($newImgStatic, $oldImgStatic)){
            return $returnStatic ? $newImgStatic : $newImg;
        }

        return false;
    }

    /**
     * 将字符串内的图片元数据保存为图片
     *
     * @param $content
     *
     * @return mixed
     */
    public static function recoverImg($content)
    {
        preg_match_all('/src="data:\s*image\/(\w+);base64,([^"]+)"/', $content, $match);

        $imgs = [];
        if(isset($match[2])){
            foreach($match[2] as $key => $data){
                $md5 = md5($data);

                if(isset($imgs[$md5])){
                    continue;
                }

                $ext = $match[1][$key];
                if($ext == 'jpeg'){
                    $ext = 'jpg';
                }

                $newImg = File::new($ext);

                $imgs[$md5] = $newImg;

                $content = str_replace($match[0][$key], 'src="' . Url::getStatic($newImg) .'"', $content);

                file_put_contents(Folder::getStatic($newImg), base64_decode($data));
            }
        }

        return [
            'content' => $content,
            'imgs' => $imgs,
        ];
    }
}
