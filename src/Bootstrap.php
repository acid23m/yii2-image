<?php

namespace imagetool;

use yii\base\BootstrapInterface;

/**
 * Module bootstrap.
 *
 * @package imagetool
 * @author Poyarkov S. <webmaster.cipa at gmail dot com>
 */
final class Bootstrap implements BootstrapInterface
{
    /**
     * {@inheritdoc}
     * @param \yii\web\Application|\yii\console\Application $app
     */
    public function bootstrap($app): void
    {
        if ($app instanceof \yii\web\Application) {
            $module = \imagetool\Module::getInstance();
            $module_id = $module !== null ? $module->id : \imagetool\Module::DEFAULT_ID;

            $app->getUrlManager()->addRules([
                'image-data/<filename>' => "$module_id/data/view"
            ], false);
        }
    }

}
