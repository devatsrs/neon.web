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
    <h3>Import IPs </h3>
    <p style="text-align: right;margin-bottom: 20px;">
        <a class="btn btn-danger btn-sm btn-icon icon-left canbutton" href="{{URL::to('/accounts')}}">
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
        #selectcheckbox1 {
            padding: 15px 10px;
        }
        .select2-container {
            width: 100%;
        }
    </style>

    <div class="panel">
        <form id="rootwizard-2" method="post" action="" class="form-wizard validate form-horizontal form-groups-bordered" enctype="multipart/form-data">
            <div class="steps-progress" style="display:none">
                <div class="progress-indicator"></div>
            </div>

            <ul id="wizardul" style="display:none">
                <li class="active" id="st1">
                    <a href="#tab2-1" data-toggle="tab"><span>1</span><h5 class="test">Select Import Type</h5></a>
                </li>
                <li id="st2">
                    <a href="#tab2-2" data-toggle="tab"><span>2</span><h5 class="test">Upload File</h5></a>
                </li>
                <li id="st3">
                    <a href="#tab2-3" data-toggle="tab"><span>3</span><h5 class="test">Mapping and Submit</h5></a>
                </li>
            </ul>

            <div class="tab-content">
                <span class="itype"><h3>Select Import Type</h3></span>
                <div class="tab-pane active" id="tab2-1">
                    <div class="row">
                        </br></br>
                        <div class="col-md-1"></div>
                        <div class="col-md-9">
                            <div class="col-md-4">
                                <input type="radio" name="size" data-id="" value="excel" id="size_S" checked />
                                <label for="size_S" class="newredio active">EXCEL</label>
                            </div>
                            <div class="col-md-4">
                                <input type="radio" name="size" data-id="" value="csv" id="size_M"/>
                                <label for="size_M" class="newredio">CSV</label>
                            </div>
                            @foreach($gatewaylist as $gateway)
                                <div class="col-md-4">
                                    <input type="radio" name="size" data-id="{{$gateway['CompanyGatewayID']}}" data-name="{{$gateway['Title']}}" data-gateway="{{$gateway['Gateway']}}" value="{{$gateway['Gateway']}}" id="size_{{$gateway['CompanyGatewayID']}}"/>
                                    <label for="size_{{$gateway['CompanyGatewayID']}}" class="newredio">{{$gateway['Title']}}</label>
                                </div>
                            @endforeach

                        </div>
                        <div class="col-md-2"></div>
                    </div>
                </div>

                <div class="tab-pane" id="tab2-2">
                    <input type="hidden" name="importway" value="">
                    <div class="row" id="csvimport">
                        <div class="col-md-1"></div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <input type="hidden" name="importfrom" value="">
                            </div>
                            <div class="form-group">
                                <label for="field-1" class="col-sm-2 control-label">Upload Template</label>
                                <div class="col-sm-4">
                                    {{ Form::select('UploadTemplate', $UploadTemplate, '' , array("class"=>"select2")) }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="field-1" class="col-sm-2 control-label">Upload (.xls, .xlxs, .csv)</label>
                                <div class="col-sm-4">
                                    <input name="excel" type="file" class="form-control file2 inline btn btn-primary" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" />
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
                        </div>
                        <div class="col-md-2"></div>
                    </div>

                    <div class="row" id="gatewayimport">
                        <input type="hidden" name="gateway" value="">
                        <input type="hidden" name="CompanyGatewayID" value="">
                        <input type="hidden" name="importprocessid" value="{{(string) GUID::generate()}}">
                        <input type="hidden" name="importaccouniptsuccess" value="">
                        <span id="gateway_filter" data-type=""></span>
                        <span id="get_accountip"></span>
                        <span id="get_accountsip_sippy"></span>
                        <span id="get_vendorsip_sippy"></span>
                        <span class="gatewayloading">Retrieving IPs ... </span>

                        {{-- here datatable will be placed using javascript --}}
                        {{-- datatable layout is at last in #table_templates div --}}
                    </div>
                </div>

                <div class="tab-pane" id="tab2-3">
                    <div id="csvactive">
                        <div class="row hidden" id="add-template">
                            <div class="col-md-12">
                                <div id="add-template-form">
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
                                </div>
                            </div>
                        </div>
                    </div> <!-- csv-excel over-->
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

    <div id="table_templates" style="display: none;">
        <div id="table_template_sippy">
            <p style="float: right">
                <button type="button" id="uploadaccountipsippy"  class="btn btn-primary "><i class="entypo-download"></i><span>Import</span></button>
            </p>
            <div class="clear"></div>
            <div class="panel-heading" style="padding: 0;">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#customertab" data-toggle="tab">Customers</a></li>
                    <li><a href="#vendortab" data-toggle="tab">Vendors</a></li>
                </ul>
            </div>
            <div class="panel-body" style="padding: 0;">
                <div class="tab-content" style="margin: 0;">
                    <div class="tab-pane fade in active" id="customertab">
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-bordered datatable" id="table-5">
                                    <thead>
                                        <tr>
                                            <th width="5%"><input type="checkbox" id="selectall" name="checkbox[]" class="" /></th>
                                            <th width="15%" >Account Name</th>
                                            <th width="15%" >IPs</th>
                                            <th width="15%" >Select Account</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="vendortab">
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-bordered datatable" id="table-6">
                                    <thead>
                                        <tr>
                                            <th width="5%"><input type="checkbox" id="selectall1" name="checkbox[]" class="" /></th>
                                            <th width="15%" >Account Name</th>
                                            <th width="15%" >IPs</th>
                                            <th width="15%" >Select Account</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="table_template_default">
            <p style="float: right">
                <button type="button" id="uploadaccountip"  class="btn btn-primary "><i class="entypo-download"></i><span>Import</span></button>
            </p>
            <div class="clear"></div>
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-bordered datatable" id="table-5">
                        <thead>
                            <tr>
                                <th width="5%"><input type="checkbox" id="selectall" name="checkbox[]" class="" /></th>
                                <th width="15%" >Account Name</th>
                                <th width="15%" >IPs</th>
                                <th width="15%" >Select Account</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div id="accountsdropdowns" style="display: none;">
        {{ Form::select('',$customerslist,'', array("class"=>"select2",'id'=>'customerdropdown','style'=>'display:none;')) }}
        {{ Form::select('',$vendorslist,'', array("class"=>"select2",'id'=>'vendordropdown','style'=>'display:none;')) }}
    </div>

    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            var imported = 0;
            var checked='';
            public_vars.$body = $("body");
            $('input[type="radio"], label').addClass('js');

            $('.newredio').on('click', function() {
                $('.newredio').removeClass('active');
                $(this).addClass('active');
            });

            $('#csvimport').hide();
            $('#csvactive').hide();
            $('#gatewayimport').hide();
            $('#uploadaccountip').hide();
            $('#uploadaccountipsippy').hide();
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
                onTabClick: function(){
                    return false;
                },
                onNext: function(tab, navigation, index) {
                    activetab = tab.attr('id');
                    if(activetab=='st1'){
                        $('.itype').hide();
                        $('#wizardul').removeAttr('style');
                        $('.steps-progress').removeAttr('style');
                        var importfrom = $("#rootwizard-2 input[name='size']:checked").val();
                        if(importfrom=='csv' || importfrom=='excel'){
                            if($('#st3 h5').hasClass("test")){
                                $("#st2 h5.test").html('Upload File');
                                $("#st3 h5.test").html('Mapping and Submit');
                            }else{
                                $('#st3').remove();
                                var html ='';
                                html+='<li id="st3">';
                                html+='<a href="#tab2-3" data-toggle="tab"><span>3</span><h5 class="test">Mapping and Submit</h5></a>';
                                html+='</li>';
                                $('#wizardul li:eq(1)').after(html);
                                //$('#wizardul').append(html);
                                $("#st2 h5.test").html('Upload File');
                                $("#st2").removeAttr('class');
                                $("#st3").removeAttr('class');
                            }
                            $("#csvimport").find("input[name='importfrom']").val(importfrom);
                            $("#rootwizard-2").find("input[name='importway']").val(importfrom);
                            $('#gatewayimport').hide();
                            $('#csvimport').show();
                        }else if(importfrom=='PBX' || importfrom=='Porta' || importfrom=='MOR' || importfrom =='CallShop' || importfrom =='Streamco' || importfrom =='FusionPBX' || importfrom =='M2' || importfrom =='SippySFTP'){
                            $('#st3').remove();
                            $("#st2 h5.test").html('Select IPs');
                            var cgid = $("#rootwizard-2 input[name='size']:checked").attr('data-id');
                            var cgname = $("#rootwizard-2 input[name='size']:checked").attr('data-name');
                            $('#csvimport').hide();
                            $("#gatewayimport").find("input[name='gateway']").val(importfrom);
                            $("#rootwizard-2").find("input[name='importway']").val(importfrom);
                            $("#rootwizard-2").find("input[name='CompanyGatewayID']").val(cgid);
                            $("#gatewayimport").find(".gatewayname").html(cgname);
                            $('#gatewayimport').show();

                            if(importfrom == 'SippySFTP') {
                                $('#gateway_filter').attr('data-type','accounts');
                                $('#gateway_filter').trigger('click');
                                $('#gateway_filter').attr('data-type','vendors');
                                $('#gateway_filter').trigger('click');
                            } else {
                                $('#gateway_filter').attr('data-type','');
                                $('#gateway_filter').trigger('click');
                            }

                        }
                    }

                    if(activetab=='st2'){
                        var importway = $("#rootwizard-2 input[name='importway']").val();
                        if(importway == 'csv' || importway == 'excel') {
                            $("#st2 h5.test").html('Upload File');
                            $("#st3 h5.test").html('Mapping and Submit');
                            $(".pager .next").addClass('disabled');
                            var uploadtemplate = $("#rootwizard-2 select[name='uploadtemplate']").val();
                            var filename = $("#rootwizard-2 input[name='excel']").val();
                            if (filename == '') {
                                toastr.error('Please upload file.', "Error", toastr_opts);
                                $(".pager .next").removeClass('disabled');
                                return false;
                            } else {
                                var formData = new FormData($('#rootwizard-2')[0]);
                                var timeDelay = 500;
                                setTimeout(loadXML, timeDelay);
                                function loadXML() {
                                    $.ajax({
                                        url:  '{{URL::to('import/ips_check_upload')}}',  //Server script to process data
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
                                                    $('#csvactive').show();
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
                        } // import from excel-csv over

                    }
                },
                onPrevious: function(tab, navigation, index) {
                    activetab = tab.attr('id');
                    if(activetab=='st2'){
                        location.reload();
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
                    console.log(el.name);
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

            $('.btn.check').click(function(e){
                e.preventDefault();
                $('#table-4_processing').removeClass('hidden');
                var formData = new FormData($('#rootwizard-2')[0]);
                var poData = $(document.forms['rootwizard-2']).serializeArray();
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
                var formData = new FormData($('#rootwizard-2')[0]);
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

            $("#selectall").click(function(ev) {
                var is_checked = $(this).is(':checked');
                $('#table-5 tbody tr').each(function(i, el) {
                    if($(this).find('.rowcheckbox').hasClass('rowcheckbox')){
                        if (is_checked) {
                            $(this).find('.rowcheckbox').prop("checked", true);
                            $(this).addClass('selected');
                        } else {
                            $(this).find('.rowcheckbox').prop("checked", false);
                            $(this).removeClass('selected');
                        }
                    }
                });
            });
            $('#table-5 tbody').on('click', 'tr', function() {
                if (checked =='') {
                    if ($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                        $(this).toggleClass('selected');
                        if ($(this).hasClass('selected')) {
                            $(this).find('.rowcheckbox').prop("checked", true);
                        } else {
                            $(this).find('.rowcheckbox').prop("checked", false);
                        }
                    }
                }
            });
            /* sippy gateway */
            $("#selectall1").click(function(ev) {
                var is_checked = $(this).is(':checked');
                $('#table-6 tbody tr').each(function(i, el) {
                    if($(this).find('.rowcheckbox').hasClass('rowcheckbox')){
                        if (is_checked) {
                            $(this).find('.rowcheckbox').prop("checked", true);
                            $(this).addClass('selected');
                        } else {
                            $(this).find('.rowcheckbox').prop("checked", false);
                            $(this).removeClass('selected');
                        }
                    }
                });
            });
            $('#table-6 tbody').on('click', 'tr', function() {
                if (checked =='') {
                    if ($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                        $(this).toggleClass('selected');
                        if ($(this).hasClass('selected')) {
                            $(this).find('.rowcheckbox').prop("checked", true);
                        } else {
                            $(this).find('.rowcheckbox').prop("checked", false);
                        }
                    }
                }
            });
            $("#gateway_filter").click(function(e) {
                var type = $(this).attr('data-type');
                if(type == '') {
                    imported = 2;
                }
                var ProcessID = $("#gatewayimport input[name='importprocessid']").val();
                var CompanyGatewayID = $("#rootwizard-2 input[name='size']:checked").attr('data-id');
                var gateway = $("#rootwizard-2 input[name='size']:checked").val();
                $.ajax({
                    url: baseurl + '/import/ips/getAccountIpFromGateway/' + CompanyGatewayID + '/' + gateway,
                    type: 'POST',
                    datatype: 'json',
                    data: 'gateway=' + gateway + '&type=' + type + '&ProcessID=' + ProcessID,
                    beforeSend: function(){
                        $(".gatewayloading").show();
                    },
                    success: function (response) {
                        $('.gatewayloading').hide();
                        if (response.status == 'success') {
                            if(type != "") {
                                imported+=1;
                            }
                            $("#gatewayimport input[name='importaccountipsuccess']").val('1');
                            $("#gatewayimport .importsuccessmsg").html(type + ' IP Succesfully Import.');
                            //$("#gatewayimport input[name='importprocessid']").val(response.processid);

                            if(response.Gateway == 'Sippy') {
                                var table_template_sippy = $('#table_template_sippy').html();
                                $('#gatewayimport').append(table_template_sippy);
                                $('#table_templates').remove();

                                if(type == 'accounts')
                                    $('#get_accountsip_sippy').trigger('click');
                                else if(type == "vendors")
                                    $('#get_vendorsip_sippy').trigger('click');

                                if(type == "" || imported >=2)
                                    $('#uploadaccountipsippy').show();
                            } else {
                                var table_template_default = $('#table_template_default').html();
                                $('#gatewayimport').append(table_template_default);
                                $('#table_templates').remove();
                                $('#get_accountip').trigger('click');
                                $('#uploadaccountip').show();
                            }

                            $('.pager li .next').addClass('disabled');
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                            $('#get_accountip').trigger('click');
                            $('#uploadaccountip').show();
                            $('.pager li .next').addClass('disabled');
                        }
                    }
                });
            });

            $("#get_accountsip_sippy").click(function(e) {
                e.preventDefault();
                var CGatewayID=$("#gatewayimport input[name='CompanyGatewayID']").val();
                var cprocessid=$("#gatewayimport input[name='importprocessid']").val();
                data_table = $("#table-5").dataTable({
                    "bProcessing":true,
                    "bDestroy": true,
                    "bServerSide":true,
                    "sAjaxSource": baseurl + "/import/ips/ajax_get_missing_gatewayaccountsip",
                    "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                    "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                    "fnServerParams": function(aoData) {
                        aoData.push({"name":"CompanyGatewayID","value":CGatewayID},{"name":"importprocessid","value":cprocessid},{"name":"accountiptype","value":1});
                        data_table_extra_params.length = 0;
                        data_table_extra_params.push({"name":"CompanyGatewayID","value":CGatewayID},{"name":"importprocessid","value":cprocessid},{"name":"Export","value":1});
                    },
                    "sPaginationType": "bootstrap",
                    "aaSorting"   : [[1, 'asc']],
                    "oTableTools":
                    {
                        "aButtons": [
                            {
                                "sExtends": "download",
                                "sButtonText": "Export Data",
                                "sUrl": baseurl + "/import/ips/ajax_get_missing_gatewayaccountsip",
                                sButtonClass: "save-collection"
                            }
                        ]
                    },
                    "aoColumns":
                            [
                                {"bSortable": false,
                                    mRender: function(id, type, full) {
                                        return '<div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + id + '" class="rowcheckbox" ></div>';
                                    }
                                }, //0 Checkbox
                                { "bSortable": true },//1 account name
                                { "bSortable": true },//2 ips
                                { "bSortable": false,
                                    mRender:function(id, type, full){
                                        var customerdropdown = $('#customerdropdown').clone();
                                        customerdropdown.removeAttr('id');
                                        customerdropdown.attr('name',full[0]);
                                        customerdropdown.find("option[value='"+full[3]+"']").attr("selected", "selected");
                                        return customerdropdown.prop("outerHTML");
                                    }
                                }//3 customers dropdown
                            ],
                    "fnDrawCallback": function() {
                        $(".dataTables_wrapper select").select2({
                            minimumResultsForSearch: -1
                        });
                        $('#table-5 tbody').off('click');
                        $('#table-5 tbody').on('click', 'tr', function() {
                            if (checked =='') {
                                if ($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                                    $(this).toggleClass('selected');
                                    if ($(this).hasClass('selected')) {
                                        $(this).find('.rowcheckbox').prop("checked", true);
                                    } else {
                                        $(this).find('.rowcheckbox').prop("checked", false);
                                    }
                                }
                            }
                        });
                        $("#selectall").click(function(ev) {
                            var is_checked = $(this).is(':checked');
                            $('#table-5 tbody tr').each(function(i, el) {
                                if($(this).find('.rowcheckbox').hasClass('rowcheckbox')){
                                    if (is_checked) {
                                        $(this).find('.rowcheckbox').prop("checked", true);
                                        $(this).addClass('selected');
                                    } else {
                                        $(this).find('.rowcheckbox').prop("checked", false);
                                        $(this).removeClass('selected');
                                    }
                                }
                            });
                        });
                        $('#selectallbutton').click(function(ev) {
                            if($(this).is(':checked')){
                                checked = 'checked=checked disabled';
                                $("#selectall").prop("checked", true).prop('disabled', true);
                                if(!$('#changeSelectedInvoice').hasClass('hidden')){
                                    $('#table-5 tbody tr').each(function(i, el) {
                                        $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                        $(this).addClass('selected');
                                    });
                                }
                            }else{
                                checked = '';
                                $("#selectall").prop("checked", false).prop('disabled', false);
                                if(!$('#changeSelectedInvoice').hasClass('hidden')){
                                    $('#table-5 tbody tr').each(function(i, el) {
                                        $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                                        $(this).removeClass('selected');
                                    });
                                }
                            }
                        });
                    }
                });
                $("#selectcheckbox").append('<input type="checkbox" id="selectallbutton" name="checkboxselect[]" class="" title="Select All Found Records" />');
            });

            $("#get_vendorsip_sippy").click(function(e) {
                e.preventDefault();
                var CGatewayID=$("#gatewayimport input[name='CompanyGatewayID']").val();
                var cprocessid=$("#gatewayimport input[name='importprocessid']").val();
                data_table = $("#table-6").dataTable({
                    "bProcessing":true,
                    "bDestroy": true,
                    "bServerSide":true,
                    "sAjaxSource": baseurl + "/import/ips/ajax_get_missing_gatewayaccountsip",
                    "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox1.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                    "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                    "fnServerParams": function(aoData) {
                        aoData.push({"name":"CompanyGatewayID","value":CGatewayID},{"name":"importprocessid","value":cprocessid},{"name":"accountiptype","value":2});
                        data_table_extra_params.length = 0;
                        data_table_extra_params.push({"name":"CompanyGatewayID","value":CGatewayID},{"name":"importprocessid","value":cprocessid},{"name":"Export","value":1});
                    },
                    "sPaginationType": "bootstrap",
                    "aaSorting"   : [[1, 'asc']],
                    "oTableTools":
                    {
                        "aButtons": [
                            {
                                "sExtends": "download",
                                "sButtonText": "Export Data",
                                "sUrl": baseurl + "/import/account/ajax_get_missing_gatewayaccountsip",
                                sButtonClass: "save-collection"
                            }
                        ]
                    },
                    "aoColumns":
                            [
                                {"bSortable": false,
                                    mRender: function(id, type, full) {
                                        return '<div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + id + '" class="rowcheckbox" ></div>';
                                    }
                                }, //0 Checkbox
                                { "bSortable": true },//1 account name
                                { "bSortable": true },//2 ips
                                { "bSortable": false,
                                    mRender:function(id, type, full){
                                        var vendordropdown = $('#vendordropdown').clone();
                                        vendordropdown.removeAttr('id');
                                        vendordropdown.attr('name',full[0]);
                                        vendordropdown.find("option[value='"+full[3]+"']").attr("selected", "selected");
                                        return vendordropdown.prop("outerHTML");
                                    }
                                }//3 customers dropdown
                            ],
                    "fnDrawCallback": function() {
                        $(".dataTables_wrapper select").select2({
                            minimumResultsForSearch: -1
                        });
                        $('#table-6 tbody').off('click');
                        $('#table-6 tbody').on('click', 'tr', function() {
                            if (checked =='') {
                                if ($(this).find('.rowcheckbox').hasClass('rowcheckbox')) {
                                    $(this).toggleClass('selected');
                                    if ($(this).hasClass('selected')) {
                                        $(this).find('.rowcheckbox').prop("checked", true);
                                    } else {
                                        $(this).find('.rowcheckbox').prop("checked", false);
                                    }
                                }
                            }
                        });
                        $("#selectall1").click(function(ev) {
                            var is_checked = $(this).is(':checked');
                            $('#table-6 tbody tr').each(function(i, el) {
                                if($(this).find('.rowcheckbox').hasClass('rowcheckbox')){
                                    if (is_checked) {
                                        $(this).find('.rowcheckbox').prop("checked", true);
                                        $(this).addClass('selected');
                                    } else {
                                        $(this).find('.rowcheckbox').prop("checked", false);
                                        $(this).removeClass('selected');
                                    }
                                }
                            });
                        });
                        $('#selectallbutton1').click(function(ev) {
                            if($(this).is(':checked')){
                                checked = 'checked=checked disabled';
                                $("#selectall1").prop("checked", true).prop('disabled', true);
                                if(!$('#changeSelectedInvoice').hasClass('hidden')){
                                    $('#table-6 tbody tr').each(function(i, el) {
                                        $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                        $(this).addClass('selected');
                                    });
                                }
                            }else{
                                checked = '';
                                $("#selectall1").prop("checked", false).prop('disabled', false);
                                if(!$('#changeSelectedInvoice').hasClass('hidden')){
                                    $('#table-6 tbody tr').each(function(i, el) {
                                        $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                                        $(this).removeClass('selected');
                                    });
                                }
                            }
                        });
                    }
                });
                $("#selectcheckbox1").append('<input type="checkbox" id="selectallbutton1" name="checkboxselect[]" class="" title="Select All Found Records" />');
            });

            // import accounts ip in system from sippy gateway
            $(document).on('click', "#uploadaccountipsippy", function(ev) {
                var criteria = '';
                var AccountIPIDs = [];
                var NeonAccountNames = {};
                var gatewayid = $("#rootwizard-2 input[name='CompanyGatewayID']").val();
                var importprocessid = $("#rootwizard-2 input[name='importprocessid']").val();

                if (!confirm('Are you sure you want to import selected gateway ips?')) {
                    return;
                }

                $(this).text('<i class="entypo-download"></i><span>Importing...</span>');
                $(this).addClass('disabled');
                $(this).attr('disabled','disabled');

                if($('#selectallbutton').is(':checked') && $('#selectallbutton1').is(':checked')){
                    //criteria = JSON.stringify($searchFilter);
                    criteria = 1;
                    var i = 0;
                    $('#table-5 tr .rowcheckbox:checked').each(function(i, el) {
                        //console.log($(this).val());
                        AccountID = $(this).val();
                        if(typeof AccountID != 'undefined' && AccountID != null && AccountID != 'null'){
                            if($('select[name='+AccountID+'] option:selected').text() != 'Select')
                                NeonAccountNames[AccountID] = $('select[name='+AccountID+'] option:selected').text();
                        }
                    });
                    $('#table-6 tr .rowcheckbox:checked').each(function(i, el) {
                        //console.log($(this).val());
                        AccountID = $(this).val();
                        if(typeof AccountID != 'undefined' && AccountID != null && AccountID != 'null'){
                            if($('select[name='+AccountID+'] option:selected').text() != 'Select')
                                NeonAccountNames[AccountID] = $('select[name='+AccountID+'] option:selected').text();
                        }
                    });
                }else{
                    var i = 0;
                    $('#table-5 tr .rowcheckbox:checked').each(function() {
                        //console.log($(this).val());
                        AccountID = $(this).val();
                        if(typeof AccountID != 'undefined' && AccountID != null && AccountID != 'null'){
                            if($('select[name='+AccountID+'] option:selected').text() != 'Select') {
                                AccountIPIDs[i++] = AccountID;
                                NeonAccountNames[AccountID] = $('select[name=' + AccountID + '] option:selected').text();
                            }
                        }
                    });
                    $('#table-6 tr .rowcheckbox:checked').each(function() {
                        AccountID = $(this).val();
                        if(typeof AccountID != 'undefined' && AccountID != null && AccountID != 'null'){
                            if($('select[name='+AccountID+'] option:selected').text() != 'Select') {
                                AccountIPIDs[i++] = AccountID;
                                NeonAccountNames[AccountID] = $('select[name=' + AccountID + '] option:selected').text();
                            }
                        }
                    });
                }
                NeonAccountNames = JSON.stringify(NeonAccountNames);
                //console.log(AccountIPIDs);
                //console.log(NeonAccountNames);
                if(AccountIPIDs.length || criteria==1 ){
                    if(criteria==''){
                        AccountIPIDs=AccountIPIDs.join(",");
                    }
                    $.ajax({
                        url: baseurl + '/import/ips/add_missing_gatewayaccountsip',
                        data: 'TempAccountIPIDs='+AccountIPIDs+'&criteria='+criteria+'&companygatewayid='+gatewayid+'&importprocessid='+importprocessid+'&NeonAccountNames='+NeonAccountNames+'&gateway=sippy',
                        error: function () {
                            toastr.error("error", "Error", toastr_opts);
                        },
                        dataType: 'json',
                        success: function (response) {
                            if (response.status == 'success') {
                                toastr.success(response.message, "Success", toastr_opts);
                                reloadJobsDrodown(0);
                                location.reload();
                            } else {
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                        },
                        type: 'POST'
                    });
                }
            });

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
            padding: 25px;
            height:25%;
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

        /*#tab2-2{
            margin: 0 0 0 50px;
        }*/
        .pager li.disabled{
            display: none;
        }
        .export-data{
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
        .gatewayloading, .quickbookloading{
            display:none;
            color: #ffffff;
            background: #303641;
            display: table;
            position: fixed;
            visibility: visible;
            padding: 10px;
            text-align: center;
            left: 50%; top: auto;
            margin: 71px auto;
            z-index: 999;
            border: 1px solid #303641;
        }
        #st1 a,#st2 a,#st3 a{
            cursor: default;
            text-decoration: none;
        }

        #csvimport{
            /*padding: 0 75px;*/
        }
        h5{
            font-size: 14px !important;
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