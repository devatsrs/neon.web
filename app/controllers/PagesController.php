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
		$data = array();
		//https://codedesk.atlassian.net/browse/NEON-1591
		//Audit Trails of user activity
		$UserActilead = UserActivity::UserActivitySaved($data,'View','About');
		return View::make('pages.about', compact(''));
	}

}