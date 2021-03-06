<?php

namespace imagetool\components;

use ImageOptimizer\Optimizer;
use ImageOptimizer\OptimizerFactory;
use imagetool\helpers\File;
use Intervention\Image\Constraint;
use Intervention\Image\Exception\NotWritableException;
use Intervention\Image\Image as ImageLib;
use Intervention\Image\ImageManager;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\helpers\FileHelper;

/**
 * Manipulates images.
 * Works only with raster graphics.
 *
 * @package imagetool\components
 * @author Poyarkov S. <webmaster.cipa at gmail dot com>
 * @link http://image.intervention.io/
 * @link https://github.com/psliwa/image-optimizer
 */
class Image extends Component
{
    public const DPR_1X = 1;
    public const DPR_2X = 2;
    public const DPR_3X = 3;
    public const DPR_POSTFIX = '@{dpr}x';

    public const FORMAT_JPG = 'jpg';
    public const FORMAT_PNG = 'png';
    public const FORMAT_GIF = 'gif';
    public const FORMAT_TIF = 'tif';
    public const FORMAT_BMP = 'bmp';
    public const FORMAT_ICO = 'ico';
    public const FORMAT_PSD = 'psd';
    public const FORMAT_WEBP = 'webp';
    public const FORMAT_DATA_URI = 'data-url';

    public const FONT_FILE = '@vendor/acid23m/yii2-image/src/assets/fonts/Anonymous_Pro.ttf';

    /**
     * @var array Configuration for image optimizer
     */
    public $optimizer_config = [
        'ignore_errors' => false,
//        'ignore_errors' => !YII_DEBUG,
        'execute_only_first_png_optimizer' => false,
        'execute_only_first_jpeg_optimizer' => false
    ];
    /**
     * @var int Image quality
     */
    public $quality = 90;
    /**
     * @var bool Use image optimizers or not
     */
    public $use_optimizer = true;

    /**
     * @var ImageLib
     */
    private $image_manager;
    /**
     * @var Optimizer
     */
    private $image_optimizer;
    /**
     * @var string Image filename without extension
     */
    private $image_name;

    /**
     * Image constructor.
     * @param string|\Imagick|ImageLib|\SplFileInfo $img
     * @param array $config
     * @throws \ImageOptimizer\Exception\Exception
     */
    public function __construct($img, $config = [])
    {
        parent::__construct($config);

        // init image manager
        $image_manager = new ImageManager(['driver' => 'imagick']);
        $this->image_manager = $image_manager->make($img);

        // init image optimizer
        $opt_factory = new OptimizerFactory($this->optimizer_config);
        $this->image_optimizer = $opt_factory->get();

        // set image filename
        /*$this->image_name = md5(
            ((string) $this->getManager())
            . time()
        );*/
        $this->image_name = \md5(
            \time()
            . \random_int(0, 10000)
        );
    }

    /**
     * @return ImageLib
     */
    public function getManager(): ImageLib
    {
        return $this->image_manager;
    }

    /**
     * @return Optimizer
     */
    public function getOptimizer(): Optimizer
    {
        return $this->image_optimizer;
    }

    /**
     * @return string Image filename without extension
     */
    public function getName(): string
    {
        return $this->image_name;
    }

    /**
     * 2 level directories for image file without heading and trailing slashes.
     * @return string Directories
     */
    public function getDir(): string
    {
        return File::defineDir($this->getName());
    }

    /**
     * Resize the image and constrain aspect ratio.
     * @param int|null $width
     * @param int|null $height
     * @return ImageLib
     */
    public function resizeProportional(?int $width, ?int $height): ImageLib
    {
        return $this->getManager()->resize($width, $height, static function (Constraint $constraint) {
            $constraint->aspectRatio();
        });
    }

    /**
     * Image postfix.
     * @param int $dpr
     * @return string
     */
    public static function getDprPostfix(int $dpr): string
    {
        return $dpr <= 1 ? '' : \str_replace('{dpr}', $dpr, self::DPR_POSTFIX);
    }

    /**
     * Resize the image for retina display (device pixel ratio).
     * @param int $orig Original DPR
     * @param int $new New DPR
     * @param bool $resize Change image size
     * @return ImageLib
     */
    public function changeDPR(int $orig, int $new, bool $resize = true): ImageLib
    {
        $dpr_postfix = self::getDprPostfix($new);
        $this->image_name .= $dpr_postfix;

        if (!$resize) {
            return $this->getManager();
        }

        $ratio = $new / $orig;
        $current_width = $this->getManager()->width();
        $new_width = (int) \ceil($current_width * $ratio);

        return $this->resizeProportional($new_width, null);
    }

    /**
     * Save image file.
     * @param string $ext File extension
     * @return string Image filename
     * @throws NotWritableException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function save(string $ext): string
    {
        // create directory
        $storage_path = \rtrim(\Yii::getAlias(\imagetool\Module::STORAGE_PATH), '/');
        $image_save_path = $storage_path . '/' . $this->getDir();
        if (!\file_exists($image_save_path)) {
            FileHelper::createDirectory($image_save_path);
        }

        $image_save_file = $image_save_path . '/' . $this->getName() . ".$ext";

        // save
        $this->getManager()->save($image_save_file, $this->quality);
        // optimize
        if ($this->use_optimizer) {
            try {
                $this->getOptimizer()->optimize($image_save_file);
            } catch (\ImageOptimizer\Exception\Exception $e) {
            }
        }

        return $this->getName() . ".$ext";
    }

    /**
     * Get image source.
     * @param string $format
     * @return string
     * @throws NotWritableException
     * @link http://image.intervention.io/api/encode
     */
    public function encode(string $format): string
    {
        $storage_path = \rtrim(\sys_get_temp_dir(), '/');
        $image_save_file = $storage_path . '/' . \md5($format . \time());

        // save temporary
        $this->getManager()->encode($format)->save($image_save_file, $this->quality);
        // optimize
        if ($this->use_optimizer) {
            try {
                $this->getOptimizer()->optimize($image_save_file);
            } catch (\ImageOptimizer\Exception\Exception $e) {
            }
        }

        $image_manager = new ImageManager(['driver' => 'imagick']);
        $image = $image_manager->make($image_save_file);

        \unlink($image_save_file);

        return (string) $image->encode($format);
    }

}
