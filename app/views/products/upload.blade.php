@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>
        <a href="{{URL::to('products')}}">Items</a>
    </li>
    <li class="active">
        <strong>Upload Items</strong>
    </li>
</ol>
<h3>Items Upload</h3>

@include('includes.errors')
@include('includes.success')
<div class="tab-content">
    <div class="tab-pane active">
        <div class="row">
            <div class="col-md-12">
                <form novalidate class="form-horizontal form-groups-bordered validate" method="post" id="bulk_upload" enctype="multipart/form-data">
                    <div data-collapsed="0" class="panel panel-primary">
                        <div class="panel-heading">
                            <div class="panel-title">
                                Bulk Items Upload
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="field-1">Upload Template</label>
                                <div class="col-sm-3">
                                    {{ Form::select('FileUploadTemplateID', $UploadTemplate, '' , array("class"=>"select2 small")) }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="field-1" class="col-sm-2 control-label">Upload (.xls, .xlxs, .csv)</label>
                                <div class="col-sm-4">
                                    <input name="excel" type="file" class="form-control file2 inline btn btn-primary" data-label="
                            <i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" />

                                </div>
                            </div>

                            <!--<div class="form-group">
                                <label class="col-sm-2 control-label">Settings</label>

                                <div class="col-sm-10">
                                    <div class="checkbox ">
                                        <label>
                                            <input type="hidden" name="RateCDR" value="0" >
                                            <input type="checkbox" id="RateCDR" name="RateCDR" value="1" > Rate CDR</label>

                                    </div>
                                    <div class="checkbox ">
                                        <input type="hidden" name="CheckFile" value="0" >
                                        <label><input type="checkbox" id="rd-1" name="CheckFile" checked value="1"> Verify this file</label>
                                    </div>
                                    <div class="checkbox " id="IgnoreNotRated">
                                        <input type="hidden" name="IgnoreZeroRatedCall" value="0" >
                                        <label><input type="checkbox" id="rd-1" name="IgnoreZeroRatedCall" value="1"> Ignore zero rated calls</label>
                                    </div>
                                </div>
                            </div>-->

                            <div class="form-group">
                                <label for="field-1" class="col-sm-2 control-label">Note</label>
                                <div class="col-sm-8">
                                    <p><i class="glyphicon glyphicon-minus"></i><strong>Allowed Extension</strong> .xls, .xlxs, .csv</p>
                                    <p>Please upload the file in given <span class="label label-info" onclick="jQuery('#modal-fileformat').modal('show');" style="cursor: pointer">File Format</span> </p>
                                    <p>Sample File <a href="{{URL::to('products_upload/download_sample_excel_file')}}" class="btn btn-success btn-sm btn-icon icon-left"><i class="entypo-down"></i>Download</a> </p>
                                </div>
                            </div>
                            <p style="text-align: right;">

                                {{--@if(User::checkCategoryPermission('ITEM','Upload'))--}}
                                <button id="upload" class="btn btn-primary btn-sm btn-icon icon-left" type="submit">
                                    <i class="glyphicon glyphicon-circle-arrow-up"></i>
                                    Upload
                                </button>
                                {{--@endif--}}
                            </p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="row hidden" id="add-template">
            <div class="col-md-12">
                <form id="add-template-form" method="post" class="form-horizontal">
                    <div class="panel panel-primary" data-collapsed="0">
                        <div class="panel-heading">
                            <div class="panel-title">
                                Add New Template
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label for="field-1" class="col-sm-2 control-label">Template Name:</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" name="TemplateName" value="" />
                                </div>
                            </div>
                        </div>
                        <div class="panel-heading">
                            <div class="panel-title">
                                Call Rate Rules CSV Importer
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label for="field-1" class="col-sm-2 control-label">Delimiter:</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" name="option[Delimiter]" value="," />
                                    <input type="hidden" name="TemplateFile" value="" />
                                </div>
                                <label for="field-1" class="col-sm-2 control-label">Enclosure:</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" name="option[Enclosure]" value="" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Escape:</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" name="option[Escape]" value="" />
                                </div>
                                <label for="field-1" class="col-sm-2 control-label">First row:</label>
                                <div class="col-sm-4">
                                    {{Form::select('option[Firstrow]', array('columnname'=>'Column Name','data'=>'Data'),'',array("class"=>"select2 small"))}}
                                </div>
                            </div>
                            <p style="text-align: right;">
                                <br />
                                <button id="check_upload" class="check btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                                    <i class="entypo-floppy"></i>
                                    Check
                                </button>
                            </p>
                        </div>
                    </div>
                    <div class="panel panel-primary" data-collapsed="0">
                        <div class="panel-heading">
                            <div class="panel-title">
                                Field Remapping
                            </div>

                            <div class="panel-options">
                                <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                            </div>
                        </div>

                        <div class="panel-body" id="mapping">
                            <div class="form-group">
                                <label for="field-1" class="col-sm-2 control-label">Name *</label>
                                <div class="col-sm-4">
                                    {{Form::select('selection[Name]', array(),'',array("class"=>"select2 small"))}}
                                </div>

                                <label for="field-1" class="col-sm-2 control-label">Code *</label>
                                <div class="col-sm-4">
                                    {{Form::select('selection[Code]', array(),'',array("class"=>"select2 small"))}}
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="field-1" class="col-sm-2 control-label">Description *</label>
                                <div class="col-sm-4">
                                    {{Form::select('selection[Description]', array(),'',array("class"=>"select2 small"))}}
                                </div>

                                <label for="field-1" class="col-sm-2 control-label">Unit Cost *</label>
                                <div class="col-sm-4">
                                    {{Form::select('selection[Amount]', array(),'',array("class"=>"select2 small"))}}
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="field-1" class="col-sm-2 control-label">Action</label>
                                <div class="col-sm-4">
                                    {{Form::select('selection[Action]', array(),'',array("class"=>"select2 small"))}}
                                </div>
                                <label for="field-1" class="col-sm-2 control-label">Action Insert</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" name="selection[ActionInsert]" value="I" />
                                </div>

                            </div>
                            <div class="form-group">
                                <label for="field-1" class="col-sm-2 control-label">Action Update</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" name="selection[ActionUpdate]" value="U" />
                                </div>
                                <label for="field-1" class="col-sm-2 control-label">Action Delete</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" name="selection[ActionDelete]" value="D" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="field-1" class="col-sm-2 control-label">Note</label>
                                <div class="col-sm-4">
                                    {{Form::select('selection[Note]', array(),'',array("class"=>"select2 small"))}}
                                </div>

                                @if (isset($DynamicFields) && $DynamicFields['totalfields'] > 0)
                                    <?php $l=0; ?>
                                    @foreach($DynamicFields['fields'] as $field)
                                        @if($field->Status == 1)
                                            @if($l%2 != 0)
                                                </div>
                                                <div class="form-group">
                                            @endif
                                            <label for="field-1" class="col-sm-2 control-label">
                                                @if($field->ItemTypeID > 0)
                                                    {{getItemType($field->ItemTypeID)}}_{{ $field->FieldName }}
                                                @else
                                                    {{ $field->FieldName }}
                                                @endif
                                            </label>
                                            <div class="col-sm-4">
                                                {{Form::select('selection[DynamicFields-'.$field->DynamicFieldsID.']', array(),'',array("class"=>"select2 small"))}}
                                            </div>
                                            <?php $l++; ?>
                                        @endif
                                    @endforeach
                                @endif

                            </div>
                            <div class="form-group">
                                <label for="field-1" class="col-sm-2 control-label">AppliedTo</label>
                                <div class="col-sm-4">
                                    {{Form::select('selection[AppliedTo]', array(),'',array("class"=>"select2 small"))}}
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="panel panel-primary" data-collapsed="0">
                        <div class="panel-heading">
                            <div class="panel-title">
                                CSV File to be loaded
                            </div>

                            <div class="panel-options">
                                <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                            </div>
                        </div>

                        <div class="panel-body scrollx">
                            <table class="table table-bordered datatable" id="table-4">
                                <thead>
                                <tr>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button id="save_template" type="submit"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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
</div>
<script type="text/javascript">
    var $searchFilter = {};
    var update_new_url;
    var postdata;
    var click_btn;
    jQuery(document).ready(function ($) {
        public_vars.$body = $("body");
        //show_loading_bar(40);
        $('#bulk_upload').submit(function(e){
            e.preventDefault();
            update_new_url = '{{URL::to('products/check_upload')}}';

            var formData = new FormData($('#bulk_upload')[0]);
            show_loading_bar(0);
            $.ajax({
                url: update_new_url,  //Server script to process data
                type: 'POST',
                dataType: 'json',
                beforeSend: function(){
                    $('.btn.upload').button('loading');
                    show_loading_bar({
                        pct: 50,
                        delay: 5
                    });

                },
                afterSend: function(){
                    console.log("Afer Send");
                },
                success: function (response) {
                    show_loading_bar({
                        pct: 100,
                        delay: 2
                    });

                    if (response.status == 'success') {
                        toastr.success(response.message, "Success", toastr_opts);
                        reloadJobsDrodown(0);
                        var data = response.data;
                        $('#add-template').removeClass('hidden');
                        var scrollTo = $('#add-template').offset().top;
                        $('html, body').animate({scrollTop:scrollTo}, 1000);
                        createGrid(data);
                    } else {
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                    //alert(response.message);
                    $('.btn.upload').button('reset');
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
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });

//        $('#add-template').hide();
        $(document).ajaxSuccess(function( event, jqXHR, ajaxSettings, ResponseData ) {
            if (ResponseData.status != undefined &&  ResponseData.status == 'success' && ResponseData.data) {
                createGrid(ResponseData.data);
                $('#add-template').show();
                var scrollTo = $('#add-template').offset().top;
                $('html, body').animate({scrollTop:scrollTo}, 1000);
            }
        });
        $('#check_upload').click(function(e){

            var btn = $(this);
            btn.button('loading');
            $('#table-4_processing').removeClass('hidden');
            update_new_url = '{{URL::to('products/ajaxfilegrid')}}';
            data =$('#add-template-form').serialize()+'&'+$("#bulk_upload").serialize();
            $.ajax({
                url:update_new_url, //Server script to process data
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    btn.button('reset');
                    if (response.status == 'success') {
                        var data = response.data;
                        createGrid(data);
                        toastr.success(response.message, "Success", toastr_opts);
                    } else {
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                },
                data: data,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false
            });
            $('#table-4_processing').addClass('hidden');
            e.preventDefault();
        });
        $("#save_template").click(function(e){
            update_new_url = '{{URL::to('products/storeTemplate')}}';
            click_btn = $(this);
        });
        $('#add-template-form').submit(function(e){
            e.preventDefault();
            submit_ajaxbtn(update_new_url,$('#add-template-form').serialize()+'&'+$('#bulk_upload').serialize(),'',click_btn,1);
            return false;
        });

    });
    function createGrid(data){
        var tr = $('#table-4 thead tr');
        var body = $('#table-4 tbody');
        tr.empty();
        body.empty();
        $.each( data.columns, function( key, value ) {
            tr.append('<th>'+value+'</th>');
        });

        $.each( data.rows, function(key, row) {
            var tr = '<tr>';
            $.each( row, function(key, item) {
                tr+='<td>'+item+'</td>';
            });
            tr += '</tr>';
            body.append(tr);
        });
        $("#mapping select").each(function(i, el){
            if(el.name !='selection[DateFormat]' && el.name != 'selection[Authentication]' && el.name != 'selection[InboundRateTableID]' && el.name != 'selection[OutboundRateTableID]' && el.name != 'selection[ServiceID]' && el.name != 'selection[TrunkID]'){
                var self = $('#add-template-form [name="'+el.name+'"]');
                rebuildSelect2(self,data.columns,'Skip loading');
            }else if( el.name == 'selection[ServiceID]' || el.name == 'selection[TrunkID]'){
                var self = $('#add-template-form [name="'+el.name+'"]');
                var label = 'Map From File';
                rebuildSelectComposite(self,data.columns,label);
            }
        });
        if(data.FileUploadTemplate){
            $.each( data.FileUploadTemplate, function( optionskey, option_value ) {
                if(optionskey == 'Title'){
                    $('#add-template-form').find('[name="TemplateName"]').val(option_value)
                }
                if(optionskey == 'Options'){
                    $.each( option_value.option, function( key, value ) {

                        if(typeof $("#add-template-form [name='option["+key+"]']").val() != 'undefined'){
                            $('#add-template-form').find('[name="option['+key+']"]').val(value)
                            if(key == 'Firstrow'){
                                $("#add-template-form [name='option["+key+"]']").val(value).trigger("change");
                            }
                        }

                    });
                    $.each( option_value.selection, function( key, value ) {
                        if(typeof $("#add-template-form input[name='selection["+key+"]']").val() != 'undefined'){
                            $('#add-template-form').find('input[name="selection['+key+']"]').val(value)
                        }else if(typeof $("#add-template-form select[name='selection["+key+"]']").val() != 'undefined'){
                            $("#add-template-form [name='selection["+key+"]']").val(value).trigger("change");
                        }
                    });
                }
            });
        }else{
            $('#add-template-form').find('[name="TemplateName"]').val('');
        }

        $('#add-template-form').find('[name="TemplateFile"]').val(data.filename);
        $('#add-template-form').find('[name="TempFileName"]').val(data.tempfilename);
    }

</script>
<style>
    .dataTables_filter label{
        display:none !important;
    }
    .dataTables_wrapper .export-data{
        right: 30px !important;
    }
    .radio label{
        min-height:16px !important;
    }
</style>
@stop
@section('footer_ext')
    @parent
    <div class="modal fade" id="modal-fileformat">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Item File Format</h4>
                </div>



                <div class="modal-body">
                    <p>The first line should have the column headings.</p>
                    <table class="table responsive">
                        <thead>
                        <tr>
                            <th>Name *</th>
                            <th>Code *</th>
                            <th>Description *</th>
                            <th>Unit Cost *</th>
                            <th>Note</th>
                            <th>Barcode</th>
                            <th>AppliedTo</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>abc</td>
                            <td>v1</td>
                            <td>abc product</td>
                            <td>50</td>
                            <td>abc note</td>
                            <td>111</td>
                            <td>customer</td>
                            <td>I <span data-original-title="Insert" data-content="When action is set to 'I', It will insert new Item" data-placement="top" data-trigger="hover" data-toggle="popover" class="label label-info popover-primary">?</span></td>
                        </tr>
                        <tr>
                            <td>pqr</td>
                            <td>v2</td>
                            <td>pqr product</td>
                            <td>100</td>
                            <td>pqr note</td>
                            <td>111</td>
                            <td>customer</td>
                            <td>U <span data-original-title="Update" data-content="When action is set to 'U',It will replace existing Item" data-placement="top" data-trigger="hover" data-toggle="popover" class="label label-info popover-primary">?</span></td>
                        </tr>
                        <tr>
                            <td>xyz</td>
                            <td>v3</td>
                            <td>xyz product</td>
                            <td>75</td>
                            <td>xyz note</td>
                            <td></td>
                            <td>reseller</td>
                            <td>D <span data-original-title="Delete" data-content="When action is set to 'D',It will delete existing Item" data-placement="top" data-trigger="hover" data-toggle="popover" class="label label-info popover-primary">?</span></td>
                        </tr>
                        </tbody>
                    </table>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@stop