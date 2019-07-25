<?php

class AccountRateTable extends \Eloquent {
	protected $fillable = [];
    protected $guarded = array('AccountRateTableID');

    protected $table = 'tblAccountRateTable';

    protected  $primaryKey = "AccountRateTableID";



    public static function addAccountRateTable($AccountID, $data){

        //Account Rate Tables
        $AccountAccessRateTableID = empty($data['AccountAccessRateTableID']) ? '0' : $data['AccountAccessRateTableID'];
        $AccountPackageRateTableID = empty($data['AccountPackageRateTableID']) ? '0' : $data['AccountPackageRateTableID'];
        $AccountTerminationRateTableID = empty($data['AccountTerminationRateTableID']) ? '0' : $data['AccountTerminationRateTableID'];

        $AccountRateTableData = [
            'AccessRateTableID'      => $AccountAccessRateTableID,
            'PackageRateTableID'     => $AccountPackageRateTableID,
            'TerminationRateTableID' => $AccountTerminationRateTableID,
        ];

        $AccountRateTable = AccountRateTable::where(['AccountID' => $AccountID])->first();
        if($AccountRateTable != false){
            $AccountRateTable->update($AccountRateTableData);
        } else {
            $AccountRateTableData['AccountID'] = $AccountID;
            AccountRateTable::create($AccountRateTableData);
        }
    }
}