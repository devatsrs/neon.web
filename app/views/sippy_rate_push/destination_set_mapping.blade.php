@extends('layout.main')
@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form id="destination_mapping_filter" method="get" class="form-horizontal form-groups-bordered validate" novalidate>
                <div class="form-group">
                    <label for="field-1" class="control-label">Account Name</label>
                    {{ Form::select('AccountID', $accounts, '', array("class"=>"select2","data-allow-clear"=>"true","data-placeholder"=>"Select Account")) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Trunk</label>
                    {{ Form::select('Trunk', $trunks, '', array("class"=>"select2","data-allow-clear"=>"true","data-placeholder"=>"Select Trunk")) }}
                </div>
                <div class="form-group">
                    <label for="field-1" class="control-label">Destination Set</label>
                    {{ Form::text('DestinationSet', '', array("class"=>"form-control")) }}
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
    <script src="<?php echo URL::to('/').'/assets/js/bootstrap-tagsinput.min.js'; ?>" ></script>

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li class="active">
            <strong>Sippy Vendor Rate Pushing</strong>
        </li>
        <li class="active">
            <strong>Destination Set Mapping</strong>
        </li>
        <li>
            <a><span>{{sippygatewaylist_dropbox($id)}}</span></a>
        </li>
    </ol>
    <h3>Destination Set Mapping</h3>

    @include('includes.errors')
    @include('includes.success')
    <form class="form-horizontal form-groups-bordered" method="post" id="destination-mapping-form" role="form">
        <div style="text-align: right;padding:10px 0 ">
            <button class="btn btn-primary btn-sm btn-icon icon-left" id="btn-save">
                <i class="entypo-floppy"></i>
                Save
            </button>
        </div>

        <table class="table table-bordered datatable" id="table-4">
            <thead>
            <tr>
                <th width="20%">Account Name</th>
                <th width="20%">Code-Rule</th>
                <th width="10%">Trunk</th>
                <th width="20%">Destination Set</th>
                <!--<th width="20%">Is Mapped</th>-->
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </form>
    <script>
        var $searchFilter 	= 	{};
        CompanyGatewayID = $('#CompanyGatewayID').val();
        var $searchFilter = {};
        $(document).ready(function() {
            $('#filter-button-toggle').show();

            var form_action = '/sippy_rate_push/updatedestinationsetlist/{{$id}}';
            $("#destination-mapping-form").submit(function () {

                var formData = new FormData($('#destination-mapping-form')[0]);

                $.ajax({
                    url: baseurl+form_action,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        $(".save.btn").button('reset');

                        if (response.status == 'success') {
                            toastr.success(response.message, "Success", toastr_opts);
                            if( typeof data_table != 'undefined' ){
                                data_table.fnFilter('', 0);
                            }
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                    },

                    // Form data
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false
                }).success(function(response){
                    if(typeof response.warning != 'undefined' && response.warning != '') {
                        toastr.warning(response.warning, "Error", toastr_opts);
                    }
                    if (typeof ajax_form_success !== 'undefined' && $.isFunction(ajax_form_success)) {
                        ajax_form_success(response);
                    }

                });
                return false;
            });


            $("#destination_mapping_filter").submit(function(e){
                e.preventDefault();
                $searchFilter.AccountID 	= 	$("#destination_mapping_filter select[name='AccountID']").val();
                $searchFilter.Trunk 		= 	$("#destination_mapping_filter [name='Trunk']").val();
                $searchFilter.DestinationSet 		= 	$("#destination_mapping_filter [name='DestinationSet']").val();
                data_table.fnFilter('', 0);
                return false;
            });


            $('#CompanyGatewayID').change(function() {
                var CompanyGatewayID = $(this).val();
                location.href = baseurl+"/sippy_rate_push/"+CompanyGatewayID+"/destinationsetmapping";
            });

            $searchFilter.Gateway = $("#CompanyGatewayID").val();

            //hide datatable warnings
            $.fn.dataTable.ext.errMode = 'none';
            $searchFilter.AccountID 		= 	$("#destination_mapping_filter select[name='AccountID']").val();
            $searchFilter.Trunk 		= 	$("#destination_mapping_filter [name='Trunk']").val();
            $searchFilter.DestinationSet 		= 	$("#destination_mapping_filter [name='DestinationSet']").val();

            data_table = $("#table-4").on( 'error.dt', function ( e, settings, techNote, message ) {
                var error_message = message.replace('DataTables warning: table id=table-4 - ','');
                toastr.error(error_message, "Error", toastr_opts);
            } ).dataTable({
                "bDestroy": true,
                "bProcessing":true,
                "bServerSide":true,
                "sAjaxSource": baseurl + "/sippy_rate_push/"+CompanyGatewayID+"/getdestinationsetlist",
                "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "fnServerParams": function(aoData) {
                    aoData.push({"name":"Gateway","value":$searchFilter.Gateway},{"name":"AccountID","value":$searchFilter.AccountID},{"name":"Trunk","value":$searchFilter.Trunk},{"name":"DestinationSet","value":$searchFilter.DestinationSet});
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push({"name":"Export","value":1},{"name":"Gateway","value":$searchFilter.Gateway},{"name":"AccountID","value":$searchFilter.AccountID},{"name":"Trunk","value":$searchFilter.Trunk},{"name":"DestinationSet","value":$searchFilter.DestinationSet});
                },
                "aaSorting": [[0, 'asc']],
                "aoColumns":
                        [
                            {},  //0 AccountName
                            {
                                "bSortable": false,
                                mRender: function(status, type, full) {
                                    html =    '<input type="text" name="code-rule[]" value="'+full[1]+'" class="tagsinput-control" />';
                                    return html
                                }
                            },  //1 code-rule
                            {},  //2 TrunkName
                            // {},  //3 DestinationSet Name
                            {
                                "bSortable": false,
                                mRender: function(status, type, full) {
                                    var action = '';

                                    action = '<div class = "hiddenRowData" >';
                                    action += '<input type = "hidden"  name = "AccountName[]" value = "' + full[0] + '" / >';
                                    action += '<input type = "hidden"  name = "destination_set_name[]" value = "' + full[3] + '" / >';
                                    action += '<input type = "hidden"  name = "SippyDestinationSetID[]" value = "' + full[4] + '" / >';
                                    action += '<input type = "hidden"  name = "CompanyGatewayID[]" value = "' + full[5] + '" / >';
                                    action += '<input type = "hidden"  name = "AccountID[]" value = "' + full[6] + '" / >';
                                    action += '<input type = "hidden"  name = "TrunkID[]" value = "' + full[7] + '" / >';
                                    action += '<input type = "hidden"  name = "i_vendor[]" value = "' + full[8] + '" / >';
                                    action += '<input type = "hidden"  name = "i_connection[]" value = "' + full[9] + '" / >';
                                    action += '<input type = "hidden"  name = "i_destination_set[]" value = "' + full[10] + '" / >';
                                    action += '</div>';

                                    action += full[3];

                                    return action;
                                }
                            }, //0 if destination set is mapped in our database
                        ],
                "oTableTools": {
                    "aButtons": [

                    ]
                },
                "fnDrawCallback": function() {
                    $(".tagsinput-control").tagsinput('items');

                    //onDelete Click
                    $(".btn.delete").click(function (e) {
                        e.preventDefault();
                        var id = $(this).attr('data-id');
                        var url = baseurl + '/gateway/'+id+'/ajax_existing_gateway_cronjob';
                        $('#delete-gateway-form [name="CompanyGatewayID"]').val(id);
                        if(confirm('Are you sure you want to delete selected gateway? All related data like CDR, summary etc will also delete.')) {
                            $.ajax({
                                url: url,
                                type: 'POST',
                                dataType: 'html',
                                success: function (response) {
                                    $(".btn.delete").button('reset');
                                    if (response) {
                                        $('#modal-delete-gateway .container').html(response);
                                        $('#modal-delete-gateway').modal('show');
                                    }else{
                                        $('#delete-gateway-form').submit();
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

                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
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
        }
        .bootstrap-tagsinput {
            cursor: text;
        }
    </style>
    <!--Only for Delete operation-->
@stop


@section('footer_ext')
    @parent

@stop
