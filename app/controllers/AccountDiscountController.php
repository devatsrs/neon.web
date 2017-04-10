<?php

class AccountDiscountController extends \BaseController {

    public function discount_plan($id) {
        $type = Input::get('Type');
        $AccountDiscountPlan = AccountDiscountPlan::getDiscountPlan($id,$type);

        return View::make('accountdiscountplan.discount', compact('currencies','AccountDiscountPlan'));
    }

}