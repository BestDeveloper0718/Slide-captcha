<?php

namespace Tncode;

class SlideCaptchaFacade extends \Illuminate\Support\Facades\Facade
{
    /**
     * 获取Facade注册名称
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return SlideCaptcha::class;
    }
}