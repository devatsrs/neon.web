<?php

class EstimateLog extends \Eloquent {
    protected $connection = 'sqlsrv2';
    protected $fillable = [];
    protected $guarded = array('EstimateLogID');
    protected $table = 'tblEstimateLog';
    protected  $primaryKey = "EstimateLogID";

    const CREATED = 1;
    const UPDATED = 2;
    const VIEWED = 3;
    const SENT =4;
    const CANCEL =5;
    const REGENERATED  =6;
    const ACCEPTED  =7;
    const REJECTED  =8;
    const COMMENT  =9;

    public static $log_status = array(
                                    self::CREATED=>'Created',
                                    self::VIEWED=>'Viewed',
                                    self::CANCEL=>'Cancel',
                                    self::SENT=>'Sent',
                                    self::UPDATED=>'Updated',
                                    self::REGENERATED => 'Regenerated',
                                    self::ACCEPTED => 'Accepted',
                                    self::REJECTED => 'Rejected',
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