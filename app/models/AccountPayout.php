<?php
class AccountPayout extends \Eloquent
{
    protected $fillable = [];
    protected $guarded = array('AccountPayoutID');
    protected $table = 'tblAccountPayout';
    protected $primaryKey = "AccountPayoutID";
    public static $StatusActive = 1;
    public static $StatusDeactive = 0;

    public static function getActivePayoutAccounts($AccountID,$PaymentGatewayID)
    {
        $AccountPayout = AccountPayout::where(array('AccountID' => $AccountID,'PaymentGatewayID'=>$PaymentGatewayID,'Status' => 1,'isDefault' => 1))
            ->Where(function($query)
            {
                $query->where("Blocked",'<>',1)
                    ->orwhereNull("Blocked");
            })
            ->first();
        return $AccountPayout;
    }

    public static function setPayoutBlock($AccountPayoutID)
    {
        AccountPayout::where(array('AccountPayoutID' => $AccountPayoutID))->update(array('Blocked' => 1));
    }

    public static function getPayoutAccount($AccountPayoutID)
    {
        $AccountPayout = AccountPayout::where(array('AccountPaymentProfileID' => $AccountPayoutID))->first();
        return $AccountPayout;
    }
}