<?php

class UploadedFiles extends \Eloquent {
    protected $guarded = array("UploadedFileID");

    protected $table = 'tblUploadedFiles';

    protected  $primaryKey = "UploadedFileID";

}