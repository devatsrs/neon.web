@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <a>Import Missing Gatway Account</a>
    </li>
</ol>
<h3>Import Missing Gatway Account</h3>

@include('includes.errors')
@include('includes.success')


<div class="tab-content">
    <div class="tab-pane active">
        <div class="row">
            <div class="col-md-12">
                <form novalidate="novalidate" class="form-horizontal form-groups-bordered validate" method="post" id="gateway_filter">
                    <div data-collapsed="0" class="panel panel-primary">
                        <div class="panel-heading">
                            <div class="panel-title">
                                Filter
                            </div>
                            <div class="panel-options">
                                <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label class="col-sm-3 control-label" for="field-1">Gateway</label>
                                <div class="col-sm-4">
                                    {{ Form::select('CompanyGatewayID',$gateway,'', array("class"=>"select2","id"=>"bluk_CompanyGatewayID")) }}
                                </div>

                            </div>
                            <p style="text-align: right;">
                                <button class="btn btn-primary btn-sm btn-icon icon-left" type="submit">
                                    <i class="entypo-search"></i>
                                    Search
                                </button>
                            </p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div  class="col-md-12">
            <div class="input-group-btn pull-right" style="width:200px;">
                <span style="text-align: right;padding-right: 10px;"><button type="button" id="importaccount"  class="btn btn-primary "><span>Import</span></button></span>
                <span style="text-align: right;padding-right: 10px;"><button type="button" class="btn importgatewayaccount btn-primary "><span>Import From Gateway</span></button></span>
            </div><!-- /btn-group -->
        </div>
        <div class="clear"></div>
    <br>
        <div class="row">
            <div class="col-md-12">
                <table class="table table-bordered datatable" id="table-4">
                    <thead>
                    <tr>
                        <th width="5%"><input type="checkbox" id="selectall" name="checkbox[]" class="" /></th>
                        <th width="15%" >Account Name</th>
                        <th width="15%" >First Name</th>
                        <th width="15%" >Last Name</th>
                        <th width="15%" >Email</th>
                    </tr>
                    </thead>
                    <tbody>
                   </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
