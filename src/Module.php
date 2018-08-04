<?php
/**
 * Created by PhpStorm.
 * User: Poyarkov S. <webmaster.cipa at gmail dot com>
 * Date: 01.08.18
 * Time: 1:42
 */

namespace imagetool;

use yii\base\BootstrapInterface;
use yii\helpers\FileHelper;

/**
 * Class Module.
 *
 * @package imagetool
 * @author Poyarkov S. <webmaster.cipa at gmail dot com>
 */
final class Module extends \yii\base\Module implements BootstrapInterface
{
    public const STORAGE_PATH = '@root/userdata/images';

    /**
     * @inheritdoc
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidArgumentException
     */
    public function init(): void
    {
        parent::init();

        $this->defaultRoute = 'data/view';

        // init storage
        $storage_path = \Yii::getAlias(self::STORAGE_PATH);
        if (!file_exists($storage_path)) {
            FileHelper::createDirectory($storage_path);
        }
    }

    /**
     * @inheritdoc
     * @param \yii\web\Application|\yii\console\Application $app
     */
    public function bootstrap($app): void
    {
        if ($app instanceof \yii\web\Application) {
            $app->getUrlManager()->addRules([
                "{$this->id}/data/view/<filename>" => 'data/view'
            ], false);
        }
    }


}
