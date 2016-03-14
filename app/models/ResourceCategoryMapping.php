<?php

class ResourceCategoryMapping extends \Eloquent {

    protected $guarded = array('MappingID');

    protected $table = 'tblResourceCategoryMapping';

    protected  $primaryKey = "MappingID";

    public $timestamps = false; // no created_at and updated_at

    public static function getResourceCategoriesAction($ResourceCategoryID){
        $result = ResourceCategoryMapping::select('ResourceCategoryID','ResourceID')->orderBy('ResourceCategoryName')->get();        
        return $result;
    }

}