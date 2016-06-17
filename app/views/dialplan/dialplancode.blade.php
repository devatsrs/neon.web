@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>
            <a href="{{URL::to('dialplans')}}">Dial Plans</a>
    </li>
    <li class="active">
        <strong>{{$DialPlanName}}</strong>
    </li>
</ol>
<h3>Dial Strings</h3>

@include('includes.errors')
@include('includes.success')

<div style="float: right;">
    @if( User::checkCategoryPermission('DialPlans','Add') )
        <a href="javascript:;" id="add-new-code" class="btn upload btn-primary ">
            <i class="entypo-upload"></i>
            Add Dial String
        </a>
    @endif
</div>

<ul class="nav nav-tabs bordered">
    <!-- available classes "bordered", "right-aligned" -->
    <li class="active">
        <a href="{{URL::to('/dialplans/'.$id.'/dialplancode')}}">
            <span class="hidden-xs">Dial String</span>
        </a>
    </li>
    @if( User::checkCategoryPermission('DialPlans','Upload') )
    <li><a href="{{URL::to('/dialplans/'.$id.'/upload')}}"> <span
                    class="hidden-xs">Upload</span>
        </a></li>
    @endif
</ul>

<div class="row">
    <div class="col-md-12">
        <form novalidate="novalidate" class="form-horizontal form-groups-bordered validate" method="post" id="dialplan_filter">
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
                        <label class="col-sm-1 control-label" for="field-1">Dial String</label>
                        <div class="col-sm-2">
                            <input type="text" name="ft_dialstring" class="form-control">
                            <input name="ft_dialplanid" value="{{$id}}" type="hidden" >
                        </div>
                        <label class="col-sm-1 control-label">Charge Code</label>
                        <div class="col-sm-2">
                            <input type="text" name="ft_chargecode" class="form-control">
                        </div>
                        <label class="col-sm-1 control-label">Description</label>
                        <div class="col-sm-2">
                            <input type="text" name="ft_description" class="form-control">
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
<div style="text-align: right;padding:10px 0 ">
    @if( User::checkCategoryPermission('DialPlans','Edit'))
    <a href="javascript:;"  id="changeSelectedCode" class="btn btn-primary btn-sm btn-icon icon-left" onclick="jQuery('#modal-6').modal('show', {backdrop: 'static'});" href="javascript:;">
        <i class="entypo-floppy"></i>
        Change Selected Dial String
    </a>
    @endif
    @if( User::checkCategoryPermission('DialPlans','Delete'))
    <button type="submit" id="delete-bulk-code" class="btn btn-danger btn-sm btn-icon icon-left">
        <i class="entypo-cancel"></i>
        Delete Selected Dial String
    </button>
    @endif
</div>
<table class="table table-bordered datatable" id="table-4">
    <thead>
    <tr>
        <th width="5%"><input type="checkbox" id="selectall" name="codedeck[]" class="" />
        </th>
        <th width="20%">Dial String</th>
        <th width="20%">Charge Code</th>
        <th width="20%">Description</th>
        <th width="10%">Forbidden</th>
        <th width="25%">Actions</th>
    </tr>
    </thead>
    <tbody>


    </tbody>
</table>


