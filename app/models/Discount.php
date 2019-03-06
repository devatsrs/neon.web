<?php




class Discount extends \Eloquent
{
    protected $guarded = array("DiscountID");

    protected $table = 'tblDiscount';

    protected $primaryKey = "DiscountID";

    public static function checkForeignKeyById($id) {


        $hasInAccountDiscountScheme = AccountDiscountScheme::where("DiscountID",$id)->count();
        if( intval($hasInAccountDiscountScheme) > 0){
            return true;
        }else{
            return false;
        }
    }

}