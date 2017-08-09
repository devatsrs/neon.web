<?php

class CompanyGateway extends \Eloquent {


    protected $guarded = array('CompanyGatewayID');

    protected $table = 'tblCompanyGateway';

    protected  $primaryKey = "CompanyGatewayID";

    /** add columns here to save in table  */
    protected $fillable = array(
        'CompanyID','GatewayID','Title','IP','Settings',
        'Status', 'CreatedBy', 'created_at','ModifiedBy','updated_at',
        'TimeZone', 'BillingTime', 'BillingTimeZone','UniqueID'
    );



    public static function checkForeignKeyById($id){
        $hasIngatewaycount =  GatewayAccount::where(array('CompanyGatewayID'=>$id))->count();

        if( intval($hasIngatewaycount) > 0 ){
            return true;
        }else{
            return false;
        }
    }
    public static function getCompanyGatewayIdList(){
        $row = CompanyGateway::where(array('Status'=>1,'CompanyID'=>User::get_companyID()))->lists('Title', 'CompanyGatewayID');
        if(!empty($row)){
            $row = array(""=> "Select")+$row;
        }
        return $row;

    }
    public static function getCompanyGatewayConfig($CompanyGatewayID){
        return CompanyGateway::where(array('Status'=>1,'CompanyGatewayID'=>$CompanyGatewayID))->pluck('Settings');
    }
    public static function getCompanyGatewayID($gatewayid){
        return CompanyGateway::where(array('GatewayID'=>$gatewayid,'CompanyID'=>User::get_companyID()))->pluck('CompanyGatewayID');
    }
    public static function getGatewayIDList($gatewayid){
        $row = CompanyGateway::where(array('Status'=>1,'GatewayID'=>$gatewayid,'CompanyID'=>User::get_companyID()))->lists('Title', 'CompanyGatewayID');
        if(!empty($row)){
            $row = array(""=> "Select")+$row;
        }
        return $row;

    }

    public static function getCompanyGatewayName($CompanyGatewayID){
        return CompanyGateway::where(array('CompanyGatewayID'=>$CompanyGatewayID))->pluck('Title');
    }

    public static function importgatewaylist(){
        $row = array();
        $gatewaylist = array();
        $companygateways = CompanyGateway::where(array('Status'=>1,'CompanyID'=>User::get_companyID()))->get();
        if(count($companygateways)>0){
            foreach($companygateways as $companygateway){
                if(!empty($companygateway['Settings'])){
                    $option = json_decode($companygateway['Settings']);
                    if(!empty($option->AllowAccountImport)){
                        $GatewayName = Gateway::getGatewayName($companygateway['GatewayID']);
                        $row['CompanyGatewayID'] = $companygateway['CompanyGatewayID'];
                        $row['Title'] = $companygateway['Title'];
                        $row['Gateway'] = $GatewayName;
                        $gatewaylist[] = $row;
                    }
                }
            }
        }
        return $gatewaylist;
    }

    public static function getMissingCompanyGatewayIdList(){
        $row = array();
        $companygateways = CompanyGateway::where(array('Status'=>1,'CompanyID'=>User::get_companyID()))->get();
        if(count($companygateways)>0){
            foreach($companygateways as $companygateway){
                if(!empty($companygateway['Settings'])){
                    $option = json_decode($companygateway['Settings']);
                    if(!empty($option->AllowAccountImport)){
                        $row[$companygateway['CompanyGatewayID']] = $companygateway['Title'];
                    }
                }
            }
        }
        if(!empty($row)){
            $row = array(""=> "Select")+$row;
        }
        return $row;
    }

    public static function getCompanyGatewayIDByName($Title){
        $companyID  	 = User::get_companyID();
        return CompanyGateway::where(array('Title'=>$Title,"CompanyID" => $companyID))->pluck('CompanyGatewayID');
    }

