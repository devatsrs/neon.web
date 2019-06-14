@extends('layout.main')

@section('filter')

    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form novalidate class="form-horizontal form-groups-bordered validate" method="post" id="ratetable_filter">
                {{--<div class="form-group">
                    <label for="Search" class="control-label">Search</label>
                    <input class="form-control" name="Search" id="Search"  type="text" >
                </div>--}}
                <div class="form-group">
                    <label class="control-label" for="field-1">Apply To</label>
                    {{ Form::select('level', ["S"=>"Service","A"=>"Account",], 'S', array("class"=>"select2 level","data-type"=>"level")) }}
                </div>

                <div class="form-group hidden S">
                    <label class="control-label" for="field-1">Service</label>
                    {{ Form::select('services', $allservice, '', array("class"=>"select2","data-type"=>"service")) }}
                </div>
                <div class="form-group T">
                    <label class="control-label" for="field-1">Trunk</label>
                    {{ Form::select('TrunkID', $trunks, '', array("class"=>"select2","data-type"=>"trunk")) }}
                </div>
                
                <div class="form-group A">
                    <label for="field-1" class="control-label">Account</label>
                    {{Form::select('SourceCustomers[]', $all_customers, array() ,array("class"=>"form-control select2",'multiple','id'=>"Customerlist"))}}
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
        <li>

            <a href="{{URL::to('routingprofiles')}}">Routing Profiles</a>
        </li>
        <li class="active">
            <strong>Assign Routing Profile</strong>
        </li>
    </ol>
    <h3>Assign Routing Profile</h3>
