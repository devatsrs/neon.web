<?php

class AccountDiscountController extends \BaseController {

    public function discount_plan($id) {
        $type = Input::get('Type');
        $ServiceID = Input::get('ServiceID');
        if(empty($ServiceID)){
            $ServiceID = 0;
        }
        $AccountDiscountPlan = AccountDiscountPlan::getDiscountPlan($id,$type,$ServiceID);

        return View::make('accountdiscountplan.discount', compact('currencies','AccountDiscountPlan'));
    }

}