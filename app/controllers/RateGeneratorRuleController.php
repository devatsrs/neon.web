<?php

class RateGeneratorRuleController extends \BaseController {


    public function add($id) {


        if ($id > 0) {

            $rateGenerator = RateGenerator::findOrFail($id);
            $rategenerator_rules = RateRule::with('RateRuleMargin', 'RateRuleSource')->where([
                "RateGeneratorId" => $id
            ]) ->orderBy("Order", "asc")->get();

            $Timezones = Timezones::getTimezonesIDList();
            $companyID = User::get_companyID();
            $vendors = Account::select([
                "AccountName",
                "AccountID",
                "IsVendor"
            ])->where(["Status" => 1, "IsVendor" => 1, "AccountType" => 1, "CompanyID" => $companyID /*'CodeDeckId'=>$rateGenerator->CodeDeckId*/])->get();
            $type = Rate::where("CompanyID",User::get_companyID())->whereRaw('Type IS NOT NULL')->lists('Type','Type');
            $countryForRule = Country::lists('Country','CountryID');

            // $country = ServiceTemplate::Join('tblCountry', function($join) {
            //     $join->on('tblServiceTemplate.country','=','tblCountry.country');
            // })->select('tblServiceTemplate.country AS country','tblCountry.countryID As CountryID')->where("tblServiceTemplate.CompanyID",User::get_companyID())
            //     ->orderBy('tblServiceTemplate.country')->lists("country", "CountryID");

            $Package = array('' => "All") + Package::where([
                "status" => 1,
                "CompanyID" => User::get_companyID()
            ])->lists("Name", "PackageId");


            $country            = ServiceTemplate::getCountryDD($companyID);
            $AccessType         = ServiceTemplate::getAccessTypeDD($companyID);
            $City               = ServiceTemplate::getCityDD($companyID);
            $Tariff             = ServiceTemplate::getTariffDD($companyID);
            $Prefix             = ServiceTemplate::getPrefixDD($companyID);      
            // $CityTariffFilter = [];
            // foreach($CityTariff as $key => $City){
            //     if(strpos($City, " per ")){
            //         $CityTariffFilter[$City] = $City;
            //         unset($CityTariff[$key]);
            //     }
            // }

            $country = array('' => "All") + $country;
            $AccessType = array('' => "All") + $AccessType;
            $Prefix = array('' => "All") + $Prefix;
            $City = array('' => "All") + $City;
            $type = array('' => "All") + $type;
            $Tariff = array('' => "All") + $Tariff;
            $countryForRule = array('' => "All") + $countryForRule;

            return View::make('rategenerators.rules.add', compact('id','Timezones','vendors','rateGenerator','rategenerator_rules','country','AccessType','Prefix','City','type','countryForRule','Tariff','Package'));
        }
    }
    public function edit($id, $RateRuleID) {
        if ($id > 0 && $RateRuleID > 0) {
            //Code
            $companyID = User::get_companyID();
            $rategenerator_rule = RateRule::where(["RateRuleId" => $RateRuleID])->get()->first()->toArray();
            $OriginationCode        = $rategenerator_rule["OriginationCode"];
            $OriginationType        = $rategenerator_rule["OriginationType"];
            $OriginationCountryID   = $rategenerator_rule["OriginationCountryID"];
            $DestinationCode        = $rategenerator_rule["Code"];
            $DestinationType        = $rategenerator_rule["DestinationType"];
            $DestinationCountryID   = $rategenerator_rule["DestinationCountryID"];


            $type = Rate::where("CompanyID",User::get_companyID())->whereRaw('Type IS NOT NULL')->lists('Type','Type');
            $countryForRule = Country::lists('Country','CountryID');
            
            // $country = ServiceTemplate::Join('tblCountry', function($join) {
            //     $join->on('tblServiceTemplate.country','=','tblCountry.country');
            // })->select('tblServiceTemplate.country AS country','tblCountry.countryID As CountryID')->where("tblServiceTemplate.CompanyID",User::get_companyID())
            //     ->orderBy('tblServiceTemplate.country')->lists("country", "CountryID");

            $Package = array('' => "All") + Package::where([
                "status" => 1,
                "CompanyID" => User::get_companyID()
            ])->lists("Name", "PackageId");

            $country            = ServiceTemplate::getCountryDD($companyID);
            $AccessType         = ServiceTemplate::getAccessTypeDD($companyID);
            $City               = ServiceTemplate::getCityDD($companyID);
            $Tariff             = ServiceTemplate::getTariffDD($companyID);
            $Prefix             = ServiceTemplate::getPrefixDD($companyID);   
            // $CityTariffFilter = [];
            // foreach($CityTariff as $key => $City){
            //     if(strpos($City, " per ")){
            //         $CityTariffFilter[$City] = $City;
            //         unset($CityTariff[$key]);
            //     }
            // }

            $country = array('' => "All") + $country;
            $AccessType = array('' => "All") + $AccessType;
            $Prefix = array('' => "All") + $Prefix;
            $City = array('' => "All") + $City;
            $type = array('' => "All") + $type;
            $Tariff = array('' => "All") + $Tariff;
            $countryForRule = array('' => "All") + $countryForRule;

            $Timezones = Timezones::getTimezonesIDList();
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

            return View::make('rategenerators.rules.edit', compact('id','Timezones','rategenerator_rule', 'RateRuleID', 'OriginationCode', 'OriginationDescription', 'DestinationCode', 'DestinationDescription' ,'Description', 'rategenerator_sources', 'vendors', 'rategenerator' ,  'rategenerator_margins','country','AccessType','Prefix','City','type','countryForRule','OriginationType','OriginationCountryID','DestinationType','DestinationCountryID','Tariff','Package'));



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
                'tblRateRuleMargin.MinRate',
                'tblRateRuleMargin.MaxRate',
                'tblRateRuleMargin.AddMargin',
                'tblRateRuleMargin.FixedValue',
                'tblRateRuleMargin.RateRuleMarginId',
            ))->orderBy('MinRate', 'ASC');




//            $rategenerator_margins = RateRuleMargin::leftJoin('tblRateGenerator', function($join) {
//                $join->on('tblRateGenerator.RateGeneratorId','=','tblRateRuleMargin.RateGeneratorId');
//            })
//    ->where("tblRateRuleMargin.RateRuleID", $RateRuleID)
//    ->select([
//        'tblRateRuleMargin.MinRate','tblRateRuleMargin.MaxRate','tblRateRuleMargin.AddMargin', 'tblRateRuleMargin.FixedValue', 'tblRateRuleMargin.RateRuleMarginId', 'tblRateGenerator.LessThenRate', 'tblRateGenerator.ChargeRate'
//    ]);

