<?php

class ResourceCategories extends \Eloquent {

    protected $guarded = array('ResourceCategoryID');

    protected $table = 'tblResourceCategories';

    protected  $primaryKey = "ResourceCategoryID";

    public $timestamps = false; // no created_at and updated_at

    public static function getResourceCategories(){
        $result = ResourceCategories::select('ResourceCategoryID','ResourceCategoryName')->orderBy('ResourceCategoryName')->get();
        return $result;
    }

}