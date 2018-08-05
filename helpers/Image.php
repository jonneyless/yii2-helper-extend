<?php

namespace ijony\helpers;

include_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Boostrap.php';

use Imagine\Image\ImageInterface;
use Imagine\Image\ManipulatorInterface;
use Yii;
use yii\base\ErrorException;
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
     * @param int  $mode
     *
     * @return mixed
     * @throws \Imagine\Image\InvalidArgumentException
     */
    public static function getImg($original, $width = 0, $height = 0, $default = true, $mode = self::THUMB_MODE_CUT)
    {
        $assetUrl = Yii::$app->getAssetManager()->getPublishedUrl('@vendor/jonneyless/yii2-admin-asset/statics');

        if(!file_exists($assetUrl)){
            Yii::$app->getAssetManager()->publish('@vendor/jonneyless/yii2-admin-asset/statics');
        }

        if(substr($original, 0, 4) == 'http'){
            return $original;
        }

        $originalStatic = Folder::getStatic($original);

        if(!file_exists($originalStatic) || is_dir($originalStatic)){
            if($default){
                if($default === true){
                    return Url::getFull($assetUrl . '/img/default.jpg');
                }else{
                    return Url::getStatic($default);
                }
            }else{
                return '';
            }
        }

        list($oWidth, $oHeight) = getimagesize($originalStatic);

        if($width >= $oWidth && $height >= $oHeight){
            return Url::getStatic($original);
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
                return Url::getStatic($original);
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
            Folder::mkdir(Folder::getStatic($thumbFolder));

            YiiImage::thumbnail($originalStatic, $width, $height, $mode)
                ->interlace(ImageInterface::INTERLACE_LINE)
                ->save($thumbStatic, ['quality' => 100]);
        }

        return Url::getStatic($thumb);
    }

    /**
     * 缩小图片
     *
     * @param     $image
     * @param     $width
     * @param int $height
     *
     * @return string
     * @throws \Imagine\Image\InvalidArgumentException
     */
    public static function resizeImg($image, $width, $height = 0)
    {
        $newImg = self::getImg($image, $width, $height, false, ManipulatorInterface::THUMBNAIL_OUTBOUND);

        if(self::copyImg($newImg, $image)){
            File::delFile($newImg);
        }
    }

    /**
     * @param        $original
     * @param        $maxHeight
     * @param string $subFolder
     *
     * @return array
     * @throws \Imagine\Image\InvalidArgumentException
     * @throws \yii\base\ErrorException
     */
    public static function verticalSplitImg($original, $maxHeight, $subFolder = 'image')
    {
        $originalStatic = Folder::getStatic($original);

        if(!file_exists($originalStatic) || is_dir($originalStatic)){
            throw new ErrorException('Not image file！');
        }

        list($oWidth, $oHeight) = getimagesize($originalStatic);

        $quantity = ceil($oHeight / $maxHeight);

        if($quantity == 1){
            return [
                $original
            ];
        }

        $perHeight = ceil($oHeight / $quantity);

        $ext = pathinfo($originalStatic, PATHINFO_EXTENSION);
        $startY = 0;
        $return = [];

        for($index = 0; $index < $quantity; $index++){
            $newImg = File::newBufferFile($ext, $subFolder);

            if($startY + $perHeight > $oHeight){
                $perHeight = $oHeight - $startY;
            }

            self::cropImg($originalStatic, Folder::getStatic($newImg), $oWidth, $perHeight, [0, $startY]);

            $return[] = $newImg;

            $startY += $perHeight;
        }

        return $return;
    }

    /**
     * @param       $image
     * @param       $newImg
     * @param       $width
     * @param       $height
     * @param array $start
     *
     * @return \Imagine\Image\ImageInterface
     * @throws \Imagine\Image\InvalidArgumentException
     */
    public static function cropImg($image, $newImg, $width, $height, array $start = [0, 0])
    {
        return YiiImage::crop($image, $width, $height, $start)
            ->interlace(ImageInterface::INTERLACE_LINE)
            ->save($newImg, ['quality' => 100]);
    }

    /**
     * 复制图片
     *
     * @param string  $oldImg
     * @param string  $newImg
     * @param string  $subFolder
     * @param boolean $returnStatic
     *
     * @return string
     */
    public static function copyImg($oldImg, $newImg = '', $subFolder = 'image', $returnStatic = false)
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
            $newImg = File::newFile(pathinfo($oldImgStatic, PATHINFO_EXTENSION), $subFolder);
        }

        $newImgStatic = Folder::getStatic($newImg);

        Folder::mkdir(pathinfo($newImgStatic, PATHINFO_DIRNAME));

        if(file_exists($newImgStatic)){
            File::delFile($newImg);
        }

        if(File::saveFile($newImgStatic, $oldImgStatic)){
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

                $newImg = File::newFile($ext);

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
