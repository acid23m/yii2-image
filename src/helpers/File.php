<?php
/**
 * Created by PhpStorm.
 * User: Poyarkov S. <webmaster.cipa at gmail dot com>
 * Date: 02.08.18
 * Time: 2:53
 */

namespace imagetool\helpers;

use yii\base\InvalidArgumentException;
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
     * @return string
     * @throws InvalidArgumentException
     */
    public static function getUrl(string $filename): string
    {
        $imagetool = \imagetool\Module::getInstance() === null
            ? ''
            : \imagetool\Module::getInstance()->id;

        return Url::to(["$imagetool/data/view", 'filename' => $filename]);
    }

}
