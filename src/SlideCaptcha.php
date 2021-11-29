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

    private $bgNum = 7;

    private $x = 0;

    private $y = 0;

    /**
     * 容错象素 越大体验越好，越小破解难度越高
     * @var int
     */
    public $fault = 3;

    private $quality = 100;

    /**
     * 背景图片路径
     * @var string
     */
    private $bgImgPath = '';

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
     * 设置背景图片数量
     * @param int $num
     * @return $this
     * @throws \Exception
     */
    public function setBgNum(int $num)
    {
        if ($num < 1) {
            throw new \Exception('无效参数');
        }
        $this->bgNum = $num;

        return $this;
    }

    public function build()
    {
        $this->init();
        $this->createSlide();
        $this->createBg();
        $this->merge();

        return $this;
    }

    public function make($nowebp = 0)
    {
        $this->init();
        $this->createSlide();
        $this->createBg();
        $this->merge();
        $this->imgout($nowebp, 1);
        $this->destroy();
    }

    private function init()
    {
        $bg = mt_rand(1, $this->bgNum);
        $file_bg = !empty($this->bgImgPath) ? $this->bgImgPath . '/' . $bg . '.png' : dirname(__FILE__) . '/bg/' . $bg . '.png';

        $this->imFullBg = imagecreatefrompng($file_bg);
        $this->imBg = imagecreatetruecolor($this->imWidth, $this->imHeight);
        imagecopy($this->imBg, $this->imFullBg, 0, 0, 0, 0, $this->imWidth, $this->imHeight);

        $this->imSlide = imagecreatetruecolor($this->markWidth, $this->imHeight);

        $_SESSION['tncode_r'] = $this->x = mt_rand(50, $this->imWidth - $this->markWidth - 1);
        $_SESSION['tncode_err'] = 0;

        $this->y = mt_rand(0, $this->imHeight - $this->markHeight - 1);
    }

    private function destroy()
    {
        imagedestroy($this->im);
        imagedestroy($this->imFullBg);
        imagedestroy($this->imBg);
        imagedestroy($this->imSlide);
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
     * @return int
     */
    public function getCode()
    {
        return $this->x;
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
