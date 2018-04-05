@extends('layout.main')
@section('content')
    <ol class="breadcrumb bc-3">
        <li><a href="{{URL::to('/dashboard')}}"><i class="entypo-home"></i>Home</a></li>
        <li><a href="{{URL::to('/rate_tables')}}">Rate Table</a></li>
        <li class="active"><strong>Rate Upload</strong></li>
    </ol>
    <h3>Rate Upload</h3>
    <div class="float-right" >
        <a href="{{URL::to('/rate_tables')}}"  class="btn btn-primary btn-sm btn-icon icon-left" >
            <i class="entypo-floppy"></i>
            Back
        </a>
    </div>
    {{--<ul class="nav nav-tabs bordered">
        <!-- available classes "bordered", "right-aligned" -->
        <li><a href="{{URL::to('/rate_tables/'.$id.'/view')}}"> <span
                        class="hidden-xs">Rate</span>
            </a></li>
        <li class="active"><a href="{{URL::to('/rate_tables/'.$id.'/upload')}}"> <span
                        class="hidden-xs">Rate Upload</span>
            </a></li>
    </ul>--}}
<div class="row">
<div class="col-md-12">
    <form role="form" id="form-upload" name="form-upload" method="post" action="{{URL::to('rate_tables/'.$id.'/process_upload')}}" class="form-horizontal form-groups-bordered" enctype="multipart/form-data">
        <div class="panel panel-primary" data-collapsed="0">
            <div class="panel-heading">
                <div class="panel-title">
                    Upload Rate sheet
                </div>
                
                <div class="panel-options">
                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                </div>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">Upload Template</label>
                    <div class="col-sm-4">
                        {{ Form::select('uploadtemplate', $uploadtemplate, '' , array("class"=>"select2")) }}
                        <input type="hidden" name="Trunk" value="{{$rateTable[0]->TrunkID}}" />
                        <input type="hidden" name="CodeDeckID" value="{{$rateTable[0]->CodeDeckId}}" />
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
                    <label class="col-sm-2 control-label">Settings</label>
                    
                    <div class="col-sm-10">
                        <div class="checkbox ">
                            <label>
                            <input type="hidden" name="checkbox_replace_all" value="0" >
                            <input type="checkbox" id="rd-1" name="checkbox_replace_all" value="1" > Replace all of the existing rates with the rates from the file</label>

                        </div>
                        <div class="checkbox ">
                            <input type="hidden" name="checkbox_rates_with_effected_from" value="0" >
                            <label><input type="checkbox" id="rd-1" name="checkbox_rates_with_effected_from" value="1" checked> Rates with 'effective from' date in the past should be uploaded as effective immediately</label>
                        </div>
                        {{--<div class="checkbox ">
                            <label><input type="checkbox" id="rd-1" name="checkbox_skip_rates_with_same_date" value="1" checked> Skip rates with the same date</label>
                        </div>--}}
                        <div class="checkbox ">
                            <input type="hidden" name="checkbox_add_new_codes_to_code_decks" value="0" >
                            <label><input type="checkbox" id="rd-1" name="checkbox_add_new_codes_to_code_decks" value="1" checked> Add new codes from the file to code decks</label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Note</label>
                    <div class="col-sm-8">
                        
                        <p><i class="glyphicon glyphicon-minus"></i><strong>Allowed Extension</strong> .xls, .xlxs, .csv</p>
                        <p>Please upload the file in given <span style="cursor: pointer" onclick="jQuery('#modal-fileformat').modal('show');" class="label label-info">Format</span></p>

                        <p>Sample File <a class="btn btn-success btn-sm btn-icon icon-left" href="{{URL::to('rate_tables/download_sample_excel_file')}}"><i class="entypo-down"></i>Download</a></p>

                         <i class="glyphicon glyphicon-minus"></i> <strong>Replace all of the existing rates with the rates from the file -</strong> The default option is to add new rates. If there is at least one parameter that differentiates a new rate from the existent one then the new rate will override it. If a rate for a certain prefix exists in the tariff but is not present in the file you received from the carrier, it will remain unchanged. The replace mode uploads all the new rates from the file and marks all the existent rates as discontinued. <br><br>
                        
                        <i class="glyphicon glyphicon-minus"></i> <strong>Rates with 'effective from' date in the past should be uploaded as 'effective immediately' - </strong> Sometimes you might receive a file with rates later than expected, when the moment at which the rates were supposed to become effective has already passed. By default this check box is disabled and a rate that has an 'effective from' date that has passed will be rejected and not included in the tariff. Altematively, you may choose to insert these rates into the tariff and make them effective from the current moment; to do so enable this check box. <br><br>
                        

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
                        Call Rate Rules CSV Importer
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
                        <label for="field-1" class="col-sm-2 control-label">Code*</label>
                        <div class="col-sm-4">
                            {{Form::select('selection[Code]', array(),'',array("class"=>"select2 small"))}}
                        </div>

                        <label for="field-1" class="col-sm-2 control-label">Description*</label>
                        <div class="col-sm-4">
                            {{Form::select('selection[Description]', array(),'',array("class"=>"select2 small"))}}
                        </div>
                    </div>
                    <div class="form-group">
                        <br />
                        <br />
                        <label for="field-1" class="col-sm-2 control-label">Rate*</label>
                        <div class="col-sm-4">
                            {{Form::select('selection[Rate]', array(),'',array("class"=>"select2 small"))}}
                        </div>

                        <label for="field-1" class="col-sm-2 control-label">EffectiveDate <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="If not selected then rates will be uploaded as effective immediately" data-original-title="EffectiveDate">?</span></label>
                        <div class="col-sm-4">
                            {{Form::select('selection[EffectiveDate]', array(),'',array("class"=>"select2 small"))}}
                        </div>
                    </div>
                    <div class="form-group">
                        <br />
                        <br />
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
                        <br />
                        <br />
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
                        <br />
                        <br />
                        <label for="field-1" class="col-sm-2 control-label">Interval1</label>
                        <div class="col-sm-4">
                            {{Form::select('selection[Interval1]', array(),'',array("class"=>"select2 small"))}}
                        </div>

                        <label for=" field-1" class="col-sm-2 control-label">IntervalN</label>
                        <div class="col-sm-4">
                            {{Form::select('selection[IntervalN]', array(),'',array("class"=>"select2 small"))}}
                        </div>
                    </div>
                    <div class="form-group">
                        <br />
                        <br />
                        <label for=" field-1" class="col-sm-2 control-label">Connection Fee</label>
                        <div class="col-sm-4">
                            {{Form::select('selection[ConnectionFee]', array(),'',array("class"=>"select2 small"))}}
                        </div>
                        <label for=" field-1" class="col-sm-2 control-label">Date Format</label>
                        <div class="col-sm-4">
                            {{Form::select('selection[DateFormat]',Company::$date_format ,'',array("class"=>"select2 small"))}}
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
                url:  '{{URL::to('rate_tables/'.$id.'/check_upload')}}',  //Server script to process data
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
            url:'{{URL::to('rate_tables/'.$id.'/ajaxfilegrid')}}',
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
            url:'{{URL::to('rate_tables/'.$id.'/storeTemplate')}}', //Server script to process data
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

    $("#form-upload").submit(function () {
           // return false;
            var formData = new FormData($('#form-upload')[0]);
            show_loading_bar(0);
            $.ajax({
                url:  $('#form-upload').attr("action"),  //Server script to process data
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
        if(data.RateTableFileUploadTemplate){
            $.each( data.RateTableFileUploadTemplate, function( optionskey, option_value ) {
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
                <h4 class="modal-title">Rate Table File Format</h4>
            </div>



            <div class="modal-body">
            <p>All columns are mandatory and the first line should have the column headings.</p>
                        <table class="table responsive">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Destination</th>
                                    <th>Rate</th>
                                    <th>Effective Date</th>
                                    <th>Action</th>
                                    <th>Connection Fee(Opt.)</th>
                                    <th>Interval1(Opt.)</th>
                                    <th>IntervalN(Opt.)</th>

                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>9379</td>
                                    <td>Afghanistan Cellular-Others</td>
                                    <td>0.001</td>
                                    <td> 11-12-2014  12:00:00 AM</td>
                                    <td>I <span data-original-title="Insert" data-content="When action is set to 'I', It will insert new Vendor Rate" data-placement="top" data-trigger="hover" data-toggle="popover" class="label label-info popover-primary">?</span></td>
                                    <td>0.05</td>
                                    <td>1</td>
                                    <td>1</td>
                                </tr>
                                <tr>
                                    <td>9377</td>
                                    <td>Afghanistan Cellular-Areeba</td>
                                    <td>0.002</td>
                                    <td> 11-12-2014  12:00:00 AM</td>
                                    <td>U <span data-original-title="Update" data-content="When action is set to 'U',It will replace existing Vendor Rate" data-placement="top" data-trigger="hover" data-toggle="popover" class="label label-info popover-primary">?</span></td>
                                    <td>0.05</td>
                                    <td>1</td>
                                    <td>1</td>
                                </tr>
                                <tr>
                                    <td>9378</td>
                                    <td>Afghanistan Cellular</td>
                                    <td>0.003</td>
                                    <td> 11-12-2014  12:00:00 AM</td>
                                    <td>D <span data-original-title="Delete" data-content="When action is set to 'D',It will delete existing Vendor Rate" data-placement="top" data-trigger="hover" data-toggle="popover" class="label label-info popover-primary">?</span></td>
                                    <td>0.05</td>
                                    <td>1</td>
                                    <td>1</td>
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