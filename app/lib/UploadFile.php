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
            $uploadPath = CompanyConfiguration::get('TEMP_PATH');
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
	
	  public static function DownloadFileLocal($attachmentsinfo,$add_type=''){
        $filesArrayreturn = [];
        $uploadedFile = [];
        $returnText	='';
     
        $filesArray = unserialize($attachmentsinfo);  
       if(!is_array($filesArray)){return array();}
        foreach ($filesArray as $file){
			$ext = pathinfo($file['filepath'], PATHINFO_EXTENSION);
			$FileNewPathTEmp = str_replace(".".$ext,"-new",$file['filepath']);
			$FileNewPath    =  CompanyConfiguration::get('TEMP_PATH').'/'.$FileNewPathTEmp.".".$ext;
			$dirpath 		=  dirname($FileNewPath);			
			
			if (!file_exists($dirpath)){
                RemoteSSH::run("mkdir -p " . $dirpath);
                @mkdir($dirpath, 0777, TRUE);
                    //mkdir($dirpath, 0777, true);
             }			
            RemoteSSH::run("chmod -R 777 " . $dirpath);

			$Attachmenturl  =  AmazonS3::unSignedUrl($file['filepath']);  
			file_put_contents($FileNewPath,file_get_contents($Attachmenturl));
			$filesArrayreturn[]	=	array("filename"=>$file['filename'],"filepath"=>$FileNewPath);
		}
        
       if($add_type!=''){$class="reply_del_attachment";}else{$class='del_attachment';}
        foreach($filesArray as $key=> $fileData) {
            $returnText  .= '<span class="file_upload_span imgspan_filecontrole">'.$fileData['filename'].'<a  del_file_name="'.$fileData['filename'].'" class="clickable '.$class.'"> X </a><br></span>';
        }
        return ['text'=>$returnText,'attachmentsinfo'=>json_encode($filesArrayreturn)];
    }

	
	
}