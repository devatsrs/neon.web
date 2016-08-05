@extends('layout.main')
@section('content')
    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li>
            <a href="{{URL::to('destination_group_set')}}">Destination Group Set</a>
        </li>
        <li>
            <a href="{{URL::to('destination_group_set/show/'.$DestinationGroupSetID)}}">Destination Group ({{$groupname}})</a>
        </li>
        <li class="active">
            <strong>Destination Group Code ({{$name}})</strong>
        </li>
    </ol>
    <h3>Destination Group Code</h3>
    <p style="text-align: right;">
        @if(User::checkCategoryPermission('DestinationGroup','Edit'))
        <button  id="add-button" class=" btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."><i class="fa fa-floppy-o"></i>Save</button>
        @endif
        <a href="{{URL::to('/destination_group_set/show/'.$DestinationGroupSetID)}}" class="btn btn-danger btn-sm btn-icon icon-left">
            <i class="entypo-cancel"></i>
            Close
        </a>
    </p>
    @include('includes.errors')
    @include('includes.success')
    <div class="row">
        <div class="col-md-12">
            <form id="table_filter" method=""  action="" class="form-horizontal form-groups-bordered validate" novalidate>
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
                            <label for=" field-1" class="col-sm-1 control-label">
                                Country
                            </label>
                            <div class="col-sm-2">
                                {{Form::select('CountryID', $countries,'',array("class"=>"form-control select2"))}}
                            </div>
                            <label class="col-sm-1 control-label">Code</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="FilterCode">
                            </div>
                            <label class="col-sm-1 control-label">Description</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" name="FilterDescription">
                            </div>
                        </div>
                        <input type="hidden" name="DestinationGroupID" value="{{$DestinationGroupID}}" >
                        <input type="hidden" name="DestinationGroupSetID" value="{{$DestinationGroupSetID}}">
                        <p style="text-align: right;">
                            <button type="submit" id="search_code" class="btn btn-primary btn-sm btn-icon icon-left" style="visibility: visible;">
                                <i class="entypo-search"></i>
                                Search
                            </button>
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <form id="modal-form">
    <table id="table-extra" class="table table-bordered datatable">
        <thead>
        <th width="10%">
            <div class="checkbox">
                <input type="checkbox" name="RateID[]" class="selectall" id="selectall">
            </div>
        </th>
        <th width="45%">Code</th>
        <th width="45%">Description</th>
        </thead>
        <tbody>
        </tbody>
    </table>
    </form>



    <style>
    #selectcodecheckbox{
        padding: 15px 10px;
    }
    </style>
    <script type="text/javascript">
        /**
         * JQuery Plugin for dataTable
         * */
        var data_table_list;
        var update_new_url;
        var postdata;
        var edit_url = baseurl + "/destination_group/update/{{$DestinationGroupID}}";
        var datagrid_extra_url = baseurl + "/destination_group_code/ajax_datagrid";
        var checked='';
        var $searchFilter = {};

        var loading_btn;

        jQuery(document).ready(function ($) {
            $searchFilter.Code = $("#table_filter [name='FilterCode']").val();
            $searchFilter.Description = $("#table_filter [name='FilterDescription']").val();
            $searchFilter.CountryID = $("#table_filter [name='CountryID']").val();
            $searchFilter.DestinationGroupSetID = '{{$DestinationGroupSetID}}';
            $searchFilter.DestinationGroupID = '{{$DestinationGroupID}}';

            $("#selectall").click(function(ev) {
                var is_checked = $(this).is(':checked');
                $('#table-extra tbody tr').each(function(i, el) {
                    if (is_checked) {
                        $(this).find('.rowcheckbox').prop("checked", true);
                        $(this).addClass('selected');
                    } else {
                        $(this).find('.rowcheckbox').prop("checked", false);
                        $(this).removeClass('selected');
                    }
                });
            });
            // apply filter
            $("#table_filter").submit(function(ev) {
                ev.preventDefault();
                $searchFilter.Code = $("#table_filter [name='FilterCode']").val();
                $searchFilter.Description = $("#table_filter [name='FilterDescription']").val();
                $searchFilter.CountryID = $("#table_filter [name='CountryID']").val();
                console.log($searchFilter)
                data_table.fnFilter('', 0);
                return false;
            });
            // save codes
            $("#modal-form").submit(function(e){
                e.preventDefault();
                loading_btn.button('loading');
                submit_ajaxbtn(edit_url,$(this).serialize()+'&'+ $.param($searchFilter),'',loading_btn);
            });
            $('#add-button').click(function(ev){
                ev.preventDefault();
                loading_btn = $(this);
                $("#modal-form").trigger('submit');
            });
            //select all records
            $('#table-extra tbody').on('click', 'tr', function() {
                if (checked =='') {
                    $(this).toggleClass('selected');
                    if ($(this).hasClass('selected')) {
                        $(this).find('.rowcheckbox').prop("checked", true);
                    } else {
                        $(this).find('.rowcheckbox').prop("checked", false);
                    }
                }
            });

            data_table = $("#table-extra").dataTable({
                "bDestroy": true, // Destroy when resubmit form
                "bProcessing":true,
                "bServerSide": true,
                "iDisplayLength": '{{Config::get('app.pageSize')}}',
                "fnServerParams": function(aoData) {
                    aoData.push(
                            {"name": "DestinationGroupSetID", "value": $searchFilter.DestinationGroupSetID},
                            {"name": "DestinationGroupID", "value":$searchFilter.DestinationGroupID},
                            {"name": "Code", "value":$searchFilter.Code},
                            {"name": "Description", "value":$searchFilter.Description},
                            {"name": "CountryID", "value":$searchFilter.CountryID}

                    );
                },
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left '<'#selectcodecheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "sAjaxSource": datagrid_extra_url,
                "oTableTools": {
                    "aButtons": [

                    ]
                },
                "aoColumns": [
                    {"bSearchable":false,"bSortable": false, //RateID
                        mRender: function(id, type, full) {
                            if(full[3] > 0) {
                                return '<div class="checkbox "><input checked type="checkbox" name="RateID[]" value="' + id + '" class="rowcheckbox" ></div>';
                            }else{
                                return '<div class="checkbox "><input type="checkbox" name="RateID[]" value="' + id + '" class="rowcheckbox" ></div>';
                            }
                        }
                    },
                    {  "bSearchable":true,"bSortable": false },  // 0 Code
                    {  "bSearchable":true,"bSortable": false },  // 0 description
                ],

                "fnDrawCallback": function() {
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });
                    add_selected();

                    $('#table-extra tbody tr').each(function(i, el) {

                        if (checked!='') {
                            $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                            $(this).addClass('selected');
                            $('#selectallbutton').prop("checked", true);
                        } else if(!$(this).hasClass('donotremove')){
                            $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);;
                            $(this).removeClass('selected');
                        }
                    });

                    $('#selectallbutton').click(function(ev) {
                        if($(this).is(':checked')){
                            checked = 'checked=checked disabled';
                            $("#selectall").prop("checked", true).prop('disabled', true);
                            if(!$('#changeSelectedInvoice').hasClass('hidden')){
                                $('#table-extra tbody tr').each(function(i, el) {
                                    $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                    $(this).addClass('selected');
                                });
                            }
                        }else{
                            checked = '';
                            $("#selectall").prop("checked", false).prop('disabled', false);
                            if(!$('#changeSelectedInvoice').hasClass('hidden')){
                                $('#table-extra tbody tr').each(function(i, el) {
                                    $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                                    $(this).removeClass('selected');
                                });
                            }
                        }
                    });
                }

            });
            $("#selectcodecheckbox").append('<input type="checkbox" id="selectallbutton" name="selectallcodes[]" class="" title="Select All Found Records" />');
        });
        function add_selected(){
            $('#table-extra tbody tr').each(function(i, el) {
                if ($(this).find('.rowcheckbox').prop("checked")) {
                    $(this).addClass('selected donotremove');
                } else {
                    $(this).removeClass('selected');
                }
            });
        }

    </script>


@stop
