<?php
use app\controllers\api\Codes;

class LanguageApiController extends ApiController {


	public function getList()
	{

		$Languages = Language::select(["LanguageID","ISOCode","Language"])
			->get();
		return Response::json(["data"=>$Languages],Codes::$Code200[0]);
	}
}