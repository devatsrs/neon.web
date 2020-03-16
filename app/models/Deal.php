<?php

class Deal extends \Eloquent {

    protected $guarded      = array("DealID");
    protected $table        = 'tblDeal';
    protected $primaryKey   = "DealID";

    const StatusActive = "Active";
    const StatusClosed = "Closed";
    const TypeRevenue = "Revenue";
    const TypePayment = "Payment";

    public static $StatusDropDown = array(
        self::StatusActive => 'Active',
        self::StatusClosed => 'Closed'
    );
    public static $TypeDropDown = array(
        self::TypeRevenue => 'Revenue',
        self::TypePayment => 'Payment'
    );

    public static $rules = array(
        'Title'         => 'required|unique:tblDeal',
        'DealType'      => 'required',
        'AccountID'     => 'required',
        'CodedeckID'    => 'required',
        'Status'        => 'required',
        'AlertEmail'    => 'email',
        'StartDate'     => 'required|date|date_format:Y-m-d',
        'EndDate'       => 'required|date|date_format:Y-m-d',
    );


    public static function dealDetailArray($DealID, $data){
        $return = ['status' => true, 'message' => "Please fill all data in deal details."];
        $dealDetail = [];
        if(isset($data['Type']) && count($data['Type']) > 0)
            foreach($data['Type'] as $key => $item){
                $dealDetail[$key] = [
                    'Type' => $item,
                    'DealID' => $DealID,
                    'DestinationCountryID' => $data['Destination'][$key],
                    'DestinationBreak' => $data['DestinationBreak'][$key],
                    'Prefix' => $data['Prefix'][$key],
                    'TrunkID' => $data['Trunk'][$key],
                    'Revenue' => $data['Revenue'][$key],
                    'SalePrice' => $data['SalePrice'][$key],
                    'BuyPrice' => $data['BuyPrice'][$key],
                    'Minutes' => $data['Minutes'][$key],
                    'PerMinutePL' => $data['PLPerMinute'][$key],
                    'TotalPL' => $data['PL'][$key],
                    'created_at' => date("Y-m-d H:i:s"),
                ];

                $validateArr = $dealDetail[$key];
                // un-setting which are not required
                unset($validateArr['DestinationCountryID']);
                unset($validateArr['DestinationBreak']);
                unset($validateArr['Prefix']);

                $checkDestination = $dealDetail[$key]['DestinationCountryID'] == "" && $dealDetail[$key]['DestinationBreak'] == "" && $dealDetail[$key]['Prefix'] == "";

                if(in_array('',$validateArr)){
                    $return['status'] = false;
                    $return['message'] = "Deal Details fields are empty.";
                    break;
                } elseif ($checkDestination != false){
                    $return['status'] = false;
                    $return['message'] = "Please select at least one option in destination, destination break, prefix.";
                    break;
                }
            }

        $return['data'] = $return['status'] != false ? $dealDetail : [];

        return $return;
    }

