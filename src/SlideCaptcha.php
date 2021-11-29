<?php

namespace Tncode;

class SlideCaptcha
{
    private $im = null;

    private $imFullBg = null;

    private $imBg = null;

    private $imSlide = null;

    private $imWidth = 240;

    private $imHeight = 150;

    private $markWidth = 50;

    private $markHeight = 50;

    private $logoWidth;

    private $logoHeight;

    private $x = 0;

    private $y = 0;

    /**
     * 容错象素 越大体验越好，越小破解难度越高
     * @var int
     */
    public $fault = 3;

    private $quality = 100;

    /**
     * logo 路径
     * @var string
     */
    private $logoPath = '';

    /**
     * 是否打logo
     * @var bool
     */
    private $isWriteLogo = false;

    /**
     * logo resource
     * @var null
     */
    private $imgLogo = null;

    /**
     * 背景图片路径
     * @var string
     */
    private $bgImgPath = '';

    /**
     * Allowed image types for the background images
     *
     * @var array
     */
    protected $allowedBackgroundImageTypes = array('image/png', 'image/jpeg', 'image/gif');

    public function __construct($imWidth = 240, $imHeight = 150, $markWidth = 50, $markHeight = 50)
    {
        $this->imWidth = $imWidth;
        $this->imHeight = $imHeight;
        $this->markWidth = $markWidth;
        $this->markHeight = $markHeight;

        error_reporting(0);
        if (!isset($_SESSION)) {
            session_start();
        }
    }

    /**
     * 设置背景图路径
     * @param $path
     * @return $this
     * @throws \Exception
     */
    public function setBgImgPath(string $path)
    {
        if (!is_dir($path)) {
            throw new \Exception('无效的背景图片路径');
        }
        $this->bgImgPath = $path;

        return $this;
    }

