@extends('layout.main')
@section('content')
    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li>
            <a href="{{URL::to('discount_plan')}}">Discount Plan </a>
        </li>
        <li>
            <a><span>{{discountplan_dropbox($id)}}</span></a>
        </li>
        <li class="active">
            <strong>Discount ({{$name}})</strong>
        </li>
    </ol>
    <h3>Discount</h3>

    @include('includes.errors')
    @include('includes.success')
    <p style="text-align: right;">
        @if(User::checkCategoryPermission('DiscountPlan','Edit'))
                <a  id="add-button" class=" btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-plus"></i>Add New</a>
        @endif
        <a href="{{URL::to('/discount_plan')}}" class="btn btn-danger btn-sm btn-icon icon-left">
            <i class="entypo-cancel"></i>
            Close
        </a>
    </p>


    <div id="table_filter" method="get" action="#" >
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
                    <label for="field-1" class="col-sm-1 control-label">Name</label>
                    <div class="col-sm-2">
                        <input type="text" name="Name" class="form-control" value="" />
                    </div>
                </div>
                <p style="text-align: right;">
                    <button class="btn btn-primary btn-sm btn-icon icon-left" id="filter_submit">
                        <i class="entypo-search"></i>
                        Search
                    </button>
                </p>
            </div>
        </div>
    </div>

    <table id="table-list" class="table table-bordered datatable">
        <thead>
        <tr>

            <th width="15%">Product Group</th>
            <th width="15%">Discount Type</th>
            <th width="10%">Modified By</th>
            <th width="10%">Modified Date</th>
            <th width="20%">Action</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    <script type="text/javascript">
        /**
         * JQuery Plugin for dataTable
         * */
        var data_table_list;
        var update_new_url;
        var postdata;
        var DestinationGroupID;

        jQuery(document).ready(function ($) {
            var list_fields  = ["Name","Service","Discount","UnlimitedText","UpdatedBy","updated_at","DiscountID","DiscountPlanID","DestinationGroupID","DiscountSchemeID","Threshold","Unlimited"];
            //public_vars.$body = $("body");
            var $search = {};
            var add_url = baseurl + "/discount/store";
            var edit_url = baseurl + "/discount/update/{id}";
            var delete_url = baseurl + "/discount/delete/{id}";
            var datagrid_url = baseurl + "/discount/ajax_datagrid";

            $("#filter_submit").click(function(e) {
                e.preventDefault();

                $search.Name = $("#table_filter").find('[name="Name"]').val();
                data_table = $("#table-list").dataTable({
                    "bDestroy": true,
                    "bProcessing":true,
                    "bServerSide": true,
                    "sAjaxSource": datagrid_url,
                    "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                    "sPaginationType": "bootstrap",
                    "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                    "aaSorting": [[0, 'asc']],
                    "fnServerParams": function (aoData) {
                        aoData.push(
                                {"name": "Name", "value": $search.Name},
                                {"name": "DiscountPlanID", "value": '{{$id}}'}


                        );
                        data_table_extra_params.length = 0;
                        data_table_extra_params.push(
                                {"name": "Name", "value": $search.Name},
                                {"name": "DiscountPlanID", "value": '{{$id}}'},
                                {"name": "Export", "value": 0}
                        );

                    },
                    "aoColumns": [
                        {  "bSortable": true },  // 0 Name
                        {
                            "bSortable": true,
                            mRender: function ( id, type, full ) {
                                if (full[1] == 1) {
                                    return "Volume";
                                } else if (full[1] == 2) {
                                    return "Minutes";
                                }else if (full[1] == 3) {
                                    return "Fixed";
                                }
                            }
                        },// 1 Service

                        {  "bSortable": true },  // 4 UpdatedBy
                        {  "bSortable": true },  // 5 updated_at
                        {  "bSortable": false,
                            mRender: function ( id, type, full ) {
                                action = '<div class = "hiddenRowData" >';
                                for(var i = 0 ; i< list_fields.length; i++){
                                    action += '<input disabled type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null?full[i]:'')+ '" / >';
                                }
                                action += '</div>';

                                        @if(User::checkCategoryPermission('DiscountPlan','Edit'))
                                        action += ' <a href="' + edit_url.replace("{id}",full[6]) +'" title="Edit" class="edit-button btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>'
                                        @endif
                                        @if(User::checkCategoryPermission('DiscountPlan','Delete'))
                                        action += ' <a href="' + delete_url.replace("{id}",full[6]) +'" title="Delete" class="delete-button btn btn-danger btn-sm"><i class="entypo-trash"></i></a>'
                                        @endif

                                        return action;
                            }
                        },  // 0 Created


                    ],
                    "oTableTools": {
                        "aButtons": [
                            {
                                "sExtends": "download",
                                "sButtonText": "EXCEL",
                                "sUrl": baseurl + "/discount/exports/xlsx?Export=1",
                                sButtonClass: "save-collection btn-sm"
                            },
                            {
                                "sExtends": "download",
                                "sButtonText": "CSV",
                                "sUrl": baseurl + "/discount/exports/csv?Export=1",
                                sButtonClass: "save-collection btn-sm"
                            }
                        ]

                    },
                    "fnDrawCallback": function() {
                        $(".dataTables_wrapper select").select2({
                            minimumResultsForSearch: -1
                        });
                    }

                });
            });


            $('#filter_submit').trigger('click');
            //inst.myMethod('I am a method');
            $('#add-button').click(function(ev){
                ev.preventDefault();
                $('#modal-form').trigger("reset");
                $('#modal-list h4').html('Add Discount');
                $("#modal-form [name=DiscountID]").val("");
                $("#modal-form [name=DiscountSchemeID]").val("");
                $("#modal-form [name=DestinationGroupID]").select2().select2('val',"");
                $("#modal-form [name=Service]").select2().select2('val',"");
                $("#modal-form [name=Service]").removeAttr("disabled");

                $("#Minutes-components").hide();
                var $item = $('#' + 'Minutes-components' + ' tr:last').attr('id');
                var numb = getNumber($item);
                for (var i =2; i<= numb;i++) {
                    deleteMinuteRow('minute-' + i, 'MinutesSubBox','getIDs','1');
                }
                $("#modal-form [id='" + 'MinutesComponent-' + (1) + "']").select2().select2('val', '');
                $("#modal-form [name='" + 'MinutesDiscount-' + (1) + "']").val('');
                $("#modal-form [name='" + 'MinutesTreshhold-' + (1) + "']").val('');
                $("#modal-form [id='" + 'MinutesUnlimited-' + (1) + "']").select2().select2('val', '');


                $("#Volume-components").hide();
                var $item = $('#' + 'Volume-components' + ' tr:last').attr('id');
                var numb = getNumber($item);
                for (var i =2; i<= numb;i++) {
                    deleteVolumeRow('volume-' + i, 'VolumeSubBox','getVolumeIDs','1');
                }
                $("#modal-form [id='" + 'VolumeComponent-' + (1) + "']").select2().select2('val', '');
                $("#modal-form [name='" + 'VolumeFromMin-' + (1) + "']").val('');
                $("#modal-form [name='" + 'VolumeToMin-' + (1) + "']").val('');
                $("#modal-form [name='" + 'VolumeDiscount-' + (1) + "']").val('');

                $("#Fixed-components").hide();
                var $item = $('#' + 'Fixed-components' + ' tr:last').attr('id');
                var numb = getNumber($item);

                for (var i =2; i<= numb;i++) {
                    deleteFixedRow('Fixed-' + i, 'FixedSubBox', 'getFixedIDs', '1');
                }
                $("#modal-form [id='" + 'FixedComponent-' + (1) + "']").select2().select2('val', '');
                $("#modal-form [name='" + 'FixedDiscount-' + (1) + "']").val('');

                $('#getIDs').val('');
                $('#getVolumeIDs').val('');
                $('#getFixedIDs').val('');

                $('#modal-form').attr("action",add_url);
                $('#modal-list').modal('show');
            });
            $('table tbody').on('click', '.edit-button', function (ev) {
                ev.preventDefault();
                //alert('1');
                $('#modal-form').trigger("reset");
                var edit_url  = $(this).attr("href");
                $('#modal-form').attr("action",edit_url);
                $('#modal-list h4').html('Edit Discount');
                var cur_obj = $(this).prev("div.hiddenRowData");


                $("#Minutes-components").hide();
                var $item = $('#' + 'Minutes-components' + ' tr:last').attr('id');
                var numb = getNumber($item);
                for (var i =2; i<= numb;i++) {
                    deleteMinuteRow('minute-' + i, 'MinutesSubBox','getIDs','1');
                }

                $("#modal-form [id='" + 'MinutesComponent-' + (i + 1) + "']").select2().select2('val', '');
                $("#modal-form [name='" + 'MinutesDiscount-' + (i + 1) + "']").val('');
                $("#modal-form [name='" + 'MinutesTreshhold-' + (i + 1) + "']").val('');
                $("#modal-form [id='" + 'MinutesUnlimited-' + (i + 1) + "']").select2().select2('val', '');


                $("#Volume-components").hide();
                var $item = $('#' + 'Volume-components' + ' tr:last').attr('id');
                var numb = getNumber($item);
                for (var i =2; i<= numb;i++) {
                    deleteVolumeRow('volume-' + i, 'VolumeSubBox','getVolumeIDs','1');
                }
                $("#modal-form [id='" + 'VolumeComponent-' + (i + 1) + "']").select2().select2('val', '');
                $("#modal-form [name='" + 'VolumeFromMin-' + (i + 1) + "']").val('');
                $("#modal-form [name='" + 'VolumeToMin-' + (i + 1) + "']").val('');
                $("#modal-form [name='" + 'VolumeDiscount-' + (i + 1) + "']").val('');

                $("#Fixed-components").hide();
                var $item = $('#' + 'Fixed-components' + ' tr:last').attr('id');
                var numb = getNumber($item);

                for (var i =2; i<= numb;i++) {
                    deleteFixedRow('Fixed-' + i, 'FixedSubBox', 'getFixedIDs', '1');
                }
                $("#modal-form [id='" + 'FixedComponent-' + (1) + "']").select2().select2('val', '');
                $("#modal-form [name='" + 'FixedDiscount-' + (1) + "']").val('');

                $('#getIDs').val('');
                $('#getVolumeIDs').val('');
                $('#getFixedIDs').val('');

                var fullurl = baseurl + "/discount/getDiscountScheme";
                var data = [];
                data['DiscountID']= cur_obj.find("input[name='"+list_fields[6]+"']").val();
                fullurl = fullurl + "?DiscountID=" + data['DiscountID'];
                // alert(fullurl);
                var serviceID = cur_obj.find("input[name='"+list_fields[1]+"']").val();
               // alert(serviceID);$("#Volume-components").hide();
                if (serviceID == 2) {
                    $("#Minutes-components").show();
                    var $item = $('#' + 'Minutes-components' + ' tr:last').attr('id');
                    var numb = getNumber($item);
                    $('#getIDs').val(numb+',');
                    $.ajax({
                        url: fullurl, //Server script to process data
                        type: 'POST',
                        dataType: 'json',
                        success: function (response) {
                            if (response.status == 'success') {
                                // var obj = JSON.parse(response.message);
                                //alert(response.message.length);
                                var discountScheme;
                                var MinutesComponent = [];
                               // alert(numb);
                                for (var i = 0; i < response.message.length; i++) {
                                    discountScheme = response.message[i];
                                    var componentsArray = discountScheme['Components'].split(",");
                                    if (i != 0 && numb < response.message.length) {
                                        createMinuteCloneRow('MinutesSubBox', 'getIDs', componentsArray, discountScheme['Discount'], discountScheme['Threshold'], discountScheme['Unlimited']);
                                    } else {
                                        $("#modal-form [id='" + 'MinutesComponent-' + (i + 1) + "']").select2().select2('val', componentsArray);
                                        $("#modal-form [name='" + 'MinutesDiscount-' + (i + 1) + "']").val(discountScheme['Discount']);
                                        $("#modal-form [name='" + 'MinutesTreshhold-' + (i + 1) + "']").val(discountScheme['Threshold']);
                                        $("#modal-form [id='" + 'MinutesUnlimited-' + (i + 1) + "']").select2().select2('val', discountScheme['Unlimited']);

                                    }
                                }
                                markThresholdFieldReadOnly("MinutesUnlimited-1","MinutesTreshhold-1");
                            } else {
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                        },
                        data: data,
                        //Options to tell jQuery not to process data or worry about content-type.
                        cache: false
                    });
                }
                if (serviceID == `1`) {
                    $("#Volume-components").show();
                    var $item = $('#' + 'Volume-components' + ' tr:last').attr('id');
                    var numb = getNumber($item);
                    $('#getVolumeIDs').val(numb+',');
                    $.ajax({
                        url: fullurl, //Server script to process data
                        type: 'POST',
                        dataType: 'json',
                        success: function (response) {
                            if (response.status == 'success') {
                                // var obj = JSON.parse(response.message);
                                //alert(response.message.length);
                                var discountScheme;
                                var MinutesComponent = [];
                                // alert(numb);
                                for (var i = 0; i < response.message.length; i++) {
                                    discountScheme = response.message[i];
                                    var componentsArray = discountScheme['Components'].split(",");
                                    if (i != 0 && numb < response.message.length) {
                                        createVolumeCloneRow('VolumeSubBox','getVolumeIDs',componentsArray,discountScheme['FromMin'],discountScheme['ToMin'],discountScheme['Discount']);
                                    } else {

                                        $("#modal-form [id='" + 'VolumeComponent-' + (i + 1) + "']").select2().select2('val', componentsArray);
                                        $("#modal-form [name='" + 'VolumeFromMin-' + (i + 1) + "']").val(discountScheme['FromMin']);
                                        $("#modal-form [name='" + 'VolumeToMin-' + (i + 1) + "']").val(discountScheme['ToMin']);
                                        $("#modal-form [name='" + 'VolumeDiscount-' + (i + 1) + "']").val(discountScheme['Discount']);
                                    }
                                }

                            } else {
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                        },
                        data: data,
                        //Options to tell jQuery not to process data or worry about content-type.
                        cache: false
                    });
                }

                if (serviceID == `3`) {
                    $("#Fixed-components").show();
                    var $item = $('#' + 'Fixed-components' + ' tr:last').attr('id');
                    var numb = getNumber($item);
                    $('#getFixedIDs').val(numb+',');
                    $.ajax({
                        url: fullurl, //Server script to process data
                        type: 'POST',
                        dataType: 'json',
                        success: function (response) {
                            if (response.status == 'success') {
                                // var obj = JSON.parse(response.message);
                                //alert(response.message.length);
                                var discountScheme;
                                var MinutesComponent = [];
                                // alert(numb);
                                for (var i = 0; i < response.message.length; i++) {
                                    discountScheme = response.message[i];
                                    var componentsArray = discountScheme['Components'].split(",");
                                    if (i != 0 && numb < response.message.length) {
                                        createFixedCloneRow('FixedSubBox','getFixedIDs',componentsArray,discountScheme['Discount']);
                                    } else {

                                        $("#modal-form [id='" + 'FixedComponent-' + (i + 1) + "']").select2().select2('val', componentsArray);
                                        $("#modal-form [name='" + 'FixedDiscount-' + (i + 1) + "']").val(discountScheme['Discount']);

                                    }
                                }

                            } else {
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                        },
                        data: data,
                        //Options to tell jQuery not to process data or worry about content-type.
                        cache: false
                    });
                }
                for(var i = 0 ; i< list_fields.length; i++){
                    $("#modal-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                    if(list_fields[i] == 'DestinationGroupID'){
                        $("#modal-form [name='"+list_fields[i]+"']").select2().select2('val',cur_obj.find("input[name='"+list_fields[i]+"']").val());
                    }else if(list_fields[i] == 'Service'){
                        $("#modal-form [name='"+list_fields[i]+"']").attr('disabled', 'disabled');
                        $("#modal-form [name='"+list_fields[i]+"']").select2().select2('val',cur_obj.find("input[name='"+list_fields[i]+"']").val());
                    }else if(list_fields[i] == 'Unlimited') {
                        if (cur_obj.find("[name='Unlimited']").val() == 1) {
                            $('#modal-form [name="Unlimited"]').prop('checked', true)
                        } else {
                            $('#modal-form [name="Unlimited"]').prop('checked', false)
                        }
                    }
                }
                $('#modal-list').modal('show');
            });
            $('table tbody').on('click', '.delete-button', function (ev) {
                ev.preventDefault();
                result = confirm("Are you Sure?");
                if(result){
                    var delete_url  = $(this).attr("href");

                    submit_ajax_datatable( delete_url,"",0,data_table);
                }
                return false;
            });

            $("#modal-form").submit(function(e){
                e.preventDefault();
                if($('#modal-form [name="Unlimited"]').prop("checked") == false){
                    if($('#modal-form [name="Threshold"]').val() == 0){
                        setTimeout(function(){
                            $(".btn").button('reset');
                        },10);
                        toastr.error("Please Enter Value Greater then zero", "Error", toastr_opts);
                        return false;
                    }
                }
                var _url  = $(this).attr("action");
                submit_ajax_datatable(_url,$(this).serialize(),0,data_table);
            });

            function markThresholdFieldReadOnly(UnlimitedSelectBox,ThresholdField){
                if($('#modal-form [name=' + UnlimitedSelectBox +']').val() == 1){
                    $('#modal-form [name=' +ThresholdField + ']').val(0);
                    $('#modal-form [name=' + ThresholdField + ']').attr('readonly',true);
                }else {
                    $('#modal-form [name=' + ThresholdField + ']').attr('readonly',false);
                }
            }


            $("#MinutesUnlimited-1").bind("change", function() {
                markThresholdFieldReadOnly("MinutesUnlimited-1","MinutesTreshhold-1");
            });
           // $( "#MinutesUnlimited-1" ).bind( "change", markThresholdFieldReadOnly("MinutesUnlimited-1","MinutesTreshhold-1"));
           // $('#modal-form [name="MinutesUnlimited-1"]').on( "change",markThresholdFieldReadOnly("MinutesUnlimited-1","MinutesTreshhold-1"));

        });
    </script>

@stop
@section('footer_ext')
    @parent
    <div class="modal fade custom-width in " id="modal-list">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="modal-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add Discount</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Discount Type*</label>
                                    {{Form::select('Service',DiscountPlan::$discount_service, '' ,array("class"=>"form-control select2"))}}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Product Group*</label>
                                    {{Form::select('DestinationGroupID', $DestinationGroup, '' ,array("id"=>"DestinationGroupID","class"=>"form-control select2"))}}

                                </div>
                            </div>


                        </div>


                    </div>
                    <input type="hidden" name="DiscountID">
                    <input type="hidden" name="DiscountSchemeID">
                    <input type="hidden" name="DiscountPlanID" value="{{$id}}">
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary print btn-sm btn-icon icon-left" data-loading-text="Loading...">
                            <i class="entypo-floppy"></i>
                            Save
                        </button>
                        <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                            <i class="entypo-cancel"></i>
                            Close
                        </button>
                    </div>
                    <!-- Merge=Minutes -->
                    <div class="panel panel-primary" data-collapsed="0" id="Minutes-components">
                        <div class="panel-heading">
                            <div class="panel-title">
                                Minutes Components
                            </div>

                            <div class="panel-options">
                                <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="col-md-12">
                                <br/>
                                <input type="hidden" id="getIDs" name="getIDs" value=""/>
                                <table id="MinutesSubBox" class="table table-bordered datatable">
                                    <thead>
                                    <tr>
                                        <th width="30%">Component</th>
                                        <th width="15%">Discount</th>
                                        <th width="15%">Treshhold</th>
                                        <th width="20%">Unlimited</th>
                                        <th width="20%">Add</th>
                                    </tr>
                                    </thead>
                                    <tbody id="tbody">
                                    <tr id="MinutesRow-1">
                                        <td id="testValues">
                                            {{ Form::select('MinutesComponent-1[]', $DiscountPlanComponents, null, array("class"=>"select2 selected-Components" ,'multiple', "id"=>"MinutesComponent-1")) }}
                                        </td>
                                        <td>
                                            <input type="text" class="form-control popover-primary" data-trigger="hover" data-toggle="popover" data-placement="top" data-content="For Percentage,Specify 'p'" data-original-title="Discount" name="MinutesDiscount-1" id="MinutesDiscount-1"/>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="MinutesTreshhold-1" id="MinutesTreshhold-1"/>
                                        </td>
                                        <td>
                                            {{ Form::select('MinutesUnlimited-1',  DiscountPlan::$Unlimited, null, array("class"=>"select2", "id"=>"MinutesUnlimited-1")) }}

                                        </td>

                                        <td>
                                            <button type="button" onclick="createMinuteCloneRow('MinutesSubBox','getIDs','','','','')" id="minute-update" class="btn btn-primary btn-sm add-clone-row-btn" data-loading-text="Loading...">
                                                <i></i>
                                                +
                                            </button>
                                            <a onclick="deleteMinuteRow(this.id, 'MinutesSubBox','getIDs','0')" id="minute-0" class="btn btn-danger btn-sm hidden" data-loading-text="Loading..." >
                                                <i></i>
                                                -
                                            </a>
                                        </td>
                                    </tr>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Merge=Volume -->
                    <div class="panel panel-primary" data-collapsed="0" id="Volume-components">
                        <div class="panel-heading">
                            <div class="panel-title">
                                Volume Components
                            </div>

                            <div class="panel-options">
                                <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="col-md-12">
                                <br/>
                                <input type="hidden" id="getVolumeIDs" name="getVolumeIDs" value=""/>
                                <table id="VolumeSubBox" class="table table-bordered datatable">
                                    <thead>
                                    <tr>
                                        <th width="30%">Component</th>
                                        <th width="15%" >From</th>
                                        <th width="15%">To</th>
                                        <th width="20%">Discount</th>
                                        <th width="20%">Add</th>
                                    </tr>
                                    </thead>
                                    <tbody id="tbody">
                                    <tr id="VolumeRow-1">
                                        <td id="testValues">
                                            {{ Form::select('VolumeComponent-1[]', $DiscountPlanComponents, null, array("class"=>"select2 selected-Components" ,'multiple', "id"=>"VolumeComponent-1")) }}
                                        </td>
                                        <td>
                                            <input type="text" class="form-control popover-primary" data-trigger="hover" data-toggle="popover" data-placement="top" data-content="Please enter the number or > or < value" data-original-title="From" name="VolumeFromMin-1"/>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="VolumeToMin-1"/>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control popover-primary" data-trigger="hover" data-toggle="popover" data-placement="top" data-content="For Percentage,Specify 'p'" data-original-title="Discount"  name="VolumeDiscount-1"/>
                                        </td>

                                        <td>
                                            <button type="button" onclick="createVolumeCloneRow('VolumeSubBox','getVolumeIDs','','','','')" id="Volume-update" class="btn btn-primary btn-sm add-clone-row-btn" data-loading-text="Loading...">
                                                <i></i>
                                                +
                                            </button>
                                            <a onclick="deleteVolumeRow(this.id, 'VolumeSubBox','getVolumeIDs','0')" id="Volume-0" class="btn btn-danger btn-sm hidden" data-loading-text="Loading..." >
                                                <i></i>
                                                -
                                            </a>
                                        </td>
                                    </tr>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-primary" data-collapsed="0" id="Fixed-components">
                        <div class="panel-heading">
                            <div class="panel-title">
                                Fixed Components
                            </div>

                            <div class="panel-options">
                                <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="col-md-12">
                                <br/>
                                <input type="hidden" id="getFixedIDs" name="getFixedIDs" value=""/>
                                <table id="FixedSubBox" class="table table-bordered datatable">
                                    <thead>
                                    <tr>
                                        <th width="60%">Component</th>
                                        <th width="20%">Discount</th>
                                        <th width="20%">Add</th>
                                    </tr>
                                    </thead>
                                    <tbody id="tbody">
                                    <tr id="FixedRow-1">
                                        <td id="testValues">
                                            {{ Form::select('FixedComponent-1[]', $DiscountPlanComponents, null, array("class"=>"select2 selected-Components" ,'multiple', "id"=>"FixedComponent-1")) }}
                                        </td>

                                        <td>
                                            <input type="text" class="form-control popover-primary" data-trigger="hover" data-toggle="popover" data-placement="top" data-content="For Percentage,Specify 'p'" data-original-title="Discount" name="FixedDiscount-1"/>
                                        </td>

                                        <td>
                                            <button type="button" onclick="createFixedCloneRow('FixedSubBox','getFixedIDs','','');" id="Fixed-update" class="btn btn-primary btn-sm add-clone-row-btn" data-loading-text="Loading...">
                                                <i></i>
                                                +
                                            </button>
                                            <a onclick="deleteFixedRow(this.id, 'FixedSubBox','getFixedIDs','0')" id="Fixed-0" class="btn btn-danger btn-sm hidden" data-loading-text="Loading..." >
                                                <i></i>
                                                -
                                            </a>
                                        </td>
                                    </tr>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        function getNumber($item){
            var txt = $item;
            var numb = txt.match(/\d/g);
            numb = numb.join("");
            return numb;
        }

        $(document).ready(function() {
            $("#Minutes-components").hide();
            $("#Volume-components").hide();
            $("#Fixed-components").hide();

        });

        $("#modal-form [name='Service']").on('change', function() {

            var TypeValue = $(this).val();

            if(TypeValue == 2){

                $("#Minutes-components").show();
                $("#Volume-components").hide();
                $("#Fixed-components").hide();
                var $item = $('#' + 'Minutes-components' + ' tr:last').attr('id');
                var numb = getNumber($item);
                $('#getIDs').val(numb+',');

            }else if(TypeValue == 1){
                var $item = $('#' + 'Volume-components' + ' tr:last').attr('id');
                var numb = getNumber($item);
                $('#getVolumeIDs').val(numb+',');
                $("#Minutes-components").hide();
                $("#Volume-components").show();
                $("#Fixed-components").hide();
            }else if(TypeValue == 3){
                var $item = $('#' + 'Fixed-components' + ' tr:last').attr('id');
                var numb = getNumber($item);
                $('#getFixedIDs').val(numb+',');
                $("#Minutes-components").hide();
                $("#Volume-components").hide();
                $("#Fixed-components").show();

            } else {
                $("#Minutes-components").hide();
                $("#Volume-components").hide();
                $("#Fixed-components").hide();
            }

        });

        function markThresholdFieldReadOnly(UnlimitedSelectBox,ThresholdField){
            if($('#modal-form [name=' + UnlimitedSelectBox +']').val() == 1){
                $('#modal-form [name=' +ThresholdField + ']').val(0);
                $('#modal-form [name=' + ThresholdField + ']').attr('readonly',true);
            }else {
                $('#modal-form [name=' + ThresholdField + ']').attr('readonly',false);
            }
        }
        function createMinuteCloneRow(tblID, idInp,MinutesComponent,MinutesDiscount,MinutesTreshhold,MinutesUnlimited)
        {

            var $item = $('#' + tblID + ' tr:last').attr('id');
            var numb = getNumber($item);
            numb++;

            $("#"+$item).clone().appendTo('#' + tblID + ' tbody');

            var row =  "MinutesRow";


            $('#' + tblID + ' tr:last').attr('id', row + '-' + numb);
            $('#' + tblID + ' tr:last').children('td:eq(0)').children('select').attr('name', 'MinutesComponent-' + numb + '[]').attr('id', 'MinutesComponent-' + numb).select2().select2('val', MinutesComponent);
            $('#' + tblID + ' tr:last').children('td:eq(1)').children('input').attr('name', 'MinutesDiscount-' + numb).attr('id', 'MinutesDiscount-' + numb).val(MinutesDiscount);
            $('#' + tblID + ' tr:last').children('td:eq(2)').children('input').attr('name', 'MinutesTreshhold-' + numb).attr('id', 'MinutesTreshhold-' + numb).val(MinutesTreshhold);
            $('#' + tblID + ' tr:last').children('td:eq(3)').children('select').attr('name', 'MinutesUnlimited-' + numb).attr('id', 'MinutesUnlimited-' + numb).select2().select2('val', MinutesUnlimited);



            if($('#'+idInp).val() == '' ){
                $('#'+idInp).val(numb+',');
            }else{
                var getIDString =  $('#'+idInp).val();
                getIDString = getIDString + numb + ',';
                $('#'+idInp).val(getIDString);
            }



            $('#' + tblID + ' tr:last').closest('tr').children('td:eq(4)').children('a').attr('id', "minute-"+ numb);


            $('#' + tblID + ' tr:last').children('td:eq(0)').find('div:first').remove();
            $('#' + tblID + ' tr:last').children('td:eq(1)').find('div:first').remove();
            $('#' + tblID + ' tr:last').children('td:eq(2)').find('div:first').remove();
            $('#' + tblID + ' tr:last').children('td:eq(3)').find('div:first').remove();

            $('#' + tblID + ' tr:last').closest('tr').children('td:eq(4)').find('a').removeClass('hidden');
            markThresholdFieldReadOnly("MinutesUnlimited-" + numb,"MinutesTreshhold-" + numb);
            $("#MinutesUnlimited-" + numb).bind("change", function() {
                markThresholdFieldReadOnly("MinutesUnlimited-" + numb,"MinutesTreshhold-" + numb);

            });

        }

        function deleteMinuteRow(id, tblID, idInp,cacheDelete)
        {
            // alert(id + ' ' + tblID + ' ' + idInp);
            var confirmStatus = true;
            if (cacheDelete != '1') {
                confirmStatus = confirm("Are You Sure?");
            }
            if(confirmStatus) {
                var selectedSubscription = $('#'+idInp).val();
                var removeValue = id + ",";
                var removalueIndex = selectedSubscription.indexOf(removeValue);
                var firstValue = selectedSubscription.substr(0, removalueIndex);
                var lastValue = selectedSubscription.substr(removalueIndex + removeValue.length, selectedSubscription.length);
                var selectedSubscription = firstValue + lastValue;
                if (selectedSubscription.charAt(0) == ',') {
                    selectedSubscription = selectedSubscription.substr(1, selectedSubscription.length)
                }
                $('#'+idInp).val(selectedSubscription);


                var rowCount = $("#" + tblID + " > tbody").children().length;
                if (rowCount > 1) {
                    $("#" + id).closest("tr").remove();
                    getIds(tblID, idInp);

                } else {

                    toastr.error("You cannot delete. At least one component is required.", "Error", toastr_opts);
                }
                return false;
            }
        }

        function createVolumeCloneRow(tblID, idInp,VolumeComponentVal,VolumeFromMinVal,VolumeToMinVal,VolumeDiscountVal)
        {

            var $item = $('#' + tblID + ' tr:last').attr('id');
            var numb = getNumber($item);
            numb++;

            $("#"+$item).clone().appendTo('#' + tblID + ' tbody');

            var row =  "VolumeRow";


            $('#' + tblID + ' tr:last').attr('id', row + '-' + numb);
            $('#' + tblID + ' tr:last').children('td:eq(0)').children('select').attr('name', 'VolumeComponent-' + numb + '[]').attr('id', 'VolumeComponent-' + numb).select2().select2('val', VolumeComponentVal);
            $('#' + tblID + ' tr:last').children('td:eq(1)').children('input').attr('name', 'VolumeFromMin-' + numb).attr('id', 'VolumeFromMin-' + numb).val(VolumeFromMinVal);
            $('#' + tblID + ' tr:last').children('td:eq(2)').children('input').attr('name', 'VolumeToMin-' + numb).attr('id', 'VolumeToMin-' + numb).val(VolumeToMinVal);
            $('#' + tblID + ' tr:last').children('td:eq(3)').children('input').attr('name', 'VolumeDiscount-' + numb).attr('id', 'VolumeDiscount-' + numb).val(VolumeDiscountVal);


            if($('#'+idInp).val() == '' ){
                $('#'+idInp).val(numb+',');
            }else{
                var getIDString =  $('#'+idInp).val();
                getIDString = getIDString + numb + ',';
                $('#'+idInp).val(getIDString);
            }

           // alert(idInp + '' + $('#'+idInp).val());

            $('#' + tblID + ' tr:last').closest('tr').children('td:eq(4)').children('a').attr('id', "volume-"+ numb);



            $('#' + tblID + ' tr:last').children('td:eq(0)').find('div:first').remove();
            $('#' + tblID + ' tr:last').children('td:eq(1)').find('div:first').remove();
            $('#' + tblID + ' tr:last').children('td:eq(2)').find('div:first').remove();
            $('#' + tblID + ' tr:last').children('td:eq(3)').find('div:first').remove();

            $('#' + tblID + ' tr:last').closest('tr').children('td:eq(4)').find('a').removeClass('hidden');


        }

        function deleteVolumeRow(id, tblID, idInp,cacheDelete)
        {
            // alert(id + ' ' + tblID + ' ' + idInp);
            var confirmStatus = true;
            if (cacheDelete != '1') {
                confirmStatus = confirm("Are You Sure?");
            }
            if(confirmStatus) {
                var selectedSubscription = $('#'+idInp).val();
                var removeValue = id + ",";
                var removalueIndex = selectedSubscription.indexOf(removeValue);
                var firstValue = selectedSubscription.substr(0, removalueIndex);
                var lastValue = selectedSubscription.substr(removalueIndex + removeValue.length, selectedSubscription.length);
                var selectedSubscription = firstValue + lastValue;
                if (selectedSubscription.charAt(0) == ',') {
                    selectedSubscription = selectedSubscription.substr(1, selectedSubscription.length)
                }
                $('#'+idInp).val(selectedSubscription);


                var rowCount = $("#" + tblID + " > tbody").children().length;
                if (rowCount > 1) {
                    $("#" + id).closest("tr").remove();
                    getIds(tblID, idInp);

                } else {

                    toastr.error("You cannot delete. At least one component is required.", "Error", toastr_opts);
                }
                return false;
            }
        }


        function createFixedCloneRow(tblID, idInp,FixedComponentVal,FixedDiscountVal)
        {

            var $item = $('#' + tblID + ' tr:last').attr('id');
            var numb = getNumber($item);
            numb++;

            $("#"+$item).clone().appendTo('#' + tblID + ' tbody');

            var row =  "FixedRow";


            $('#' + tblID + ' tr:last').attr('id', row + '-' + numb);
            $('#' + tblID + ' tr:last').children('td:eq(0)').children('select').attr('name', 'FixedComponent-' + numb + '[]').attr('id', 'FixedComponent-' + numb).select2().select2('val', FixedComponentVal);
            $('#' + tblID + ' tr:last').children('td:eq(1)').children('input').attr('name', 'FixedDiscount-' + numb).attr('id', 'FixedDiscount-' + numb).val(FixedDiscountVal);


            if($('#'+idInp).val() == '' ){
                $('#'+idInp).val(numb+',');
            }else{
                var getIDString =  $('#'+idInp).val();
                getIDString = getIDString + numb + ',';
                $('#'+idInp).val(getIDString);
            }



            $('#' + tblID + ' tr:last').closest('tr').children('td:eq(2)').children('a').attr('id', "Fixed-"+ numb);



            $('#' + tblID + ' tr:last').children('td:eq(0)').find('div:first').remove();
            $('#' + tblID + ' tr:last').children('td:eq(1)').find('div:first').remove();


            $('#' + tblID + ' tr:last').closest('tr').children('td:eq(2)').find('a').removeClass('hidden');

        }

        function deleteFixedRow(id, tblID, idInp,cacheDelete)
        {
           // alert(id + ' ' + tblID + ' ' + idInp);
            var confirmStatus = true;
            if (cacheDelete != '1') {
                confirmStatus = confirm("Are You Sure?");
            }
            if(confirmStatus) {
                var selectedSubscription = $('#'+idInp).val();
               // alert(selectedSubscription);
                var removeValue = id + ",";
                var removalueIndex = selectedSubscription.indexOf(removeValue);
                var firstValue = selectedSubscription.substr(0, removalueIndex);
                var lastValue = selectedSubscription.substr(removalueIndex + removeValue.length, selectedSubscription.length);
                var selectedSubscription = firstValue + lastValue;
                if (selectedSubscription.charAt(0) == ',') {
                    selectedSubscription = selectedSubscription.substr(1, selectedSubscription.length)
                }
               // alert(selectedSubscription);
                $('#'+idInp).val(selectedSubscription);


                var rowCount = $("#" + tblID + " > tbody").children().length;
               // alert(rowCount + ' ' + id);
                if (rowCount > 1) {
                    $("#" + id).closest("tr").remove();
                    getIds(tblID, idInp);

                } else {

                    toastr.error("You cannot delete. At least one component is required.", "Error", toastr_opts);
                }
                return false;
            }
        }

        function getIds(tblID, idInp){
            $('#'+idInp).val("");
            $('#' + tblID + ' tbody tr').each(function() {

                var row = tblID == "servicetableSubBox" ? "selectedRow-0" : "selectedRateRow-0";
                var id = 0;
                if(this.id != row)
                    id = getNumber(this.id);

                var getIDString =  $('#'+idInp).val();
                getIDString = getIDString + id + ',';
                $('#'+idInp).val(getIDString);

            });
        }
    </script>
@stop
