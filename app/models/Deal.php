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
        'TotalPL'       => 'required',
    );


    public static function dealDetailArray($DealID, $data){
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
                unset($validateArr['DestinationBreak']);
                unset($validateArr['Prefix']);

                if(in_array('',$validateArr)){
                    $dealDetail = false;
                    break;
                }
            }

        return $dealDetail;
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
}