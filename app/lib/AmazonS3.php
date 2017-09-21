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
    );

    // Instantiate an S3 client
    public static function getS3Client(){
		     	
	 	$AmazonData		=	SiteIntegration::CheckIntegrationConfiguration(true,SiteIntegration::$AmazoneSlug);
		
		if(!$AmazonData){
            self::$isAmazonS3='NoAmazon';
            return 'NoAmazon';
		}else{
            self::$isAmazonS3='Amazon';
			return $s3Client = S3Client::factory(array(
				'region' => $AmazonData->AmazonAwsRegion,
				'credentials' => array(
					'key' => $AmazonData->AmazonKey,
					'secret' => $AmazonData->AmazonSecret
				),
			));
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
	
	 public static function getAmazonSettings(){     
		$amazon 		= 	array();
		$AmazonData		=	SiteIntegration::CheckIntegrationConfiguration(true,SiteIntegration::$AmazoneSlug);
		
		if($AmazonData){
			$amazon 	=	 array("AWS_BUCKET"=>$AmazonData->AmazonAwsBucket,"AMAZONS3_KEY"=>$AmazonData->AmazonKey,"AMAZONS3_SECRET"=>$AmazonData->AmazonSecret,"AWS_REGION"=>$AmazonData->AmazonAwsRegion);	
		}
		
        return $amazon;
    }

    /*
     * Generate Path
     * Ex. WaveTell/18-Y/VendorUploads/2015/05
     * */
    static function generate_upload_path($dir ='',$accountId = '' ) {

        if(empty($dir))
            return false;

        $CompanyID = User::get_companyID();//   Str::slug(Company::getName());

        $path = self::generate_path($dir,$CompanyID,$accountId);

        return $path;
    }

    static function generate_path($dir ='',$companyId , $accountId = '' ) {

        $path = $companyId  ."/";

        if($accountId > 0){
            $path .= $accountId ."/";
        }

        $path .=  $dir . "/". date("Y")."/".date("m") ."/" .date("d") ."/";
        $dir = CompanyConfiguration::get('UPLOAD_PATH') . '/'. $path;
        if (!file_exists($dir)) {
            RemoteSSH::run("mkdir -p " . $dir);
            RemoteSSH::run("chmod -R 777 " . $dir);
            @mkdir($dir, 0777, TRUE);
        }

        return $path;
    }

    static function upload($file,$dir){

        // Instantiate an S3 client
        $s3 = self::getS3Client();

        //When no amazon return true;
        if($s3 == 'NoAmazon'){
            return true;
        }
		
		$AmazonSettings  = self::getAmazonSettings();		
        $bucket 		 = $AmazonSettings['AWS_BUCKET'];
        // Upload a publicly accessible file. The file size, file type, and MD5 hash
        // are automatically calculated by the SDK.
        try {
            $resource = fopen($file, 'r');
            $s3->upload($bucket, $dir.basename($file), $resource, 'public-read');
//            @unlink($file); // check first file in local


                return true;
        } catch (S3Exception $e) {
            return false ; //"There was an error uploading the file.\n";
        }
    }

    static function preSignedUrl($key=''){

        $s3 = self::getS3Client();

        //When no amazon ;

            $Uploadpath = CompanyConfiguration::get('UPLOAD_PATH')."/".$key;
            if ( file_exists($Uploadpath) ) {
                return $Uploadpath;
            }
            elseif(self::$isAmazonS3=='Amazon')
            {
                $AmazonSettings = self::getAmazonSettings();
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

    static function unSignedUrl($key=''){

//        $s3 = self::getS3Client();

        //When no amazon ;
//        if($s3 == 'NoAmazon'){
            return  self::preSignedUrl($key);
//        }

        /*$AmazonSettings  = self::getAmazonSettings();
        $bucket 		 = $AmazonSettings['AWS_BUCKET'];
        $unsignedUrl = '';
        if(!empty($key)){
           $unsignedUrl = $s3->getObjectUrl($bucket, $key);
        }
        return $unsignedUrl;*/

    }

    static function unSignedImageUrl($key=''){

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

        $imagepath=self::preSignedUrl($key);
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

    static function delete($file){
        $return=false;
        if(strlen($file)>0) {
            // Instantiate an S3 client
            $s3 = self::getS3Client();

            //When no amazon ;

                $Uploadpath = CompanyConfiguration::get('UPLOAD_PATH') . "/"."".$file;
                if ( file_exists($Uploadpath) ) {
                    @unlink($Uploadpath);
                    if(self::$isAmazonS3=="NoAmazon")
                    {
                        $return=true;
                    }
                }

            if(self::$isAmazonS3=="Amazon")
            {
                 $AmazonSettings  = self::getAmazonSettings();
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
