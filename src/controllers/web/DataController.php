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
    protected const CACHE_TIME = 86400;

    /**
     * Show image.
     * @param string $filename
     * @return null|string|View
     * @throws InvalidArgumentException
     * @throws NotFoundHttpException
     * @throws UnsupportedMediaTypeHttpException
     * @throws \ImageOptimizer\Exception\Exception
     * @throws \Intervention\Image\Exception\NotWritableException
     */
    public function actionView($filename)
    {
        $filename = Html::encode($filename);

        $request = \Yii::$app->getRequest();
        $response = \Yii::$app->getResponse();

        $image_path = File::getPath($filename);
        if (!file_exists($image_path)) {
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
        $mtime = filemtime($image_path);

        // check client cache
        if ($request->getHeaders()->has('If-Modified-Since')) {
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

        $response->format = Response::FORMAT_RAW;
        $response->getHeaders()->set('Content-Type', $mime);
//        $response->getHeaders()->set('Content-Length', $size); // wrong because of gzip
        $response->getHeaders()->set('Last-Modified', gmdate(DATE_RFC7231, $mtime));
        $response->getHeaders()->set('Cache-Control', 'public, max-age=' . static::CACHE_TIME . ', must-revalidate');
        $response->getHeaders()->set('Expires', gmdate(DATE_RFC7231, $mtime + static::CACHE_TIME));
        $response->getHeaders()->remove('Pragma');

        return (string) $image->encode($extension);
    }

}
