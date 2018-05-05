<?php

class RateGeneratorRuleController extends \BaseController {


    public function add($id) {

        if ($id > 0) {

            return View::make('rategenerators.rules.add', compact('id'));
        }
    }
    public function edit($id, $RateRuleID) {
        if ($id > 0 && $RateRuleID > 0) {

            //Code
            $companyID = User::get_companyID();

            $rategenerator_rule = RateRule::where(["RateRuleId" => $RateRuleID])->get(["Code","Description"])->first()->toArray();
            $Code = $rategenerator_rule["Code"];
            $Description = $rategenerator_rule["Description"];

            //source
            $rategenerator_sources = RateRuleSource::where(["RateRuleID" => $RateRuleID])->lists('AccountID', 'AccountId');
            $rategenerator = RateGenerator::find($id);

            $vendors = Account::select([
                "AccountName",
                "AccountID",
                "IsVendor"
            ])->where(["Status" => 1, "IsVendor" => 1, "AccountType" => 1, "CompanyID" => $companyID /*'CodeDeckId'=>$rategenerator->CodeDeckId*/])->get();



            //margin
            $rategenerator_margins = RateRuleMargin::where([
                "RateRuleID" => $RateRuleID
            ])->get();

            return View::make('rategenerators.rules.edit', compact('id', 'RateRuleID', 'Code', 'Description', 'rategenerator_sources', 'vendors', 'rategenerator' ,    'rategenerator_margins'));
        }
    }

    // margin data grid
    public function ajax_margin_datagrid() {
        $data = Input::all();
        $id = $data['id'];
        $RateRuleID = $data['RateRuleID'];
        if ($id > 0 && $RateRuleID > 0) {
            $companyID = User::get_companyID();
            $rategenerator_margins = RateRuleMargin::where([
                "RateRuleID" => $RateRuleID
            ])->select(array(
                'MinRate',
                'MaxRate',
                'AddMargin',
                'FixedValue',
                'RateRuleMarginId',
            ))->orderBy('MinRate', 'ASC');
            return Datatables::of($rategenerator_margins)->make();
        }

    }


    // CreateCode
    public function store_code($id) {
        if ($id > 0) {
            $last_max_order =  RateRule::where(["RateGeneratorId" => $id])->max('Order');

            $data = Input::all();
            $data['Order'] = $last_max_order+1;
           // print_R($data);exit;
            $data ['CreatedBy'] = User::get_user_full_name();
            $data ['RateGeneratorId'] = $id;
            $rules = array(
                'Code' => 'required_without_all:Description|unique:tblRateRule,Code,NULL,RateGeneratorId,RateGeneratorId,'.$data['RateGeneratorId'],
                'Description' => 'required_without_all:Code|unique:tblRateRule,Code,NULL,RateGeneratorId,RateGeneratorId,'.$data['RateGeneratorId'],
                'RateGeneratorId' => 'required',
                'CreatedBy' => 'required'
            );
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            if ($rule_id = RateRule::insertGetId($data)) {
                return Response::json(array("status" => "success", "message" => "RateGenerator Rule Successfully Created" , "redirect" => \Illuminate\Support\Facades\URL::to('/rategenerators/' . $id .'/rule/'.$rule_id . '/edit') ));
            } else {
                 return Response::json(array("status" => "failed", "message" => "Problem Creating RateGenerator Rule."));
            }
        }
    }

    // Update Code
    public function update_rule($id, $RateRuleID) {
        if ($id > 0 && $RateRuleID > 0) {
            // $companyID = User::get_companyID();
            $rategenerator_rules = RateRule::find($RateRuleID); // RateRule::where([ "RateRuleID" => $RateRuleID])->get();

            $data = Input::all();

            $data ['ModifiedBy'] = User::get_user_full_name();
            $rules = array(
                'Code' => 'required_without_all:Description|unique:tblRateRule,Code,' . $RateRuleID . ',RateRuleID,RateGeneratorId,'.$id,
                'Description' => 'required_without_all:Code|unique:tblRateRule,Description,' . $RateRuleID . ',RateRuleID,RateGeneratorId,'.$id,
                'ModifiedBy' => 'required'
            );

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            if ($rategenerator_rules->update($data)) {
                return Response::json(array(
                    "status" => "success",
                    "message" => "RateGenerator Rule Destination Successfully Updated"
                ));
            } else {
                return Response::json(array(
                    "status" => "failed",
                    "message" => "Problem Updating RateGenerator Rule Destination."
                ));
            }
        }
    }

