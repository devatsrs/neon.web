<?php

class NoticeBoardPost extends \Eloquent {

    protected $guarded = array('NoticeBoardPostID');

    protected $table = 'tblNoticeBoardPost';

    protected  $primaryKey = "NoticeBoardPostID";

}