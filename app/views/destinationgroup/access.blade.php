@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <div id="table_filter" method="get" action="#" >
                <div class="form-group">
                    <label for="field-1" class="control-label">Name</label>
                    <input type="text" name="Name" class="form-control" value="" />
                </div>
                @if(!empty($typename))

                   @if($typename == 'Termination')

                                <div class="form-group">
                                    <label for="field-5" class="control-label">Country</label>
                                    {{ Form::select('CountryID', $countries , '' , array("class"=>"select2")) }}
                                </div>
                            
                        
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Type</label>
                                    {{ Form::select('Type', $terminationtype , '' , array("class"=>"select2")) }}
                                </div>

                   @endif

                   @if($typename == 'Access')

                   <div class="form-group">
                                    <label for="field-5" class="control-label">Country</label>
                                    {{ Form::select('CountryID', $countries , '' , array("class"=>"select2")) }}
                                </div>
                            
                        
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Type</label>
                                    {{ Form::select('Type', $AccessTypes , '' , array("class"=>"select2")) }}
                                </div>

                                <div class="form-group">
                                    <label for="field-5" class="control-label">Prefix</label>
                                    {{ Form::select('Prefix', $Prefix , '' , array("class"=>"select2")) }}
                                </div>
                            
                        
                                <div class="form-group">
                                    <label for="field-5" class="control-label">City</label>
                                   {{ Form::select('City', $City , '' , array("class"=>"select2")) }}
                                </div>

                                <div class="form-group">
                                    <label for="field-5" class="control-label">Tariff</label>
                                   {{ Form::select('Tariff', $CityTariffFilter , '' , array("class"=>"select2")) }}
                                </div>
                            
                               
                   @endif

                   @if($typename == 'Package')
                   <div class="form-group">
                        <label for="field-5" class="control-label">Package</label>
                         {{ Form::select('PackageID', $Packages , '' , array("class"=>"select2")) }}
                    </div>
                   @endif

                @endif
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-md btn-icon icon-left" id="filter_submit">
                        <i class="entypo-search"></i>
                        Search
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop


