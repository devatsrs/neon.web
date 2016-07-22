<?php

class AccountDiscountController extends \BaseController {

    public function discount_plan($id) {

        $AccountDiscountPlan = AccountDiscountPlan::getDiscountPlan($id);

        return View::make('accountdiscountplan.discount', compact('currencies','AccountDiscountPlan'));
    }

}