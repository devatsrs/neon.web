@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>
        <a href="{{URL::to('accounts')}}">Accounts</a>
    </li>
    <li>
        {{customer_dropbox($id,["IsVendor"=>1])}}
    </li>
    <li>
        <a href="{{URL::to('accounts/'.$Account->AccountID.'/edit')}}"></i>Edit Account({{$Account->AccountName}})</a>
    </li>
    <li class="active">
        <strong>Vendor Connection</strong>
    </li>
</ol>
<h3>Vendor Connection</h3>

@include('includes.errors')
@include('includes.success')

<ul class="nav nav-tabs bordered"><!-- available classes "bordered", "right-aligned" -->
    @if(User::checkCategoryPermission('VendorRates','Connection'))
        <li class="active">
            <a href="{{ URL::to('/vendor_rates/connection/'.$id) }}" >
                <span class="hidden-xs">Connection</span>
            </a>
        </li>
    @endif

    @if(User::checkCategoryPermission('VendorRates','TrunkCost'))
        <li>
            <a href="{{ URL::to('vendor_rates/'.$id.'/trunk_cost') }}" >
                <span class="hidden-xs">Trunk Cost</span>
            </a>
        </li>
    @endif
    {{--<li>
        <a href="{{ URL::to('vendor_rates/'.$id) }}" >
            <span class="hidden-xs">Vendor Rate</span>
        </a>
    </li>--}}
    {{--@if(User::checkCategoryPermission('VendorRates','Upload'))
    <li>
        <a href="{{ URL::to('/vendor_rates/'.$id.'/upload') }}" >
            <span class="hidden-xs">Vendor Rate Upload</span>
        </a>
    </li>
    @endif--}}
   {{-- @if(User::checkCategoryPermission('VendorRates','Download'))
        <li>
            <a href="{{ URL::to('/vendor_rates/'.$id.'/download') }}" >
                <span class="hidden-xs">Vendor Rate Download</span>
            </a>
        </li>
    @endif--}}
   {{-- @if(User::checkCategoryPermission('VendorRates','Settings'))
        <li>
            <a href="{{ URL::to('/vendor_rates/'.$id.'/settings') }}" >
                <span class="hidden-xs">Settings</span>
            </a>
        </li>
    @endif--}}
    {{--@if(User::checkCategoryPermission('VendorRates','Blocking'))
        <li >
            <a href="{{ URL::to('vendor_blocking/'.$id) }}" >
                <span class="hidden-xs">Blocking</span>
            </a>
        </li>
    @endif
    @if(User::checkCategoryPermission('VendorRates','Preference'))
        <li >
            <a href="{{ URL::to('/vendor_rates/vendor_preference/'.$id) }}" >
                <span class="hidden-xs">Preference</span>
            </a>
        </li>
    @endif--}}
    {{--@if(User::checkCategoryPermission('VendorRates','History'))
        <li>
            <a href="{{ URL::to('/vendor_rates/'.$id.'/history') }}" >
                <span class="hidden-xs">Vendor Rate History</span>
            </a>
        </li>
    @endif--}}
    @if(User::checkCategoryPermission('Timezones','Add'))
        <li>
            <a href="{{ URL::to('/timezones_vendor/vendor_rates/'.$id) }}" >
                <span class="hidden-xs">Time Of Day</span>
            </a>
        </li>
    @endif
    {{--@include('vendorrates.upload_rates_button')--}}
</ul>


<div class="row">
    <div class="col-md-12">
        <form role="form" id="vendor-rate-search" method="get"  action="{{URL::to('vendor_rates/'.$id.'/search')}}" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        Search
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="form-group">
                        <label for="field-1" class="col-sm-1 control-label">Type</label>
                        <div class="col-sm-3">
                            {{ Form::select('RateTypeID',[''=>'All']+RateType::getRateTypeDropDownList(), '', array("class"=>"select2 FilterConnectionType")) }}
                        </div>

                        <label for="field-1" class="col-sm-1 control-label">Name</label>
                        <div class="col-sm-3">
                            <input type="text" name="Name" class="form-control" id="filter-0" placeholder="" value="{{Input::get('IP')}}" />
                        </div>

                        <div class="FilterCategory">
                            <label for="field-1" class="col-sm-1 control-label">Category</label>
                            <div class="col-sm-3">
                                {{ Form::select('DIDCategoryID', $DIDCategories, '', array("class"=>"select2")) }}
                            </div>
                        </div>

                    </div>

                    <div class="form-group">
                        <div class="FilterVoiceCallDiv">
                            <div class="FilterIP">
                                <label for="field-1" class="col-sm-1 control-label">IP</label>
                                <div class="col-sm-3">
                                    <input type="text" name="IP" class="form-control" id="filter-1" placeholder="" value="{{Input::get('IP')}}" />
                                </div>
                            </div>
                            <div class="FilterTrunk">
                                <label for="field-1" class="col-sm-1 control-label">Trunk</label>
                                <div class="col-sm-3">
                                    {{ Form::select('TrunkID', $trunks, '', array("class"=>"select2")) }}
                                </div>
                            </div>
                        </div>

                        <label for="field-5" class="col-sm-1 control-label">Status</label>

                        <div class="col-sm-3">
                            {{ Form::select('FilterActive', [''=>'Both','1'=>'Active','0'=>'Deactive'], 1, array("class"=>"select2")) }}
                        </div>
                    </div>



                    <p style="text-align: right;">
                        <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left">
                            <i class="entypo-search"></i>
                            Search
                        </button>
                    </p>
                </div>
            </div>
        </form>
    </div>


    <div class="input-group-btn pull-right  dropdown" style="width:70px;margin-right: 20px;">
        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="caret"></span></button>
        <ul class="dropdown-menu dropdown-menu-left" role="menu" style="background-color: #000; border-color: #000; margin-top:0px;">
            @if(User::checkCategoryPermission('Products','Edit'))
                <li class="">
                    <a class="" id="changeStatus" href="javascript:;">
                        <span>Change Status</span>
                    </a>
                </li>
                <li class="">
                    <a class="" id="delete_multiconnection" href="javascript:;">
                        <i class=""></i>
                        <span>Delete</span>
                    </a>
                </li>

            @endif
        </ul>
    </div>

    @if( User::checkCategoryPermission('TaxRates','Add') )
        <a href="#" id="add-new-connection" class="btn btn-primary pull-right" style="margin-right:10px;margin-bottom: 10px;">
            <i class="entypo-plus"></i>
            Add New
        </a>
    @endif
