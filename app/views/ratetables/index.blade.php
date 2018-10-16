@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form novalidate class="form-horizontal form-groups-bordered validate" method="get" id="ratetable_filter">
                <div class="form-group">
                    <label for="Search" class="control-label">Search</label>
                    <input class="form-control" name="Search" id="Search"  type="text" >
                </div>
                <div class="form-group">
                    <label class="control-label" for="field-1">Trunk</label>
                    {{ Form::select('TrunkID', $trunks, '', array("class"=>"select2","data-type"=>"trunk")) }}
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
        <a href="{{URL::to('/dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Rate Table</strong>
    </li>
</ol>
<h3>Rate Table</h3>
<p style="text-align: right;">
@if(User::checkCategoryPermission('RateTables','Add'))
    <a href="#" id="add-new-rate-table" class="btn btn-primary ">
        <i class="entypo-plus"></i>
        Add New RateTable
    </a>
@endif
    <a href="{{URL::to('rate_tables/apply_rate_table')}}" id="add-new-rate-table" class="btn btn-primary ">
        Apply Rate Table
    </a>
</p>

<div class="cler row">
    <div class="col-md-12">
        <form role="form" id="form1" method="post" class="form-horizontal form-groups-bordered validate" novalidate>
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        Rate Table
                    </div>
                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <div class="col-md-12">
                            <table class="table table-bordered datatable" id="table-4">
                                <thead>
                                    <tr>
                                        <th >Name</th>
                                        <th >Currency</th>
                                        <th >Trunk</th>
                                        <th >Codedeck</th>
                                        <th >Last Updated</th>
                                         <th >Action</th>
                                    </tr>
                                </thead>
                                <tbody>


                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
