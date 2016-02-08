<?php

class Resources extends \Eloquent {

    protected $guarded = array('ResourceID');

    protected $table = 'tblResource';

    protected  $primaryKey = "ResourceID";

    public static function getResources(){
        $roles = Resources::select('ResourceID','ResourceName')->orderBy('ResourceName')->lists('ResourceName', 'ResourceID');
        return $roles;
    }

    public static function insertResources(){
        $CompanyID = user::get_companyID();
        $routeCollection = Route::getRoutes();
        foreach ($routeCollection as $value) {
            $str = $value->getActionName();
            if(strpos($str,'\\')===false) {
                $resourceName = str_replace('Controller@','.',$str);
                $resourceValue = str_replace('@','.',$str);
                if(Resources::where(['ResourceValue'=>$resourceValue])->count()>0){
                }else {
                    $data = ['ResourceName' => $resourceName, 'ResourceValue' => $resourceValue, 'CompanyID' => $CompanyID, 'CreatedBy' => User::get_user_full_name()];
                    Resources::create($data);
                    if (strpos($resourceName, '.index')) {
                        $resourceName = str_replace('.index', '.*', $resourceName);
                        $resourceValue = str_replace('@index', '.*', $str);
                        $data = ['ResourceName' => $resourceName, 'ResourceValue' => $resourceValue, 'CompanyID' => $CompanyID, 'CreatedBy' => User::get_user_full_name()];
                        Resources::create($data);
                    }
                }
            }
        }
    }
}