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
                                {{Form::select('status',["0"=>"Running",CronJob::CRON_FAIL=>"Failed"],Account::VERIFIED,array("class"=>"selectboxit"))}}

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
        jQuery(document).ready(function ($) {
            public_vars.$body = $("body");
            var list_fields  = ['PID','Title','RunningTime','CronJobID','LastRunTime'];
            var $searchFilter = {};

            data_table_cronjob = $("#cronjobs").dataTable({
                "bDestroy": true,
                "bProcessing":true,
                "bServerSide":true,
                //"bPaginate": false,
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
                            {  "bSortable": true },//0 Pid
                            {  "bSortable": true },  //1   Title
                            {  "bSortable": true,

                                mRender: function ( RunningTime, type, full ) {
                                    var PID =  full[0];
                                    if(PID > 0){
                                        return RunningTime;
                                    }

                                }

                            },  //2   Running Hour
                            {                       //3
                                "bSortable": false,
                                mRender: function ( CronJobID, type, full ) {

                                    var action ='';

                                    action = '<div class = "hiddenRowData" >';
                                    for(var i = 0 ; i< list_fields.length; i++){
                                        action += '<input type = "hidden"  name = "' + list_fields[i] + '" value = "' + (full[i] != null?full[i]:'')+ '" / >';
                                    }

                                    var PID = full[0];
                                    if(PID > 0 ){
                                        action += ' <a data-id="'+ CronJobID +'" data-pid="'+PID+'" class="delete-config btn delete btn-danger btn-sm btn-icon icon-left"><i class="entypo-cancel"></i>Kill</a>';
                                    }else {
                                        action += ' <a data-id="'+ CronJobID +'" class="btn start btn-success btn-sm btn-icon icon-left"><i class="entypo-check"></i>Start</a>';
                                    }

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
                }

            });

            setInterval(function() {
                data_table_cronjob.fnFilter('', 0);
            }, 1000 * 5); // where X is your every X minutes

            // Replace Checboxes
            $(".pagination a").click(function (ev) {
                replaceCheckboxes();
            });

            $("#refreshcronjob").click(function(){
                data_table_cronjob.fnFilter('', 0);
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
@stop
