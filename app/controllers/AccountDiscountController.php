<?php

class AccountDiscountController extends \BaseController {

    public function discount_plan($id) {
        $type = Input::get('Type');
        $ServiceID = Input::get('ServiceID');
        $AccountSubscriptionID = Input::get('AccountSubscriptionID');
        $SubscriptionDiscountPlanID = Input::get('SubscriptionDiscountPlanID');
        if(empty($ServiceID)){
            $ServiceID = 0;
        }
        if(empty($AccountSubscriptionID)){
            $AccountSubscriptionID = 0;
        }
        if(empty($SubscriptionDiscountPlanID)){
            $SubscriptionDiscountPlanID = 0;
        }
        log::info('AccountID '.$id);
        log::info('ServiceID '.$ServiceID);
        log::info('Type '.$type);
        log::info('AccountSubscriptionID '.$AccountSubscriptionID);
        log::info('SubscriptionDiscountPlanID '.$SubscriptionDiscountPlanID);
        $AccountDiscountPlan = AccountDiscountPlan::getDiscountPlan($id,$type,$ServiceID,$AccountSubscriptionID,$SubscriptionDiscountPlanID);

        return View::make('accountdiscountplan.discount', compact('currencies','AccountDiscountPlan'));
    }

}