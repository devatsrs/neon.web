<?php

class AccountApprovalController extends \BaseController {

    public function ajax_datagrid() {

        $CompanyID = User::get_companyID();
        $AccountApproval = AccountApproval::leftjoin('tblCountry','tblCountry.CountryId','=','tblAccountApproval.CountryId')
            ->select('Key','AccountType','DocumentFile','tblCountry.Country','BillingType','Required','Status','AccountApprovalID','Infomsg','tblCountry.CountryId')
            ->where("CompanyID", $CompanyID);

        return Datatables::of($AccountApproval)->make();
    }

    public function index()
    {
        $countries = Country::getCountryDropdownIDList();
        return View::make('accountapproval.index', compact('countries'));

    }

    /**
     * Store a newly created resource in storage.
     * POST /AccountApprovals
     *
     * @return Response
     */
    public function create()
    {
        $data = Input::all();
        $companyID = User::get_companyID();
        $data['CompanyID'] = $companyID;
        $data['Key'] = $data['Key'];
        unset($data['AccountApprovalID']);
        unset($data['Required_name']);
        unset($data['Status_name']);
        unset($data['DocumentFiles']);
        $rules = array(
            'CompanyID' => 'required',
            'Key' => 'required|unique:tblAccountApproval,Key,NULL,AccountApprovalID,CompanyID,'.$data['CompanyID'],
            'Required' => 'required',
        );
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }
        $today = date('Y-m-d');
        if (Input::hasFile('DocumentFiles')) {
            $upload_path = CompanyConfiguration::get('ACC_DOC_PATH');
            //$destinationPath = $upload_path.'/SampleUpload/'.Company::getName().'/';
            $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['ACCOUNT_APPROVAL_CHECKLIST_FORM']) ;
	    $destinationPath = $upload_path . '/' . $amazonPath;

            $excel = Input::file('DocumentFiles');
            // ->move($destinationPath);
            $ext = $excel->getClientOriginalExtension();
            if (in_array(strtolower($ext), array("doc", "docx", 'xls','xlsx',"pdf",'png','jpg','gif'))) {
                $filename = rename_upload_file($destinationPath,$excel->getClientOriginalName());
                $fullPath = $destinationPath .$filename;
                $excel->move($destinationPath,$filename);
                if(!AmazonS3::upload($destinationPath.$filename,$amazonPath)){
                    return Response::json(array("status" => "failed", "message" => "Failed to upload."));
                }
                $data['DocumentFile'] = $amazonPath . $filename;
            }else{
                $data['DocumentFile'] ='';
            }
        }
        $data['CreatedBy'] = User::get_user_full_name();
        $data['created_at'] =  $today;

        if (AccountApproval::create($data)) {
            return Response::json(array("status" => "success", "message" => "Document Successfully Created"));
        } else {
            return Response::json(array("status" => "failed", "message" => "Problem Creating Document."));
        }
    }

    /**
     * Display the specified resource.
     * GET /AccountApproval/{id}
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
     * GET /AccountApproval/{id}/edit
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
     * PUT /AccountApproval/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        if( $id > 0 ) {
            $data = Input::all();
            $AccountApproval = AccountApproval::findOrFail($id);
            $companyID = User::get_companyID();
            $data['CompanyID'] = $companyID;
            $data['Key'] = $data['Key'];
            unset($data['AccountApprovalID']);
            unset($data['Required_name']);
            unset($data['Status_name']);
            unset($data['DocumentFiles']);
            $rules = array(
                'CompanyID' => 'required',
                'Key' => 'required|unique:tblAccountApproval,Key,'.$id.',AccountApprovalID,CompanyID,'.$data['CompanyID'],
                'Required' => 'required',
            );
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }
            $today = date('Y-m-d');
            if (Input::hasFile('DocumentFiles')) {
                $upload_path = CompanyConfiguration::get('ACC_DOC_PATH');
                //$destinationPath = $upload_path.'/SampleUpload/'.Company::getName().'/';
                $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['ACCOUNT_APPROVAL_CHECKLIST_FORM']) ;
		$destinationPath = $upload_path . '/' . $amazonPath;
                $excel = Input::file('DocumentFiles');
                // ->move($destinationPath);
                $ext = $excel->getClientOriginalExtension();
                if (in_array(strtolower($ext), array("doc", "docx",'xls','xlsx', "pdf",'png','jpg','gif'))) {
                    $filename = rename_upload_file($destinationPath,$excel->getClientOriginalName());
                    $fullPath = $destinationPath .$filename;
                    $excel->move($destinationPath,$filename);
                    if(!AmazonS3::upload($destinationPath.$filename,$amazonPath)){
                        return Response::json(array("status" => "failed", "message" => "Failed to upload."));
                    }
                    $data['DocumentFile'] = $amazonPath . $filename;
                    AmazonS3::delete($AccountApproval->DocumentFile);

                }else{
                    $data['DocumentFile'] ='';
                }
            }
            $data['CreatedBy'] = User::get_user_full_name();
            $data['created_at'] =  $today;
            if ($AccountApproval->update($data)) {
                return Response::json(array("status" => "success", "message" => "Document Successfully Updated"));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Updating Document."));
            }
        }else {
            return Response::json(array("status" => "failed", "message" => "Problem Updating Document."));
        }
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /AccountApproval/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function delete($id)
    {
        if( intval($id) > 0){
            if(!AccountApproval::checkForeignKeyById($id)) {
                try {
                    $result = AccountApproval::find($id)->delete();
                    if ($result) {
                        return Response::json(array("status" => "success", "message" => "Document Successfully Deleted"));
                    } else {
                        return Response::json(array("status" => "failed", "message" => "Problem Deleting Document."));
                    }
                } catch (Exception $ex) {
                    return Response::json(array("status" => "failed", "message" => "Document is in Use, You cant delete this Document."));
                }
            }else{
                    return Response::json(array("status" => "failed", "message" => "Document is in Use, You cant delete this Document."));
                }
        }else{
            return Response::json(array("status" => "failed", "message" => "Document is in Use, You cant delete this Document."));
        }
    }

}
