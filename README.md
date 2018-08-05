Image
=====
Image module for my dockerized Yii2 application.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
php composer.phar require --prefer-dist acid23m/yii2-image "dev-master"
```

or add

```
"acid23m/yii2-image": "dev-master"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, do next:

- Storage path is not editable - *@root/userdata/images*.
Add *userdata/* to **.gitignore**.


- Add module (recommended id is *imagetool*) in *backend/config/main.php* and *frontend/config/main.php*.

```php
'modules' => [
    'imagetool' => [
        'class' => \imagetool\Module::class,
        'controllerNamespace' => 'imagetool\controllers\web'
    ]
]
```

Helpers
-------

- File

```php
// get full path to image
echo imagetool\helpers\File::getPath('abc123cde456.jpg');

// get url to image
// link will look like https://example.com/image-data/abc123cde456.jpg
echo imagetool\helpers\File::getUrl('abc123cde456.jpg');
```

- Html

```php
echo imagetool\helpers\Html::img('abc123cde456.jpg', [
    'class' => 'img-responsive',
    'alt' => 'Company logo'
]);
```