    public static function dealNoteArray($DealID, $data){
        $dealNote = [];
        if(isset($data['Note']) && count($data['Note']) > 0)
            foreach($data['Note'] as $key => $item){
                if(!empty($item))
                    $dealNote[$key] = [
                        'Note' => $item,
                        'DealID' => $DealID,
                        'CreatedBy' => User::get_user_full_name(),
                        'created_at' => date("Y-m-d H:i:s"),
                    ];
            }

        return $dealNote;
    }
    public static function dealSummary($CompanyID,$Deal,$DealDetails)
    {
        $customer_sum = ['planned_cost' => 0, 'planned_minutes' => 0, 'actual_cost' => 0, 'actual_minutes' => 0];
        $vendor_sum = ['planned_cost' => 0, 'planned_minutes' => 0, 'actual_cost' => 0, 'actual_minutes' => 0];

        $deal_summary = [];
        if (!empty($DealDetails)) {
            foreach ($DealDetails as $dealDetail) {
                if ($dealDetail->Type == "Customer") {
                    $customer_sum['planned_cost'] += $dealDetail->Revenue;
                    $customer_sum['planned_minutes'] += $dealDetail->Minutes;
                }
                if ($dealDetail->Type == "Vendor") {
                    $vendor_sum['planned_cost'] += $dealDetail->Revenue;
                    $vendor_sum['planned_minutes'] += $dealDetail->Minutes;
                }
                $trunks = $dealDetail->TrunkID;
                $prefixes = $dealDetail->Prefix;
                $country = $dealDetail->DestinationCountryID;
                $dtbreaks = $dealDetail->DestinationBreak;

                if($dealDetail->Type == "Customer") {

                    $query_common = DB::connection('neon_report')
                        ->table('tblHeader')
                        ->join('tblDimDate', 'tblDimDate.DateID', '=', 'tblHeader.DateID')
                        ->join('tblUsageSummaryDay', 'tblHeader.HeaderID', '=', 'tblUsageSummaryDay.HeaderID')
                        ->where(['tblHeader.CompanyID' => $CompanyID])
                        ->where(['tblHeader.AccountID' => $Deal->AccountID])
                        ->whereBetween('tblDimDate.date', array($Deal->StartDate, $Deal->EndDate));
                    $query_common2 = DB::connection('neon_report')
                        ->table('tblHeader')
                        ->join('tblDimDate', 'tblDimDate.DateID', '=', 'tblHeader.DateID')
                        ->join('tblUsageSummaryDayLive', 'tblHeader.HeaderID', '=', 'tblUsageSummaryDayLive.HeaderID')
                        ->where(['tblHeader.CompanyID' => $CompanyID])
                        ->where(['tblHeader.AccountID' => $Deal->AccountID])
                        ->whereBetween('tblDimDate.date', array($Deal->StartDate, $Deal->EndDate));
                    if (!empty($trunks)) {
                        $query_common->where('Trunk', $trunks);
                        $query_common2->where('Trunk', $trunks);
                    }
                    if (!empty($prefixes)) {
                        $query_common->where('AreaPrefix', $prefixes);
                        $query_common2->where('AreaPrefix', $prefixes);
                    }
                    if (!empty($country)) {
                        $query_common->where('CountryID', $country);
                        $query_common2->where('CountryID', $country);
                    }


                    $query_common->union($query_common2);

                    $customer_data = $query_common->get();
                    $deal_summary[$dealDetail->DealNoteID]['data'] = $customer_data;
                    $deal_summary[$dealDetail->DealNoteID]['detail'] = $dealDetail;
                    $TotalCharges = $TotalBilledDuration = 0;
                    if (!empty($customer_data)) {
                        foreach ($customer_data as $customer_data_row) {
                            $TotalCharges += $customer_data_row->TotalCharges;
                            $TotalBilledDuration += $customer_data_row->TotalBilledDuration;
                        }
                    }
                    $deal_summary[$dealDetail->DealNoteID]['data']['TotalBilledDuration'] = (int)($TotalBilledDuration / 60);
                    $deal_summary[$dealDetail->DealNoteID]['data']['TotalCharges'] = (int)($TotalBilledDuration / 60 * $dealDetail->PerMinutePL);
                    $customer_sum['actual_minutes'] += (int)($TotalBilledDuration / 60);
                    $customer_sum['actual_cost'] += (int)($TotalBilledDuration / 60 * $dealDetail->PerMinutePL);
                } else if($dealDetail->Type == "Vendor") {

                    $query_common3 = DB::connection('neon_report')
                        ->table('tblHeaderV')
                        ->join('tblDimDate', 'tblDimDate.DateID', '=', 'tblHeaderV.DateID')
                        ->join('tblVendorSummaryDay', 'tblHeaderV.HeaderVID', '=', 'tblVendorSummaryDay.HeaderVID')
                        ->where(['tblHeaderV.CompanyID' => $CompanyID])
                        ->where(['tblHeaderV.VAccountID' => $Deal->AccountID])
                        ->whereBetween('tblDimDate.date', array($Deal->StartDate, $Deal->EndDate));
                    $query_common4 = DB::connection('neon_report')
                        ->table('tblHeaderV')
                        ->join('tblDimDate', 'tblDimDate.DateID', '=', 'tblHeaderV.DateID')
                        ->join('tblVendorSummaryDayLive', 'tblHeaderV.HeaderVID', '=', 'tblVendorSummaryDayLive.HeaderVID')
                        ->where(['tblHeaderV.CompanyID' => $CompanyID])
                        ->where(['tblHeaderV.VAccountID' => $Deal->AccountID])
                        ->whereBetween('tblDimDate.date', array($Deal->StartDate, $Deal->EndDate));

                    if (!empty($trunks)) {
                        $query_common3->where('Trunk', $trunks);
                        $query_common4->where('Trunk', $trunks);
                    }
                    if (!empty($prefixes)) {
                        $query_common3->where('AreaPrefix', $prefixes);
                        $query_common4->where('AreaPrefix', $prefixes);
                    }
                    if (!empty($country)) {
                        $query_common3->where('CountryID', $country);
                        $query_common4->where('CountryID', $country);
                    }
                    $query_common3->union($query_common4);

                    $vendor_data = $query_common3->get();

                    $deal_summary[$dealDetail->DealNoteID]['data'] = $vendor_data;
                    $deal_summary[$dealDetail->DealNoteID]['detail'] = $dealDetail;

                    $TotalCharges = $TotalBilledDuration = 0;
                    if (!empty($vendor_data)) {
                        foreach ($vendor_data as $vendor_data_row) {
                            $TotalCharges += $vendor_data_row->TotalSales;
                            $TotalBilledDuration += $vendor_data_row->TotalBilledDuration;
                        }
                    }
                    $deal_summary[$dealDetail->DealNoteID]['data']['TotalBilledDuration'] = (int)($TotalBilledDuration / 60);
                    $deal_summary[$dealDetail->DealNoteID]['data']['TotalCharges'] = (int)($TotalBilledDuration / 60 * $dealDetail->PerMinutePL);

                    $vendor_sum['actual_minutes'] += (int)($TotalBilledDuration / 60);
                    $vendor_sum['actual_cost'] += (int)($TotalBilledDuration / 60 * $dealDetail->PerMinutePL);
                }

            }
        }
        $deal_summary['vendor_sum'] = $vendor_sum;
        $deal_summary['customer_sum'] = $customer_sum;

        return $deal_summary;
    }
}