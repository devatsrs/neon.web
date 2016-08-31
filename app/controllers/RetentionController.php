<?php

class RetentionController extends \BaseController {

	public function index()
	{
        $DataRetenion1 = CompanySetting::getKeyVal('DataRetention');
        if(!empty($DataRetenion1)){
            $DataRetenion =  json_decode($DataRetenion1);
        }
        $FileRetenion1 = CompanySetting::getKeyVal('FileRetention');
        if(!empty($FileRetenion1)){
            $FileRetenion =  json_decode($FileRetenion1);
        }
        return View::make('Retention.index', compact('DataRetenion','FileRetenion'));

    }

	/**
	 * Store a newly created Retention in company setting.
	 * POST /Retention
	 *
	 * @return Response
	 */
	public function create()
	{
        $data = Input::all();
        $RetenionData = json_encode($data['TableData']);

        if(empty($data['FileData'])){
            $RetenionFileData = '';
        }else{
            $RetenionFileData = json_encode($data['FileData']);
        }


        CompanySetting::setKeyVal('DataRetention',$RetenionData);
        CompanySetting::setKeyVal('FileRetention',$RetenionFileData);

        return Response::json(array("status" => "success", "message" => "Retention Successfully Saved"));
	}

}