    public static function createCronJobsByCompanyGateway($CompanyGatewayID){
        $CompanyID = User::get_companyID();
        $CompanyGateway = CompanyGateway::find($CompanyGatewayID);
        $GatewayID = $CompanyGateway->GatewayID;
        if(!empty($GatewayID)){
            $GatewayName = Gateway::getGatewayName($GatewayID);

            if(isset($GatewayName) && $GatewayName == 'SippySFTP'){
                log::info($GatewayName);
                log::info('--SIPPYSFTP FILE DOWNLOAD CRONJOB START--');

                $DownloadCronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('sippydownloadcdr');
                $DownloadSetting = CompanyConfiguration::get('SIPPYSFTP_DOWNLOAD_CRONJOB');
                $DownloadJobTitle = $CompanyGateway->Title.' CDR File Download';
                $DownloadTag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $DownloadSettings = str_replace('"CompanyGatewayID":""',$DownloadTag,$DownloadSetting);

                log::info($DownloadSettings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$DownloadCronJobCommandID,$DownloadSettings,$DownloadJobTitle);
                log::info('--SIPPYSFTP FILE DOWNLOAD CRONJOB END--');

                log::info('--SIPPYSFTP FILE PROCESS CRONJOB START--');

                $ProcessCronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('sippyaccountusage');
                $ProcessSetting = CompanyConfiguration::get('SIPPYSFTP_PROCESS_CRONJOB');
                $ProcessJobTitle = $CompanyGateway->Title.' CDR File Process';
                $ProcessTag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $ProcessSettings = str_replace('"CompanyGatewayID":""',$ProcessTag,$ProcessSetting);

                log::info($ProcessSettings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$ProcessCronJobCommandID,$ProcessSettings,$ProcessJobTitle);
                log::info('--SIPPYSFTP FILE PROCESS CRONJOB END--');

                CompanyGateway::createSummaryCronJobs(1);

            }elseif(isset($GatewayName) && $GatewayName == 'VOS'){
                log::info($GatewayName);
                log::info('--VOS FILE DOWNLOAD CRONJOB START--');

                $DownloadCronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('vosdownloadcdr');
                $DownloadSetting = CompanyConfiguration::get('VOS_DOWNLOAD_CRONJOB');
                $DownloadJobTitle = $CompanyGateway->Title.' CDR File Download';
                $DownloadTag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $DownloadSettings = str_replace('"CompanyGatewayID":""',$DownloadTag,$DownloadSetting);

                log::info($DownloadSettings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$DownloadCronJobCommandID,$DownloadSettings,$DownloadJobTitle);
                log::info('--VOS FILE DOWNLOAD CRONJOB END--');

                log::info('--VOS FILE PROCESS CRONJOB START--');

                $ProcessCronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('vosaccountusage');
                $ProcessSetting = CompanyConfiguration::get('VOS_PROCESS_CRONJOB');
                $ProcessJobTitle = $CompanyGateway->Title.' CDR File Process';
                $ProcessTag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $ProcessSettings = str_replace('"CompanyGatewayID":""',$ProcessTag,$ProcessSetting);

                log::info($ProcessSettings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$ProcessCronJobCommandID,$ProcessSettings,$ProcessJobTitle);
                log::info('--VOS FILE PROCESS CRONJOB END--');

                CompanyGateway::createSummaryCronJobs(1);

            }elseif(isset($GatewayName) && $GatewayName == 'PBX'){
                log::info($GatewayName);
                log::info('--PBX CRONJOB START--');

                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('pbxaccountusage');
                $setting = CompanyConfiguration::get('PBX_CRONJOB');
                $JobTitle = $CompanyGateway->Title.' CDR Download';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);

                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                log::info('--PBX CRONJOB END--');

                CompanyGateway::createSummaryCronJobs(0);
            }elseif(isset($GatewayName) && $GatewayName == 'Porta'){
                log::info($GatewayName);
                log::info('--PORTA CRONJOB START--');

                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('portaaccountusage');
                $setting = CompanyConfiguration::get('PORTA_CRONJOB');
                $JobTitle = $CompanyGateway->Title.' CDR Download';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);

                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                log::info('--PORTA CRONJOB START--');

                CompanyGateway::createSummaryCronJobs(0);
            }elseif(isset($GatewayName) && $GatewayName == 'ManualCDR'){
                log::info($GatewayName);
                log::info('--ManualCDR CRONJOB START--');

                CompanyGateway::createSummaryCronJobs(0);

                log::info('--ManualCDR CRONJOB START--');
            }elseif(isset($GatewayName) && $GatewayName == 'MOR'){
                log::info($GatewayName);
                log::info('--MOR CRONJOB START--');

                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('moraccountusage');
                $setting = CompanyConfiguration::get('MOR_CRONJOB');
                $JobTitle = $CompanyGateway->Title.' CDR Download';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);

                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                log::info('--MOR CRONJOB END--');

