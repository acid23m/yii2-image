Image
=====
Image module for my dockerized Yii2 application.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
php composer.phar require --prefer-dist acid23m/yii2-image "~1.0"
```

or add

```
"acid23m/yii2-image": "~1.0"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, do next:

- Storage path is not editable - `@root/userdata/images`.
Add `userdata/` to **.gitignore**.


- Add module (recommended id is **imagetool**) in `backend/config/main.php` and `frontend/config/main.php`.

```php
'modules' => [
    'imagetool' => [
        'class' => \imagetool\Module::class,
        'controllerNamespace' => 'imagetool\controllers\web',
        //'browser_cache_time' => 60
    ]
]
```

Helpers
-------

### File

```php
// get full path to image
// output: /var/www/site/app/userdata/images/ad/c1/abc123cde456.jpg
echo imagetool\helpers\File::getPath('abc123cde456.jpg');

// get url to image
// output: https://site.com/image-data/abc123cde456.jpg
echo imagetool\helpers\File::getUrl('abc123cde456.jpg');

// get mime type of image in data-uri format
// output: image/png
echo imagetool\helpers\File::getMimeOfDataUri('data:image/png;base64,iVBORw0KG');

// get extension of image in data-uri format
// output: png
echo imagetool\helpers\File::getExtensionOfDataUri('data:image/png;base64,iVBORw0KG');

// delete image
// unlink abc123cde456.jpg, abc123cde456@2x.jpg and abc123cde456@3x.jpg
imagetool\helpers\File::delete('abc123cde456.jpg');
```

### Html

```php
// output: <img />
echo imagetool\helpers\Html::img('abc123cde456.jpg', [
    'class' => 'img-responsive',
    'alt' => 'Company logo'
]);
```
