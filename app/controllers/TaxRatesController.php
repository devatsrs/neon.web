<?php

class TaxRatesController extends \BaseController {
    var $model = 'TaxRate';

    public function ajax_datagrid() {
       $data = Input::all();
        $CompanyID = User::get_companyID();
        $taxrates = TaxRate::select('tblTaxRate.Title','tblTaxRate.Amount','tblTaxRate.Country as ISO2','tblTaxRate.DutchProvider','tblTaxRate.DutchFoundation','tblTaxRate.VATCode','tblTaxRate.TaxRateId','tblCountry.Country as Country')
        ->leftjoin('tblCountry','tblTaxRate.Country', '=' , 'tblCountry.ISO2')
        ->where("CompanyID", $CompanyID);
        if(isset($data['Title']) and !empty($data['Title']))
        {
             $taxrates = $taxrates->where('tblTaxRate.Title', 'like', '%'.$data['Title'].'%');
        }
        if(isset($data['TaxType'])  and !empty($data['TaxType']))
        {
             $taxrates = $taxrates->where('tblTaxRate.TaxType',  $data['TaxType']);
        }
        if(isset($data['Country']) and !empty($data['Country']))
        {
            //Log::info('country '.$data['Country']);
             $taxrates = $taxrates->where('tblTaxRate.Country', $data['Country']);
        }
        if(isset($data['FlatStatus']) and $data['FlatStatus']!=0)
        {
             $taxrates = $taxrates->where('tblTaxRate.FlatStatus', 1);
        }
        if(isset($data['ftDutchProvider']) and $data['ftDutchProvider']!=0)
        {
             $taxrates = $taxrates->where('tblTaxRate.DutchProvider', 1);
        }
        if(isset($data['ftDutchFoundation']) and $data['ftDutchFoundation']!=0)
        {
             $taxrates = $taxrates->where('tblTaxRate.DutchFoundation', 1);
        }
        if(!empty($data['VATCode'])) {
            $taxrates = $taxrates->where('tblTaxRate.VATCode', 'like', '%'.$data['VATCode'].'%');
        }
        Log::info($taxrates->toSql());
        return Datatables::of($taxrates)->make();
    }

    public function index()
    {
        
        return View::make('taxrates.index', compact(''));

    }

    /**
     * Store a newly created resource in storage.
     * POST /taxrates
     *
     * @return Response
     */
    public function create()
    {
        $data = Input::all();
        $companyID = User::get_companyID();
        $data['CompanyID'] = $companyID;
        unset($data['TaxRateID']);
        $rules = array(
            'CompanyID' => 'required',
            'Title' => 'required|unique:tblTaxRate,Title,NULL,TaxRateID,CompanyID,'.$data['CompanyID'],
            'Amount' => 'required|numeric',
            'TaxType' => 'required|numeric',
            'FlatStatus' => 'required|numeric',
            'Country' => 'required',
        ); 
        $attributeNames = array(
   'Amount' => 'VAT',     
);

        $validator = Validator::make($data, $rules);
        $validator->setAttributeNames($attributeNames);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        unset($data['Status_name']);
        unset($data['DutchProviderSt']);
        unset($data['DutchFoundationSt']);


        if ($taxrate = TaxRate::create($data)) {
            TaxRate::clearCache();
            return Response::json(array("status" => "success", "message" => "TaxRate Successfully Created",'LastID'=>$taxrate->TaxRateId));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating TaxRate."));
        }
    }

    /**
     * Display the specified resource.
     * GET /taxrates/{id}
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
     * GET /taxrates/{id}/edit
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
     * PUT /taxrates/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        if( $id > 0 ) {
            $data = Input::all();
            
            $TaxRate = TaxRate::findOrFail($id);
            $companyID = User::get_companyID();
            $data['CompanyID'] = $companyID;

            $rules = array(
                'Title' => 'required|unique:tblTaxRate,Title,'.$id.',TaxRateID,CompanyID,'.$data['CompanyID'],
                'CompanyID' => 'required',
                'Amount' => 'required|numeric',
                'TaxType' => 'required|numeric',
                'FlatStatus' => 'required|numeric',
            );
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
             
            unset($data['TaxRateID']);
            unset($data['Status_name']);
            unset($data['DutchProviderSt']);
        unset($data['DutchFoundationSt']);

            if ($TaxRate->update($data)) {
                TaxRate::clearCache();
                return Response::json(array("status" => "success", "message" => "TaxRate Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating TaxRate."));
            }
        }else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating TaxRate."));
        }
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /taxrates/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function delete($id)
    {
        if( intval($id) > 0){

            if(!TaxRate::checkForeignKeyById($id)){
                try{
                    $result = TaxRate::find($id)->delete();
                    TaxRate::clearCache();
                    if ($result) {
                        return Response::json(array("status" => "success", "message" => "TaxRate Successfully Deleted"));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Deleting TaxRate."));
                    }
                }catch (Exception $ex){
                    return Response::json(array("status" => "failed", "message" => "TaxRate is in Use, You cant delete this TaxRate."));
                }
            }else{
                return Response::json(array("status" => "failed", "message" => "TaxRate is in Use, You cant delete this TaxRate."));
            }
        }
    }

    public function export(){
        
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $taxrates = TaxRate::select(DB::Raw("tblTaxRate.Title,tblTaxRate.Amount as 'VAT %',tblCountry.Country,tblTaxRate.DutchProvider as 'Dutch Provider',tblTaxRate.DutchFoundation as 'Dutch Foundation',tblTaxRate.VATCode"))
        ->leftjoin('tblCountry','tblCountry.ISO2','=','tblTaxRate.Country')
        ->where("CompanyID", $CompanyID);


        if(isset($data['Title']) and !empty($data['Title']))
        {
             $taxrates = $taxrates->where('tblTaxRate.Title', 'like', '%'.$data['Title'].'%');
        }
        if(isset($data['TaxType'])  and !empty($data['TaxType']))
        {
             $taxrates = $taxrates->where('tblTaxRate.TaxType',  $data['TaxType']);
        }
        if(isset($data['Country']) and !empty($data['Country']))
        {
            //Log::info('country '.$data['Country']);
             $taxrates = $taxrates->where('tblTaxRate.Country', $data['Country']);
        }
        if(isset($data['FlatStatus']) and $data['FlatStatus']!=0)
        {
             $taxrates = $taxrates->where('tblTaxRate.FlatStatus', 1);
        }
        if(isset($data['ftDutchProvider']) and $data['ftDutchProvider']!=0)
        {
             $taxrates = $taxrates->where('tblTaxRate.DutchProvider', 1);
        }
        if(isset($data['ftDutchFoundation']) and $data['ftDutchFoundation']!=0)
        {
             $taxrates = $taxrates->where('tblTaxRate.DutchFoundation', 1);
        }
        if(!empty($data['VATCode'])) {
            $taxrates = $taxrates->where('tblTaxRate.VATCode', 'like', '%'.$data['VATCode'].'%');
        }
        $taxrates = $taxrates->get();
		$ExcelFile = json_decode(json_encode($taxrates),true);
		if(isset($data['Export']) && $data['Export'] == 1){
			$file_path = CompanyConfiguration::get('UPLOAD_PATH') .'/Taxrates.xls';
			$NeonExcel = new NeonExcelIO($file_path);
			$NeonExcel->download_excel($ExcelFile);
		}
    }

}