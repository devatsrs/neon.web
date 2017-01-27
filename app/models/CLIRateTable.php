<?php

class CLIRateTable extends \Eloquent {
    protected $guarded = array("CLIRateTableID");

    protected $fillable = [];

    protected $table = "tblCLIRateTable";

    protected $primaryKey = "CLIRateTableID";

    public $timestamps = false; // no created_at and updated_at

}