</div>



<table class="table table-bordered datatable" id="table-4">
    <thead>
    <tr>
        <th width="5%"><input type="checkbox" id="selectall" name="checkbox[]" class="" /></th>
        <th width="12%">Name</th>
        <th width="10%">Type</th>
        <th width="10%">IP</th>
        <th width="10%">Status</th>
        <th width="10%">Trunk</th>
        <th width="12%">Category</th>
        <th width="10%">Location</th>
        <th width="10%">RateTable</th>
        <th width="10%">Created At</th>
        <th width="20%">Action</th>
    </tr>
    </thead>
    <tbody>


    </tbody>
</table>

<script type="text/javascript">
    var $searchFilter = {};
    var checked='';
    var list_fields  = ['VendorConnectionID','Name','RateTypeTitle','IP','Active','TrunkName','CategoryName','Location','created_at','DIDCategoryID','RateTableID','TrunkID','CLIRule','CLDRule','CallPrefix','Port','Username','PrefixCDR','SipHeader','AuthenticationMode','RateTypeID'];
    var TrunkID, IP, RateTypeID,Name,DIDCategoryID,Active,update_new_url;

    jQuery(document).ready(function($) {
        var ArchiveRates;
        //var data_table;
        connectionDataTable();
        $("#vendor-rate-search").submit(function(e) {
            return connectionDataTable();
        });

        $('#table-4 tbody').on('click', 'tr', function() {
            if($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                if (checked == '') {
                    $(this).toggleClass('selected');
                    if ($(this).hasClass('selected')) {
                        $(this).find('.rowcheckbox').prop("checked", true);
                    } else {
                        $(this).find('.rowcheckbox').prop("checked", false);
                    }
                }
            }
        });

        // Replace Checboxes
        $(".pagination a").click(function(ev) {
            replaceCheckboxes();
        });

        //Bulk Form and Edit Single Form Submit
        $("#edit-vendor-rate-form").submit(function() {
            var formData = new FormData($(this)[0]);
            var VendorConnectionID = $("#edit-vendor-rate-form [name='VendorConnectionID']").val();

            if( typeof VendorConnectionID != 'undefined' && VendorConnectionID != ''){
                update_new_url = baseurl + '/vendor_rates/connection/{{$id}}/update/'+VendorConnectionID;
            }else{
                update_new_url = baseurl + '/vendor_rates/connection/{{$id}}/create';
            }

            $.ajax({
                url:update_new_url, //Server script to process data
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    $(".save.btn").button('reset');

                    if (response.status == 'success') {
                        $('#modal-VendorRate').modal('hide');
                        toastr.success(response.message, "Success", toastr_opts);
                        $("#edit-vendor-rate-form .select2").select2("val", "");
                        //data_table.fnFilter('', 0);
                        connectionDataTable('addedit');
                    } else {
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                },
                error: function(error) {
                   // $("#modal-BulkConnection").modal("hide");
                    //$("#modal-VendorRate").modal("hide");
                    toastr.error(error, "Error", toastr_opts);
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

        $("#DiscontinuedRates").on('change', function (event, state) {
            if($("#DiscontinuedRates").is(':checked')) {
                $(".EffectiveBox").hide();
            } else {
                $(".EffectiveBox").show();
            }
        });

        $(document).on('click', '.btn-history', function() {
            var $this   = $(this);
            var Codes   = $this.prevAll("div.hiddenRowData").find("input[name='Code']").val();
            getArchiveVendorRates($this,Codes);
        });

        //set RateN value = Rate1 value if RateN value is blank
        $(document).on('focusout','.Rate1', function() {
            var formid = $(this).closest("form").attr('id');
            var val = $(this).val();

            if($('#'+formid+' .RateN').val() == '') {
                $('#'+formid+' .RateN').val(val);
            }
        });

        $("#add-new-connection").click(function(){
            $('#edit-vendor-rate-form').trigger("reset");
            $("#edit-vendor-rate-form [name='RateTypeID']").removeAttr("disabled");
            $("#edit-vendor-rate-form [name='did[DIDCategoryID]']").removeAttr("disabled");
            $("#edit-vendor-rate-form [name='voice[TrunkID]']").removeAttr("disabled");
            $('#edit-vendor-rate-form').find(".select2").select2("val", "");
            $("#edit-vendor-rate-form [name='VendorConnectionID']").val('');

            $('.vendor-connection-modal-title').html('Add New  Vendor Connection');
            $('#did_Div,#voice_Div,#package_Div').addClass('hidden');
            var RateTypeID = $("#vendor-rate-search select[name='RateTypeID']").val();
            if(typeof(RateTypeID)!='undefined' && $.trim(RateTypeID)!=''){
                $("#edit-vendor-rate-form [name='RateTypeID']").val(RateTypeID).trigger("change");
            }

            jQuery('#modal-VendorRate').modal('show', {backdrop: 'static'});
        });

        $("select[name='RateTypeID']").change(function(){
           var  RateTypeID=$(this).val();
            $('#did_Div,#voice_Div,#package_Div').find('input:text').val('');
            $('#voice_Div,#did_Div,#package_Div').find(".select2").select2("val", "");

            if(typeof(RateTypeID)!='undefined' && RateTypeID=='{{$DIDType}}'){
                $("#did_Div").removeClass('hidden');
                $("#package_Div").addClass('hidden');
                $("#voice_Div").addClass('hidden');
            }else if(typeof(RateTypeID)!='undefined' && RateTypeID=='{{$VoiceCallType}}'){
                $("#did_Div").addClass('hidden');
                $("#package_Div").addClass('hidden');
                $("#voice_Div").removeClass('hidden');
            }else if(typeof(RateTypeID)!='undefined' && RateTypeID=='{{$PackageCallType}}'){
                $("#did_Div").addClass('hidden');
                $("#voice_Div").addClass('hidden');
                $("#package_Div").removeClass('hidden');
            }else{
                $("#did_Div").addClass('hidden');
                $("#voice_Div").addClass('hidden');
                $("#package_Div").addClass('hidden');
            }

        });

        //DID Change Category - Load Tariff
        $("select[name='did[DIDCategoryID]']").change(function(){
            var categoryID=$(this).val();
            loadTariffByCategory(categoryID);
        });


        //VoiceCall Change Trunk - Load Tariff
        $("select[name='voice[TrunkID]']").change(function(){
            var TrunkID=$(this).val();
            loadTariffByTrunk(TrunkID);

        });


    });

    function loadTariffByCategory(categoryID,arg1){
        //var categoryID=$(this).val();
        $("#DIDTariffLoading").removeClass("hidden");
        $.ajax({
            url: baseurl + "/vendor_rates/connection/{{$id}}/get_tariff_by_category_trunk",
            data: 'categoryID='+categoryID,
            type: 'POST',
            success: function (response) {
                //console.log("5555");

                $("#DIDTariffLoading").addClass("hidden");
                var VendorConnectionID = $("#edit-vendor-rate-form [name='VendorConnectionID']").val();
                if(typeof VendorConnectionID == 'undefined' || VendorConnectionID == ''){
                    $("select[name='did[RateTableID]']").select2("val", "");
                }

                if($.trim(response)){

                    $("select[name='did[RateTableID]']").html(response);

                    if(typeof VendorConnectionID != 'undefined' && VendorConnectionID != ''){
                        console.log("func "+arg1);
                        $("select[name='did[RateTableID]']").select2("val",arg1);
                    }

                }
            },
            cache: false

        });
    }

    function loadTariffByTrunk(TrunkID,arg1){
        //var categoryID=$(this).val();
        $("#VoiceTariffLoading").removeClass("hidden");
        $.ajax({
            url: baseurl + "/vendor_rates/connection/{{$id}}/get_tariff_by_category_trunk",
            data: 'TrunkID='+TrunkID,
            type: 'POST',
            success: function (response) {
                console.log("5555");

                $("#VoiceTariffLoading").addClass("hidden");
                var VendorConnectionID = $("#edit-vendor-rate-form [name='VendorConnectionID']").val();
                if(typeof VendorConnectionID == 'undefined' || VendorConnectionID == ''){
                    $("select[name='voice[RateTableID]']").select2("val", "");
                }

                if($.trim(response)){

                    $("select[name='voice[RateTableID]']").html(response);

                    if(typeof VendorConnectionID != 'undefined' && VendorConnectionID != ''){
                        console.log("func "+arg1);
                        $("select[name='voice[RateTableID]']").select2("val",arg1);
                    }

                }
            },
            cache: false

        });
    }

    function getArchiveVendorRates($clickedButton,Codes) {
        //var Codes = new Array();
        var ArchiveRates;
        /*$("#table-4 tr td:nth-child(2)").each(function(){
         Codes.push($(this).html());
         });*/

        var tr = $clickedButton.closest('tr');
        var row = data_table.row(tr);

        if (row.child.isShown()) {
            tr.find('.details-control i').toggleClass('entypo-plus-squared entypo-minus-squared');
            row.child.hide();
            tr.removeClass('shown');
        } else {
            tr.find('.details-control i').toggleClass('entypo-plus-squared entypo-minus-squared');
            $clickedButton.attr('disabled','disabled');

            $.ajax({
                url : baseurl + "/vendor_rates/{{$id}}/search_ajax_datagrid_archive_rates",
                type : 'POST',
                data : "Codes="+Codes+"&TimezonesID="+$searchFilter.Timezones+"&TrunkID="+$searchFilter.Trunk,
                dataType : 'json',
                cache: false,
                success : function(response){
                    $clickedButton.removeAttr('disabled');

                    if (response.status == 'success') {
                        ArchiveRates = response.data;
                        //$('.details-control').show();
                    } else {
                        ArchiveRates = {};
                        toastr.error(response.message, "Error", toastr_opts);
                    }

                    $clickedButton.find('i').toggleClass('entypo-plus-squared entypo-minus-squared');
                    var hiddenRowData = tr.find('.hiddenRowData');
                    var Code = hiddenRowData.find('input[name="Code"]').val();
                    var table = $('<table class="table table-bordered datatable dataTable no-footer" style="margin-left: 4%;width: 92% !important;"></table>');
                    table.append("<thead><tr><th>Code</th><th>Description</th><th>Connection Fee</th><th>Interval 1</th><th>Interval N</th><th>Rate1</th><th>RateN</th><th class='sorting_desc'>Effective Date</th><th>End Date</th><th>Modified Date</th><th>Modified By</th></tr></thead>");
                    var tbody = $("<tbody></tbody>");

                    /*ArchiveRates.sort(function(obj1, obj2) {
                     // Ascending: first age less than the previous
                     return new Date(obj2.EffectiveDate).getTime() - new Date(obj1.EffectiveDate).getTime();
                     });*/
                    ArchiveRates.forEach(function(data){
                        if(data['Code'] == Code) {
                            var html = "";
                            html += "<tr class='no-selection'>";
                            html += "<td>" + data['Code'] + "</td>";
                            html += "<td>" + data['Description'] + "</td>";
                            html += "<td>" + data['ConnectionFee'] + "</td>";
                            html += "<td>" + data['Interval1'] + "</td>";
                            html += "<td>" + data['IntervalN'] + "</td>";
                            html += "<td>" + data['Rate'] + "</td>";
                            html += "<td>" + data['RateN'] + "</td>";
                            html += "<td>" + data['EffectiveDate'] + "</td>";
                            html += "<td>" + data['EndDate'] + "</td>";
                            html += "<td>" + data['ModifiedDate'] + "</td>";
                            html += "<td>" + data['ModifiedBy'] + "</td>";
                            html += "</tr>";
                            table.append(html);
                        }
                    });
                    table.append(tbody);
                    row.child(table).show();
                    row.child().addClass('no-selection child-row');
                    tr.addClass('shown');
                }
            });
        }
    }

    function connectionDataTable() {
        $searchFilter.TrunkID = TrunkID = $("#vendor-rate-search select[name='TrunkID']").val();
        $searchFilter.IP = IP = $("#vendor-rate-search input[name='IP']").val();
        $searchFilter.RateTypeID = RateTypeID = $("#vendor-rate-search select[name='RateTypeID']").val();
        $searchFilter.Name = Name = $("#vendor-rate-search input[name='Name']").val();
        $searchFilter.DIDCategoryID = DIDCategoryID = $("#vendor-rate-search select[name='DIDCategoryID']").val();
        $searchFilter.FilterActive = FilterActive = $("#vendor-rate-search select[name='FilterActive']").val();

        /* if(RateTypeID == '' || typeof RateTypeID  == 'undefined'){
             toastr.error("Please Select Type", "Error", toastr_opts);
             return false;
         }*/

        data_table = $("#table-4").DataTable({
            "bDestroy": true, // Destroy when resubmit form
            "bAutoWidth": false,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": baseurl + "/vendor_rates/connection/{{$id}}/search_ajax_datagrid/type",
            "fnServerParams": function(aoData) {
                aoData.push({"name": "TrunkID", "value": TrunkID}, {"name": "IP", "value": IP}, {"name": "RateTypeID", "value": RateTypeID},{"name": "Name", "value": Name},{"name": "DIDCategoryID", "value": DIDCategoryID},{"name": "FilterActive", "value": FilterActive});
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name": "TrunkID", "value": TrunkID}, {"name": "IP", "value": IP}, {"name": "RateTypeID", "value": RateTypeID},{"name": "Name", "value": Name},{"name": "DIDCategoryID", "value": DIDCategoryID},{"name": "FilterActive", "value": FilterActive},{ "name": "Export", "value": 1});
            },
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [9, "desc"],
            "aoColumns":
                    [
                        {"bSortable": false, //RateID
                            mRender: function(id, type, full) {
                                return '<div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + id + '" class="rowcheckbox" ></div>';
                            }
                        },
                        {"bSortable": true}, //1 Connection Name
                        {"bSortable": true}, //2 IP
                        {"bSortable": true}, //3 Type
                        {
                            "bSortable": true, //4 Active
                            mRender: function ( id, type, full ) {
                                var action='';
                                var checked="";
                                if(full[4] == '1')
                                {
                                    checked="checked";

                                }

                                var Url = "{{ URL::to('/vendor_rates/connection/{id}/statusupdate')}}";
                                Url  = Url .replace( '{id}', full[0] );

                                action +='<div class="make-switch switch-small"> <input type="checkbox" id="EnableDisable" name="EnableDisable" '+checked+'  value="1" class="EnableDisable" data-id="'+ full[0] +'" data-href="'+Url+'"> </div>';

                                return action;
                            }

                        },
                        {"bSortable": false}, //5 TrunkName
                        {"bSortable": false}, //6 CategoryName
                        {"bSortable": false}, //7 Location
                        {// 9 Action
                            "bSortable": false,
                            mRender: function(id, type, full) {
                                var RateTable = full[21];
                                return RateTable;
                            }
                        }, //8 RateTable
                        {"bSortable": true,
                            mRender: function(id, type, full) {
                                var created_at = full[8];
                                return created_at;
                            }

                        }, //9 created at
                        {// 10 Action
                            "bSortable": false,
                            mRender: function(id, type, full) {

                                var action, edit_, delete_,VendorConnectionID;
                                edit_ = "{{ URL::to('/vendor_rates/connection/update/{id}')}}";
                                VendorConnectionID = full[0];
                                var deleteUrl = "{{ URL::to('/vendor_rates/connection/{id}/delete')}}";
                                deleteUrl  = deleteUrl .replace( '{id}', VendorConnectionID );

                                action = '<div class = "hiddenRowData" >';
                                for(var i = 0 ; i< list_fields.length; i++){
                                    action += '<input type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null?full[i]:'')+ '" / >';
                                }
                                action += '</div>';

                                if (VendorConnectionID > 0) {
                                    <?php if(User::checkCategoryPermission('VendorRates','Edit')) { ?>
                                            action += ' <a href="Javascript:;" title="Edit" class="edit-vendor-rate btn btn-default btn-xs"><i class="entypo-pencil"></i>&nbsp;</a>';
                                    <?php } ?>

                                    <?php if(User::checkCategoryPermission('VendorRates','Delete')) { ?>

                                        action += ' <button href="' + deleteUrl + '" title="Delete"  class="btn delete_connection btn-danger btn-xs" data-loading-text="Loading..."><i class="entypo-trash"></i></button>';

                                    <?php } ?>
                                }
                                return action;
                            }
                        }, // 10 Action
                    ],
            "oTableTools":
            {
                "aButtons": [
                    {
                        "sExtends": "download",
                        "sButtonText": "EXCEL",
                        "sUrl": baseurl + "/vendor_rates/connection/{{$id}}/search_ajax_datagrid/xlsx",
                        sButtonClass: "save-collection btn-sm"
                    },
                    {
                        "sExtends": "download",
                        "sButtonText": "CSV",
                        "sUrl": baseurl + "/vendor_rates/connection/{{$id}}/search_ajax_datagrid/csv",
                        sButtonClass: "save-collection btn-sm"
                    }
                ]
            },
            "fnDrawCallback": function() {
                //getArchiveVendorRates(); //rate history for plus button
                $("#clear-bulk-rate-form").find("input[name='TrunkID']").val($searchFilter.TrunkID);

                $("#selectall").click(function(ev) {
                    var is_checked = $(this).is(':checked');
                    $('#table-4 tbody tr').each(function(i, el) {
                        if (is_checked) {
                            $(this).find('.rowcheckbox').prop("checked", true);
                            $(this).addClass('selected');
                        } else {
                            $(this).find('.rowcheckbox').prop("checked", false);
                            $(this).removeClass('selected');
                        }
                    });
                });


                $(".FilterConnectionType").change(function() {
                   var Type=$(this).val();
                   if(Type== '{{$DIDType}}'){
                       $(".FilterCategory").css('display','block');
                       $(".FilterVoiceCallDiv").css('display','none');
                       $("select[name='TrunkID']").select2('val','');
                       $("input[name='IP']").val('');
                   }else if(Type=='{{$VoiceCallType}}'){
                       $(".FilterVoiceCallDiv").css('display','block');
                       $(".FilterCategory").css('display','none');
                       $("select[name='DIDCategoryID']").select2('val','');
                   }else{
                       $(".FilterVoiceCallDiv").css('display','block');
                       $(".FilterCategory").css('display','block');
                    }

                });
                //Edit Button
                $(".edit-vendor-rate.btn").off('click');

                $(".edit-vendor-rate.btn").click(function(ev) {
                    ev.stopPropagation();
                    $('.vendor-connection-modal-title').html("Edit Vendor Connection");
                    $('#edit-vendor-rate-form').trigger("reset");
                    var cur_obj = $(this).prev("div.hiddenRowData");
                    var RateTypeID=cur_obj.find("input[name='RateTypeID']").val();

                    $("#edit-vendor-rate-form [name='RateTypeID']").val(RateTypeID).trigger("change");
                    $("#edit-vendor-rate-form [name='RateTypeID']").attr("disabled",true);

                    for(var i = 0 ; i< list_fields.length; i++){
                        if(RateTypeID=='{{$DIDType}}'){

                            if(list_fields[i] == 'Active'){
                                if(cur_obj.find("input[name='"+list_fields[i]+"']").val() == 1){
                                    console.log('true');
                                    $('#edit-vendor-rate-form [name="did[Active]"]').prop('checked',true);
                                }else{
                                    console.log('false');
                                    $('#edit-vendor-rate-form [name="did[Active]"]').prop('checked',false);
                                }
                            }else if(list_fields[i] == 'DIDCategoryID'){
                                $("#edit-vendor-rate-form [name='did[" + list_fields[i] + "]']").removeAttr('disabled');
                                var DIDCategoryID_ = cur_obj.find("input[name='"+list_fields[i]+"']").val();
                                $("#edit-vendor-rate-form [name='did["+list_fields[i]+"]']").select2("val",DIDCategoryID_);
                                var DIDCategoryID = $("#edit-vendor-rate-form [name='did[DIDCategoryID]']").val();
                                var TarrifID = cur_obj.find("input[name='RateTableID']").val();

                                loadTariffByCategory(DIDCategoryID,TarrifID);
                                if(typeof(DIDCategoryID_)!='undefined' && DIDCategoryID_!=0) {
                                    $("#edit-vendor-rate-form [name='did[" + list_fields[i] + "]']").attr("disabled", true);
                                }

                            }else if(list_fields[i] == 'RateTableID'){
                                $("#edit-vendor-rate-form [name='did[" + list_fields[i] + "]']").val(cur_obj.find("input[name='" + list_fields[i] + "']").val()).trigger("change");

                            }

                        }else if(RateTypeID=='{{$PackageCallType}}'){

                            if(list_fields[i] == 'Active'){
                                if(cur_obj.find("input[name='"+list_fields[i]+"']").val() == 1){
                                    console.log('true');
                                    $('#edit-vendor-rate-form [name="package[Active]"]').prop('checked',true);
                                }else{
                                    console.log('false');
                                    $('#edit-vendor-rate-form [name="package[Active]"]').prop('checked',false);
                                }
                            }else if(list_fields[i] == 'RateTableID'){
                                $("#edit-vendor-rate-form [name='package[" + list_fields[i] + "]']").val(cur_obj.find("input[name='" + list_fields[i] + "']").val()).trigger("change");

                            }

                        }else if(RateTypeID=='{{$VoiceCallType}}'){

                            if(list_fields[i] == 'Active' || list_fields[i] == 'PrefixCDR'){
                                if(cur_obj.find("input[name='"+list_fields[i]+"']").val() == 1){
                                    console.log('true');
                                    $('#edit-vendor-rate-form [name="voice['+list_fields[i]+']"]').prop('checked',true);
                                }else{
                                    console.log('false');
                                    $('#edit-vendor-rate-form [name="voice['+list_fields[i]+']"]').prop('checked',false);
                                }
                            }else if(list_fields[i] == 'TrunkID'){
                                $("#edit-vendor-rate-form [name='voice["+list_fields[i]+"]']").select2("val",cur_obj.find("input[name='"+list_fields[i]+"']").val());
                                var TrunkID = $("#edit-vendor-rate-form [name='voice[TrunkID]']").val();
                                var TarrifID = cur_obj.find("input[name='RateTableID']").val();

                                loadTariffByTrunk(TrunkID,TarrifID);

                                $("#edit-vendor-rate-form [name='voice["+list_fields[i]+"]']").attr("disabled",true);
                            }else if(list_fields[i] == 'RateTableID'){
                                $("#edit-vendor-rate-form [name='voice["+list_fields[i]+"]']").select2("val",cur_obj.find("input[name='"+list_fields[i]+"']").val());
                            }else if(list_fields[i] == 'Password'){
                                //remain blank
                                $("#edit-vendor-rate-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                            }else{
                                $("#edit-vendor-rate-form [name='voice["+list_fields[i]+"]']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                            }

                        }
                        //Common Fields
                        if(list_fields[i] != 'RateTypeID'){
                            $("#edit-vendor-rate-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                        }

                    }

                    jQuery('#modal-VendorRate').modal('show', {backdrop: 'static'});
                });

                $(".delete_connection").click(function(e){
                    e.preventDefault();

                    response = confirm('Are you sure?');

                    if (response) {
                        $('.dataTables_processing').css({"display": "block", "z-index": 10000 })
                        $.ajax({
                            url: $(this).attr("href"),
                            type: 'POST',
                            dataType: 'json',
                            success: function (response) {
                                $('.dataTables_processing').css({"display": "none"});
                                $(".btn.delete").button('reset');
                                if (response.status == 'success') {
                                    toastr.success(response.message, "Success", toastr_opts);
                                    connectionDataTable();
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
                    }
                    return false;

                });

                $('.EnableDisable').unbind().on('change',function(e){
                    var VendorConnectionID=$(this).attr('data-id');
                    var is_checked="";
                    $this=this;
                    $currentchecked=$this.checked;
                    response = confirm('Are you sure?');
                    if(response){

                        if($currentchecked) {
                            $(this).prop("checked");
                        }
                        console.log($this.checked);
                        if($currentchecked == true){
                            // alert("1")
                            is_checked = 1;
                        }
                        else {
                            // alert("0")
                            is_checked = 0;
                        }
                        $('#table-4_processing').css('display','block');
                        setTimeout(function(){
                            e.preventDefault();
                            e.stopPropagation();

                            $.ajax({
                                url: $($this).attr("data-href")+"/"+is_checked,
                                type: 'POST',
                                dataType: 'json',
                                success: function (response) {
                                    $(".btn.delete").button('reset');
                                    if (response.status == 'success') {
                                        toastr.success(response.message, "Success", toastr_opts);
                                        connectionDataTable();
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

                        }, 500);
                    }else{
                        console.log("Cancel");
                        if($currentchecked){
                            $($this).prop('checked',false);
                        }else{
                            $($this).prop('checked',true);
                        }

                    }

                });

                //ChangeMultipleStatus
                $("#changeStatus").unbind().click(function(ev) {
                    $('#bulk-edit-connection-form').trigger("reset");
                    $("#bulk-edit-connection-form").find(".statusActive").prop('checked',true);
                    $(".statusActive").closest('.switch-animate').removeClass('switch-off');
                    var criteria='';
                    if($('#selectallbutton').is(':checked')){
                        //if($('#selectallbutton').find('i').hasClass('entypo-cancel')){
                        criteria = JSON.stringify($searchFilter);
                        if(criteria==''){
                            return false;
                        }
                    }
                    var RateIDs = [];
                    var VendorConnectionIDs = [];
                    var i = 0;
                    var j = 0;
                    $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                        RateID = $(this).val();
                        if($(this).parents('tr').children().find('div.hiddenRowData').find("[name='VendorConnectionID']").val().trim() != ''){
                            console.log($(this).parents('tr').children().find('div.hiddenRowData').find("[name='VendorConnectionID']").val())
                            VendorConnectionIDs[j++] = $(this).parents('tr').children().find('div.hiddenRowData').find("[name='VendorConnectionID']").val()
                        }
                        RateIDs[i++] = RateID;
                    });
                    $('#modal-BulkConnection .modal-header h4').text('Bulk Edit Vendor Connection');
                    $('#bulk-update-params-show').hide();
                    var cur_obj = $(this).prev("div.hiddenRowData");
                    for(var i = 0 ; i< list_fields.length; i++){
                        $("#bulk-edit-connection-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                    }

                    if(criteria!=''){
                        $('#modal-BulkConnection').modal('show', {backdrop: 'static'});
                        $("#bulk-edit-connection-form [name='Action']").val('bulk');
                        $("#bulk-edit-connection-form [name='RateID']").val('');
                        $("#bulk-edit-connection-form [name='VendorConnectionID']").val('');
                    }else if(RateIDs.length){
                        $('#modal-BulkConnection').modal('show', {backdrop: 'static'});
                        $("#bulk-edit-connection-form [name='Action']").val('selected');
                        $("#bulk-edit-connection-form [name='ConnectionID']").val(RateIDs.join(","));
                        $("#bulk-edit-connection-form [name='VendorConnectionID']").val(VendorConnectionIDs.join(","));
                    }
                });

                //Bulk Form Submit
                $("#bulk-edit-connection-form").unbind().submit(function(e) {
                    e.preventDefault();
                    console.log("Bulk Submit");

                    $.ajax({
                        url: baseurl + '/vendor_rates/connection/bulk_update_connection/{{$id}}', //Server script to process data
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            $(".save.btn").button('reset');
                            if (response.status == 'success') {
                                $("#modal-BulkConnection").modal("hide");
                                toastr.success(response.message, "Success", toastr_opts);
                               // data_table.fnFilter('', 0);
                                connectionDataTable();
                            } else {
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                        },
                        error: function(error) {
                            $("#modal-BulkConnection").modal("hide");
                        },
                        // Form data
                        data: $('#bulk-edit-connection-form').serialize()+'&'+$.param($searchFilter),
                        //Options to tell jQuery not to process data or worry about content-type.
                        cache: false

                    });
                    $("#bulk-edit-connection-form").unbind('submit');
                    return false;
                });

                $('#delete_multiconnection').unbind().click(function(e) {
                    console.log("111");
                    e.preventDefault();

                    var criteria = '';
                    if ($('#selectallbutton').is(':checked')) {
                        //if($('#selectallbutton').find('i').hasClass('entypo-cancel')){
                        criteria = JSON.stringify($searchFilter);
                        if (criteria == '') {
                            return false;
                        }
                    }
                    var ConnectionIDs = [];
                    var VendorConnectionIDs = [];
                    var i = 0;
                    var j = 0;
                    var action = '';
                    var ConnectionID = '';


                        $('#table-4 tr .rowcheckbox:checked').each(function (i, el) {
                            ConID = $(this).val();
                            if ($(this).parents('tr').children().find('div.hiddenRowData').find("[name='VendorConnectionID']").val().trim() != '') {
                                console.log($(this).parents('tr').children().find('div.hiddenRowData').find("[name='VendorConnectionID']").val())
                                VendorConnectionIDs[j++] = $(this).parents('tr').children().find('div.hiddenRowData').find("[name='VendorConnectionID']").val()
                            }
                            ConnectionIDs[i++] = ConID;
                        });

                        if (criteria != '') {
                            action = 'bulk';

                        } else if (ConnectionIDs.length) {
                            action = 'selected';

                            ConnectionID = ConnectionIDs.join(",");
                        } else {
                            return false;
                        }
                        var confirmD=confirm('Are you sure?');
                        if(confirmD) {
                        $('#table-4_processing').css('display','block');
                        $.ajax({
                            url: baseurl + '/vendor_rates/connection/bulk_update_connection/{{$id}}', //Server script to process data
                            type: 'POST',
                            dataType: 'json',
                            success: function (response) {
                                $(".save.btn").button('reset');
                                if (response.status == 'success') {
                                    toastr.success(response.message, "Success", toastr_opts);
                                    // data_table.fnFilter('', 0);
                                    connectionDataTable();
                                    //if(action=='bulk'){
                                        //$("#selectall").prop('checked',false);
                                        $("input[type=checkbox]").prop('checked',false);

                                   // setTimeout(function(){
                                        $('#table-4').find('tr').removeClass('selected');
                                    //}, 1000);

                                   // }
                                } else {
                                    toastr.error(response.message, "Error", toastr_opts);
                                }
                            },
                            error: function (error) {

                            },
                            // Form data
                            data: 'Action=' + action + '&ConnectionID=' + ConnectionID + '&isDelete=1&' + $.param($searchFilter),
                            //Options to tell jQuery not to process data or worry about content-type.
                            cache: false

                        });

                    }

                });

                $(".dataTables_wrapper select").select2({
                    minimumResultsForSearch: -1
                });

                $('#table-4 tbody tr').each(function(i, el) {
                    if (checked!='') {
                        $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                        $(this).addClass('selected');
                        $('#selectallbutton').prop("checked", true);
                    } else {
                        $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);;
                        $(this).removeClass('selected');
                    }
                });

                $('#selectallbutton').click(function(ev) {
                    if($(this).is(':checked')){
                        checked = 'checked=checked disabled';
                        $("#selectall").prop("checked", true).prop('disabled', true);
                        if(!$('#changeSelectedInvoice').hasClass('hidden')){
                            $('#table-4 tbody tr').each(function(i, el) {
                                if($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                                    $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                    $(this).addClass('selected');
                                }
                            });
                        }
                    }else{
                        checked = '';
                        $("#selectall").prop("checked", false).prop('disabled', false);
                        if(!$('#changeSelectedInvoice').hasClass('hidden')){
                            $('#table-4 tbody tr').each(function(i, el) {
                                if($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                                    $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                                    $(this).removeClass('selected');
                                }
                            });
                        }
                    }
                });

            }
        });
        $("#selectcheckbox").append('<input type="checkbox" id="selectallbutton" name="checkboxselect[]" class="" title="Select All Found Records" />');
        return false;
    }
</script>
<style>
    .dataTables_filter label{
        display:none !important;
    }
    .dataTables_wrapper .export-data{
        right: 30px !important;
    }
    #selectcheckbox{
        padding: 15px 10px;
    }
    #table-4 tbody tr td.details-control{
        width: 8%;
    }
</style>

@stop


@section('footer_ext')
@parent

<div class="modal fade" id="modal-VendorRate">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="edit-vendor-rate-form" method="post" >
                <input type="hidden" name="VendorConnectionID" />

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"
                            aria-hidden="true">&times;</button>
                    <h4 class="vendor-connection-modal-title">Edit Vendor Connection</h4>
                </div>

                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-4" class="control-label">Type*</label>
                                {{ Form::select('RateTypeID', $Type, '', array("class"=>"select2")) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-4" class="control-label">Name*</label>
                                <input type="text"  name="Name" class="form-control" value="" />
                            </div>
                        </div>
                    </div>

                    <div id="did_Div" class="hidden">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Category</label>
                                    {{ Form::select('did[DIDCategoryID]', $DIDCategories, '', array("class"=>"select2 jjj")) }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Rate Table*</label>
                                    {{ Form::select('did[RateTableID]', $TariffDID, '', array("class"=>"select2")) }}
                                    <span id="DIDTariffLoading" class="hidden">Loading ...</span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Active</label>
                                    <p class="make-switch switch-small">
                                        <input id="did[Active]" name="did[Active]" type="checkbox" value="1" checked >
                                    </p>
                                </div>

                            </div>

                        </div>

                    </div>


                    <div id="package_Div" class="hidden">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Rate Table*</label>
                                    {{ Form::select('package[RateTableID]', $TariffPackage, '', array("class"=>"select2")) }}
                                    <span id="DIDTariffLoading" class="hidden">Loading ...</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group" style="padding-top:15px;">
                                    <label for="field-5" class="control-label">Active</label>
                                    <p class="make-switch switch-small">
                                        <input id="package[Active]" name="package[Active]" type="checkbox" value="1" checked >
                                    </p>
                                </div>

                            </div>
                        </div>

                    </div>


                    <div id="voice_Div" class="hidden">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Trunk*</label>
                                    {{ Form::select('voice[TrunkID]', $trunks, '', array("class"=>"select2")) }}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Trunk Prefix</label>
                                    <input type="text" name="voice[CallPrefix]" class="form-control" id="field-3" placeholder="">
                                </div>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">CLD Translation Rule</label>
                                    <input type="text" name="voice[CLDRule]" class="form-control" id="field-2" placeholder="">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">CLI Translation Rule</label>
                                    <input type="text" name="voice[CLIRule]" class="form-control" id="field-1" placeholder="">
                                </div>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">IP</label>
                                    <input type="text" name="voice[IP]" class="form-control" id="field-4" placeholder="">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Port</label>
                                    <input type="text" name="voice[Port]" class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Username</label>
                                    <input type="text" name="voice[Username]" class="form-control" id="field-6" placeholder="">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Password</label>
                                    <input type="password" name="voice[Password]" class="form-control" id="field-7" placeholder="">
                                </div>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Sip Header</label>
                                    <input type="text" name="voice[SipHeader]" class="form-control" id="field-8" placeholder="">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Authentication Mode</label>
                                    <input type="text" name="voice[AuthenticationMode]" class="form-control" id="field-9" placeholder="">
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Rate Table</label>
                                    {{ Form::select('voice[RateTableID]', $TariffVoiceCall, '', array("class"=>"select2")) }}
                                    <span id="VoiceTariffLoading" class="hidden">Loading ...</span>

                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group" style="margin-top:15px;">
                                    <label for="field-5" class="control-label">Use Prefix In CDR</label>
                                    <p class="make-switch switch-small">
                                        <input id="voice[PrefixCDR]" name="voice[PrefixCDR]" type="checkbox" value="1" >
                                    </p>
                                </div>

                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Location</label>
                                    <input type="text" name="voice[Location]" class="form-control" id="field-10" placeholder="">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group" style="padding-top:15px;">
                                    <label for="field-5" class="control-label">Active</label>
                                    <p class="make-switch switch-small">
                                        <input id="voice[Active]" name="voice[Active]" type="checkbox" value="1" checked >
                                    </p>
                                </div>

                            </div>

                        </div>


                    </div>

                </div>

                <div class="modal-footer">

                    <button type="submit" class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-floppy"></i> Save
                    </button>
                    <button type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                        <i class="entypo-cancel"></i> Close
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="modal-BulkConnection">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="bulk-edit-connection-form" method="post" action="javascript:void(0);">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Bulk Edit Connections Status</h4>
                </div>

                <div class="modal-body">
                    <div id="bulk-update-params-show">
                    </div>
                    <div class="row">

                        <div class="col-md-6">

                            <div class="form-group">
                                <label for="field-5" class="control-label">Status</label>

                                <p class="make-switch switch-small">
                                    <input id="Active" class="statusActive" name="Active" type="checkbox" value="1" checked>
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <input type="hidden" name="VendorPreferenceID" value="">
                    <input type="hidden" name="ConnectionID" value="">
                    <input type="hidden" name="Action" value="">
                    <input type="hidden" name="criteria" value="">
                    <button type="submit"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-floppy"></i>
                        Save
                    </button>
                    <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                        <i class="entypo-cancel"></i>
                        Close
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
