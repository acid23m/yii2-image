<?php

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
     * Renders html image tag with srcset attribute.
     * @param string $filename Image name
     * @param array $options Image tag options
     * @return string Html markup
     * @throws InvalidArgumentException
     */
    public static function img(string $filename, array $options = []): string
    {
        $info = \pathinfo($filename);
        $filename = $info['basename'];
        $ext = $info['extension'];
        $name = $info['filename'];

        $width = null;
        if (isset($options['width'])) {
            $width = (int) $options['width'];
        }

        $height = null;
        if (isset($options['height'])) {
            $height = (int) $options['height'];
        }

        $quality = null;
        if (isset($options['quality'])) {
            $quality = (int) $options['quality'];
            unset($options['quality']);
        }

        $dpr_postfix = Image::getDprPostfix(Image::DPR_2X);
        $filename_2 = $name . $dpr_postfix . '.' . $ext;

        $dpr_postfix = Image::getDprPostfix(Image::DPR_3X);
        $filename_3 = $name . $dpr_postfix . '.' . $ext;

        /**
         * @param int $dpr
         * @return array
         */
        $image_params = static function (int $dpr) use ($width, $height, $quality): array {
            $params = [];
            if ($width !== null) {
                $params['w'] = \abs($width) * $dpr;
            }
            if ($height !== null) {
                $params['h'] = \abs($height) * $dpr;
            }
            if ($quality !== null) {
                $params['q'] = \abs($quality);
            }

            return $params;
        };

        $image_1 = File::getUrl($filename, $image_params(Image::DPR_1X));
        $image_2 = File::getUrl($filename_2, $image_params(Image::DPR_2X));
        $image_3 = File::getUrl($filename_3, $image_params(Image::DPR_3X));

        $srcset = "$image_1 1x";

        if (\file_exists(File::getPath($filename_2))) {
            $srcset .= ", $image_2 2x";
        }
        if (\file_exists(File::getPath($filename_3))) {
            $srcset .= ", $image_3 3x";
        }

        $options = ArrayHelper::merge($options, \compact('srcset'));

        return \yii\helpers\Html::img($image_1, $options);
    }

}
