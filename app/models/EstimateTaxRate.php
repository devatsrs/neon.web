<?php

class EstimateTaxRate extends \Eloquent {
    protected $connection = 'sqlsrv2';
    protected $fillable = [];
    protected $guarded = array('EstimateTaxRateID');
    protected $table = 'tblEstimateTaxRate';
    protected  $primaryKey = "EstimateTaxRateID";

    public static function getEstimateTaxRateByProductDetail($EstimateID){
        $InvoiceDetail=EstimateDetail::where('EstimateID',$EstimateID)->select('EstimateDetailID','TaxRateID','TaxRateID2','Price','ProductType')->get();
        $Result = array();
        foreach($InvoiceDetail as $data) {
            if (!empty($data->TaxRateID)) {
                $TaxRate = array();
                if($data->ProductType==3){
                    $TaxRate['EstimateTaxType']=2;
                }else{
                    $TaxRate['EstimateTaxType']=0;
                }

                $TaxRate['TaxRateID'] = $data->TaxRateID;
                $TaxRate['EstimateDetailID'] = $data->EstimateDetailID;
                $TaxRate['Title'] = TaxRate::getTaxName($data->TaxRateID);
                $TaxRate['created_at'] = date("Y-m-d H:i:s");
                $TaxRate['EstimateID'] = $EstimateID;
                $TaxRate['TaxAmount'] = TaxRate::calculateProductTaxAmount($data->TaxRateID, $data->Price);
                $Result[] = $TaxRate;
            }
            if (!empty($data->TaxRateID2)) {
                $TaxRate = array();
                if($data->ProductType==3){
                    $TaxRate['EstimateTaxType']=2;
                }else{
                    $TaxRate['EstimateTaxType']=0;
                }
                $TaxRate['TaxRateID'] = $data->TaxRateID2;
                $TaxRate['EstimateDetailID'] = $data->EstimateDetailID;
                $TaxRate['Title'] = TaxRate::getTaxName($data->TaxRateID2);
                $TaxRate['created_at'] = date("Y-m-d H:i:s");
                $TaxRate['EstimateID'] = $EstimateID;
                $TaxRate['TaxAmount'] = TaxRate::calculateProductTaxAmount($data->TaxRateID2, $data->Price);
                $Result[] = $TaxRate;
            }
        }
        return $Result;
    }

}