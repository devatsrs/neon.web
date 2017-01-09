@extends('layout.main') @section('content')

<ol class="breadcrumb bc-3">
    <li><a href="{{URL::to('/dashboard')}}"><i class="entypo-home"></i>Home</a></li>
    <li><a href="{{URL::to('/rate_tables')}}">Rate Table</a></li>
    <li class="active"><strong>{{$rateTable->RateTableName}}</strong></li>
</ol>
<h3>View Rate Table</h3>
<div class="float-right" >
    <a href="{{URL::to('/rate_tables')}}"  class="btn btn-primary btn-sm btn-icon icon-left" >
        <i class="entypo-floppy"></i>
        Back
    </a>
</div>
<div class="row">
    <div class="col-md-12">
        <ul class="nav nav-tabs bordered">
            <!-- available classes "bordered", "right-aligned" -->
            <li class="active  "><a href="{{URL::to('/rate_tables/'.$id.'/view')}}"> <span
                        class="hidden-xs">Rate</span>
                </a></li>
            <li><a href="{{URL::to('/rate_tables/'.$id.'/upload')}}"> <span
                            class="hidden-xs">Upload Rates</span>
                </a></li>
        </ul>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <form role="form" id="rate-table-search"  method="post" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">Search</div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>

                <div class="panel-body">

                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Code</label>
                        <div class="col-sm-4">
                            <input type="text" name="Code" class="form-control" id="field-1" placeholder="" />
                        </div>

                        <label class="col-sm-2 control-label">Description</label>
                        <div class="col-sm-4">
                            <input type="text" name="Description" class="form-control" id="field-1" placeholder="" />
                            <input type="hidden" name="TrunkID" value="{{$trunkID}}" >

                        </div>

                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Country</label>
                        <div class="col-sm-4">{{ Form::select('Country', $countries, Input::old('Country') , array("class"=>"select2")) }}</div>
                        <label for="field-1" class="col-sm-2 control-label">Effective</label>
                        <div class="col-sm-4">
                            <select name="Effective" class="select2" data-allow-clear="true" data-placeholder="Select Effective">
                                <option value="Now">Now</option>
                                <option value="Future">Future</option>
                                <option value="All">All</option>
                            </select>
                        </div>
                    </div>

                    <p style="text-align: right;">
                        <button type="submit"  class="btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-search"></i> Search
                        </button>
                    </p>
                </div>
            </div>
        </form>
    </div>
</div>


<p style="text-align: right;">
    @if($isBandTable)
        @if(User::checkCategoryPermission('RateTables','Add') )
            <button id="add-new-rate" class="btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-plus"></i> Add New</button>
        @endif    
    @endif
    @if(User::checkCategoryPermission('RateTables','Edit') )
        <a  id="change-bulk-rate" class="btn btn-primary btn-sm btn-icon icon-left" href="javascript:;"> <i class="entypo-floppy"></i>
            Change Selected
        </a>
    @endif
    @if(User::checkCategoryPermission('RateTables','Delete') )
        <button id="clear-bulk-rate" class="btn btn-danger btn-sm btn-icon icon-left" data-loading-text="Loading..."> <i class="entypo-cancel"></i> Delete Selected </button>
    @endif    
<form id="clear-bulk-rate-form" ><input type="hidden" name="RateTableRateID" /><input type="hidden" name="criteria" /></form>
</p>

<table class="table table-bordered datatable" id="table-4">
    <thead>
        <tr>
        <th width="4%">
            <div class="checkbox ">
                <input type="checkbox" id="selectall" name="checkbox[]" />
            </div>
        </th>
        <th width="4%">Code</th>
        <th width="20%">Description</th>
        <th width="5%">Interval 1</th>
        <th width="5%">Interval N</th>
        <th width="5%">Connection Fee</th>
        <th width="5%">Rate ({{$code}})</th>
        <th width="10%">Effective Date</th>
        <th width="10%">Modified Date</th>
        <th width="10%">Modified By</th>
        <th width="12%" > Action</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>