<script type="text/javascript">
var $searchFilter = {};
var update_new_url;
var checked='';
var postdata;
    jQuery(document).ready(function ($) {
        public_vars.$body = $("body");
        //show_loading_bar(40);

        $("#dialplan_filter").submit(function(e) {
            e.preventDefault();

            $searchFilter.ft_dialstring = $("#dialplan_filter [name='ft_dialstring']").val();
            $searchFilter.ft_chargecode = $("#dialplan_filter [name='ft_chargecode']").val();
            $searchFilter.ft_description = $("#dialplan_filter [name='ft_description']").val();
            $searchFilter.ft_dialplanid = $("#dialplan_filter [name='ft_dialplanid']").val();

            if($searchFilter.ft_dialplanid == ''){
                ShowToastr("error",'Please Select DialPlan');
                return false;
            }


            data_table = $("#table-4").dataTable({
                "bDestroy": true,
                "bProcessing":true,
                "bServerSide":true,
                "sAjaxSource": baseurl + "/dialplans/ajax_datagrid/type",
                "iDisplayLength": '{{Config::get('app.pageSize')}}',
                "fnServerParams": function(aoData) {
                    aoData.push({"name":"ft_dialstring","value":$searchFilter.ft_dialstring},{"name":"ft_chargecode","value":$searchFilter.ft_chargecode},{"name":"ft_description","value":$searchFilter.ft_description},{"name":"ft_dialplanid","value":$searchFilter.ft_dialplanid});
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push({"name":"ft_dialstring","value":$searchFilter.ft_dialstring},{"name":"ft_chargecode","value":$searchFilter.ft_chargecode},{"name":"ft_description","value":$searchFilter.ft_description},{"name":"ft_dialplanid","value":$searchFilter.ft_dialplanid},{ "name": "Export", "value": 1});
                },
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "aaSorting": [[1, 'ASC']],
                "aoColumns":
                        [
                            {"bSortable": false, //DialPlanCodeID
                                mRender: function(id, type, full) {
                                    return '<div class="checkbox "><input type="checkbox" name="codedeck[]" value="' + id + '" class="rowcheckbox" ></div>';
                                }
                            },
                            {  "bSortable": true },//dialstring
                            { "bSortable": true },//chargecode
                            { "bSortable": true },//description
                            { "bSortable": true },//forbidden

                            {
                                "bSortable": true,
                                mRender: function ( id, type, full ) {
                                    var action , edit_ , show_ , delete_;
                                    delete_ = "{{ URL::to('dialplans/{id}/deletecode')}}";

                                    delete_ = delete_.replace( '{id}', full[0] );

                                    DialPlanCodeID = full[0];
                                    DialString = full[1];
                                    ChargeCode = full[2];
                                    Description = full[3];
                                   // Forbidden = ( full[4] == null )? 1:full[4];
                                    Forbidden = full[4];
                                    action = '<div class = "hiddenRowData" >';
                                    action += '<input type = "hidden"  name = "DialPlanCodeID" value = "' + DialPlanCodeID + '" / >';
                                    action += '<input type = "hidden"  name = "DialString" value = "' + DialString + '" / >';
                                    action += '<input type = "hidden"  name = "ChargeCode" value = "' + ChargeCode + '" / >';
                                    action += '<input type = "hidden"  name = "Description" value = "' + Description + '" / >';
                                    action += '<input type = "hidden"  name = "Forbidden" value = "' +  Forbidden + '" / >' ;
                                    action += '</div>';

                                    <?php if(User::checkCategoryPermission('DialPlans','Edit')){ ?>
                                            action += '<a href="javascript:;" class="edit-dialplan btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit </a>';
                                    <?php } ?>
                                            <?php if(User::checkCategoryPermission('DialPlans','Delete') ){ ?>
                                            action += ' <a href="'+ delete_ +'" class="delete-dialplancode btn delete btn-danger btn-sm btn-icon icon-left"><i class="entypo-cancel"></i>Delete </a>';
                                    <?php } ?>

                                            return action;
                                }
                            }
                        ],
                "oTableTools": {
                    "aButtons": [
                        {
                            "sExtends": "download",
                            "sButtonText": "EXCEL",
                            "sUrl": baseurl + "/dialplans/ajax_datagrid/xlsx", //baseurl + "/generate_xlsx.php",
                            sButtonClass: "save-collection btn-sm"
                        },
                        {
                            "sExtends": "download",
                            "sButtonText": "CSV",
                            "sUrl": baseurl + "/dialplans/ajax_datagrid/csv", //baseurl + "/generate_csv.php",
                            sButtonClass: "save-collection btn-sm"
                        }
                    ]
                },
                "fnDrawCallback": function() {

                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });

                    $(".delete-dialplancode").click(function(e) {
                        e.preventDefault();
                        response = confirm('Are you sure?');
                        if (response) {
                            $(this).text('Loading..');
                            $('#table-4_processing').css('visibility','visible');
                            $.ajax({
                                url: $(this).attr("href"),
                                type: 'POST',
                                dataType: 'json',
                                beforeSend: function(){
                                    //    $(this).text('Loading..');
                                },
                                success: function(response) {
                                    if (response.status == 'success') {
                                        toastr.success(response.message, "Success", toastr_opts);
                                        data_table.fnFilter('', 0);
                                    } else {
                                        toastr.error(response.message, "Error", toastr_opts);
                                        data_table.fnFilter('', 0);
                                    }
                                    $('#table-4_processing').css('visibility','hidden');
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

        $('#table-4 tbody').on('click', 'tr', function() {
            if (checked =='') {
                $(this).toggleClass('selected');
                if ($(this).hasClass('selected')) {
                    $(this).find('.rowcheckbox').prop("checked", true);
                } else {
                    $(this).find('.rowcheckbox').prop("checked", false);
                }
            }
        });

        $('#add-new-code').click(function(ev){
            ev.preventDefault();
            $('#add-new-code-form').trigger("reset");
            $("#add-new-code-form [name='DialPlanCodeID']").val('');
            $('#add-new-modal').modal('show');
        });

        $('table tbody').on('click','.edit-dialplan',function(ev){
            ev.preventDefault();
            ev.stopPropagation();
            var prev_raw = $(this).prev("div.hiddenRowData");
            $('#add-new-code-form').trigger("reset");
            $("#add-new-code-form [name='DialPlanCodeID']").val(prev_raw.find("input[name='DialPlanCodeID']").val());
            $("#add-new-code-form [name='DialString']").val(prev_raw.find("input[name='DialString']").val());
            $("#add-new-code-form [name='ChargeCode']").val(prev_raw.find("input[name='ChargeCode']").val());
            $("#add-new-code-form [name='Description']").val(prev_raw.find("input[name='Description']").val());
            $("#add-new-code-form [name='Forbidden']").val(prev_raw.find("input[name='Forbidden']").val());

            $('#add-new-modal').modal('show');
        });

        $("#add-new-code-form").submit(function(e) {
            e.preventDefault();
            var codeid = $("#add-new-code-form [name='DialPlanCodeID']").val();
            if( typeof codeid != 'undefined' && codeid != ''){
                update_new_url = baseurl + '/dialplans/update/'+codeid;
            }else{
                update_new_url = baseurl + '/dialplans/store';
            }

            bulk_update(update_new_url,$("#add-new-code-form").serialize());

        });

        function bulk_update(fullurl,data){
        //alert(data)
            $.ajax({
                url:fullurl, //Server script to process data
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    $("#dialplan-update").button('reset');
                    $(".btn").button('reset');

                    if (response.status == 'success') {
                        $('#modal-DialPlan').modal('hide');
                        $('#add-new-modal').modal('hide');
                        toastr.success(response.message, "Success", toastr_opts);
                        if( typeof data_table !=  'undefined'){
                            data_table.fnFilter('', 0);
                        }
                    } else {
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                },
                data: data,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false
            });
        }

        $("#changeSelectedCode").click(function(ev) {
            var Dialcodes = [];
            var i = 0;
            $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
               // console.log($(this).val());
                Dialcode = $(this).val();
                Dialcodes[i++] = Dialcode;
            });
            if(Dialcodes.length){
                $('#bulk-edit-code-form').trigger("reset");
                $('#modal-DialPlan').modal('show', {backdrop: 'static'});
            }

        });

        $("#bulk-edit-code-form").submit(function(e) {
            e.preventDefault();
            update_new_url = '';
            var criteria = '';
            var Dialcodes = [];
            var Action = '';
            if($('#selectallbutton').is(':checked')){
                criteria = JSON.stringify($searchFilter);
                Action = 'criteria';
            }else{
                Action = 'code';
                var i = 0;
                $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                 //   console.log($(this).val());
                    Dialcode = $(this).val();
                    Dialcodes[i++] = Dialcode;
                });
            }
            if(Dialcodes.length!='' || criteria!=''){
                update_new_url = baseurl + '/dialplans/update_selected';
                bulk_update(update_new_url,'Action='+Action+'&Dialcodes='+Dialcodes+'&criteria='+criteria+'&'+$('#bulk-edit-code-form').serialize());
            }

            return false;
        });

        $("#delete-bulk-code").click(function(ev) {
            ev.preventDefault();
            update_new_url = '';
            var criteria = '';
            var Dialcodes = [];
            var Action = '';

            var dialplanid = $("#dialplan_filter [name='ft_dialplanid']").val();
            if($('#selectallbutton').is(':checked')){
                criteria = JSON.stringify($searchFilter);
                Action = 'criteria';
            }else{
                Action = 'code';
                var i = 0;
                $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                    //   console.log($(this).val());
                    Dialcode = $(this).val();
                    Dialcodes[i++] = Dialcode;
                });
            }
            if(Dialcodes.length!='' || criteria!=''){
                result = confirm("Are you Sure?");
                if(result) {
                    update_new_url = baseurl + '/dialplans/delete_selected';
                    bulk_update(update_new_url, 'DialPlanID=' + dialplanid + '&Action=' + Action + '&Dialcodes=' + Dialcodes + '&criteria=' + criteria);
                }
            }

            return false;

        });

    });
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
</style>
@stop


@section('footer_ext')
@parent
<div class="modal fade" id="add-new-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="add-new-code-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Dial String</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Dial String</label>
                                <input type="text" name="DialString" class="form-control"  placeholder="DialString">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Charge Code</label>
                                <input type="text" name="ChargeCode" class="form-control"  placeholder="ChargeCode">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Description</label>
                                <input type="text" name="Description" class="form-control" id="field-1" placeholder="Description">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Forbidden</label>
                                <input type="text" name="Forbidden" class="form-control" id="field-5" placeholder="">
                                <input name="DialPlanID" value="{{$id}}" type="hidden" >
                                <input name="DialPlanCodeID" value="" type="hidden" >
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="dialplan-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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
<div class="modal fade" id="modal-DialPlan">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="bulk-edit-code-form" method="post">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Bulk Edit Dial String</h4>
                </div>

                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-6">

                            <div class="form-group">
                                <input type="checkbox" name="updateChageCode" class="" />
                                <label for="field-5" class="control-label">Charge Code</label>

                                <input type="text" name="ChargeCode" class="form-control" id="field-5" placeholder="">

                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="form-group">
                                <input type="checkbox" name="updateDescription" class="" />
                                <label for="field-5" class="control-label">Description</label>
                                <input type="text" name="Description" class="form-control" id="field-5" placeholder="">

                            </div>

                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="checkbox" name="updateForbidden" class="" />
                                <label for="field-5" class="control-label">Forbidden</label>
                                <input type="text" name="Forbidden" class="form-control" id="field-5" placeholder="">
                                <input name="DialPlanID" value="{{$id}}" type="hidden" >
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">

                    <button type="submit" id="dialplan-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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
