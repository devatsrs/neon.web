<?php


class DiscountScheme extends \Eloquent
{
    protected $guarded = array("DiscountSchemeID");

    protected $table = 'tblDiscountScheme';

    protected $primaryKey = "DiscountSchemeID";

    public static function checkForeignKeyById($id) {


        /** todo implement this function   */
        return false;
    }

}