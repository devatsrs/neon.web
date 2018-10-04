<?php
/**
 * Created by PhpStorm.
 * User: deven
 * Date: 24/02/2015
 * Time: 12:00 PM
 */
use Aws\S3\S3Client;

class AmazonS3 {

    public static $isAmazonS3;
    public static $dir = array(
        'AUTOIMPORT_UPLOAD' =>  'AutoImportUploads',
        'CODEDECK_UPLOAD' =>  'CodedecksUploads',
        'VENDOR_UPLOAD' =>  'VendorUploads',
        'VENDOR_DOWNLOAD' =>  'VendorDownloads',
        'CUSTOMER_DOWNLOAD' =>  'CustomerDownloads',
        'ACCOUNT_APPROVAL_CHECKLIST_FORM' =>  'AccountApprovalChecklistForms',
        'ACCOUNT_DOCUMENT' =>  'AccountDocuments',
        'INVOICE_COMPANY_LOGO' =>  'InvoiceCompanyLogos',
        'PAYMENT_PROOF'=>'PaymentProof',
        'INVOICE_PROOF_ATTACHMENT' =>  'InvoiceProofAttachment',
        'INVOICE_UPLOAD' =>  'Invoices',
		'ESTIMATE_UPLOAD' =>  'estimates',
		'CREDITNOTES_UPLOAD' =>  'creditnotes',
        'CUSTOMER_PROFILE_IMAGE' =>  'CustomerProfileImage',
        'USER_PROFILE_IMAGE' =>  'UserProfileImage',
        'BULK_LEAD_MAIL_ATTACHEMENT' => 'bulkleadmailattachment',
        'TEMPLATE_FILE' => 'TemplateFile',
        'CDR_UPLOAD'=>'CDRUPload',
        'VENDOR_TEMPLATE_FILE' => 'vendortemplatefile',
        'BULK_ACCOUNT_MAIL_ATTACHEMENT' =>'bulkaccountmailattachment',
        'BULK_INVOICE_MAIL_ATTACHEMENT'=>'bulkinvoicemailattachment',
        'RATETABLE_UPLOAD'=>'RateTableUpload',
        'WYSIHTML5_FILE_UPLOAD'=>'Wysihtml5fileupload',
        'PAYMENT_UPLOAD'=>'PaymentUpload',
        'OPPORTUNITY_ATTACHMENT'=>'OpportunityAttachment',
		'THEMES_IMAGES'=>'ThemeImages',
		'DISPUTE_ATTACHMENTS'=>'DisputesAttachment',
        'TASK_ATTACHMENT'=>'TaskAttachment',
        'EMAIL_ATTACHMENT'=>'EmailAttachment',
		'TICKET_ATTACHMENT'=>'TicketAttachment',
        'DIALSTRING_UPLOAD'=>'DialString',
        'IP_UPLOAD'=>'IPUpload',
        'RECURRING_INVOICE_UPLOAD'=>'RecurringInvoice',
        'ITEM_UPLOAD'=>'ITEMUPload',
        'RATESHEET_TEMPLATE'=>'RatesheetTemplate',
        'XERO_UPLOAD'=>'XeroUpload',
        'GATEWAY_KEY'=>'GatewayKey',
        'DIGITAL_SIGNATURE_KEY'=>'DigitalSignature',
        'BULK_DISPUTE_MAIL_ATTACHEMENT'=>'bulkdisputemailattachment',
        'PRODUCT_ATTACHMENTS'=>'ProductAttachment',
    );

    // Instantiate an S3 client
    public static function getS3Client($CompanyID=0){
		     	
	 	$AmazonData		=	SiteIntegration::CheckIntegrationConfiguration(true,SiteIntegration::$AmazoneSlug,$CompanyID);
		
		if(!$AmazonData){
            self::$isAmazonS3='NoAmazon';
            return 'NoAmazon';
		}else{
            self::$isAmazonS3='Amazon';
            $Amazone=array(
                'region' => $AmazonData->AmazonAwsRegion,
                'credentials' => array(
                    'key' => $AmazonData->AmazonKey,
                    'secret' => $AmazonData->AmazonSecret
                ),
            );

            if(isset($AmazonData->SignatureVersion) && $AmazonData->SignatureVersion!=''){
                $Amazone['signature']=$AmazonData->SignatureVersion;
            }
			return $s3Client = S3Client::factory($Amazone);
		}

       /*
	      $AMAZONS3_KEY  = getenv("AMAZONS3_KEY");
        $AMAZONS3_SECRET = getenv("AMAZONS3_SECRET");
        $AWS_REGION = getenv("AWS_REGION");
	
	    if(empty($AMAZONS3_KEY) || empty($AMAZONS3_SECRET) || empty($AWS_REGION) ){
            return 'NoAmazon';
        }else {

            return $s3Client = S3Client::factory(array(
                'region' => $AWS_REGION,
                'credentials' => array(
                    'key' => $AMAZONS3_KEY,
                    'secret' => $AMAZONS3_SECRET
                ),
            ));
        }*/
    }
	
	 public static function getAmazonSettings($CompanyID=0){
		$amazon 		= 	array();
		$AmazonData		=	SiteIntegration::CheckIntegrationConfiguration(true,SiteIntegration::$AmazoneSlug,$CompanyID);
		
		if($AmazonData){
			$amazon 	=	 array("AWS_BUCKET"=>$AmazonData->AmazonAwsBucket,"AMAZONS3_KEY"=>$AmazonData->AmazonKey,"AMAZONS3_SECRET"=>$AmazonData->AmazonSecret,"AWS_REGION"=>$AmazonData->AmazonAwsRegion);	
		}
		
        return $amazon;
    }

