@extends('layout.main')

@section('content')

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
                        <table class="table table-bordered dealTable" id="table-4">
                            <thead>
                            <tr class="revenueRow">
                                <th style="width: 10%">Type</th>
                                <th style="width: 10%">Destination</th>
                                <th style="width: 10%">Destination Break</th>
                                <th style="width: 10%">Prefix</th>
                                <th style="width: 10%">Trunk</th>
                                <th style="width: 10%">Revenue</th>
                                <th style="width: 9%">Sale Price</th>
                                <th style="width: 9%">Buy Price</th>
                                <th style="width: 9%">(Profit/Loss) per min</th>
                                <th style="width: 9%">Minutes</th>
                                <th style="width: 9%">Profit/Loss</th>
                                <th style="width: 5%">Action</th>
                            </tr>
                            <tr class="paymentRow">
                                <th style="width: 10%">Type</th>
                                <th style="width: 10%">Destination</th>
                                <th style="width: 10%">Destination Break</th>
                                <th style="width: 10%">Prefix</th>
                                <th style="width: 10%">Trunk</th>
                                <th style="width: 10%">Minutes</th>
                                <th style="width: 9%">Sale Price</th>
                                <th style="width: 9%">Buy Price</th>
                                <th style="width: 9%">(Profit/Loss) per min</th>
                                <th style="width: 9%">Revenue</th>
                                <th style="width: 9%">Profit/Loss</th>
                                <th style="width: 5%">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($DealDetails as $dealDetail)
                                <tr data-pl="{{ $dealDetail->TotalPL }}">
                                    <td>
                                        <select class="select2 dealer" name="Type[]" onchange="changePrice(this)">
                                            <option @if($dealDetail->Type == "Customer") selected @endif value="Customer">Customer</option>
                                            <option @if($dealDetail->Type == "Vendor") selected @endif value="Vendor">Vendor</option>
                                        </select>
                                    </td>
                                    <td>
                                        {{Form::select('Destination[]', $Countries, $dealDetail->DestinationCountryID, array("class"=>"select2"))}}
                                    </td>
                                    <td>
                                        {{Form::select('DestinationBreak[]', $destinationBreaks, $dealDetail->DestinationBreak,array("class"=>"select2"))}}
                                    </td>
                                    <td>
                                        <input type="text" name="Prefix[]" class="form-control" value="{{ $dealDetail->Prefix }}">
                                    </td>
                                    <td>
                                        {{ Form::select('Trunk[]', $Trunks, $dealDetail->TrunkID, array("class"=>"select2")) }}
                                    </td>
                                    <td>
                                        @if($dealDetail->Type == "Revenue")
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
                                        @if($dealDetail->Type == "Revenue")
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
                            <tr>
                                <th colspan="7"></th>
                                <th>Total</th>
                                <th class="pl-grand">0</th>
                                <input type="hidden" name="TotalPL" value="{{ $Deal->TotalPL }}">
                            </tr>
                            </tfoot>
                        </table>
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
                        <table class="table table-bordered noteTable" id="table-4">
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