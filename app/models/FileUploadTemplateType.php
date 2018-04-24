<?php

class FileUploadTemplateType extends \Eloquent
{
    protected $fillable = [];
    protected $guarded = array();
    protected $table = 'tblFileUploadTemplateType';
    protected $primaryKey = "FileUploadTemplateTypeID";

    public static function getTemplateTypeIDList(){
        $row = FileUploadTemplateType::where(['Status'=>'1'])->orderBy('FileUploadTemplateTypeID','ASC')->lists('Title', 'FileUploadTemplateTypeID');
        $row = array(""=> "Select")+$row;
        return $row;
    }

    public static function getTemplateUploadDir($id){
        $dir = FileUploadTemplateType::where(['FileUploadTemplateTypeID'=>$id,'Status'=>'1'])->pluck('UploadDir');
        return $dir;
    }

    public static function getTemplateType($Type){
        return FileUploadTemplateType::where(['TemplateType'=>$Type])->pluck('FileUploadTemplateTypeID');
    }

}

?>