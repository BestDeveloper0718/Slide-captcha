<img src="https://tva1.sinaimg.cn/large/007S8ZIlgy1ghm3zqnkfhj30e60e6jxb.jpg" style="zoom:30%;" />

### Installation

Use composer:

```php
composer require yuanlj-tea/slide-captcha
```

### Usage

You can create a slide captcha with make func:

```php
use Tncode\SlideCaptcha;

$captcha = new SlideCaptcha();
$captcha->make();
```

You can output captcha directly:

```php
use Tncode\SlideCaptcha;

$captcha = new SlideCaptcha();
$captcha->build();
$captcha->imgout(0,1);
```

Or inline it directly in the HTML page:

```php
use Tncode\SlideCaptcha;

$captcha = new SlideCaptcha();
$captcha->build();
$linlie = $captcha->getInline();
echo "<img src='".$linlie."' />";
```

You'll be able to get the code and compare it with a user input :

```php
use Tncode\SlideCaptcha;

$captcha = new SlideCaptcha();
$captcha->build();
$captcha->getCode();
```

### Used in Laravel

Register ServiceProvider and Facade with config/app.php:

```php
'providers' => [
    // ...
    \Tncode\SlideCaptchaServiceProvider::class,
],
'aliases' => [
    // ...
    'SlideCode' => \Tncode\SlideCaptchaFacade::class,
],
```

Get a service instance：

Method parameter injection:

```php
use Tncode\SlideCaptcha;

public function getImage(Request $request, SlideCaptcha $captcha)
{
  
}
```

Obtained by the facade class:

```php
use SlideCode;

public function getImageV1()
{
     SlideCode::build();
     $imgData = SlideCode::getInline();
     $code = SlideCode::getCode();
}
```

By service name：

```php
public function getImageV2()
{
     $captcha = app('slide_captcha');
     $captcha->build();

     $imgData = $captcha->getInline();
     $code = $captcha->getCode();
}
```

Check demo:

```php
public function getCaptchaDemo(Request $request, SlideCaptcha $captcha)
    {
        $key = 'slide-captcha-' . \Str::random(32);

        $captcha->build();

        \Cache::put($key, ['code' => $captcha->getCode()], 600);

        $result = [
            'captcha_key' => $key,
            'expired_at' => time() + 600,
            'captcha_image_content' => $captcha->getInline()
        ];
        return $this->responseData($result);
    }

    public function checkDemo(Request $request)
    {
        $key = $request->get('captcha_key', '');
        $code = $request->get('captcha_code', '');

        if (!\Cache::has($key)) {
            return $this->responseData('无效的key', 400);
        }

        $ret = abs(\Cache::get($key)['code'] - $code) <= 3;
        if ($ret) {
            return $this->responseData('验证成功');
        } else {
            $errKey = $key . '_error';
            $errCount = $request->session()->has($errKey) ? $request->session()->get($errKey) : 1;
            $request->session()->put($errKey, $errCount + 1);

            if ($errCount > 8) {
                \Cache::forget($key);
                $request->session()->forget($errKey);
                return $this->responseData('失败次数过多，请重新获取验证码', 400);
            }
            return $this->responseData('验证失败', 400);
        }
    }
```

Web demo:

```php
see /path/vendor/yuanlj-tea/slide-captcha/src/index.html
```



## License

MIT