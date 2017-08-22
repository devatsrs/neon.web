<?php

class AuditHeader extends \Eloquent {
    protected $guarded = array("AuditHeaderID");
    protected $table = 'tblAuditHeader';
    protected  $primaryKey = "AuditHeaderID";
	public    $timestamps 	= 	false; // no created_at and updated_at

	public static function add_AuditLog($header=array(),$details=array()){
		$today = date('Y-m-d');
		
		$checkheader = array();	
		
		$checkheader['UserID']=$header['UserID'];
		$checkheader['CompanyID']=$header['CompanyID'];
		$checkheader['Date']=$today;
		$checkheader['ParentColumnName']=$header['ParentColumnName'];
		$checkheader['Type']=$header['Type'];
        $checkheader['IP']=$header['IP'];
        $checkheader['UserType']=$header['UserType'];

		if(!empty($details) && count($details)>0){
			foreach($details as $detail){
				if(!empty($detail['ParentColumnID'])){
                    $checkheader['ParentColumnID']=$detail['ParentColumnID'];
					$AuditHeaderID = AuditHeader::create_AuditHeader($checkheader);
					if(!empty($AuditHeaderID)){
						$AuditDetailData = array();
						
						$AuditDetailData['AuditHeaderID']=$AuditHeaderID;
						$AuditDetailData['ColumnName']=$detail['ColumnName'];
						$AuditDetailData['OldValue']=$detail['OldValue'];
						$AuditDetailData['NewValue']=$detail['NewValue'];
						$AuditDetailData['created_at']=date('Y-m-d H:i:s');
						$AuditDetailData['created_by']=User::get_user_full_name();

						AuditDetails::create($AuditDetailData);
						
					}
					
				}
			}
		}
		
	}
	
	public static function create_AuditHeader($data=array()){
		
		$AuditHeaderID = AuditHeader::get_AuditHeaderID($data);
		if(empty($AuditHeaderID)){
			if ($AuditHeader = AuditHeader::create($data)) {
				return $AuditHeader->AuditHeaderID;
			}else{
				return false;
			}
		}
		return $AuditHeaderID;
		
	}
	
	public static function get_AuditHeaderID($data=array()){
		
		$AuditHeaderID = AuditHeader::where(['UserID'=>$data['UserID'],'CompanyID'=>$data['CompanyID'],'Date'=>$data['Date'],'Type'=>$data['Type'],'ParentColumnID'=>$data['ParentColumnID']])->pluck('AuditHeaderID');
		
		return $AuditHeaderID;
	}

}