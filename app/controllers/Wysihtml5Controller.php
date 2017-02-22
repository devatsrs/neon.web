<?php
use Carbon\Carbon;
class Wysihtml5Controller extends \BaseController {



    public function getfiles(){
        $data = Input::all();
        $CompanyID = User::get_companyID();
        $select = ["UploadedFilePath"];
        $files = UploadedFiles::where(['CompanyID'=>$CompanyID])->select($select);
        return Datatables::of($files)
            ->edit_column('UploadedFilePath',function($row){
                $path = AmazonS3::unSignedUrl($row->UploadedFilePath);
                if (!is_numeric(strpos($path, "https://"))) {
                    //$path = str_replace('/', '\\', $path);
                    if (copy($path, './uploads/' . basename($path))) {
                        $path = URL::to('/') . '/uploads/' . basename($path);
                    }
                }

                return $path;
            })->make();
    }

	/**
	 * Store a newly created resource in storage.
	 * POST /accountsubscription
	 *
	 * @return Response
	 */
	public function file_upload()
	{
		$data = Input::all();
        if (Input::hasFile('file')) {

            $CompanyID = User::get_companyID();
            $file = Input::file('file'); // ->move($destinationPath);
            $ext = $file->getClientOriginalExtension();

            $file_name = "Wysihtml5_". GUID::generate() . '.' . $ext;
            $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['WYSIHTML5_FILE_UPLOAD']) ;
            $destinationPath = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;
            $file->move($destinationPath, $file_name);
            if(!AmazonS3::upload($destinationPath.$file_name,$amazonPath)){
                return Response::json(array("status" => "failed", "message" => "Failed to upload."));
            }
            $fullPath = $amazonPath . $file_name;

            $data['full_path'] = $fullPath;

            $File_data = array();
            $File_data["UploadedFileName"] = basename($data["full_path"]);
            $File_data["UploadedFilePath"] = $data["full_path"] ;
            $File_data["CompanyID"] = $CompanyID;
            $File_data["UserID"] = User::get_userID();
            $File_data["UploadedFileHttpPath"] = 0;
            $File_data["CreatedBy"] = User::get_user_full_name();

            if(UploadedFiles::create($File_data)){
                return Response::json(array("status" => "success", "message" => "File uploaded successfully"));
            }else{
                return Response::json(array("status" => "failed", "message" => "Problem Saving Record"));
            }

        } else {
            return Response::json(array("status" => "failed", "message" => "Please upload file <5MB."));
        }
	}
}