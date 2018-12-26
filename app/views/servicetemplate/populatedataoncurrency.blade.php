@if($selecteddata == "service")
    <option value="">Select</option>
    @foreach($currenciesservices as $currenciesservice)
        <option value="{{$currenciesservice["ServiceId"]}}">{{$currenciesservice["ServiceName"]}}</option>
    @endforeach
@endif
@if($selecteddata == "outboundPlan")

    <option value="">Select</option>
    @foreach($outbounddiscountplan as $outbounddiscountsingleplan)
        <option value="{{$outbounddiscountsingleplan["DiscountPlanID"]}}">{{$outbounddiscountsingleplan["Name"]}}</option>
    @endforeach

@endif
@if($selecteddata == "inboundPlan")


    <option value="">Select</option>
    @foreach($inbounddiscountplan as $inbounddiscountsingleplan)
        <option value="{{$inbounddiscountsingleplan["DiscountPlanID"]}}">{{$inbounddiscountsingleplan["Name"]}}</option>
    @endforeach

@endif
@if($selecteddata == "outboundTariff")

    <option value="">Select</option>
    @foreach($outboundtarifflist as $outboundtariffsingle)
        <option value="{{$outboundtariffsingle["RateTableId"]}}">{{$outboundtariffsingle["RateTableName"]}}</option>
    @endforeach

@endif
@if($selecteddata == "DidCategoryID")
    @foreach($categorytarifflist as $categorytariffsingle)
        <option value="{{$categorytariffsingle["RateTableID"]}}">{{$categorytariffsingle["RateTableName"]}}</option>
    @endforeach
@endif

@if($selecteddata == "templateSubscriptionList")
    @foreach($billingsubscriptionlist as $billingsubscriptionsingle)
        <option value="{{$billingsubscriptionsingle["SubscriptionID"]}}">{{$billingsubscriptionsingle["Name"]}}</option>
    @endforeach
@endif

@if($selecteddata == "editSelectedTemplateSubscription")
    <?php $index1 = 0; $ajaxEditSelectedTemplateSubscription = ''?>
    @foreach($billingsubsforsrvtemplate as $billingsubsforsrvtempsingle)
        <?php $ajaxEditSelectedTemplateSubscription = $ajaxEditSelectedTemplateSubscription . $billingsubsforsrvtempsingle["SubscriptionID"] . "," ?>
        <tr class="draggable" id="SubscriptionRowID{{$billingsubsforsrvtempsingle["SubscriptionID"]}}">
            <td>{{$billingsubsforsrvtempsingle["Name"]}}</td>
            <td><a title="Delete" onClick="RemoveSubscriptionRowInTable('SubscriptionRowID{{$billingsubsforsrvtempsingle["SubscriptionID"]}}');" class="delete-service1 btn btn-danger btn-sm"><i class="entypo-trash"></i></a></td>
        </tr>
    @endforeach
    <input type="hidden" value="{{$ajaxEditSelectedTemplateSubscription}}" id="ajaxEditSelectedTemplateSubscription"/>
@endif

@if($selecteddata == "editSelectedTemplateDIDTariff")
    <?php $index1 = 0; $ajaxEditSelectedTemplateDIDTariff = ''?>
    @foreach($selecteddidcategorytariflist as $categoryTariffList)
        <?php
            if ($categoryTariffList->DIDCategoryID == null || $categoryTariffList->DIDCategoryID == ''){
                $CategoryTariffValue = "0" .'-'. $categoryTariffList->RateTableID ;
            }else {
                $CategoryTariffValue = $categoryTariffList->DIDCategoryID .'-'. $categoryTariffList->RateTableID ;
            }

            $ajaxEditSelectedTemplateDIDTariff = $ajaxEditSelectedTemplateDIDTariff . $CategoryTariffValue . ","

        ?>
        <tr class="draggable" id="CategoryTariffRowID{{$CategoryTariffValue}}">
                    <?php $index1 = $index1 + 1; ?>
            <td>{{$categoryTariffList->CategoryName}}</td>
            <td>{{$categoryTariffList->RateTableName}}</td>
            <td>
                <a title="Delete" onClick="RemoveCategoryTariffRowInTable('CategoryTariffRowID{{$CategoryTariffValue}}');" class="delete-service2 btn btn-danger btn-sm"><i class="entypo-trash"></i></a>
            </td>
        </tr>
    @endforeach
    <input type="hidden" value="{{$ajaxEditSelectedTemplateDIDTariff}}" id="ajaxEditSelectedTemplateDIDTariff"/>
@endif


