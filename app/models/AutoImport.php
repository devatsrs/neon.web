<?php

class AutoImport extends \Eloquent
{

    protected $fillable = [];
    protected $guarded = [];
    protected $table = 'tblAutoImport';
    protected $primaryKey = "AutoImportID";


    public static function getEmailById($AutoImportID){

        return AutoImport::where('AutoImportID','=',$AutoImportID)->get();
    }

	

}