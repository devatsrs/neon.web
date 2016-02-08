<?php

class Note extends \Eloquent {

	//protected $fillable = ["NoteID","CompanyID","AccountID","Title","Note","created_at","updated_at","created_by","updated_by" ];

    protected $guarded = array();

    protected $table = 'tblNote';

    protected  $primaryKey = "NoteID";

}