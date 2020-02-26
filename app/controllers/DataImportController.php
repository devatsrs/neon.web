<?php

class DataImportController extends \BaseController {

    public function index() {
        return View::make('dataimport.importrates');
    }

    public function uploadTemplateFile() {
        $data = Input::all();

        if (Input::hasFile('excel')) {
            $CompanyID      = User::get_companyID();
            $upload_path    = CompanyConfiguration::get('TEMP_PATH');
            $excel          = Input::file('excel');
            $ext            = $excel->getClientOriginalExtension();
            if (in_array(strtolower($ext), array("xls", "xlsx", "csv"))) {
                $file_name_without_ext = GUID::generate();
                $file_name = $file_name_without_ext . '.' . strtolower($excel->getClientOriginalExtension());
                $excel->move($upload_path, $file_name);
                $file_name = $upload_path . '/' . $file_name;

                $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['DATA_IMPORT']);

                $file_name          = basename($file_name);
                $temp_path          = CompanyConfiguration::get('TEMP_PATH').'/' ;
                $destinationPath    = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;
                copy($temp_path . $file_name, $destinationPath . $file_name);
                if (!AmazonS3::upload($destinationPath . $file_name, $amazonPath)) {
                    return Response::json(array("status" => "failed", "message" => "Failed to upload template file."));
                }
                $fullPath               = $amazonPath . $file_name; //$destinationPath . $file_name;
                $save['full_path']      = $fullPath;

                //Inserting Job Log
                try {
                    DB::beginTransaction();
                    $result = Job::logJob('DIFT', $save);
                    if ($result['status'] != "success") {
                        DB::rollback();
                        return json_encode(["status" => "failed", "message" => $result['message']]);
                    }
                    DB::commit();
                    //@unlink($temp_path . $file_name);
                    return json_encode(["status" => "success", "message" => "File Uploaded, File is added to queue for processing. You will be notified once file upload is completed. "]);
                } catch (Exception $ex) {
                    DB::rollback();
                    return json_encode(["status" => "failed", "message" => " Exception: " . $ex->getMessage()]);
                }
            } else {
                return Response::json(array("status" => "failed", "message" => "Please select excel file."));
            }
        } else {
            return Response::json(array("status" => "failed", "message" => "Please select excel file."));
        }
    }

}