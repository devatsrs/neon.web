<?php

class CompanyGateway extends \Eloquent {
	protected $fillable = [];

    protected $guarded = array('CompanyGatewayID');

    protected $table = 'tblCompanyGateway';

    protected  $primaryKey = "CompanyGatewayID";



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
        return CompanyGateway::where(array('Title'=>$Title))->pluck('CompanyGatewayID');
    }

    public static function createCronJobsByCompanyGateway($CompanyGatewayID){
        $CompanyID = User::get_companyID();
        $CompanyGateway = CompanyGateway::find($CompanyGatewayID);
        $GatewayID = $CompanyGateway->GatewayID;
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