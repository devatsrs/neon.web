<?php
class CreditNotesTaxRate extends \Eloquent {


    protected $connection = 'sqlsrv2';
    protected $fillable = [];
    protected $guarded = array('CreditNotesTaxRateID');
    protected $table = 'tblCreditNotesTaxRate';
    protected  $primaryKey = "CreditNotesTaxRateID";

    public static function getCreditNotesTaxRateByProductDetail($CreditNotesID){
        $CreditNotesDetail=CreditNotesDetail::where('CreditNotesID',$CreditNotesID)->select('CreditNotesDetailID','TaxRateID','TaxRateID2','Price')->get();
        $Result = array();
        foreach($CreditNotesDetail as $data) {
            if (!empty($data->TaxRateID)) {
                $TaxRate = array();
                $TaxRate['TaxRateID'] = $data->TaxRateID;
                $TaxRate['CreditNotesDetailID'] = $data->CreditNotesDetailID;
                $TaxRate['Title'] = TaxRate::getTaxName($data->TaxRateID);
                $TaxRate['created_at'] = date("Y-m-d H:i:s");
                $TaxRate['CreditNotesID'] = $CreditNotesID;
                $TaxRate['TaxAmount'] = TaxRate::calculateProductTaxAmount($data->TaxRateID, $data->Price);
                $Result[] = $TaxRate;
            }
            if (!empty($data->TaxRateID2)) {
                $TaxRate = array();
                $TaxRate['TaxRateID'] = $data->TaxRateID2;
                $TaxRate['CreditNotesDetailID'] = $data->CreditNotesDetailID;
                $TaxRate['Title'] = TaxRate::getTaxName($data->TaxRateID2);
                $TaxRate['created_at'] = date("Y-m-d H:i:s");
                $TaxRate['CreditNotesID'] = $CreditNotesID;
                $TaxRate['TaxAmount'] = TaxRate::calculateProductTaxAmount($data->TaxRateID2, $data->Price);
                $Result[] = $TaxRate;
            }
        }
        return $Result;
    }

}