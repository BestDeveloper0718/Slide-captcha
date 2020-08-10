<?php

namespace Tncode;

class SlideCodeFacade extends \Illuminate\Support\Facades\Facade
{
    /**
     * 获取Facade注册名称
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return SlideCode::class;
    }
}