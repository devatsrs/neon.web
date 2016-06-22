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

    <table class="table table-bordered datatable" id="cronjobs">
        <thead>
        <tr>
            <th width="20%">Title</th>
            <th width="20%">PID</th>
            <th width="20%">Since Process Running</th>
            <th width="20%">Last Run Time</th>
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

            //show_loading_bar(40);
            data_table_cronjob = $("#cronjobs").dataTable({
                "bDestroy": true,
                "bProcessing":true,
                "bServerSide":true,
                "sAjaxSource": baseurl + "/cronjobs/activecronjob_ajax_datagrid",
                "iDisplayLength": '{{Config::get('app.pageSize')}}',
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "fnServerParams": function(aoData) {
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push({"name":"Export","value":1});
                },
                "aaSorting": [[0, 'asc']],
                "aoColumns":
                        [
                            {  "bSortable": true },//0 title
                            {  "bSortable": true },  //1   Pid
                            {  "bSortable": true },  //2   Running Hour
                            {  "bSortable": true },  //3   Last Run Time
                            {                       //4
                                "bSortable": false,
                                mRender: function ( CronJobID, type, full ) {

                                    var action ='';

                                    action = '<div class = "hiddenRowData" >';
                                    action += '<input type = "hidden"  name = "JobTitle" value = "' + (full[0] !== null ?full[0]:'') + '" / >';
                                    action += '<input type = "hidden"  name = "PID" value = "' + (full[1] !== null ?full[1]:'') + '" / >';
                                    action += '<input type = "hidden"  name = "RunningHour" value = "' + (full[2] !== null ?full[2]:'')+ '" / >';
                                    action += '<input type = "hidden"  name = "LastRunTime" value = "' + (full[2] !== null ?full[2]:'')+ '" / >';
                                    action += '<input type = "hidden"  name = "CronJobID" value = "' + CronJobID + '" / >';
                                    action += ' <a data-id="'+ CronJobID +'" data-pid="'+(full[1] !== null ?full[1]:'')+'" class="delete-config btn delete btn-danger btn-sm btn-icon icon-left"><i class="entypo-cancel"></i>Terminate</a>';

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
    </style>
    @stop

    @section('footer_ext')
    @parent
@stop
