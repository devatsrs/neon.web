<?php
/**
 * Created by PhpStorm.
 * User: deven
 * Date: 21/06/2016
 * Time: 11:55 AM
 */
?>
@extends('layout.main')
@section('content')
    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li>
            <a href="{{URL::to('cronjob_monitor')}}">Cron Job</a>
        </li>
        <li class="active">
            <strong>Cron Job</strong>
        </li>
    </ol>
    <h3>Cron Job</h3>
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
            <form id="cronjob_filter" method=""  action="" class="form-horizontal form-groups-bordered validate" novalidate>
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
                            <label for="field-1" class="col-sm-1 control-label">Search</label>
                            <div class="col-sm-2">
                                <input class="form-control" name="Title"  type="text" />
                            </div>
                            <label for="field-1" class="col-sm-1 control-label">Status</label>
                            <div class="col-sm-2">

                                 {{ Form::select('Status', [""=>"All",CronJob::ACTIVE=>"Active",CronJob::INACTIVE=>"Inactive","running"=>"Running"], CronJob::ACTIVE, array("class"=>"form-control select2 small")) }}

                            </div>
                            <label for="field-1" class="col-sm-1 control-label">Auto Refresh</label>
                            <div class="col-sm-2">

                                <p class="make-switch switch-small">
                                    <input id="" name="AutoRefresh" type="checkbox" checked value="1">
                                </p>
                            </div>
                            <label for="field-1" class="col-sm-1 control-label">Cron Tab Status</label>
                            <div class="col-sm-2">

                                    @if($crontab_status)
                                        <button type="button" data-loading-text="..." data-original-title="Cron Tab is running" data-content="What is Cron Tab? Cron Tab is a linux utility that allows tasks to be automatically run in the background at regular intervals by the cron daemon." data-placement="top" data-trigger="hover" data-toggle="popover" class="btn btn-green btn-sm popover-primary">&nbsp;</button>
                                    @else
                                        <button type="button" data-loading-text="..." data-original-title="Cron Tab is stopped" data-content="What is Cron Tab? Cron Tab is a linux utility that allows tasks to be automatically run in the background at regular intervals by the cron daemon." data-placement="top" data-trigger="hover" data-toggle="popover" class="start_crontab btn btn-red btn-sm popover-primary">&nbsp;</button>
                                    @endif


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



    <div class="clear-fix clear"></div>


    <table class="table table-bordered datatable" id="cronjobs">
        <thead>
        <tr>
            <th width="5%"></th>
            <th width="5%">PID</th>
            <th width="20%">Title</th>
            <th width="20%">Running Since</th>
            <th width="15%">Last Run Time</th>
            <th width="15%">Next Run Time</th>
            <th width="20%"></th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>

    <script type="text/javascript">
        var $searchFilter = {};
        var update_new_url;
        var postdata;
        var auto_refresh=true;
        jQuery(document).ready(function ($) {
            public_vars.$body = $("body");
            var list_fields  = ['Active','PID','JobTitle','RunningTime','LastRunTime','NextRunTime','CronJobID','Status',"CronJobCommandID"] /* Settings, CronJobStatus */;
            var $searchFilter = {};
            $searchFilter.Status = $('#cronjob_filter [name="Status"]').val();
            $searchFilter.Title = $('#cronjob_filter [name="Title"]').val();
            $searchFilter.AutoRefresh = $('#cronjob_filter [name="AutoRefresh"]').val();

            data_table = $("#cronjobs").dataTable({
                "bDestroy": true,
                "bProcessing":true,
                "bServerSide":true,
                "bPaginate": true,
                "sAjaxSource": baseurl + "/cronjobs/activecronjob_ajax_datagrid",
                "iDisplayLength": '{{Config::get('app.pageSize')}}',
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left  'l><'col-xs-6 col-right'<'change-view'><'export-data'T>f>r><'gridview'>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "fnServerParams": function(aoData) {
                    //aoData.length = 0;
                    aoData.push({"name": "Status", "value": $searchFilter.Status},{"name": "Title", "value": $searchFilter.Title},{"name": "Export", "value": 1});
                },
                "aaSorting": [[0, 'desc']],
                "fnRowCallback": function( nRow, data, iDisplayIndex, iDisplayIndexFull ) {

                    if(typeof data[10] != 'undefined' && data[10] == "{{CronJob::CRON_FAIL}}"  ){ // Last failed CronJob 'CronJobStatus'
                        $(nRow).css('background-color', '#f88379');
                        $(nRow).find("td:nth-child(3)").append('&nbsp;&nbsp; <span title="Cron Job is failing..." data-placement="top" class="badge badge-danger" data-toggle="tooltip">i</span>');
                    }
                    if(typeof data[7] != 'undefined' && data[7] == 0  ){ // 'Status'  InActive CronJob Gray color
                        $(nRow).css('background-color', '#f5f5f6');
                        $(nRow).find("td:nth-child(3)").append('&nbsp;&nbsp; <span title="Cron Job is Disabled" data-placement="top" class="badge badge-warning" data-toggle="tooltip">i</span>');
                    }



                },
                "aoColumns":
                        [
                            {  "bSortable": false,

                                mRender: function ( Active, type, full ) {
                                    var action ='';
                                    var CronJobID = full[6];
                                    if(Active==0){
                                        action += ' <button data-id="'+ CronJobID +'" class="cronjob_trigger btn btn-green btn-sm" type="button" title="Manually Execute" data-placement="top" data-toggle="tooltip"><i class="entypo-play"></i></button>';
                                    } else {
                                        action += ' <button data-id="'+ CronJobID +'" class="cronjob_terminate btn btn-red btn-sm" type="button" title="Terminate" data-placement="top" data-toggle="tooltip"><i class="entypo-stop" ></i></button>';
                                    }

                                    return action;
                                }

                            },//0 Active
                            {  "bSortable": true },//1 Pid
                            {  "bSortable": true },  //2   Title
                            {  "bSortable": false,    // Runnince since

                                mRender: function ( RunningTime, type, full ) {
                                    var PID =  full[1];
                                    if(PID > 0){
                                        RunningTime = RunningTime.replace("0 Hours, ", "")
                                        RunningTime = RunningTime.replace("0 Minutes, ", "");
                                        return RunningTime;
                                    }

                                }

                            },  //3   Running Hour
                            {  "bSortable": true,},  //3   Last Run Time
                            {  "bSortable": true,},  //3   Next Run Time
                            {                       //4
                                "bSortable": false,
                                mRender: function ( CronJobID, type, full ) {

                                    var action ='';

                                    action = '<div class = "hiddenRowData" >';
                                    for(var i = 0 ; i< list_fields.length; i++){
                                        action += '<input type = "hidden"  name = "' + list_fields[i] + '" value = "' + (full[i] != null?full[i]:'')+ '" / >';
                                    }
                                    action += '<div id="cron_set" style="display: none" >' + (full[9] !== null ? full[9] : '') + '</div>'
                                    action += '</div>';

                                    var Status = full[7];

                                    <?php if(User::checkCategoryPermission('CronJob','Edit') ){ ?>
                                            action += '&nbsp;<button   data-id="' + CronJobID + '" class="edit-config btn btn-default btn-sm" title="Edit" data-placement="top" data-toggle="tooltip"><i class="entypo-pencil"></i></button>';
                                    <?php } ?>

                                    var history_url = baseurl + "/cronjobs/history/" + CronJobID;

                                    action += '&nbsp;<a target="_blank" href="'+ history_url +'" class=" btn btn-default btn-sm" title="History" data-placement="top" data-toggle="tooltip"><i class="entypo-back-in-time"></i></a>';

                                    <?php if(User::checkCategoryPermission('CronJob','Edit') ){ ?>
                                        if(Status == 1 ) {
                                            action += '&nbsp;<button data-id="'+ CronJobID +'" data-status="'+Status+'" class="cronjob_change_status btn btn-red btn-sm" type="button" title="InActive" data-placement="left" data-toggle="tooltip"><i class="glyphicon glyphicon-ban-circle" ></i></button>';
                                        }else {
                                            action += '&nbsp;<button data-id="' + CronJobID + '" data-status="'+Status+'" class="cronjob_change_status btn btn-green btn-sm" type="button" title="Active" data-placement="left" data-toggle="tooltip"><i class="entypo-check"></i></button>';
                                        }
                                    <?php } ?>

                                    <?php if(User::checkCategoryPermission('CronJob','Delete')){ ?>
                                            action += '&nbsp;<button data-id="' + CronJobID + '" class="delete-config btn delete btn-danger btn-sm" title="Delete" data-placement="top" data-toggle="tooltip"><i class="entypo-cancel"></i></button>';
                                    <?php } ?>


                                    return action;
                                }
                            },

                        ],
                "oTableTools": {
                    "aButtons": [
                        {
                            "sExtends": "download",
                            "sButtonText": "Refresh",
                            sButtonClass: "save-collection"
                        }
                    ]
                },
                "fnDrawCallback": function() {

                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });

                    $('[data-toggle="popover"]').each(function(i, el)
                    {
                        var $this = $(el),
                                placement = attrDefault($this, 'placement', 'right'),
                                trigger = attrDefault($this, 'trigger', 'click'),
                                popover_class = $this.hasClass('popover-secondary') ? 'popover-secondary' : ($this.hasClass('popover-primary') ? 'popover-primary' : ($this.hasClass('popover-default') ? 'popover-default' : ''));

                        $this.popover({
                            placement: placement,
                            trigger: trigger
                        });

                        $this.on('shown.bs.popover', function(ev)
                        {
                            var $popover = $this.next();

                            $popover.addClass(popover_class);
                        });
                    });

                    $('[data-toggle="tooltip"]').each(function(i, el)
                    {
                        var $this = $(el),
                                placement = attrDefault($this, 'placement', 'top'),
                                trigger = attrDefault($this, 'trigger', 'hover'),
                                popover_class = $this.hasClass('tooltip-secondary') ? 'tooltip-secondary' : ($this.hasClass('tooltip-primary') ? 'tooltip-primary' : ($this.hasClass('tooltip-default') ? 'tooltip-default' : ''));

                        $this.tooltip({
                            placement: placement,
                            trigger: trigger
                        });

                        $this.on('shown.bs.tooltip', function(ev)
                        {
                            var $tooltip = $this.next();

                            $tooltip.addClass(popover_class);
                        });
                    });
                }

            });

            $("#cronjob_filter").submit(function(e) {
                e.preventDefault();

                $searchFilter.Status = $('#cronjob_filter [name="Status"]').val();
                $searchFilter.Title = $('#cronjob_filter [name="Title"]').val();

                data_table.fnFilter('', 0);

                return false;
            });

            setInterval(function() {

                if($("#cronjob_filter [name='AutoRefresh']").prop("checked")){
                    data_table.fnFilter('', 0);
                }
            }, 1000 * 5); // where X is your every X minutes

            // Replace Checboxes
            $(".pagination a").click(function (ev) {
                replaceCheckboxes();
            });

            $("#refreshcronjob").click(function(){
                data_table.fnFilter('', 0);
            });


            $('table tbody').on('click','.cronjob_change_status',function(ev){
                result = confirm("Are you Sure?");
                if(result){
                    status = ($(this).attr('data-status')==0)?1:0;
                    submit_ajax(baseurl+'/cronjob/'+$(this).attr('data-id') + '/change_status/' +  status );
                }
            });

            $('table tbody').on('click','.cronjob_terminate',function(ev){
                result = confirm("Are you Sure?");
                if(result){
                    status = ($(this).attr('data-status')==0)?1:0;
                    submit_ajax(baseurl+'/cronjob/'+$(this).attr('data-id') + '/terminate'  );
                }
            });

            $('table tbody').on('click','.cronjob_trigger',function(ev){
                result = confirm("Are you Sure?");
                if(result){
                    status = ($(this).attr('data-status')==0)?1:0;
                    submit_ajax(baseurl+'/cronjob/'+$(this).attr('data-id') + '/trigger'  );
                }
            });

            $('.start_crontab').click(function(ev){
                result = confirm("Are you Sure to Start Cron Tab?");
                if(result){

                    ajax_json(baseurl+'/cronjob/change_crontab_status/1','',function(response){
                        $(".btn").button('reset');
                        if (response.status == 'success') {
                            toastr.success(response.message, "Success", toastr_opts);
                            setTimeout(function(){
                                location.reload();
                            },200);
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }

                    });
                }
            });
            $('.stop_crontab').click(function(ev){
                result = confirm("Are you Sure to Stop Cron Tab?");
                if(result){
                    ajax_json(baseurl+'/cronjob/change_crontab_status/0','',function(response){
                        $(".btn").button('reset');
                        if (response.status == 'success') {
                            toastr.success(response.message, "Success", toastr_opts);
                            setTimeout(function(){
                                location.reload();
                            },200);
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }

                    });

                }
            });




        });
    </script>
    <style>
        .dataTables_filter label{
            display:none !important;
        }
        .dataTables_wrapper .export-data{
            right: 30px !important;
            display: none;
        }
        .refresh-collection{
            float: right;
            right: 30px !important;
            padding-bottom: 5px;;
        }
        #selectcheckbox{
            padding: 15px 10px;
        }

    </style>
    @stop

@section('footer_ext')
    @parent
    @include('cronjob.cronjob_edit_popup')
@stop

@stop
