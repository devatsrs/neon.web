<?php

class RecurringTaxRate extends \Eloquent {
    protected $connection = 'sqlsrv2';
    protected $fillable = [];
    protected $guarded = array('RecurringInvoiceTaxRateID');
    protected $table = 'tblRecurringInvoiceTaxRate';
    protected  $primaryKey = "RecurringInvoiceTaxRateID";

    public static function getRecurringInvoiceTaxRateByProductDetail($RecurringInvoiceID){
        $InvoiceDetail=RecurringInvoiceDetail::where('RecurringInvoiceID',$RecurringInvoiceID)->select('RecurringInvoiceDetailID','TaxRateID','TaxRateID2','Price')->get();
        $Result = array();
        foreach($InvoiceDetail as $data) {
            if (!empty($data->TaxRateID)) {
                $TaxRate = array();
                $TaxRate['TaxRateID'] = $data->TaxRateID;
                $TaxRate['RecurringInvoiceDetailID'] = $data->RecurringInvoiceDetailID;
                $TaxRate['Title'] = TaxRate::getTaxName($data->TaxRateID);
                $TaxRate['created_at'] = date("Y-m-d H:i:s");
                $TaxRate['RecurringInvoiceID'] = $RecurringInvoiceID;
                $TaxRate['TaxAmount'] = TaxRate::calculateProductTaxAmount($data->TaxRateID, $data->Price);
                $Result[] = $TaxRate;
            }
            if (!empty($data->TaxRateID2)) {
                $TaxRate = array();
                $TaxRate['TaxRateID'] = $data->TaxRateID2;
                $TaxRate['RecurringInvoiceDetailID'] = $data->RecurringInvoiceDetailID;
                $TaxRate['Title'] = TaxRate::getTaxName($data->TaxRateID2);
                $TaxRate['created_at'] = date("Y-m-d H:i:s");
                $TaxRate['RecurringInvoiceID'] = $RecurringInvoiceID;
                $TaxRate['TaxAmount'] = TaxRate::calculateProductTaxAmount($data->TaxRateID2, $data->Price);
                $Result[] = $TaxRate;
            }
        }
        return $Result;
    }

}