            return Datatables::of($rategenerator_margins)->make();
        }

    }


    // CreateCode
    public function store_code($id) {

        if ($id > 0) {
            $last_max_order =  RateRule::where(["RateGeneratorId" => $id])->max('Order');
            $data = Input::all();
            $rules = array();
            $data['Order'] = $last_max_order+1;
            $data['CreatedBy'] = User::get_user_full_name();
            $data['RateGeneratorId'] = $id;

            if(isset($data['Origination'])) {
                $data ['OriginationDescription'] = $data['Origination'];
                unset($data['Origination']);
            }
            $rateGenerator = RateGenerator::findOrFail($id);

            if($rateGenerator->SelectType == RateType::getRateTypeIDBySlug(RateType::SLUG_DID) || $rateGenerator->SelectType == RateType::getRateTypeIDBySlug(RateType::SLUG_PACKAGE)) {
                $rules = array(
                    'Component'  => 'required',
                    'TimeOfDay'  => 'required',
                    'CreatedBy'  => 'required',
                );

            }
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            if($rateGenerator->SelectType != RateType::getRateTypeIDBySlug(RateType::SLUG_DID)) {
                if (isset($data['Code']) && !empty($data['Code'])) {
                    $rateRuleDesination = RateRule::select('Code')->where(["RateGeneratorId" => $data['RateGeneratorId'], "Code" => $data['Code'],'DestinationType' => $data['DestinationType']])->first();
                    if ($rateRuleDesination) {
                        if (isset($rateRuleDesination->Code) && isset($rateRuleDesination->Description)) {
                            return Response::json(array("status" => "failed", "message" => "Destination Code or Description already exist"));
                        }
                    }
                }
                if (isset($data['OriginationCode']) && !empty($data['OriginationCode'])) {
                    $rateRuleOrigination = RateRule::select('OriginationCode', 'OriginationDescription')->where(["RateGeneratorId" => $data['RateGeneratorId'], "OriginationCode" => $data['OriginationCode'],'OriginationType' =>$data["OriginationType"]])->first();
                    if ($rateRuleOrigination) {
                        if (isset($rateRuleOrigination->OriginationCode) && isset($rateRuleOrigination->OriginationDescription)) {
                            return Response::json(array("status" => "failed", "message" => "Origination Code or Description already exist"));
                        }
                    }
                }
            }
            if(isset($data['CountryID']) && $data['CountryID'] == ''){
                $data['CountryID'] = null;
            }
            if(isset($data['DestinationCountryID']) && $data['DestinationCountryID'] == ''){
                $data['DestinationCountryID'] = null;
            }
            if(isset($data['OriginationCountryID']) && $data['OriginationCountryID'] == ''){
                $data['OriginationCountryID'] = null;
            }

            // Checking if any other rule exist with same condition
            $whereArr = [];
            foreach($data as $key => $item){
                if($key != "CreatedBy" && $key != "Order")
                    $whereArr[$key] = $item;
            }

            $check = RateRule::where($whereArr)->count();
            if($check > 0)
                return Response::json(array("status" => "failed", "message" => "Margin rule already exist with same condition."));

            if ($rule_id = RateRule::insertGetId($data)) {

                // If type is not DID
                // Selecting all vendors as sources by default
                if($rateGenerator->SelectType != RateType::getRateTypeIDBySlug(RateType::SLUG_DID)){
                    $companyID = User::get_companyID();
                    $vendors = Account::select(["AccountID"])->where(["Status" => 1, "IsVendor" => 1, "AccountType" => 1, "CompanyID" => $companyID])->get();
                    if($vendors != false){
                        $insertSources = [];
                        foreach($vendors as $vendor){
                            $insertSources[] = [
                                'RateRuleId' => $rule_id,
                                'AccountId'  => $vendor->AccountID,
                                'CreatedBy'  => $data['CreatedBy'],
                                'created_at' => date('Y-m-d H:i:s'),
                                'ModifiedBy' => $data['CreatedBy'],
                                'updated_at' => date('Y-m-d H:i:s'),
                            ];
                        }

                        if(!empty($insertSources)){
                            try {
                                DB::beginTransaction();
                                RateRuleSource::insert($insertSources);
                                //$rateGenerator->update(['Sources' => 'all']);
                                DB::commit();
                            } catch (Exception $ex) {
                                DB::rollback();
                            }
                        }
                    }
                }

                return Response::json(array("status" => "success", "message" => "RateGenerator Rule Successfully Created" , "redirect" => \Illuminate\Support\Facades\URL::to('/rategenerators/' . $id .'/rule/'.$rule_id . '/edit') ));
            } else {
                return Response::json(array("status" => "failed", "message" => "Problem Creating RateGenerator Rule."));
            }
        }
    }

    // Update Code
    public function update_rule($id, $RateRuleID) {

        if ($id > 0 && $RateRuleID > 0) {
            $data = Input::all();
            $rules = array();
          
            $rategenerator_rules = RateRule::findOrFail($RateRuleID); // RateRule::where([ "RateRuleID" => $RateRuleID])->get();
            $rateGenerator = RateGenerator::findOrFail($id);
            $data ['ModifiedBy'] = User::get_user_full_name();

            if(isset($data['Origination'])) {
                $data ['OriginationDescription'] = $data['Origination'];
                unset($data['Origination']);
            }

            if($rateGenerator->SelectType == RateType::getRateTypeIDBySlug(RateType::SLUG_DID) || $rateGenerator->SelectType == RateType::getRateTypeIDBySlug(RateType::SLUG_PACKAGE)) {
                $rules = array(
                    'Component'   => 'required',
                    'TimeOfDay'   => 'required',
                    'ModifiedBy'  => 'required',
                );
            }

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return json_validator_response($validator);
            }

            if(isset($data['CountryID']) && $data['CountryID'] == ''){
                $data['CountryID'] = null;
            }
            if(isset($data['DestinationCountryID']) && $data['DestinationCountryID'] == ''){
                $data['DestinationCountryID'] = null;
            }
            if(isset($data['OriginationCountryID']) && $data['OriginationCountryID'] == ''){
                $data['OriginationCountryID'] = null;
            }

            // Checking if any other rule exist with same condition
            $whereArr = [];
            foreach($data as $key => $item){
                if($key != "CreatedBy" && $key != "Order" && $key != "ModifiedBy")
                    $whereArr[$key] = $item;
            }
            $whereArr['RateGeneratorId'] = $id;
            $check = RateRule::where('RateRuleId','!=',$RateRuleID)->where($whereArr)->count();
            if($check > 0)
                return Response::json(array("status" => "failed", "message" => "Margin rule already exist with same condition."));

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
        $data = Input::all();


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
            if(!empty($data ['MinRate'])){
                $data ['MinRate'] = doubleval($data ['MinRate']);
            }else{
                $data ['MinRate']='';
            }
            if(!empty($data ['MaxRate'])){
                $data ['MaxRate'] = doubleval($data ['MaxRate']);
            }else{
                $data ['MaxRate']='';
            }
            
            $data ['FixedValue'] = doubleval($data ['FixedValue']);
            $RateGeneratorID  = $data['RateGeneratorId'];
            unset($data['RateGeneratorId']);
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