<?php

class DynamicFieldsValue extends \Eloquent {

    protected $connection = 'sqlsrv';
    protected $fillable = [];
    protected $guarded = array('DynamicFieldsValueID');
    protected $table = 'tblDynamicFieldsValue';
    public  $primaryKey = "DynamicFieldsValueID"; //Used in BasedController
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

    public static function validate($data) {
        foreach ($data as $DynamicField) {

            $DynamicColumn = DynamicFields::where('Status',1)->find($DynamicField['DynamicFieldsID']);

            if($DynamicColumn) {
                $isUnique = $DynamicColumn->fieldUniqueOption()->first();

                if ($isUnique->count() > 0) {
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

            if ($isUnique->count() > 0) {
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
            }
        } else {
            return  Response::json(array("status" => "failed", "message" => "Requested dynamic field not exist or it is disabled, Please refresh the page and try again or Please contact your system administrator!"));
        }
    }

}