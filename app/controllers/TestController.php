<?php

class TestController extends BaseController
{

    function index()
    {
        return View::make('test.index');
    }
}