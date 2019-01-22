<?php

class LanguageApiController extends ApiController {


	public function getList()
	{

		$Languages = Language::select(["LanguageID","ISOCode","Language", "flag", "is_rtl"])
			->get();
		return Response::json(["status"=>"success", "data"=>$Languages]);
	}
}