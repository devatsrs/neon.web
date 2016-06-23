@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Cron Job</strong>
    </li>
</ol>
<h3>Cron Job</h3>

@include('includes.errors')
@include('includes.success')
<p style="text-align: right;">
@if( User::checkCategoryPermission('CronJob','Add') )
    <a href="#" id="add-new-config" class="btn btn-primary ">
        <i class="entypo-plus"></i>
        Add Cron Job
    </a>
@endif
</p>

<div class="row">
    <div class="col-md-12">
        <form id="cronjob_filter" method="get"    class="form-horizontal form-groups-bordered validate" novalidate>
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        Filter
                    </div>
                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Active</label>
                        <div class="col-sm-2">
                            <?php $active = [""=>"Both","1"=>"Active","0"=>"Inactive"]; ?>
                            {{ Form::select('Active', $active, 1, array("class"=>"form-control selectboxit")) }}
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
</div>


<table class="table table-bordered datatable" id="table-4">
    <thead>
    <tr>
        <th width="20%">Job Title</th>
        <th width="20%">Name</th>
        <th width="20%">Status</th>
        <th width="20%">Action</th>
    </tr>
    </thead>
    <tbody>
    </tbody>
</table>

<script type="text/javascript">
var $searchFilter = {};
var update_new_url;
var postdata;
    jQuery(document).ready(function ($) {
        public_vars.$body = $("body");

        //show_loading_bar(40);
        $('#cronjob_filter').submit(function(e) {
            e.preventDefault();
            $searchFilter.Active = $('#cronjob_filter [name="Active"]').val();
            data_table = $("#table-4").dataTable({
                "bDestroy": true,
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": baseurl + "/cronjobs/ajax_datagrid/type",
                "iDisplayLength": '{{Config::get('app.pageSize')}}',
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "fnServerParams": function (aoData) {
                    aoData.push({"name": "Active", "value": $searchFilter.Active});
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push({"name": "Active", "value": $searchFilter.Active},
                            {"name": "Export", "value": 1});
                },
                "aaSorting": [[0, 'asc']],
                "aoColumns": [
                    {"bSortable": true},//0 title
                    {"bSortable": true},  //1   name
                    {
                        mRender: function (status, type, full) {
                            if (status == 1)
                                return '<i style="font-size:22px;color:green" class="entypo-check"></i>';
                            else
                                return '<i style="font-size:28px;color:red" class="entypo-cancel"></i>';
                        }
                    }, //2   Status
                    {                       //3
                        "bSortable": false,
                        mRender: function (CronJobID, type, full) {

                            var action = '';


                            action = '<div class = "hiddenRowData" >';
                            action += '<input type = "hidden"  name = "JobTitle" value = "' + (full[0] !== null ? full[0] : '') + '" / >';
                            action += '<input type = "hidden"  name = "Title" value = "' + (full[1] !== null ? full[1] : '') + '" / >';
                            action += '<input type = "hidden"  name = "Status" value = "' + (full[2] !== null ? full[2] : '') + '" / >';
                            action += '<input type = "hidden"  name = "CronJobID" value = "' + CronJobID + '" / >';
                            action += '<input type = "hidden"  name = "CronJobCommandID" value = "' + (full[4] !== null ? full[4] : '') + '" / >';
                            action += '<div id="cron_set" style="display: none" >' + (full[5] !== null ? full[5] : '') + '</div>'
                            //action += '<input type = "hidden"  name = "SippyUsageInterval" value = "' + setiting.SippyUsageInterval+ '" / >';
                            action += '</div>';
                            var history_url = baseurl + "/cronjobs/history/" + CronJobID;

                            <?php if(User::checkCategoryPermission('CronJob','Edit') ){ ?>
                            action += ' <a data-name = "' + full[1] + '" data-id="' + CronJobID + '" class="edit-config btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit </a>';
                            <?php } ?>
                            <?php if(User::checkCategoryPermission('CronJob','Delete') ){ ?>
                            action += ' <a data-id="' + CronJobID + '" class="delete-config btn delete btn-danger btn-sm btn-icon icon-left"><i class="entypo-cancel"></i>Delete </a>';
                            <?php } ?>
                            action += ' <a href="' + history_url + '" class=" btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>History </a>';

                            return action;
                        }
                    },

                ],
                "oTableTools": {
                    "aButtons": [
                        {
                            "sExtends": "download",
                            "sButtonText": "EXCEL",
                            "sUrl": baseurl + "/cronjobs/ajax_datagrid/xlsx", //baseurl + "/generate_xlsx.php",
                            sButtonClass: "save-collection btn-sm"
                        },
                        {
                            "sExtends": "download",
                            "sButtonText": "CSV",
                            "sUrl": baseurl + "/cronjobs/ajax_datagrid/csv", //baseurl + "/generate_csv.php",
                            sButtonClass: "save-collection btn-sm"
                        }
                    ]
                },
                "fnDrawCallback": function () {
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });
                }

            });
        });


        // Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });

    $('#cronjob_filter').submit();
    $('#add-new-config').click(function(ev){
        ev.preventDefault();
        $('#add-new-config-form').trigger("reset");
        $("#add-new-config-form [name='CronJobID']").val('');
        $("#CronJobCommandID").select2().select2('val','');
        //$("#add-new-config-form [name='Setting[JobTime]']").val('');
        $("#add-new-config-form [name='Setting[JobTime]']").selectBoxIt().data("selectBox-selectBoxIt").selectOption('');
        $("#add-new-config-form [name='Setting[JobDay][]']").select2().select2('val','');
        $("#CronJobCommandID").trigger('change');
        $("#CronJobCommandID").prop("disabled", false);
        $('#add-new-modal-config h4').html('Add New Cron Job');
        $('#add-new-modal-config').modal('show');
    });
    $('table tbody').on('click','.delete-config',function(ev){
        result = confirm("Are you Sure?");
        if(result){
            submit_ajax(baseurl+'/cronjobs/delete/'+$(this).attr('data-id'));
        }
    });

    $('table tbody').on('click','.edit-config',function(ev){
        ev.preventDefault();
        ev.stopPropagation();
        $('#add-new-config-form').trigger("reset");
        var prevrow = $(this).prev("div.hiddenRowData");
        $('#add-new-modal-config h4').html('Edit Cron Job');
        $('#add-new-modal-config').modal('show');
        $("#add-new-config-form [name='CronJobID']").val(prevrow.find("input[name='CronJobID']").val())
        $("#add-new-config-form [name='Title']").val(prevrow.find("input[name='Title']").val())
        $("#add-new-config-form [name='JobStartTime']").val(prevrow.find("input[name='JobStartTime']").val())
        $("#add-new-config-form [name='JobTitle']").val(prevrow.find("input[name='JobTitle']").val())
        var setting = prevrow.find("#cron_set").html()
        if (setting !== null && setting.trim() != ''){
            var json = JSON.parse(setting);
            if(json !== null && typeof  json['JobStartTime'] != 'undefined'){
                $("#add-new-config-form [name='Setting[JobStartTime]']").val(json['JobStartTime']);
            }
            if(json !== null && typeof  json['JobTime'] != 'undefined'){
                $("#add-new-config-form [name='Setting[JobTime]']").selectBoxIt().data("selectBox-selectBoxIt").selectOption(json['JobTime']);
            }
            if(json !== null && typeof  json['JobInterval'] != 'undefined'){
                $("#add-new-config-form [name='Setting[JobInterval]']").selectBoxIt().data("selectBox-selectBoxIt").selectOption(json['JobInterval']);
            }
            if(json !== null && typeof json['JobDay'] != 'undefined'){
                var str =json['JobDay'].toString().split(",");
                $("#add-new-config-form [name='Setting[JobDay][]']").select2().select2('val',str);
            }else{
                $("#add-new-config-form [name='Setting[JobDay][]']").select2().select2('val','');
            }
            if(json !== null && typeof  json['JobStartDay'] != 'undefined'){
                $("#add-new-config-form [name='Setting[JobStartDay]']").selectBoxIt().data("selectBox-selectBoxIt").selectOption(json['JobStartDay']);
            }

         }
        $("#CronJobCommandID").select2().select2('val',prevrow.find("input[name='CronJobCommandID']").val());
        $("#CronJobCommandID").trigger('change');
        $("#CronJobCommandID").prop("disabled", true);

        if(prevrow.find("input[name='Status']").val() == 1 ){
            $('[name="Status_name"]').prop('checked',true)
        }else{
            $('[name="Status_name"]').prop('checked',false)
        }




    });
    $('[name="Status_name"]').change(function(e){
        if($(this).prop('checked')){
            $("#add-new-config-form [name='Status']").val(1);
        }else{
            $("#add-new-config-form [name='Status']").val(0);
        }

    });
    $('#add-new-config-form').submit(function(e){
        e.preventDefault();
        var CronJobID = $("#add-new-config-form [name='CronJobID']").val()
        if( typeof CronJobID != 'undefined' && CronJobID != ''){
            update_new_url = baseurl + '/cronjobs/update/'+CronJobID;
        }else{
            update_new_url = baseurl + '/cronjobs/create';
        }
        $.ajax({
            url: update_new_url,  //Server script to process data
            type: 'POST',
            dataType: 'json',
            success: function (response) {
                if(response.status =='success'){
                    toastr.success(response.message, "Success", toastr_opts);
                    $('#add-new-modal-config').modal('hide');
                     data_table.fnFilter('', 0);
                }else{
                    toastr.error(response.message, "Error", toastr_opts);
                }
                $("#config-update").button('reset');
            },
            // Form data
            data: $('#add-new-config-form').serialize(),
            //Options to tell jQuery not to process data or worry about content-type.
            cache: false
        });
    });

    $('#CronJobCommandID').change(function(e){

        $('#ajax_config_html').html('Loading...<br>');

        if($(this).val() != ''){
        $("#CronJobCommandID_hide").val($(this).val());
        $.ajax({
            url: baseurl + "/cronjobs/ajax_load_cron_dropdown",
            type: 'POST',
            success: function(response) {
                $('#ajax_config_html').html(response);
            },
            // Form data
            data: "CronJobCommandID="+$(this).val()+'&CronJobID='+$("#add-new-config-form [name='CronJobID']").val(),
            cache: false
            });
        }else{
            $('#ajax_config_html').html('');
        }
    });


         $("#add-new-config-form [name='Setting[JobTime]']").change(function(){
            console.log("jobtype" + $(this).val());
            populateJonInterval($(this).val());

         });

        function populateJonInterval(jobtype){

            //console.log("in populateJonInterval ");
            $("#add-new-config-form [name='Setting[JobInterval]']").addClass('visible');
            var selectBox = $("#add-new-config-form [name='Setting[JobInterval]']").selectBoxIt().data("selectBox-selectBoxIt");
            var selectBoxStartDay = $("#add-new-config-form [name='Setting[JobStartDay]']").selectBoxIt().data("selectBox-selectBoxIt");
            $("#add-new-config-form .JobStartDay").hide();
            var starttime = $("#add-new-config-form .starttime");
            if(selectBox){
                 selectBox.remove();
                // console.log("jobtype" + jobtype);
                if(jobtype == 'HOUR'){
                    for(var i=1;i<'24';i++){
                        selectBox.add({ value: i, text: i+" Hour"})
                    }
                    starttime.show();
                }else if(jobtype == 'MINUTE'){
                    for(var i=1;i<60;i++){
                        selectBox.add({ value: i, text: i+" Minute"})
                    }
                    starttime.hide();
                    starttime.val('');
                }else if(jobtype == 'DAILY'){
                    for(var i=1;i<'32';i++){
                         selectBox.add({ value: i, text: i+" Day"})
                    }
                    //console.log("jobtype" + jobtype);
                    starttime.show();
                }else if(jobtype == 'MONTHLY'){
                    for(var i=1;i<13;i++){
                        selectBox.add({ value: i, text: i+" Month"})
                    }
                    for(var i=1;i<'32';i++){
                         selectBoxStartDay.add({ value: i, text: i+" Day"})
                    }
                    $("#add-new-config-form .JobStartDay").show();
                    starttime.show();
                }
                @if(isset($commandconfigval->JobInterval))
                    selectBox.selectOption('{{$commandconfigval->JobInterval}}');
                @endif
            }
        }

        // Timepicker
        if ($.isFunction($.fn.timepicker))
        {
            $(".timepicker").each(function(i, el)
            {
                var $this = $(el),
                        opts = {
                    template: attrDefault($this, 'template', false),
                    showSeconds: attrDefault($this, 'showSeconds', false),
                    defaultTime: attrDefault($this, 'defaultTime', 'current'),
                    showMeridian: attrDefault($this, 'showMeridian', true),
                    minuteStep: attrDefault($this, 'minuteStep', 15),
                    secondStep: attrDefault($this, 'secondStep', 15)
                },
                $n = $this.next(),
                        $p = $this.prev();

                $this.timepicker(opts);

                if ($n.is('.input-group-addon') && $n.has('a'))
                {
                    $n.on('click', function(ev)
                    {
                        ev.preventDefault();

                        $this.timepicker('showWidget');
                    });
                }

                if ($p.is('.input-group-addon') && $p.has('a'))
                {
                    $p.on('click', function(ev)
                    {
                        ev.preventDefault();

                        $this.timepicker('showWidget');
                    });
                }
            });
        }

    });
