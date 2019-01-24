<?php

class LanguageApiController extends ApiController {


	public function getList()
	{

		$Languages = Language::select(["LanguageID","ISOCode","Language"])
			->get();
		return Response::json(["status"=>"200", "data"=>$Languages]);
	}
}