<?php
use app\controllers\api\Codes;

class EmailTemplateApiController extends ApiController {


	public function getList()
	{
		$companyID 	=  User::get_companyID();
		$EmailTemplates = EmailTemplate::select(["TemplateID","LanguageID", "TemplateName", "Subject", "TemplateBody"])
			->where(["CompanyID" => $companyID])->get();
		return Response::json($EmailTemplates,Codes::$Code200[0]);
	}
}