<?php

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
     * @var int Client cache time in seconds
     */
    public $browser_cache_time = 2592000; // 1 month
    /**
     * @var bool Set ETag header
     */
    public $etag = true;
    /**
     * @var bool Remove Set-Cookie header
     */
    public $unset_cookie = true;

    /**
     * {@inheritdoc}
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidArgumentException
     */
    public function init(): void
    {
        parent::init();

        $this->defaultRoute = 'data/view';

        // init storage
        $storage_path = \Yii::getAlias(self::STORAGE_PATH);
        if (!\file_exists($storage_path)) {
            FileHelper::createDirectory($storage_path);
        }
    }

}
