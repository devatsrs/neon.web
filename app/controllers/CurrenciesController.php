<?php

class CurrenciesController extends \BaseController {

    public function ajax_datagrid() {

        $CompanyID = User::get_companyID();
        $currencies = Currency::
                select('Code','Symbol', 'Description',  'CurrencyId');
                //->where("CompanyID", $CompanyID);

        return Datatables::of($currencies)->make();
    }

	public function index()
	{
        $PageRefresh=1;
        return View::make('currencies.index', compact('PageRefresh'));

    }

	/**
	 * Store a newly created resource in storage.
	 * POST /currencies
	 *
	 * @return Response
	 */
	public function create()
	{
        $data = Input::all();
        $companyID = User::get_companyID();
        $data['CompanyID'] = $companyID;
        unset($data['CurrencyID']);
        unset($data['PageRefresh']);
        $rules = array(
            'CompanyID' => 'required',
            'Code' => 'required|unique:tblCurrency,Code,NULL,CurrencyID,CompanyID,'.$data['CompanyID'],
            'Description' => 'required',
            'Symbol' => 'required',
        );
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if ($currency = Currency::create($data)) {
            Currency::clearCache();
            return Response::json(array("status" => "success", "message" => "Currency Successfully Created",'LastID'=>$currency->CurrencyId,'newcreated'=>$currency));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Currency."));
        }
	}

	/**
	 * Display the specified resource.
	 * GET /currencies/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 * GET /currencies/{id}/edit
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 * PUT /currencies/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        if( $id > 0 ) {
            $data = Input::all();
            $Currency = Currency::findOrFail($id);
            $companyID = User::get_companyID();
            $data['CompanyID'] = $companyID;

            $rules = array(
                'Code' => 'required|unique:tblCurrency,Code,'.$id.',CurrencyID,CompanyID,'.$data['CompanyID'],
                'CompanyID' => 'required',
                'Description' => 'required',
                'Symbol' => 'required',
            );
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            unset($data['CurrencyID']);
            unset($data['PageRefresh']);
            if ($Currency->update($data)) {
                Currency::clearCache();
                return Response::json(array("status" => "success", "message" => "Currency Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Currency."));
            }
        }else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Currency."));
        }
	}

	/**
	 * Remove the specified resource from storage.
	 * DELETE /currencies/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function delete($id)
	{
        if( intval($id) > 0){

            if(!Currency::checkForeignKeyById($id)){
                try{
                    $result = Currency::find($id)->delete();
                    Currency::clearCache();
                    if ($result) {
                        return Response::json(array("status" => "success", "message" => "Currency Successfully Deleted"));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Deleting Currency."));
                    }
                }catch (Exception $ex){
                    return Response::json(array("status" => "failed", "message" => "Currency is in Use, You cant delete this Currency."));
                }
            }else{
                return Response::json(array("status" => "failed", "message" => "Currency is in Use, You cant delete this Currency."));
            }
        }
	}

    public function exports($type){
        $CompanyID = User::get_companyID();
        $currencies = Currency::where(["CompanyID" => $CompanyID])->get(['Code','Symbol', 'Description']);
        $currencies = json_decode(json_encode($currencies),true);
        if($type=='csv'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Currency.csv';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_csv($currencies);
        }elseif($type=='xlsx'){
            $file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Currency.xls';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->download_excel($currencies);
        }

    }

}