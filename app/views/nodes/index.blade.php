@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form id="node_filter" class="form-horizontal form-groups-bordered validate" novalidate>
                <div class="form-group">
                    <label for="field-1" class="control-label">Name</label>
                    {{ Form::text('ServerName', '', array("class"=>"form-control")) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Server Ip</label>
                    {{ Form::text('ServerIP', '', array("class"=>"form-control")) }}
                </div>
                {{--<div class="form-group">--}}
                    {{--<label for="field-1" class="control-label">Currency</label>--}}
                    {{--{{ Form::select('CurrencyId', Currency::getCurrencyDropdownIDList(),'', array("class"=>"select2 small")) }}--}}
                    {{--<input id="PackageRefresh" type="hidden" value="1">--}}
                    {{--<input id="editRateTableId" type="hidden" value="">--}}
                {{--</div>--}}
                <div class="form-group">
                    <label class="control-label">Status</label><br/>
                    <p class="make-switch switch-small">
                        <input name="Status" type="checkbox" value="" checked="checked">
                    </p>
                </div>
                <div class="form-group">
                    <br/>
                    <button type="submit" class="btn btn-primary btn-md btn-icon icon-left">
                        <i class="entypo-search"></i>
                        Search
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop


@section('content')

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li class="active">
            <strong>Nodes</strong>
        </li>
    </ol>
    <h3>Nodes</h3>
    <p class="text-right">
        <a href="#" data-action="showAddModal" data-type="package" data-modal="add-new-modal-node" class="btn btn-primary">
            <i class="entypo-plus"></i>
            Add New Node
        </a>
    </p>

    <table class="table table-bordered datatable" id="table-4">
        <thead>
        <tr>
            <th>Server Name</th>
            <th>Server Ip</th>
            {{--<th>Currency</th>--}}
            <th>Status</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>


        </tbody>
    </table>

    <script type="text/javascript">
        var checkBoxArray =[];
        var $searchFilter = {};
        jQuery(document).ready(function ($) {
            $('#filter-button-toggle').show();

            $searchFilter.ServerName = $("#node_filter [name='ServerName']").val();
            $searchFilter.ServerIP = $("#node_filter [name='ServerIP']").val();
            $searchFilter.Status = $("#node_filter [name='Status']").prop("checked");
           
            
            data_table = $("#table-4").dataTable({

                "bProcessing":true,
                "bServerSide":true,
                "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "sAjaxSource": baseurl + "/node/ajax_datagrid",
                "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                "sPaginationType": "bootstrap",
                "aaSorting"   : [[5, 'desc']],
                "fnServerParams": function(aoData) {
                    aoData.push({"name":"ServerName","value":$searchFilter.ServerName});
                    aoData.push({"name":"ServerIP","value":$searchFilter.ServerIP});
                    aoData.push({"name":"status","value":$searchFilter.Status});
                    data_table_extra_params.length = 0;
                   
                    data_table_extra_params.push({"name":"ServerName","value":$searchFilter.ServerName},{ "name": "Export", "value": 1});
                    data_table_extra_params.push({"name":"ServerIP","value":$searchFilter.ServerIP},{ "name": "Export", "value": 1});
                    data_table_extra_params.push({"name":"status","value":$searchFilter.Status},{ "name": "Export", "value": 1});
                },
                "aoColumns":
                        [
                            { "bSortable": true }, //ServerName
                            { "bSortable": true }, //ServerIP
                            {
                                "bSortable": false,
                                mRender: function (id, type, full) {

                                    var output = full[2] ;
                                        if(output==1){
                                            action='<i class="entypo-check" style="font-size:22px;color:green"></i>';
                                        }else{
                                            action='<i class="entypo-cancel" style="font-size:22px;color:red"></i>';
                                        }
                                        return action;
                                    }


                            },
                            {
                                "bSortable": true,
                                mRender: function ( id, type, full ) {
                                    var action , edit_ , show_, delete_ ;
                                    action = '<div class = "hiddenRowData" >';
                                    action += '<input type = "hidden"  name ="ServerID" value= "' + (full[3] != null ? full[3] : '') + '" / >';
                                    action += '<input type = "hidden"  name ="ServerName" value= "' + (full[0] != null ? full[0] : '') + '" / >';
                                    action += '<input type = "hidden"  name ="ServerIP" value= "' + (full[1] != null ? full[1] : '') + '" / >';
                                    action += '<input type = "hidden"  name ="status" value= "' + (full[2] != null ? full[2] : '') + '" / >';
                                    action += '</div>';
                                    action += ' <a data-name = "'+full[0]+'" data-id="'+ full[3] +'" title="Edit" class="edit-package btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>';
                                    action += ' <a data-id="'+ full[3] +'" title="Delete" class="delete-package btn btn-danger btn-sm"><i class="entypo-trash"></i></a>';
                                    return action;
                                }
                            }
                        ],
                "oTableTools":
                {
                    "aButtons": [
                        {
                            "sExtends": "download",
                            "sButtonText": "EXCEL",
                            "sUrl": baseurl + "/node/exports/xlsx",
                            sButtonClass: "save-collection btn-sm"
                        },
                        {
                            "sExtends": "download",
                            "sButtonText": "CSV",
                            "sUrl": baseurl + "/node/exports/csv",
                            sButtonClass: "save-collection btn-sm"
                        }
                    ]
                },
                "fnDrawCallback": function() {
                    $(".delete-package.btn").click(function(ev) {
                        response = confirm('Are you sure?');
                        if (response) {
                            var clear_url;
                            var id  = $(this).attr("data-id");
                            clear_url = baseurl + "/node/delete/"+id;
                            $(this).button('loading');
                            //get
                            $.get(clear_url, function (response) {
                                if (response.status == 'success') {
                                    $(this).button('reset');
                                    toastr.success(response.message, "Success", toastr_opts);
                                } else {
                                    toastr.error(response.message, "Error", toastr_opts);
                                }
                                data_table.fnFilter('', 0);
                            });
                        }
                        return false;


                    });
                }
            });
            

            $("#node_filter").submit(function(e) {
                e.preventDefault();

                $searchFilter.ServerName = $("#node_filter [name='ServerName']").val();
                $searchFilter.ServerIP = $("#node_filter [name='ServerIP']").val();
                $searchFilter.Status = $("#node_filter [name='Status']").prop("checked");

                data_table.fnFilter('', 0);
                return false;
            });

            $(".dataTables_wrapper select").select2({
                minimumResultsForSearch: -1
            });
      
            $('table tbody').on('click','.edit-package',function(ev){
                ev.preventDefault();
                ev.stopPropagation();
                $('#add-new-node-form').trigger("reset");

                ServerID = $(this).prev("div.hiddenRowData").find("input[name='ServerID']").val();
                ServerName = $(this).prev("div.hiddenRowData").find("input[name='ServerName']").val();
                ServerIP = $(this).prev("div.hiddenRowData").find("input[name='ServerIP']").val();
                Status  = $(this).prev("div.hiddenRowData").find("input[name='status']").val();

                $("#add-new-node-form [name='ServerID']").val(ServerID);
                $("#add-new-node-form [name='ServerName']").val(ServerName);
                $("#add-new-node-form [name='ServerIP']").val(ServerIP);
                $("#add-new-node-form [name='status']").val(Status).prop('checked', Status == 1);
                $('#add-new-modal-node h4').html('Edit Node');
               
                $('#add-new-modal-node').modal('show');
            });

       });

    </script>
    @include('nodes.nodemodal')
@stop