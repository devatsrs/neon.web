@extends('layout.main')
@section('content')
    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li>
            <a href="{{URL::to('accounts')}}">Account</a>
        </li>
        <li class="active">
            <strong>IPs</strong>
        </li>
    </ol>
    <!--<h3>Account IPs Upload </h3>-->
<div class="row">
<div class="col-md-12">
    <form role="form" id="form-upload" name="form-upload" method="post" action="" class="form-horizontal form-groups-bordered" enctype="multipart/form-data">
        <div class="panel panel-primary" data-collapsed="0">
            <div class="panel-heading">
                <div class="panel-title">
                    Upload Account IPs
                </div>
                
                <div class="panel-options">
                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                </div>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">Upload Template</label>
                    <div class="col-sm-4">
                        {{ Form::select('UploadTemplate', $UploadTemplate, '' , array("class"=>"select2")) }}
                    </div>
                </div>
                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">Upload (.xls, .xlxs, .csv)</label>
                    <div class="col-sm-4">
                        <input name="excel" type="file" class="form-control file2 inline btn btn-primary" data-label="
                        <i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" />
                        
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Note</label>
                    <div class="col-sm-8">
                        
                        <p><i class="glyphicon glyphicon-minus"></i><strong>Allowed Extension</strong> .xls, .xlxs, .csv</p>
                        <p>Please upload the file in given <span style="cursor: pointer" onclick="jQuery('#modal-fileformat').modal('show');" class="label label-info">Format</span></p>

                        <p>Sample File <a class="btn btn-success btn-sm btn-icon icon-left" href="{{URL::to('import/ips_download_sample_excel_file')}}"><i class="entypo-down"></i>Download</a></p><br>

                    </div>
                    
                </div>
                <p style="text-align: right;">
                <button  type="submit" class="btn upload btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                    <i class="glyphicon glyphicon-circle-arrow-up"></i>
                    Upload
                </button>
                <!-- <a href="#" class="btn btn-danger btn-sm btn-icon icon-left">
                    <i class="entypo-cancel"></i>
                    Cancel
                </a> -->
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

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
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
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        CSV Importer
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Delimiter:</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="option[Delimiter]" value="," />
                            <input type="hidden" name="TemplateFile" value="" />
                            <input type="hidden" name="TempFileName" value="" />
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
                        <button class="check btn btn-primary btn-sm btn-icon icon-left">
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
                        <label for="field-1" class="col-sm-2 control-label">Account Name*</label>
                        <div class="col-sm-4">
                            {{Form::select('selection[AccountName]', array(),'',array("class"=>"select2 small"))}}
                        </div>

                        <label for="field-1" class="col-sm-2 control-label">IP*</label>
                        <div class="col-sm-4">
                            {{Form::select('selection[IP]', array(),'',array("class"=>"select2 small"))}}
                        </div>
                    </div>
                    <div class="form-group">
                        <br />
                        <br />
                        <label for="field-1" class="col-sm-2 control-label">Type*
                            <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Customer IP or Vendor IP" data-original-title="Type">?</span>
                        </label>
                        <div class="col-sm-4">
                            {{Form::select('selection[Type]', array(),'',array("class"=>"select2 small"))}}
                        </div>

                        <label for="field-1" class="col-sm-2 control-label">Service</label>
                        <div class="col-sm-4">
                            {{Form::select('selection[Service]', array(),'',array("class"=>"select2 small"))}}
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
                    <div id="table-4_processing" class="dataTables_processing hidden">Processing...</div>
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
            <p style="text-align: right;">

                <button id="save_template" type="submit"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                    <i class="entypo-floppy"></i>
                    Save
                </button>
            </p>
            </div>
           </div>
        </form>
    </div>
