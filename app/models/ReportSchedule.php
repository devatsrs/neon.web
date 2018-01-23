<?php
class ReportSchedule extends \Eloquent {
    protected $guarded = array("ReportScheduleID");
    protected $table = "tblReportSchedule";
    protected $primaryKey = "ReportScheduleID";
    protected $connection = 'neon_report';
    protected $fillable = array(
        'CompanyID','Name','Settings','ReportID','created_at','UpdatedBy','updated_at','CreatedBy','Status'
    );

    public static function getDropdownIDList($CompanyID){
        $DropdownIDList = ReportSchedule::where(array("CompanyID"=>$CompanyID))->lists('Name', 'ReportScheduleID');
        $DropdownIDList = array('' => "Select") + $DropdownIDList;
        return $DropdownIDList;
    }
}