    /**
     * 设置logo路径
     * @param $path
     * @return $this
     * @throws \Exception
     */
    public function setLogoPath($path)
    {
        if (!is_file($path)) {
            throw new \Exception('invalid logo path');
        }
        $this->logoPath = $path;
        $this->isDrawLogo = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function setDrawLogo()
    {
        $this->isDrawLogo = true;

        return $this;
    }

    public function __destruct()
    {
        is_resource($this->im) && imagedestroy($this->im);
        is_resource($this->imFullBg) && imagedestroy($this->imFullBg);
        is_resource($this->imBg) && imagedestroy($this->imBg);
        is_resource($this->imSlide) && imagedestroy($this->imSlide);
        is_resource($this->imgLogo) && imagedestroy($this->imgLogo);
    }

    private function init()
    {
        $imgList = glob(!empty($this->bgImgPath) ? $this->bgImgPath . '/*' : dirname(__FILE__) . '/bg/*');
        $random = array_rand($imgList, 1);
        $file_bg = $imgList[$random];

        $this->imFullBg = imagecreatefrompng($file_bg);
        $this->imBg = imagecreatetruecolor($this->imWidth, $this->imHeight);
        imagecopy($this->imBg, $this->imFullBg, 0, 0, 0, 0, $this->imWidth, $this->imHeight);

        $this->imSlide = imagecreatetruecolor($this->markWidth, $this->imHeight);

        $_SESSION['tncode_r'] = $this->x = mt_rand(50, $this->imWidth - $this->markWidth - 1);
        $_SESSION['tncode_err'] = 0;

        $this->y = mt_rand(0, $this->imHeight - $this->markHeight - 1);
    }

    private function merge()
    {
        $this->im = imagecreatetruecolor($this->imWidth, $this->imHeight * 3);
        imagecopy($this->im, $this->imBg, 0, 0, 0, 0, $this->imWidth, $this->imHeight);
        imagecopy($this->im, $this->imSlide, 0, $this->imHeight, 0, 0, $this->markWidth, $this->imHeight);
        imagecopy($this->im, $this->imFullBg, 0, $this->imHeight * 2, 0, 0, $this->imWidth, $this->imHeight);
        imagecolortransparent($this->im, 0);//16777215
    }

    private function createBg()
    {
        $file_mark = dirname(__FILE__) . '/img/mark.png';
        $im = imagecreatefrompng($file_mark);
        // header('Content-Type: image/png');
        //imagealphablending( $im, true);
        imagecolortransparent($im, 0);//16777215
        imagecopy($this->imBg, $im, $this->x, $this->y, 0, 0, $this->markWidth, $this->markHeight);
        imagedestroy($im);
    }

    private function createSlide()
    {
        $file_mark = dirname(__FILE__) . '/img/mark2.png';
        $img_mark = imagecreatefrompng($file_mark);

        imagecopy($this->imSlide, $this->imFullBg, 0, $this->y, $this->x, $this->y, $this->markWidth, $this->markHeight);
        imagecopy($this->imSlide, $img_mark, 0, $this->y, 0, 0, $this->markWidth, $this->markHeight);
        imagecolortransparent($this->imSlide, 0);//16777215

        //header('Content-Type: image/png');
        //imagepng($this->imSlide);exit;
        imagedestroy($img_mark);
    }

    /**
     * 画logo
     * @throws \Exception
     */
    protected function drawLogo()
    {
        $logoPath = !empty($this->logoPath) ? $this->logoPath : __DIR__ . '/logo/ky-logo.png';
        $logoMimeType = $this->validateImg($logoPath);
        $this->imgLogo = $this->createImgFromType($logoPath, $logoMimeType);

        $this->logoWidth = imagesx($this->imgLogo);
        $this->logoHeight = imagesy($this->imgLogo);

        list($srcX, $srcY) = $this->getLogoRightBottomPos();
        list($midX, $midY) = $this->getLogoRightMidPos();

        imagecopymerge($this->im, $this->imgLogo, $srcX, $srcY, 0, 0, $this->logoWidth, $this->logoHeight, 20);
        imagecopymerge($this->im, $this->imgLogo, $midX, $midY, 0, 0, $this->logoWidth, $this->logoHeight, 20);

        return $this;
    }

    /**
     * 设置logo在右下角时的起始坐标
     * @return array
     */
    private function getLogoRightBottomPos()
    {
        $srcX = $this->imWidth - $this->logoWidth;
        $srcY = $this->imHeight * 3 - $this->logoHeight;

        return [$srcX, $srcY];
    }

    /**
     * 获取logo在右边中间位置的起始坐标
     * @return array
     */
    private function getLogoRightMidPos()
    {
        $srcX = $this->imWidth - $this->logoWidth;
        $srcY = $this->imHeight - $this->logoHeight;

        return [$srcX, $srcY];
    }

    public function build()
    {
        $this->init();
        $this->createSlide();
        $this->createBg();

        $this->merge();

        if ($this->isDrawLogo) {
            $this->drawLogo();
        }

        return $this;
    }

    public function imgout($nowebp = 0, $show = 0)
    {
        if (!$nowebp && function_exists('imagewebp')) {//优先webp格式，超高压缩率
            $type = 'webp';
            $this->quality = 90;//图片质量 0-100
        } else {
            $type = 'png';
            $this->quality = 7;//图片质量 0-9
        }
        if ($show) {
            header('Content-Type: image/' . $type);
        }
        $func = "image" . $type;
        $func($this->im, null, $this->quality);
    }

    /**
     * @param $nowebp
     * @param $show
     * @return false|string
     */
    private function get($nowebp, $show)
    {
        ob_start();
        $this->imgout($nowebp, $show);
        return ob_get_clean();
    }

    /**
     * @param int $nowebp
     * @param int $show
     * @return string
     */
    public function getInline($nowebp = 0, $show = 0)
    {
        return 'data:image/jpeg;base64,' . base64_encode($this->get($nowebp, $show));
    }


    public function make($nowebp = 0)
    {
        $this->build();
        $this->imgout($nowebp, 1);
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->x;
    }

    /**
     * Create background image from type
     * @param $backgroundImage
     * @param $imageType
     * @return false|resource
     * @throws \Exception
     */
    protected function createImgFromType($backgroundImage, $imageType)
    {
        switch ($imageType) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($backgroundImage);
                break;
            case 'image/png':
                $image = imagecreatefrompng($backgroundImage);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($backgroundImage);
                break;

            default:
                throw new \Exception('Not supported file type for background image!');
                break;
        }

        return $image;
    }

    /**
     * Validate the background image path. Return the image type if valid
     * @param $backgroundImage
     * @return mixed
     * @throws \Exception
     */
    protected function validateImg($backgroundImage)
    {
        // check if file exists
        if (!file_exists($backgroundImage)) {
            $backgroundImageExploded = explode('/', $backgroundImage);
            $imageFileName = count($backgroundImageExploded) > 1 ? $backgroundImageExploded[count($backgroundImageExploded) - 1] : $backgroundImage;

            throw new \Exception('Invalid background image: ' . $imageFileName);
        }

        // check image type
        $finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
        $imageType = finfo_file($finfo, $backgroundImage);
        finfo_close($finfo);

        if (!in_array($imageType, $this->allowedBackgroundImageTypes)) {
            throw new \Exception('Invalid background image type! Allowed types are: ' . join(', ', $this->allowedBackgroundImageTypes));
        }

        return $imageType;
    }

    /**
     * @param string $offset
     * @return bool
     */
    public function check($offset = '')
    {
        if (!$_SESSION['tncode_r']) {
            return false;
        }
        if (!$offset) {
            $offset = $_REQUEST['tn_r'];
        }
        $ret = abs($_SESSION['tncode_r'] - $offset) <= $this->fault;
        if ($ret) {
            unset($_SESSION['tncode_r']);
        } else {
            $_SESSION['tncode_err']++;
            if ($_SESSION['tncode_err'] > 10) {//错误10次必须刷新
                unset($_SESSION['tncode_r']);
            }
        }
        return $ret;
    }
}
