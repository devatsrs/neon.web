@extends('layout.main')

@section('content')
    <style>
        #table-4 {
            max-width: 100% !important;
            width: 100%
        }

        @media screen and (max-width: 1600px) {

            #table-4 {
                max-width: 1600px !important;
                width: 1600px
            }
        }
    </style>
    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li>

            <a href="{{URL::to('dealmanagement')}}">Deal Management</a>
        </li>
        <li class="active">
            <strong>Edit Deal</strong>
        </li>
    </ol>
    <h3>Edit Deal</h3>
    @include('includes.errors')
    @include('includes.success')

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        Deal Summary
                    </div>
                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class=" ">
                        <div class="row">
                            <div class="col-md-6">
                                <h4><strong>Customer</strong></h4>
                                <table class="table table-bordered">
                                    <tbody>
                                    @foreach($deal_summary['customer_sum'] as $index => $data_total)
                                        <tr>
                                            <th> {{ucfirst(str_replace('_',' ',$index))  }}</th>
                                            <td>
                                                {{$data_total}}
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <th> %</th>
                                        <td>
                                            {{($deal_summary['customer_sum']['planned_cost'] ? $deal_summary['customer_sum']['actual_cost']/$deal_summary['customer_sum']['planned_cost'] : "0") *100}}%
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h4><strong>Vendor</strong></h4>
                                <table class="table table-bordered">
                                    <tbody>
                                    @foreach($deal_summary['vendor_sum'] as $index => $data_total)
                                        <tr>
                                            <th> {{ucfirst(str_replace('_',' ',$index))  }}</th>
                                            <td>
                                                {{$data_total}}
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <th> %</th>
                                        <td>
                                            {{($deal_summary['vendor_sum']['planned_cost'] ? $deal_summary['vendor_sum']['actual_cost']/$deal_summary['vendor_sum']['planned_cost'] : "0") *100}}%
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <h4><strong>Summary</strong></h4>
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th style="width: 9% !important">Type</th>
                                        <th style="width: 12% !important">Destination</th>
                                        <th style="width: 12% !important">Destination Break</th>
                                        <th style="width: 8% !important">Prefix</th>
                                        <th style="width: 9% !important">Trunk</th>
                                        <th style="width: 8%">Revenue/Cost</th>
                                        <th style="width: 7%">Deal Rate</th> <!-- Sale Price -->
                                        <th style="width: 7%">Actual Rate</th> <!-- Buy Price -->
                                        <th style="width: 7%">Actual Revenue/Cost</th> <!-- Buy Price -->
                                        <th style="width: 7%">Actual Minutes</th> <!-- Buy Price -->
                                        <th style="width: 7%">%</th> <!-- Buy Price -->
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($deal_summary as $deal_detail_id => $deal_detail)
                                        @if(isset($deal_detail['detail']))
                                        <tr>
                                            <?php

                                            $deal_detail_row = $deal_detail['detail'];
                                            $deal_detail_data = $deal_detail['data'];
                                            $deal_percentage = ($deal_detail_data['TotalCharges'] /$deal_detail_row->Revenue)*100;
                                            ?>


                                            <td> {{$deal_detail_row->Type}}</td>
                                            <td> {{$deal_detail_row->DestinationCountryID ? $Countries[$deal_detail_row->DestinationCountryID] : ''}} </td>
                                            <td> {{$deal_detail_row->DestinationBreak}} </td>
                                            <td> {{$deal_detail_row->Prefix}} </td>
                                            <td> {{$Trunks[$deal_detail_row->TrunkID]}} </td>
                                            <td> {{$deal_detail_row->Revenue}} </td>
                                            <td> {{$deal_detail_row->SalePrice}} </td>
                                            <td> {{$deal_detail_row->BuyPrice}} </td>
                                            <td> {{$deal_detail_data['TotalCharges']}} </td>
                                            <td> {{$deal_detail_data['TotalBilledDuration']}} </td>
                                            <td> {{$deal_percentage}}%<div class="progress"> <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="{{$deal_percentage}}" aria-valuemin="0" aria-valuemax="100" style="width: {{$deal_percentage}}%"> <span class="sr-only">{{$deal_percentage}}% Complete</span> </div> </div> </td>
                                        </tr>
                                        @endif
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <p style="text-align: right;">
        <button type="button"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..." id="save_deal">
            <i class="entypo-floppy"></i>
            Save
        </button>

        <a href="{{URL::to('/dealmanagement')}}" class="btn btn-danger btn-sm btn-icon icon-left">
            <i class="entypo-cancel"></i>
            Close
        </a>
    </p>
    <br>
    <div class="row">
        <div class="col-md-12">
            <form role="form" id="deal-from" method="post" action="{{URL::to('dealmanagement/update/'.$id)}}" class="form-horizontal form-groups-bordered">

                <div class="panel panel-primary" data-collapsed="0">
                    <div class="panel-body">
                        <div class="form-group">
                            <label class="col-md-2 control-label">Title*</label>
                            <div class="col-md-4">
                                <input type="text" name="Title" class="form-control" id="field-1" placeholder="" value="{{ $Deal->Title }}" />
                            </div>
                            <label class="col-md-2 control-label">Deal Type*</label>
                            <div class="col-md-4">
                                @if($DealDetails != false && count($DealDetails) > 0)
                                    {{Form::select('DealType',Deal::$TypeDropDown, $Deal->DealType,array("class"=>"select2", "disabled" => "disabled"))}}
                                    <input type='hidden' name='DealType' value='{{ $Deal->DealType }}'>
                                @else
                                    {{Form::select('DealType',Deal::$TypeDropDown, $Deal->DealType,array("class"=>"select2"))}}
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label">Account*</label>
                            <div class="col-md-4">
                                {{Form::select('AccountID',$Accounts, $Deal->AccountID,array("class"=>"select2"))}}
                            </div>
                            <label class="col-md-2 control-label">Codedeck*</label>
                            <div class="col-md-4">
                                @if($DealDetails != false && count($DealDetails) > 0)
                                    {{Form::select('CodedeckID',$codedecklist,$Deal->CodedeckID,array("class"=>"select2", "disabled" => "disabled"))}}
                                    <input type='hidden' name='CodedeckID' value='{{ $Deal->CodedeckID }}'>
                                @else
                                    {{Form::select('CodedeckID',$codedecklist,$Deal->CodedeckID,array("class"=>"select2"))}}
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label">Status*</label>
                            <div class="col-md-4">
                                {{Form::select('Status',Deal::$StatusDropDown, $Deal->Status,array("class"=>"select2"))}}
                            </div>
                            <label class="col-md-2 control-label">Alert Email</label>
                            <div class="col-md-4">
                                <input type="text" name="AlertEmail" class="form-control" id="field-1" placeholder="" value="{{ $Deal->AlertEmail }}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label">Start Date*</label>
                            <div class="col-md-4">
                                {{ Form::text('StartDate', date("Y-m-d", strtotime($Deal->StartDate)), array("class"=>"form-control small-date-input datepicker", 'id' => 'StartDate', "data-date-format"=>"yyyy-mm-dd" ,"data-enddate"=>date('Y-m-d'))) }}
                            </div>
                            <label class="col-md-2 control-label">End Date*</label>
                            <div class="col-md-4">
                                {{ Form::text('EndDate', date("Y-m-d", strtotime($Deal->EndDate)), array("class"=>"form-control small-date-input datepicker", 'id' => 'EndDate',"data-date-format"=>"yyyy-mm-dd" ,"data-enddate"=>date('Y-m-d'))) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel panel-primary" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title">
                            Deal Detail
                        </div>

                        <div class="panel-options">
                            <button type="button" onclick="addDeal()" class="btn btn-primary btn-xs add-deal" data-loading-text="Loading...">
                                <i></i>
                                +
                            </button>
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-bordered dealTable" id="table-4">
                                <thead>
                                <tr class="revenueRow">
                                    <th style="width: 9% !important">Type</th>
                                    <th style="width: 12% !important">Destination</th>
                                    <th style="width: 12% !important">Destination Break</th>
                                    <th style="width: 8% !important">Prefix</th>
                                    <th style="width: 9% !important">Trunk</th>
                                    <th style="width: 8%">Revenue/Cost</th>
                                    <th style="width: 7%">Deal Rate</th> <!-- Sale Price -->
                                    <th style="width: 7%">Actual Rate</th> <!-- Buy Price -->
                                    <th style="width: 8%">(Profit/Loss) per min</th>
                                    <th style="width: 7%">Minutes</th>
                                    <th style="width: 10%">Profit/Loss</th>
                                    <th style="width: 5%">Action</th>
                                </tr>
                                <tr class="paymentRow">
                                    <th style="width: 9% !important">Type</th>
                                    <th style="width: 12% !important">Destination</th>
                                    <th style="width: 12% !important">Destination Break</th>
                                    <th style="width: 8% !important">Prefix</th>
                                    <th style="width: 9% !important">Trunk</th>
                                    <th style="width: 7%">Minutes</th>
                                    <th style="width: 7%">Deal Rate</th> <!-- Sale Price -->
                                    <th style="width: 7%">Actual Rate</th> <!-- Buy Price -->
                                    <th style="width: 8%">(Profit/Loss) per min</th>
                                    <th style="width: 8%">Revenue/Cost</th>
                                    <th style="width: 10%">Profit/Loss</th>
                                    <th style="width: 5%">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <input type="hidden" name="TotalPL" value="{{ $Deal->TotalPL }}">
                                @foreach($DealDetails as $dealDetail)
                                    <tr data-pl="{{ $dealDetail->TotalPL }}" data-rev="{{ $dealDetail->Revenue }}" data-dealer="{{ $dealDetail->Type }}">
                                        <td>
                                            <select class="select2 dealer" name="Type[]" onchange="changePrice(this)">
                                                <option @if($dealDetail->Type == "Customer") selected @endif value="Customer">Customer</option>
                                                <option @if($dealDetail->Type == "Vendor") selected @endif value="Vendor">Vendor</option>
                                            </select>
                                        </td>
                                        <td>
                                            {{Form::select('Destination[]', $Countries, $dealDetail->DestinationCountryID, array("class"=>"select2 destination"))}}
                                        </td>
                                        <td>
                                            {{Form::select('DestinationBreak[]', $destinationBreaks, $dealDetail->DestinationBreak,array("class"=>"select2 destinationBreaks"))}}
                                        </td>
                                        <td>
                                            <input type="text" name="Prefix[]" class="form-control" value="{{ $dealDetail->Prefix }}">
                                        </td>
                                        <td>
                                            {{ Form::select('Trunk[]', $Trunks, $dealDetail->TrunkID, array("class"=>"select2")) }}
                                        </td>
                                        <td>
                                            @if($Deal->DealType == "Revenue")
                                                <input type="number" name="Revenue[]" value="{{ $dealDetail->Revenue }}" onkeyup="changePrice(this)" onchange="changePrice(this)" onblur="changePrice(this)" class="form-control revenue">
                                            @else
                                                <input type="number" name="Minutes[]" value="{{ $dealDetail->Minutes }}" class="form-control minutes" onkeyup="changePrice(this)" onchange="changePrice(this)" onblur="changePrice(this)">
                                            @endif
                                        </td>
                                        <td>
                                            <input type="number" name="SalePrice[]" value="{{ $dealDetail->SalePrice }}" onkeyup="changePrice(this)" onchange="changePrice(this)" onblur="changePrice(this)" class="form-control salePrice">
                                        </td>
                                        <td>
                                            <input type="number" name="BuyPrice[]" value="{{ $dealDetail->BuyPrice }}" onkeyup="changePrice(this)" onchange="changePrice(this)" onblur="changePrice(this)" class="form-control buyPrice">
                                        </td>
                                        <td>
                                            <input readonly type="number" name="PLPerMinute[]" value="{{ $dealDetail->PerMinutePL }}" class="form-control pl-minute">
                                        </td>
                                        <td>
                                            @if($Deal->DealType == "Revenue")
                                                <input readonly type="number" name="Minutes[]" value="{{ $dealDetail->Minutes }}" class="form-control minutes">
                                            @else
                                                <input type="number" name="Revenue[]" value="{{ $dealDetail->Revenue }}" readonly class="form-control revenue">
                                            @endif
                                        </td>
                                        <td>
                                            <input readonly type="number" name="PL[]" value="{{ $dealDetail->TotalPL }}" class="form-control pl-total">
                                        </td>
                                        <td>
                                            <button type="button" title="Delete" onclick="deleteDeal(this)" class="btn btn-danger btn-xs del-deal" data-loading-text="Loading...">
                                                <i></i>
                                                -
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr class="revenueRow">
                                    <th colspan="9"></th>
                                    <th>Total</th>
                                    <th class="pl-grand">0</th>
                                </tr>
                                <tr class="paymentRow">
                                    <th colspan="8"></th>
                                    <th>Total</th>
                                    <th class="rev-grand">0</th>
                                    <th class="pl-grand">0</th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="panel panel-primary" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title">
                            Notes
                        </div>

                        <div class="panel-options">
                            <button type="button" onclick="addNote()" class="btn btn-primary btn-xs add-note" data-loading-text="Loading...">
                                <i></i>
                                +
                            </button>
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <table class="table table-bordered noteTable" id="table-44">
                            <thead>
                            <tr>
                                <th style="width: 65%">Note</th>
                                <th style="width: 15%">Created By</th>
                                <th style="width: 15%">Created At</th>
                                <th style="width: 5%">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($DealNotes as $dealNote)
                                <tr>
                                    <td>
                                        <textarea name="Note[]" placeholder="Write note here..." class="form-control">{{ $dealNote->Note }}</textarea>
                                    </td>
                                    <td>
                                        {{ $dealNote->CreatedBy }}
                                    </td>
                                    <td class="dateTime">
                                        {{ date("Y-m-d", strtotime($dealNote->created_at)) }}
                                    </td>
                                    <td>
                                        <button type="button" title="Delete" onclick="deleteNote(this)" class="btn btn-danger btn-xs del-deal" data-loading-text="Loading...">
                                            <i></i>
                                            -
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('dealmanagement.add_edit_script')
    @include('includes.ajax_submit_script', array('formID'=>'deal-from' , 'url' => "dealmanagement/$id/update" ))
@stop
@section('footer_ext')
    @parent
@stop