jQuery(document).ready(function($) {

    $('#filter-button-toggle').show();

    var $searchFilter = {};
    var update_new_url;
        $searchFilter.TrunkID = $("#ratetable_filter [name='TrunkID']").val();
		$searchFilter.Search = $('#ratetable_filter [name="Search"]').val();
        data_table = $("#table-4").dataTable({
            "bDestroy": true,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": baseurl + "/rate_tables/ajax_datagrid",
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "oTableTools": {},
            "aaSorting": [[0, "asc"]],
            "fnServerParams": function(aoData) {
                aoData.push({"name":"TrunkID","value":$searchFilter.TrunkID},{"name":"Search","value":$searchFilter.Search});
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name":"TrunkID","value":$searchFilter.TrunkID},{"name":"Search","value":$searchFilter.Search});
            },
            "fnRowCallback": function(nRow, aData) {
                $(nRow).attr("id", "host_row_" + aData[2]);
            },
            "aoColumns":
                    [
                        {},
                        {},
                        {},
                        {},
                        {},
                        {
                            mRender: function(id, type, full) {
                                var action, view_, delete_;
                                view_ = "{{ URL::to('/rate_tables/{id}/view')}}";
                                delete_ = "{{ URL::to('/rate_tables/{id}/delete')}}";

                                view_ = view_.replace('{id}', id);
                                delete_ = delete_.replace('{id}', id);

                                action = '<a title="View" href="' + view_ + '" class="btn btn-default btn-sm"><i class="fa fa-eye"></i></a>&nbsp;';
                                action += '<a title="Edit" data-id="'+  id +'" data-rateTableName="'+full[0]+'" data-TrunkID="'+full[6]+'" data-CurrencyID="'+full[7]+'" data-RoundChargedAmount="'+full[8]+'" class="edit-ratetable btn btn-default btn-sm"><i class="entypo-pencil"></i></a>&nbsp;';

                                <?php if(User::checkCategoryPermission('RateTables','Delete') ) { ?>
                                    action += ' <a title="Delete" href="' + delete_ + '" data-redirect="{{URL::to("/rate_tables")}}"  class="btn btn-default delete btn-danger btn-sm" data-loading-text="Loading..."><i class="entypo-trash"></i></a>';
                                <?php } ?>
                                //action += status_link;
                                return action;
                            }
                        },
                    ],
                    "oTableTools":
                    {
                        "aButtons": [
                            {
                                "sExtends": "download",
                                "sButtonText": "EXCEL",
                                "sUrl": baseurl + "/rate_tables/exports/xlsx",
                                sButtonClass: "save-collection btn-sm"
                            },
                            {
                                "sExtends": "download",
                                "sButtonText": "CSV",
                                "sUrl": baseurl + "/rate_tables/exports/csv",
                                sButtonClass: "save-collection btn-sm"
                            }
                        ]
                    }, 
            "fnDrawCallback": function() {
                $(".dataTables_wrapper select").select2({
                    minimumResultsForSearch: -1
                });

                $(".btn.delete").click(function(e) {
                    e.preventDefault();
                    response = confirm('Are you sure?');
                    //redirect = ($(this).attr("data-redirect") == 'undefined') ? "{{URL::to('/rate_tables')}}" : $(this).attr("data-redirect");
                    if (response) {
                        $(this).text('Loading..');
                        $('#table-4_processing').css('visibility','visible');
                        $.ajax({
                            url: $(this).attr("href"),
                            type: 'POST',
                            dataType: 'json',
                            beforeSend: function(){
                            //    $(this).text('Loading..');
                            },
                            success: function(response) {
                                if (response.status == 'success') {
                                    toastr.success(response.message, "Success", toastr_opts);
                                    data_table.fnFilter('', 0);
                                } else {
                                    toastr.error(response.message, "Error", toastr_opts);
                                    data_table.fnFilter('', 0);
                                }
                                $('#table-4_processing').css('visibility','hidden');
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
                $(".btn.change_status").click(function(e) {
                    //redirect = ($(this).attr("data-redirect") == 'undefined') ? "{{URL::to('/rate_tables')}}" : $(this).attr("data-redirect");
                     $(this).button('loading');
                        $.ajax({
                            url: $(this).attr("href"),
                            type: 'POST',
                            dataType: 'json',
                            success: function(response) {
                                $(this).button('reset');
                                if (response.status == 'success') {
                                    toastr.success(response.message, "Success", toastr_opts);
                                    data_table.fnFilter('', 0);
                                } else {
                                    toastr.error(response.message, "Error", toastr_opts);
                                }
                            },

                            // Form data
                            //data: {},
                            cache: false,
                            contentType: false,
                            processData: false
                        });
                    return false;
                });
            }
        });
        $('table tbody').on('click','.edit-ratetable',function(ev){
            ev.preventDefault();
            ev.stopPropagation();
            $('#modal-edit-new-rate-table').trigger("reset");
            $("#modal-edit-new-rate-table [name='RateTableId']").val($(this).attr('data-id'));
            $("#modal-edit-new-rate-table [name='RateTableName']").val($(this).attr('data-ratetablename'));
            $("#modal-edit-new-rate-table [name='TrunkID']").select2('val', $(this).attr('data-TrunkID'));
            $("#modal-edit-new-rate-table [name='CurrencyID']").select2('val', $(this).attr('data-CurrencyID'));
            $("#modal-edit-new-rate-table [name='RoundChargedAmount']").val($(this).attr('data-RoundChargedAmount'));
            $('#modal-edit-new-rate-table').modal('show');
        });
        $("#ratetable_filter").submit(function(e) {
            e.preventDefault();
            $searchFilter.TrunkID = $("#ratetable_filter [name='TrunkID']").val();
			$searchFilter.Search = $('#ratetable_filter [name="Search"]').val();
            data_table.fnFilter('', 0);
            return false;
         });
         $("#add-new-rate-table").click(function(ev) {
             ev.preventDefault();
             $('#modal-add-new-rate-table').modal('show', {backdrop: 'static'});
         });
         $("#add-new-form").submit(function(ev){
            ev.preventDefault();
            update_new_url = baseurl + '/rate_tables/store';
            submit_ajax(update_new_url,$("#add-new-form").serialize());
         });
        $("#edit-form").submit(function(ev){
            ev.preventDefault();
            var RateTableId = $("#modal-edit-new-rate-table [name='RateTableId']").val();
            update_new_url = baseurl + '/rate_tables/edit/'+RateTableId;
            submit_ajax(update_new_url,$("#edit-form").serialize());
        });
    });

</script>
@include('includes.errors')
@include('includes.success')
@include('trunk.trunkmodal')
@include('currencies.currencymodal')
@stop
@section('footer_ext')
@parent
<div class="modal fade" id="modal-add-new-rate-table">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="add-new-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add New RateTable</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group ">
                                <label for="field-5" class="control-label">Codedeck</label>
                                {{Form::select('CodedeckId', $codedecks, '',array("class"=>"form-control select2"))}}
                            </div>
                        </div>
                         <div class="col-md-6">
                            <div class="form-group ">
                                <label for="field-5" class="control-label">Trunk</label>
                                {{Form::SelectControl('trunk')}}
                               <!-- {Form::select('TrunkID', $trunks, $trunk_keys,array("class"=>"form-control select2"))}}-->
                            </div>
                        </div>
                         </div>
                    <div class="row">
                       <div class="col-md-6">
                           <div class="form-group ">
                               <label for="field-5" class="control-label">Currency</label>
                               {{Form::SelectControl('currency')}}
                               <!--{ Form::select('CurrencyID', $currencylist,  '', array("class"=>"select2")) }}-->
                           </div>
                       </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-4" class="control-label">RateTable Name</label>
                                <input type="text" name="RateTableName" class="form-control" value="" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                       <div class="col-md-6">
                           <div class="form-group ">
                               <label for="field-5" class="control-label">Round Charged Amount (123.45)</label>
                               <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="bottom" data-content="it's round up the value to given decimal points. Ex: you have entered 2 in Round Charged Amount then it will round the CDR amount like this : amount is 1.2355 becomes 1.24. Note that rounding off is always done upwards." data-original-title="Round Charged Amount (123.45)">?</span>
                               <div class="input-spinner">
                                   <button type="button" class="btn btn-default">-</button>
                                   {{Form::text('RoundChargedAmount', 2, array("class"=>"form-control", "maxlength"=>"1", "data-min"=>0,"data-max"=>6,"Placeholder"=>"Add Numeric value" , "data-mask"=>"decimal"))}}
                                   <button type="button" class="btn btn-default">+</button>
                               </div>
                           </div>
                       </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="codedeck-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-floppy"></i>
                        Save
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
<div class="modal fade" id="modal-edit-new-rate-table">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="edit-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Edit New RateTable</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group ">
                                <label for="field-5" class="control-label">Trunk</label>
                                {{Form::SelectControl('trunk')}}
                                        <!-- {Form::select('TrunkID', $trunks, $trunk_keys,array("class"=>"form-control select2"))}}-->
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group ">
                                <label for="field-5" class="control-label">Currency</label>
                                {{Form::SelectControl('currency')}}
                                        <!--{ Form::select('CurrencyID', $currencylist,  '', array("class"=>"select2")) }}-->
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-4" class="control-label">RateTable Name</label>
                                <input type="hidden" value="" name="RateTableId" />

                                <input type="text" name="RateTableName" class="form-control" value="" required/>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group ">
                                <label for="field-5" class="control-label">Round Charged Amount (123.45)</label>
                                <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="bottom" data-content="it's round up the value to given decimal points. Ex: you have entered 2 in Round Charged Amount then it will round the CDR amount like this : amount is 1.2355 becomes 1.24. Note that rounding off is always done upwards." data-original-title="Round Charged Amount (123.45)">?</span>
                                <div class="input-spinner">
                                    <button type="button" class="btn btn-default">-</button>
                                    {{Form::text('RoundChargedAmount', ( isset($BillingClass->RoundChargesAmount)?$BillingClass->RoundChargesAmount:'2' ),array("class"=>"form-control", "maxlength"=>"1", "data-min"=>0,"data-max"=>6,"Placeholder"=>"Add Numeric value" , "data-mask"=>"decimal"))}}
                                    <button type="button" class="btn btn-default">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="codedeck-edit"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-floppy"></i>
                        Save
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
@stop