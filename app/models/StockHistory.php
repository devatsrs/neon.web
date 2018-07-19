<?php

class StockHistory extends \Eloquent {

    protected $connection = 'sqlsrv2';
    protected $fillable = [];
    protected $guarded = array('StockHistoryID');
    protected $table = 'tblStockHistory';
    public  $primaryKey = "StockHistoryID"; //Used in BasedController





}