@extends('layout.main')

@section('content')


    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{URL::to('/dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li>
            <a href="{{URL::to('/rategenerators')}}">Rate Generator</a>
        </li>

        <li class="active">
            <strong>Create Rate Generator</strong>
        </li>
    </ol>
    <h3>Create Rate Generator</h3>
    <form role="form" id="rategenerator-from" method="post" action="{{URL::to('/rategenerators/store')}}" class="form-horizontal form-groups-bordered">
        <div class="float-right">
            <button type="submit"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..." id="submitBtn">
                <i class="entypo-floppy"></i>
                Save
            </button>

            <a href="{{URL::to('/rategenerators')}}" class="btn btn-danger btn-sm btn-icon icon-left">
                <i class="entypo-cancel"></i>
                Close
            </a>
        </div>
        <br/>

        <div class="row">
            <div class="panel-body">
                <div class="panel panel-primary" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title">
                            Rate Generator Rule Information
                        </div>

                        <div class="panel-options">
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        </div>
                    </div>

                    <div class="panel-body">
                        <div class="form-group">
                            <label for="field-1" class="col-sm-2 control-label">Type*</label>
                            <div class="col-sm-4">
                                {{Form::select('SelectType',$AllTypes,'',array("class"=>"form-control select2 small"))}}
                            </div>
                            <label for="field-1" class="col-sm-2 control-label">Name*</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="RateGeneratorName" data-validate="required" data-message-required="." id="field-1" placeholder="" value="{{Input::old('RateGeneratorName')}}" />
                            </div>
                        </div>

                        <div class="form-group" id="rate-ostion-trunk-div">
                            <label for="field-1" class="col-sm-2 control-label">Policy*</label>
                            <div class="col-sm-4">
                                {{ Form::select('Policy', LCR::$policy, null , array("class"=>"select2")) }}
                            </div>

                            <label for="field-1" class="col-sm-2 control-label">Trunk*</label>
                            <div class="col-sm-4">
                                {{ Form::select('TrunkID', $trunks, null , array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="form-group" id="group-preference-div">


                            <label for="field-1" class="col-sm-2 control-label" id="group-by-lbl" style="display: none;">Group By</label>
                            <div class="col-sm-4" id="group-by-select-div" style="display: none;">
                                {{ Form::select('GroupBy', array('Code'=>'Code'), 'Code' , array("class"=>"select2")) }}
                                {{--{{ Form::select('GroupBy', array('Code'=>'Code','Desc'=>'Description'), 'Code' , array("class"=>"select2")) }}--}}
                            </div>
                            <div class="panel-options">
                                <div class="col-sm-6">
                                    <label for="field-1" class="col-sm-4 control-label">
                                        Use Preference
                                        <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="If ON then vendors will be ordered based on Preference instead of rate." data-original-title="Use Preference">?</span>
                                    </label>
                                    <div class="col-sm-8">
                                        <div class="make-switch switch-small">
                                            {{Form::checkbox('UsePreference', 1, '');}}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="rate-aveg-div">
                                <div class="col-sm-6">
                                    <div class="panel-options">
                                        <label for="field-1" class="col-sm-4 control-label">
                                            Use Average
                                            <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="IF ON system will take the average of all available vendor rates and use that." data-original-title="Use Average">?</span>
                                        </label>
                                        <div class="col-sm-8">
                                            <div class="make-switch switch-small">
                                                {{Form::checkbox('UseAverage', 1,  '' );}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="form-group NonDID-Div">
                            <label for="field-1" class="col-sm-2 control-label">If calculated rate is less then</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="LessThenRate" value="" />

                            </div>

                            <label for="field-1" class="col-sm-2 control-label">Change rate to</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="ChargeRate" value="" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="field-1" class="col-sm-2 control-label">Currency*</label>
                            <div class="col-sm-4">
                                {{Form::SelectControl('currency')}}
                            </div>

                            <label class="col-sm-2 control-label Position">Rate Position*</label>
                            <div class="col-sm-1">
                                <input type="text" class="form-control" name="RatePosition" data-validate="required" data-message-required="." id="field-1" placeholder="" value="" />
                            </div>
                            <div id="percentageRate">
                                <label class="col-sm-1 control-label Percentage">Percentage</label>
                                <div class="col-sm-2">
                                    <input type="text" class="form-control popover-primary" rows="1" id="percentageRate" name="percentageRate" value="" data-trigger="hover" data-toggle="popover" data-placement="top" data-content="Use vendor position mention in Rate Position unless vendor selected position is more then N% more costly than the previous vendor" data-original-title="Percentage" />
                                </div>
                            </div>
                        </div>
                        <div class="form-group NonDID-Div">
                            <label for="field-1" class="col-sm-2 control-label">CodeDeck*</label>
                            <div class="col-sm-4">
                                {{ Form::select('codedeckid', $codedecklist,  null , array("class"=>"select2")) }}

                            </div>

                            {{-- <label for="field-1" class="col-sm-2 control-label">Time Of Day*</label>
                            <div class="col-sm-4">
                                {{ Form::select('Timezones[]', $Timezones, array_keys($Timezones) , array("class"=>"select2 multiselect", "multiple"=>"multiple")) }}
                            </div> --}}
                        </div>
                        {{-- <div class="form-group NonDID-Div">
                            {{-- <label class="col-sm-3 control-label text-center">Merge Rate By Time Of Day</label>
                            <div class="col-sm-4">
                                <div class="make-switch switch-small">
                                    {{Form::checkbox('IsMerge', 1,  null, array('id' => 'IsMerge') );}}
                                </div>
                            </div> --}}
                            {{-- <label class="col-sm-2 control-label IsMerge">Take Price</label>
                            <div class="col-sm-4 IsMerge">
                                {{ Form::select('TakePrice', array(RateGenerator::HIGHEST_PRICE=>'Highest Price',RateGenerator::LOWEST_PRICE=>'Lowest Price'), null , array("class"=>"select2")) }}
                            </div> --}}
                        {{-- </div> --}}
                        <div class="form-group">
                            <label class="col-sm-2 control-label">
                                Applied To
                                <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="This option is for Rate Tables created from this Rate Generator." data-original-title="Applied To">?</span>
                            </label>
                            <div class="col-sm-4">
                                    @if(is_reseller())
                                        <label class="radio-inline">
                                                {{Form::radio('AppliedTo', RateTable::APPLIED_TO_CUSTOMER, true,array("class"=>""))}}
                                                Customer
                                        </label>
                                    @else
                                        <label class="radio-inline">
                                                {{Form::radio('AppliedTo', RateTable::APPLIED_TO_RESELLER, true,array("class"=>""))}}
                                                Partner
                                        </label>
                                    @endif 
                            </div>
                            <div class="ResellerBox" style="display: none;">
                                <label class="col-sm-2 control-label">Partner</label>
                                <div class="col-sm-4">
                                    {{Form::select('Reseller', $ResellerDD, '',array("class"=>"form-control select2"))}}
                                </div>
                            </div>
                        </div>
                        <div class="form-group NonDID-Div">
                            <label class="col-sm-2 control-label IsMerge">Merge Into</label>
                            <div class="col-sm-4 IsMerge">
                                {{ Form::select('MergeInto', $Timezones, null , array("class"=>"select2")) }}
                            </div>

                            <div id="hide-components">
                                <label for="field-1" class="col-sm-2 control-label">Components</label>
                                <div class="col-sm-4">
                                    {{ Form::select('AllComponent[]', RateGenerator::$Component, null, array("class"=>"select2 multiselect" , "multiple"=>"multiple", "id"=>"AllComponent" )) }}
                                </div>

                            </div>
                        </div>
                        <div class="form-group DID-Div">
                            <label for="field-1" class="col-sm-2 control-label">Country*</label>
                            <div class="col-sm-4">
                                {{ Form::select('CountryID', $country, '', array("class"=>"select2")) }}
                            </div>
                            <label for="field-1" class="col-sm-2 control-label">Access Type*</label>
                            <div class="col-sm-4">
                                {{ Form::select('AccessType', $AccessType, '', array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="form-group DID-Div">
                            <label for="field-1" class="col-sm-2 control-label">Prefix*</label>
                            <div class="col-sm-4">
                                {{ Form::select('Prefix', $Prefix, '', array("class"=>"select2")) }}
                            </div>
                            <label for="field-1" class="col-sm-2 control-label">City*</label>
                            <div class="col-sm-4">
                                {{ Form::select('City', $City, null, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="form-group" id="DIDCategoryDiv">
                            <label for="field-1" class="col-sm-2 control-label">Tariff*</label>
                            <div class="col-sm-4">
                                {{ Form::select('Tariff', $Tariff,null, array("class"=>"select2")) }}
                            </div>
                            <label for="field-1" class="col-sm-2 control-label">Category*</label>
                            <div class="col-sm-4">
                                {{ Form::select('Category', $Categories,null, array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="form-group DID-Div Package-Div1">
                            <label for="DateFrom" class="col-sm-2 control-label">Date From*</label>
                            <div class="col-sm-4">
                                <input id="DateFrom" type="text" name="DateFrom" class="form-control datepicker" data-date-format="yyyy-mm-dd" placeholder="YYYY-MM-DD"/>
                            </div>
                            <label for="DateTo" class="col-sm-2 control-label">Date To*</label>
                            <div class="col-sm-4">
                                <input id="DateTo" type="text" name="DateTo" class="form-control datepicker" data-date-format="yyyy-mm-dd" placeholder="YYYY-MM-DD"/>
                            </div>
                        </div>
                        <div class="form-group DID-Div Package-Div1">
                            <label for="Calls" class="col-sm-2 control-label">Calls</label>
                            <div class="col-sm-4">
                                <input type="number" min="0" class="form-control" id="Calls" name="Calls"/>
                            </div>
                            <label for="Minutes" class="col-sm-2 control-label">Minutes</label>
                            <div class="col-sm-4">
                                <input type="number" min="0" class="form-control" id="Minutes" name="Minutes"/>
                            </div>
                        </div>
                        <div class="form-group DID-Div">
                            <label for="field-1" class="control-label col-sm-2">No Of Services</label>
                            <div class="col-sm-4">
                                {{Form::number('NoOfServicesContracted','0' ,array("class"=>"form-control","min" => "0"))}}
                            </div>
                        </div>
                        <div class="form-group DID-Div Package-Div1">
                            <label for="TimeOfDay" class="col-sm-2 control-label">Time of Day</label>
                            <div class="col-sm-4">
                                {{ Form::select('TimezonesID', $Timezones, null, array("class"=>"select2")) }}
                            </div>
                            <label for="TimeOfDayPercentage" class="col-sm-2 control-label">Time of Day %</label>
                            <div class="col-sm-4">
                                <input type="number" min="0" class="form-control" id="TimeOfDayPercentage" name="TimezonesPercentage"/>
                            </div>
                        </div>
                        <div class="form-group DID-Div">
                            <label for="Origination" class="col-sm-2 control-label">Origination</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" id="Origination" name="Origination"/>
                            </div>
                            <label for="OriginationPercentage" class="col-sm-2 control-label">Origination %</label>
                            <div class="col-sm-4">
                                <input type="number" min="0" class="form-control" id="OriginationPercentage" name="OriginationPercentage"/>
                            </div>
                        </div>
                    </div>
                    <div class="form-group Package-Div" style="display:none;">
                        <label for="field-1" class="col-sm-2 control-label">Package*</label>
                        <div class="col-sm-4">
                            {{ Form::select('PackageID', $Package, null, array("class"=>"select2")) }}
                        </div>
                    </div>
                </div>
                
                <div class="panel panel-primary" data-collapsed="0" id="Merge-components">
                    <div class="panel-heading">
                        <div class="panel-title">
                            Merge Components
                        </div>

                        <div class="panel-options">
                            <button type="button" onclick="createCloneRow('servicetableSubBox','getIDs')" id="rate-update" class="btn btn-primary btn-xs add-clone-row-btn" data-loading-text="Loading...">
                                <i></i>
                                +
                            </button>
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
                                    <th style="width:250px;">Component</th>
                                    <th style="width:250px;" class="DID-Div">From Country</th>
                                    <th style="width:250px;" class="DID-Div">From Access Type</th>
                                    <th style="width:250px;" class="DID-Div">From Prefix</th>
                                    <th style="width:250px;" class="DID-Div">From City</th>
                                    <th style="width:250px;" class="DID-Div">From Tariff</th>
                                    <th style="width:200px;" class="DID-Div">Origination</th>
                                    <th style="width:200px;">Time of Day</th>
                                    <th style="width:200px;">Action</th>
                                    <th style="width:300px;">Merge To</th>
                                    <th style="width:250px;" class="DID-Div">To Country</th>
                                    <th style="width:250px;" class="DID-Div">To Access Type</th>
                                    <th style="width:250px;" class="DID-Div">To Prefix</th>
                                    <th style="width:250px;" class="DID-Div">To City</th>
                                    <th style="width:250px;" class="DID-Div">To Tariff</th>
                                    <th style="width:200px;" class="DID-Div">To Origination</th>
                                    <th style="width:200px;">To Time of Day</th>
                                </tr>
                                </thead>
                                <tbody id="tbody">
                                {{--<tr id="selectedRow-1">--}}
                                    {{--<td id="testValues">--}}
                                        {{--{{ Form::select('Component-1[]', RateGenerator::$Component, null, array("class"=>"select2 selected-Components" ,'multiple', "id"=>"Component-1")) }}--}}
                                    {{--</td>--}}
                                    {{--<td>--}}
                                        {{--{{ Form::select('FCountry-1', $country, '', array("class"=>"select2")) }}--}}
                                    {{--</td>--}}
                                    {{--<td>--}}
                                        {{--{{ Form::select('FAccessType-1', $AccessType, '', array("class"=>"select2")) }}--}}
                                    {{--</td>--}}
                                    {{--<td>--}}
                                        {{--{{ Form::select('FPrefix-1', $Prefix, '', array("class"=>"select2")) }}--}}
                                    {{--</td>--}}
                                    {{--<td>--}}
                                        {{--{{ Form::select('FCity_Tariff-1', $CityTariff, null, array("class"=>"select2")) }}--}}
                                    {{--</td>--}}
                                    {{--<td>--}}
                                        {{--<input type="text" class="form-control" name="Origination-1"/>--}}
                                    {{--</td>--}}
                                    {{--<td>--}}
                                        {{--{{ Form::select('TimeOfDay-1', $Timezones, '', array("class"=>"select2")) }}--}}
                                    {{--</td>--}}
                                    {{--<td>--}}
                                        {{--{{ Form::select('Action-1',  RateGenerator::$Action, RateGenerator::$Action, array("class"=>"select2")) }}--}}

                                    {{--</td>--}}
                                    {{--<td>--}}
                                        {{--{{ Form::select('MergeTo-1', RateGenerator::$Component,  null , array("class"=>"select2" , "id"=>"MergeTo-1")) }}--}}

                                    {{--</td>--}}
                                    {{--<td>--}}
                                        {{--<input type="text" class="form-control" name="ToOrigination-1"/>--}}
                                    {{--</td>--}}
                                    {{--<td>--}}
                                        {{--{{ Form::select('ToTimeOfDay-1', $Timezones, '', array("class"=>"select2")) }}--}}
                                    {{--</td>--}}
                                    {{--<td>--}}
                                        {{--{{ Form::select('TCountry-1', $country, '', array("class"=>"select2")) }}--}}
                                    {{--</td>--}}
                                    {{--<td>--}}
                                        {{--{{ Form::select('TAccessType-1', $AccessType, '', array("class"=>"select2")) }}--}}
                                    {{--</td>--}}
                                    {{--<td>--}}
                                        {{--{{ Form::select('TPrefix-1', $Prefix, '', array("class"=>"select2")) }}--}}
                                    {{--</td>--}}
                                    {{--<td>--}}
                                        {{--{{ Form::select('TCity_Tariff-1', $CityTariff, null, array("class"=>"select2")) }}--}}
                                    {{--</td>--}}
                                    {{--<td>--}}

                                        {{--<a onclick="deleteRow(this.id, 'servicetableSubBox','getIDs')" id="merge-0" class="btn btn-danger btn-sm" data-loading-text="Loading..." >--}}
                                            {{--<i></i>--}}
                                            {{-----}}
                                        {{--</a>--}}
                                    {{--</td>--}}
                                {{--</tr>--}}

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="panel panel-primary DID-Div" data-collapsed="0" id="Calculated-Rate">
                    <div class="panel-heading">
                        <div class="panel-title">
                            Calculated Rate
                        </div>
                        <div class="panel-options">
                            <button type="button" onclick="createCloneRow('ratetableSubBox','getRateIDs')" id="rate-update" class="btn btn-primary btn-xs add-clone-row-btn" data-loading-text="Loading...">
                                <i></i>
                                +
                            </button>
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="" style=" overflow: auto;">
                            <br/>
                            <input type="hidden" id="getRateIDs" name="getRateIDs" value=""/>
                            <table id="ratetableSubBox" class="table table-bordered datatable">
                                <thead>
                                <tr>
                                    <th style="width:250px;" class="Package-Div">Package</th>
                                    <th style="width:250px !important;">Component</th>
                                    <th style="width:250px !important;">Country</th>
                                    <th style="width:250px !important;">Access Type</th>
                                    <th style="width:250px !important;">Prefix</th>
                                    <th style="width:250px !important;">City</th>
                                    <th style="width:250px !important;">Tariff</th>
                                    <th style="width:200px !important;">Origination</th>
                                    <th style="width:200px !important;">Time of Day</th>
                                    <th style="width:200px !important;">Calculated Rate</th>
                                    <th style="width:200px !important;">Change Rate To</th>
                                   
                                </tr>
                                </thead>
                                <tbody id="ratetbody">
                                {{--<tr id="selectedRateRow-1">--}}
                                    {{--<td>--}}
                                        {{--{{ Form::select('RateComponent-1[]', RateGenerator::$Component, '', array("class"=>"select2 selected-Components" ,'multiple', "id"=>"RateComponent-1")) }}--}}
                                    {{--</td>--}}
                                    {{--<td>--}}
                                        {{--<input type="text" class="form-control" name="RateOrigination-1"/>--}}
                                    {{--</td>--}}
                                    {{--<td>--}}
                                        {{--{{ Form::select('RateTimeOfDay-1', $Timezones, '', array("class"=>"select2")) }}--}}
                                    {{--</td>--}}
                                    {{--<td>--}}
                                        {{--{{ Form::select('Country1-1', $country, '', array("class"=>"select2")) }}--}}
                                    {{--</td>--}}
                                    {{--<td>--}}
                                        {{--{{ Form::select('AccessType1-1', $AccessType, '', array("class"=>"select2")) }}--}}
                                    {{--</td>--}}
                                    {{--<td>--}}
                                        {{--{{ Form::select('Prefix1-1', $Prefix, '', array("class"=>"select2")) }}--}}
                                    {{--</td>--}}
                                    {{--<td>--}}
                                        {{--{{ Form::select('City_Tariff1-1', $CityTariff, null, array("class"=>"select2")) }}--}}
                                    {{--</td>--}}
                                    {{--<td>--}}
                                        {{--<input type="number" min="0" class="form-control" name="RateLessThen-1"/>--}}
                                    {{--</td>--}}
                                    {{--<td>--}}
                                        {{--<input type="number" min="0" class="form-control" name="ChangeRateTo-1"/>--}}
                                    {{--</td>--}}
                                    {{--<td>--}}
                                        {{--<a onclick="deleteRow(this.id,'ratetableSubBox','getRateIDs')" id="rateCal-0" class="btn btn-danger btn-sm" data-loading-text="Loading..." >--}}
                                            {{--<i></i>--}}
                                            {{-----}}
                                        {{--</a>--}}
                                    {{--</td>--}}
                                {{--</tr>--}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="panel panel-primary" data-collapsed="0" id="Vendors">
                    <div class="panel-heading">
                        <div class="panel-title">
                            Vendors &nbsp;<span class="label label-info popover-primary vendor-tooltip" data-toggle="popover" data-trigger="hover" data-placement="right" data-content="" data-original-title="">?</span>
                        </div>
                        <div class="panel-options">
                            <button type="button" onclick="createCloneRow('ratetableVendorBox','getRateVendorIDs')" id="rate-update" class="btn btn-primary btn-xs add-clone-row-btn" data-loading-text="Loading...">
                                <i></i>
                                +
                            </button>
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="" style=" overflow: auto;">
                            <br/>
                            <input type="hidden" id="getRateVendorIDs" name="getRateVendorIDs" value=""/>
                            <table id="ratetableVendorBox" class="table table-bordered datatable">
                                <thead>
                                <tr>
                                    <th style="width:250px;" class="Package-Div">Package</th>
                                    <th style="width:250px;">Vendors</th>
                                    <th style="width:250px !important;" class="DID-Div">Country</th>
                                    <th style="width:250px !important;" class="DID-Div">Access Type</th>
                                    <th style="width:250px !important;" class="DID-Div">Prefix</th>
                                    <th style="width:250px !important;" class="DID-Div">City</th>
                                    <th style="width:250px !important;" class="DID-Div">Tariff</th>
                                </tr>
                                </thead>
                                <tbody id="ratetbody">
                                {{--<tr id="selectedRateRow-1">--}}
                                    {{--<td>--}}
                                        {{--{{ Form::select('RateComponent-1[]', RateGenerator::$Component, '', array("class"=>"select2 selected-Components" ,'multiple', "id"=>"RateComponent-1")) }}--}}
                                    {{--</td>--}}
                                    {{--<td>--}}
                                        {{--<input type="text" class="form-control" name="RateOrigination-1"/>--}}
                                    {{--</td>--}}
                                    {{--<td>--}}
                                        {{--{{ Form::select('RateTimeOfDay-1', $Timezones, '', array("class"=>"select2")) }}--}}
                                    {{--</td>--}}
                                    {{--<td>--}}
                                        {{--{{ Form::select('Country1-1', $country, '', array("class"=>"select2")) }}--}}
                                    {{--</td>--}}
                                    {{--<td>--}}
                                        {{--{{ Form::select('AccessType1-1', $AccessType, '', array("class"=>"select2")) }}--}}
                                    {{--</td>--}}
                                    {{--<td>--}}
                                        {{--{{ Form::select('Prefix1-1', $Prefix, '', array("class"=>"select2")) }}--}}
                                    {{--</td>--}}
                                    {{--<td>--}}
                                        {{--{{ Form::select('City_Tariff1-1', $CityTariff, null, array("class"=>"select2")) }}--}}
                                    {{--</td>--}}
                                    {{--<td>--}}
                                        {{--<input type="number" min="0" class="form-control" name="RateLessThen-1"/>--}}
                                    {{--</td>--}}
                                    {{--<td>--}}
                                        {{--<input type="number" min="0" class="form-control" name="ChangeRateTo-1"/>--}}
                                    {{--</td>--}}
                                    {{--<td>--}}
                                        {{--<a onclick="deleteRow(this.id,'ratetableSubBox','getRateIDs')" id="rateCal-0" class="btn btn-danger btn-sm" data-loading-text="Loading..." >--}}
                                            {{--<i></i>--}}
                                            {{-----}}
                                        {{--</a>--}}
                                    {{--</td>--}}
                                {{--</tr>--}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="hidden">
        <table id="table-1">
            <tr id="selectedRow-">
                <td class="Package-Div">
                    {{ Form::select('Package-1', $Package, '', array("class"=>"select2")) }}
                </td>
                <td id="testValues" class="testValues">
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
                    {{ Form::select('TimeOfDay-1[]', $Timezones, '', array("class"=>"select2", "multiple")) }}
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
                    {{ Form::select('ToTimeOfDay-1[]', $Timezones, '', array("class"=>"select2" , "multiple")) }}
                </td>
                <td class="w-del">

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
                <td class="testRateValues">
                </td>
                <td>
                    {{ Form::select('Country1-1', $country, '', array("class"=>"select2")) }}
                </td>
                <td>
                    {{ Form::select('AccessType1-1', $AccessType, '', array("class"=>"select2")) }}
                </td>
                <td>
                    {{ Form::select('Prefix1-1', $Prefix, '', array("class"=>"select2")) }}
                </td>
                <td>
                    {{ Form::select('City1-1', $City, null, array("class"=>"select2")) }}
                </td>
                <td class="DID-Div">
                    {{ Form::select('Tariff1-1', $Tariff, null, array("class"=>"select2")) }}
                </td>
                <td >
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
                <td class="w-del">
                    <a onclick="deleteRow(this.id,'ratetableSubBox','getRateIDs')" id="rateCal-0" class="btn btn-danger btn-sm" data-loading-text="Loading..." >
                        <i></i>
                        -
                    </a>
                </td>
            </tr>
        </table>
        <table id="table-3">
            <tr id="selectedRateVendorRow-0">
                <td class="Package-Div">
                    {{ Form::select('Package2-1', $Package, '', array("class"=>"select2")) }}
                </td>
                <td>
                    {{ Form::select('Vendors2-1[]', $Vendors, '', array("class"=>"select2" , "multiple")) }}
                </td>
                
                <td class="DID-Div">
                    {{ Form::select('Country2-1', $country, '', array("class"=>"select2")) }}
                </td>
                <td class="DID-Div">
                    {{ Form::select('AccessType2-1', $AccessType, '', array("class"=>"select2")) }}
                </td>
                <td class="DID-Div">
                    {{ Form::select('Prefix2-1', $Prefix, '', array("class"=>"select2")) }}
                </td>
                <td class="DID-Div">
                    {{ Form::select('City2-1', $City, null, array("class"=>"select2")) }}
                </td>
                <td class="DID-Div">
                    {{ Form::select('Tariff2-1', $Tariff, null, array("class"=>"select2")) }}
                </td>
                <td class="w-del">
                    <a onclick="deleteRow(this.id,'ratetableVendorBox','getRateVendorIDs')" id="rateVendorCal-0" class="btn btn-danger btn-sm" data-loading-text="Loading..." >
                        <i></i>
                        -
                    </a>
                </td>
            </tr>
        </table>

    </div>
    <style>
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
        #ratetableVendorBox{
            width:1600px;
            overflow-x: auto;
        }
    </style>
    <script type="text/javascript">

        function ajax_form_success(response){
            if(typeof response.redirect != 'undefined' && response.redirect != ''){
                window.location = response.redirect;
            }
        }
        /*jQuery(document).ready(function($) {
         $(".save.btn").click(function(ev) {
         $("#rategenerator-from").submit();
         });
         });*/

        function getIds(tblID, idInp){
            $('#'+idInp).val("");
            $('#' + tblID + ' tbody tr').each(function() {

                var row = "";
                if(tblID == "servicetableSubBox"){
                    row = "selectedRow";
                }else if(tblID == "ratetableVendorBox"){
                    row = "selectedRateVendorRow";
                }else{
                    row = "selectedRateRow";
                }
                var id = 0;
                if(this.id != row)
                    id = getNumber(this.id);

                var getIDString =  $('#'+idInp).val();
                getIDString = getIDString + id + ',';
                $('#'+idInp).val(getIDString);

            });
        }


        $(document).ready(function() {

            if(performance.navigation.type == 2)
            {
                $('#getIDs').val('');
                $('#getRateIDs').val('');
                $("#hide-components").hide();
            }

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


            $(window).load(function() {
                getIds("servicetableSubBox", "getIDs");
                getIds("ratetableSubBox", "getRateIDs");
                getIds("ratetableVendorBox", "getRateVendorIDs");
            });

            $('#servicetableSubBox tbody tr').each(function() {

                var id = getNumber(this.id);
                var getIDString =  $('#getIDs').val();
                getIDString = getIDString + id + ',';
                $('#getIDs').val(getIDString);

                selectAllComponents = $("#AllComponent").val();
                selectAllComponents = String(selectAllComponents);
                var ComponentsArray = selectAllComponents.split(',');

                var component = $('#Component-' + id).val();
                component = String(component);
                var getCompArray = component.split(',');

                for( var i = 0; i < ComponentsArray.length; i++){
                    if ( ComponentsArray[i] == getCompArray[i]) {
                        ComponentsArray.splice(i, 1);
                    }
                }

                var i;
                for (i = 0; i < ComponentsArray.length; ++i) {
                    var data = {
                        id: ComponentsArray[i],
                        text: ComponentsArray[i]
                    };

                    if (typeof data.id != 'undefined' && data.id != 'null') {

                        var newOption = new Option(data.text, data.id, false, false);
                        var newOption2 = new Option(data.text, data.id, false, false);
                        $('#Component-' + id).append(newOption).trigger('change');
                        $('#MergeTo-' + id).append(newOption2).trigger('change');
                    }
                }

            });

            $('#rategenerator-from input[name="AppliedTo"]').change(function() {
                var AppliedTo = $('#rategenerator-from input[name="AppliedTo"]:checked').val();

                if(AppliedTo == {{ RateTable::APPLIED_TO_RESELLER }}) {
                    $('.ResellerBox').show();
                } else {
                    $('.ResellerBox').hide();
                    $('#rategenerator-from select[name="Reseller"]').select2("val","0");
                }
            });
            $('#rategenerator-from input[name="AppliedTo"]').trigger('change');

        });

        var TypeValue = $("#rategenerator-from [name='SelectType']").val();
        if(TypeValue == 2){
            $("#rate-ostion-trunk-div").hide();
            $("#rate-aveg-div").hide();
            $("#group-preference-div").hide();
            $("#DIDCategoryDiv").show();
            $("#Merge-components").show();
            $(".DID-Div").show();
            $(".NonDID-Div").hide();$(".Package-Div").hide();

        }else if(TypeValue == 1){
            $("#rate-ostion-trunk-div").show();
            $("#rate-aveg-div").show();
            $("#group-preference-div").show();
            $("#Merge-components").hide();
            $("#DIDCategoryDiv").hide();
            $("#hide-components").hide();
            $("#Vendors").hide();
            $(".DID-Div").hide();
            $(".NonDID-Div").show();$(".Package-Div").hide();
        }else if(TypeValue == 3){

            $(".DID-Div").hide();
            $(".NonDID-Div").hide();
            $(".Package-Div").show();
            $("#rate-ostion-trunk-div").hide();
            $("#rate-aveg-div").hide();
            $("#group-preference-div").hide();
            $("#DIDCategoryDiv").hide();
            $("#Merge-components").show();
            $("#Vendors").show();

        } else {
            $(".DID-Div").hide();
            $(".NonDID-Div").show();$(".Package-Div").hide();
        }

        $("#rategenerator-from [name='SelectType']").on('change', function() {

            var TypeValue = $(this).val();
            var lastrow = $('#servicetableSubBox tbody tr').length;

            if(TypeValue == 2){
                $("#rate-ostion-trunk-div").hide();
                $("#rate-aveg-div").hide();
                $("#group-preference-div").hide();
                $("#Merge-components").show();
                $("#Vendors").show();
                $("#DIDCategoryDiv").show();
                $("#hide-components").show();
                $(".DID-Div").show();
                $(".NonDID-Div").hide();$(".Package-Div").hide();
                $('#servicetableSubBox').css('width','3200px');
                $('#ratetableVendorBox').css('width','1600px');
                $('.w-del').css('width','1%');
                $('#getRateVendorIDs').val('');
                $('#getIDs').val('');
                $('#getRateIDs').val('');
                $("#Vendors tbody").empty();
                $('.vendor-tooltip').attr('data-content','Keep ALL Product option at the bottom. If any condition added for any specific Product then also add ALL option for remaining Product.');
                $('.vendor-tooltip').attr('data-original-title','Product');
                $('#servicetableSubBox tbody').empty();
                $('#ratetableSubBox tbody').empty();
                $('.testValues').html('{{ Form::select("Component-1[]",DiscountPlan::$RateTableDIDRate_Components , null, array("class"=>"DID Components1" ,"multiple", "id"=>"Component-1")) }}');
                $('.testRateValues').html('{{ Form::select("RateComponent-1[]",DiscountPlan::$RateTableDIDRate_Components , null, array("class"=>"DID Components2" ,"multiple", "id"=>"RateComponent-1")) }}');
                $('.mergetestvalues').html('{{ Form::select("MergeTo-1[]",DiscountPlan::$RateTableDIDRate_Components , null, array("class"=>"DID Components3","multiple" , "id"=>"MergeTo-1")) }}');
                $('.DID').select2();
            }else if(TypeValue == 1){
                $("#rate-ostion-trunk-div").show();
                $("#rate-aveg-div").show();
                $("#group-preference-div").show();
                $("#Merge-components").hide();
                $("#Vendors").hide();
                $("#hide-components").hide();
                $("#DIDCategoryDiv").hide();
                $(".DID-Div").hide();
                $(".NonDID-Div").show();$(".Package-Div").hide();
                $('#getRateVendorIDs').val('');
                $('#getIDs').val('');
                $('#getRateIDs').val('');

            }else if(TypeValue == 3){

                $(".DID-Div").hide();
                $(".Package-Div1").show();
                $(".NonDID-Div").hide();
                $(".Package-Div").show();
                $("#rate-ostion-trunk-div").hide();
                $("#rate-aveg-div").hide();
                $("#group-preference-div").hide();
                $("#DIDCategoryDiv").hide();
                $("#Merge-components").show();
                $("#Vendors").show();
//              $('#servicetableSubBox thead th').css('width','50px');
                $('#servicetableSubBox').css('width','1225px');
                $('#ratetableVendorBox').css('width','1030px');
                $('.w-del').css('width','3%');
                $("#Vendors tbody").empty();
                $('#servicetableSubBox tbody').empty();
                $('#ratetableSubBox tbody').empty();
                $('#getRateVendorIDs').val('');
                $('#getIDs').val('');
                $('#getRateIDs').val('');
                $('.vendor-tooltip').attr('data-content','Keep ALL package option at the bottom. If any condition added for any specific Package then also add ALL option for remaining packages. ')
                $('.vendor-tooltip').attr('data-original-title','Package');
                $('.testValues').html('{{ Form::select("Component-[]", DiscountPlan::$RateTablePKGRate_Components , null, array("class"=>"PKG" ,"multiple", "id"=>"Component-1")) }}');
                $('.testRateValues').html('{{ Form::select("RateComponent-1[]", DiscountPlan::$RateTablePKGRate_Components , null, array("class"=>"PKG" ,"multiple", "id"=>"RateComponent-1")) }}');
                $('.mergetestvalues').html('{{ Form::select("MergeTo-1", DiscountPlan::$RateTablePKGRate_Components , null, array("class"=>"PKG" , "id"=>"MergeTo-1")) }}');
                $('.PKG').select2();

            } else {
                $(".DID-Div").hide();$(".Package-Div").hide();
                $(".NonDID-Div").show();
            }

        });
        $('#IsMerge').on('change', function(e, s) {
            if($('#IsMerge').is(':checked')) {
                $('.IsMerge').show();
            } else {
                $('.IsMerge').hide();
            }
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


        $("#rategenerator-from [name='SelectType']").on('change', function() {

            var TypeValue = $(this).val();
            if(TypeValue == 2){
                $("#rate-ostion-trunk-div").hide();
                $("#rate-aveg-div").hide();
                $("#group-preference-div").hide();

            }else if(TypeValue == 1) {
                $("#rate-ostion-trunk-div").show();
                $("#rate-aveg-div").show();
                $("#group-preference-div").show();

            }

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
            }else if(tblID == 'ratetableVendorBox'){
                $("#table-3 tr").clone().appendTo('#' + tblID + ' tbody');
                $("#table-3 tr").attr('id', 'selectedRateVendorRow-'+numb);
            }else{
                $("#table-2 tr").clone().appendTo('#' + tblID + ' tbody');
            }
           

            var row = "";

            if(tblID == "servicetableSubBox"){
                 row = "selectedRow";
            }else if(tblID == "ratetableVendorBox"){
                 row = "selectedRateVendorRow";
            }else{
                row = "selectedRateRow";
            }

            $('#' + tblID + ' tr:last').attr('id', row + '-' + numb);
            if (tblID == "servicetableSubBox") {
                $('#' + tblID + ' tr:last').children('td:eq(0)').children('select').attr('name', 'Package-' + numb).attr('id', 'Package-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(1)').removeAttr('class').children('select').attr('name', 'Component-' + numb + '[]').attr('id', 'Component-' + numb).select2().select2('val', '');
                $('#' + tblID + ' tr:last').children('td:eq(2)').children('select').attr('name', 'FCountry-' + numb).attr('id', 'FCountry-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(3)').children('select').attr('name', 'FAccessType-' + numb).attr('id', 'FAccessType-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(4)').children('select').attr('name', 'FPrefix-' + numb).attr('id', 'FPrefix-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(5)').children('select').attr('name', 'FCity-' + numb).attr('id', 'FCity-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(6)').children('select').attr('name', 'FTariff-' + numb).attr('id', 'FTariff-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(7)').children('input').attr('name', 'Origination-' + numb).attr('id', 'Origination-' + numb).val('');
                $('#' + tblID + ' tr:last').children('td:eq(8)').children('select').attr('name', 'TimeOfDay-' + numb + '[]').attr('id', 'TimeOfDay-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(9)').children('select').attr('name', 'Action-' + numb).attr('id', 'Action-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(10)').removeAttr('class').children('select').attr('name', 'MergeTo-' + numb + '[]').attr('id', 'MergeTo-' + numb).select2().select2('val', 'OneOffCost');
                $('#' + tblID + ' tr:last').children('td:eq(11)').children('select').attr('name', 'TCountry-' + numb).attr('id', 'TCountry-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(12)').children('select').attr('name', 'TAccessType-' + numb).attr('id', 'TccessType-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(13)').children('select').attr('name', 'TPrefix-' + numb).attr('id', 'TPrefix-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(14)').children('select').attr('name', 'TCity-' + numb).attr('id', 'TCity_Tariff-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(15)').children('select').attr('name', 'TTariff-' + numb).attr('id', 'TTariff-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(16)').children('input').attr('name', 'ToOrigination-' + numb).attr('id', 'ToOrigination-' + numb).val('');
                $('#' + tblID + ' tr:last').children('td:eq(17)').children('select').attr('name', 'ToTimeOfDay-' + numb + '[]').attr('id', 'ToTimeOfDay-' + numb).select2();

            }else if (tblID == "ratetableVendorBox") { 
                $('#' + tblID + ' tr:last').children('td:eq(0)').children('select').attr('name', 'Package2-' + numb).attr('id', 'Package2-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(1)').children('select').attr('name', 'Vendor2-' + numb + '[]').attr('id', 'Vendor2-' + numb ).select2();
                $('#' + tblID + ' tr:last').children('td:eq(2)').children('select').attr('name', 'Country2-' + numb).attr('id', 'Country2-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(3)').children('select').attr('name', 'AccessType2-' + numb).attr('id', 'AccessType2-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(4)').children('select').attr('name', 'Prefix2-' + numb).attr('id', 'Prefix2-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(5)').children('select').attr('name', 'City2-' + numb).attr('id', 'City_Tariff2-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(6)').children('select').attr('name', 'Tariff2-' + numb).attr('id', 'Tariff2-' + numb).select2();
            } else {
                $('#' + tblID + ' tr:last').children('td:eq(0)').children('select').attr('name', 'Package1-' + numb).attr('id', 'Package1-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(1)').removeAttr('class').children('select').attr('name', 'RateComponent-' + numb + '[]').attr('id', 'RateComponent-' + numb).select2().select2('val', '');
                $('#' + tblID + ' tr:last').children('td:eq(2)').children('select').attr('name', 'Country1-' + numb).attr('id', 'Country1-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(3)').children('select').attr('name', 'AccessType1-' + numb).attr('id', 'AccessType1-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(4)').children('select').attr('name', 'Prefix1-' + numb).attr('id', 'Prefix1-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(5)').children('select').attr('name', 'City1-' + numb).attr('id', 'City_Tariff1-' + numb).select2();
                $('#' + tblID + ' tr:last').children('td:eq(6)').children('select').attr('name', 'Tariff1-' + numb).attr('id', 'Tariff-' + numb).select2();
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
                $('#' + tblID + ' tr:last').closest('tr').children('td:eq(18)').children('a').attr('id', "merge-" + numb);
            }else if (tblID == "ratetableVendorBox") {
                $('#' + tblID + ' tr:last').closest('tr').children('td:eq(7)').children('a').attr('id', "rateVendorCal-" + numb);
            } else {
                $('#' + tblID + ' tr:last').closest('tr').children('td:eq(11)').children('a').attr('id', "rateCal-" + numb);
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
            }else if(tblID == "ratetableVendorBox") {
                $('#' + tblID + ' tr:last').children('td:eq(5)').find('div:first').remove();
                $('#' + tblID + ' tr:last').children('td:eq(6)').find('div:first').remove();
                $('#' + tblID + ' tr:last').closest('tr').children('td:eq(7)').find('a').removeClass('hidden');
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


//                var rowCount = $("#" + tblID + " > tbody").children().length;
//                if (rowCount > 1) {
//                    $("#" + id).closest("tr").remove();
//                    getIds(tblID, idInp);
//
//                } else {
//
//                    toastr.error("You cannot delete. At least one component is required.", "Error", toastr_opts);
//                }
                $("#" + id).closest("tr").remove();
                getIds(tblID, idInp);
                return false;
            }
        }


    </script>
    @include('currencies.currencymodal')
    @include('trunk.trunkmodal')
    @include('includes.ajax_submit_script', array('formID'=>'rategenerator-from' , 'url' => ('/rategenerators/store'),'update_url'=>'rategenerators/{id}/update'))
@stop         