<script type="text/javascript">
    var $searchFilter = {};
    var checked='';
    var codedeckid = '{{$id}}';
    var list_fields  = ['ID','Code','Description','Interval1','IntervalN','ConnectionFee','Rate','EffectiveDate','updated_at','ModifiedBy','RateTableRateID','RateID'];
    jQuery(document).ready(function($) {

        $("#rate-table-search").submit(function(e) {
            $searchFilter.Code = $("#rate-table-search input[name='Code']").val();
            $searchFilter.Description = $("#rate-table-search input[name='Description']").val();
            $searchFilter.Country = $("#rate-table-search select[name='Country']").val();
            $searchFilter.TrunkID = $("#rate-table-search [name='TrunkID']").val();
            $searchFilter.Effective = $("#rate-table-search [name='Effective']").val();

            data_table = $("#table-4").dataTable({
                "bDestroy": true, // Destroy when resubmit form
                "bProcessing": true,
                "bServerSide": true,
                "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "sAjaxSource": baseurl + "/rate_tables/{{$id}}/search_ajax_datagrid",
                "fnServerParams": function(aoData) {
                    aoData.push({"name": "Code", "value": $searchFilter.Code}, {"name": "Description", "value": $searchFilter.Description}, {"name": "Country", "value": $searchFilter.Country},{"name": "TrunkID", "value": $searchFilter.TrunkID},{"name": "Effective", "value": $searchFilter.Effective});
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push({"name": "Code", "value": $searchFilter.Code}, {"name": "Description", "value": $searchFilter.Description}, {"name": "Country", "value": $searchFilter.Country},{"name": "TrunkID", "value": $searchFilter.TrunkID},{"name": "Effective", "value": $searchFilter.Effective});
                },
                "iDisplayLength": parseInt('{{Config::get('app.pageSize')}}'),
                "sPaginationType": "bootstrap",
                //  "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "aaSorting": [[1, "asc"]],
                "aoColumns":
                        [
                            {"bSortable": false,
                                mRender: function(id, type, full) {
                                    return '<div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + id + '" class="rowcheckbox" ></div>';
                                }
                            }, //0Checkbox
                            {}, //1 code
                            {}, //2 description
                            {}, //3 interval 1
                            {}, //4 interval n
                            {}, //5 ConnectionFee
                            {}, //5 Rate
                            {}, //4 Effective Date
                            {}, //7 ModifiedDate
                            {}, //8 ModifiedBy
                            {
                                mRender: function(id, type, full) {
                                    var action, edit_, delete_;
                                    clerRate_ = "{{ URL::to('/rate_tables/{id}/clear_rate')}}";

                                    clerRate_ = clerRate_.replace('{id}', id);
                                    action = '<div class = "hiddenRowData" >';
                                    for(var i = 0 ; i< list_fields.length; i++){
                                        action += '<input type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null?full[i]:'')+ '" / >';
                                    }
                                    action += '</div>';
                                    <?php if(User::checkCategoryPermission('RateTables','Edit')) { ?>
                                        action += '<a href="Javascript:;" class="edit-rate-table btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit</a>';
                                    <?php } ?>
                                    if (id != null && id > 0) {
                                        <?php if(User::checkCategoryPermission('RateTables','Delete')) { ?>
                                            action += ' <button href="' + clerRate_ + '"  class="btn clear-rate-table btn-danger btn-sm btn-icon icon-left" data-loading-text="Loading..."><i class="entypo-cancel"></i>Delete</button>';
                                        <?php } ?>
                                    }
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
                                    "sUrl": baseurl + "/rate_tables/{{$id}}/rate_exports/xlsx",
                                    sButtonClass: "save-collection btn-sm"
                                },
                                {
                                    "sExtends": "download",
                                    "sButtonText": "CSV",
                                    "sUrl": baseurl + "/rate_tables/{{$id}}/rate_exports/csv",
                                    sButtonClass: "save-collection btn-sm"
                                }
                            ]
                        },
                "fnDrawCallback": function() {

                    $(".btn.clear").click(function(e) {

                        response = confirm('Are you sure?');
                        //redirect = ($(this).attr("data-redirect") == 'undefined') ? "{{URL::to('/rate_tables')}}" : $(this).attr("data-redirect");
                        if (response) {
                            $.ajax({
                                url: $(this).attr("href"),
                                type: 'POST',
                                dataType: 'json',
                                success: function(response) {
                                    $(".btn.delete").button('reset');
                                    if (response.status == 'success') {
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


                        }
                        return false;

                    });
                    $("#selectall").click(function(ev) {
                        var is_checked = $(this).is(':checked');
                        $('#table-4 tbody tr').each(function(i, el) {
                            if (is_checked) {
                                $(this).find('.rowcheckbox').prop("checked", true);
                                $(this).addClass('selected');
                            } else {
                                $(this).find('.rowcheckbox').prop("checked", false);
                                $(this).removeClass('selected');
                            }
                        });
                    });

                    //Edit Button
                    $(".edit-rate-table.btn").click(function(ev) {
                        ev.stopPropagation();
                        var cur_obj = $(this).prev("div.hiddenRowData");
                        for(var i = 0 ; i< list_fields.length; i++){
                            $("#edit-rate-table-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                        }
                        jQuery('#modal-rate-table').modal('show', {backdrop: 'static'});
                    });

                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });
                    //Clear Button
                    $(".clear-rate-table.btn").click(function(ev) {
                        response = confirm('Are you sure?');
                        if (response) {
                            var clear_url;
                            clear_url = $(this).attr("href");
                            $(this).button('loading');
                            //get
                            $.get(clear_url, function (response) {
                                if (response.status == 'success') {
                                    $(this).button('reset');
                                    data_table.fnFilter('', 0);
                                    toastr.success(response.message, "Success", toastr_opts);
                                } else {
                                    $(this).button('reset');
                                    toastr.error(response.message, "Error", toastr_opts);
                                }
                            });
                        }
                        return false;


                    });
                    //select all button
                    $('#table-4 tbody tr').each(function(i, el) {
                        if($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                            if (checked != '') {
                                $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                $(this).addClass('selected');
                                $('#selectallbutton').prop("checked", true);
                            } else {
                                $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                                ;
                                $(this).removeClass('selected');
                            }
                        }
                    });

                    $('#selectallbutton').click(function(ev) {
                        if($(this).is(':checked')){
                            checked = 'checked=checked disabled';
                            $("#selectall").prop("checked", true).prop('disabled', true);
                            if(!$('#changeSelectedInvoice').hasClass('hidden')){
                                $('#table-4 tbody tr').each(function(i, el) {
                                    if($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                                        $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                        $(this).addClass('selected');
                                    }
                                });
                            }
                        }else{
                            checked = '';
                            $("#selectall").prop("checked", false).prop('disabled', false);
                            if(!$('#changeSelectedInvoice').hasClass('hidden')){
                                $('#table-4 tbody tr').each(function(i, el) {
                                    if($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                                        $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                                        $(this).removeClass('selected');
                                    }
                                });
                            }
                        }
                    });
                }
            });
            $("#selectcheckbox").append('<input type="checkbox" id="selectallbutton" name="checkboxselect[]" class="" title="Select All Found Records" />');
            return false;
        });
        $('#table-4 tbody').on('click', 'tr', function() {
            if (checked =='') {
                $(this).toggleClass('selected');
                if ($(this).hasClass('selected')) {
                    $(this).find('.rowcheckbox').prop("checked", true);
                } else {
                    $(this).find('.rowcheckbox').prop("checked", false);
                }
            }
        });

        //Bulk Clear Submit
        $("#clear-bulk-rate").click(function() {
            var responsecheck = confirm('Are you sure?');
            if(!responsecheck){
                return false;
            }

            var RateTableRateIDs = [];
            var i = 0;
            $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                //console.log($(this).val());
                RateTableRateID = $(this).val();
                RateTableRateIDs[i++] = RateTableRateID;
            });
            var criteria='';
            if($('#selectallbutton').is(':checked')){
                criteria = JSON.stringify($searchFilter);
                $("#clear-bulk-rate-form").find("input[name='RateTableRateID']").val('');
                $("#clear-bulk-rate-form").find("input[name='criteria']").val(criteria);
            }else{
                $("#clear-bulk-rate-form").find("input[name='RateTableRateID']").val(RateTableRateIDs.join(","))
                $("#clear-bulk-rate-form").find("input[name='criteria']").val('');
            }


            var formData = new FormData($('#clear-bulk-rate-form')[0]);

            $.ajax({
                url: baseurl + '/rate_tables/{{$id}}/bulk_clear_rate_table_rate', //Server script to process data
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    $("#clear-bulk-rate").button('reset');
                    if (response.status == 'success') {
                        toastr.success(response.message, "Success", toastr_opts);
                        data_table.fnFilter('', 0);
                    } else {
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                },
                // Form data
                data: formData,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false,
                contentType: false,
                processData: false
            });
            return false;
        });
        //Bulk Edit Button
        $("#change-bulk-rate").click(function(ev) {

            var RateTableRateIDs = [];
            var RateIDs = [];

            var i = 0;
            $('#table-4 tr .rowcheckbox:checked').each(function(i, el) {
                //console.log($(this).val());
                RateTableRateID = $(this).val();
                RateTableRateIDs[i] = RateTableRateID;
                RateID = $(this).parents("tr").find("div.hiddenRowData").find("input[name='RateID']").val();
                RateIDs[i] = RateID;


                i++;
            });
            date = new Date();
            var month = date.getMonth()+1;
            var day = date.getDate();
            currentDate = date.getFullYear() + '-' +   (month<10 ? '0' : '') + month + '-' +     (day<10 ? '0' : '') + day;
            $("#bulk-edit-rate-table-form")[0].reset();
            $("#bulk-edit-rate-table-form").find("input[name='RateTableRateID']").val(RateTableRateIDs.join(","));
            $("#bulk-edit-rate-table-form").find("input[name='RateID']").val(RateIDs.join(","));
            $("#bulk-edit-rate-table-form").find("input[name='Interval1']").val(1);
            $("#bulk-edit-rate-table-form").find("input[name='IntervalN']").val(1);
            $("#bulk-edit-rate-table-form").find("input[name='EffectiveDate']").val(currentDate);


            if(RateIDs.length){
                jQuery('#modal-bulk-rate-table').modal('show', {backdrop: 'static'});
            }

        });
        //Edit Form Submit
        $("#edit-rate-table-form").submit(function() {

            var formData = new FormData($('#edit-rate-table-form')[0]);
            RateTableRateID = $("#edit-rate-table-form").find("input[name='RateTableRateID']").val();
            $.ajax({
                url: baseurl + '/rate_tables/{{$id}}/update_rate_table_rate/'+RateTableRateID, //Server script to process data
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    $(".save.btn").button('reset');

                    if (response.status == 'success') {
                        $('#modal-rate-table').modal('hide');
                        toastr.success(response.message, "Success", toastr_opts);
                        data_table.fnFilter('', 0);
                    } else {
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                },
                // Form data
                data: formData,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false,
                contentType: false,
                processData: false
            });
            return false;
        });

        //Bulk Form Submit
        $("#bulk-edit-rate-table-form").submit(function() {
            var criteria='';
            if($('#selectallbutton').is(':checked')){
                criteria = JSON.stringify($searchFilter);
                $("#bulk-edit-rate-table-form").find("input[name='RateTableRateID']").val('');
                $("#bulk-edit-rate-table-form").find("input[name='criteria']").val(criteria);
            }

            var formData = new FormData($('#bulk-edit-rate-table-form')[0]);
            $.ajax({
                url: baseurl + '/rate_tables/{{$id}}/bulk_update_rate_table_rate', //Server script to process data
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    $(".save.btn").button('reset');

                    if (response.status == 'success') {
                        $('#modal-bulk-rate-table').modal('hide');
                        toastr.success(response.message, "Success", toastr_opts);
                        data_table.fnFilter('', 0);
                    } else {
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                },
                // Form data
                data: formData,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false,
                contentType: false,
                processData: false
            });
            return false;
        });




        // Replace Checboxes
        $(".pagination a").click(function(ev) {
            replaceCheckboxes();
        });
        $("#add-new-rate").click(function(e){
            e.preventDefault();
            $("#new-rate-form")[0].reset();
            //$("#new-rate-form [name='RateID']").select2().select2('val','');
            $("#modal-add-new").modal('show');
        });
        $("#new-rate-form").submit(function(e){
            e.preventDefault();
            fullurl = baseurl+'/rate_tables/{{$id}}/add_newrate';
            submit_ajax(fullurl,$("#new-rate-form").serialize());
        return false;
        });
        $('#rateid_list').select2({
            placeholder: 'Enter a Code',
            minimumInputLength: 1,
            ajax: {
                dataType: 'json',
                url: baseurl+'/rate_tables/getCodeByAjax',
                data: function (term, page) {
                    return {
                        q: term,
                        page: codedeckid
                    };
                },
                quietMillis: 500,
                error: function (data) {
                    return false;
                },
                results: function (data, page) {
                    return {
                        results: data
                    };
                }
            }
        });
    });

</script>
<style>
#selectcheckbox{
    padding: 15px 10px;
}
</style>
@stop @section('footer_ext') @parent
<div class="modal fade" id="modal-rate-table">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="edit-rate-table-form" method="post" >

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"
                            aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Edit Rate Table</h4>
                </div>

                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-6">

                            <div class="form-group">
                                <label for="field-4" class="control-label">Effective Date</label>

                                <input type="text"  name="EffectiveDate" class="form-control datepicker" data-startdate="{{date('Y-m-d')}}" data-start-date="" data-date-format="yyyy-mm-dd" value="" />
                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="form-group">
                                <label for="field-5" class="control-label">Rate</label>
                                <input type="text" name="Rate" class="form-control" id="field-5" placeholder="">

                            </div>

                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-6">

                            <div class="form-group">
                                <label for="field-4" class="control-label">Interval 1</label>
                                <input type="text" name="Interval1" class="form-control" value="" />

                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="form-group">
                                <label for="field-5" class="control-label">Interval N</label>
                                <input type="text" name="IntervalN" class="form-control" id="field-5" placeholder="">

                            </div>

                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Connection Fee</label>
                                <input type="text" name="ConnectionFee" class="form-control" id="field-5" placeholder="">
                            </div>
                        </div>
                     </div>
                </div>

                <div class="modal-footer">
                    <input type="hidden" name="RateTableRateID" value="">
                    <input type="hidden" name="RateID" value="">

                    <button type="submit" class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-floppy"></i> Save
                    </button>
                    <button type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                        <i class="entypo-cancel"></i> Close
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Bulk Update -->
<div class="modal fade" id="modal-bulk-rate-table">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="bulk-edit-rate-table-form" method="post">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"
                            aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Bulk Edit Rate Table</h4>
                </div>

                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-6">

                            <div class="form-group">
                                <input type="checkbox" name="updateEffectiveDate" class="" />
                                <label for="field-4" class="control-label">Effective Date</label>

                                <input type="text" name="EffectiveDate" class="form-control datepicker"  data-startdate="{{date('Y-m-d')}}" data-date-format="yyyy-mm-dd" value="" />
                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="form-group">
                                <input type="checkbox" name="updateRate" class="" />
                                <label for="field-5" class="control-label">Rate</label>

                                <input type="text" name="Rate" class="form-control" id="field-5" placeholder="">
                            </div>

                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-6">

                            <div class="form-group">
                                <input type="checkbox" name="updateInterval1" class="" />
                                <label for="field-4" class="control-label">Interval 1</label>
                                <input type="text" name="Interval1" class="form-control" value="" />

                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="form-group">
                                <input type="checkbox" name="updateIntervalN" class="" />
                                <label for="field-5" class="control-label">Interval N</label>
                                <input type="text" name="IntervalN" class="form-control" id="field-5" placeholder="">

                            </div>

                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="checkbox" name="updateConnectionFee" class="" />
                                <label for="field-5" class="control-label">Connection Fee</label>
                                <input type="text" name="ConnectionFee" class="form-control" id="field-5" placeholder="">
                            </div>
                        </div>
                     </div>

                </div>

                <div class="modal-footer">
                    <input type="hidden" name="RateTableRateID" value="">
                    <input type="hidden" name="RateID" value="">
                    <input type="hidden" name="criteria" value="">

                    <button type="submit"
                            class="save btn btn-primary btn-sm btn-icon icon-left"
                            data-loading-text="Loading...">
                        <i class="entypo-floppy"></i> Save
                    </button>
                    <button type="button"
                            class="btn btn-danger btn-sm btn-icon icon-left"
                            data-dismiss="modal">
                        <i class="entypo-cancel"></i> Close
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-add-new">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="new-rate-form" method="post">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add New Rate</h4>
                </div>

                <div class="modal-body">

                    <div class="row">

                        <div class="col-md-6">

                            <div class="form-group">
                                <label for="field-4" class="control-label">Code</label>
                                {{--{{ Form::select('RateID', array(), '', array("class"=>"select2 rateid_list")) }}--}}
                                <input type="hidden" id="rateid_list" name="RateID" />

                            </div>

                        </div>
                        <div class="col-md-6">

                            <div class="form-group">
                                <label for="field-4" class="control-label">Effective Date</label>

                                <input type="text" name="EffectiveDate" class="form-control datepicker" data-startdate="{{date('Y-m-d')}}" data-start-date="" data-date-format="yyyy-mm-dd" value="" />
                            </div>

                        </div>
                        <div class="col-md-6 clear">

                            <div class="form-group">
                                <label for="field-5" class="control-label">Rate</label>

                                <input type="text" name="Rate" class="form-control" id="field-5" placeholder="">

                            </div>

                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Connection Fee</label>
                                <input type="text" name="ConnectionFee" class="form-control" id="field-5" placeholder="">
                            </div>
                        </div>
                        <div class="col-md-6 clear">

                            <div class="form-group">
                                <label for="field-4" class="control-label">Interval 1</label>

                                <input type="text" name="Interval1" class="form-control" value="" />
                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="form-group">
                                <label for="field-5" class="control-label">Interval N</label>

                                <input type="text" name="IntervalN" class="form-control" id="field-5" placeholder="">

                            </div>

                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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