                CompanyGateway::createSummaryCronJobs(1);
            }elseif(isset($GatewayName) && $GatewayName == 'CallShop'){
                log::info($GatewayName);
                log::info('--CallShop CRONJOB START--');

                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('callshopaccountusage');
                $setting = CompanyConfiguration::get('CALLSHOP_CRONJOB');
                $JobTitle = $CompanyGateway->Title.' CDR Download';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);

                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                CompanyGateway::createSummaryCronJobs(1);
                log::info('--CallShop CRONJOB END--');
            }elseif(isset($GatewayName) && $GatewayName == 'Streamco'){
                log::info($GatewayName);
                log::info('--Streamco download CDR CRONJOB START--');
                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('streamcoaccountusage');
                $setting = CompanyConfiguration::get('STREAMCO_DOWNLOAD_CDR_CRONJOB');
                $JobTitle = $CompanyGateway->Title.' CDR Download';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);
                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                log::info('--Streamco download CDR CRONJOB END--');

                log::info('--Streamco Customer Rate File Generation CRONJOB START--');
                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('customerratefileexport');
                $setting = CompanyConfiguration::get('STREAMCO_CUSTOMER_RATE_FILE_GEN_CRONJOB');
                $JobTitle = $CompanyGateway->Title.' Customer Rate File Generation';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);
                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                log::info('--Streamco Customer Rate File Generation CRONJOB END--');

                log::info('--Streamco Vendors Rate File Generation CRONJOB START--');
                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('vendorratefileexport');
                $setting = CompanyConfiguration::get('STREAMCO_VENDOR_RATE_FILE_GEN_CRONJOB');
                $JobTitle = $CompanyGateway->Title.' Vendors Rate File Generation';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);
                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                log::info('--Streamco Vendors Rate File Generation CRONJOB END--');

                log::info('--Streamco Customers Rate File Download CRONJOB START--');
                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('customerratefiledownload');
                $setting = CompanyConfiguration::get('STREAMCO_RATE_FILE_DOWNLOAD_CRONJOB');
                $JobTitle = $CompanyGateway->Title.' Customer Rate File Download';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);
                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                log::info('--Streamco Customers Rate File Download CRONJOB END--');

                log::info('--Streamco Vendors Rate File Download CRONJOB START--');
                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('vendorratefiledownload');
                $setting = CompanyConfiguration::get('STREAMCO_RATE_FILE_DOWNLOAD_CRONJOB');
                $JobTitle = $CompanyGateway->Title.' Vendor Rate File Download';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);
                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                log::info('--Streamco Vendors Rate File Download CRONJOB END--');

                log::info('--Streamco Customers Rate File Process CRONJOB START--');
                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('customerratefileprocess');
                $setting = CompanyConfiguration::get('STREAMCO_RATE_FILE_PROCESS_CRONJOB');
                $JobTitle = $CompanyGateway->Title.' Customer Rate File Process';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);
                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                log::info('--Streamco Customers Rate File Process CRONJOB END--');

                log::info('--Streamco Vendors Rate File Process CRONJOB START--');
                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('vendorratefileprocess');
                $setting = CompanyConfiguration::get('STREAMCO_RATE_FILE_PROCESS_CRONJOB');
                $JobTitle = $CompanyGateway->Title.' Vendor Rate File Process';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);
                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                log::info('--Streamco Vendors Rate File Process CRONJOB END--');

                log::info('--Streamco Account Import CRONJOB START--');
                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('streamcoaccountimport');
                $setting = CompanyConfiguration::get('STREAMCO_ACCOUNT_IMPORT');
                $JobTitle = $CompanyGateway->Title.' Account Import';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);
                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                log::info('--Streamco Account Import CRONJOB END--');

                log::info('--Streamco Customer Rate file Import CRONJOB START--');
                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('customerratefilegeneration');
                $setting = CompanyConfiguration::get('CUSTOMER_RATE_FILE_IMPORT_CRONJOB');
                $JobTitle = $CompanyGateway->Title.' Customer Rate file Import';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);
                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                log::info('--Streamco Customer Rate file Import CRONJOB END--');

                log::info('--Streamco Vendor Rate file Import CRONJOB START--');
                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('vendorratefilegeneration');
                $setting = CompanyConfiguration::get('VENDOR_RATE_FILE_IMPORT_CRONJOB');
                $JobTitle = $CompanyGateway->Title.' Vendor Rate file Import';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);
                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                log::info('--Streamco Vendor Rate file Import CRONJOB END--');

                log::info('--Streamco Vendor Rate file Import CRONJOB START--');
                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('ratefileexport');
                $setting = CompanyConfiguration::get('RATE_FILE_EXPORT_CRONJOB');
                $JobTitle = $CompanyGateway->Title.' Vendor Rate file Import';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);
                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                log::info('--Streamco Vendor Rate file Import CRONJOB END--');

                CompanyGateway::createSummaryCronJobs(0);
            }
        }else{
            log::info('--Other CRONJOB START--');

            CompanyGateway::createSummaryCronJobs(0);

            log::info('--Other CRONJOB START--');
        }
    }

    public static function createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle){
        $CompanyID = User::get_companyID();
        $today = date('Y-m-d');
        if(!empty($CompanyGatewayID)){
            $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
            $cronJobs_count = CronJob::where('Settings','LIKE', '%'.$tag.'%')->where(['CompanyID'=>$CompanyID,'CronJobCommandID'=>$CronJobCommandID])->count();
        }else{
            $cronJobs_count = CronJob::where(['CompanyID'=>$CompanyID,'CronJobCommandID'=>$CronJobCommandID])->count();
        }

        log::info('count - '.$cronJobs_count);
        if($cronJobs_count == 0 && !empty($settings)){
            $cronjobdata = array();
            $cronjobdata['CompanyID'] = $CompanyID;
            $cronjobdata['CronJobCommandID'] = $CronJobCommandID;
            $cronjobdata['Settings'] = $settings;
            $cronjobdata['Status'] = 1;
            $cronjobdata['created_by'] = User::get_user_full_name();
            $cronjobdata['created_at'] =  $today;
            $cronjobdata['JobTitle'] = $JobTitle;
            log::info($cronjobdata);
            CronJob::create($cronjobdata);
        }
    }

    public static function createSummaryCronJobs($type){
        $CompanyGatewayID = 0;
        log::info('--CUSTOMER SUMMARY DAILY CRONJOB START--');
        $CustomerSummaryDailyCommandID = CronJobCommand::getCronJobCommandIDByCommand('createsummary');
        $CustomerSummaryDailySetting = CompanyConfiguration::get('CUSTOMER_SUMMARYDAILY_CRONJOB');
        $CustomerSummaryDailyJobTitle = 'Create Customer Summary';
        log::info($CustomerSummaryDailySetting);
        CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CustomerSummaryDailyCommandID,$CustomerSummaryDailySetting,$CustomerSummaryDailyJobTitle);

        log::info('--CUSTOMER SUMMARY DAILY CRONJOB END--');

        log::info('--CUSTOMER SUMMARY LIVE CRONJOB START--');
        $CustomerSummaryLiveCommandID = CronJobCommand::getCronJobCommandIDByCommand('createsummarylive');
        $CustomerSummaryLiveSetting = CompanyConfiguration::get('CUSTOMER_SUMMARYLIVE_CRONJOB');
        $CustomerSummaryLiveJobTitle = 'Create Customer Summary Live';
        log::info($CustomerSummaryLiveSetting);
        CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CustomerSummaryLiveCommandID,$CustomerSummaryLiveSetting,$CustomerSummaryLiveJobTitle);

        log::info('--CUSTOMER SUMMARY LIVE CRONJOB END--');

        if($type=='1'){
            log::info('--VENDOR SUMMARY DAILY CRONJOB START--');
            $VendorSummaryDailyCommandID = CronJobCommand::getCronJobCommandIDByCommand('createvendorsummary');
            $VendorSummaryDailySetting = CompanyConfiguration::get('VENDOR_SUMMARYDAILY_CRONJOB');
            $VendorSummaryDailyJobTitle = 'Create Vendor Summary';
            log::info($VendorSummaryDailySetting);
            CompanyGateway::createGatewayCronJob($CompanyGatewayID,$VendorSummaryDailyCommandID,$VendorSummaryDailySetting,$VendorSummaryDailyJobTitle);

            log::info('--VENDOR SUMMARY DAILY CRONJOB END--');

            log::info('--VENDOR SUMMARY LIVE CRONJOB START--');
            $VendorSummaryLiveCommandID = CronJobCommand::getCronJobCommandIDByCommand('createvendorsummarylive');
            $VendorSummaryLiveSetting = CompanyConfiguration::get('VENDOR_SUMMARYLIVE_CRONJOB');
            $VendorSummaryLiveJobTitle = 'Create Vendor Summary Live';
            log::info($VendorSummaryLiveSetting);
            CompanyGateway::createGatewayCronJob($CompanyGatewayID,$VendorSummaryLiveCommandID,$VendorSummaryLiveSetting,$VendorSummaryLiveJobTitle);

            log::info('--VENDOR SUMMARY LIVE CRONJOB END--');
        }

    }

}