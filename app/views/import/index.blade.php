@extends('layout.main')
@section('content')
<?php
    $bVisibleRoutingCategory = "hidden";
    if(!empty($rateTable)){
        if($rateTable->Type == $TypeVoiceCall && $rateTable->AppliedTo == RateTable::APPLIED_TO_VENDOR) {
            if($ROUTING_PROFILE == 1) {
                $bVisibleRoutingCategory = "";
            }
        }
    }
?>
    <link rel="stylesheet" type="text/css" href="<?php echo URL::to('/').'/assets/Bootstrap-Dual-Listbox/bootstrap-duallistbox.css'; ?>">
    <script src="<?php echo URL::to('/').'/assets/Bootstrap-Dual-Listbox/jquery.bootstrap-duallistbox.min.js'; ?>" ></script>

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li class="active">
            <strong>Import</strong>
        </li>
    </ol>
    <h3>Import</h3><br/>
    {{--@include('accounts.errormessage')--}}
    <div class="row">
        <div class="col-md-12">
            <form role="form" id="form-upload" name="form-upload" method="post" class="form-horizontal form-groups-bordered" enctype="multipart/form-data">
                <div class="panel panel-primary" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title">
                            Import
                        </div>

                        <div class="panel-options">
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="form-group" id="">
                            <label class="col-sm-2 control-label">
                                <!-- <input id="checkbox_import_rate" name="checkbox_import_rate" value="1" checked="" type="checkbox"/> --> Import Type
                            </label>
                            <div class="col-sm-4">
                                {{ Form::select('importtype', ['' => 'Select','Account' => 'Account' , 'Service' => 'Service' , 'Package' => 'Package'], "" , array("class"=>"select2","id"=>"importtype")) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="field-1" class="col-sm-2 control-label">Upload (.xls, .xlsx, .csv)</label>
                            <div class="col-sm-1">
                                <input name="excel" id="excel" type="file" class="form-control file2 inline btn btn-primary" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" />
                            </div>
                        </div>
                        
                        
                        <!--<div class="form-group hidden" id="SheetBox">
                            <label for="field-1" class="col-sm-2 control-label">Select Sheet</label>
                            <div class="col-sm-4">
                                {{-- Form::select('Sheet', [], "" , array("class"=>"select2 small","id"=>"Sheet")) --}}
                            </div>
                        </div>-->
                        <p style="text-align: right;">
                            <button  type="submit" id="file-upload" class="btn upload btn-primary btn-sm btn-icon icon-left">
                                <i class="glyphicon glyphicon-circle-arrow-up"></i>
                                Upload
                            </button>
                        </p>

                    </div>
                </div>
            </form>
        </div>
    </div>

    
    <style>
        #selectcheckbox-new,#selectcheckbox-deleted{
            padding: 15px 10px;
        }
        .change-selected {
            margin-top: 13px;
            margin-right: 27px;
        }
        #modal-reviewrates .modal-body {
            overflow-y: auto;
        }
        .radio {
            min-height: 16px !important;
        }
    </style>
    <script type="text/javascript">

        

        jQuery(document).ready(function ($) {
            

            $(document).on('submit','#form-upload', function(e) {
                e.preventDefault();
                if($('#importtype').val() == ''){
                    toastr.error('Please select import type first', "Error", toastr_opts);
                }else{
                    var formData = new FormData($('#form-upload')[0]);
                    show_loading_bar(0);
                    $.ajax({
                        url:  '{{URL::to('import/storeimportfiles')}}',  //Server script to process data
                        type: 'POST',
                        dataType: 'json',
                        beforeSend: function(){
                            $('.btn.upload').button('loading');
                            show_loading_bar({
                                pct: 50,
                                delay: 5
                            });
                        },
                        success: function (response) {
                            show_loading_bar({
                                pct: 100,
                                delay: 2
                            });
                            $('.btn.upload').button('reset');
                            if (response.status == 'success') {
                                toastr.success(response.message, "Error", toastr_opts);
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
                }
            });
        

        });
    </script>
@stop
@section('footer_ext')
    @parent
@stop