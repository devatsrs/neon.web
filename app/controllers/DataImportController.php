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

    public function compareSpecialRates() {
        $Customers = Account::getCustomerAccountIDList();
        return View::make('dataimport.compare_special_rates', compact('Customers'));
    }

    public function getNumbersByCustomer($AccountID) {
        try {
            $Numbers = Account::join('tblAccountService', 'tblAccountService.AccountID', '=', 'tblAccount.AccountID')
                ->join('tblCLIRateTable', function ($join) {
                    $join->on('tblCLIRateTable.AccountID', '=', 'tblAccountService.AccountID');
                    $join->on('tblCLIRateTable.AccountServiceID', '=', 'tblAccountService.AccountServiceID');
                })
                ->where(['tblAccountService.Status' => 1, 'tblCLIRateTable.Status' => 1, 'tblAccount.AccountID' => $AccountID])
                ->select(array('tblCLIRateTable.CLIRateTableID', 'tblCLIRateTable.CLI'))
                ->lists('CLI', 'CLIRateTableID');

            //$Numbers = array(""=> "Select")+$Numbers;

            return json_encode(["status" => "success", "message" => "success", "Numbers" => $Numbers]);

        } catch (Exception $ex) {
            return json_encode(["status" => "failed", "message" => " Exception: " . $ex->getMessage()]);
        }
    }

    public function getSpecialRatesByNumber($CLIRateTableID,$Type) {
        try {
            $data   = Input::all();
            $result = [];

            $Rates = [];
            if($Type == RateTable::RATE_TABLE_TYPE_ACCESS) {
                $Rates = LegacyRateImport::getAccessSpecialRateComparison($CLIRateTableID);
            } else if($Type == RateTable::RATE_TABLE_TYPE_PACKAGE) {
                $Rates = LegacyRateImport::getPackageSpecialRateComparison($CLIRateTableID);
            } else if($Type == RateTable::RATE_TABLE_TYPE_TERMINATION) {
                $Rates = LegacyRateImport::getTerminationSpecialRateComparison($CLIRateTableID);
            }

            $totalcount     = count($Rates);
            $iDisplayStart  = $data['iDisplayStart'];
            $iDisplayLength = $data['iDisplayLength'];
            $aaData         = array_slice($Rates,$iDisplayStart,$iDisplayLength);

            $result['sEcho']                = $data['sEcho'];
            $result['iTotalRecords']        = $totalcount;
            $result['iTotalDisplayRecords'] = $totalcount;
            $result['aaData']               = $aaData;
            $result['Total']['totalcount']  = $totalcount;

            return json_encode($result);

        } catch (Exception $ex) {
            return json_encode(["status" => "failed", "message" => " Exception: " . $ex->getMessage()]);
        }
    }

    public function exportSpecialRates($CLIRateTableID) {
        try {
            $data   = Input::all();
            $data   = json_decode($data['data'],true);
            $result = [];

            $AccessRates        = LegacyRateImport::getAccessSpecialRateComparison($CLIRateTableID);
            $PackageRates       = LegacyRateImport::getPackageSpecialRateComparison($CLIRateTableID);
            $TerminationRates   = LegacyRateImport::getTerminationSpecialRateComparison($CLIRateTableID);

            foreach ($AccessRates as $key => $value) {
                foreach ($data['Access'] as $data_key => $data_value) {
                    if($value['Key'] == $data_value['Key'] && $value['component'] == $data_value['component']) {
                        $AccessRates[$key]['NewPrice'] = $data_value['NewRate'];
                    }
                }
            }
            foreach ($PackageRates as $key => $value) {
                foreach ($data['Package'] as $data_key => $data_value) {
                    if($value['Key'] == $data_value['Key'] && $value['component'] == $data_value['component']) {
                        $PackageRates[$key]['NewPrice'] = $data_value['NewRate'];
                    }
                }
            }
            foreach ($TerminationRates as $key => $value) {
                foreach ($data['Termination'] as $data_key => $data_value) {
                    if($value['Key'] == $data_value['Key']) {
                        $TerminationRates[$key]['NewPrice'] = $data_value['NewRate'];
                    }
                }
            }

            $excel_data['Access']      = $AccessRates;
            $excel_data['Package']     = $PackageRates;
            $excel_data['Termination'] = $TerminationRates;
            $excel_data = json_decode(json_encode($excel_data), true);

            $file_path =  CompanyConfiguration::get('UPLOAD_PATH') .'/legacy_special_rates.xls';
            $NeonExcel = new NeonExcelIO($file_path);
            $NeonExcel->write_multi_sheet_excel($excel_data);

            return json_encode($result);

        } catch (Exception $ex) {
            return json_encode(["status" => "failed", "message" => " Exception: " . $ex->getMessage()]);
        }
    }

    public function importSpecialRates() {
        $data = Input::all();

        $rules['Customer']  = 'required';
        $rules['Number']    = 'required';
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return json_validator_response($validator);
        }

        if (Input::hasFile('excel')) {
            $upload_path    = CompanyConfiguration::get('TEMP_PATH');
            $excel          = Input::file('excel');
            $ext            = $excel->getClientOriginalExtension();
            if (in_array(strtolower($ext), array("xls"))) {
                $file_name_without_ext = GUID::generate();
                $file_name = $file_name_without_ext . '.' . strtolower($excel->getClientOriginalExtension());
                $excel->move($upload_path, $file_name);
                $file_name = $upload_path . '/' . $file_name;

                $amazonPath = AmazonS3::generate_upload_path(AmazonS3::$dir['SPECIAL_RATE_IMPORT']);

                $file_name          = basename($file_name);
                $temp_path          = CompanyConfiguration::get('TEMP_PATH').'/' ;
                $destinationPath    = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $amazonPath;
                copy($temp_path . $file_name, $destinationPath . $file_name);
                if (!AmazonS3::upload($destinationPath . $file_name, $amazonPath)) {
                    return Response::json(array("status" => "failed", "message" => "Failed to upload template file."));
                }
                $fullPath               = $amazonPath . $file_name; //$destinationPath . $file_name;
                $save['full_path']      = $fullPath;
                $save['Customer']       = $data['Customer'];
                $save['Number']         = $data['Number'];

                //Inserting Job Log
                try {
                    DB::beginTransaction();
                    $result = Job::logJob('CSRI', $save);
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
                return Response::json(array("status" => "failed", "message" => "Only .xls file is allowed which is exported from Neon."));
            }
        } else {
            return Response::json(array("status" => "failed", "message" => "Please select excel file."));
        }
    }

    public function download_exported_file(){
        $filePath =  CompanyConfiguration::get('UPLOAD_PATH') .'/legacy_special_rates.xls';
        download_file($filePath);
    }

}