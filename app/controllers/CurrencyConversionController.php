<?php

class CurrencyConversionController extends \BaseController {

    public function ajax_datagrid() {

        $CompanyID = User::get_companyID();
        $currencies = CurrencyConversion::join('tblCurrency as frm', 'frm.CurrencyId', '=', 'tblCurrencyConversion.CurrencyFromID')
                ->join('tblCurrency as toc', 'toc.CurrencyId', '=', 'tblCurrencyConversion.CurrencyToID')
                ->select('Name', 'frm.Code','toc.Code as Code2','Value','InverseValue','ConversionID','CurrencyFromID','CurrencyToID')
                ->where("tblCurrencyConversion.CompanyID", $CompanyID);

        return Datatables::of($currencies)->make();
    }

    public function ajax_datagrid_history() {

        $CompanyID = User::get_companyID();
        $currencies = CurrencyConversionLog::join('tblCurrency as frm', 'frm.CurrencyId', '=', 'tblCurrencyConversionLog.CurrencyID')
            ->select('frm.Code As Code','Value','EffectiveDate')
            ->where("tblCurrencyConversionLog.CompanyID", $CompanyID);

        return Datatables::of($currencies)->make();
    }

	public function index()
	{
        $CompanyID = User::get_companyID();
        $CurrencyId = Company::getCompanyField($CompanyID,'CurrencyId');
        $code = Currency::getCurrency($CurrencyId);
        //$currencylist = Currency::getCurrencyDropdownIDList();
        $currencylists = Currency::select('Code','CurrencyID','Description')
                        //->where("CompanyId",$CompanyID)
                        ->orderBy('Code','Asc')->get();
        $currencyarray=array();
        $defaultcurrency=array();
        $restcurrency=array();
        foreach($currencylists as $currencylist)
        {
			$Amount = CurrencyConversion::where(array("CurrencyID"=>$currencylist['CurrencyID'],"CompanyID"=>$CompanyID))->pluck("Value");
			$currencylist['Amount'] = $Amount;
            if($currencylist['CurrencyID']==$CurrencyId){
                $defaultcurrency[]=$currencylist;
            }else{
                $restcurrency[]=$currencylist;
            }
        }
        $currencyarray=array_merge($defaultcurrency,$restcurrency);
        return View::make('currencyconversion.index', compact('currencyarray','CurrencyId','code'));

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
        if(empty($data['ExchangeRate'])){
            return Response::json(array("status" => "failed", "message" => "No Exchange Rate available."));
        }
        $companyID = User::get_companyID();
        $EffectiveDate = date('Y-m-d H:i:s');
        $ExchangeRates = $data['ExchangeRate'];
        $CurrencyCon = array();
        $CurrencyConversionLog = array();
        $success = 'success';
        foreach($ExchangeRates as $ExchangeRate)
        {
            //check insert currency value
            if(!empty($ExchangeRate['Value']))
            {
                $CurrencyCon['CurrencyID'] = $ExchangeRate['CurrencyID'];
                $CurrencyCon['Value'] = $ExchangeRate['Value'];
                $CurrencyCon['EffectiveDate'] = $EffectiveDate;
                $CurrencyCon['CompanyID'] = $companyID;

                $CurrencyConversion = CurrencyConversion::select('Value','EffectiveDate')->where(array('CompanyId' => $companyID, 'CurrencyID' => $ExchangeRate['CurrencyID']))->first();
                if(count($CurrencyConversion)>0){
                    $cval = $CurrencyConversion->Value;
                    // check Currency updated or not
                    if($cval != $ExchangeRate['Value']){
                        $CurrencyConversionLog['EffectiveDate']=$CurrencyConversion->EffectiveDate;
                        $CurrencyConversionLog['Value']=$CurrencyConversion->Value;
                        $CurrencyConversionLog['CurrencyID']=$CurrencyCon['CurrencyID'];
                        $CurrencyConversionLog['CompanyID']=$companyID;
                        // insert history of Currency
                        if ($currency = CurrencyConversionLog::create($CurrencyConversionLog)) {
                            //update Currency value
                            if($currencyCon = CurrencyConversion::where(array('CompanyId' => $companyID, 'CurrencyID' => $CurrencyCon['CurrencyID']))->update($CurrencyCon)){
                                $success = 'success';
                            }
                            else{
                                $success = 'failed';
                            }
                        }
                    }
                }else{
                    //new entry
                    if ($currency = CurrencyConversion::create($CurrencyCon)) {
                        $success = 'success';
                    } else {
                        $success = 'failed';
                    }
                }
            }else{
                return Response::json(array("status" => "failed", "message" => "Exchange Rate should not empty."));
            }
        }
        if($success=='success'){
            return Response::json(array("status" => "success", "message" => "Exchange Rate Successfully Created"));
        }else{
            return Response::json(array("status" => "failed", "message" => "Problem Creating Exchange Rate."));
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
            $CurrencyConversion = CurrencyConversion::findOrFail($id);
            $companyID = User::get_companyID();
            $data['CompanyID'] = $companyID;
            $rules = CurrencyConversion::$rules;
            $rules['CurrencyFromID'] ='required|unique:tblCurrencyConversion,CurrencyFromID,'.$id.',ConversionID,CompanyID,'.$data['CompanyID'].',CurrencyToID,'.$data['CurrencyToID'];
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            $currencyfromcount = CurrencyConversion::where(array('CompanyID'=>$companyID,'CurrencyFromID'=>$data['CurrencyFromID'],'CurrencyToID'=>$data['CurrencyToID']))->where('ConversionID','!=',$id)->count();
            $currencytocount = CurrencyConversion::where(array('CompanyID'=>$companyID,'CurrencyToID'=>$data['CurrencyFromID'],'CurrencyFromID'=>$data['CurrencyToID']))->where('ConversionID','!=',$id)->count();

            if( $currencyfromcount > 0 || $currencytocount > 0 ){
                return Response::json(array("status" => "failed", "message" => "Exchange Rate Already defined"));
            }

            if($data['CurrencyFromID'] == $data['CurrencyToID']){
                return Response::json(array("status" => "failed", "message" => "Please select a Different Currency in From and To."));
            }
            if ($CurrencyConversion->update($data)) {
                return Response::json(array("status" => "success", "message" => "Exchange Rate Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Exchange Rate."));
            }
        }else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Exchange Rate."));
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

            if(!CurrencyConversion::checkForeignKeyById($id)){
                try{
                    if (CurrencyConversion::find($id)->delete()) {
                        return Response::json(array("status" => "success", "message" => "Exchange Rate Successfully Deleted"));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Deleting Exchange Rate."));
                    }
                }catch (Exception $ex){
                    return Response::json(array("status" => "failed", "message" => "Exchange Rate is in Use, You cant delete this Currency."));
                }
            }else{
                return Response::json(array("status" => "failed", "message" => "Currency  Conversion is in Use, You cant delete this Currency."));
            }
        }
	}

}