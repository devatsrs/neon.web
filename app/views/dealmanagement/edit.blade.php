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
                                {{Form::select('DealType',Deal::$TypeDropDown, $Deal->DealType,array("class"=>"select2","disabled"=>"disabled"))}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label">Account*</label>
                            <div class="col-md-4">
                                {{Form::select('AccountID',$Accounts, $Deal->AccountID,array("class"=>"select2"))}}
                            </div>
                            <label class="col-md-2 control-label">Codedeck*</label>
                            <div class="col-md-4">
                                {{Form::select('CodedeckID',$codedecklist,$Deal->CodedeckID,array("class"=>"select2","disabled"=>"disabled"))}}
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
                            <tr>
                                <th style="width: 13%">Type</th>
                                <th style="width: 12%">Destination</th>
                                <th style="width: 12%">Trunk</th>
                                <th style="width: 10%">Revenue</th>
                                <th style="width: 9%">Sale Price</th>
                                <th style="width: 9%">Buy Price</th>
                                <th style="width: 9%">(Profit/Loss) per min</th>
                                <th style="width: 10%">Minutes</th>
                                <th style="width: 10%">Profit/Loss</th>
                                <th style="width: 5%">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                            <tfoot>
                            <tr>
                                <th colspan="7"></th>
                                <th>Total</th>
                                <th class="pl-grand">0</th>
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
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <table id="addRow" class="hide hidden">
        <tr>
            <td>
                <select class="selectOpt dealer" onchange="changePrice(this)">
                    <option value="customer">Customer</option>
                    <option value="vendor">Vendor</option>
                </select>
            </td>
            <td>
                <select class="selectOpt">
                    <option value="">Select</option>
                </select>
            </td>
            <td>
                <select class="selectOpt">
                    <option value="">Select</option>
                </select>
            </td>
            <td>
                <input type="number" value="0.00" onkeyup="changePrice(this)" onchange="changePrice(this)" onblur="changePrice(this)" class="form-control revenue">
            </td>
            <td>
                <input type="number" value="0.00" onkeyup="changePrice(this)" onchange="changePrice(this)" onblur="changePrice(this)" class="form-control salePrice">
            </td>
            <td>
                <input type="number" value="0.00" onkeyup="changePrice(this)" onchange="changePrice(this)" onblur="changePrice(this)" class="form-control buyPrice">
            </td>
            <td>
                <input readonly type="number" value="0.0000" class="form-control pl-minute">
            </td>
            <td>
                <input readonly type="number" value="0" class="form-control minutes">
            </td>
            <td>
                <input readonly type="number" value="0.0000" class="form-control pl-total">
            </td>
            <td>
                <button type="button" title="Delete" onclick="deleteDeal(this)" class="btn btn-danger btn-xs del-deal" data-loading-text="Loading...">
                    <i></i>
                    -
                </button>
            </td>
        </tr>
    </table>
    <table id="addNote" class="hide hidden">
        <tr>
            <td>
                <textarea placeholder="Write note here..." class="form-control"></textarea>
            </td>
            <td>
                {{ User::get_user_full_name() }}
            </td>
            <td class="dateTime">
                {{ date("d-m-Y") }}
            </td>
            <td>
                <button type="button" title="Delete" onclick="deleteNote(this)" class="btn btn-danger btn-xs del-deal" data-loading-text="Loading...">
                    <i></i>
                    -
                </button>
            </td>
        </tr>
    </table>

    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $("#save_deal").click(function (ev) {
                $('#save_deal').button('loading');
                $("#deal-from").submit();
            });

            $("#StartDate").datepicker({
                todayBtn:  1,
                autoclose: true
            }).on('changeDate', function (selected) {
                var minDate = new Date(selected.date.valueOf());
                var endDate = $('#EndDate');
                endDate.datepicker('setStartDate', minDate);
                if(endDate.val() && new Date(endDate.val()) != undefined) {
                    if(minDate > new Date(endDate.val()))
                        endDate.datepicker("setDate", minDate)
                }
            });

            $("#EndDate").datepicker({autoclose: true})
                    .on('changeDate', function (selected) {
                        var maxDate = new Date(selected.date.valueOf());
                        //$('#StartDate').datepicker('setEndDate', maxDate);
                    });

            if(new Date($('#StartDate').val()) != undefined){
                $("#EndDate").datepicker('setStartDate', new Date($('#StartDate').val()))
            }

            disableFieldsOnAddDetails();

        });

        function ajax_form_success(response){
            if(typeof response.redirect != 'undefined' && response.redirect != ''){
                window.location = response.redirect;
            }
        }

        function disableFieldsOnAddDetails(){
            var rowLength = $(".dealTable tbody tr");
            var fields = $("select[name='DealType'], select[name='CodedeckID']");
            if(rowLength.length > 0){
                fields.attr("disabled","disabled").trigger("change")
            } else {
                fields.removeAttr("disabled").trigger("change")
            }
        }

        function addDeal(){
            var row = $("#addRow tr:first").parent().html();
            var tbody = $(".dealTable tbody");
            tbody.append(row);
            var lastRow = $(".dealTable tbody tr:last");
            lastRow.find(".selectOpt").select2();
            disableFieldsOnAddDetails();
        }

        function countTotalPL(){

            var totalPL = 0;
            $(".dealTable tbody tr").each(function () {
                var plVal = $(this).attr('data-pl');
                totalPL +=  (plVal != "" && plVal != undefined && plVal != "NaN") ? parseFloat(plVal) : 0;
            });
            $(".pl-grand").text(totalPL.toFixed(2));

        }

        function changePrice(ele){
            var that = $(ele);
            var row = that.parent().parent();
            var dealer = row.find("select.dealer").val();
            //console.log(dealer)

            //Getting Values
            var revenue = row.find(".revenue").val() == "" ? 0 : row.find(".revenue").val();
            revenue = (revenue != undefined && revenue != "NaN") ? parseFloat(revenue) : 0;
            var salePrice = row.find(".salePrice").val() == "" ? 0 : row.find(".salePrice").val();
            salePrice = (salePrice != undefined && salePrice != "NaN") ? parseFloat(salePrice) : 0;
            var buyPrice = row.find(".buyPrice").val() == "" ? 0 : row.find(".buyPrice").val();
            buyPrice = (buyPrice != undefined && buyPrice != "NaN") ? parseFloat(buyPrice) : 0;


            //Calculating values
            var plminute = dealer == "customer" ? salePrice - buyPrice : buyPrice - salePrice;
            var minutes = salePrice != 0 ? revenue / salePrice : 0;
            minutes = (minutes != undefined && minutes != "NaN") ? minutes : 0;
            var profileLoss = plminute * minutes;

            row.find(".pl-minute").val(plminute);
            row.find(".minutes").val(minutes);
            row.find(".pl-total").val(profileLoss);
            row.attr("data-pl", profileLoss);
            countTotalPL();
        }


        function deleteDeal(ele){
            var that = $(ele);
            var row = that.parent().parent();
            row.remove();
            countTotalPL();
            disableFieldsOnAddDetails();
        }

        function addNote(){
            var row = $("#addNote tr:first").parent().html();
            var tbody = $(".noteTable tbody");
            tbody.append(row);
            var time = new Date();
            $(".noteTable tbody tr:last td.dateTime").append(" " + time.toLocaleTimeString().toLowerCase());
        }


        function deleteNote(ele){
            var that = $(ele);
            var row = that.parent().parent();
            row.remove();
            countTotalPL();
        }

    </script>
    @include('includes.ajax_submit_script', array('formID'=>'deal-from' , 'url' => 'dealmanagement/store','update_url'=>'dealmanagement/update/{id}' ))
@stop
@section('footer_ext')
    @parent
@stop