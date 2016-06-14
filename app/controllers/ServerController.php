<?php

class ServerController extends \BaseController {


    var $model = 'User';
    public function __construct() {
    }
    /**
     * Display a listing of the resource.
     * 
     * @return Response
     */
    public function index() {
		return View::make('server.index', compact(''));
    }
}