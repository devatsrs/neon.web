<?php

class NoticeBoardPost extends \Eloquent {

    protected $guarded = array('NoticeBoardPostID');

    protected $table = 'tblNoticeBoardPost';

    protected  $primaryKey = "NoticeBoardPostID";

    /** add columns here to save in table  */
    protected $fillable = array(
        'Title','Detail','Type','CompanyID'
    );

}