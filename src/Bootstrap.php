<?php
/**
 * Created by PhpStorm.
 * User: Poyarkov S. <webmaster.cipa at gmail dot com>
 * Date: 05.08.18
 * Time: 0:26
 */

namespace imagetool;

use yii\base\BootstrapInterface;

/**
 * Module bootstrap.
 *
 * @package imagetool
 * @author Poyarkov S. <webmaster.cipa at gmail dot com>
 */
class Bootstrap implements BootstrapInterface
{
    /**
     * @inheritdoc
     * @param \yii\web\Application|\yii\console\Application $app
     */
    public function bootstrap($app): void
    {
        if ($app instanceof \yii\web\Application) {
            $app->getUrlManager()->addRules([
                'data/view/<filename>' => 'data/view'
            ], false);
        }
    }

}
