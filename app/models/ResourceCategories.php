<?php

class ResourceCategories extends \Eloquent {

    protected $guarded = array('ResourceCategoryID');

    protected $table = 'tblResourceCategories';

    protected  $primaryKey = "ResourceCategoryID";

    public static function getResourceCategories(){
        $result = ResourceCategories::select('ResourceCategoryID','ResourceCategoryName','CategoryGroupID')->orderBy('ResourceCategoryName')->get();
        return $result;
    }

}