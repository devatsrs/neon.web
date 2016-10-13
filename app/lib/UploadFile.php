<?php

class UploadFile{

    public static function UploadFileLocal($data){
        $filesArray = [];
        $uploadedFile = [];
        $returnText	='';
        $files = $data['file'];
        $attachmentsinfo = $data['attachmentsinfo'];
        if(!empty($attachmentsinfo)){
            $filesArray = json_decode($attachmentsinfo,true);
        }
        foreach ($files as $file){
            $uploadPath = getenv('TEMP_PATH');
            $fileNameWithoutExtension = GUID::generate();
            $fileName = $fileNameWithoutExtension . '.' . $file->getClientOriginalExtension();
            $file->move($uploadPath, $fileName);
            $uploadedFile[]	=	 array ("filename"=>$file->getClientOriginalName(),"filepath"=>$uploadPath . '/' . $fileName);
        }
        if(!empty($filesArray) && count($filesArray)>0) {
            $filesArray	=	array_merge($filesArray,$uploadedFile);
        } else {
            $filesArray	=	$uploadedFile;
        } 
		if(isset($data['add_type'])){$class="reply_del_attachment";}else{$class='del_attachment';}
        foreach($filesArray as $key=> $fileData) {
            $returnText  .= '<span class="file_upload_span imgspan_filecontrole">'.$fileData['filename'].'<a  del_file_name="'.$fileData['filename'].'" class="clickable '.$class.'"> X </a><br></span>';
        }
        return ['text'=>$returnText,'attachmentsinfo'=>$filesArray];
    }

    public static function DeleteUploadFileLocal($data){
        $file = $data['file'];
        unlink($file['filepath']);
    }
}