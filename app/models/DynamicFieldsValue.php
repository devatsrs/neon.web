<?php

class DynamicFieldsValue extends \Eloquent {

    protected $guarded = array("DynamicFieldsValueID");
    protected $table = 'tblDynamicFieldsValue';
    protected  $primaryKey = "DynamicFieldsValueID";
    public    $timestamps 	= 	false; // no created_at and updated_at
    protected $fillable = [];
    static protected  $enable_cache = false;
    const BARCODE_SLUG = 'BarCode';

    public static function getDynamicColumnValuesByProductID($DynamicFieldsID,$ProductID) {
        $CompanyID = User::get_companyID();

        return DynamicFieldsValue::where('CompanyID',$CompanyID)
                                    ->where('ParentID',$ProductID)
                                    ->where('DynamicFieldsID',$DynamicFieldsID)
                                    ->get();
    }

    public static function deleteDynamicColumnValuesByProductID($CompanyID,$ProductID,$DynamicFieldsIDs) {
        return DynamicFieldsValue::where('CompanyID',$CompanyID)
                                    ->where('ParentID',$ProductID)
                                    ->whereIn('DynamicFieldsID',$DynamicFieldsIDs)
                                    ->delete();
    }

    public static function deleteDynamicValuesByProductID($CompanyID,$ProductID) {
        return DynamicFieldsValue::where('CompanyID',$CompanyID)
            ->where('ParentID',$ProductID)
            ->delete();
    }

    public static function validate($data) {
        foreach ($data as $DynamicField) {

            $DynamicColumn = DynamicFields::where('Status',1)->find($DynamicField['DynamicFieldsID']);

            if($DynamicColumn) {
                $isUnique = $DynamicColumn->fieldUniqueOption()->first();

                if ($isUnique && $isUnique->count() > 0) {
                    if ($isUnique->Options == 1) {
                        $rules = array(
                            'FieldValue' => 'unique:tblDynamicFieldsValue,FieldValue,NULL,DynamicFieldsValueID,DynamicFieldsID,' . $DynamicField['DynamicFieldsID'],
                        );
                        $message = array(
                            'FieldValue.unique' => $DynamicColumn->FieldName . ' already exist!',
                        );

                        $validator = Validator::make($DynamicField, $rules, $message);

                        if ($validator->fails()) {
                            return json_validator_response($validator);
                        }
                    }
                }else{
                    $ruleString='';
                    if($DynamicColumn->FieldDomType == 'numeric'){
                        $ruleString='numeric';
                        if($DynamicColumn->Minimum > 0 || $DynamicColumn->Maximum > 0){
                            if($ruleString!=''){ $ruleString.='|'; }
                            $ruleString.='digits_between:'.$DynamicColumn->Minimum.','.$DynamicColumn->Maximum;
                        }
                    }else{
                        if($DynamicColumn->Minimum > 0){
                            if($ruleString!=''){ $ruleString.='|'; }
                            $ruleString.='min:' . $DynamicColumn->Minimum;
                        }
                        if($DynamicColumn->Maximum > 0){
                            if($ruleString!=''){ $ruleString.='|'; }
                            $ruleString.='max:' . $DynamicColumn->Maximum;

                        }
                    }
                    if($ruleString!=''){
                        $rules = array(
                            'FieldValue' => $ruleString,
                        );
                        $message = array(
                            'FieldValue.digits_between' => $DynamicColumn->FieldName . 'length should not be greater then '.$DynamicColumn->Maximum.' or less then '.$DynamicColumn->Minimum,
                            'FieldValue.numeric' => $DynamicColumn->FieldName . ' must be a number.',
                            'FieldValue.min' => $DynamicColumn->FieldName . ' should be greater then '.$DynamicColumn->Minimum.' and less then '.$DynamicColumn->Maximum.' characters.',
                            'FieldValue.max' => $DynamicColumn->FieldName . ' should be greater then '.$DynamicColumn->Minimum.' and less then '.$DynamicColumn->Maximum.' characters.',
                        );
                        $validator = Validator::make($DynamicField, $rules, $message);

                        if ($validator->fails()) {
                            return json_validator_response($validator);
                        }
                    }

                }
            } else {
                return  Response::json(array("status" => "failed", "message" => "Requested field not exist or it is disabled, Please refresh the page and try again or Please contact your system administrator!"));
            }
        }
    }

    public static function validateOnUpdate($DynamicField) {

        $DynamicColumn = DynamicFields::where('Status',1)->find($DynamicField['DynamicFieldsID']);

        if($DynamicColumn) {
            $isUnique = $DynamicColumn->fieldUniqueOption()->first();

            if ($isUnique && $isUnique->count() > 0) {
                if ($isUnique->Options == 1) {
                    $rules = array(
                        'FieldValue' => 'unique:tblDynamicFieldsValue,FieldValue,'.$DynamicField['DynamicFieldsValueID'].',DynamicFieldsValueID,DynamicFieldsID,' . $DynamicField['DynamicFieldsID'],
                    );
                    $message = array(
                        'FieldValue.unique' => $DynamicColumn->FieldName . ' already exist!',
                    );

                    $validator = Validator::make($DynamicField, $rules, $message);

                    if ($validator->fails()) {
                        return json_validator_response($validator);
                    }
                }
            }else{
                $ruleString='';
                if($DynamicColumn->FieldDomType == 'numeric'){
                    $ruleString='numeric';
                    if($DynamicColumn->Minimum > 0 || $DynamicColumn->Maximum > 0){
                        if($ruleString!=''){ $ruleString.='|'; }
                        $ruleString.='digits_between:'.$DynamicColumn->Minimum.','.$DynamicColumn->Maximum;
                    }
                }else{
                    if($DynamicColumn->Minimum > 0){
                        if($ruleString!=''){ $ruleString.='|'; }
                        $ruleString.='min:' . $DynamicColumn->Minimum;
                    }
                    if($DynamicColumn->Maximum > 0){
                        if($ruleString!=''){ $ruleString.='|'; }
                        $ruleString.='max:' . $DynamicColumn->Maximum;

                    }
                }
                if($ruleString!=''){
                    $rules = array(
                        'FieldValue' => $ruleString,
                    );
                    $message = array(
                        'FieldValue.digits_between' => $DynamicColumn->FieldName . ' length should not be greater then '.$DynamicColumn->Maximum.' or less then '.$DynamicColumn->Minimum,
                        'FieldValue.numeric' => $DynamicColumn->FieldName . ' must be a number.',
                        'FieldValue.min' => $DynamicColumn->FieldName . ' should be greater then '.$DynamicColumn->Minimum.' and less then '.$DynamicColumn->Maximum.' characters.',
                        'FieldValue.max' => $DynamicColumn->FieldName . ' should be greater then '.$DynamicColumn->Minimum.' and less then '.$DynamicColumn->Maximum.' characters.',
                    );
                    $validator = Validator::make($DynamicField, $rules, $message);

                    if ($validator->fails()) {
                        return json_validator_response($validator);
                    }
                }
            }
        } else {
            return  Response::json(array("status" => "failed", "message" => "Requested dynamic field not exist or it is disabled, Please refresh the page and try again or Please contact your system administrator!"));
        }
    }


}