<?php
/**
 * Created by PhpStorm.
 * User: Poyarkov S. <webmaster.cipa at gmail dot com>
 * Date: 02.08.18
 * Time: 2:53
 */

namespace imagetool\helpers;

use imagetool\components\Image;
use yii\base\InvalidArgumentException;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * Class File.
 *
 * @package imagetool\helpers
 * @author Poyarkov S. <webmaster.cipa at gmail dot com>
 */
class File
{
    /**
     * 2 level directories for image file without heading and trailing slashes based on filename.
     * @param string $filename
     * @return string
     */
    public static function defineDir(string $filename): string
    {
        $dir_1 = $filename[0] . $filename[1];
        $dir_2 = $filename[2] . $filename[3];

        return $dir_1 . '/' . $dir_2;
    }

    /**
     * Get full path to image.
     * @param string $filename
     * @return string
     * @throws InvalidArgumentException
     */
    public static function getPath(string $filename): string
    {
        return rtrim(\Yii::getAlias(\imagetool\Module::STORAGE_PATH), '/')
            . '/' . static::defineDir($filename)
            . '/' . $filename;
    }

    /**
     * Get url to image.
     * @param string $filename
     * @param array $params
     * @return string
     * @throws InvalidArgumentException
     */
    public static function getUrl(string $filename, array $params = []): string
    {
        $query = '';
        if (!empty($params)) {
            $query = '?' . http_build_query($params);
        }
//        $module = \imagetool\Module::getInstance();
//        $module_id = $module !== null ? $module->id : 'imagetool';

//        return Url::to(
//            ArrayHelper::merge(["/$module_id/data/view", 'filename' => $filename], $params),
//            true
//        );
        return Url::to("@web/image-data/{$filename}{$query}", true);
    }

    /**
     * Get mime type from data-uri string.
     * @param string $data Data-uri
     * @return null|string
     */
    public static function getMimeOfDataUri(string $data): ?string
    {
        $data = substr($data, 5); // cut "data:"
        if ($data === false) {
            return null;
        }

        $mime = substr($data, 0, strpos($data, ';'));

        return $mime === false ? null : $mime;
    }

    /**
     * Get extension from data-uri string.
     * @param string $data Data-uri
     * @return null|string
     */
    public static function getExtensionOfDataUri(string $data): ?string
    {
        $mime = static::getMimeOfDataUri($data);
        $mimes = new \Mimey\MimeTypes;

        return $mimes->getExtension($mime);
    }

    /**
     * Delete image.
     * @param string $filename
     * @throws InvalidArgumentException
     */
    public static function delete(string $filename): void
    {
        $info = pathinfo($filename);
        $filename = $info['basename'];
        $ext = $info['extension'];
        $name = $info['filename'];

        $dpr_postfix = Image::getDprPostfix(Image::DPR_2X);
        $filename_2 = $name . $dpr_postfix . '.' . $ext;

        $dpr_postfix = Image::getDprPostfix(Image::DPR_3X);
        $filename_3 = $name . $dpr_postfix . '.' . $ext;

        $image_1 = static::getPath($filename);
        $image_2 = static::getPath($filename_2);
        $image_3 = static::getPath($filename_3);

        try {
            unlink($image_1);
            unlink($image_2);
            unlink($image_3);
        } catch (\Throwable $e) {
        }
    }

}