var $searchFilter = {};
var checked='';
var update_new_url;
var postdata;

    jQuery(document).ready(function ($) {

        public_vars.$body = $("body");
        //$("#gateway_filter [name='CompanyGatewayID']").select2().select2('val','');

        $('#bluk_CompanyGatewayID').change(function(e){

        if($(this).val()){
            $.ajax({
                url:  baseurl +'/cdr_upload/get_accounts/'+$(this).val(),  //Server script to process data
                type: 'POST',
                success: function (response) {
                $('#bulk_AccountID').empty();
                $('#bulk_AccountID').append(response);
                setTimeout(function(){
                    $("#bulk_AccountID").select2('val','');
                },200)
                },
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false
            });

        }
        });
        $('#bluk_CompanyGatewayID').trigger('change');
        // Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });
        $("#selectall").click(function(ev) {
            var is_checked = $(this).is(':checked');
            $('#table-4 tbody tr').each(function(i, el) {
                if($(this).find('.rowcheckbox').hasClass('rowcheckbox')){
                    if (is_checked) {
                        $(this).find('.rowcheckbox').prop("checked", true);
                        $(this).addClass('selected');
                    } else {
                        $(this).find('.rowcheckbox').prop("checked", false);
                        $(this).removeClass('selected');
                    }
                }
            });
        });
        $('#table-4 tbody').on('click', 'tr', function() {
            if (checked =='') {
                if ($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                    $(this).toggleClass('selected');
                    if ($(this).hasClass('selected')) {
                        $(this).find('.rowcheckbox').prop("checked", true);
                    } else {
                        $(this).find('.rowcheckbox').prop("checked", false);
                    }
                }
            }
        });

        $("#gateway_filter").submit(function(e) {
            e.preventDefault();
            var list_fields  =['AccountName','CompanyGatewayID'];

            $searchFilter.CompanyGatewayID = $("#gateway_filter [name='CompanyGatewayID']").val();

            if($searchFilter.CompanyGatewayID.trim() == ''){
                toastr.error("Please Select a Gateway", "Error", toastr_opts);
                return false;
            }
            data_table = $("#table-4").dataTable({

                "bProcessing":true,
                "bDestroy": true,
                "bServerSide":true,
                "sAjaxSource": baseurl + "/accounts/ajax_get_missing_gatewayaccounts",
                "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "iDisplayLength": '{{Config::get('app.pageSize')}}',
                "fnServerParams": function(aoData) {
                    aoData.push({"name":"CompanyGatewayID","value":$searchFilter.CompanyGatewayID});
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push({"name":"CompanyGatewayID","value":$searchFilter.CompanyGatewayID},{"name":"Export","value":1});
                },
                "sPaginationType": "bootstrap",
                "aaSorting"   : [[1, 'asc']],
                "oTableTools":
                {
                    "aButtons": [
                        {
                            "sExtends": "download",
                            "sButtonText": "Export Data",
                            "sUrl": baseurl + "/accounts/ajax_get_missing_gatewayaccounts",
                            sButtonClass: "save-collection"
                        }
                    ]
                },
                "aoColumns":
                [
                    {"bSortable": false,
                        mRender: function(id, type, full) {
                            return '<div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + id + '" class="rowcheckbox" ></div>';
                        }
                    }, //0Checkbox
                    { "bSortable": true },//account name
                    { "bSortable": true },//first name
                    { "bSortable": true },// last name
                    { "bSortable": true }  /* email,
                         { mRender: function(id, type, full) {
                             action = '<div class = "hiddenRowData" >';
                             for(var i = 0 ; i< list_fields.length; i++){
                                                         action += '<input type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null?full[i]:'')+ '" / >';
                             }
                              action += '</div>';

                             action += ' <button class="btn clear delete_cdr btn-danger btn-sm btn-icon icon-left" data-loading-text="Loading..."><i class="entypo-cancel"></i>Clear CDR</button>';

                             return action;
                             }*/
                ],
                "fnDrawCallback": function() {
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });
                    $('#table-4 tbody tr').each(function(i, el) {
                        if($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                            if (checked != '') {
                                $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                $(this).addClass('selected');
                                $('#selectallbutton').prop("checked", true);
                            } else {
                                $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                                $(this).removeClass('selected');
                            }
                        }
                    });

                    $('#selectallbutton').click(function(ev) {
                        if($(this).is(':checked')){
                            checked = 'checked=checked disabled';
                            $("#selectall").prop("checked", true).prop('disabled', true);
                            if(!$('#changeSelectedInvoice').hasClass('hidden')){
                                $('#table-4 tbody tr').each(function(i, el) {
                                    $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                    $(this).addClass('selected');
                                });
                            }
                        }else{
                            checked = '';
                            $("#selectall").prop("checked", false).prop('disabled', false);
                            if(!$('#changeSelectedInvoice').hasClass('hidden')){
                                $('#table-4 tbody tr').each(function(i, el) {
                                    $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                                    $(this).removeClass('selected');
                                });
                            }
                        }
                    });

                }
                });
                $("#selectcheckbox").append('<input type="checkbox" id="selectallbutton" name="checkboxselect[]" class="" title="Select All Found Records" />');
            });
            //ajax search over

        $("#importaccount").click(function(ev) {
            var criteria = '';
            var AccountIDs = [];
            var gatewayid = $searchFilter.CompanyGatewayID;
            if($('#selectallbutton').is(':checked')){
                //criteria = JSON.stringify($searchFilter);
                criteria = 1;
            }else{
                var i = 0;
                $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                    //console.log($(this).val());
                    AccountID = $(this).val();
                    if(typeof AccountID != 'undefined' && AccountID != null && AccountID != 'null'){
                        AccountIDs[i++] = AccountID;
                    }
                });
            }
            if(AccountIDs.length || criteria==1 ){
                if(criteria==''){
                    AccountIDs=AccountIDs.join(",");
                }
                if (!confirm('Are you sure you want to import selected gateway account?')) {
                   return;
                }
                $.ajax({
                    url: baseurl + '/accounts/add_missing_gatewayaccounts',
                    data: 'TempAccountIDs='+AccountIDs+'&criteria='+criteria+'&companygatewayid='+gatewayid,
                    error: function () {
                        toastr.error("error", "Error", toastr_opts);
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status == 'success') {
                            toastr.success(response.message, "Success", toastr_opts);
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                    },
                    type: 'POST'
                });

            }
        });

        $('.importgatewayaccount').on('click',function(ev){
            ev.preventDefault();
            ev.stopPropagation();
            if($searchFilter.CompanyGatewayID.trim() == ''){
                toastr.error("Please Select a Gateway", "Error", toastr_opts);
                return false;
            }
            $(this).button('loading');
            submit_ajax(baseurl+'/accounts/getAccountInfoFromGateway/'+$searchFilter.CompanyGatewayID,'');
        });

        });

</script>
<style>
.dataTables_filter label{
    /*display:none !important;*/
}
.dataTables_wrapper .export-data{
    right: 30px !important;
}
#selectcheckbox{
    padding: 15px 10px;
}
</style>
@stop