    /*
     * Generate Path
     * Ex. WaveTell/18-Y/VendorUploads/2015/05
     * */
    static function generate_upload_path($dir ='',$accountId = '',$CompanyID=0 , $noDateFolders=false) {

        if(empty($dir))
            return false;
        if(empty($CompanyID)) {
            $CompanyID = User::get_companyID();//   Str::slug(Company::getName());
        }
        $path = self::generate_path($dir,$CompanyID,$accountId, $noDateFolders);

        return $path;
    }

    static function generate_path($dir ='',$companyId , $accountId = '' , $noDateFolders=false) {

        $path = $companyId  ."/";

        if($accountId > 0){
            $path .= $accountId ."/";
        }
        if($noDateFolders){
            $path .=  $dir . "/";
        }else{
            $path .=  $dir . "/". date("Y")."/".date("m") ."/" .date("d") ."/";
        }

        $dir = CompanyConfiguration::get('UPLOAD_PATH',$companyId) . '/'. $path;
        if (!file_exists($dir)) {
            RemoteSSH::run("mkdir -p " . $dir);
            RemoteSSH::run("chmod -R 777 " . $dir);
            @mkdir($dir, 0777, TRUE);
        }

        return $path;
    }

    static function upload($file,$dir,$CompanyID=0,$delete = 1){

        // Instantiate an S3 client
        $s3 = self::getS3Client($CompanyID);

        //When no amazon return true;
        if($s3 == 'NoAmazon'){
            return true;
        }
		
		$AmazonSettings  = self::getAmazonSettings($CompanyID);
        $bucket 		 = $AmazonSettings['AWS_BUCKET'];
        // Upload a publicly accessible file. The file size, file type, and MD5 hash
        // are automatically calculated by the SDK.
        try {
            $resource = fopen($file, 'r');
            $s3->upload($bucket, $dir.basename($file), $resource, 'public-read');
            if($delete==1){
                @unlink($file); // delete from local if amazon is on
            }
            return true;
        } catch (S3Exception $e) {
            return false ; //"There was an error uploading the file.\n";
        }
    }

    static function preSignedUrl($key='',$CompanyID=0){

        $s3 = self::getS3Client($CompanyID);

        //When no amazon ;

            $Uploadpath = CompanyConfiguration::get('UPLOAD_PATH',$CompanyID)."/".$key;
            if ( file_exists($Uploadpath) ) {
                return $Uploadpath;
            }
            elseif(self::$isAmazonS3=='Amazon')
            {
                $AmazonSettings = self::getAmazonSettings($CompanyID);
                $bucket = $AmazonSettings['AWS_BUCKET'];

                // Get a command object from the client and pass in any options
                // available in the GetObject command (e.g. ResponseContentDisposition)
                $command = $s3->getCommand('GetObject', array(
                    'Bucket' => $bucket,
                    'Key' => $key,
                    'ResponseContentDisposition' => 'attachment; filename="' . basename($key) . '"'
                ));

                // Create a signed URL from the command object that will last for
                // 10 minutes from the current time
                return $command->createPresignedUrl('+10 minutes');
            }
            else
            {
                return "";
            }
    }

    static function unSignedUrl($key='',$CompanyID=0){
        $s3 = self::getS3Client($CompanyID);
        $Uploadpath = CompanyConfiguration::get('UPLOAD_PATH',$CompanyID) . '/' .$key;

        if ( file_exists($Uploadpath) ) {
            return $Uploadpath;
        } elseif(self::$isAmazonS3=='Amazon') {
            $AmazonSettings = self::getAmazonSettings($CompanyID);
            $bucket = $AmazonSettings['AWS_BUCKET'];
            $unsignedUrl = $s3->getObjectUrl($bucket, $key);
            return $unsignedUrl;
        } else {
            return "";
        }
    }

    static function unSignedImageUrl($key='',$CompanyID=0){

        /*$s3 = self::getS3Client();

        //When no amazon ;
        if($s3 == 'NoAmazon'){
            $file = CompanyConfiguration::get('UPLOAD_PATH') . '/' . $key;
            if ( file_exists($file) ) {
                return  get_image_data($file);
            } else {
                return get_image_data("http://placehold.it/250x100");
            }
        }
        return self::unSignedUrl($key);*/

        $imagepath=self::preSignedUrl($key,$CompanyID);
        if(file_exists($imagepath)){
            return  get_image_data($imagepath);
        }
        elseif (self::$isAmazonS3=="Amazon") {
            return  $imagepath;
        }
        else{
            return get_image_data("http://placehold.it/250x100");
        }

    }

    static function delete($file,$CompanyID=0){
        $return=false;
        if(strlen($file)>0) {
            // Instantiate an S3 client
            $s3 = self::getS3Client($CompanyID);

            //When no amazon ;

                $Uploadpath = CompanyConfiguration::get('UPLOAD_PATH',$CompanyID) . "/"."".$file;
                if ( file_exists($Uploadpath) ) {
                    @unlink($Uploadpath);
                    if(self::$isAmazonS3=="NoAmazon")
                    {
                        $return=true;
                    }
                }

            if(self::$isAmazonS3=="Amazon")
            {
                 $AmazonSettings  = self::getAmazonSettings($CompanyID);
                 $bucket 		 = $AmazonSettings['AWS_BUCKET'];
                // Upload a publicly accessible file. The file size, file type, and MD5 hash
                // are automatically calculated by the SDK.
                try {
                    $result = $s3->deleteObject(array('Bucket' => $bucket, 'Key' => $file));
                    $return=true;
                } catch (S3Exception $e) {
                    $return=false; //"There was an error uploading the file.\n";
                }
            }
        }else{
            $return=false;
        }
        return $return;
    }
}
