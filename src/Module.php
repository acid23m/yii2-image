<?php
/**
 * Created by PhpStorm.
 * User: Poyarkov S. <webmaster.cipa at gmail dot com>
 * Date: 01.08.18
 * Time: 1:42
 */

namespace imagetool;

use yii\base\BootstrapInterface;

/**
 * Class Module.
 *
 * @package imagetool
 * @author Poyarkov S. <webmaster.cipa at gmail dot com>
 */
final class Module extends \yii\base\Module implements BootstrapInterface
{
    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
    }

    /**
     * @inheritdoc
     * @param \yii\web\Application|\yii\console\Application $app
     */
    public function bootstrap($app): void
    {

    }

}
