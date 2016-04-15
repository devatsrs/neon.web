<?php

class CRMBoardColumn extends \Eloquent {

    protected $connection = 'sqlsrv';
    protected $fillable = [];
    protected $guarded = array('BoardColumnID');
    protected $table = 'tblCRMBoardColumn';
    public  $primaryKey = "BoardColumnID";

    public static function getTaskStatusList($boardID){
        $companyID = User::get_companyID();
        $row = CRMBoardColumn::where(['BoardID'=>$boardID])
            ->select(['BoardColumnID','BoardColumnName'])
            ->lists('BoardColumnName','BoardColumnID');
        return $row;
    }
}