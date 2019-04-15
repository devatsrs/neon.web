@extends('layout.main')

@section('content')


    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{URL::to('/dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li>
            <a href="{{URL::to('/rategenerators')}}">Rate Generator</a>
        </li>
        <li>
            <a><span>{{rategenerators_dropbox($rategenerators->RateGeneratorId)}}</span></a>
        </li>
        <!-- <li class="active">
        <strong>{{!empty($rategenerator)?$rategenerator->RateGeneratorName:''}}</strong>
    </li>-->
        <li class="active">
            <strong>Edit Rate Generator</strong>
        </li>

    </ol>
    <h3> Update Rate Generator</h3>
    <div class="float-right" >
        @if($rategenerators->Status==1)
            <button title="Deactivate" href="{{URL::to('/rategenerators')}}/{{$rategenerators->RateGeneratorId}}/change_status/0" class="btn btn-default change_status btn-danger btn-sm" data-loading-text="Loading..."><i class="entypo-minus-circled"></i></button>
        @elseif($rategenerators->Status==0)
            <button title="Activate" href="{{URL::to('/rategenerators')}}/{{$rategenerators->RateGeneratorId}}/change_status/1" class="btn btn-default change_status btn-success btn-sm" data-loading-text="Loading..."><i class="entypo-check"></i></button>
        @endif
        <a title="Delete" href="{{URL::to('/rategenerators')}}/{{$rategenerators->RateGeneratorId}}/delete" data-redirect="{{URL::to('/rategenerators')}}" data-id="{{$rategenerators->RateGeneratorId}}" class="btn btn-default btn-sm delete_rate btn-danger"><i class="entypo-trash"></i></a>
        @if($rategenerators->Status==1)
            <div class="btn-group">
                <button href="#" class="btn generate btn-success btn-sm  dropdown-toggle" data-toggle="dropdown" data-loading-text="Loading...">Generate Rate Table <span class="caret"></span></button>
                <ul class="dropdown-menu dropdown-green" role="menu">
                    <li><a href="{{URL::to('/rategenerators')}}/{{$rategenerators->RateGeneratorId}}/generate_rate_table/create" class="generate_rate create" >Create New Rate Table</a></li>
                    <li><a href="{{URL::to('/rategenerators')}}/{{$rategenerators->RateGeneratorId}}/generate_rate_table/update" class="generate_rate update" data-trunk="{{$rategenerators->TrunkID}}" data-codedeck="{{$rategenerators->CodeDeckId}}" data-currency="{{$rategenerators->CurrencyID}}" data-type="{{$rategenerators->SelectType}}" data-AppliedTo="{{$rategenerators->AppliedTo}}">Update Existing Rate Table</a></li>
                </ul>
            </div>
        @endif
        <button type="button"  class="update_form btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
            <i class="entypo-floppy"></i>
            Save
        </button>
        <a href="{{URL::to('/rategenerators')}}" class="btn btn-danger btn-sm btn-icon icon-left">
            <i class="entypo-cancel"></i>
            Close
        </a>
    </div>
    <br>
    <br>

    <div class="clear  row">
        <div class="col-md-12">
            <form role="form" id="rategenerator-from" method="post" action="{{URL::to('rategenerators/'.$rategenerators->RateGeneratorId.'/update')}}" class="form-horizontal form-groups-bordered">
                <div class="panel panel-primary" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title">
                            Detail
                        </div>

                        <div class="panel-options">
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label for="field-1" class="col-sm-2 control-label">Type*</label>
                            <div class="col-sm-4">
                                {{Form::select('SelectType',$AllTypes, $rategenerators->SelectType,array("class"=>"form-control select2 small","disabled"=>"disabled"))}}
                            </div>
                            <label for="field-1" class="col-sm-2 control-label">Name*</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="RateGeneratorName" data-validate="required" data-message-required="." id="field-1" placeholder="" value="{{$rategenerators->RateGeneratorName}}" />
                            </div>
                        </div>
                        <div class="form-group" id="rate-ostion-trunk-div">
                            <label for="field-1" class="col-sm-2 control-label">Policy</label>
                            <div class="col-sm-4">
                                {{ Form::select('Policy', LCR::$policy, $rategenerators->Policy , array("class"=>"select2")) }}
                            </div>
                            <label for="field-1" class="col-sm-2 control-label">Trunk*</label>
                            <div class="col-sm-4">
                                {{ Form::select('TrunkID', $trunks, $rategenerators->TrunkID , array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="form-group" id="group-preference-div">
                            <label for="field-1" class="col-sm-2 control-label">Group By</label>
                            <div class="col-sm-4">
                                {{ Form::select('GroupBy', array('Code'=>'Code','Desc'=>'Description'), $rategenerators->GroupBy , array("class"=>"select2")) }}
                            </div>
                            <div class="col-sm-3">
                                <label for="field-1" class="control-label">Use Preference</label>
                                <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="If ON then vendors will be ordered based on Preference instead of rate." data-original-title="Use Preference">?</span>
                                <div class="make-switch switch-small">
                                    {{Form::checkbox('UsePreference', 1,  $rategenerators->UsePreference );}}
                                </div>
                            </div>
                            <div id="rate-aveg-div">
                                <div class="col-sm-3">
                                    <label for="field-1" class="control-label">Use Average</label>
                                    <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="IF ON system will take the average of all available vendor rates and use that." data-original-title="Use Average">?</span>

                                    <div class="make-switch switch-small">
                                        {{Form::checkbox('UseAverage', 1,  $rategenerators->UseAverage );}}
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($rategenerator->SelectType == 1  || $rategenerator->SelectType == 3)
                            <div class="form-group NonDID-Div">
                                <label for="field-1" class="col-sm-2 control-label">If calculated rate is less then</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" name="LessThenRate" value="{{(!empty($rategenerators->LessThenRate)?$rategenerators->LessThenRate:'')}}" />

                                </div>

                                <label for="field-1" class="col-sm-2 control-label">Change rate to</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" name="ChargeRate" value="{{(!empty($rategenerators->ChargeRate)?$rategenerators->ChargeRate:'')}}" />
                                </div>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="field-1" class="col-sm-2 control-label">Currency*</label>
                            <div class="col-sm-4">
                                <?php if($rategenerators->CurrencyID == ''){
                                    unset($array_op['disabled']);
                                }
                                ?>
                                        <!--{ Form::select('CurrencyID', $currencylist,  $rategenerators->CurrencyID, array_merge( array("class"=>"select2"),$array_op)) }}-->
                                {{Form::SelectControl('currency',0,$rategenerators->CurrencyID,($rategenerators->CurrencyID==''?0:1))}}
                                @if($rategenerators->CurrencyID !='')
                                    <input type="hidden" name="CurrencyID" readonly  value="{{$rategenerators->CurrencyID}}">
                                @endif
                            </div>

                            <label class="col-sm-2 control-label">Rate Position*</label>
                            <div class="col-sm-1">
                                <input type="text" class="form-control" name="RatePosition" data-validate="required" data-message-required="." id="field-1" placeholder="" value="{{$rategenerators->RatePosition}}" />
                            </div>
                            <div id="percentageRate">
                                <label class="col-sm-1 control-label">Percentage </label>
                                <div class="col-sm-2">
                                    <input type="text" class="form-control popover-primary" rows="1" id="percentageRate" name="percentageRate" value="{{$rategenerators->percentageRate}}" data-trigger="hover" data-toggle="popover" data-placement="top" data-content="Use vendor position mention in Rate Position unless vendor selected position is more then N% more costly than the previous vendor" data-original-title="Percentage" />
                                </div>
                            </div>
                        </div>
                        {{--<input type="hidden" name="GroupBy" value="Code">--}}

                        <div class="form-group">
                            <label class="col-sm-2 control-label">
                                Applied To
                                <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="This option is for Rate Tables created from this Rate Generator." data-original-title="Applied To">?</span>
                            </label>
                            <div class="col-sm-4">
                                <?php
                                $AppliedToCustomer = $rategenerators->AppliedTo == RateTable::APPLIED_TO_CUSTOMER ? true : false;
                                $AppliedToReseller = $rategenerators->AppliedTo == RateTable::APPLIED_TO_RESELLER ? true : false;
                                ?>
                                <label class="radio-inline">
                                    {{Form::radio('AppliedTo', RateTable::APPLIED_TO_CUSTOMER, $AppliedToCustomer,array("class"=>""))}}
                                    Customer
                                </label>
                                <label class="radio-inline">
                                    {{Form::radio('AppliedTo', RateTable::APPLIED_TO_RESELLER, $AppliedToReseller,array("class"=>""))}}
                                    Partner
                                </label>
                            </div>
                            <div class="ResellerBox" style="display: none;">
                                <label class="col-sm-2 control-label">Partner</label>
                                <div class="col-sm-4">
                                    {{Form::select('Reseller', $ResellerDD, $rategenerators->Reseller,array("class"=>"form-control select2"))}}
                                </div>
                            </div>
                        </div>

                        @if($rategenerator->SelectType == 1)
                            <div class="form-group NonDID-Div">
                                <label for="field-1" class="col-sm-2 control-label">CodeDeck*</label>
                                <div class="col-sm-4">
                                    {{ Form::select('codedeckid', $codedecklist,  $rategenerators->CodeDeckId, array_merge( array("class"=>"select2"),$array_op)) }}
                                    @if(isset($array_op['disabled']) && $array_op['disabled'] == 'disabled')
                                        <input type="hidden" name="codedeckid" readonly  value="{{$rategenerators->CodeDeckId}}">
                                    @endif
                                </div>
                                <label for="field-1" class="col-sm-2 control-label">Time Of Day*</label>
                                <div class="col-sm-4">
                                    {{ Form::select('Timezones[]', $Timezones, explode(',',$rategenerators->Timezones) , array("class"=>"select2 multiselect", "multiple"=>"multiple")) }}
                                </div>
                            </div>
                            <div class="form-group NonDID-Div">
                                <label class="col-sm-3 control-label">Merge Rate By Time Of Day</label>
                                <div class="col-sm-4">
                                    <div class="make-switch switch-small">
                                        {{Form::checkbox('IsMerge', 1,  $rategenerators->IsMerge, array('id' => 'IsMerge') );}}
                                    </div>
                                </div>
                                <label class="col-sm-2 control-label IsMerge">Take Price</label>
                                <div class="col-sm-4 IsMerge">
                                    {{ Form::select('TakePrice', array(RateGenerator::HIGHEST_PRICE=>'Highest Price',RateGenerator::LOWEST_PRICE=>'Lowest Price'), $rategenerators->TakePrice , array("class"=>"select2")) }}
                                </div>
                            </div>
                            <div class="form-group NonDID-Div">
                                <label class="col-sm-2 control-label IsMerge">Merge Into</label>
                                <div class="col-sm-4 IsMerge">
                                    {{ Form::select('MergeInto', $Timezones, $rategenerators->MergeInto , array("class"=>"select2")) }}
                                </div>
                                <div id="hide-components">
                                    <label for="field-1" class="col-sm-2 control-label">Components</label>
                                    <div class="col-sm-4">
                                        {{ Form::select('AllComponent[]', RateGenerator::$Component, explode("," ,$rategenerators->SelectedComponents) , array("class"=>"select2 multiselect" , "multiple"=>"multiple", "id"=>"AllComponent" )) }}
                                    </div>

                                </div>
                            </div>
                        @endif

                        @if($rategenerator->SelectType == 2 || $rategenerator->SelectType == 3)

                            @if($rategenerator->SelectType == 2)
                                <div class="form-group DID-Div">
                                    <label for="field-1" class="col-sm-2 control-label">Country*</label>
                                    <div class="col-sm-4">
                                        {{ Form::select('CountryID', $country, $rategenerators->CountryID, array("class"=>"select2")) }}
                                    </div>
                                    <label for="field-1" class="col-sm-2 control-label">Access Type*</label>
                                    <div class="col-sm-4">
                                        {{ Form::select('AccessType', $AccessType, $rategenerators->AccessType, array("class"=>"select2")) }}
                                    </div>
                                </div>
                                <div class="form-group DID-Div">
                                    <label for="field-1" class="col-sm-2 control-label">Prefix*</label>
                                    <div class="col-sm-4">
                                        {{ Form::select('Prefix', $Prefix, $rategenerators->Prefix, array("class"=>"select2")) }}
                                    </div>
                                    <label for="field-1" class="col-sm-2 control-label">City*</label>
                                    <div class="col-sm-4">
                                        {{ Form::select('City', $City, $rategenerators->City, array("class"=>"select2")) }}
                                    </div>
                                </div>
                            @endif
                            @if($rategenerator->SelectType == 2)
                                <div class="form-group" id="DIDCategoryDiv">
                                    <label for="field-1" class="col-sm-2 control-label">Tariff*</label>
                                    <div class="col-sm-4">
                                        {{ Form::select('Tariff', $Tariff,$rategenerators->Tariff, array("class"=>"select2")) }}
                                    </div>
                                    <label for="field-1" class="col-sm-2 control-label">Category*</label>
                                    <div class="col-sm-4">
                                        {{ Form::select('Category', $Categories, $rategenerators->DIDCategoryID , array("class"=>"select2")) }}
                                    </div>
                                </div>
                        @endif

                            <div class="form-group DID-Div Package-Div1">
                                <label for="DateFrom" class="col-sm-2 control-label">Date From*</label>
                                <div class="col-sm-4">
                                    <input id="DateFrom" type="text" name="DateFrom" class="form-control datepicker" data-date-format="yyyy-mm-dd" placeholder="YYYY-MM-DD" value="{{$rategenerators->DateFrom}}"/>
                                </div>
                                <label for="DateTo" class="col-sm-2 control-label">Date To*</label>
                                <div class="col-sm-4">
                                    <input id="DateTo" type="text" name="DateTo" class="form-control datepicker" data-date-format="yyyy-mm-dd" placeholder="YYYY-MM-DD" value="{{$rategenerators->DateTo}}"/>
                                </div>
                            </div>
                            <div class="form-group DID-Div Package-Div1">
                                <label for="Calls" class="col-sm-2 control-label">Calls</label>
                                <div class="col-sm-4">
                                    <input type="number" min="0" class="form-control" id="Calls" value="{{$rategenerators->Calls}}" name="Calls"/>
                                </div>
                                <label for="Minutes" class="col-sm-2 control-label">Minutes</label>
                                <div class="col-sm-4">
                                    <input type="number" min="0" class="form-control" id="Minutes" value="{{$rategenerators->Minutes}}" name="Minutes"/>
                                </div>
                            </div>
                            <div class="form-group DID-Div Package-Div1">
                                <label for="TimeOfDay" class="col-sm-2 control-label">Time of Day</label>
                                <div class="col-sm-4">
                                    {{ Form::select('TimezonesID', $Timezones, $rategenerators->TimezonesID, array("class"=>"select2")) }}
                                </div>
                                <label for="TimeOfDayPercentage" class="col-sm-2 control-label">Time of Day %</label>
                                <div class="col-sm-4">
                                    <input type="number" min="0" class="form-control" value="{{$rategenerators->TimezonesPercentage}}" id="TimeOfDayPercentage" name="TimezonesPercentage"/>
                                </div>
                            </div>
                            <div class="form-group DID-Div">
                                <label for="Origination" class="col-sm-2 control-label">Origination</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" id="Origination" value="{{$rategenerators->Origination}}" name="Origination"/>
                                </div>
                                <label for="OriginationPercentage" class="col-sm-2 control-label">Origination %</label>
                                <div class="col-sm-4">
                                    <input type="number" min="0" class="form-control" value="{{$rategenerators->OriginationPercentage}}" id="OriginationPercentage" name="OriginationPercentage"/>
                                </div>
                            </div>
                        @endif
                        @if($rategenerator->SelectType == 3)
                            <div class="form-group Package-Div" >
                                <label for="field-1" class="col-sm-2 control-label">Package*</label>
                                <div class="col-sm-4">
                                    {{ Form::select('PackageID', $Package, $rategenerators->PackageID, array("class"=>"select2")) }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="panel panel-primary" data-collapsed="0" id="Merge-components">
                    <div class="panel-heading">
                        <div class="panel-title">
                            Merge Components
                        </div>
                        <div class="panel-options">
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="" style="overflow: auto;">
                            <br/>
                            <input type="hidden" id="getIDs" name="getIDs" value=""/>
                            <table id="servicetableSubBox" class="table table-bordered datatable">
                                <thead>
                                <tr>
                                    <th style="width:250px;" class="Package-Div">Package</th>
                                    <th style="width:250px !important;">Component</th>
                                    <th style="width:250px !important;" class="DID-Div">From Country</th>
                                    <th style="width:250px !important;" class="DID-Div">From Access Type</th>
                                    <th style="width:250px !important;" class="DID-Div">From Prefix</th>
                                    <th style="width:250px !important;" class="DID-Div">From City</th>
                                    <th style="width:250px !important;" class="DID-Div">From Tariff</th>
                                    <th style="width:200px !important;" class="DID-Div">Origination</th>
                                    <th style="width:200px !important;">Time of Day</th>
                                    <th style="width:200px !important;">Action</th>
                                    <th style="width:200px !important;">Merge To</th>
                                    <th style="width:250px !important;" class="DID-Div">To Country</th>
                                    <th style="width:250px !important;" class="DID-Div">To Access Type</th>
                                    <th style="width:250px !important;" class="DID-Div">To Prefix</th>
                                    <th style="width:250px !important;" class="DID-Div">To City</th>
                                    <th style="width:250px !important;" class="DID-Div">To Tariff</th>
                                    <th style="width:200px !important;" class="DID-Div">To Origination</th>
                                    <th style="width:200px !important;">To Time of Day</th>
                                    <th style="width:40px !important;">
                                        <button type="button" onclick="createCloneRow('servicetableSubBox','getIDs')" id="Service-update" class="btn btn-primary btn-sm add-clone-row-btn" data-loading-text="Loading...">
                                            <i></i>
                                            +
                                        </button>
                                    </th>
                                </tr>
                                </thead>
                                <tbody id="tbody">
                                <?php
                                $ComponentsArr=array();
                                if(!empty($rategenerators->SelectedComponents)){
                                    $ComponentsArr = explode("," ,$rategenerators->SelectedComponents);
                                    $ComponentsArr = array_combine($ComponentsArr, $ComponentsArr);
                                }
                                if (isset($rategeneratorComponents) && count($rategeneratorComponents)  > 0 )
                                {
                                $a = 0;
                                $hiddenClass='';
                                $ComponentArray1 = array();

                                ?>

                                @foreach ($rategeneratorComponents as $Component)
                                    <?php
                                    $a++;
                                    if($a==1){
                                        $hiddenClass='hidden';
                                    }else{
                                        $hiddenClass='';
                                    }
                                    $ComponentArray = explode("," ,$Component->Component);
                                    foreach ($ComponentArray as $Component1) {
                                        $ComponentArray1[$Component1]=$Component1;
                                    }

                                    $ActionsArray = explode("," ,$Component->Action);
                                    foreach ($ActionsArray as $Action1) {
                                        $ActionsArray1[$Action1]=$Action1;
                                    }

                                    $MergeToArray = explode("," ,$Component->MergeTo);
                                    foreach ($MergeToArray as $MergeTo1) {
                                        $MergeToArray1[$MergeTo1]=$MergeTo1;
                                    }

                                    ?>
                                    <tr id="selectedRow-{{$a}}">
                                    {{-- @if($rategenerators->SelectType == 3)
                                        
                                    @endif --}}
                                        <td class="Package-Div">
                                            {{ Form::select('Package-'.$a, $Package, $Component->Package, array("class"=>"select2")) }}
                                        </td>
                                        <td id="testValues">
                                            @if($rategenerators->SelectType == 2)
                                            {{ Form::select('Component-'.$a.'[]', DiscountPlan::$RateTableDIDRate_Components, $ComponentArray1, array("class"=>"select2 selected-Components" ,'multiple', "id"=>"Component-".$a)) }}
                                            @elseif($rategenerators->SelectType == 3)
                                            {{ Form::select('Component-'.$a.'[]', DiscountPlan::$RateTablePKGRate_Components, $ComponentArray1, array("class"=>"select2 selected-Components" ,'multiple', "id"=>"Component-".$a)) }}
                                            @endif

                                        </td>
                                        <td class="DID-Div">
                                            {{ Form::select('FCountry-'.$a, $country, $Component->FromCountryID, array("class"=>"select2")) }}
                                        </td>
                                        <td class="DID-Div">
                                            {{ Form::select('FAccessType-'.$a, $AccessType, $Component->FromAccessType, array("class"=>"select2")) }}
                                        </td>
                                        <td class="DID-Div">
                                            {{ Form::select('FPrefix-'.$a, $Prefix, $Component->FromPrefix, array("class"=>"select2")) }}
                                        </td>
                                        <td class="DID-Div">
                                            {{ Form::select('FCity-'.$a, $City, $Component->FromCity, array("class"=>"select2")) }}
                                        </td>
                                        <td class="DID-Div">
                                            {{ Form::select('FTariff-'.$a, $Tariff, $Component->FromTariff, array("class"=>"select2")) }}
                                        </td>
                                        <td class="DID-Div">
                                            <input type="text" class="form-control" value="{{$Component->Origination}}" name="Origination-{{$a}}"/>
                                        </td>
                                        <td>
                                            {{ Form::select('TimeOfDay-'.$a, $Timezones, $Component->TimezonesID, array("class"=>"select2")) }}
                                        </td>
                                        <td>
                                            {{ Form::select('Action-'.$a,  RateGenerator::$Action, $ActionsArray1, array("class"=>"select2")) }}

                                        </td>
                                        <td>
                                            @if($rategenerators->SelectType == 2)
                                            {{ Form::select('MergeTo-'.$a,DiscountPlan::$RateTableDIDRate_Components,  $MergeToArray1 , array("class"=>"select2" , "id"=>"MergeTo-".$a)) }}
                                            @elseif($rategenerators->SelectType == 3)
                                            {{ Form::select('MergeTo-'.$a, DiscountPlan::$RateTablePKGRate_Components,  $MergeToArray1 , array("class"=>"select2" , "id"=>"MergeTo-".$a)) }}
                                            @endif
                                        </td>
                                        <td class="DID-Div">
                                            {{ Form::select('TCountry-'.$a, $country, $Component->ToCountryID, array("class"=>"select2")) }}
                                        </td>
                                        <td class="DID-Div">
                                            {{ Form::select('TAccessType-'.$a, $AccessType, $Component->ToAccessType, array("class"=>"select2")) }}
                                        </td>
                                        <td class="DID-Div">
                                            {{ Form::select('TPrefix-'.$a, $Prefix, $Component->ToPrefix, array("class"=>"select2")) }}
                                        </td>
                                        <td class="DID-Div">
                                            {{ Form::select('TCity-'.$a, $City, $Component->ToCity, array("class"=>"select2")) }}
                                        </td>
                                        <td class="DID-Div">
                                            {{ Form::select('TTariff-'.$a, $Tariff, $Component->ToTariff, array("class"=>"select2")) }}
                                        </td>
                                        <td class="DID-Div">
                                            <input type="text" class="form-control" value="{{$Component->ToOrigination}}" name="ToOrigination-{{$a}}"/>
                                        </td>
                                        <td>
                                            {{ Form::select('ToTimeOfDay-'.$a, $Timezones, $Component->ToTimezonesID, array("class"=>"select2")) }}
                                        </td>
                                        <td>

                                            <a onclick="deleteRow(this.id,'servicetableSubBox','getIDs')" id="merge-{{$a}}" class="btn btn-danger btn-sm" data-loading-text="Loading...">
                                                <i></i>
                                                -

                                            </a>
                                        </td>
                                    </tr>
                                    <?php $ComponentArray1 = null ; $ActionsArray1 = null; $MergeToArray1 = null;?>
                                @endforeach
                                <?php
                                }?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @if($rategenerator->SelectType == 2  || $rategenerator->SelectType == 3)
                    <div class="panel panel-primary" data-collapsed="0" id="Calculated-Rate">
                        <div class="panel-heading">
                            <div class="panel-title">
                                Calculated Rate
                            </div>
                            <div class="panel-options">
                                <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="" style="overflow: auto;">
                                <br/>
                                <input type="hidden" id="getRateIDs" name="getRateIDs" value=""/>
                                <table id="ratetableSubBox" class="table table-bordered datatable">
                                    <thead>
                                    <tr>
                                        <th style="width:250px;" class="Package-Div">Package</th>
                                        <th style="width:250px !important;">Component</th>
                                        <th style="width:250px !important;" class="DID-Div">Country</th>
                                        <th style="width:250px !important;" class="DID-Div">Access Type</th>
                                        <th style="width:250px !important;" class="DID-Div">Prefix</th>
                                        <th style="width:250px !important;" class="DID-Div">City</th>
                                        <th style="width:250px !important;" class="DID-Div">Tariff</th>
                                        <th style="width:200px !important;" class="DID-Div">Origination</th>
                                        <th style="width:200px !important;">Time of Day</th>
                                        <th style="width:200px !important;">Calculated Rate</th>
                                        <th style="width:200px !important;">Change Rate To</th>
                                        <th style="width:40px !important;"> 
                                            <button type="button" onclick="createCloneRow('ratetableSubBox','getRateIDs')" id="rate-update" class="btn btn-primary btn-sm add-clone-row-btn" data-loading-text="Loading...">
                                                <i></i>
                                                +
                                            </button>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody id="ratetbody">
                                    <?php
                                    $CalculatedArr=array();
                                    if (isset($rateGeneratorCalculatedRate) && count($rateGeneratorCalculatedRate) > 0)
                                    {
                                    $a = 0;
                                    $hiddenClass='';
                                    ?>

                                    @foreach ($rateGeneratorCalculatedRate as $calculatedRate)
                                        <?php
                                        $a++;
                                        if($a==1){
                                            $hiddenClass='hidden';
                                        }else{
                                            $hiddenClass='';
                                        }
                                        ?>
                                        <tr id="selectedRateRow-{{$a}}">
                                            <td class="Package-Div">
                                                {{ Form::select('Package1-'.$a, $Package, $calculatedRate->Package, array("class"=>"select2")) }}
                                            </td>
                                            <td>
                                                @if($rategenerators->SelectType == 2)
                                                {{ Form::select('RateComponent-'.$a.'[]',DiscountPlan::$RateTableDIDRate_Components, explode("," ,$calculatedRate->Component), array("class"=>"select2 selected-RComponents" ,'multiple', "id"=>"RateComponent-".$a)) }}
                                                @elseif($rategenerators->SelectType == 3)
                                                {{ Form::select('RateComponent-'.$a.'[]', DiscountPlan::$RateTablePKGRate_Components, explode("," ,$calculatedRate->Component), array("class"=>"select2 selected-RComponents" ,'multiple', "id"=>"RateComponent-".$a)) }}
                                                @endif
                                            </td>
                                            <td  class="DID-Div">
                                                {{ Form::select('Country1-'.$a, $country, $calculatedRate->CountryID, array("class"=>"select2")) }}
                                            </td>
                                            <td  class="DID-Div">
                                                {{ Form::select('AccessType1-'.$a, $AccessType, $calculatedRate->AccessType, array("class"=>"select2")) }}
                                            </td>
                                            <td  class="DID-Div">
                                                {{ Form::select('Prefix1-'.$a, $Prefix, $calculatedRate->Prefix, array("class"=>"select2")) }}
                                            </td>
                                            <td  class="DID-Div">
                                                {{ Form::select('City1-'.$a, $City, $calculatedRate->City, array("class"=>"select2")) }}
                                            </td>
                                            <td  class="DID-Div">
                                                {{ Form::select('Tariff1-'.$a, $Tariff, $calculatedRate->Tariff, array("class"=>"select2")) }}
                                            </td>
                                            <td class="DID-Div">
                                                <input type="text" class="form-control" value="{{$calculatedRate->Origination}}" name="RateOrigination-{{$a}}"/>
                                            </td>
                                            <td>
                                                {{ Form::select('RateTimeOfDay-'.$a, $Timezones, $calculatedRate->TimezonesID, array("class"=>"select2")) }}
                                            </td>
                                            <td>
                                                <input type="number" min="0" value="{{$calculatedRate->RateLessThen}}" class="form-control" name="RateLessThen-{{$a}}"/>
                                            </td>
                                            <td>
                                                <input type="number" min="0" value="{{$calculatedRate->ChangeRateTo}}" class="form-control" name="ChangeRateTo-{{$a}}"/>
                                            </td>
                                            <td>
                                                {{--<button type="button" onclick="createCloneRow('ratetableSubBox','getRateIDs')" id="rate-update" class="btn btn-primary btn-sm add-clone-row-btn" data-loading-text="Loading...">--}}
                                                    {{--<i></i>--}}
                                                    {{--+--}}
                                                {{--</button>--}}
                                                <a onclick="deleteRow(this.id,'ratetableSubBox','getRateIDs')" id="rateCal-{{$a}}" class="btn btn-danger btn-sm" data-loading-text="Loading..." >
                                                    <i></i>
                                                    -
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    <?php }?>
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>
                @endif
            </form>
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        Margins
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body">

                    <div class="pull-right">

                        <a  href="{{URL::to('/rategenerators')}}/{{$rategenerators->RateGeneratorId}}/rule/add" class="btn addnew btn-primary btn-sm btn-icon icon-left" >
                            <i class="entypo-floppy"></i>
                            Add New
                        </a>
                        <br><br>
                    </div>

                    @if(count($rategenerator_rules))
                        <form id="RateRulesDataFrom" method="POST" />
                        <div class="dataTables_wrapper clear-both">
                            <table class="table table-bordered datatable" id="table-4">
                                <thead>
                                <tr>
                                    @if($rategenerator->SelectType == 3)
                                        <th width="15%">Package</th>
                                    @endif
                                    @if($rategenerator->SelectType == 2  || $rategenerator->SelectType == 3)
                                        <th width="25%">Component</th>
                                        @if($rategenerator->SelectType == 2)
                                            <th width="15%">Origination</th>
                                        @endif
                                        <th width="15%">Time of Day</th>
                                    @else
                                        <th width="25%">Rate Filter Origination</th>
                                        <th width="15%">Rate Filter Destination</th>
                                        <th width="15%">Sources</th>
                                    @endif
                                    <th width="30%">Margins</th>
                                    <th width="15%">Action</th>
                                </tr>
                                </thead>
                                <tbody id="sortable">
                                @foreach($rategenerator_rules as $rategenerator_rule)
                                    <tr class="odd gradeX" data-id="{{$rategenerator_rule->RateRuleId}}">
                                        @if($rategenerator->SelectType == 3)
                                            <td>
                                                @if(@$rategenerator_rule->Package != '') {{Package::getServiceNameByID($rategenerator_rule->Package)}} @else ALL @endif
                                            </td>    
                                        @endif
                                        @if($rategenerator->SelectType == 2  || $rategenerator->SelectType == 3)
                                            <td>
                                                @if($rategenerator->SelectType == 3)
                                                    {{ @DiscountPlan::$RateTablePKGRate_Components[$rategenerator_rule->Component] }}
                                                @elseif($rategenerator->SelectType == 2)
                                                    {{ @ DiscountPlan::$RateTableDIDRate_Components[$rategenerator_rule->Component] }}
                                                @endif
                                                @if($rategenerator->SelectType == 2)
                                                    <br>
                                                    Country: @if(@$rategenerator_rule->Country != ''){{@$rategenerator_rule->Country->Country}}@else ALL @endif
                                                    <br>
                                                        Type: @if(@$rategenerator_rule->AccessType != ''){{@$rategenerator_rule->AccessType}} @else ALL @endif
                                                    <br>
                                                        Prefix: @if(@$rategenerator_rule->Prefix != ''){{@$rategenerator_rule->Prefix}} @else ALL @endif
                                                    <br>
                                                        City: @if(@$rategenerator_rule->City != ''){{@$rategenerator_rule->City}} @else ALL @endif
                                                    <br>
                                                        Tariff: @if(@$rategenerator_rule->Tariff != ''){{@$rategenerator_rule->Tariff}} @else ALL @endif    
                                                @endif                                               
                                            </td>
                                            @if($rategenerator->SelectType == 2)
                                            <td>
                                                {{$rategenerator_rule->OriginationDescription }}
                                            </td>
                                            @endif
                                            <td>
                                                {{@$Timezones[$rategenerator_rule->TimeOfDay] }}
                                            </td>
                                        @else
                                            <td>
                                                {{$rategenerator_rule->OriginationCode}}@if(!empty($rategenerator_rule->OriginationCode)) <br/> @endif
                                                @if($rategenerator_rule->OriginationType != '')
                                                    Type : {{$rategenerator_rule->OriginationType}}
                                                    <br>    
                                                @else
                                                    Type : ALL     
                                                    <br>
                                                @endif
                                                @if($rategenerator_rule->OriginationCountryID != '')
                                                    Country : {{Country::getName($rategenerator_rule->OriginationCountryID)}}
                                                @else
                                                    Country : ALL    
                                                @endif
                                            </td>
                                            <td>
                                                {{$rategenerator_rule->Code}}@if(!empty($rategenerator_rule->Code)) <br/> @endif
                                                @if($rategenerator_rule->DestinationType != '')
                                                    Type : {{$rategenerator_rule->DestinationType}}
                                                    <br> 
                                                @else
                                                    Type : ALL    
                                                    <br>
                                                @endif
                                                @if($rategenerator_rule->DestinationCountryID != '')
                                                    Country : {{Country::getName($rategenerator_rule->DestinationCountryID)}}
                                                @else
                                                    Country : ALL    
                                                @endif
                                            </td>

                                            
                                            <td>
                                                @if(count($rategenerator_rule['RateRuleSource']))
                                                    @foreach($rategenerator_rule['RateRuleSource'] as $rateruleSource )
                                                        {{Account::getCompanyNameByID($rateruleSource->AccountId);}}<br>
                                                    @endforeach
                                                @endif
                                            </td>
                                        @endif
                                        <td>
                                            @if(count($rategenerator_rule['RateRuleMargin']))
                                                @foreach($rategenerator_rule['RateRuleMargin'] as $index=>$materulemargin )
                                                    {{$materulemargin->MinRate}} {{$index!=0?'<':'<='}}  rate <= {{$materulemargin->MaxRate}} {{$materulemargin->AddMargin}} {{$materulemargin->FixedValue}} <br>

                                                @endforeach
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{URL::to('/rategenerators/'.$id. '/rule/' . $rategenerator_rule->RateRuleId .'/edit' )}}" id="add-new-margin" class="update btn btn-primary btn-sm">
                                                <i class="entypo-pencil"></i>
                                            </a>

                                            <a href="{{URL::to('/rategenerators/'.$id. '/rule/' . $rategenerator_rule->RateRuleId .'/clone_rule' )}}" data-rate-generator-id="{{$id}}" id="clone-rule" class="clone_rule btn btn-default  btn-sm" data-original-title="Clone" title="" data-placement="top" data-toggle="tooltip" data-loading-text="...">
                                                <i class="fa fa-clone"></i>
                                            </a>

                                            <a href="{{URL::to('/rategenerators/'.$id. '/rule/' . $rategenerator_rule->RateRuleId .'/delete' )}}" class="btn delete btn-danger btn-sm" data = "0" data-redirect="{{Request::url()}}">
                                                <i class="entypo-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <input type="hidden" name="main_fields_sort" id="main_fields_sort" value="">
                        </form>
                    @endif

                </div>
            </div>
        </div>

    </div>
    <div class="hidden">
        <table id="table-1">
            <tr id="selectedRow-">
                <td class="Package-Div">
                    {{ Form::select('Package-1', $Package, '', array("class"=>"select2")) }}
                </td>
                <td id="testValuess">
                    {{--{{ Form::select('Component-1[]', RateGenerator::$Component, null, array("class"=>"select2 selected-Components" ,'multiple', "id"=>"Component-1")) }}--}}
                </td>
                <td class="DID-Div">
                    {{ Form::select('FCountry-1', $country, '', array("class"=>"select2")) }}
                </td>
                <td class="DID-Div">
                    {{ Form::select('FAccessType-1', $AccessType, '', array("class"=>"select2")) }}
                </td>
                <td class="DID-Div">
                    {{ Form::select('FPrefix-1', $Prefix, '', array("class"=>"select2")) }}
                </td>
                <td class="DID-Div">
                    {{ Form::select('FCity-1', $City, null, array("class"=>"select2")) }}
                </td>
                <td class="DID-Div">
                    {{ Form::select('FTariff-1', $Tariff, null, array("class"=>"select2")) }}
                </td>
                
                <td class="DID-Div">
                    <input type="text" class="form-control" name="Origination-1"/>
                </td>
                <td>
                    {{ Form::select('TimeOfDay-1', $Timezones, '', array("class"=>"select2")) }}
                </td>
                <td>
                    {{ Form::select('Action-1',  RateGenerator::$Action, RateGenerator::$Action, array("class"=>"select2")) }}

                </td>
                <td class="mergetestvalues">
                    {{--{{ Form::select('MergeTo-1', RateGenerator::$Component,  'OneOffCost' , array("class"=>"select2" , "id"=>"MergeTo-1")) }}--}}

                </td>
                <td class="DID-Div">
                    {{ Form::select('TCountry-1', $country, '', array("class"=>"select2")) }}
                </td>
                <td class="DID-Div">
                    {{ Form::select('TAccessType-1', $AccessType, '', array("class"=>"select2")) }}
                </td>
                <td class="DID-Div">
                    {{ Form::select('TPrefix-1', $Prefix, '', array("class"=>"select2")) }}
                </td>
                <td class="DID-Div">
                    {{ Form::select('TCity-1', $City, null, array("class"=>"select2")) }}
                </td>
                <td class="DID-Div">
                    {{ Form::select('TTariff-1', $Tariff, null, array("class"=>"select2")) }}
                </td>
                <td class="DID-Div">
                    <input type="text" class="form-control" name="ToOrigination-1"/>
                </td>
                <td>
                    {{ Form::select('ToTimeOfDay-1', $Timezones, '', array("class"=>"select2")) }}
                </td>
                <td>

                    <a onclick="deleteRow(this.id, 'servicetableSubBox','getIDs')" id="merge-0" class="btn btn-danger btn-sm" data-loading-text="Loading..." >
                        <i></i>
                        -
                    </a>
                </td>
            </tr>
        </table>
        <table id="table-2">
            <tr id="selectedRateRow-0">
                <td class="Package-Div">
                    {{ Form::select('Package1-1', $Package, '', array("class"=>"select2")) }}
                </td>
                <td id="testRateValues">
                    {{--{{ Form::select('RateComponent-1[]', RateGenerator::$Component, '', array("class"=>"select2 selected-Components" ,'multiple', "id"=>"RateComponent-1")) }}--}}
                </td>
                <td class="DID-Div">
                    {{ Form::select('Country1-1', $country, '', array("class"=>"select2")) }}
                </td>
                <td class="DID-Div">
                    {{ Form::select('AccessType1-1', $AccessType, '', array("class"=>"select2")) }}
                </td>
                <td class="DID-Div">
                    {{ Form::select('Prefix1-1', $Prefix, '', array("class"=>"select2")) }}
                </td>
                <td class="DID-Div">
                    {{ Form::select('City1-1', $City, null, array("class"=>"select2")) }}
                </td>
                <td class="DID-Div">
                    {{ Form::select('Tariff1-1', $Tariff, null, array("class"=>"select2")) }}
                </td>
                <td  class="DID-Div">
                    <input type="text" class="form-control" name="RateOrigination-1"/>
                </td>
                <td>
                    {{ Form::select('RateTimeOfDay-1', $Timezones, '', array("class"=>"select2")) }}
                </td>
                <td>
                    <input type="number" min="0" class="form-control" name="RateLessThen-1"/>
                </td>
                <td>
                    <input type="number" min="0" class="form-control" name="ChangeRateTo-1"/>
                </td>
                <td>
                    <a onclick="deleteRow(this.id,'ratetableSubBox','getRateIDs')" id="rateCal-0" class="btn btn-danger btn-sm" data-loading-text="Loading..." >
                        <i></i>
                        -
                    </a>
                </td>
            </tr>
        </table>

    </div>
    <style>
        #sortable tr:hover {
            cursor: all-scroll;
        }
        .IsMerge {
            display: none;
        }
        #servicetableSubBox {
            width:3200px;
            overflow-x: auto;
        }
        #ratetableSubBox{
            width:1900px;
            overflow-x: auto;
        }
    </style>
    <script type="text/javascript">

        function getIds(tblID, idInp){
            $('#'+idInp).val("");
            $('#' + tblID + ' tbody tr').each(function() {

                var row = tblID == "servicetableSubBox" ? "selectedRow-0" : "selectedRateRow-0";
                var id = 0;
                if(this.id != row)
                    id = getNumber(this.id);

                var getIDString =  $('#'+idInp).val();
                getIDString = getIDString + id + ',';
                $('#'+idInp).val(getIDString);

            });
        }

        jQuery(document).ready(function() {
            /*$(".btn.addnew").click(function(ev) {
             jQuery('#modal-rate-generator-rule').modal('show', {backdrop: 'static'});
             });*/
            // $( "#sortable" ).sortable();

            var selectAllComponents;

            $("#DateFrom").datepicker({
                todayBtn:  1,
                autoclose: true
            }).on('changeDate', function (selected) {
                var minDate = new Date(selected.date.valueOf());
                var endDate = $('#DateTo');
                endDate.datepicker('setStartDate', minDate);
                if(endDate.val() && new Date(endDate.val()) != undefined) {
                    if(minDate > new Date(endDate.val()))
                        endDate.datepicker("setDate", minDate)
                }
            });

            $("#DateTo").datepicker({autoclose: true})
                    .on('changeDate', function (selected) {
                        var maxDate = new Date(selected.date.valueOf());
                        //$('#StartDate').datepicker('setEndDate', maxDate);
                    });

            if(new Date($('#DateFrom').val()) != undefined){
                $("#DateTo").datepicker('setStartDate', new Date($('#DateFrom').val()))
            }

            var TypeValue = $("#rategenerator-from [name='SelectType']").val();

            if(TypeValue == 2){
                $("#rate-ostion-trunk-div").hide();
                $("#rate-aveg-div").hide();
                $("#group-preference-div").hide();
                $("#DIDCategoryDiv").show();
                $("#Merge-components").show();
                $(".DID-Div").show();
                $(".NonDID-Div").hide();

            }else if(TypeValue == 1){
                $("#rate-ostion-trunk-div").show();
                $("#rate-aveg-div").show();
                $("#group-preference-div").show();
                $("#Merge-components").hide();
                $("#DIDCategoryDiv").hide();
                $("#hide-components").hide();
                $(".DID-Div").hide();
                $(".NonDID-Div").show();
            }else if(TypeValue == 3) {
                $(".DID-Div").hide();
                $(".Package-Div1").show();
                $(".NonDID-Div").hide();
                $(".Package-Div").show();
                $("#rate-ostion-trunk-div").hide();
                $("#rate-aveg-div").hide();
                $("#group-preference-div").hide();
                $("#DIDCategoryDiv").hide();
                $("#Merge-components").show();
                $('#servicetableSubBox').css('width','1225px');
                $('#ratetableSubBox').css('width','1025px');
                $('#testValuess').html('{{ Form::select("Component-1[]", DiscountPlan::$RateTablePKGRate_Components , null, array("class"=>"PKG" ,"multiple", "id"=>"Component-1")) }}');
                $('#testRateValues').html('{{ Form::select("RateComponent-1[]", DiscountPlan::$RateTablePKGRate_Components , null, array("class"=>"PKG" ,"multiple", "id"=>"RateComponent-1")) }}');
                $('.mergetestvalues').html('{{ Form::select("MergeTo-1",DiscountPlan::$RateTablePKGRate_Components , null, array("class"=>"PKG", "id"=>"MergeTo-1")) }}');

                $('.PKG').select2();
            } else {
                $(".DID-Div").hide();
                $(".NonDID-Div").show();
            }

            $(window).load(function() {
                getIds("servicetableSubBox", "getIDs");
                getIds("ratetableSubBox", "getRateIDs");
            });


            $( "#AllComponent" ).on('change', function() {

                var selectAllComponents = $("#AllComponent").val();
                selectAllComponents = String(selectAllComponents);
                var ComponentsArray = selectAllComponents.split(',');
                var addCostComponents = new Array();
                var j= 0;


                $('#servicetableSubBox tbody tr').each(function() {

                    if(this.id == 'selectedRow-0')
                        var id = 0;
                    else
                        var id = getNumber(this.id);

                    var component = $('#Component-' + id).val();
                    component = String(component);
                    var getCompArray = component.split(',');

//                for( var i = 0; i < ComponentsArray.length; i++){
//                    alert(ComponentsArray[i]);
//                    if ( ComponentsArray[i] == getCompArray[i]) {
//                          ComponentsArray.splice(ComponentsArray.indexOf(i), 1);
//                    }
//                }
//                $('#MergeTo-'+id+' option').each(function() {
//                    $(this).remove();
//                });
                    $('#Component-'+id+' option').each(function() {
                        $(this).remove();
                    });
                    $('#MergeTo-'+id+' option').each(function() {
                        $(this).remove();
                    });
                    var i;
                    for (i = 0; i < ComponentsArray.length; ++i) {
                        var data = {
                            id: ComponentsArray[i],
                            text: ComponentsArray[i]
                        };

                        if( typeof data.id != 'undefined' && data.id  != 'null'){

                            var newOption = new Option(data.text, data.id, false, false);
                            var newOption2 = new Option(data.text, data.id, false, false);

                            $('#Component-'+id).append(newOption).trigger('change');
                            $('#MergeTo-'+id).append(newOption2).trigger('change');
                        }
                    }

                    $('#Component-'+id).select2().select2('val', getCompArray);

                });

            });

            var TypeValue = $("#rategenerator-from [name='SelectType']").val();
            if(TypeValue == 2){
                $("#rate-ostion-trunk-div").hide();
                $("#rate-aveg-div").hide();
                $("#group-preference-div").hide();
                $("#DIDCategoryDiv").show();
                $("#Merge-components").show();
                $(".DID-Div").show();
                $(".NonDID-Div").hide();
                $(".Package-Div").hide();
                $('#testValuess').html('{{ Form::select("Component-1[]",DiscountPlan::$RateTableDIDRate_Components , null, array("class"=>"DID" ,"multiple", "id"=>"Component-1")) }}');
                $('#testRateValues').html('{{ Form::select("RateComponent-1[]",DiscountPlan::$RateTableDIDRate_Components , null, array("class"=>"DID" ,"multiple", "id"=>"RateComponent-1")) }}');
                $('.mergetestvalues').html('{{ Form::select("MergeTo-1",DiscountPlan::$RateTableDIDRate_Components , null, array("class"=>"DID", "id"=>"MergeTo-1")) }}');
                $('.DID').select2();
            }else if(TypeValue == 1){
                $("#rate-ostion-trunk-div").show();
                $("#rate-aveg-div").show();
                $("#group-preference-div").show();
                $("#Merge-components").hide();
                $("#DIDCategoryDiv").hide();
                $("#hide-components").hide();
                $(".DID-Div").hide();
                $(".NonDID-Div").show();
            }else if(TypeValue == 3) {
                $(".DID-Div").hide();
                $(".Package-Div1").show();
                $(".NonDID-Div").hide();
                $(".Package-Div").show();
                $("#rate-ostion-trunk-div").hide();
                $("#rate-aveg-div").hide();
                $("#group-preference-div").hide();
                $("#DIDCategoryDiv").hide();
                $("#Merge-components").show();
                $('#servicetableSubBox').css('width','1225px');
                $('#ratetableSubBox').css('width','1025px');
                $('#testValuess').html('{{ Form::select("Component-1[]", DiscountPlan::$RateTablePKGRate_Components , null, array("class"=>"PKG" ,"multiple", "id"=>"Component-1")) }}');
                $('#testRateValues').html('{{ Form::select("RateComponent-1[]", DiscountPlan::$RateTablePKGRate_Components , null, array("class"=>"PKG" ,"multiple", "id"=>"RateComponent-1")) }}');
                $('.mergetestvalues').html('{{ Form::select("MergeTo-1",DiscountPlan::$RateTablePKGRate_Components , null, array("class"=>"PKG", "id"=>"MergeTo-1")) }}');
               

                $('.PKG').select2();
            }
            else {
                $(".DID-Div").hide();
                $(".NonDID-Div").show();
            }

            function initSortable(){
                // Code using $ as usual goes here.
                $('#sortable').sortable({
                    connectWith: '#sortable',
                    placeholder: 'placeholder',
                    start: function() {
                        //setting current draggable item
                        currentDrageable = $('#sortable');
                    },
                    stop: function(ev,ui) {
                        saveOrder();
                        //de-setting draggable item after submit order.
                        currentDrageable = '';
                    }
                });
            }
            function saveOrder() {
                var Ticketfields_array   = 	new Array();
                $('#sortable tr').each(function(index, element) {
                    var TicketfieldsSortArray  =  {};
                    TicketfieldsSortArray["data_id"] = $(element).attr('data-id');
                    TicketfieldsSortArray["Order"] = index+1;

                    Ticketfields_array.push(TicketfieldsSortArray);
                });
                var data_sort_fields =  JSON.stringify(Ticketfields_array);
                $('#main_fields_sort').val(data_sort_fields);
                $('#RateRulesDataFrom').submit();
            }

            $('#RateRulesDataFrom').submit(function(e){
                e.stopPropagation();
                e.preventDefault();

                var formData = new FormData($(this)[0]);
                var url		 = baseurl + '/rategenerators/update_fields_sorting';

                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if(response.status =='success'){

                            toastr.success(response.message, "Success", toastr_opts);
                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                    },
                    // Form data
                    data: formData,
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
                return false;
            });

            initSortable();

            $(".update_form.btn").click(function(ev) {
                $("#rategenerator-from").submit();
            });

            $(".btn.change_status").click(function (e) {
                //redirect = ($(this).attr("data-redirect") == 'undefined') ? "{{URL::to('/rate_tables')}}" : $(this).attr("data-redirect");
                $(this).button('loading');
                $.ajax({
                    url: $(this).attr("href"),
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        $(this).button('reset');
                        if (response.status == 'success') {
                            toastr.success(response.message, "Success", toastr_opts);
                            location.reload();
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                    },

                    // Form data
                    //data: {},
                    cache: false,
                    contentType: false,
                    processData: false
                });
                return false;
            });

            $(".generate_rate.create").click(function (e) {
                e.preventDefault();
                $('#update-rate-generator-form').trigger("reset");
                $('#modal-update-rate').modal('show', {backdrop: 'static'});
                $('.radio-replace').removeClass('checked');
                $('#defaultradiorate').addClass('checked');
                $('#RateTableIDid').hide();
                $('#RateTableNameid').show();
                $('#RateTableReplaceRate').hide();
                $('#RateTableEffectiveRate').show();
                $('#modal-update-rate h4').html('Generate Rate Table');
                update_rate_table_url = $(this).attr("href");

                return false;

            });

            $('body').on('click', '.generate_rate.update', function (e) {
                e.preventDefault();
                $('#modal-update-rate').modal('show', {backdrop: 'static'});
                $('#update-rate-generator-form').trigger("reset");
                var trunkID = $(this).attr("data-trunk");
                var codeDeckId = $(this).attr("data-codedeck");
                var CurrencyID = $(this).attr("data-currency");
                var Type = $(this).attr("data-type");
                var AppliedTo = $(this).attr("data-AppliedTo");
                $.ajax({
                    url: baseurl + "/rategenerators/ajax_load_rate_table_dropdown",
                    type: 'GET',
                    dataType: 'text',
                    success: function(response) {

                        $("#modal-update-rate #DropdownRateTableID").html('');
                        $("#modal-update-rate #DropdownRateTableID").html(response);
                        $("#modal-update-rate #DropdownRateTableID select.select2").addClass('visible');
                        $("#modal-update-rate #DropdownRateTableID select.select2").select2();

                    },
                    // Form data
                    data: "TrunkID="+trunkID+'&CodeDeckId='+codeDeckId+'&CurrencyID='+CurrencyID+'&Type='+Type+'&AppliedTo='+AppliedTo,
                    cache: false,
                    contentType: false,
                    processData: false
                });
                /*
                 * Submit and Generate Joblog
                 * */
                update_rate_table_url = $(this).attr("href");

                $('.radio-replace').removeClass('checked');
                $('#defaultradiorate').addClass('checked');

                $('#RateTableIDid').show();
                $('#RateTableReplaceRate').show();
                $('#RateTableEffectiveRate').show();
                $('#RateTableNameid').hide();
                $('#modal-update-rate h4').html('Update Rate Table');
            });

            $('#update-rate-generator-form').submit(function (e) {
                e.preventDefault();
                if( typeof update_rate_table_url != 'undefined' && update_rate_table_url != '' ){
                    $.ajax({
                        url: update_rate_table_url,
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            $(".btn.generate").button('reset');
                            if (response.status == 'success') {
                                toastr.success(response.message, "Success", toastr_opts);
                                reloadJobsDrodown(0);
                                $('#modal-update-rate').modal('hide');
                            } else {
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                            $(".btn.generate").button('reset');
                            $(".save.TrunkSelect").button('reset');

                        },
                        // Form data
                        data: $('#update-rate-generator-form').serialize(),
                        cache: false

                    });
                }else{
                    $(".btn").button('reset');
                    $('#modal-update-rate').modal('hide');
                    toastr.info('Nothing Changed. Try again', "info", toastr_opts);
                }
            });


            $(".btn.delete_rate").click(function (e) {
                e.preventDefault();
                var id = $(this).attr('data-id');
                var url = baseurl + '/rategenerators/'+id+'/ajax_existing_rategenerator_cronjob';
                $('#delete-rate-generator-form [name="RateGeneratorID"]').val(id);
                if(confirm('Are you sure you want to delete selected rate generator?')) {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        dataType: 'html',
                        success: function (response) {
                            $(".btn.delete_rate").button('reset');
                            if (response) {
                                $('#modal-delete-rategenerator .container').html(response);
                                $('#modal-delete-rategenerator').modal('show');
                            }else{
                                $('#delete-rate-generator-form').submit();
                            }
                        },

                        // Form data
                        //data: {},
                        cache: false,
                        contentType: false,
                        processData: false
                    });
                }
                return false;

            });
            $(".btn.clone_rule").click(function (e) {
                e.preventDefault();
                $(this).button('loading');
                var url = $(this).attr('href');
                var rate_generator_id = $(this).attr('data-rate-generator-id');

                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {

                        if (response.status == 'success') {
                            toastr.success(response.message, "Success", toastr_opts);
                            var new_rule_url = baseurl + '/rategenerators/' + rate_generator_id + '/rule/' + response.RateRuleID + '/edit';

                            setTimeout( function() {  window.location = new_rule_url } ,1000 );
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                        $(".btn.clone_rule").button('reset');


                    },

                    // Form data
                    //data: {},
                    cache: false,
                    contentType: false,
                    processData: false
                });
                return false;

            });


            $('#delete-rate-generator-form').submit(function (e) {
                e.preventDefault();
                if($('#modal-delete-rategenerator .container').is(':empty')) {
                    var RateGeneratorID = $(this).find('[name="RateGeneratorID"]').val();
                    var url = baseurl + '/rategenerators/' + RateGeneratorID + '/delete';
                    $.ajax({
                        url: url,
                        type: 'POST',
                        dataType: 'json',
                        success: function (response) {
                            if (response.status == 'success') {
                                toastr.success(response.message, "Success", toastr_opts);
                                $('#modal-delete-rategenerator').modal('hide');
                                window.location = "{{URL::to('/rategenerators')}}";
                            } else {
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                            $(".save.TrunkSelect").button('reset');

                        },
                        // Form data
                        data: $('#update-rate-generator-form').serialize(),
                        cache: false

                    });
                }else{
                    var SelectedIDs = getselectedIDs("cronjob-table");
                    if (SelectedIDs.length == 0) {
                        alert('No cron job selected.');
                        $("#rategenerator-select").button('reset');
                        return false;
                    }else{
                        var deleteid = SelectedIDs.join(",");
                        cronjobsdelete(deleteid);
                    }
                }
            });

            function cronjobsdelete(deleteid){
                if(confirm('Are you sure you want to delete selected cron job?')){
                    var rateGeneratorID = $('#delete-rate-generator-form [name="RateGeneratorID"]').val();
                    var url = baseurl + "/rategenerators/"+rateGeneratorID+"/deletecronjob";
                    var cronjobs = deleteid;
                    $('#modal-delete-rategenerator .container').html('');
                    $('#modal-delete-rategenerator').modal('hide');
                    $.ajax({
                        url: url,
                        type:'POST',
                        data:{cronjobs:cronjobs},
                        datatype:'json',
                        success: function(response) {
                            if (response.status == 'success') {
                                toastr.success(response.message,'Success', toastr_opts);
                                var url = baseurl + '/rategenerators/'+rateGeneratorID+'/ajax_existing_rategenerator_cronjob';
                                $('#delete-rate-generator-form [name="RateGeneratorID"]').val(rateGeneratorID);
                                $.ajax({
                                    url: url,
                                    type: 'POST',
                                    dataType: 'html',
                                    success: function (response) {
                                        $(".btn.delete_rate").button('reset');
                                        if (response) {
                                            $('#modal-delete-rategenerator .container').html(response);
                                            $('#modal-delete-rategenerator').modal('show');
                                        }else{
                                            $('#delete-rate-generator-form').submit();
                                        }
                                    },

                                    // Form data
                                    //data: {},
                                    cache: false,
                                    contentType: false,
                                    processData: false
                                });
                            }else{
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                        }

                    });
                }
            }

            function getselectedIDs(table){
                var SelectedIDs = [];
                $('#'+table+' tr .rowcheckbox:checked').each(function (i, el) {
                    var cronjob = $(this).val();
                    SelectedIDs[i++] = cronjob;
                });
                return SelectedIDs;
            }

            $(document).on('click','#selectall',function(){
                if($(this).is(':checked')){
                    checked = 'checked=checked';
                    $(this).prop("checked", true);
                    $(this).parents('table').find('tbody tr').each(function (i, el) {
                        $(this).find('.rowcheckbox').prop("checked", true);
                        $(this).addClass('selected');
                    });
                }else{
                    checked = '';
                    $(this).prop("checked", false);
                    $(this).parents('table').find('tbody tr').each(function (i, el) {
                        $(this).find('.rowcheckbox').prop("checked", false);
                        $(this).removeClass('selected');
                    });
                }
            });

            // when page load check if IsMerge checked or not
            if($('#IsMerge').is(':checked')) {
                $('.IsMerge').show();
            } else {
                $('.IsMerge').hide();
            }
            $('#IsMerge').on('change', function(e, s) {
                if($('#IsMerge').is(':checked')) {
                    $('.IsMerge').show();
                } else {
                    $('.IsMerge').hide();
                }
            });

            $("#rategenerator-from [name='SelectType']").on('change', function() {
                var TypeValue = $(this).val();
                if(TypeValue == 2){
                    $("#rate-ostion-trunk-div").hide();
                    $("#rate-aveg-div").hide();
                    $("#group-preference-div").hide();
                    $("#Merge-components").show();
                    $("#DIDCategoryDiv").show();
                    $("#hide-components").show();
                    $(".DID-Div").show();
                    $(".NonDID-Div").hide();
                    $('#testValues').html('{{ Form::select("Component-1[]",DiscountPlan::$RateTableDIDRate_Components , null, array("class"=>"DID" ,"multiple", "id"=>"Component-1")) }}');
                    $('#testRateValues').html('{{ Form::select("RateComponent-1[]",DiscountPlan::$RateTableDIDRate_Components , null, array("class"=>"DID" ,"multiple", "id"=>"RateComponent-1")) }}');
                    $('.DID').select2();
                    $(".DID").prop("disabled", false);
                }else if(TypeValue == 1){
                    $("#rate-ostion-trunk-div").show();
                    $("#rate-aveg-div").show();
                    $("#group-preference-div").show();
                    $("#Merge-components").hide();
                    $("#hide-components").hide();
                    $("#DIDCategoryDiv").hide();
                    $(".DID-Div").hide();
                    $(".NonDID-Div").show();
                }else if(TypeValue == 3) {
                    $(".DID-Div").hide();
                    $(".Package-Div1").show();
                    $(".NonDID-Div").hide();
                    $(".Package-Div").show();
                    $("#rate-ostion-trunk-div").hide();
                    $("#rate-aveg-div").hide();
                    $("#group-preference-div").hide();
                    $("#DIDCategoryDiv").hide();
                    $("#Merge-components").show();
                    $('#testValues').html('{{ Form::select("Component-1[]", DiscountPlan::$RateTablePKGRate_Components , null, array("class"=>"PKG" ,"multiple", "id"=>"Component-1")) }}');
                    $('#testRateValues').html('{{ Form::select("RateComponent-1[]", DiscountPlan::$RateTablePKGRate_Components , null, array("class"=>"PKG" ,"multiple", "id"=>"RateComponent-1")) }}');
                    $('.PKG').select2();
                }
                else {
                    $(".DID-Div").hide();
                    $(".NonDID-Div").show();
                }
            });

            $('#rategenerator-from input[name="AppliedTo"]').change(function() {
                var AppliedTo = $('#rategenerator-from input[name="AppliedTo"]:checked').val();

                if(AppliedTo == {{ RateTable::APPLIED_TO_RESELLER }}) {
                    $('#rategenerator-from select[name="Reseller"]').select2("val","{{$rategenerators->Reseller}}");
                    $('.ResellerBox').show();
                } else {
                    $('.ResellerBox').hide();
                    $('#rategenerator-from select[name="Reseller"]').select2("val","0");
                }
            });
            $('#rategenerator-from input[name="AppliedTo"]').trigger('change');

        });


        function getNumber($item){
            var txt = $item;
            var numb = txt.match(/\d/g);
            numb = numb.join("");
            return numb;
        }


        function createCloneRow(tblID, idInp) {
            var lastrow = $('#' + tblID + ' tbody tr:last');
            var $item = lastrow.attr('id');
            var numb = lastrow.length > 0 ? getNumber($item) : 0;
            numb++;

            if(tblID == 'servicetableSubBox'){
                $("#table-1 tr").clone().appendTo('#' + tblID + ' tbody');
                $("#table-1 tr").attr('id', 'selectedRow-'+numb);
            }else{
                $("#table-2 tr").clone().appendTo('#' + tblID + ' tbody');
            }

            var row = tblID == "servicetableSubBox" ? "selectedRow" : "selectedRateRow";

            $('#' + tblID + ' tr:last').attr('id', row + '-' + numb);
            if (tblID == "servicetableSubBox") {
                $('#' + tblID + ' tr:last').children('td:eq(0)').children('select').attr('name', 'Package-' + numb).attr('id', 'Package-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(1)').children('select').attr('name', 'Component-' + numb + '[]').attr('id', 'Component-' + numb).select2().select2('val', '');
                $('#' + tblID + ' tr:last').children('td:eq(2)').children('select').attr('name', 'FCountry-' + numb).attr('id', 'FCountry-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(3)').children('select').attr('name', 'FAccessType-' + numb).attr('id', 'FAccessType-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(4)').children('select').attr('name', 'FPrefix-' + numb).attr('id', 'FPrefix-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(5)').children('select').attr('name', 'FCity-' + numb).attr('id', 'FCity-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(6)').children('select').attr('name', 'FTariff-' + numb).attr('id', 'FTariff-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(7)').children('input').attr('name', 'Origination-' + numb).attr('id', 'Origination-' + numb).val('');
                $('#' + tblID + ' tr:last').children('td:eq(8)').children('select').attr('name', 'TimeOfDay-' + numb).attr('id', 'TimeOfDay-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(9)').children('select').attr('name', 'Action-' + numb).attr('id', 'Action-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(10)').children('select').attr('name', 'MergeTo-' + numb).attr('id', 'MergeTo-' + numb).select2().select2('val', 'OneOffCost');
                $('#' + tblID + ' tr:last').children('td:eq(11)').children('select').attr('name', 'TCountry-' + numb).attr('id', 'TCountry-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(12)').children('select').attr('name', 'TAccessType-' + numb).attr('id', 'TccessType-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(13)').children('select').attr('name', 'TPrefix-' + numb).attr('id', 'TPrefix-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(14)').children('select').attr('name', 'TCity-' + numb).attr('id', 'TCity-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(15)').children('select').attr('name', 'TTariff-' + numb).attr('id', 'TTariff-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(16)').children('input').attr('name', 'ToOrigination-' + numb).attr('id', 'ToOrigination-' + numb).val('');
                $('#' + tblID + ' tr:last').children('td:eq(17)').children('select').attr('name', 'ToTimeOfDay-' + numb).attr('id', 'ToTimeOfDay-' + numb).select2();


            } else {
                $('#' + tblID + ' tr:last').children('td:eq(0)').children('select').attr('name', 'Package1-' + numb).attr('id', 'Package1-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(1)').children('select').attr('name', 'RateComponent-' + numb + '[]').attr('id', 'RateComponent-' + numb).select2().select2('val', '');
                $('#' + tblID + ' tr:last').children('td:eq(2)').children('select').attr('name', 'Country1-' + numb).attr('id', 'Country1-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(3)').children('select').attr('name', 'AccessType1-' + numb).attr('id', 'AccessType1-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(4)').children('select').attr('name', 'Prefix1-' + numb).attr('id', 'Prefix1-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(5)').children('select').attr('name', 'City1-' + numb).attr('id', 'City1-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(6)').children('select').attr('name', 'Tariff1-' + numb).attr('id', 'Tariff1-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(7)').children('input').attr('name', 'RateOrigination-' + numb).attr('id', 'RateOrigination-' + numb).val('');
                $('#' + tblID + ' tr:last').children('td:eq(8)').children('select').attr('name', 'RateTimeOfDay-' + numb).attr('id', 'RateTimeOfDay-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(9)').children('input').attr('name', 'RateLessThen-' + numb).attr('id', 'RateLessThen-' + numb).val('');
                $('#' + tblID + ' tr:last').children('td:eq(10)').children('input').attr('name', 'ChangeRateTo-' + numb).attr('id', 'ChangeRateTo-' + numb).val('');
            }
            if ($('#' + idInp).val() == '') {
                $('#' + idInp).val(numb + ',');
            } else {
                var getIDString = $('#' + idInp).val();
                getIDString = getIDString + numb + ',';
                $('#' + idInp).val(getIDString);
            }

            /* if(tblID == "servicetableSubBox") {
             $('#Component-' + numb + ' option').each(function () {
             $(this).remove();
             });
             } else {
             $('#RateComponent-' + numb + ' option').each(function () {
             $(this).remove();
             });
             }*/
            if (tblID == "servicetableSubBox") {
                $('#' + tblID + ' tr:last').closest('tr').children('td:eq(17)').children('a').attr('id', "merge-" + numb);
            } else {
                $('#' + tblID + ' tr:last').closest('tr').children('td:eq(10)').children('a').attr('id', "rateCal-" + numb);
            }


            $('#' + tblID + ' tr:last').children('td:eq(0)').find('div:first').remove();
            $('#' + tblID + ' tr:last').children('td:eq(1)').find('div:first').remove();
            $('#' + tblID + ' tr:last').children('td:eq(2)').find('div:first').remove();
            $('#' + tblID + ' tr:last').children('td:eq(3)').find('div:first').remove();
            $('#' + tblID + ' tr:last').children('td:eq(4)').find('div:first').remove();

            if (tblID == "servicetableSubBox") {
                $('#' + tblID + ' tr:last').children('td:eq(5)').find('div:first').remove();
                $('#' + tblID + ' tr:last').children('td:eq(6)').find('div:first').remove();
                $('#' + tblID + ' tr:last').children('td:eq(7)').find('div:first').remove();
                $('#' + tblID + ' tr:last').children('td:eq(8)').find('div:first').remove();
                $('#' + tblID + ' tr:last').children('td:eq(9)').find('div:first').remove();
                $('#' + tblID + ' tr:last').children('td:eq(10)').find('div:first').remove();
                $('#' + tblID + ' tr:last').children('td:eq(11)').find('div:first').remove();
                $('#' + tblID + ' tr:last').children('td:eq(12)').find('div:first').remove();
                $('#' + tblID + ' tr:last').children('td:eq(13)').find('div:first').remove();
                $('#' + tblID + ' tr:last').children('td:eq(14)').find('div:first').remove();
                $('#' + tblID + ' tr:last').children('td:eq(15)').find('div:first').remove();
                $('#' + tblID + ' tr:last').children('td:eq(16)').find('div:first').remove();
                $('#' + tblID + ' tr:last').children('td:eq(17)').find('div:first').remove();
                $('#' + tblID + ' tr:last').closest('tr').children('td:eq(18)').find('a').removeClass('hidden');
            } else {
                $('#' + tblID + ' tr:last').children('td:eq(5)').find('div:first').remove();
                $('#' + tblID + ' tr:last').children('td:eq(6)').find('div:first').remove();
                $('#' + tblID + ' tr:last').children('td:eq(7)').find('div:first').remove();
                $('#' + tblID + ' tr:last').children('td:eq(8)').find('div:first').remove();
                $('#' + tblID + ' tr:last').children('td:eq(9)').find('div:first').remove();
                $('#' + tblID + ' tr:last').children('td:eq(10)').find('div:first').remove();

                $('#' + tblID + ' tr:last').closest('tr').children('td:eq(11)').find('a').removeClass('hidden');
            }
        }

        function deleteRow(id, tblID, idInp)
        {
            if(confirm("Are You Sure?")) {
                var selectedSubscription = $('#'+idInp).val();
                var removeValue = id + ",";
                var removalueIndex = selectedSubscription.indexOf(removeValue);
                var firstValue = selectedSubscription.substr(0, removalueIndex);
                var lastValue = selectedSubscription.substr(removalueIndex + removeValue.length, selectedSubscription.length);
                var selectedSubscription = firstValue + lastValue;
                if (selectedSubscription.charAt(0) == ',') {
                    selectedSubscription = selectedSubscription.substr(1, selectedSubscription.length)
                }
                $('#'+idInp).val(selectedSubscription);


                //var rowCount = $("#" + tblID + " > tbody").children().length;

                    $("#" + id).closest("tr").remove();
                    getIds(tblID, idInp);


                return false;
            }
        }

    </script>
    @include('includes.ajax_submit_script', array('formID'=>'rategenerator-from' , 'url' => ('rategenerators/'.$rategenerators->RateGeneratorId.'/update')))

    @include('includes.errors')
    @include('includes.success')
    @include('currencies.currencymodal')
@stop
@section('footer_ext') @parent
@include('rategenerators.rategenerator_models')
@stop