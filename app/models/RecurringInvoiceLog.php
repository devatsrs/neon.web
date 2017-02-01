<?php

class RecurringInvoiceLog extends \Eloquent {
    protected $connection = 'sqlsrv2';
    protected $fillable = [];
    protected $guarded = array('RecurringInvoicesLogID');
    protected $table = 'tblRecurringInvoiceLog';
    protected  $primaryKey = "RecurringInvoicesLogID";

    const CREATED = 1;
    const UPDATED = 2;
    const VIEWED = 3;
    const SENT = 4;
    const START=5;
    const STOP=6;
    const GENERATE = 7;
    const COMMENT  =8;

    public static $log_status = array(
                                    self::CREATED=>'Created',
                                    self::VIEWED=>'Viewed',
                                    self::SENT=>'Sent',
                                    self::UPDATED=>'Updated',
                                    self::START => 'Start',
                                    self::STOP => 'Stop',
                                    self::GENERATE => 'Generate',
                                    self::COMMENT => 'Comment'
                                );

    public static function get_comments($EstimateID){
        $EstimateComment 	= 	EstimateLog::where(["EstimateID" => $EstimateID,"EstimateLogStatus" =>EstimateLog::COMMENT])->orderBy("EstimateLogID", "asc")->get();
        return $EstimateComment;
    }

    public static function get_comments_count($EstimateID){
        $EstimateCommentCount 	= 	EstimateLog::where(["EstimateID" => $EstimateID,"EstimateLogStatus" =>EstimateLog::COMMENT])->count();
        return $EstimateCommentCount;
    }

}