    // Update Source
    public function update_rule_source($id, $RateRuleId) {
        if ($id > 0 && $RateRuleId > 0) {

            $data = Input::all();

            // Delete all vendors first
            RateRuleSource::where(["RateRuleID" => $RateRuleId])->delete();
            $user_full_name = User::get_user_full_name();

            $InsertData = array();
            $i = 0;
            $j = 0; // contains 200 of records in each sql
            $max_records_per_insert = 200;
            // Update Sources
            if(isset($data["Sources"])){
                RateGenerator::find($id)->update(["Sources"=>$data["Sources"]]);
            }

            // Loop Selected Vendor IDs and insert.
            if (count($data ['AccountIds']) > 0) {

                foreach ((array) $data ['AccountIds'] as $AccountId) {

                    if ((int) $AccountId > 0) {

                        if ($i++ == $max_records_per_insert) {
                            $i = 1;
                            $j++;
                        }
                        $ModifiedBy = $user_full_name;
                        $CreatedBy = $user_full_name;
                        $InsertData [$j] [] = compact('AccountId', 'RateRuleId', 'ModifiedBy', 'CreatedBy');
                    }
                }
                try {
                    DB::beginTransaction();
                    foreach ($InsertData as $key => $row) {
                        RateRuleSource::insert($row);
                    }
                    DB::commit();
                    return Response::json(array(
                        "status" => "success",
                        "message" => "RateGenerator Rule Source Successfully Updated"
                    ));
                } catch (Exception $ex) {
                    DB::rollback();
                    return Response::json(array(
                        "status" => "failed",
                        " Exception: " . $ex->getMessage()
                    ));
                }
            } else {
                return Response::json(array(
                    "status" => "success",
                    "message" => "RateGenerator Rule Source Removed Successfully Updated"
                ));
            }
        }
    }



    // Update Margin
    public function update_rule_margin($id, $RateRuleId) {
        if ($id > 0 && $RateRuleId > 0) {
            $data = Input::all();

            $RateRuleMarginId = $data ['RateRuleMarginId'];
            $rategenerator_rule_margin = RateRuleMargin::find($RateRuleMarginId);

            $data ['ModifiedBy'] = User::get_user_full_name();
            $data ['RateRuleId'] = $RateRuleId;
            $data ['MinRate'] = doubleval($data ['MinRate']);
            $data ['MaxRate'] = doubleval($data ['MaxRate']);
            $data ['FixedValue'] = doubleval($data ['FixedValue']);
            $rules = array(
                'MinRate' => 'numeric|unique:tblRateRuleMargin,MinRate,'.$RateRuleMarginId.',RateRuleMarginId,RateRuleId,'.$RateRuleId,
                'MaxRate' => 'numeric|unique:tblRateRuleMargin,MaxRate,'.$RateRuleMarginId.',RateRuleMarginId,RateRuleId,'.$RateRuleId,
                'AddMargin' => 'required_without:FixedValue',
                'FixedValue' => 'required_without:AddMargin',
                'RateRuleId' => 'required',
                'RateRuleMarginId' => 'required',
                'ModifiedBy' => 'required'
            );

            if(!empty($data['AddMargin']) && !empty($data['FixedValue'])) {
                return Response::json(array(
                    "status" => "failed",
                    "message" => "Add Margin or Fixed Rate, Both are not allowed"
                ));
            }

            $minRateCount = RateRuleMargin::whereBetween('MinRate', array($data ['MinRate'], $data ['MaxRate']))
                ->where(['RateRuleId'=>$RateRuleId])
                ->where('RateRuleMarginId','!=',$RateRuleMarginId)
                ->count();
            $maxRateCount = RateRuleMargin::whereBetween('MaxRate', array($data ['MinRate'], $data ['MaxRate']))
                ->where(['RateRuleId'=>$RateRuleId])
                ->where('RateRuleMarginId','!=',$RateRuleMarginId)
                ->count();

            $minRate = RateRuleMargin::where('MaxRate','>=',$data['MinRate'])->where('MinRate','<=',$data['MinRate'])
                ->where(['RateRuleId'=>$RateRuleId])
                ->where('RateRuleMarginId','!=',$RateRuleMarginId)
                ->count();

            $maxRate = $data ['MinRate']>$data ['MaxRate']?1:0;

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            if($minRateCount>0 || $maxRateCount>0 || $minRate>0){
                return Response::json(array(
                    "status" => "failed",
                    "message" => "RateGenerator Rule Margin is overlapping."
                ));
            }
            if($maxRate>0){
                return Response::json(array(
                    "status" => "failed",
                    "message" => "MaxRate should greater then MinRate."
                ));
            }

            if ($rategenerator_rule_margin->update($data)) {
                return Response::json(array(
                    "status" => "success",
                    "message" => "RateGenerator Rule Margin Successfully Updated"
                ));
            } else {
                return Response::json(array(
                    "status" => "failed",
                    "message" => "Problem Updating RateGenerator Rule Margin."
                ));
            }
        }
    }

