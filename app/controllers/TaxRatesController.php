<?php

class TaxRatesController extends \BaseController {
    var $model = 'TaxRate';

    public function ajax_datagrid() {

        $CompanyID = User::get_companyID();
        $taxrates = TaxRate::select('Title', 'Amount','TaxRateId','TaxType','FlatStatus')->where("CompanyID", $CompanyID);
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
        );
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        unset($data['Status_name']);
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

}