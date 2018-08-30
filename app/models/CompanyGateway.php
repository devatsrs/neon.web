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
    public static function getCompanyGatewayIdList($CompanyID=0){
        $company_id = $CompanyID>0?$CompanyID : User::get_companyID();
        $row = CompanyGateway::where(array('Status'=>1,'CompanyID'=>$company_id))->lists('Title', 'CompanyGatewayID');
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

    public static function importIPGatewayList(){
        $row = array();
        $gatewaylist = array();
        $companygateways = CompanyGateway::where(array('Status'=>1,'CompanyID'=>User::get_companyID()))->get();
        if(count($companygateways)>0){
            foreach($companygateways as $companygateway){
                if(!empty($companygateway['Settings'])){
                    $option = json_decode($companygateway['Settings']);
                    if(!empty($option->AllowAccountIPImport)){
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
        //$CompanyID = User::get_companyID();
        $CompanyGateway = CompanyGateway::find($CompanyGatewayID);
        $CompanyID = $CompanyGateway->CompanyID;
        $GatewayID = $CompanyGateway->GatewayID;
        if(!empty($GatewayID)){
            $GatewayName = Gateway::getGatewayName($GatewayID);

            if(isset($GatewayName) && $GatewayName == 'SippySFTP'){
                log::info($GatewayName);
                log::info('--SIPPYSFTP FILE DOWNLOAD CRONJOB START--');

                $DownloadCronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('sippydownloadcdr',$CompanyID);
                $DownloadSetting = CompanyConfiguration::getValueConfigurationByKey('SIPPYSFTP_DOWNLOAD_CRONJOB',$CompanyID);
                $DownloadJobTitle = $CompanyGateway->Title.' CDR File Download';
                $DownloadTag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $DownloadSettings = str_replace('"CompanyGatewayID":""',$DownloadTag,$DownloadSetting);

                log::info($DownloadSettings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$DownloadCronJobCommandID,$DownloadSettings,$DownloadJobTitle);
                log::info('--SIPPYSFTP FILE DOWNLOAD CRONJOB END--');

                log::info('--SIPPYSFTP FILE PROCESS CRONJOB START--');

                $ProcessCronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('sippyaccountusage',$CompanyID);
                $ProcessSetting = CompanyConfiguration::getValueConfigurationByKey('SIPPYSFTP_PROCESS_CRONJOB',$CompanyID);
                $ProcessJobTitle = $CompanyGateway->Title.' CDR File Process';
                $ProcessTag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $ProcessSettings = str_replace('"CompanyGatewayID":""',$ProcessTag,$ProcessSetting);

                log::info($ProcessSettings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$ProcessCronJobCommandID,$ProcessSettings,$ProcessJobTitle);
                log::info('--SIPPYSFTP FILE PROCESS CRONJOB END--');

                CompanyGateway::createSummaryCronJobs(1,$CompanyID);

            }elseif(isset($GatewayName) && $GatewayName == 'VOS'){
                log::info($GatewayName);
                log::info('--VOS FILE DOWNLOAD CRONJOB START--');

                $DownloadCronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('vosdownloadcdr',$CompanyID);
                $DownloadSetting = CompanyConfiguration::getValueConfigurationByKey('VOS_DOWNLOAD_CRONJOB',$CompanyID);
                $DownloadJobTitle = $CompanyGateway->Title.' CDR File Download';
                $DownloadTag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $DownloadSettings = str_replace('"CompanyGatewayID":""',$DownloadTag,$DownloadSetting);

                log::info($DownloadSettings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$DownloadCronJobCommandID,$DownloadSettings,$DownloadJobTitle);
                log::info('--VOS FILE DOWNLOAD CRONJOB END--');

                log::info('--VOS FILE PROCESS CRONJOB START--');

                $ProcessCronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('vosaccountusage',$CompanyID);
                $ProcessSetting = CompanyConfiguration::getValueConfigurationByKey('VOS_PROCESS_CRONJOB',$CompanyID);
                $ProcessJobTitle = $CompanyGateway->Title.' CDR File Process';
                $ProcessTag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $ProcessSettings = str_replace('"CompanyGatewayID":""',$ProcessTag,$ProcessSetting);

                log::info($ProcessSettings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$ProcessCronJobCommandID,$ProcessSettings,$ProcessJobTitle);
                log::info('--VOS FILE PROCESS CRONJOB END--');

                CompanyGateway::createSummaryCronJobs(1,$CompanyID);

            }elseif(isset($GatewayName) && $GatewayName == 'PBX'){
                log::info($GatewayName);
                log::info('--PBX CRONJOB START--');

                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('pbxaccountusage',$CompanyID);
                $setting = CompanyConfiguration::getValueConfigurationByKey('PBX_CRONJOB',$CompanyID);
                $JobTitle = $CompanyGateway->Title.' CDR Download';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);

                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                log::info('--PBX CRONJOB END--');
                log::info('--PBX Reseller CRONJOB START--');

                $ResellerCronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('resellerpbxaccountusage',$CompanyID);
                $Resellersetting = CompanyConfiguration::getValueConfigurationByKey('PBX_RESELLER_CRONJOB',$CompanyID);
                $ResellerJobTitle = $CompanyGateway->Title.' Reseller CDR Process';
                $Resellertag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $Resellersettings = str_replace('"CompanyGatewayID":""',$Resellertag,$Resellersetting);

                log::info($Resellersettings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$ResellerCronJobCommandID,$Resellersettings,$ResellerJobTitle);

                log::info('--PBX Reselller CRONJOB END--');

                CompanyGateway::createSummaryCronJobs(0,$CompanyID);
            }elseif(isset($GatewayName) && $GatewayName == 'Porta'){
                log::info($GatewayName);
                log::info('--PORTA CRONJOB START--');

                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('portaaccountusage',$CompanyID);
                $setting = CompanyConfiguration::getValueConfigurationByKey('PORTA_CRONJOB',$CompanyID);
                $JobTitle = $CompanyGateway->Title.' CDR Download';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);

                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                log::info('--PORTA CRONJOB START--');

                CompanyGateway::createSummaryCronJobs(0,$CompanyID);
            }elseif(isset($GatewayName) && $GatewayName == 'ManualCDR'){
                log::info($GatewayName);
                log::info('--ManualCDR CRONJOB START--');

                CompanyGateway::createSummaryCronJobs(0,$CompanyID);

                log::info('--ManualCDR CRONJOB START--');
            }elseif(isset($GatewayName) && $GatewayName == 'MOR'){
                log::info($GatewayName);
                log::info('--MOR CRONJOB START--');

                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('moraccountusage',$CompanyID);
                $setting = CompanyConfiguration::getValueConfigurationByKey('MOR_CRONJOB',$CompanyID);
                $JobTitle = $CompanyGateway->Title.' CDR Download';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);

                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                log::info('--MOR CRONJOB END--');

                CompanyGateway::createSummaryCronJobs(1,$CompanyID);
            }elseif(isset($GatewayName) && $GatewayName == 'CallShop'){
                log::info($GatewayName);
                log::info('--CallShop CRONJOB START--');

                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('callshopaccountusage',$CompanyID);
                $setting = CompanyConfiguration::getValueConfigurationByKey('CALLSHOP_CRONJOB',$CompanyID);
                $JobTitle = $CompanyGateway->Title.' CDR Download';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);

                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                CompanyGateway::createSummaryCronJobs(1,$CompanyID);
                log::info('--CallShop CRONJOB END--');
            }elseif(isset($GatewayName) && $GatewayName == 'Streamco'){
                log::info($GatewayName);
                log::info('--Streamco download CDR CRONJOB START--');
                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('streamcoaccountusage',$CompanyID);
                $setting = CompanyConfiguration::getValueConfigurationByKey('STREAMCO_DOWNLOAD_CDR_CRONJOB',$CompanyID);
                $JobTitle = $CompanyGateway->Title.' CDR Download';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);
                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                log::info('--Streamco download CDR CRONJOB END--');

                log::info('--Streamco Customer Rate File Export CRONJOB START--');
                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('customerratefileexport',$CompanyID);
                $setting = CompanyConfiguration::getValueConfigurationByKey('STREAMCO_CUSTOMER_RATE_FILE_GEN_CRONJOB',$CompanyID);
                $JobTitle = $CompanyGateway->Title.' Customer Rate File Export';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);
                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                log::info('--Streamco Customer Rate File Export CRONJOB END--');

                log::info('--Streamco Vendors Rate File Export CRONJOB START--');
                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('vendorratefileexport',$CompanyID);
                $setting = CompanyConfiguration::getValueConfigurationByKey('STREAMCO_VENDOR_RATE_FILE_GEN_CRONJOB',$CompanyID);
                $JobTitle = $CompanyGateway->Title.' Vendors Rate File Export';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);
                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                log::info('--Streamco Vendors Rate File Export CRONJOB END--');

                log::info('--Streamco Customers Rate File Download CRONJOB START--');
                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('customerratefiledownload',$CompanyID);
                $setting = CompanyConfiguration::getValueConfigurationByKey('STREAMCO_RATE_FILE_DOWNLOAD_CRONJOB',$CompanyID);
                $JobTitle = $CompanyGateway->Title.' Customer Rate File Download';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);
                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                log::info('--Streamco Customers Rate File Download CRONJOB END--');

                log::info('--Streamco Vendors Rate File Download CRONJOB START--');
                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('vendorratefiledownload',$CompanyID);
                $setting = CompanyConfiguration::getValueConfigurationByKey('STREAMCO_RATE_FILE_DOWNLOAD_CRONJOB',$CompanyID);
                $JobTitle = $CompanyGateway->Title.' Vendor Rate File Download';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);
                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                log::info('--Streamco Vendors Rate File Download CRONJOB END--');

                log::info('--Streamco Customers Rate File Process CRONJOB START--');
                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('customerratefileprocess',$CompanyID);
                $setting = CompanyConfiguration::getValueConfigurationByKey('STREAMCO_RATE_FILE_PROCESS_CRONJOB',$CompanyID);
                $JobTitle = $CompanyGateway->Title.' Customer Rate File Process';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);
                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                log::info('--Streamco Customers Rate File Process CRONJOB END--');

                log::info('--Streamco Vendors Rate File Process CRONJOB START--');
                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('vendorratefileprocess',$CompanyID);
                $setting = CompanyConfiguration::getValueConfigurationByKey('STREAMCO_RATE_FILE_PROCESS_CRONJOB',$CompanyID);
                $JobTitle = $CompanyGateway->Title.' Vendor Rate File Process';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);
                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                log::info('--Streamco Vendors Rate File Process CRONJOB END--');

                log::info('--Streamco Account Import CRONJOB START--');
                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('streamcoaccountimport',$CompanyID);
                $setting = CompanyConfiguration::getValueConfigurationByKey('STREAMCO_ACCOUNT_IMPORT',$CompanyID);
                $JobTitle = $CompanyGateway->Title.' Account Import';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);
                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                log::info('--Streamco Account Import CRONJOB END--');

                log::info('--Streamco Customer Rate file Import CRONJOB START--');
                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('customerratefilegeneration',$CompanyID);
                $setting = CompanyConfiguration::getValueConfigurationByKey('CUSTOMER_RATE_FILE_IMPORT_CRONJOB',$CompanyID);
                $JobTitle = $CompanyGateway->Title.' Customer Rate file Import';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);
                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                log::info('--Streamco Customer Rate file Import CRONJOB END--');

                log::info('--Streamco Vendor Rate file Import CRONJOB START--');
                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('vendorratefilegeneration',$CompanyID);
                $setting = CompanyConfiguration::getValueConfigurationByKey('VENDOR_RATE_FILE_IMPORT_CRONJOB',$CompanyID);
                $JobTitle = $CompanyGateway->Title.' Vendor Rate file Import';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);
                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                log::info('--Streamco Vendor Rate file Import CRONJOB END--');

                log::info('--Streamco Rate File Export CRONJOB START--');
                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('ratefileexport',$CompanyID);
                $setting = CompanyConfiguration::getValueConfigurationByKey('RATE_FILE_EXPORT_CRONJOB',$CompanyID);
                $JobTitle = $CompanyGateway->Title.' Rate File Export';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);
                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                log::info('--Streamco Rate File Export Import CRONJOB END--');

                CompanyGateway::createSummaryCronJobs(0,$CompanyID);
            }elseif(isset($GatewayName) && $GatewayName == 'FusionPBX'){
                log::info($GatewayName);
                log::info('--FusionPBX CRONJOB START--');

                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('fusionpbxaccountusage',$CompanyID);
                $setting = CompanyConfiguration::getValueConfigurationByKey('FUSION_PBX_CRONJOB',$CompanyID);
                $JobTitle = $CompanyGateway->Title.' CDR Download';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);

                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                log::info('--FusionPBX CRONJOB END--');

                CompanyGateway::createSummaryCronJobs(0,$CompanyID);
            } elseif(isset($GatewayName) && $GatewayName == 'M2') {
                log::info($GatewayName);
                log::info('--M2 CRONJOB START--');

                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('m2accountusage',$CompanyID);
                $setting = CompanyConfiguration::getValueConfigurationByKey('M2_CRONJOB',$CompanyID);
                $JobTitle = $CompanyGateway->Title.' CDR Download';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);

                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                log::info('--M2 CRONJOB END--');

                CompanyGateway::createSummaryCronJobs(1,$CompanyID);
            }elseif(isset($GatewayName) && $GatewayName == 'VoipNow'){
                log::info($GatewayName);
                log::info('--VOIPNOW CRONJOB START--');

                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('voipnowaccountusage',$CompanyID);
                $setting = CompanyConfiguration::getValueConfigurationByKey('VIOPNOW_PBX_CRONJOB',$CompanyID);
                $JobTitle = $CompanyGateway->Title.' CDR Download';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);

                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                log::info('--VOIPNOW CRONJOB END--');

                CompanyGateway::createSummaryCronJobs(0,$CompanyID);
            }elseif(isset($GatewayName) && $GatewayName == 'VOS5000'){
                log::info($GatewayName);
                log::info('--VOS5000 FILE DOWNLOAD CRONJOB START--');

                $DownloadCronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('vos5000downloadcdr',$CompanyID);
                $DownloadSetting = CompanyConfiguration::getValueConfigurationByKey('VOS5000_DOWNLOAD_CRONJOB',$CompanyID);
                $DownloadJobTitle = $CompanyGateway->Title.' CDR File Download';
                $DownloadTag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $DownloadSettings = str_replace('"CompanyGatewayID":""',$DownloadTag,$DownloadSetting);

                log::info($DownloadSettings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$DownloadCronJobCommandID,$DownloadSettings,$DownloadJobTitle);
                log::info('--VOS5000 FILE DOWNLOAD CRONJOB END--');

                log::info('--VOS5000 FILE PROCESS CRONJOB START--');

                $ProcessCronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('vos5000accountusage',$CompanyID);
                $ProcessSetting = CompanyConfiguration::getValueConfigurationByKey('VOS_PROCESS_CRONJOB',$CompanyID);
                $ProcessJobTitle = $CompanyGateway->Title.' CDR File Process';
                $ProcessTag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $ProcessSettings = str_replace('"CompanyGatewayID":""',$ProcessTag,$ProcessSetting);

                log::info($ProcessSettings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$ProcessCronJobCommandID,$ProcessSettings,$ProcessJobTitle);
                log::info('--VOS5000 FILE PROCESS CRONJOB END--');

                CompanyGateway::createSummaryCronJobs(1,$CompanyID);

            }elseif(isset($GatewayName) && $GatewayName == 'VoipMS'){
                log::info($GatewayName);
                log::info('--VoipMS CRONJOB START--');

                $CronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('voipmsaccountusage',$CompanyID);
                $setting = CompanyConfiguration::getValueConfigurationByKey('VOIPMS_CRONJOB',$CompanyID);
                $JobTitle = $CompanyGateway->Title.' CDR Download';
                $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
                $settings = str_replace('"CompanyGatewayID":""',$tag,$setting);

                log::info($settings);
                CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle);
                log::info('--VoipMS CRONJOB START--');

                CompanyGateway::createSummaryCronJobs(0,$CompanyID);
            }
        }else{
            log::info('--Other CRONJOB START--');

            CompanyGateway::createSummaryCronJobs(0,$CompanyID);

            log::info('--Other CRONJOB START--');
        }
    }

    public static function createGatewayCronJob($CompanyGatewayID,$CronJobCommandID,$settings,$JobTitle){
        $CronJobCommand = CronJobCommand::find($CronJobCommandID);
        $CompanyID = $CronJobCommand->CompanyID;
        $today = date('Y-m-d');
        $Status = 1;
        if(!empty($CompanyGatewayID)){
            $tag = '"CompanyGatewayID":"'.$CompanyGatewayID.'"';
            $cronJobs_count = CronJob::where('Settings','LIKE', '%'.$tag.'%')->where(['CompanyID'=>$CompanyID,'CronJobCommandID'=>$CronJobCommandID])->count();
            $CompanyGateway = CompanyGateway::find($CompanyGatewayID);
            $Status = $CompanyGateway->Status;
        }else{
            $cronJobs_count = CronJob::where(['CompanyID'=>$CompanyID,'CronJobCommandID'=>$CronJobCommandID])->count();
        }

        log::info('count - '.$cronJobs_count);
        if($cronJobs_count == 0 && !empty($settings)){
            $cronjobdata = array();
            $cronjobdata['CompanyID'] = $CompanyID;
            $cronjobdata['CronJobCommandID'] = $CronJobCommandID;
            $cronjobdata['Settings'] = $settings;
            $cronjobdata['Status'] = $Status;
            $cronjobdata['created_by'] = User::get_user_full_name();
            $cronjobdata['created_at'] =  $today;
            $cronjobdata['JobTitle'] = $JobTitle;
            log::info($cronjobdata);
            CronJob::create($cronjobdata);
        }
    }

    public static function createSummaryCronJobs($type,$CompanyID){
        $CompanyGatewayID = 0;
        log::info('--CUSTOMER SUMMARY DAILY CRONJOB START--');
        $CustomerSummaryDailyCommandID = CronJobCommand::getCronJobCommandIDByCommand('createsummary',$CompanyID);
        $CustomerSummaryDailySetting = CompanyConfiguration::getValueConfigurationByKey('CUSTOMER_SUMMARYDAILY_CRONJOB',$CompanyID);
        $CustomerSummaryDailyJobTitle = 'Create Customer Summary';
        log::info($CustomerSummaryDailySetting);
        CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CustomerSummaryDailyCommandID,$CustomerSummaryDailySetting,$CustomerSummaryDailyJobTitle);

        log::info('--CUSTOMER SUMMARY DAILY CRONJOB END--');

        log::info('--CUSTOMER SUMMARY LIVE CRONJOB START--');
        $CustomerSummaryLiveCommandID = CronJobCommand::getCronJobCommandIDByCommand('createsummarylive',$CompanyID);
        $CustomerSummaryLiveSetting = CompanyConfiguration::getValueConfigurationByKey('CUSTOMER_SUMMARYLIVE_CRONJOB',$CompanyID);
        $CustomerSummaryLiveJobTitle = 'Create Customer Summary Live';
        log::info($CustomerSummaryLiveSetting);
        CompanyGateway::createGatewayCronJob($CompanyGatewayID,$CustomerSummaryLiveCommandID,$CustomerSummaryLiveSetting,$CustomerSummaryLiveJobTitle);

        log::info('--CUSTOMER SUMMARY LIVE CRONJOB END--');

        if($type=='1'){
           /* log::info('--VENDOR SUMMARY DAILY CRONJOB START--');
            $VendorSummaryDailyCommandID = CronJobCommand::getCronJobCommandIDByCommand('createvendorsummary',$CompanyID);
            $VendorSummaryDailySetting = CompanyConfiguration::getValueConfigurationByKey('VENDOR_SUMMARYDAILY_CRONJOB',$CompanyID);
            $VendorSummaryDailyJobTitle = 'Create Vendor Summary';
            log::info($VendorSummaryDailySetting);
            CompanyGateway::createGatewayCronJob($CompanyGatewayID,$VendorSummaryDailyCommandID,$VendorSummaryDailySetting,$VendorSummaryDailyJobTitle);*/

            log::info('--VENDOR SUMMARY DAILY CRONJOB END--');

            log::info('--VENDOR SUMMARY LIVE CRONJOB START--');
            $VendorSummaryLiveCommandID = CronJobCommand::getCronJobCommandIDByCommand('createvendorsummarylive',$CompanyID);
            $VendorSummaryLiveSetting = CompanyConfiguration::getValueConfigurationByKey('VENDOR_SUMMARYLIVE_CRONJOB',$CompanyID);
            $VendorSummaryLiveJobTitle = 'Create Vendor Summary Live';
            log::info($VendorSummaryLiveSetting);
            CompanyGateway::createGatewayCronJob($CompanyGatewayID,$VendorSummaryLiveCommandID,$VendorSummaryLiveSetting,$VendorSummaryLiveJobTitle);

            log::info('--VENDOR SUMMARY LIVE CRONJOB END--');
        }

    }

    public static function createDefaultCronJobs($CompanyID){
        log::info('-- Active CronJob --');
        $today = date('Y-m-d');
        $ActiveCronJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('activecronjobemail',$CompanyID);
        $ActiveCronJob_Count = CronJob::where(['CompanyID'=>$CompanyID,'CronJobCommandID'=>$ActiveCronJobCommandID])->count();
        if($ActiveCronJob_Count == 0) {
            $ActiveCronJobTitle = 'Active Cron Job Email';
            $ActiveCronJobSetting = '{"AlertEmailInterval":"60","SuccessEmail":"","ErrorEmail":"","JobTime":"MINUTE","JobInterval":"1","JobDay":["SUN","MON","TUE","WED","THU","FRI","SAT"],"JobStartTime":"12:00:00 AM"}';
            $ActiveCronJobdata = array();
            $ActiveCronJobdata['CompanyID'] = $CompanyID;
            $ActiveCronJobdata['CronJobCommandID'] = $ActiveCronJobCommandID;
            $ActiveCronJobdata['Settings'] = $ActiveCronJobSetting;
            $ActiveCronJobdata['Status'] = 1;
            $ActiveCronJobdata['created_by'] = 'system';
            $ActiveCronJobdata['created_at'] = $today;
            $ActiveCronJobdata['JobTitle'] = $ActiveCronJobTitle;
            log::info($ActiveCronJobdata);
            CronJob::create($ActiveCronJobdata);
        }
        log::info('-- Active CronJob END--');

        log::info('-- Activity Reminder --');
        $ActivityReminderJobCommandID = CronJobCommand::getCronJobCommandIDByCommand('accountactivityreminder',$CompanyID);
        $ActivityReminder_Count = CronJob::where(['CompanyID'=>$CompanyID,'CronJobCommandID'=>$ActivityReminderJobCommandID])->count();
        if($ActivityReminder_Count == 0) {
            $ActivityReminderJobTitle = 'Activity Reminder';
            $ActivityReminderSetting = '{"ThresholdTime":"60","SuccessEmail":"","ErrorEmail":"","JobTime":"DAILY","JobInterval":"1","JobDay":["SUN","MON","TUE","WED","THU","FRI","SAT"],"JobStartTime":"8:00:00 AM"}';
            $ActivityReminderdata = array();
            $ActivityReminderdata['CompanyID'] = $CompanyID;
            $ActivityReminderdata['CronJobCommandID'] = $ActivityReminderJobCommandID;
            $ActivityReminderdata['Settings'] = $ActivityReminderSetting;
            $ActivityReminderdata['Status'] = 1;
            $ActivityReminderdata['created_by'] = 'system';
            $ActivityReminderdata['created_at'] = $today;
            $ActivityReminderdata['JobTitle'] = $ActivityReminderJobTitle;
            log::info($ActivityReminderdata);
            CronJob::create($ActivityReminderdata);
        }
        log::info('-- Activity Reminder END--');

        log::info('-- Auto Invoice Generator --');
        $AutoInvoiceGeneratorCommandID = CronJobCommand::getCronJobCommandIDByCommand('invoicegenerator',$CompanyID);
        $AutoInvoiceGenerator_Count = CronJob::where(['CompanyID'=>$CompanyID,'CronJobCommandID'=>$AutoInvoiceGeneratorCommandID])->count();
        if($AutoInvoiceGenerator_Count == 0) {
            $AutoInvoiceGeneratorJobTitle = 'Auto Invoice Generator';
            $AutoInvoiceGeneratorSetting = '{"ThresholdTime":"120","SuccessEmail":"","ErrorEmail":"","JobTime":"DAILY","JobInterval":"1","JobDay":["SUN","MON","TUE","WED","THU","FRI","SAT"],"JobStartTime":"7:00:00 AM"}';
            $AutoInvoiceGeneratordata = array();
            $AutoInvoiceGeneratordata['CompanyID'] = $CompanyID;
            $AutoInvoiceGeneratordata['CronJobCommandID'] = $AutoInvoiceGeneratorCommandID;
            $AutoInvoiceGeneratordata['Settings'] = $AutoInvoiceGeneratorSetting;
            $AutoInvoiceGeneratordata['Status'] = 1;
            $AutoInvoiceGeneratordata['created_by'] = 'system';
            $AutoInvoiceGeneratordata['created_at'] = $today;
            $AutoInvoiceGeneratordata['JobTitle'] = $AutoInvoiceGeneratorJobTitle;
            log::info($AutoInvoiceGeneratordata);
            CronJob::create($AutoInvoiceGeneratordata);
        }
        log::info('-- Auto Invoice Generator END--');
        log::info('-- Create Summary --');
        $CreateSummaryCommandID = CronJobCommand::getCronJobCommandIDByCommand('createsummary',$CompanyID);
        $CreateSummary_Count = CronJob::where(['CompanyID'=>$CompanyID,'CronJobCommandID'=>$CreateSummaryCommandID])->count();
        if($CreateSummary_Count == 0) {
            $CreateSummaryJobTitle = 'Create Summary';
            $CreateSummarySetting = '{"StartDate":"","EndDate":"","SuccessEmail":"","ErrorEmail":"","ThresholdTime":"500","JobTime":"DAILY","JobInterval":"1","JobDay":["SUN","MON","TUE","WED","THU","FRI","SAT"],"JobStartTime":"2:00:00 AM"}';
            $CreateSummarydata = array();
            $CreateSummarydata['CompanyID'] = $CompanyID;
            $CreateSummarydata['CronJobCommandID'] = $CreateSummaryCommandID;
            $CreateSummarydata['Settings'] = $CreateSummarySetting;
            $CreateSummarydata['Status'] = 1;
            $CreateSummarydata['created_by'] = 'system';
            $CreateSummarydata['created_at'] = $today;
            $CreateSummarydata['JobTitle'] = $CreateSummaryJobTitle;
            log::info($CreateSummarydata);
            CronJob::create($CreateSummarydata);
        }
        log::info('-- Create Summary END--');
        log::info('-- Create Summary Live --');
        $CreateSummaryLiveCommandID = CronJobCommand::getCronJobCommandIDByCommand('createsummarylive',$CompanyID);
        $CreateSummaryLive_Count = CronJob::where(['CompanyID'=>$CompanyID,'CronJobCommandID'=>$CreateSummaryLiveCommandID])->count();
        if($CreateSummaryLive_Count == 0) {
            $CreateSummaryLiveJobTitle = 'Create Customer Summary Live';
            $CreateSummaryLiveSetting = '{"ThresholdTime":"30","SuccessEmail":"","ErrorEmail":"","JobTime":"MINUTE","JobInterval":"5","JobDay":["SUN","MON","TUE","WED","THU","FRI","SAT"],"JobStartTime":"12:00:00 AM"}';
            $CreateSummaryLivedata = array();
            $CreateSummaryLivedata['CompanyID'] = $CompanyID;
            $CreateSummaryLivedata['CronJobCommandID'] = $CreateSummaryLiveCommandID;
            $CreateSummaryLivedata['Settings'] = $CreateSummaryLiveSetting;
            $CreateSummaryLivedata['Status'] = 1;
            $CreateSummaryLivedata['created_by'] = 'system';
            $CreateSummaryLivedata['created_at'] = $today;
            $CreateSummaryLivedata['JobTitle'] = $CreateSummaryLiveJobTitle;
            log::info($CreateSummaryLivedata);
            CronJob::create($CreateSummaryLivedata);
        }
        log::info('-- Create Summary Live END--');

        log::info('-- System Alert --');
        $SystemAlertCommandID = CronJobCommand::getCronJobCommandIDByCommand('neonalerts',$CompanyID);
        $SystemAlert_Count = CronJob::where(['CompanyID'=>$CompanyID,'CronJobCommandID'=>$SystemAlertCommandID])->count();
        if($SystemAlert_Count == 0) {
            $SystemAlertJobTitle = 'System Alerts';
            $SystemAlertSetting = '{"ThresholdTime":"30","SuccessEmail":"","ErrorEmail":"","JobTime":"MINUTE","JobInterval":"5","JobDay":["SUN","MON","TUE","WED","THU","FRI","SAT"],"JobStartTime":"12:00:00 AM"}';
            $SystemAlertLivedata = array();
            $SystemAlertLivedata['CompanyID'] = $CompanyID;
            $SystemAlertLivedata['CronJobCommandID'] = $SystemAlertCommandID;
            $SystemAlertLivedata['Settings'] = $SystemAlertSetting;
            $SystemAlertLivedata['Status'] = 1;
            $SystemAlertLivedata['created_by'] = 'system';
            $SystemAlertLivedata['created_at'] = $today;
            $SystemAlertLivedata['JobTitle'] = $SystemAlertJobTitle;
            log::info($SystemAlertLivedata);
            CronJob::create($SystemAlertLivedata);
        }
        log::info('-- System Alert END--');

    }

}