    // Add Margin
    public function add_rule_margin($id, $RateRuleId) {
        if ($id > 0 && $RateRuleId > 0) {
            $data = Input::all();

            $data ['CreatedBy'] = User::get_user_full_name();
            $data ['RateRuleId'] = $RateRuleId;
            $data ['MinRate'] = doubleval($data ['MinRate']);
            $data ['MaxRate'] = doubleval($data ['MaxRate']);
            $data ['FixedValue'] = doubleval($data ['FixedValue']);
            $rules = array(
                'MinRate' => 'numeric|unique:tblRateRuleMargin,MinRate,NULL,RateRuleMarginId,RateRuleId,'.$RateRuleId,
                'MaxRate' => 'numeric|unique:tblRateRuleMargin,MaxRate,NULL,RateRuleMarginId,RateRuleId,'.$RateRuleId,
                'AddMargin' => 'required_without:FixedValue',
                'FixedValue' => 'required_without:AddMargin',
                'RateRuleId' => 'required',
                'CreatedBy' => 'required'
            );

            if(!empty($data['AddMargin']) && !empty($data['FixedValue'])) {
                return Response::json(array(
                    "status" => "failed",
                    "message" => "Add Margin or Fixed Rate, Both are not allowed"
                ));
            }


            $minRateCount = RateRuleMargin::whereBetween('MinRate', array(doubleval($data['MinRate']), doubleval($data['MaxRate'])))
                ->where(['RateRuleId'=>$RateRuleId])
                ->count();
            $maxRateCount = RateRuleMargin::whereBetween('MaxRate', array(doubleval($data['MinRate']), doubleval($data['MaxRate'])))
                ->where(['RateRuleId'=>$RateRuleId])
                ->count();

            $minRate = RateRuleMargin::where('MaxRate','>=',$data['MinRate'])->where('MinRate','<=',$data['MinRate'])
                ->where(['RateRuleId'=>$RateRuleId])
                ->count();

            $maxRate = $data ['MinRate']>$data ['MaxRate']?1:0;

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            if($minRateCount>0 || $maxRateCount>0 || $minRate>0){
                return Response::json(array(
                    "status" => "failed",
                    "message" => "RateGenerator Rule Margin is overlapping."
                ));
            }
            if($maxRate>0){
                return Response::json(array(
                    "status" => "failed",
                    "message" => "MaxRate should greater then MinRate."
                ));
            }

            if (RateRuleMargin::insert($data)) {
                return Response::json(array(
                    "status" => "success",
                    "message" => "RateGenerator Rule Margin Successfully Inserted"
                ));
            } else {
                return Response::json(array(
                    "status" => "failed",
                    "message" => "Problem Inserting RateGenerator Rule Margin."
                ));
            }
        }
    }

    // Delete Margin
    public function delete_rule_margin($RateRuleId, $RateRuleMarginId) {
        if ($RateRuleMarginId > 0 && $RateRuleId > 0) {

            if (RateRuleMargin::where([
                "RateRuleMarginId" => $RateRuleMarginId,
                "RateRuleId" => $RateRuleId
            ])->delete()) {
                return Response::json(array(
                    "status" => "success",
                    "message" => "RateGenerator Rule Margin Successfully Deleted"
                ));
            } else {
                return Response::json(array(
                    "status" => "failed",
                    "message" => "Problem Deleting RateGenerator Rule Margin."
                ));
            }
        }
    }

    // Delet eCode
    public function delete_rule($id, $RateRuleID) {
        if ($id > 0 && $RateRuleID > 0) {
            if (RateRule::find($RateRuleID)->delete()) {
                // return Redirect::back()->with('success_message', "RateGenerator Rule Successfully Deleted");
                return json_encode([
                    "status" => "success",
                    "message" => "RateGenerator Rule Successfully Deleted"
                ]);
            } else {
                return json_encode([
                    "status" => "failed",
                    "message" => "Problem Deleting RateGenerator Rule"
                ]);
                // return Redirect::back()->with('error_message', "Problem Deleting RateGenerator Rule.");
            }
        }
    }

    //clone rule
    public function clone_rule($id, $RateRuleID) {

        if ($id > 0 && $RateRuleID > 0) {

            $CreatedBy = User::get_user_full_name();

            $query = "call prc_CloneRateRuleInRateGenerator (?,?)";

            $NewRateRuleObj = DB::select($query,array($RateRuleID,$CreatedBy));

            if(isset($NewRateRuleObj[0]->RateRuleID)  ) {
                $RateRuleID = $NewRateRuleObj[0]->RateRuleID;

                return json_encode([
                    "status" => "success",
                    "message" => "RateGenerator Rule Successfully Cloned",
                    "RateRuleID" => $RateRuleID
                ]);
            }

        }

        return json_encode([
            "status" => "failed",
            "message" => "Problem Cloning RateGenerator Rule"
        ]);


    }
}