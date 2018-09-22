<?php

class CreditNotesLog extends \Eloquent {
    protected $connection = 'sqlsrv2';
    protected $fillable = [];
    protected $guarded = array('CreditNotesLogID');
    protected $table = 'tblCreditNotesLog';
    protected  $primaryKey = "InvoiceLogID";

    const CREATED = 1;
    const UPDATED = 2;
    const VIEWED = 3;
    const SENT =4;
    const CANCEL =5;
    const REGENERATED  =6;
    const POST  = 7;
    const COMMENT  =9;
    const PAID  =10;

    public static $log_status = array(self::CREATED=>'Created',self::VIEWED=>'Viewed',self::CANCEL=>'Cancel',self::SENT=>'Sent',self::UPDATED=>'Updated',self::REGENERATED => 'Regenerated',self::POST => 'Post',self::COMMENT => 'Comment',self::PAID => 'Paid');

    public static function get_comments($CreditNotesID){
        $CreditNotesComment 	= 	CreditNotesLog::where(["CreditNotesID" => $CreditNotesID,"CreditNotesLogStatus" =>CreditNotesLog::COMMENT])->orderBy("CreditNotesLogID", "asc")->get();
        return $CreditNotesComment;
    }

    public static function get_comments_count($CreditNotesID){
        $CreditNotesCommentCount 	= 	CreditNotesLog::where(["CreditNotesID" => $CreditNotesID,"CreditNotesLogStatus" =>CreditNotesLog::COMMENT])->count();
        return $CreditNotesCommentCount;
    }

}