@section('content')
    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li>
            <a href="{{URL::to('destination_group_set')}}">Product Group Set </a>
        </li>
        <li class="active">
            <strong>Product Group ({{$name}})</strong>
        </li>
    </ol>
    <h3>Product Group </h3>
    <p style="text-align: right;">
        @if(User::checkCategoryPermission('DestinationGroup','Edit'))
        @if($discountplanapplied ==0)
        <a  id="add-button" class=" btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-plus"></i>Add New</a>
        @endif
        @endif
        <a href="{{URL::to('/destination_group_set')}}" class="btn btn-danger btn-sm btn-icon icon-left">
            <i class="entypo-cancel"></i>
            Close
        </a>
    </p>
    @include('includes.errors')
    @include('includes.success')


    <table id="table-list" class="table table-bordered datatable">
        <thead>
        <tr>
            <th width="20%">Name</th>
            <th width="10%">Country</th>
            <th width="10%">Type</th>
            <th width="10%">Prefix</th>
            <th width="10%">City</th>
            <th width="10%">Tariff</th>
            <th width="10%">Created By</th>
            <th width="10%">Created</th>
            <th width="30%">Action</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    <style>
    #table-extra{padding:0px margin:0px;}
    .hidediv {display: none;}
    .showdiv {display: block;}
    </style>
    <script type="text/javascript">
        /**
         * JQuery Plugin for dataTable
         * */
        var data_table_list;
        var update_new_url;
        var postdata;
        var add_url = baseurl + "/destination_group/store";
        var edit_url = baseurl + "/destination_group/update_name/{id}";
        var view_url = baseurl + "/destination_group/show/{id}";
        var delete_url = baseurl + "/destination_group/delete/{id}";
        var datagrid_url = baseurl + "/destination_group/ajax_datagrid";
        var datagrid_extra_url = baseurl + "/destination_group_code/ajax_datagrid";
        var checked='';

        jQuery(document).ready(function ($) {

            $('#filter-button-toggle').show();
var nm = 'king';
            var list_fields  = ["Name","CountryID","Type","Prefix","City","Tariff","CreatedBy","created_at","DestinationGroupID","DestinationGroupSetID","CompanyID"];
            //public_vars.$body = $("body");
            var $search = {};


            $("#filter_submit").click(function(e) {
                e.preventDefault();

                $search.Name = $("#table_filter").find('[name="Name"]').val();
                $search.CountryID = $("#table_filter").find('[name="CountryID"]').val();
                $search.Type = $("#table_filter").find('[name="Type"]').val();
                $search.Prefix = $("#table_filter").find('[name="Prefix"]').val();
                $search.City = $("#table_filter").find('[name="City"]').val();
                $search.Tariff = $("#table_filter").find('[name="Tariff"]').val();
                $search.PackageID = $("#table_filter").find('[name="PackageID"]').val();

                data_table = $("#table-list").dataTable({
                    
                    "bDestroy": true,
                    "bProcessing":true,
                    "bServerSide": true,
                    "sAjaxSource": datagrid_url,
                    "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                    "sPaginationType": "bootstrap",
                    "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                    "aaSorting": [[0, 'asc']],
                    "fnServerParams": function (aoData) {
                        aoData.push(
                                {"name": "Name", "value": $search.Name},
                                {"name": "CountryID", "value": $search.CountryID},
                                {"name": "Type", "value": $search.Type},
                                {"name": "Prefix", "value": $search.Prefix},
                                {"name": "City", "value": $search.City},
                                {"name": "Tariff", "value": $search.Tariff},
                                {"name": "PackageID", "value": $search.PackageID},
                                {"name": "DestinationGroupSetID", "value": '{{$DestinationGroupSetID}}'}

                        );
                        data_table_extra_params.length = 0;
                        data_table_extra_params.push(
                                {"name": "Name", "value": $search.Name},
                                {"name": "DestinationGroupSetID", "value": '{{$DestinationGroupSetID}}'},
                                {"name": "Export", "value": 1}
                        );

                    },
                    
                    "aoColumns": [
                        {  "bSortable": true },  // 0 Name
                        {  "bSortable": true },  // country
                        {  "bSortable": true },  // type
                        {  "bSortable": true }, //prefix
                        {  "bSortable": true },  //city
                        {  "bSortable": true },  //Tariff
                        {  "bSortable": true }, //created by
                        {  "bSortable": true },  //created at
                        {  "bSortable": false,
                            mRender: function ( id, type, full ) {
                                action = '<div class = "hiddenRowData" >';
                                for(var i = 0 ; i< list_fields.length; i++){
                                    action += '<input disabled type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null?full[i]:'')+ '" / >';
                                }
                                action += '</div>';
                                @if(User::checkCategoryPermission('DestinationGroup','Edit'))
                                action += ' <a href="' + edit_url.replace("{id}",id) +'" title="Edit" class="edit-button btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>'
                                @endif
                                //action += ' <a href="' + view_url.replace("{id}",id) +'" title="View" class="view-button btn btn-default btn-sm"><i class="fa fa-eye"></i></a>'
                                @if($discountplanapplied ==0)
                                @if(User::checkCategoryPermission('DestinationGroup','Delete'))
                                action += ' <a href="' + delete_url.replace("{id}",id) +'" title="Delete" class="delete-button btn btn-danger btn-sm"><i class="entypo-trash"></i></a>'
                                @endif
                                @endif
                                return action;
                            }
                        },  // 0 Created


                    ],
                    "oTableTools": {
                        "aButtons": [
                            {
                                "sExtends": "download",
                                "sButtonText": "Export Data",
                                "sUrl": datagrid_url,
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

            });




            $('#filter_submit').trigger('click');
            //inst.myMethod('I am a method');
            $('#add-button').click(function(ev){
                ev.preventDefault();
               

                $('#modal-form-data').trigger("reset");
                $('#modal-list .panel-title').html('Add Product Group');
                $("#modal-form-data [name=DestinationGroupID]").val("");
                $("#modal-form-data [name=CountryID]").select2('val',"");
                $("#modal-form-data [name=Type]").select2('val',"");
                $("#modal-form-data [name=PackageID]").select2('val',"");
                $("#modal-form-data [name=DestinationGroupSetID]").val("{{$DestinationGroupSetID}}");
                $('#modal-form-data').attr("action",add_url);
                $("#showmodal").hide();
                $("#showmodal_new").show();
                $('#modal-list').modal('show');
                 

            });
            
            $('table tbody').on('click', '.edit-button', function (ev) {
                ev.preventDefault();
                $('#modal-form-data').trigger("reset");

                var edit_url  = $(this).attr("href");
                $("#showmodal_new").hide();
                $("#showmodal").show();
                $('#modal-form-data').attr("action",edit_url);
                $('#modal-list .panel-title').html('Edit Product Group');
                
                var cur_obj = $(this).prev("div.hiddenRowData");

                for(var i = 0 ; i< list_fields.length; i++){
                    if(list_fields[i] == 'CountryID')
                    {   

                        var select2value = cur_obj.find("[name="+list_fields[i]+"]").val();

                        
                        $("#modal-form-data [name='"+list_fields[i]+"']").select2('data',{id: select2value, text: select2value});
                        
                    } 
                    else if(list_fields[i] == 'Type')
                    {   
                        var select2value = cur_obj.find("[name="+list_fields[i]+"]").val();
                        $("#modal-form-data [name='"+list_fields[i]+"']").select2('data',{id: select2value, text: select2value});
                    } 

                    else if(list_fields[i] == 'City')
                    {   
                        var select2value = cur_obj.find("[name="+list_fields[i]+"]").val();
                        $("#modal-form-data [name='"+list_fields[i]+"']").select2('data',{id: select2value, text: select2value});
                    } else if(list_fields[i] == 'Tariff')
                    {
                        var select2value = cur_obj.find("[name="+list_fields[i]+"]").val();
                        $("#modal-form-data [name='"+list_fields[i]+"']").select2('data',{id: select2value, text: select2value});
                    }
                    else if(list_fields[i] == 'Prefix')
                    {
                        var select2value = cur_obj.find("[name="+list_fields[i]+"]").val();
                        $("#modal-form-data [name='"+list_fields[i]+"']").select2('data',{id: select2value, text: select2value});
                    }
                    else if(list_fields[i] == 'PackageID')
                    {   
                        var select2value = cur_obj.find("[name="+list_fields[i]+"]").val();
                        $("#modal-form-data [name='"+list_fields[i]+"']").select2('data',{id: select2value, text: select2value});
                    } 

                     else {
                    $("#modal-form-data [name='"+list_fields[i]+"']").val(cur_obj.find("[name='"+list_fields[i]+"']").val());
                    }
                }
                $("#newdata").hide();
                $("#newdata").empty();
                $('#modal-list').modal('show');
                
            });

            $('table tbody').on('click', '.delete-button', function (ev) {
                ev.preventDefault();
                result = confirm("Are you Sure?");
                if(result){
                    var delete_url  = $(this).attr("href");
                    submit_ajax_datatable( delete_url,"",0,data_table);
                    
                }
            });

            $("#modal-form-data").submit(function(err){
                err.preventDefault();
                var _url  = $(this).attr("action");
                submit_ajax_datatable(_url,$(this).serialize(),0,data_table);
                data_table.fnFilter("",0);
                $("#newdata").hide();
            });

                $("#showmodal").click(function(){
                    $("#editdata").empty();
                    $("#editdata").html("<div align='center'>loading...</div>");
                    var dgid = $("input[name='DestinationGroupID']").val();
                var dgsid = $("input[name='DestinationGroupSetID']").val();
                var stype = "{{$typename}}";
                    //$("#appcodes").load(baseurl+"/destination_group/loadappliedcodes",{DestinationGroupID:dgid, DestinationGroupSetID:dgsid});
                    var countries = '{{json_encode($countries)}}';
                        $("#editdata").load(baseurl+"/destination_group_code/codelist", {DestinationGroupID:dgid, DestinationGroupSetID:dgsid, iDisplayStart:0, iDisplayLength:0, countries:countries, stype:stype });
                    $('#modal_codes').modal('show');
                });

                $("#close_codes").click(function(){
                    $('#modal_codes').modal('hide');
                });
$("#newdata").hide();
                $("#showmodal_new").click(function(){
                    $("#newdata").toggle();
                    $("#newdata").html("<div align='center'>loading...</div>");
                    var gdsids =  $("[name=DestinationGroupSetID]").val();
               $("#newdata").load(baseurl+"/destination_group_code/codelists", {DestinationGroupID:0, DestinationGroupSetID:gdsids, iDisplayStart:0, iDisplayLength:0 });
                    //$('#modal_codes_new').modal('show');
                });
                
                $("#close_codes_new").click(function(){
                   // $('#modal_codes_new').modal('hide');
                    
                });

        });
    </script>




@stop
@section('footer_ext')
    @parent


    <div class="modal fade in" id="modal-list">
        <div class="modal-dialog">
            <div class="modal-content">
                
<form id="modal-form-data" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" data-target="#modal-list" aria-hidden="true">×</button>
                        <h4 class="modal-title">Add Product Group</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Name</label>
                                    <input type="text" name="Name" class="form-control" value="" />
                                </div>
                            </div>
                        </div>
                        @if(!empty($typename))

                        @if($typename == 'Termination')
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Country</label>
                                    {{ Form::select('CountryID', $countries , '' , array("class"=>"select2")) }}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Type</label>
                                    {{ Form::select('Type', $terminationtype , '' , array("class"=>"select2")) }}
                                </div>
                            </div>
                        </div>

                        
                        <div class="row">
                            <div class="col-md-12">
                                <button type="button" id="showmodal" class="btn btn-primary">Display Codes</button>
                                <button type="button" id="showmodal_new" class="btn btn-primary">Display Codes</button>
                                
                                
                            </div>
                        </div>

                        <br>
                        @elseif($typename == 'Access')
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Country</label>
                                    {{ Form::select('CountryID', $countries , '' , array("class"=>"select2")) }}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Type</label>
                                    {{ Form::select('Type', $AccessTypes , '' , array("class"=>"select2")) }}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Prefix</label>
                                    {{ Form::select('Prefix', $Prefix , '' , array("class"=>"select2")) }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">City</label>
                                   {{ Form::select('City', $City , '' , array("class"=>"select2")) }}
                                </div>
                            </div>
                        </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="field-5" class="control-label">Tariff</label>
                                            {{ Form::select('Tariff', $Tariff , '' , array("class"=>"select2")) }}
                                        </div>
                                    </div>
                                </div>

                        @elseif($typename == 'Package')
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Package</label>
                                    {{ Form::select('PackageID', $Packages , '' , array("class"=>"select2")) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    @endif
                    @endif
                    
                    <input type="hidden" name="DestinationGroupID">
                    <input type="hidden" name="DestinationGroupSetID">
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

                    <div id="newdata" style="height:400px; overflow-y: scroll;"> </div>

                </form>
            </div>
        </div>
    </div>
   
                                
                            
                            
                            <div class="modal fade in" id="modal_codes">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" id="close_codes">×</button>
                                                <h4 class="modal-title">Select Codes</h4>
                                            </div>
                                            <div class="modal-body">
                                                
                                            <div id="editdata">
                                               
                                            </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                            
@stop
