<?php
use app\controllers\api\Codes;

class LanguageApiController extends ApiController {


	public function getList()
	{

		$Languages = Language::select(["LanguageID","ISOCode","Language"])
			->get();
		return Response::json($Languages,Codes::$Code200[0]);
	}
}