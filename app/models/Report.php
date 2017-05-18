<?php
class Report extends \Eloquent {
    protected $guarded = array("ReportID");
    protected $table = "tblReport";
    protected $primaryKey = "ReportID";
    protected $connection = 'neon_report';
    protected $fillable = array(
        'CompanyID','Name','Settings','created_at'
    );

    public static $rules = array(
        'Name'=>'required',
    );

    public static $cube = array(
        'summary'=>'Summary',
    );

    public static $dimension = array(
        'summary'=>array('year','quarter_of_year','month','week_of_year','date','AccountID','CompanyGatewayID','Trunk','CountryID','AreaPrefix'),
    );

    public static $measures = array(
        'summary'=>array('TotalCharges','TotalBilledDuration','NoOfCalls','NoOfFailCalls'),
    );


    public static  function generateDynamicTable($CompanyID,$cube,$data=array()){
        $response = '';
        switch ($cube) {
            case 'summary':
                $response = self::generateSummaryQuery($CompanyID,$data);
                break;
            case 'payment':
                break;
            case 'invoice':
                break;

        }
        return $response;
    }

    public static function generateSummaryQuery($CompanyID,$data){
        $columns = array();
        if(count($data['row'])) {
            $query_distinct = DB::connection('neon_report')
                ->table('tblHeader')
                ->join('tblUsageSummaryDay', 'tblHeader.HeaderID', '=', 'tblUsageSummaryDay.HeaderID')
                ->join('tblDimDate', 'tblDimDate.DateID', '=', 'tblHeader.DateID')
                ->where(['CompanyID' => $CompanyID])
                //->where(['AccountID' => 30])
                ->distinct();
            foreach ($data['row'] as $column) {
                $query_distinct->orderby($column);
            }
            $columns = $query_distinct->get($data['row']);
            $columns = json_decode(json_encode($columns), true);

            //$response['column'] = self::generateColumnNames($columns);
            $response['distinct_row'] = $columns;
            $response['distinct_row'] = array_map('custom_implode',$response['distinct_row']);
        }


        $final_query = DB::connection('neon_report')
            ->table('tblHeader')
            ->join('tblUsageSummaryDay', 'tblHeader.HeaderID', '=', 'tblUsageSummaryDay.HeaderID')
            ->join('tblDimDate', 'tblDimDate.DateID', '=', 'tblHeader.DateID')
            ->where(['CompanyID' => $CompanyID])
            //->where(['AccountID' => 30])
        ;
        foreach ($data['column'] as $column) {
            $final_query->groupby($column);
        }
        foreach ($data['row'] as $column) {
            $final_query->groupby($column);
        }

        $data['row'] = array_merge($data['row'],$data['column']);
        foreach($data['sum'] as $colname) {
            $data['row'][] = DB::Raw("SUM(tblUsageSummaryDay.".$colname.") as ".$colname);
        }
        /*if(!empty($select_columns)){
            $data['row'][] = DB::Raw($select_columns);
        }*/
        //print_r($data['row']);exit;
        if(!empty($data['row'])) {
            $response['data'] = $final_query->get($data['row']);
            $response['data'] = json_decode(json_encode($response['data']),true);
        }else{
            $response['data'] = array();
        }


        return $response;
    }

    public static function generateColumnNames($columns){
        $header_array = array();
        foreach ($columns as $key => $single_columns){
            foreach ($single_columns as $col_name => $col_val) {
                if(!isset($header_array['names'][$col_name][$col_val])) {
                    $header_array['names'][$col_name][$col_val] = self::getName($col_name, $col_val); //$col_name.$col_val
                }
            }
        }
        return $header_array;
    }

    public static function getName($PKColumnName,$ID){
        $name = $ID;
        switch ($PKColumnName) {
            case 'CompanyGatewayID':
                $name = CompanyGateway::getCompanyGatewayName($ID);
                break;
            case 'AccountID':
                $name = Account::getCompanyNameByID($ID);
                break;
            case 'CountryID':
                $name = Country::getName($ID);
                break;

        }
        return $name;
    }

    public static function generateRowNames($columns){
        $header_array = array();
        foreach ($columns as $key => $single_columns){
            foreach ($single_columns as $col_name => $col_val) {
                if(!isset($header_array['names'][$col_name][$col_val])) {
                    $header_array['names'][$col_name][$col_val] = self::getName($col_name, $col_val); //$col_name.$col_val
                }
            }
        }
        return $header_array;
    }


}