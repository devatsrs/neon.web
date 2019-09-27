<?php

class Nodes extends \Eloquent {

	//protected $fillable = ["NoteID","CompanyID","AccountID","Title","Note","created_at","updated_at","created_by","updated_by" ];

    protected $guarded = array();

    protected $table = 'tblNode';

    protected  $primaryKey = "ServerID";

    public static $rules = array(
        'ServerName' =>      'required|unique:tblNode',
        'ServerIP' =>      'required|unique:tblNode',
        'Username' =>      'required|unique:tblNode',
    );

    public static function getActiveNodes(){
        $Nodes = Nodes::where('Status','1')->lists('ServerName','ServerIP');
        return $Nodes;
    }
}