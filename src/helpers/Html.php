<?php
/**
 * Created by PhpStorm.
 * User: Poyarkov S. <webmaster.cipa at gmail dot com>
 * Date: 05.08.18
 * Time: 12:15
 */

namespace imagetool\helpers;

use imagetool\components\Image;
use yii\base\InvalidArgumentException;
use yii\helpers\ArrayHelper;

/**
 * Class Html.
 *
 * @package imagetool\helpers
 * @author Poyarkov S. <webmaster.cipa at gmail dot com>
 */
class Html
{
    /**
     * Render html image tag with srcset attribute.
     * @param string $filename Image name
     * @param array $options Image tag options
     * @return string Html markup
     * @throws InvalidArgumentException
     */
    public static function img(string $filename, array $options = []): string
    {
        $info = pathinfo($filename);
        $filename = $info['basename'];
        $ext = $info['extension'];
        $name = $info['filename'];

        $dpr_postfix = Image::getDprPostfix(Image::DPR_2X);
        $filename_2 = $name . $dpr_postfix . '.' . $ext;

        $dpr_postfix = Image::getDprPostfix(Image::DPR_3X);
        $filename_3 = $name . $dpr_postfix . '.' . $ext;

        $image_1 = File::getUrl($filename);
        $image_2 = File::getUrl($filename_2);
        $image_3 = File::getUrl($filename_3);

        $srcset = "$image_1 1x";

        if (file_exists(File::getPath($filename_2))) {
            $srcset .= ", $image_2 2x";
        }
        if (file_exists(File::getPath($filename_3))) {
            $srcset .= ", $image_3 3x";
        }

        $options = ArrayHelper::merge($options, compact('srcset'));

        return \yii\helpers\Html::img($image_1, $options);
    }

}
