<?php


class ServicesTemplateApiController extends ApiController
{


    public function storeServiceTempalteData()
    {
        Log::info('storeServiceTempalteData:Service Template Controller.');
        try {
            $post_vars = json_decode(file_get_contents("php://input"));
            if (! $post_vars ) {
                return Response::json(["status"=>"failed", "message"=>"Please provide the post message"]);
            }
            //Log::info('storeServiceTempalteData:storeServiceTempalteData.' . $post_vars->Name);
            // $data['Name'] = $post_vars->Name;
            //  return Response::json(["status"=>"success", "data"=>$post_vars]);

            $data['Name'] = $post_vars->Name;
            $data['ServiceId'] = $post_vars->ServiceId;
            $CurrenctCodeSql = Currency::where('Code',$post_vars->Currency);
            Log::info('storeServiceTempalteData $CurrenctCodeSql.' . $CurrenctCodeSql->toSql());
            $CurrenctCodeResult = $CurrenctCodeSql->first();
            if (!isset($CurrenctCodeResult)) {
                return Response::json(["status"=>"failed", "message"=>"Please provide the valid currency"]);
            }

            $data['CurrencyId'] = $CurrenctCodeResult->CurrencyId;
            $data['OutboundDiscountPlanId'] = $post_vars->OutboundDiscountPlanId;
            $data['InboundDiscountPlanId'] = $post_vars->InboundDiscountPlanId;
            $data['OutboundRateTableId'] = $post_vars->OutboundRateTableId;
            if (isset($post_vars->selectedSubscription)) {
                $data['selectedSubscription'] = $post_vars->selectedSubscription;
            }else {
                $data['selectedSubscription'] = '';
            }
            if (isset($post_vars->selectedcategotyTariff)) {
                $data['selectedcategotyTariff'] = $post_vars->selectedcategotyTariff;
            } else {
                $data['selectedcategotyTariff'] = '';
            }
            Log::info('storeServiceTempalteData:storeServiceTempalteData.' .
                'Name:' . $data['Name'] . 'ServiceId' . $data['ServiceId'] . 'CurrencyId' . $data['CurrencyId'] .
                'OutboundDiscountPlanId' . $data['OutboundDiscountPlanId'] . 'InboundDiscountPlanId' . $data['InboundDiscountPlanId'] .
                'OutboundRateTableId' . $data['OutboundRateTableId'] . 'selectedSubscription' . $data['selectedSubscription'] .
                'selectedcategotyTariff' . $data['selectedcategotyTariff']);
            $j=0;

            $CreatedBy = '';
            $companyID 					 =  User::get_companyID();
            $DynamicFields = [];

            try {
                $CreatedBy = User::get_user_full_name();
            }catch (Exception $ex) {
                $CreatedBy = '';
            }
            if (!isset($CreatedBy)) {
                    return Response::json(["status" => "failed", "message" => "Not authorized. Please Login"]);
            }
            try {
                if (isset($post_vars->DynamicFields)) {
                    foreach ($post_vars->DynamicFields as $key => $value) {
                        Log::info('Dynamic Field.' . $value->Name . ' ' . $value->Value);
                        $DynamicFields[$j]['FieldValue'] = $value->Value;
                        $Type = ServiceTemplateTypes::DYNAMIC_TYPE;
                        $DynamicFieldsSql = DynamicFields::where('Type', $Type)->where('CompanyID', $companyID)->where('Status', '1')->where('FieldName', $value->Name);
                        Log::info('storeServiceTempalteData $DynamicFieldsSql.' . $DynamicFieldsSql->toSql());
                        $DynamicFieldsResult = $DynamicFieldsSql->first();
                        if (!isset($DynamicFieldsResult)) {
                            return Response::json(["status" => "failed", "message" => "Please provide the valid and active dynamic field"]);
                        }
                        $DynamicFields[$j]['DynamicFieldsID'] = $DynamicFieldsResult->DynamicFieldsID;
                        Log::info('storeServiceTempalteData $DynamicFieldsSql.' . $DynamicFieldsResult->DynamicFieldsID);

                        $DynamicFields[$j]['FieldOrder'] = $DynamicFieldsResult->FieldOrder;
                        $DynamicFields[$j]['CompanyID'] = $companyID;
                        $DynamicFields[$j]['created_at'] = date('Y-m-d H:i:s.000');
                        $DynamicFields[$j]['created_by'] = $CreatedBy;
                        $j++;
                    }

                    if (isset($DynamicFields)) {
                        if ($error = DynamicFieldsValue::validate($DynamicFields)) {
                            return Response::json(["status" => "failed", "message" => $error]);
                        }
                    }
                }
            }catch(Exception $ex) {

            }

            $subsriptionList = isset($data['selectedSubscription']) ? $data['selectedSubscription'] : '';
            $CategoryTariffList = isset($data['selectedcategotyTariff']) ? $data['selectedcategotyTariff'] : '';
            $subsriptionList = trim($subsriptionList);
            $CategoryTariffList = trim($CategoryTariffList);
            if (ends_with($subsriptionList, ',')) {
                $subsriptionList = substr($subsriptionList, 0, strlen($subsriptionList) - 1);
            }
            if (ends_with($CategoryTariffList, ',')) {
                $CategoryTariffList = substr($CategoryTariffList, 0, strlen($CategoryTariffList) - 1);
            }

            Log::info('storeServiceTempalteData:read Subscription List.' . $subsriptionList);
            Log::info('storeServiceTempalteData:read Category Tariff List.' . $CategoryTariffList);
            $subsriptionList = explode(",", $subsriptionList);
            $CategoryTariffList = explode(",", $CategoryTariffList);
            $OutboundDiscountPlanId = isset($data['OutboundDiscountPlanId']) ? $data['OutboundDiscountPlanId'] : '';
            $InboundDiscountPlanId = isset($data['InboundDiscountPlanId']) ? $data['InboundDiscountPlanId'] : '';
            $CurrencyId = isset($data['CurrencyId']) ? $data['CurrencyId'] : '';


            if (!empty($data)) {
                Log::info('storeServiceTempalteData:read Category Tariff List1.');
                $data['CompanyID'] = User::get_companyID();
                $data['Status'] = isset($data['Status']) ? 1 : 0;


                ServiceTemplate::$rules['Name'] = 'required|unique:tblServiceTemplate';
                $validator = Validator::make($data, ServiceTemplate::$rules);
                Log::info('storeServiceTempalteData:read Category Tariff List2.');
                if ($validator->fails()) {
                    $errors = "";
                    foreach ($validator->messages()->all() as $error) {
                        $errors .= $error . "<br>";
                    }
                    return Response::json(["status" => "failed", "message" => $errors]);
                }
                Log::info('storeServiceTempalteData:read Category Tariff List2.');
                if (isset($data['ServiceId']) && $data['ServiceId'] != '') {
                    $ServiceTemplateData['ServiceId'] = $data['ServiceId'];
                }
                $ServiceTemplateData['Name'] = $data['Name'];
                if ($OutboundDiscountPlanId != '') {
                    $ServiceTemplateData['OutboundDiscountPlanId'] = $OutboundDiscountPlanId;
                }
                if ($InboundDiscountPlanId != '') {
                    $ServiceTemplateData['InboundDiscountPlanId'] = $InboundDiscountPlanId;
                }
                if (isset($data['OutboundRateTableId']) && $data['OutboundRateTableId'] != '') {
                    $ServiceTemplateData['OutboundRateTableId'] = $data['OutboundRateTableId'];
                }

                $ServiceTemplateData['CurrencyId'] = $data['CurrencyId'];


                if ($ServiceTemplate = ServiceTemplate::create($ServiceTemplateData)) {

                    $ServiceTemapleSubscription['ServiceTemplateID'] = $ServiceTemplate->ServiceTemplateId;
                    Log::info('storeServiceTempalteData:ServiceTemplateID.' . $ServiceTemplate->ServiceTemplateId);
                    foreach ($subsriptionList as $subsription) {
                        $ServiceTemapleSubscription['SubscriptionId'] = $subsription;
                        Log::info('storeServiceTempalteData:Service Template Controller.' . $subsription);
                        if (isset($subsription) && $subsription != '') {
                            ServiceTemapleSubscription::create($ServiceTemapleSubscription);
                        }
                    }

                    foreach ($CategoryTariffList as $index1 => $CategoryTariffValue) {
                        try {
                            $ServiceTemapleInboundTariff['ServiceTemplateID'] = $ServiceTemplate->ServiceTemplateId;
                            Log::info('storeServiceTempalteData:$CategoryTariffValue1.' . $CategoryTariffValue);
                            $DIDRateTableList = explode("-", $CategoryTariffValue);
                            //Log::info('$CategoryTariffValue1.' . $DIDRateTableList);
                            Log::info('storeServiceTempalteData:$CategoryTariffValue1.' . count($DIDRateTableList));
                            if ($DIDRateTableList[0] != 0) {
                                $ServiceTemapleInboundTariff['DIDCategoryId'] = $DIDRateTableList[0];
                            }
                            $ServiceTemapleInboundTariff['RateTableId'] = $DIDRateTableList[1];
                            ServiceTemapleInboundTariff::create($ServiceTemapleInboundTariff);
                            $DIDRateTableList[0] = '';
                            $DIDRateTableList[1] = '';
                            $DIDRateTableList = '';
                            $ServiceTemapleInboundTariff = '';
                        } catch (Exception $ex) {
                            Response::json(["status" => "failed", "message" => $ex->getMessage(), 'newcreated' => $ServiceTemplate]);
                        }
                    }

                    Log::info('Create the dynamic field.' . count($DynamicFields));
                    if(isset($DynamicFields) && count($DynamicFields)>0) {
                        for($k=0; $k<count($DynamicFields); $k++) {
                            if(trim($DynamicFields[$k]['FieldValue'])!='') {
                                $DynamicFields[$k]['ParentID'] = $ServiceTemplate->ServiceTemplateId;
                                DB::table('tblDynamicFieldsValue')->insert($DynamicFields[$k]);
                            }
                        }
                    }

                    return Response::json(["status" => "success", "message" => "Service Template Successfully Created", 'newcreated' => $ServiceTemplate]);
                    // return  Response::json(array("status" => "success", "message" => "Service Template Successfully Created",'LastID'=>$ServiceTemplate->ServiceTemplateId,'newcreated'=>$ServiceTemplate));
                } else {
                    return Response::json(["status" => "failed", "message" => "Problem Creating Service."]);
                    //return  Response::json(array("status" => "failed", "message" => "Problem Creating Service."));
                }

            }
            //return Response::json(array("status" => "failed", "message" => "Problem Creating Service."));
        } catch (Exception $ex) {
            return Response::json(["status" => "failed", "message" => $ex->getMessage()]);
            //return  Response::json(array("status" => "failed", "message" => $ex->getMessage(),'LastID'=>'','newcreated'=>''));
        }
    }

    public function testData()
    {

        $post_vars = json_decode(file_get_contents("php://input"));
        //$post_vars = $_SERVER['SERVER_NAME'];
        return Response::json(["status" => "success", "data" => $post_vars]);
    }
}