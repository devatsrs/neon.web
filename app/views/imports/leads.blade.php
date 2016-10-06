@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>
        <a href="{{URL::to('leads')}}">Leads</a>
    </li>
    <li class="active">
        <strong>Import Leads</strong>
    </li>
</ol>
<h3>Import Leads</h3>
<p style="text-align: right;margin-bottom: 20px;">
    <a class="btn btn-danger btn-sm btn-icon icon-left canbutton" href="{{URL::to('/leads')}}">
        <i class="entypo-cancel"></i>
        Close
    </a>
</p>
@include('includes.errors')
@include('includes.success')
<style>
    .col-md-4{
        padding-left:5px;
        padding-right:5px;
    }
</style>
<div class="panel">
<form id="rootwizard-2" method="post" action="" class="form-wizard validate form-horizontal form-groups-bordered" enctype="multipart/form-data">

    <div class="steps-progress">
        <div class="progress-indicator"></div>
    </div>

    <ul>
        <li class="active" id="st1">
            <a href="#tab2-1" data-toggle="tab"><span>1</span>Select Import Type</a>
        </li>
        <li id="st2">
            <a href="#tab2-2" data-toggle="tab"><span>2</span>Upload File</a>
        </li>
        <li id="st3">
            <a href="#tab2-3" data-toggle="tab"><span>3</span>Mapping and Submit</a>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane active" id="tab2-1">

            <div class="row">
                </br></br>
                <div class="col-md-1"></div>
                <div class="col-md-9">
                    <div class="col-md-4">
                        <input type="radio" name="size" value="excel" id="size_S" checked />
                        <label for="size_S" class="newredio active">EXCEL</label>
                    </div>
                    <div class="col-md-4">
                        <input type="radio" name="size" value="csv" id="size_M"/>
                        <label for="size_M" class="newredio">CSV</label>
                    </div>
                    <!--<input type="radio" name="size" value="pbx" id="size_L"/>
                    <label for="size_L" class="newredio">PBX</label>-->
                </div>
                <div class="col-md-2"></div>
            </div>
        </div>

        <div class="tab-pane" id="tab2-2">

            <div class="row" id="csvimport">
                <div class="col-md-1"></div>
                <div class="col-md-9">
                    <div class="form-group">
                        <input type="hidden" name="importfrom" value="">
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Upload Template</label>
                        <div class="col-sm-4">
                            {{ Form::select('uploadtemplate', $UploadTemplate, '' , array("class"=>"select2")) }}
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Upload (.xls, .xlxs, .csv)</label>
                        <div class="col-sm-4">
                            <input name="excel" type="file" class="form-control file2 inline btn btn-primary" data-label="
                            <i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" data-validate="required"/>

                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Note</label>
                        <div class="col-sm-8">

                            <p><i class="glyphicon glyphicon-minus"></i><strong>Allowed Extension</strong> .xls, .xlxs, .csv</p>
                            <p>Please upload the file in given <span style="cursor: pointer" onclick="jQuery('#modal-fileformat').modal('show');" class="label label-info">Format</span></p>

                            <p>Sample File <a class="btn btn-success btn-sm btn-icon icon-left" href="{{URL::to('/import/leads/leads_download_sample_excel_file')}}"><i class="entypo-down"></i>Download</a></p>

                        </div>

                    </div>
                </div>
                <div class="col-md-1"></div>
            </div>

        </div>

        <div class="tab-pane" id="tab2-3">
            <div class="row hidden" id="add-template">
                <div class="col-md-12">
                    <div id="add-template-form">
                        <div class="panel panel-primary" data-collapsed="0">
                            <div class="panel-heading">
                                <div class="panel-title">
                                    Mapping Template
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
                                <div class="panel panel-primary" data-collapsed="0">
                                    <div class="panel-heading">
                                        <div class="panel-title">
                                            Lead Importer
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
                                                <!--<input type="hidden" name="TemplateName" value="" />-->
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
                                            Field Mapping
                                        </div>

                                        <div class="panel-options">
                                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                                        </div>
                                    </div>

                                    <div class="panel-body" id="mapping">
                                        <div class="form-group">
                                            <label for="field-1" class="col-sm-2 control-label">Company*</label>
                                            <div class="col-sm-4">
                                                {{Form::select('selection[AccountName]', array(),'',array("class"=>"select2 small"))}}
                                            </div>
                                            <label for="field-1" class="col-sm-2 control-label">Title</label>
                                            <div class="col-sm-4">
                                                {{Form::select('selection[NamePrefix]', array(),'',array("class"=>"select2 small"))}}
                                                <input type="hidden" class="form-control" name="AccountType" value="0" />
                                                <!--<input type="hidden" class="form-control" name="tempCompanyGatewayID" value="" />-->
                                            </div>

                                        </div>
                                        <div class="form-group">
                                            <label for="field-1" class="col-sm-2 control-label">First Name*</label>
                                            <div class="col-sm-4">
                                                {{Form::select('selection[FirstName]', array(),'',array("class"=>"select2 small"))}}
                                            </div>

                                            <label for="field-1" class="col-sm-2 control-label">Last Name*</label>
                                            <div class="col-sm-4">
                                                {{Form::select('selection[LastName]', array(),'',array("class"=>"select2 small"))}}
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="field-1" class="col-sm-2 control-label">Email</label>
                                            <div class="col-sm-4">
                                                {{Form::select('selection[Email]', array(),'',array("class"=>"select2 small"))}}
                                            </div>
                                            <label for="field-1" class="col-sm-2 control-label">Phone</label>
                                            <div class="col-sm-4">
                                                {{Form::select('selection[Phone]', array(),'',array("class"=>"select2 small"))}}
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="field-1" class="col-sm-2 control-label">Mobile</label>
                                            <div class="col-sm-4">
                                                {{Form::select('selection[Mobile]', array(),'',array("class"=>"select2 small"))}}
                                            </div>
                                            <label for="field-1" class="col-sm-2 control-label">Fax</label>
                                            <div class="col-sm-4">
                                                {{Form::select('selection[Fax]', array(),'',array("class"=>"select2 small"))}}
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="field-1" class="col-sm-2 control-label">Address1</label>
                                            <div class="col-sm-4">
                                                {{Form::select('selection[Address1]', array(),'',array("class"=>"select2 small"))}}
                                            </div>
                                            <label for="field-1" class="col-sm-2 control-label">Address2</label>
                                            <div class="col-sm-4">
                                                {{Form::select('selection[Address2]', array(),'',array("class"=>"select2 small"))}}
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="field-1" class="col-sm-2 control-label">Address3</label>
                                            <div class="col-sm-4">
                                                {{Form::select('selection[Address3]', array(),'',array("class"=>"select2 small"))}}
                                            </div>
                                            <label for="field-1" class="col-sm-2 control-label">City</label>
                                            <div class="col-sm-4">
                                                {{Form::select('selection[City]', array(),'',array("class"=>"select2 small"))}}
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="field-1" class="col-sm-2 control-label">Post Code</label>
                                            <div class="col-sm-4">
                                                {{Form::select('selection[Pincode]', array(),'',array("class"=>"select2 small"))}}
                                            </div>
                                            <label for="field-1" class="col-sm-2 control-label">Country</label>
                                            <div class="col-sm-4">
                                                {{Form::select('selection[Country]', array(),'',array("class"=>"select2 small"))}}
                                            </div>

                                        </div>
                                        <div class="form-group">
                                            <label for="field-1" class="col-sm-2 control-label">Website</label>
                                            <div class="col-sm-4">
                                                {{Form::select('selection[Website]', array(),'',array("class"=>"select2 small"))}}
                                            </div>
                                            <label for="field-1" class="col-sm-2 control-label">Employee</label>
                                            <div class="col-sm-4">
                                                {{Form::select('selection[Employee]', array(),'',array("class"=>"select2 small"))}}
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="field-1" class="col-sm-2 control-label">Skype</label>
                                            <div class="col-sm-4">
                                                {{Form::select('selection[Skype]', array(),'',array("class"=>"select2 small"))}}
                                            </div>
                                            <label for="field-1" class="col-sm-2 control-label">Twitter</label>
                                            <div class="col-sm-4">
                                                {{Form::select('selection[Twitter]', array(),'',array("class"=>"select2 small"))}}
                                            </div>

                                        </div>
                                        <div class="form-group">
                                            <label for="field-1" class="col-sm-2 control-label">Description</label>
                                            <div class="col-sm-4">
                                                {{Form::select('selection[Description]', array(),'',array("class"=>"select2 small"))}}
                                            </div>
                                            <label for="field-1" class="col-sm-2 control-label">VatNumber</label>
                                            <div class="col-sm-4">
                                                {{Form::select('selection[VatNumber]', array(),'',array("class"=>"select2 small"))}}
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="field-1" class="col-sm-2 control-label">Tags</label>
                                            <div class="col-sm-4">
                                                {{Form::select('selection[tags]', array(),'',array("class"=>"select2 small"))}}
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <div class="panel panel-primary" data-collapsed="0">
                                    <div class="panel-heading">
                                        <div class="panel-title">
                                            File to be loaded
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
                    </div>
                </div>
            </div>

        </div>

        <ul class="pager wizard">
            <li class="previous">
                <a href="#"><i class="entypo-left-open"></i> Previous</a>
            </li>

            <li class="next">
                <a href="#">Next <i class="entypo-right-open"></i></a>
            </li>
        </ul>
    </div>