<p style="text-align: right;">
            <a href="#" id="add-new-rate-table" class="btn btn-primary" title="Assign Routing Profile">
                Assign Profile
            </a>

    </p>
    <br>
    <div class="cler row">
        <div class="col-md-12">
            <form role="form" id="form1" method="post" class="form-horizontal form-groups-bordered validate" novalidate>
                <div class="form-group">
                    <div class="col-md-12">
                        {{-- Service Level --}}
                        <table class="table table-bordered datatable" id="table-service">
                            <thead>
                            <tr>
                                <th><input type="checkbox" class="table-service_selectall" id="table-service_selectall" name="service_selectall[]" /></th>
                                <th>Account Name</th>
                                <th>Routing Profile</th>
                                <th id="servicenametd">Service Name</th>
                                <th >Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                        
                        {{-- Trunk Level--}}
                        <table class="table table-bordered datatable" id="table-trunk">
                            <thead>
                            <tr>
                                <th><input type="checkbox" class="table-trunk_selectall" id="table-trunk_selectall" name="trunk_selectall[]" /></th>
                                <th>Account Name</th>
                                <th>Routing Profile</th>
                                <th>Trunk</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                        
                        {{-- Account Level--}}
                        <table class="table table-bordered datatable hidden" id="table-account" >
                            <thead>
                            <tr>
                                <th><input type="checkbox" class="table-account_selectall" id="table-account_selectall" name="account_selectall[]" /></th>
                                <th>Account Name</th>
                                <th>Routing Profile</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                        
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="modal-add-new-rate-table">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="add-new-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Bulk Apply Routing Profile</h4>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="selected_customer">
                        <input type="hidden" name="selected_trunk">
                        <input type="hidden" name="selected_service">
                        <input type="hidden" name="selected_account_service">

                        <input type="hidden" name="selected_level">
                        <div class="allpage"><input type="hidden" name="chk_allpageschecked" value="N" ></div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group ">
                                    <label for="field-5" class="control-label">Routing Profile</label>
                                    {{Form::select('RoutingProfile', $routingprofile, '0',array("class"=>"form-control select2","id"=>"RoutingProfile"))}}
                                </div>
                            </div>

                        </div>
                        
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="codedeck-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                            <i class="entypo-floppy"></i>
                            Apply
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
    <script type="text/javascript">
        var checked = '';
        jQuery(document).ready(function($) {

            var levellbl = $("#ratetable_filter select[name='level']").val();
            var levelhidden =  levellbl=='T'?'S':'T';
            $('.'+levellbl).removeClass('hidden');
            $('.'+levelhidden).addClass('hidden');
            $("#table-service").addClass('hidden');

            $('#filter-button-toggle').show();

            $("#ratetable_filter").submit(function(e) {

                var $searchFilter = {};
                $searchFilter.TrunkID = $("#ratetable_filter [name='TrunkID']").val();
                $searchFilter.RateTableId = $("#ratetable_filter [name='RateTableId']").val();
                $searchFilter.SourceCustomers = $("#ratetable_filter select[name='SourceCustomers[]']").val();
                $searchFilter.services = $('#ratetable_filter [name="services"]').val();
                $searchFilter.level = $('#ratetable_filter [name="level"]').val();
                $searchFilter.Currency = $("#ratetable_filter select[name='Currency']").val();
                if(jQuery.isEmptyObject($searchFilter.SourceCustomers) == false){
                    $searchFilter.SourceCustomers = $searchFilter.SourceCustomers.join(",");}

                if($searchFilter.level =='T'){
                    var checknoxid = 'trunk_selectcheckbox';
                    var tableid = 'table-trunk';
                    $("#table-service").addClass('hidden');$("#table-account").addClass('hidden');
                    $("#table-trunk").removeClass('hidden');
                    
                    $("#table-trunk_wrapper").removeClass('hidden');
                    $("#table-service_wrapper").addClass('hidden');
                    $("#table-account_wrapper").addClass('hidden');
                    aoColumns = [
                        {"bSortable": false, //RateID
                            mRender: function(id, type, full) {
                                var account_trunk = full[0]+'_'+full[4];
                                return '<div class="checkbox "><input type="checkbox" name="customer[]" value="' + account_trunk + '" class="rowcheckbox" ></div>';
                            }
                        },
                        {"bSortable": true},
                        {"bSortable": true},
                        {"bSortable": true},
                        {
                            mRender: function(id, type, full) {
                                var action;
                                var account_trunk = full[0]+'_'+full[4];
                                action = '<a title="Edit" data-id="'+  account_trunk +'" data-RoutingProfileID="'+full[5]+'" data-rateTableName="'+full[4]+'" data-TrunkID="'+full[5]+'"  class="edit-ratetable btn btn-default btn-sm"><i class="entypo-pencil"></i></a>&nbsp;';
                                return action;
                            }
                        },
                    ]
                }else if($searchFilter.level =='A'){
                
                    var checknoxid = 'account_selectcheckbox';
                    var tableid = 'table-account';
                    
                    $("#table-account").removeClass('hidden');
                    $("#table-service").addClass('hidden');
                    $("#table-trunk").addClass('hidden');
                    
                    $("#table-account_wrapper").removeClass('hidden');
                    $("#table-trunk_wrapper").addClass('hidden');
                    $("#table-service_wrapper").addClass('hidden');

                    aoColumns = [
                        {"bSortable": false,
                            mRender: function(id, type, full) {
                                var account_service = full[0]+'_'+full[5];
                                return '<div class="checkbox "><input type="checkbox" name="customer[]" value="' + account_service + '" class="rowcheckbox" ></div>';
                            }
                        },
                        {"bSortable": true},
                        {"bSortable": true},
                        {
                            mRender: function(id, type, full) {
                                var action;
                                var account_service = full[0]+'_'+full[5];
                                action = '<a title="Edit" data-id="'+ account_service +'" data-RoutingProfileID="'+full[3]+'" data-OutboundRatetable="'+full[8]+'" data-inboundRatetable="'+full[7]+'" data-serviceId="'+full[5]+'" class="edit-ratetable btn btn-default btn-sm"><i class="entypo-pencil"></i></a>&nbsp;';
                                return action;
                            }
                        },
                    ]
                }else{
                    var checknoxid = 'service_selectcheckbox';
                    var tableid = 'table-service';
                    $("#table-service").removeClass('hidden');
                    $("#table-trunk").addClass('hidden');
                    $("#table-account").addClass('hidden');
                    
                    $("#table-trunk_wrapper").addClass('hidden');$("#table-account_wrapper").addClass('hidden');
                    $("#table-service_wrapper").removeClass('hidden');

                    aoColumns = [
                        {"bSortable": false,
                            mRender: function(id, type, full) {
                                var account_service = full[0]+'_'+full[4]+'_'+full[6];
                                return '<div class="checkbox "><input type="checkbox" name="customer[]" value="' + account_service + '" class="rowcheckbox" ></div>';
                            }
                        },
                        {"bSortable": true},
                        {"bSortable": true},
                        {"bSortable": true,
                            mRender: function(id, type, full) {
                                return full[7] == undefined || full[7] == null ? full[3] : full[3]+' '+full[7];
                            }
                        },
                        {
                            mRender: function(id, type, full) {
                                var action;
                                var account_service = full[0]+'_'+full[4]+'_'+full[6];
                                action = '<a title="Edit" data-id="'+ account_service +'" data-OutboundRatetable="'+full[8]+'" data-inboundRatetable="'+full[7]+'" data-serviceId="'+full[5]+'" data-RoutingProfileID="'+full[5]+'" data-AccountServiceID="'+full[6]+'" class="edit-ratetable btn btn-default btn-sm"><i class="entypo-pencil"></i></a>&nbsp;';
                                return action;
                            }
                        },
                    ]
                }


                data_table = $("#"+tableid).dataTable({
                    "bDestroy": true,
                    "bProcessing": true,
                    "bServerSide": true,
                    "sAjaxSource": baseurl + "/assignrouting/ajax_datagrid/type",
                    "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                    "sPaginationType": "bootstrap",
                    "sDom": "<'row'<'col-xs-6 col-left'<'#"+checknoxid+".col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                    "oTableTools": {},
                    "aaSorting": [[1, "asc"]],
                    "fnServerParams": function(aoData) {
                        aoData.push(
                                {"name":"TrunkID","value":$searchFilter.TrunkID},
                                {"name":"Currency","value":$searchFilter.Currency},
                                {"name":"RateTableId","value":$searchFilter.RateTableId},
                                {"name":"SourceCustomers","value":$searchFilter.SourceCustomers},
                                {"name":"level","value":$searchFilter.level},
                                {"name":"services","value":$searchFilter.services},
                                {"name":"Search","value":$searchFilter.Search});
                        data_table_extra_params.length = 0;
                        data_table_extra_params.push(
                                {"name":"TrunkID","value":$searchFilter.TrunkID},
                                {"name":"Currency","value":$searchFilter.Currency},
                                {"name":"RateTableId","value":$searchFilter.RateTableId},
                                {"name":"SourceCustomers","value":$searchFilter.SourceCustomers},
                                {"name":"level","value":$searchFilter.level},
                                {"name":"services","value":$searchFilter.services},
                                {"name":"Search","value":$searchFilter.Search},
                                {"name":"Export","value":1});
                    },

                    "aoColumns":aoColumns ,
                    "oTableTools":
                    {
                        "aButtons": [
                            {
                                "sExtends": "download",
                                "sButtonText": "EXCEL",
                                "sUrl": baseurl + "/assignrouting/exports/xlsx",
                                sButtonClass: "save-collection btn-sm"
                            },
                            {
                                "sExtends": "download",
                                "sButtonText": "CSV",
                                "sUrl": baseurl + "/assignrouting/exports/csv",
                                sButtonClass: "save-collection btn-sm"
                            }
                        ]
                    },
                    "fnDrawCallback": function() {
                    console.log(tableid);
                        var table_select_all = tableid+'_selectall';
                        $('#'+tableid +' tbody tr').each(function (i, el) {
                            if (checked != '') {

                                $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                $(this).addClass('selected');
                                $('.selectallbutton').prop("checked", true);
                                $("#"+table_select_all).prop("checked", true).prop('disabled', true);

                            } else {

                                $("#"+table_select_all).prop("checked", false).prop('disabled', false);
                                $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                                $(this).removeClass('selected');

                            }
                        });

                      

                        $('.selectallbutton').click(function (ev) {
                            if ($(this).is(':checked')) {
                                checked = 'checked=checked disabled';
                                
                                $("#"+table_select_all).prop("checked", true).prop('disabled', true);
                                $('#'+tableid +' tbody tr').each(function (i, el) {
                                    $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                    $(this).addClass('selected');
                                });

                                $(".allpage").html('<input type="hidden" name="chk_Currency" value=' + $searchFilter.Currency + ' >' +
                                        '<input type="hidden" name="chk_RateTableId" value=' + $searchFilter.RateTableId + ' >' +
                                        '<input type="hidden" name="chk_SourceCustomers" value=' + $searchFilter.SourceCustomers + ' >' +
                                        '<input type="hidden" name="chk_Trunkid" value=' + $searchFilter.TrunkID + ' >' +
                                        '<input type="hidden" name="chk_services" value=' + $searchFilter.services + ' >' +
                                        '<input type="hidden" name="chk_allpageschecked" value="Y" >');

                            } else {
                                checked = '';
                                $("#"+table_select_all).prop("checked", false).prop('disabled', false);
                                $('#'+tableid +' tbody tr').each(function (i, el) {
                                    $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                                    $(this).removeClass('selected');
                                });
                                $("input[name='chk_allpageschecked']").val('N');

                            }
                        });

                        $(".dataTables_wrapper select").select2({
                            minimumResultsForSearch: -1
                        });


                    }
                });
                $("#"+checknoxid).append('<input type="checkbox" class="selectallbutton" name="checkboxselect[]" class="" title="Select All Found Records" />');
                return false;

            });


            //Select Row on click
            $('#table-trunk tbody').on('click', 'tr', function () {
                if (checked == '') {
                    $(this).toggleClass('selected');
                    if ($(this).hasClass("selected")) {
                        $(this).find('.rowcheckbox').prop("checked", true);
                    } else {
                        $(this).find('.rowcheckbox').prop("checked", false);
                    }
                }
            });
            //Select Row on click
            $('#table-service tbody').on('click', 'tr', function () {
                $(this).toggleClass('selected');
                if ($(this).hasClass("selected")) {
                    $(this).find('.rowcheckbox').prop("checked", true);
                } else {
                    $(this).find('.rowcheckbox').prop("checked", false);
                }
            });

            // Select all by table-service
            $("#table-service_selectall").click(function (ev) {
                var is_checked = $(this).is(':checked');
                $('#table-service tbody tr').each(function (i, el) {
                    if (is_checked) {
                        $(this).find('.rowcheckbox').prop("checked", true);
                        $(this).addClass('selected');
                    } else {
                        $(this).find('.rowcheckbox').prop("checked", false);
                        $(this).removeClass('selected');
                    }
                });
            });

            // Select all by table-trunk
            $("#table-trunk_selectall").click(function (ev) {
                var is_checked = $(this).is(':checked');
                $('#table-trunk tbody tr').each(function (i, el) {
                    if (is_checked) {
                        $(this).find('.rowcheckbox').prop("checked", true);
                        $(this).addClass('selected');
                    } else {
                        $(this).find('.rowcheckbox').prop("checked", false);
                        $(this).removeClass('selected');
                    }
                });
            });

            // Select all by table-trunk
            $("#table-account_selectall").click(function (ev) {
                var is_checked = $(this).is(':checked');
                $('#table-account tbody tr').each(function (i, el) {
                    if (is_checked) {
                        $(this).find('.rowcheckbox').prop("checked", true);
                        $(this).addClass('selected');
                    } else {
                        $(this).find('.rowcheckbox').prop("checked", false);
                        $(this).removeClass('selected');
                    }
                });
            });


            $('table tbody').on('click','.edit-ratetable',function(ev){
console.log($(this).attr('data-RoutingProfileID'));
                $("#add-new-form").trigger("reset");
                ev.preventDefault();
                ev.stopPropagation();
                $('#modal-add-new-rate-table').trigger("reset");

                //Select the selected routing profile
                $("#add-new-form [id='RoutingProfile']").select2().select2('val', $(this).attr('data-RoutingProfileID'));
                //----------
                /*$('#ServiceID').select2('disable');
                $("#modal-add-new-rate-table [name='AccountServiceId']").val($(this).attr('data-serviceId'));*/

                /* For Service Level */
                $("#modal-add-new-rate-table [name='selected_customer']").val($(this).attr('data-id'));
                var dataInBound = $(this).attr('data-inboundRatetable')!= 'null' ?  $(this).attr('data-inboundRatetable') : '';

                $("#modal-add-new-rate-table [name='InboundRateTable']").select2('val', dataInBound);
                var dataOutBound = $(this).attr('data-OutboundRatetable')!= 'null' ?  $(this).attr('data-OutboundRatetable') : '';
                $("#modal-add-new-rate-table [name='OutboundRateTable']").select2('val', dataOutBound);
                $("#modal-add-new-rate-table [name='ServiceID']").select2('val', $(this).attr('data-serviceId'));
                $("#modal-add-new-rate-table [name='selected_account_service']").val($(this).attr('data-AccountServiceID'));

                /* For Trunk Level */
                var RateTblId = $(this).attr('data-rateTableName')!= 'null' ?  $(this).attr('data-rateTableName') : '';
                var trunkid = $(this).attr('data-trunkid')!= 'null' ?  $(this).attr('data-trunkid') : '';
                $("#modal-add-new-rate-table [name='TrunkID']").select2('val', trunkid);
                $("#modal-add-new-rate-table [name='RateTable_Id']").select2('val', RateTblId);

                var level = $("#ratetable_filter select[name='level']").val();
                $("input[name='selected_level']").val(level);

                $('#modal-add-new-rate-table').modal('show');

            });

            $("#add-new-rate-table").click(function(ev) {
console.log('---ppppp');
                ev.preventDefault();

                $("#modal-add-new-rate-table [name='InboundRateTable']").select2('val', '');
                $("#modal-add-new-rate-table [name='OutboundRateTable']").select2('val', '');
                $("#modal-add-new-rate-table [name='ServiceID']").select2('val', '');
                $("#modal-add-new-rate-table [name='selected_account_service']").val('');

                $("input[name='selected_trunk']").val($("#ratetable_filter [name='TrunkID']").val());
                $("input[name='selected_service']").val($('#ratetable_filter [name="services"]').val());
                
                /*$('#ServiceID').select2('enable');
                $("#modal-add-new-rate-table [name='AccountServiceId']").val('');*/
                $('#modal-add-new-rate-table').modal('show', {backdrop: 'static'});
                /* Get selected Customer */
                var favorite = [];
                $.each($("input[name='customer[]']:checked"), function(){
                    favorite.push($(this).val());
                });
                $.unique(favorite);
                $("input[name='selected_customer']").val(favorite.join(","));
                var level = $("#ratetable_filter select[name='level']").val();
                $("input[name='selected_level']").val(level);
                /* Get selected Customer */


            });

            $("#add-new-form").submit(function(ev){
                ev.preventDefault();
                update_new_url = baseurl + '/assignrouting/store';
                submit_ajax(update_new_url,$("#add-new-form").serialize());
            });

            $(".level").click(function(){


                var levellbl = $("#ratetable_filter select[name='level']").val();
                if(levellbl=='A'){
                    $('.'+levellbl).removeClass('hidden');
                    $('.T').addClass('hidden');
                    $('.S').addClass('hidden');
                }else{
                    var levelhidden =  levellbl=='T'?'S':'T';
                    $('.'+levellbl).removeClass('hidden');
                    $('.'+levelhidden).addClass('hidden');
                }
                if(  $('.table-trunk_selectall').prop("checked") == false || $('.table-service_selectall').prop("checked") == false );
                {
                    $(".rowcheckbox").prop('checked', false);
                }

            });



        });


    </script>
@stop