</div>
<script type="text/javascript">
jQuery(document).ready(function ($) {
    $('.btn.upload').click(function(e){
        e.preventDefault();
        //if($('#form-upload').find('select[name="uploadtemplate"]').val()>0){
            //$("#form-upload").submit();
        //}else{
            var formData = new FormData($('#form-upload')[0]);
            show_loading_bar(0);
            $.ajax({
                url:  '{{URL::to('import/ips_check_upload')}}',  //Server script to process data
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
                        var data = response.data;
                        createGrid(data);
                        $('#add-template').removeClass('hidden');
                        var scrollTo = $('#add-template').offset().top;
                        $('html, body').animate({scrollTop:scrollTo}, 1000);
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
        //}
    });
    $('.btn.check').click(function(e){
        e.preventDefault();
        $('#table-4_processing').removeClass('hidden');
        var formData = new FormData($('#add-template-form')[0]);
        var poData = $(document.forms['form-upload']).serializeArray();
        for (var i=0; i<poData.length; i++){
            if(poData[i].name!='excel'){
                formData.append(poData[i].name, poData[i].value);
            }
        }
        $.ajax({
            url:'{{URL::to('import/ips_ajaxfilegrid')}}',
            type: 'POST',
            dataType: 'json',
            beforeSend: function(){
                $('.btn.check').button('loading');
            },
            success: function(response) {
                $('.btn.check').button('reset');
                if (response.status == 'success') {
                    var data = response.data;
                    createGrid(data);
                } else {
                    toastr.error(response.message, "Error", toastr_opts);
                }
                $('#table-4_processing').addClass('hidden');
            },
            data: formData,
            cache: false,
            contentType: false,
            processData: false
        });
    });
    $("#save_template").click(function(e){
        e.preventDefault();
        var formData = new FormData($('#add-template-form')[0]);
        var poData = $(document.forms['form-upload']).serializeArray();
        for (var i=0; i<poData.length; i++){
            if(poData[i].name!='excel'){
                formData.append(poData[i].name, poData[i].value);
            }
        }
        $.ajax({
            url:'{{URL::to('import/ips_storeTemplate')}}', //Server script to process data
            type: 'POST',
            dataType: 'json',
            beforeSend: function(){
                $('.btn.save').button('loading');
            },
            success: function(response) {
                $("#save_template").button('reset');
                if (response.status == 'success') {
                    toastr.success(response.message, "Success", toastr_opts);
                    reloadJobsDrodown(0);
                    location.reload();
                } else {
                    toastr.error(response.message, "Error", toastr_opts);
                }
            },
            data: formData,
            //Options to tell jQuery not to process data or worry about content-type.
            cache: false,
            contentType: false,
            processData: false
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
                if(typeof item == 'object' && item != null ){
                    tr+='<td>'+item.date+'</td>';
                }else{
                    tr+='<td>'+item+'</td>';
                }
            });
            tr += '</tr>';
            body.append(tr);
        });
        $("#mapping select").each(function(i, el){
            if(el.name !='selection[DateFormat]'){
                var self = $('#add-template-form [name="'+el.name+'"]');
                rebuildSelect2(self,data.columns,'Skip loading');
            }
        });
        if(data.IPFileUploadTemplate){
            $.each( data.IPFileUploadTemplate, function( optionskey, option_value ) {
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
        }

        $('#add-template-form').find('[name="TemplateFile"]').val(data.filename);
        $('#add-template-form').find('[name="TempFileName"]').val(data.tempfilename);
    }

});
</script>
@stop
@section('footer_ext')
@parent

<div class="modal fade" id="modal-fileformat">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">IP File Format</h4>
            </div>
            <div class="modal-body">
                <p>All columns are mandatory and the first line should have the column headings.</p>
                        <table class="table responsive">
                            <thead>
                                <tr>
                                    <th>AccountName</th>
                                    <th>IP</th>
                                    <th>Type</th>
                                    <th>Service(Opt.)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Test Account</td>
                                    <td>1.1.1.1</td>
                                    <td>Customer</td>
                                    <td>Default Service</td>
                                </tr>
                                <tr>
                                    <td>Test Account</td>
                                    <td>1.1.1.2</td>
                                    <td>Customer</td>
                                    <td>Default Service</td>
                                </tr>
                                <tr>
                                    <td>Test Account</td>
                                    <td>2.1.1.1</td>
                                    <td>Vendor</td>
                                    <td>Default Service</td>
                                </tr>
                                <tr>
                                    <td>Test Account</td>
                                    <td>2.1.1.1</td>
                                    <td>Vendor</td>
                                    <td>Default Service</td>
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