</form><!-- Footer -->
</div>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        public_vars.$body = $("body");
        $('input[type="radio"], label').addClass('js');

        $('.newredio').on('click', function() {
            $('.newredio').removeClass('active');
            $(this).addClass('active');
        });
        $('#csvimport').hide();
        $('#gatewayimport').hide();
        var activetab = '';
        var element= $("#rootwizard-2");
        var progress = element.find(".steps-progress div");
        $('#rootwizard-2').bootstrapWizard({
            tabClass:         '',
            nextSelector:     '.wizard li.next',
            previousSelector: '.wizard li.previous',
            firstSelector:    '.wizard li.first',
            lastSelector:     '.wizard li.last',
            onTabShow: function(tab, navigation, index)
            {
                setCurrentProgressTab(element, navigation, tab, progress, index);
            },
            onNext: function(tab, navigation, index) {
                activetab = tab.attr('id');
                if(activetab=='st1'){
                    var importfrom = $("#rootwizard-2 input[name='size']:checked").val();
                    if(importfrom=='csv' || importfrom=='excel'){
                        $("#csvimport").find("input[name='importfrom']").val(importfrom);
                        $('#csvimport').show();
                    }else if(importfrom=='pbx'){
                        $('#gatewayimport').show();
                    }

                }

                if(activetab=='st2'){
                    var uploadtemplate = $("#rootwizard-2 select[name='uploadtemplate']").val();
                    var filename = $("#rootwizard-2 input[name='excel']").val();
                    if(filename == ''){
                        toastr.error('Please upload file.', "Error", toastr_opts);
                        return false;
                    }else{
                        var formData = new FormData($('#rootwizard-2')[0]);
                        var timeDelay = 500;
                        setTimeout(loadXML, timeDelay);
                        function loadXML() {
                            $.ajax({
                                url: '{{URL::to('/import/leads/leads_check_upload')}}',  //Server script to process data
                                type: 'POST',
                                dataType: 'json',
                                xhr: function () {  // Custom XMLHttpRequest
                                    var myXhr = $.ajaxSettings.xhr();
                                    if (myXhr.upload) { // Check if upload property exists
                                        myXhr.upload.addEventListener('progress', function (evt) {
                                            var percent = (evt.loaded / evt.total) * 100;
                                            show_loading_bar(percent);
                                        }, false);
                                    }
                                    return myXhr;
                                },
                                beforeSend: function () {

                                },
                                afterSend: function () {
                                    console.log("Afer Send");
                                },
                                success: function (response) {
                                    setTimeout(function() {
                                        if (response.status == 'success') {
                                            var data = response.data;
                                            createGrid(data);
                                            $('#add-template').removeClass('hidden');

                                        } else {
                                            toastr.error(response.message, "Error", toastr_opts);
                                            return false;
                                        }
                                    },500);
                                    //alert(response.message);
                                    //$('.btn.upload').button('reset');
                                },
                                // Form data
                                data: formData,
                                //Options to tell jQuery not to process data or worry about content-type.
                                cache: false,
                                contentType: false,
                                processData: false
                            });
                        }
                    }


                }
            }
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
            if(data.AccountFileUploadTemplate){
                $.each( data.AccountFileUploadTemplate, function( optionskey, option_value ) {
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
            //$('#add-template-form').find('[name="tempCompanyGatewayID"]').val(data.CompanyGatewayID);
        }

        $('.btn.check').click(function(e){
            e.preventDefault();
            $('#table-4_processing').removeClass('hidden');
            var formData = new FormData($('#add-template-form')[0]);
            var poData = $(document.forms['rootwizard-2']).serializeArray();
            for (var i=0; i<poData.length; i++){
                if(poData[i].name!='excel'){
                    formData.append(poData[i].name, poData[i].value);
                }
            }
            $.ajax({
                url:'{{URL::to('/import/leads/leads_ajaxfilegrid')}}',
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
            var formData = new FormData($('#rootwizard-2')[0]);
            var poData = $(document.forms['form-upload']).serializeArray();
            for (var i=0; i<poData.length; i++){
                if(poData[i].name!='excel'){
                    formData.append(poData[i].name, poData[i].value);
                }
            }
            $.ajax({
                url:'{{URL::to('/import/leads/leads_storeTemplate')}}', //Server script to process data
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

        /*
        $("#rootwizard-2").submit(function(e) {
            e.preventDefault();
            //var formData = new FormData($('#rootwizard-2')[0]);
            var formData = $("#rootwizard-2 input[name='size']:checked").val();

        });
        */
    });
    </script>
<script type="text/javascript" src="<?php echo URL::to('/').'/assets/js/jquery.bootstrap.wizard.min.js'; ?>" ></script>

<style>
    .dataTables_filter label{
        display:none !important;
    }
    .dataTables_wrapper .export-data{
        right: 30px !important;
    }
    #selectcheckbox{
        padding: 15px 10px;
    }
    input[type="radio"].js {
        display: none;
    }

    .newredio.js {
        display: block;
        float: left;
        margin-right: 10px;
        border: 1px solid #ababab;        ;
        color: #ababab;
        text-align: center;
        padding: 20px;
        height:20%;
        width: 25%;
        cursor: pointer;
    }

    .newredio.js.active {
        border: 1px solid #21a9e1;
        color: #ababab;
        font-weight: bold;
    }
    .form-horizontal .control-label{
        text-align: left !important;
    }
    #tab2-2{
        margin: 0 0 0 50px;
    }
    .pager li.disabled{
        display: none;
    }
    .pager li > a, .pager li > span{
        background-color: #000000 !important;
        border-radius:3px;
        border:none;
    }
    .pager li > a{

        color : #ffffff !important;
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
                    <h4 class="modal-title">Leads File Format</h4>
                </div>
                <div class="modal-body scrollx">
                    <p>The first line should have the column headings.</p>
                    <table class="table responsive">
                        <thead>
                        <tr>
                            <th>Company</th>
                            <th>Title(Opt.)</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email(Opt.)</th>
                            <th>Phone(Opt.)</th>
                            <th>Mobile(Opt.)</th>
                            <th>Fax(Opt.)</th>
                            <th>Address1(Opt.)</th>
                            <th>Address2(Opt.)</th>
                            <th>Address3(Opt.)</th>
                            <th>City(Opt.)</th>
                            <th>Post Code(Opt.)</th>
                            <th>Country(Opt.)</th>
                            <th>Website(Opt.)</th>
                            <th>Employee(Opt.)</th>
                            <th>Skype(Opt.)</th>
                            <th>Twitter(Opt.)</th>
                            <th>Description(Opt.)</th>
                            <th>VatNumber(Opt.)</th>
                            <th>Tags(Opt.)</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>Test Account</td>
                            <td>Mr</td>
                            <td>Test</td>
                            <td>Abc</td>
                            <td>test@gmail.com</td>
                            <td>123456</td>
                            <td>9909990999</td>
                            <td>123456</td>
                            <td>address line1</td>
                            <td>address line2</td>
                            <td>address line3</td>
                            <td>London</td>
                            <td>WC2N</td>
                            <td>UNITED KINGDOM</td>
                            <td>WWW.abc.com</td>
                            <td>4</td>
                            <td>abc.skype</td>
                            <td>abc.twitter</td>
                            <td>test Description</td>
                            <td>789546</td>
                            <td>test1,test2</td>
                        </tr>
                        <tr>
                            <td>Test Account</td>
                            <td>Mr</td>
                            <td>Test</td>
                            <td>Abc</td>
                            <td>test@gmail.com</td>
                            <td>123456</td>
                            <td>9909990999</td>
                            <td>123456</td>
                            <td>address line1</td>
                            <td>address line2</td>
                            <td>address line3</td>
                            <td>London</td>
                            <td>WC2N</td>
                            <td>UNITED KINGDOM</td>
                            <td>WWW.abc.com</td>
                            <td>4</td>
                            <td>abc.skype</td>
                            <td>abc.twitter</td>
                            <td>test Description</td>
                            <td>789546</td>
                            <td>test1,test2</td>
                        </tr>
                        <tr>
                            <td>Test Account</td>
                            <td>Mr</td>
                            <td>Test</td>
                            <td>Abc</td>
                            <td>test@gmail.com</td>
                            <td>123456</td>
                            <td>9909990999</td>
                            <td>123456</td>
                            <td>address line1</td>
                            <td>address line2</td>
                            <td>address line3</td>
                            <td>London</td>
                            <td>WC2N</td>
                            <td>UNITED KINGDOM</td>
                            <td>WWW.abc.com</td>
                            <td>4</td>
                            <td>abc.skype</td>
                            <td>abc.twitter</td>
                            <td>test Description</td>
                            <td>789546</td>
                            <td>test1,test2</td>
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