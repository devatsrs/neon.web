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
        'summary'=>'Customer CDR',
    );

    public static $dimension = array(
        'summary'=>array(
            'year' => 'Year',
            'quarter_of_year' => 'Quarter' ,
            'month' => 'Month',
            'week_of_year' => 'Week',
            'date' => 'Day',
            'AccountID' =>'Customer',
            'VAccountID' =>'Vendor',
            'CompanyGatewayID' =>'Gateway',
            'Trunk' => 'Trunk',
            'CountryID' => 'Country',
            'AreaPrefix' => 'Prefix',
            'GatewayAccountPKID' => 'IP/CLI'
        ),
    );

    public static $measures = array(
        'summary'=>array(
            'TotalCharges' => 'Cost',
            'TotalBilledDuration' => 'Duration',
            'NoOfCalls' => 'No Of Calls',
            'NoOfFailCalls' => 'No Of Failed Calls'
        ),
    );

    public static $aggregator = array(
        'SUM' => 'Sum',
        'AVG' => 'Average',
        'COUNT' => 'Count',
        'COUNT_DISTINCT' => 'Count(Distinct)',
        'MAX' => 'Maximum',
        'MIN' => 'Minimum',
    );

    public static $condition = array(
        '=' => '=',
        '<>' => '<>',
        '<' => '<',
        '<=' => '<=',
        '>' => '>',
        '>=' => '>=',
    );

    public static $top = array(
        'top' => 'Top',
        'bottom' => 'Bottom',

    );

    public static $date_fields = ['date'];



    public static  function generateDynamicTable($CompanyID,$cube,$data=array(),$filters){
        $response = '';
        switch ($cube) {
            case 'summary':
                $response = self::generateSummaryQuery($CompanyID,$data,$filters);
                break;
            case 'payment':
                break;
            case 'invoice':
                break;

        }
        return $response;
    }

    public static function generateSummaryQuery($CompanyID,$data,$filters){

        if(count($data['row'])) {
            $query_distinct = self::commonCDRQuery($CompanyID,$data,$filters);
            foreach ($data['row'] as $column) {
                $query_distinct->orderby($column);
            }
            $query_distinct = $query_distinct->distinct();
            $columns = $query_distinct->get($data['row']);
            $columns = json_decode(json_encode($columns), true);

            //$response['column'] = self::generateColumnNames($columns);
            $response['distinct_row'] = $columns;
            $response['distinct_row'] = array_map('custom_implode',$response['distinct_row']);
        }

        $final_query = self::commonCDRQuery($CompanyID,$data,$filters);
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


    public static function getName($PKColumnName,$ID,$all_data){
        $name = $ID;
        switch ($PKColumnName) {
            case 'CompanyGatewayID':
                if($ID > 0 && isset($all_data['CompanyGateway'][$ID])) {
                    $name = $all_data['CompanyGateway'][$ID];
                }else{
                    $name = '';
                }
                break;
            case 'AccountID':
                if($ID > 0 && isset($all_data['Account'][$ID])) {
                    $name = $all_data['Account'][$ID];
                }else{
                    $name = '';
                }
                break;
            case 'VAccountID':
                if($ID > 0 && isset($all_data['Account'][$ID])) {
                    $name = $all_data['Account'][$ID];
                }else{
                    $name = '';
                }
                break;
            case 'CountryID':
                if($ID > 0 && isset($all_data['Country'][$ID])) {
                    $name = $all_data['Country'][$ID];
                }else{
                    $name = '';
                }
                break;

        }
        return $name;
    }

    public static function commonCDRQuery($CompanyID,$data,$filters){
        $query_common = DB::connection('neon_report')
            ->table('tblHeader')
            ->join('tblUsageSummaryDay', 'tblHeader.HeaderID', '=', 'tblUsageSummaryDay.HeaderID')
            ->join('tblDimDate', 'tblDimDate.DateID', '=', 'tblHeader.DateID')
            ->where(['CompanyID' => $CompanyID]);

        foreach($filters as $key => $filter){
            if(!empty($filter[$key]) && is_array($filter[$key])){
                $query_common->whereIn($key,$filter[$key]);
            }else if(!empty($filter['wildcard_match_val'])){
                $query_common->where($key,'like',str_replace('*','%',$filter['wildcard_match_val']));
            }else if($key == 'date'){
                if(!empty($filter['start_date'])){
                    $query_common->where('date','>=',str_replace('*','%',$filter['start_date']));
                }
                if(!empty($filter['end_date'])){
                    $query_common->where('date','<=',str_replace('*','%',$filter['end_date']));
                }
            }
        }
        return $query_common;
    }






}