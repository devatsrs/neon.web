<?php

class CRMBoard extends \Eloquent {

    protected $connection = 'sqlsrv';
    protected $fillable = [];
    protected $guarded = array('BoardID');
    protected $table = 'tblCRMBoards';
    public  $primaryKey = "BoardID";
    const OpportunityBoard = 1;
    const TaskBoard = 2;

    const InActive = 0;
    const Active = 1;
    const All = 2;

    public static function getBoards($BoardType=CRMBoard::OpportunityBoard){
        $compantID = User::get_companyID();
        $opportunity = CRMBoard::select(['BoardID','BoardName'])->where(['CompanyID'=>$compantID,'BoardType'=>$BoardType])->orderBy('BoardName', 'asc')->lists('BoardName','BoardID');
        if(!empty($opportunity)){
            $opportunity = [''=>'Select'] + $opportunity;
        }
        return $opportunity;
    }

    public static function getTaskBoard(){
        $compantID = User::get_companyID();
        $taskBoard = CRMBoard::where(['CompanyID'=>$compantID,'BoardType'=>CRMBoard::TaskBoard])->get();
        return $taskBoard;
    }
}