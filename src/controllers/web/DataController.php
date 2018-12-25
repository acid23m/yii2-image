<?php
/**
 * Created by PhpStorm.
 * User: Poyarkov S. <webmaster.cipa at gmail dot com>
 * Date: 02.08.18
 * Time: 3:07
 */

namespace imagetool\controllers\web;

use imagetool\components\Image;
use imagetool\helpers\File;
use Intervention\Image\Constraint;
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
     * Show image.
     * @param string $filename
     * @return null|string|View
     * @throws InvalidArgumentException
     * @throws NotFoundHttpException
     * @throws UnsupportedMediaTypeHttpException
     * @throws \ImageOptimizer\Exception\Exception
     */
    public function actionView($filename)
    {
        $request = \Yii::$app->getRequest();
        $response = \Yii::$app->getResponse();

        $filename = Html::encode($filename);
        $width = $request->get('w');
        if ($width !== null) {
            $width = (int) $width;
        }
        $height = $request->get('h');
        if ($height !== null) {
            $height = (int) $height;
        }
        $quality = (int) $request->get('q', 100);

        $image_path = File::getPath($filename);
        if (!\file_exists($image_path)) {
            throw new NotFoundHttpException('File not found.');
        }

        $image = new Image($image_path);

        $mime = $image->getManager()->mime();
        if ($mime === '' || $mime === null) {
            throw new UnsupportedMediaTypeHttpException('Can\'t find mime type by given extension.');
        }

        $extension = $image->getManager()->extension;
        if ($extension === 'jpeg') {
            $extension = Image::FORMAT_JPG;
        }

        $size = (int) $image->getManager()->filesize();
        $mtime = \filemtime($image_path);
        $hash = \hash('md4', $mtime);

        // check client cache
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


        $browser_cache_time = \imagetool\Module::getInstance()->browser_cache_time;
        $use_etag = \imagetool\Module::getInstance()->etag;
        $unset_cookie = \imagetool\Module::getInstance()->unset_cookie;

        $response->format = Response::FORMAT_RAW;
        $response->getHeaders()->set('Content-Type', $mime);
//        $response->getHeaders()->set('Content-Length', $size); // wrong because of gzip
        $response->getHeaders()->set('Last-Modified', gmdate(DATE_RFC7231, $mtime));
        $response->getHeaders()->set('Cache-Control', "public, max-age=$browser_cache_time, must-revalidate");
        $response->getHeaders()->set('Expires', gmdate(DATE_RFC7231, $mtime + $browser_cache_time));
        $response->getHeaders()->remove('Pragma');
        if ($use_etag) {
            $response->getHeaders()->set('ETag', $hash);
        }
        if ($unset_cookie) {
            $response->getCookies()->removeAll();
        }

        // show original image
        if ($width === null && $height === null && $quality === 100) {
            return (string) $image
                ->getManager()
                ->encode($extension, $quality);
        }

        // show image resized on-the-fly
        /**
         * @return string
         */
        $resize_on_the_fly = function () use ($image, $extension, $width, $height, $quality): string {
            return (string) $image
                ->getManager()
                ->resize($width, $height, function (Constraint $constraint) {
                    $constraint->aspectRatio();
                })
                ->encode($extension, $quality);
        };
        // cache small images
        $cache = \Yii::$app->getCache();
        if ($width <= 300 && $height <= 300 && $cache !== null) {
            $cache_key = 'img-' . \hash('md4', $filename . $width . $height . $quality);

            return $cache->getOrSet($cache_key, $resize_on_the_fly, 60);
        }

        return $resize_on_the_fly();
    }

}
