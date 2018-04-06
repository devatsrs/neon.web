@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>FTP CDR Mapping</strong>
    </li>
</ol>
<h3>FTP CDR Mapping</h3>

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
                        FTP CDR Mapping
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
                        <label for="field-1" class="col-sm-2 control-label">CDR Upload (.csv)</label>
                        <div class="col-sm-4">
                            <input name="excel" type="file" class="form-control file2 inline btn btn-primary" data-label="
                            <i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" />

                        </div>
                    </div>

                    <p style="text-align: right;">

                        @if(User::checkCategoryPermission('CDR','Upload'))
                        <button id="upload" class="btn btn-primary btn-sm btn-icon icon-left" type="submit">
                            <i class="glyphicon glyphicon-circle-arrow-up"></i>
                            Upload
                        </button>
                        @endif
                    </p>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="row hidden" id="add-template">
    <div class="col-md-12">
        <form id="add-template-form" method="post">
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
                        <br />
                        <br />
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
                            <br />
                            <br />
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
                            <label for="field-1" class="col-sm-2 control-label">Connect DateTime</label>
                            <div class="col-sm-4">
                                {{Form::select('selection[connect_datetime]', array(),'',array("class"=>"select2 small"))}}
                            </div>

                            <label for="field-1" class="col-sm-2 control-label">Disconnect DateTime</label>
                            <div class="col-sm-4">
                                {{Form::select('selection[disconnect_datetime]', array(),'',array("class"=>"select2 small"))}}
                            </div>
                        </div>
                        <div class="form-group">
                            <br />
                            <br />
                            <label for="field-1" class="col-sm-2 control-label">Connect Date</label>
                            <div class="col-sm-4">
                                {{Form::select('selection[connect_date]', array(),'',array("class"=>"select2 small"))}}
                            </div>

                            <label for="field-1" class="col-sm-2 control-label">Connect Time</label>
                            <div class="col-sm-4">
                                {{Form::select('selection[connect_time]', array(),'',array("class"=>"select2 small"))}}
                            </div>
                        </div>
                        <div class="form-group">
                            <br />
                            <br />
                            <label for="field-1" class="col-sm-2 control-label">Billed Duration</label>
                            <div class="col-sm-4">
                                {{Form::select('selection[billed_duration]', array(),'',array("class"=>"select2 small"))}}
                            </div>

                            <label for="field-1" class="col-sm-2 control-label">Duration</label>
                            <div class="col-sm-4">
                                {{Form::select('selection[duration]', array(),'',array("class"=>"select2 small"))}}
                            </div>
                        </div>
                        <div class="form-group">
                            <br />
                            <br />
                            <label for="field-1" class="col-sm-2 control-label">Area Prefix</label>
                            <div class="col-sm-4">
                                {{Form::select('selection[area_prefix]', array(),'',array("class"=>"select2 small"))}}
                            </div>

                            <label for=" field-1" class="col-sm-2 control-label">Call ID</label>
                            <div class="col-sm-4">
                                {{Form::select('selection[CallID]',array(),'',array("class"=>"select2 small"))}}
                            </div>
                        </div>
                        <div class="form-group">
                            <br />
                            <br />
                            <label for=" field-1" class="col-sm-2 control-label">CLI</label>
                            <div class="col-sm-4">
                                {{Form::select('selection[cli]', array(),'',array("class"=>"select2 small"))}}
                            </div>
                            <label for="field-1" class="col-sm-2 control-label">CLD</label>
                            <div class="col-sm-4">
                                {{Form::select('selection[cld]', array(),'',array("class"=>"select2 small"))}}
                            </div>
                        </div>
                        <div class="form-group">
                            <br />
                            <br />
                            <label for="field-1" class="col-sm-2 control-label">Account*</label>
                            <div class="col-sm-4">
                                {{Form::select('selection[Account]', array(),'',array("class"=>"select2 small"))}}
                            </div>

                            <label for=" field-1" class="col-sm-2 control-label">Cost</label>
                            <div class="col-sm-4">
                                {{Form::select('selection[cost]', array(),'',array("class"=>"select2 small"))}}
                            </div>
                        </div>
                        <div class="form-group">
                            <br />
                            <br />
                            <label class="col-sm-2 control-label" for="field-1">Service</label>
                            <div class="col-sm-4">
                                {{ Form::select('selection[ServiceID]',$Services,'', array("class"=>"select2")) }}
                            </div>

                            <label class="col-sm-2 control-label" for="field-1">Trunk</label>
                            <div class="col-sm-4">
                                {{ Form::select('selection[TrunkID]',$Trunks,'', array("class"=>"select2")) }}
                            </div>

                        </div>
                        <div class="form-group">
                            <br />
                            <br />
                            <label for=" field-1" class="col-sm-2 control-label">Date Format</label>
                            <div class="col-sm-4">
                                {{Form::select('selection[DateFormat]',Company::$date_format ,'',array("class"=>"select2 small"))}}
                            </div>
                            <label for=" field-1" class="col-sm-2 control-label">Inbound/Outbound <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="If not selected then cdrs will be uploaded as outbound" data-original-title="Inbound/Outbound">?</span></label>
                            <div class="col-sm-4">
                                {{Form::select('selection[is_inbound]',array(),'',array("class"=>"select2 small"))}}
                            </div>

                        </div>
                        <div class="form-group">
                            <br />
                            <br />
                            <label for=" field-1" class="col-sm-2 control-label">Extension</label>
                            <div class="col-sm-4">
                                {{Form::select('selection[extension]',array() ,'',array("class"=>"select2 small"))}}
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
                    <input type="hidden" name="CompanyGatewayID" value="{{$CompanyGatewayID}}"> 
                    <button id="save_template" type="submit"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-floppy"></i>
                        Save
                    </button>
                    <button  type="button" onclick="location.reload();" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
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
            update_new_url = '{{URL::to('cdr_template/check_upload')}}';

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

        $("select[name=RateFormat]").change(function (ev) {
            if($(this).val() == '{{Company::CHARGECODE}}'){
                $(".chargecode").show();
            }else{
                $(".chargecode").hide();
            }

        });
        $(".chargecode").hide();

        $('#add-template').hide();
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
                update_new_url = '{{URL::to('cdr_template/ajaxfilegrid')}}';
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
                update_new_url = '{{URL::to('cdr_template/storeTemplate')}}';
                click_btn = $(this);
            });
            $('#add-template-form').submit(function(e){
                e.preventDefault();
                submit_ajaxbtn(update_new_url,$('#add-template-form').serialize()+'&'+$('#bulk_upload').serialize(),'',click_btn,1);
                return false;
            });

        $('#RateCDR').change(function(){
            if($('#RateCDR').is(":checked")){
                $("#trunk_dropdown").removeClass("hidden");
                $("#rate_dropdown").removeClass("hidden");
            }else{
                $("#trunk_dropdown").addClass("hidden");
                $("#rate_dropdown").addClass("hidden");
            }
            $('select[name=TrunkID]').select2("val","");
            $('select[name=RateFormat]').select2("val","");
            $('select[name=RateFormat]').trigger('change');
            $('select[name=ServiceID]').select2("val","");

        });
        $('#RateCDR').trigger('change');
    });

    $('#bulk_upload [name="FileUploadTemplateID"]').change(function(){
        console.log($(this).select2('data').text);
        $('#add-template-form [name="TemplateName"]').val($(this).val()==''?'':$(this).select2('data').text);
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
</style>
@stop
@section('footer_ext')
@parent

@stop
