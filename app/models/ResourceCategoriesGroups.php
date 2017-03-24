
<?php

class ResourceCategoriesGroups extends \Eloquent {

    protected $guarded = array('CategoriesGroupID');

    protected $table = 'tblResourceCategoriesGroups';

    protected  $primaryKey = "CategoriesGroupID";

    protected $roles = [];	
	
	const  All = "All";

	public static function GetResourcesGroup(){
		return  ResourceCategoriesGroups::select('GroupName','CategoriesGroupID')->orderBy('CategoriesGroupID')->lists('GroupName', 'CategoriesGroupID');
	}
	
	public static function GetAllGroup(){
		return  ResourceCategoriesGroups::where(['GroupName'=>self::All])->orderBy('CategoriesGroupID')->pluck('CategoriesGroupID');
	}
}