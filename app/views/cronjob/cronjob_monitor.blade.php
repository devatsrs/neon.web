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
            <a href="{{URL::to('cronjobs')}}">Cron Job</a>
        </li>
        <li class="active">
            <strong>Cron Job Monitor</strong>
        </li>
    </ol>
    <h3>Cron Job Monitor</h3>


    <div class="row">
        <div class="col-md-12">
            <form id="account_filter" method=""  action="" class="form-horizontal form-groups-bordered validate" novalidate>
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
                                <input class="form-control" name="search"  type="text" >
                            </div>
                            <label for="field-1" class="col-sm-1 control-label">Status</label>
                            <div class="col-sm-2">

                                 {{ Form::select('Active', [""=>"Both",CronJob::ACTIVE=>"Active",CronJob::INACTIVE=>"Inactive"], CronJob::ACTIVE, array("class"=>"form-control selectboxit")) }}


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



    <div class="clear-fix clear"><br><br></div>


    <table class="table table-bordered datatable" id="cronjobs">
        <thead>
        <tr>
            <th width="20%"></th>
            <th width="20%">PID</th>
            <th width="20%">Title</th>
            <th width="20%"></th>
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
            var list_fields  = ['Active','PID','JobTitle','RunningTime','CronJobID','LastRunTime','Status',"CronJobCommandID"];
            var $searchFilter = {};

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
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push({"name":"Export","value":1});
                },
                "aaSorting": [[0, 'desc']],
                "aoColumns":
                        [
                            {  "bSortable": true,

                                mRender: function ( Active, type, full ) {
                                    var action ='';
                                    var CronJobID = full[4];
                                    if(Active==0){
                                        action += ' <button data-id="'+ CronJobID +'" class="cronjob_trigger btn btn-green btn-sm" type="button" title="Manually Execute" data-placement="top" data-toggle="tooltip"><i class="entypo-play"></i></button>';
                                    } else {
                                        action += ' <button data-id="'+ CronJobID +'" class="cronjob_terminate btn btn-red btn-sm" type="button" title="Terminate" data-placement="top" data-toggle="tooltip"><i class="entypo-pause" ></i></button>';
                                    }
                                    return action;
                                }

                            },//0 Pid
                            {  "bSortable": true },//1 Pid
                            {  "bSortable": true },  //2   Title
                            {  "bSortable": true,

                                mRender: function ( RunningTime, type, full ) {
                                    var PID =  full[1];
                                    if(PID > 0){
                                        return RunningTime;
                                    }

                                }

                            },  //3   Running Hour
                            {                       //4
                                "bSortable": false,
                                mRender: function ( CronJobID, type, full ) {

                                    var action ='';

                                    action = '<div class = "hiddenRowData" >';
                                    for(var i = 0 ; i< list_fields.length; i++){
                                        action += '<input type = "hidden"  name = "' + list_fields[i] + '" value = "' + (full[i] != null?full[i]:'')+ '" / >';
                                    }
                                    action += '<div id="cron_set" style="display: none" >' + (full[7] !== null ? full[7] : '') + '</div>'
                                    action += '</div>';



                                    var Status = full[6];

                                    if(Status==1) {
                                        action += ' <button data-id="'+ CronJobID +'" data-status="'+Status+'" class="cronjob_change_status btn btn-red btn-sm" type="button" title="Stop" data-placement="left" data-toggle="tooltip"><i class="entypo-stop" ></i></button>';
                                    }else {
                                        action += ' <button data-id="' + CronJobID + '" data-status="'+Status+'" class="cronjob_change_status btn btn-green btn-sm" type="button" title="Enable" data-placement="left" data-toggle="tooltip"><i class="entypo-check"></i></button>';
                                    }

                                    <?php if(User::checkCategoryPermission('CronJob','Edit') ){ ?>
                                            action += ' <a data-id="' + CronJobID + '" class="edit-config btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit </a>';
                                    <?php } ?>

                                    var history_url = baseurl + "/cronjobs/history/" + CronJobID;

                                    action += ' <a target="_blank" href="'+ history_url +'" class=" btn btn-default btn-sm btn-icon icon-left"><i class="entypo-back-in-time"></i>History </a>';

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
                    auto_refresh = true;
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

            setInterval(function() {
                if(auto_refresh == true){

                    auto_refresh = false;
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
