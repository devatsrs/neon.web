<?php

class AuditDetails extends \Eloquent {
    protected $guarded = array("AuditDetailID");

    protected $table = 'tblAuditDetails';

    protected  $primaryKey = "AuditDetailID";

    public    $timestamps 	= 	false; // no created_at and updated_at


    /*public static function getAccountLogList($AccountID) {
        return DB::table('tblAuditDetails AS d')
                    ->leftjoin('tblAuditHeader AS h','h.AuditHeaderID','=','d.AuditHeaderID')
                    ->leftjoin('tblAccount AS a','h.ParentColumnID','=','a.AccountID')
                    ->where(['h.Type'=>'account','h.ParentColumnName'=>'AccountID','h.ParentColumnID'=>$AccountID,'a.AccountID'=>$AccountID])
                    ->select('a.AccountName','d.ColumnName','d.OldValue','d.NewValue','d.created_at','d.created_by')
                    ->get();
    }*/
}