<?php
/**
 * Created by PhpStorm.
 * User: Poyarkov S. <webmaster.cipa at gmail dot com>
 * Date: 01.08.18
 * Time: 1:42
 */

namespace imagetool;

use yii\helpers\FileHelper;

/**
 * Class Module.
 *
 * @package imagetool
 * @author Poyarkov S. <webmaster.cipa at gmail dot com>
 */
final class Module extends \yii\base\Module
{
    public const DEFAULT_ID = 'imagetool';
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

}
