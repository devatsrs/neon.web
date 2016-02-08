<?php

class PagesController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 * GET /page
	 *
	 * @return Response
	 */
	public function about()
	{
		return View::make('pages.about', compact(''));
	}

}