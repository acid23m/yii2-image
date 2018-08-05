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
        $filename = pathinfo($filename, PATHINFO_BASENAME);
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $name = pathinfo($filename, PATHINFO_FILENAME);

        $img_1 = File::getUrl($filename);
        $img_2 = File::getUrl($name . '@' . Image::DPR_2X . 'x.' . $ext);
        $img_3 = File::getUrl($name . '@' . Image::DPR_3X . 'x.' . $ext);

        $srcset = "$img_1 1x";

        if (file_exists($img_2)) {
            $srcset .= ", $img_2 2x";
        }
        if (file_exists($img_3)) {
            $srcset .= ", $img_3 3x";
        }

        $options = ArrayHelper::merge($options, compact('srcset'));

        return \yii\helpers\Html::img($filename, $options);
    }

}
