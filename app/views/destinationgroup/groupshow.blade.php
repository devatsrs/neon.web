@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form id="table_filter" method=""  action="" class="form-horizontal form-groups-bordered validate" novalidate>
                <div class="form-group">
                    <label for=" field-1" class="control-label">Country</label>
                    {{Form::select('CountryID', $countries,'',array("class"=>"form-control select2"))}}
                </div>
                <div class="form-group">
                    <label class="control-label">Code</label>
                    <input type="text" class="form-control" name="FilterCode">
                </div>
                <div class="form-group">
                    <label class="control-label">Description</label>
                    <input type="text" class="form-control" name="FilterDescription">
                </div>
                <div class="form-group">
                    <label class="control-label">Show Applied Code</label>
                    <input class="icheck" name="Selected" type="checkbox" value="1" >
                </div>
                <input type="hidden" name="DestinationGroupID" value="{{$DestinationGroupID}}" >
                <input type="hidden" name="DestinationGroupSetID" value="{{$DestinationGroupSetID}}">
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
        @if($discountplanapplied == 0)
        @if(User::checkCategoryPermission('DestinationGroup','Edit'))
        <button  id="add-button" class=" btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."><i class="fa fa-floppy-o"></i>Add</button>
            <button  id="delete-button" class=" btn btn-danger btn-sm btn-icon icon-left" data-loading-text="Loading..."><i class="entypo-trash"></i>Delete</button>
        @endif
        @endif
        <a href="{{URL::to('/destination_group_set/show/'.$DestinationGroupSetID)}}" class="btn btn-danger btn-sm btn-icon icon-left">
            <i class="entypo-cancel"></i>
            Close
        </a>
    </p>
    @include('includes.errors')
    @include('includes.success')


    <form id="modal-form">
    <table id="table-extra" class="table table-bordered datatable">
        <thead>
        <th width="10%">
            <div class="checkbox">
                <input type="checkbox" name="RateID[]" class="selectall" id="selectall">
            </div>
        </th>
        <th width="30%">Code</th>
        <th width="30%">Description</th>
        <th width="30%">Applied</th>
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

            $('#filter-button-toggle').show();

            $searchFilter.Code = $("#table_filter [name='FilterCode']").val();
            $searchFilter.Description = $("#table_filter [name='FilterDescription']").val();
            $searchFilter.CountryID = $("#table_filter [name='CountryID']").val();
            $searchFilter.Selected = $("#table_filter input[name='Selected']").prop("checked");
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
                $searchFilter.Selected = $("#table_filter input[name='Selected']").prop("checked");
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
                $searchFilter.Action = 'Insert';
                $("#modal-form").trigger('submit');
            });
            $('#delete-button').click(function(ev){
                ev.preventDefault();
                loading_btn = $(this);
                $searchFilter.Action = 'Delete';
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
                "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                "fnServerParams": function(aoData) {
                    aoData.push(
                            {"name": "DestinationGroupSetID", "value": $searchFilter.DestinationGroupSetID},
                            {"name": "DestinationGroupID", "value":$searchFilter.DestinationGroupID},
                            {"name": "Code", "value":$searchFilter.Code},
                            {"name": "Description", "value":$searchFilter.Description},
                            {"name": "Selected", "value":$searchFilter.Selected},
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
                            /*if(full[3] > 0) {
                                return '<div class="checkbox "><input checked type="checkbox" name="RateID[]" value="' + id + '" class="rowcheckbox" ></div>';
                            }else{
                                return '<div class="checkbox "><input type="checkbox" name="RateID[]" value="' + id + '" class="rowcheckbox" ></div>';
                            }*/
                            return '<div class="checkbox "><input type="checkbox" name="RateID[]" value="' + id + '" class="rowcheckbox" ></div>';
                        }
                    },
                    {  "bSearchable":true,"bSortable": false },  // 0 Code
                    {  "bSearchable":true,"bSortable": false },  // 0 description
                    {  "bSearchable":true,"bSortable": false },  // 0 Applied
                ],

                "fnDrawCallback": function() {
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });


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


    </script>


@stop
