<?php

namespace imagetool\controllers\web;

use imagetool\components\Image;
use imagetool\helpers\File;
use Intervention\Image\Constraint;
use Mimey\MimeTypes;
use yii\base\InvalidArgumentException;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UnsupportedMediaTypeHttpException;
use yii\web\View;

/**
 * Class DataController.
 *
 * @package imagetool\controllers\web
 * @author Poyarkov S. <webmaster.cipa at gmail dot com>
 */
class DataController extends Controller
{
    /**
     * Shows image.
     * @param string $filename
     * @return null|string|View
     * @throws InvalidArgumentException
     * @throws NotFoundHttpException
     * @throws UnsupportedMediaTypeHttpException
     * @throws \ImageOptimizer\Exception\Exception
     * @throws \yii\base\InvalidCallException
     */
    public function actionView($filename)
    {
        $filename = Html::encode($filename);
        $image_path = File::getPath($filename);
        if (!\file_exists($image_path)) {
            throw new NotFoundHttpException('File not found.');
        }

        $extension = \pathinfo($filename, PATHINFO_EXTENSION);
        if ($extension === 'jpeg') {
            $extension = Image::FORMAT_JPG;
        }

        $mime = (new MimeTypes)->getMimeType($extension);
        if ($mime === '' || $mime === null) {
            throw new UnsupportedMediaTypeHttpException('Can\'t find mime type by given extension.');
        }

        try {
            $size = \filesize($image_path);
        } catch (\Exception $e) {
            $size = false;
        }

        $request = \Yii::$app->getRequest();
        $response = \Yii::$app->getResponse();

        $mtime = \filemtime($image_path);
        $hash = \hash('md4', $mtime);

        // checks client cache
        if ($request->getHeaders()->has('If-Modified-Since')) { // by last modified
            try {
                $if_modified_since = \DateTime::createFromFormat(
                    DATE_RFC7231,
                    $request->getHeaders()->get('If-Modified-Since'),
                    new \DateTimeZone('UTC')
                )->getTimestamp();

                if ($mtime >= $if_modified_since) {
                    $response->setStatusCode(304);

                    return null;
                }
            } catch (\Exception $e) {
            }
        }

        if ($request->getHeaders()->has('If-None-Match')) { // by etag
            $etag = $request->getHeaders()->get('If-None-Match');

            if ($hash === $etag) {
                $response->setStatusCode(304);

                return null;
            }
        }

        // prepare response
        $browser_cache_time = \imagetool\Module::getInstance()->browser_cache_time;
        $use_etag = \imagetool\Module::getInstance()->etag;
        $unset_cookie = \imagetool\Module::getInstance()->unset_cookie;

        $response->format = Response::FORMAT_RAW;
        $response->getHeaders()->set('Content-Type', $mime);
//        if ($size !== false) {
//            $response->getHeaders()->set('Content-Length', $size); // wrong because of gzip and resizing
//        }
        $response->getHeaders()->set('Last-Modified', \gmdate(DATE_RFC7231, $mtime));
        $response->getHeaders()->set('Cache-Control', "public, max-age=$browser_cache_time, must-revalidate");
        $response->getHeaders()->set('Expires', \gmdate(DATE_RFC7231, $mtime + $browser_cache_time));
        $response->getHeaders()->remove('Pragma');
        if ($use_etag) {
            $response->getHeaders()->set('ETag', $hash);
        }
        if ($unset_cookie) {
            $response->getCookies()->removeAll();
        }


        // svg
        if ($extension === 'svg') {
            return \file_get_contents($image_path);
        }


        $image = new Image($image_path);

        $width = $request->get('w');
        if ($width !== null) {
            $width = (int) $width;
        }
        $height = $request->get('h');
        if ($height !== null) {
            $height = (int) $height;
        }
        $quality = (int) $request->get('q', 100);

        // shows original image
        if ($width === null && $height === null && $quality === 100) {
            return (string) $image
                ->getManager()
                ->encode($extension, $quality);
        }

        // shows resized on-the-fly image
        /**
         * @return string
         */
        $resize_on_the_fly = static function () use ($image, $extension, $width, $height, $quality): string {
            return (string) $image
                ->getManager()
                ->resize($width, $height, static function (Constraint $constraint) {
                    $constraint->aspectRatio();
                })
                ->encode($extension, $quality);
        };
        // caches small images
        $cache = \Yii::$app->getCache();
        if ($width <= 300 && $height <= 300 && $cache !== null) {
            $cache_key = 'img-' . \hash('md4', $filename . $width . $height . $quality);

            return $cache->getOrSet($cache_key, $resize_on_the_fly, 60);
        }

        return $resize_on_the_fly();
    }

}