</script>
<style>
.dataTables_filter label{
    display:none !important;
}
.dataTables_wrapper .export-data{
    right: 30px !important;
}
</style>
@stop

@section('footer_ext')
@parent
<div class="modal fade" id="add-new-modal-config">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="add-new-config-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add New Cron Job</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Job Title</label>
                                <input type="text" name="JobTitle" class="form-control" id="field-5" placeholder="">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Cron Type</label>
                                {{ Form::select('CronJobCommandID', $commands, '', array("class"=>"select2",'id'=>'CronJobCommandID')) }}
                             </div>
                        </div>
                    </div>
                    <div id="ajax_config_html"></div>
                    <div class="row">
                    <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Job Time</label>
                                {{Form::select('Setting[JobTime]',array(""=>"Select run time","MINUTE"=>"Minute","HOUR"=>"Hourly","DAILY"=>"Daily",'MONTHLY'=>'Monthly'),'',array( "class"=>"selectboxit"))}}
                            </div>
                    </div>
                    <div class="col-md-6">
                          <div class="form-group">
                                <label for="field-5" class="control-label">Job Interval</label>
                                 {{Form::select('Setting[JobInterval]',array(),'',array( "class"=>"selectboxit"))}}
                          </div>
                    </div>
                    <div class="clear"></div>
                    <div class="col-md-6">
                         <div class="form-group">
                                <label for="field-5" class="control-label">Job Day</label>
                                 {{Form::select('Setting[JobDay][]',array("SUN"=>"Sunday","MON"=>"Monday","TUE"=>"Tuesday","WED"=>"Wednesday","THU"=>"Thursday","FRI"=>"Friday","SAT"=>"Saturday"),'',array( "class"=>"select2",'multiple',"data-placeholder"=>"Select day"))}}
                           </div>
                    </div>
                    <div class="col-md-6">
                          <div class="form-group">
                                <label for="field-5" class="control-label">Job Start Time</label>
                                <!--<input type="text"  name="Setting[JoStratTime]" value="" class="form-control timepicker starttime2" data-minute-step="5" data-show-meridian="true"  data-default-time="12:00:00 AM" data-show-seconds="true" data-template="dropdown">-->
                                <input type="text" data-template="dropdown" data-show-seconds="true" data-default-time="12:00:00 AM" data-show-meridian="true" data-minute-step="5" class="form-control timepicker starttime2" value="12:00:00 AM" name="Setting[JobStartTime]">
                          </div>
                     </div>
                </div>
                <div class="row">
                    <div class="col-md-6 JobStartDay">
                          <div class="form-group">
                                <label for="field-5" class="control-label">Job Start Day</label>
                                 {{Form::select('Setting[JobStartDay]',array(),'',array( "class"=>"selectboxit"))}}
                          </div>
                    </div>
                    <div class="col-md-6">
                     <div class="form-group">
                    <label for="field-5" class="control-label">Active</label>
                    <div class="clear">
                        <p class="make-switch switch-small">
                            <input type="checkbox" checked=""  name="Status_name" value="0">
                        </p>
                        <input type="hidden"  name="Status" value="0">
                        <input type="hidden"  name="CronJobCommandID" id="CronJobCommandID_hide">
                    </div>
                      </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="config-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-floppy"></i>
                        Save
                        <input type="hidden" name="CronJobID" value="">
                    </button>
                    <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                        <i class="entypo-cancel"></i>
                        Close
                    </button>
                </div>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

