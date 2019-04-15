@extends('layout.main')
@section('content')

    <link rel="stylesheet" type="text/css" href="<?php echo URL::to('/').'/assets/Bootstrap-Dual-Listbox/bootstrap-duallistbox.css'; ?>">
    <script src="<?php echo URL::to('/').'/assets/Bootstrap-Dual-Listbox/jquery.bootstrap-duallistbox.min.js'; ?>" ></script>

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li class="active">
            <strong>Upload Rates</strong>
        </li>
    </ol>
    <h3>Upload Rates</h3><br/>
    {{--@include('accounts.errormessage')--}}
    <div class="row">
        <div class="col-md-12">
            <form role="form" id="form-upload" name="form-upload" method="post" class="form-horizontal form-groups-bordered" enctype="multipart/form-data">
                <div class="panel panel-primary" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title">
                            Upload Rate Sheet
                        </div>

                        <div class="panel-options">
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label for="field-1" class="col-sm-2 control-label">Rate Upload Type</label>
                            <div class="col-sm-10">
                                @foreach($uploadtypes as $key => $value)
                                    <div class="radio radio-replace {{($key == $RateUploadType ? 'checked' : '')}}">
                                        {{ Form::radio('RateUploadType', $key, ($key == $RateUploadType ? true : false) , array("class"=>"RateUploadType")) }}
                                        <label>{{ $value }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group {{RateUpload::vendor.'content'}} typecontentbox {{ $RateUploadType != RateUpload::vendor ? 'hidden' : '' }}">
                            <label for="field-1" class="col-sm-2 control-label">Vendor</label>
                            <div class="col-sm-4">
                                {{ Form::select('Vendor', $Vendors, $VendorID , array("class"=>"select2","id"=>RateUpload::vendor)) }}
                            </div>
                        </div>
                        <div class="form-group {{RateUpload::ratetable.'content'}} typecontentbox {{ $RateUploadType != RateUpload::ratetable ? 'hidden' : '' }}">
                            <label for="field-1" class="col-sm-2 control-label">Ratetable</label>
                            <div class="col-sm-4">
                                {{ Form::select('Ratetable', $Ratetables, $RatetableID , array("class"=>"select2","id"=>RateUpload::ratetable)) }}
                            </div>
                        </div>
                        <div class="form-group {{RateUpload::customer.'content'}} typecontentbox {{ $RateUploadType != RateUpload::customer ? 'hidden' : '' }}">
                            <label for="field-1" class="col-sm-2 control-label">Customer</label>
                            <div class="col-sm-4">
                                {{ Form::select('Customer', $Customers, $CustomerID , array("class"=>"select2","id"=>RateUpload::customer)) }}
                            </div>
                        </div>
                        <div class="form-group {{RateUpload::vendor.'content'}} typecontentbox {{ $RateUploadType != RateUpload::vendor ? 'hidden' : '' }}">
                            <label for="field-1" class="col-sm-2 control-label">Trunk</label>
                            <div class="col-sm-4">
                                {{ Form::select('Trunk', [], "" , array("class"=>"select2 small","id"=>"Trunk")) }}
                                {{ Form::hidden('isTrunks', "0" , array("class"=>"form-control","id"=>"isTrunks","disabled"=>"disabled")) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="field-1" class="col-sm-2 control-label">Upload Template</label>
                            <div class="col-sm-4">
                                {{ Form::select('uploadtemplate', [], "" , array("class"=>"select2","id"=>"uploadtemplate")) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="field-1" class="col-sm-2 control-label">Upload (.xls, .xlsx, .csv)</label>
                            <div class="col-sm-1">
                                <input name="excel" id="excel" type="file" class="form-control file2 inline btn btn-primary" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" />
                            </div>
                        </div>
                        <div class="form-group hidden" id="rateBox">
                            <label class="col-sm-2 control-label">
                                <!-- <input id="checkbox_import_rate" name="checkbox_import_rate" value="1" checked="" type="checkbox"/> --> Import Rates From Sheet
                            </label>
                            <div class="col-sm-4">
                                {{ Form::select('importratesheet', [], "" , array("class"=>"select2","id"=>"importrate")) }}
                            </div>
                        </div>
                        <div class="form-group hidden" id="dialcodesBox">
                            <label class="col-sm-2 control-label">
                                <!--<input id="checkbox_import_dialcodes" name="checkbox_import_dialcodes" value="0" type="checkbox"/>-->
                                Import Dial Codes From Sheet
                            </label>
                            <div class="col-sm-4">
                                {{ Form::select('importdialcodessheet', [], "" , array("class"=>"select2","id"=>"importdialcodes")) }}
                            </div>
                        </div>
                        <!--<div class="form-group hidden" id="SheetBox">
                            <label for="field-1" class="col-sm-2 control-label">Select Sheet</label>
                            <div class="col-sm-4">
                                {{-- Form::select('Sheet', [], "" , array("class"=>"select2 small","id"=>"Sheet")) --}}
                                </div>
                            </div>-->
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Settings</label>
                            <div class="col-sm-10">
                                <div class="checkbox hidden">
                                    <label>
                                        <input type="hidden" name="checkbox_replace_all" value="0" >
                                        <input type="checkbox" id="rd-1" name="checkbox_replace_all" value="1" > Replace all of the existing rates with the rates from the file
                                    </label>
                                </div>
                                <div class="checkbox ">
                                    <input type="hidden" name="checkbox_rates_with_effected_from" value="0" >
                                    <label><input type="checkbox" id="rd-1" name="checkbox_rates_with_effected_from" value="1" checked> Rates with 'effective from' date in the past should be uploaded as effective immediately</label>
                                </div>
                                <div class="checkbox ">
                                    <input type="hidden" name="checkbox_add_new_codes_to_code_decks" value="0" >
                                    <label><input type="checkbox" id="rd-1" name="checkbox_add_new_codes_to_code_decks" value="1" checked> Add new codes from the file to code decks</label>
                                </div>
                                <div class="checkbox review_vendor_rate">
                                    <input type="hidden" name="checkbox_review_rates" value="0" >
                                    <label><input type="checkbox" name="checkbox_review_rates" id="checkbox_review_rates" value="1"> Review Rates</label> <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="if checked, review screen will be displayed before processing" data-original-title="Review Rates">?</span>
                                </div>
                                <div class="radio ">
                                    <label><input type="radio" name="radio_list_option" value="1" checked>Complete File</label> <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="if complete file, codes which are not in the file will be deleted." data-original-title="Completed List">?</span>
                                    <br/>
                                    <label><input type="radio" name="radio_list_option" value="2">Partial File</label> <span class="label label-info popover-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="if partial file, codes only in the file will be processed." data-original-title="Partial List">?</span>
                                </div>
                                <div style="margin-top:10px;">
                                    <label for="field-1" class="col-sm-2 control-label" style="text-align: right;">Skips rows from Start (Rate)</label>
                                    <div class="col-sm-3" style="padding-left:40px;">
                                        <input name="start_row" type="number" class="form-control" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" style="" placeholder="Skips rows from Start" min="0" value="0">
                                    </div>
                                    <label class="col-sm-3 control-label" style="text-align: right;">Skips rows from Bottom (Rate)</label>
                                    <div class="col-sm-3">
                                        <input name="end_row" type="number" class="form-control" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" placeholder="Skips rows from Bottom" min="0" value="0">
                                    </div>
                                </div>
                                <br/><br/>
                                <div class="skip_div_2" style="margin-top:10px;display:none;">
                                    <label for="field-1" class="col-sm-2 control-label" style="text-align: right;">Skips rows from Start (DialCodes)</label>
                                    <div class="col-sm-3" style="padding-left:40px;">
                                        <input name="start_row_sheet2" type="number" class="form-control" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" style="" placeholder="Skips rows from Start" min="0" value="0">
                                    </div>
                                    <label class="col-sm-3 control-label" style="text-align: right;">Skips rows from Bottom (DialCodes)</label>
                                    <div class="col-sm-3">
                                        <input name="end_row_sheet2" type="number" class="form-control" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" placeholder="Skips rows from Bottom" min="0" value="0">
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Note</label>
                            <div class="col-sm-8">
                                <p><i class="glyphicon glyphicon-minus"></i><strong>Allowed Extension</strong> .xls, .xlsx, .csv</p>
                                <!-- <p>Please upload the file in given <span style="cursor: pointer" onclick="jQuery('#modal-fileformat').modal('show');" class="label label-info">Format</span></p>
                                <p>Sample File <a class="btn btn-success btn-sm btn-icon icon-left" href="{{URL::to('vendor_rates/download_sample_excel_file')}}"><i class="entypo-down"></i>Download</a></p>-->
                                <i class="glyphicon glyphicon-minus"></i> <strong>Replace all of the existing rates with the rates from the file -</strong> The default option is to add new rates. If there is at least one parameter that differentiates a new rate from the existent one then the new rate will override it. If a rate for a certain prefix exists in the tariff but is not present in the file you received from the carrier, it will remain unchanged. The replace mode uploads all the new rates from the file and marks all the existent rates as discontinued. <br><br>
                                <i class="glyphicon glyphicon-minus"></i> <strong>Rates with 'effective from' date in the past should be uploaded as 'effective immediately' - </strong> Sometimes you might receive a file with rates later than expected, when the moment at which the rates were supposed to become effective has already passed. By default this check box is disabled and a rate that has an 'effective from' date that has passed will be rejected and not included in the tariff. Altematively, you may choose to insert these rates into the tariff and make them effective from the current moment; to do so enable this check box. <br><br>
                            </div>
                        </div>

                        {{ Form::hidden('RateType', RateType::getRateTypeIDBySlug(RateType::SLUG_DID) , array()) }}

                        <p style="text-align: right;">
                            <button  type="submit" class="btn upload btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                                <i class="glyphicon glyphicon-circle-arrow-up"></i>
                                Upload
                            </button>
                        </p>

                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row hidden" id="add-template">
        <div class="col-md-12">
            <form role="form" name="add-template-form" id="add-template-form" method="post" class="form-horizontal form-groups-bordered">
                <input name="start_row" type="hidden" value="0" min="0">
                <input name="end_row" type="hidden" value="0" min="0">
                <input type="hidden" name="ProcessID" id="ProcessID" value="" />
                <input type="hidden" name="TemplateType" id="TemplateType" value="{{FileUploadTemplateType::getTemplateType(FileUploadTemplate::TEMPLATE_VENDOR_RATE)}}" />
                <input type="hidden" name="FileUploadTemplateID" id="FileUploadTemplateID" value="" />

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
                        <div class="panel panel-primary panel-collapse" data-collapsed="0">
                            <div class="panel-heading">
                                <div class="panel-title">
                                    Import Options
                                </div>

                                <div class="panel-options">
                                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                                </div>
                            </div>
                            <div class="panel-body" style="display: none;">
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
                                    <button type="button" class="btn btn-primary btn-sm btn-icon icon-left" id="btn-manage-columns">
                                        <i class="entypo-pencil"></i>
                                        Manage Columns
                                    </button>
                                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                                </div>
                            </div>

                            <div class="panel-body field-remaping" id="mapping">
                                <?php $columns = array(); ?>
                                @include('fileuploadtemplates.didrateuploadtemplate')
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
                                <ul class="nav nav-tabs">
                                    <li class="active box_dialcode"><a href="#tabs1" data-toggle="tab">Rates</a></li>
                                    <li class="box_dialcode"><a href="#tabs2" data-toggle="tab">Dial Codes</a></li>
                                </ul>
                                <div class="tab-content" style="overflow: hidden;margin-top: 15px;">
                                    <div class="tab-pane active" id="tabs1">
                                        <table class="table table-bordered datatable" id="table-4">
                                            <thead>
                                            <tr>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="tab-pane" id="tabs2">
                                        <table class="table table-bordered datatable" id="table-5">
                                            <thead>
                                            <tr>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
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
        var AllTimezones = JSON.parse('{{json_encode($AllTimezones)}}');

        var all_selectable_fields   = ['EndDate','Action','ActionDelete','OriginationCode'/*,'CountryCode','City','Tariff','AccessType'*/];
        var all_available_fields    = ['EndDate','Action','ActionDelete','OriginationCode'/*,'CountryCode','City','Tariff','AccessType'*/];
        // FromCurrency is removed from above 2 variables as we don't need it in did upload
        var all_occupied_fields     = [];
        var relational_columns      = {
            EndDate                 : ['EndDate'],
            Action                  : ['Action','ActionDelete'],
            ActionDelete            : ['Action','ActionDelete'],
            FromCurrency            : ['FromCurrency'],
            OriginationCode         : ['OriginationCode'],
            /*CountryCode             : ['CountryCode'],
            City                    : ['City'],
            Tariff                  : ['Tariff'],
            AccessType              : ['AccessType']*/
        };
        var columns_text            = {
            EndDate                 : 'End Date',
            Action                  : 'Action',
            ActionDelete            : 'Action Delete',
            FromCurrency            : 'From Currency',
            OriginationCode         : 'Origination',
            /*CountryCode             : 'Country Code',
            City                    : 'City',
            Tariff                  : 'Tariff',
            AccessType              : 'Access Type'*/
        };

        var all_selectable_timezone_fields   = ['CostPerCall','CostPerMinute','SurchargePerCall','SurchargePerMinute','OutpaymentPerCall','OutpaymentPerMinute','Surcharges','Chargeback','CollectionCostAmount','CollectionCostPercentage','RegistrationCostPerNumber'];
        var all_available_timezone_fields    = ['CostPerCall','CostPerMinute','SurchargePerCall','SurchargePerMinute','OutpaymentPerCall','OutpaymentPerMinute','Surcharges','Chargeback','CollectionCostAmount','CollectionCostPercentage','RegistrationCostPerNumber'];
        var all_occupied_timezone_fields     = [];
        var all_occupied_timezone_fields2    = [];
        var dual_mapping_columns_all         = ['Code','AccessType','CountryCode','City','Tariff'];
        var dual_mapping_columns_static      = ['Code','AccessType','City','Tariff','CountryCode'];
        var dual_mapping_columns_dynamic     = [];
        var relational_columns_timezone      = {
            CostPerCall                 : [],
            CostPerMinute               : [],
            SurchargePerCall            : [],
            SurchargePerMinute          : [],
            OutpaymentPerCall           : [],
            OutpaymentPerMinute         : [],
            Surcharges                  : [],
            Chargeback                  : [],
            CollectionCostAmount        : [],
            CollectionCostPercentage    : [],
            RegistrationCostPerNumber   : []
        };
        var currency_columns_timezone       = [];
        var currency_static_columns_timezone= {
            OneOffCost: [],
            MonthlyCost: []
        }
        for(TimezonesID in AllTimezones) {
            $id = TimezonesID == 1 ? '' : TimezonesID;
            relational_columns_timezone.CostPerCall.push('CostPerCall'+$id);
            relational_columns_timezone.CostPerMinute.push('CostPerMinute'+$id);
            relational_columns_timezone.SurchargePerCall.push('SurchargePerCall'+$id);
            relational_columns_timezone.SurchargePerMinute.push('SurchargePerMinute'+$id);
            relational_columns_timezone.OutpaymentPerCall.push('OutpaymentPerCall'+$id);
            relational_columns_timezone.OutpaymentPerMinute.push('OutpaymentPerMinute'+$id);
            relational_columns_timezone.Surcharges.push('Surcharges'+$id);
            relational_columns_timezone.Chargeback.push('Chargeback'+$id);
            relational_columns_timezone.CollectionCostAmount.push('CollectionCostAmount'+$id);
            relational_columns_timezone.CollectionCostPercentage.push('CollectionCostPercentage'+$id);
            relational_columns_timezone.RegistrationCostPerNumber.push('RegistrationCostPerNumber'+$id);

            currency_columns_timezone['CostPerCall'+$id]                = 'CostPerCallCurrency'+$id;
            currency_columns_timezone['CostPerMinute'+$id]              = 'CostPerMinuteCurrency'+$id;
            currency_columns_timezone['SurchargePerCall'+$id]           = 'SurchargePerCallCurrency'+$id;
            currency_columns_timezone['SurchargePerMinute'+$id]         = 'SurchargePerMinuteCurrency'+$id;
            currency_columns_timezone['OutpaymentPerCall'+$id]          = 'OutpaymentPerCallCurrency'+$id;
            currency_columns_timezone['OutpaymentPerMinute'+$id]        = 'OutpaymentPerMinuteCurrency'+$id;
            currency_columns_timezone['Surcharges'+$id]                 = 'SurchargesCurrency'+$id;
            currency_columns_timezone['Chargeback'+$id]                 = 'ChargebackCurrency'+$id;
            currency_columns_timezone['CollectionCostAmount'+$id]       = 'CollectionCostAmountCurrency'+$id;
            currency_columns_timezone['RegistrationCostPerNumber'+$id]  = 'RegistrationCostPerNumberCurrency'+$id;

            currency_static_columns_timezone.OneOffCost.push('OneOffCostCurrency'+$id);
            currency_static_columns_timezone.MonthlyCost.push('MonthlyCostCurrency'+$id);
        }
        var columns_timezone_text            = {
            CostPerCall                 : 'Cost Per Call',
            CostPerMinute               : 'Cost Per Minute',
            SurchargePerCall            : 'Surcharge Per Call',
            SurchargePerMinute          : 'Surcharge Per Minute',
            OutpaymentPerCall           : 'Outpayment Per Call',
            OutpaymentPerMinute         : 'Outpayment Per Minute',
            Surcharges                  : 'Surcharges',
            Chargeback                  : 'Chargeback',
            CollectionCostAmount        : 'Collection Cost Amount',
            CollectionCostPercentage    : 'Collection Cost (%)',
            RegistrationCostPerNumber   : 'Registration Cost Per Number'
        };

        var columnsMultiSelect;
        var fileData;

        jQuery(document).ready(function ($) {
            getUploadTemplates('{{$RateUploadType}}'); //get templates by type for ex. vendor,customer,ratetable rate upload template

            var columnsMultiSelect = $('#columnsMultiSelect').bootstrapDualListbox({
                nonselectedlistlabel: 'Non-selected',
                selectedlistlabel: 'Selected',
                filterPlaceHolder: 'Search',
                moveonselect: false,
                preserveselectiononmove: 'moved',
            });

            $("input[name='RateUploadType']").on('change', function(){
                var Type = $("input[name=RateUploadType]:checked").val();
                var id   = $("#"+Type).val();
                $('.typecontentbox').hide().addClass('hidden');
                $('.'+Type+'content').show().removeClass('hidden');
                getUploadTemplates(Type);
                getTrunk(Type,id);
                $('.btn.upload').removeAttr('disabled');
            });
            $("select[name='Vendor']").on('change', function(){
                var Type = $("input[name=RateUploadType]:checked").val();
                var id   = $("select[name=Vendor]").val();

                $.when(getTrunk(Type,id)).then(function() {
                    if($('#isTrunks').val() == '0') {
                        toastr.error("You can not upload rate against this account, To upload rates against this account you need to setup trunk against this account", "Error", toastr_opts);
                        $('.btn.upload').attr('disabled','disabled');
                    } else {
                        $('.btn.upload').removeAttr('disabled');
                    }
                });

            });
            $('.dialcodeseperator').on('change', function() {
                var name = $(this).attr('name');
                var val  = $(this).val();

                if(name == 'selection[DialCodeSeparator]') {
                    $('select[name="selection[OriginationDialCodeSeparator]"]').select2("val","");
                    $('select[name="selection2[DialCodeSeparator]"]').select2("val","");
                    $('select[name="selection2[OriginationDialCodeSeparator]"]').select2("val","");
                } else if(name == 'selection2[DialCodeSeparator]') {
                    $('select[name="selection[DialCodeSeparator]"]').select2("val","");
                    $('select[name="selection[OriginationDialCodeSeparator]"]').select2("val","");
                    $('select[name="selection2[OriginationDialCodeSeparator]"]').select2("val","");
                } else if(name == 'selection[OriginationDialCodeSeparator]') {
                    $('select[name="selection[DialCodeSeparator]"]').select2("val","");
                    $('select[name="selection2[DialCodeSeparator]"]').select2("val","");
                    $('select[name="selection2[OriginationDialCodeSeparator]"]').select2("val","");
                } else if(name == 'selection2[OriginationDialCodeSeparator]') {
                    $('select[name="selection[DialCodeSeparator]"]').select2("val","");
                    $('select[name="selection[OriginationDialCodeSeparator]"]').select2("val","");
                    $('select[name="selection2[DialCodeSeparator]"]').select2("val","");
                }
            });
            $('#form-upload select[name=Ratetable]').on('change', function() {
                var id = $(this).val();
                var link = '{{URL::to('rate_upload/[id]/ratetable')}}';
                link = link.replace('[id]',id);
                location.href = link;
            });
            /*$(document).on('change','#add-template-form select[name="selection[City]"],#add-template-form select[name="selection[Tariff]"]', function() {
                var name = $(this).attr('name');
                if($(this).val() != '') {
                    if(name == "selection[City]") {
                        $('#add-template-form select[name="selection[Tariff]"]').val('').trigger('change');
                    } else {
                        $('#add-template-form select[name="selection[City]"]').val('').trigger('change');
                    }
                }
            });*/

            setTimeout(function(){getRateTableDetails()},100);
            /*$("select[name=Ratetable]").on('change', function(){
             var Type = $("input[name=RateUploadType]:checked").val();
             var id   = $("select[name=Ratetable]").val();
             getTrunk(Type,id);
             });*/


            $(document).on('change','#excel', function() {
                var formData = new FormData($('#form-upload')[0]);
                show_loading_bar(0);
                $.ajax({
                    url:  '{{URL::to('rate_upload/getSheetNamesFromExcel')}}',  //Server script to process data
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
                            var SheetNames = response.SheetNames;
                            var Extension = response.FileExtesion;
                            var html = '<option value="">Please Select Sheet</option>';
                            var html2 = '<option value="">Please Select Sheet</option>';

                            for (var i = 0; i < SheetNames.length; i++) {
                                if (i == 0)
                                    html += '<option value="' + SheetNames[i] + '" selected="selected">' + SheetNames[i] + '</option>';
                                else
                                    html += '<option value="' + SheetNames[i] + '">' + SheetNames[i] + '</option>';

                                html2 += '<option value="' + SheetNames[i] + '">' + SheetNames[i] + '</option>';
                            }

                            $("#importrate").select2("val", "");
                            $('#importrate').html(html);
                            // $("#importrate").select2("destroy").select2({placeholder: "Select a state"});
                            $('#rateBox').removeClass('hidden');

                            /*if (SheetNames.length > 1)
                            {
                                $("#importdialcodes").select2("val", "");
                                $('#importdialcodes').html(html2);
                                $('#dialcodesBox').removeClass('hidden');
                            }
                            else
                            {
                                if(!$("#dialcodesBox").hasClass("hidden")){
                                    $('#dialcodesBox').addClass('hidden');
                                }
                            }*/

                            var isMobileVersion = document.getElementsByClassName('snake--mobile');
                            if (isMobileVersion.length > 0) {
                                // elements with class "snake--mobile" exist
                            }

                            var importratesheet = $('option:selected', $('#uploadtemplate')).attr('importratesheet');
                            if(importratesheet != undefined && importratesheet != '') {
                                $('#importrate').val(importratesheet);
                            }
                            $('#importrate').trigger('change');

                            var dialcodeopt = $('#uploadtemplate').attr('importdialcodessheet');
                            if(dialcodeopt !== "undefined") {
                                var importdialcodessheet = $('option:selected', $('#uploadtemplate')).attr('importdialcodessheet');
                                if (importdialcodessheet != '') {
                                    if (importdialcodessheet != undefined && importdialcodessheet != '') {
                                        $('#importdialcodes').val(importdialcodessheet);
                                    }
                                    $('#importdialcodes').trigger('change');
                                }
                            }

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
            });

            $("#importdialcodes, #importrate").change(function() {
                var sheet1 = $('#importrate').val();
                var sheet2 = $('#importdialcodes').val();
                var dialcodeid = $(this).attr('id');
                if(dialcodeid == 'importdialcodes') {
                    if (sheet2 == "" || sheet2 == null) {
                        $("input[name=start_row_sheet2]").val('');
                        $("input[name=end_row_sheet2]").val('');
                        $(".skip_div_2").hide();
                    }
                    else {
                        $(".skip_div_2").show();
                    }
                }

                /*if(sheet1 == sheet2)
                 {
                 $("a[href='#tab2']").hide();
                 $("a[href='#tabs2']").hide();
                 }
                 else
                 {
                 if(sheet2 != null) {
                 $("a[href='#tab2']").show();
                 $("a[href='#tabs2']").show();
                 }
                 if(sheet2 == '') {
                 $("a[href='#tab2']").hide();
                 $("a[href='#tabs2']").hide();
                 }
                 }*/
            });

            $(".numbercheck").keypress(function (e) {
                //if the letter is not digit then display error and don't type anything
                if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                    //display error message
                    return false;
                }
            });

            $("#form-upload select[name='uploadtemplate']").change(function(){
                var option=$(this).find("option[value='"+$(this).val()+"']");
                var start_row   = option.attr("start_row");
                var start_row_sheet2   = option.attr("start_row_sheet2");
                var end_row     = option.attr("end_row");
                var end_row_sheet2    = option.attr("end_row_sheet2");
                //var Sheet       = option.attr("Sheet");
                var importratesheet       = option.attr("importratesheet");
                var importdialcodessheet       = option.attr("importdialcodessheet");

                $("#form-upload input[name=start_row]").val(start_row);
                $("#form-upload input[name=start_row_sheet2]").val(start_row_sheet2);
                $("#form-upload input[name=end_row]").val(end_row);
                $("#form-upload input[name=end_row_sheet2]").val(end_row_sheet2);
                //$("#form-upload select[name=Sheet]").val(Sheet);
                $("#form-upload select[name=importratesheet]").val(importratesheet);
                $("#form-upload select[name=importdialcodessheet]").val(importdialcodessheet);

                $("#form-upload select[name='importratesheet']").trigger('change');
                $("#form-upload select[name='importdialcodessheet']").trigger('change');
            });

            $("#form-upload [name='checkbox_replace_all']").change(function(){
                if($(this).prop("checked")){
                    $('#checkbox_review_rates').attr('checked', false);
                    $('.review_vendor_rate').hide();
                }else{
                    $('.review_vendor_rate').show();
                }
            });

            $("#form-upload [name='checkbox_replace_all']").trigger('change');

            $('.btn.upload').click(function(e){
                e.preventDefault();
                var ratesheet = $('#importrate').val();

                if(ratesheet == null || ratesheet == ''){
                    toastr.error("Please Select a Rate Sheet", "Error", toastr_opts);
                    return false;
                }
                var formData = new FormData($('#form-upload')[0]);
                var importdialcodessheet = formData.get('importdialcodessheet');
                show_loading_bar(0);
                $.ajax({
                    url:  '{{URL::to('rate_upload/checkUpload')}}',  //Server script to process data
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
                        $('.btn.upload').button('reset');
                        if (response.status == 'success') {
                            var data = response.data;
                            createGrid(data);
                            var data2 = response.data2;
                            if(data2 != '')
                            {
                                createGrid2(data2);
                            }

                            // if dialcode sheet selected then hide some columns
                            if(importdialcodessheet != '' && importdialcodessheet != null) {
                                $('#add-template-form select[name="selection[CountryCode]"]').select2("val","");
                                $('#add-template-form select[name="selection[Code]"]').select2("val","");
                                $('#add-template-form select[name="selection[OriginationCode]"]').select2("val","");
                                $('#add-template-form select[name="selection[DialCodeSeparator]"]').select2("val","");
                                $('#add-template-form select[name="selection[OriginationDialCodeSeparator]"]').select2("val","");
                                $('.box_code').hide();
                                $('.box_dialcode').show();
                            } else {
                                $('.box_dialcode').hide();
                                $('#tab2,#tabs2').removeClass('active');
                                $('#tab1,#tabs1').addClass('active');
                                $('li.box_dialcode a[href="#tab2"],li.box_dialcode a[href="#tabs2"]').parent('li').removeClass('active');
                                $('li.box_dialcode a[href="#tab1"],li.box_dialcode a[href="#tabs1"]').parent('li').addClass('active');
                                $('.box_code').show();
                            }

                            $('#add-template').removeClass('hidden');
                            var scrollTo = $('#add-template').offset().top;
                            $('html, body').animate({scrollTop:scrollTo}, 1000);
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                        //alert(response.message);

                        if($('#checkbox_review_rates').is(':checked')) {
                            $('#save_template').addClass('reviewrates');
                        } else {
                            $('#save_template').removeClass('reviewrates');
                        }
                        $('#ProcessID').val('');
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
                //$('#table-4_processing').removeClass('hidden');
                // $('#table-5_processing').removeClass('hidden');
                var formData = new FormData($('#add-template-form')[0]);
                var poData = $(document.forms['form-upload']).serializeArray();
                for (var i=0; i<poData.length; i++){
                    if(poData[i].name!='excel'){
                        formData.append(poData[i].name, poData[i].value);
                    }
                }
                $.ajax({
                    url:'{{URL::to('rate_upload/ajaxfilegrid')}}',
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
                            var data2 = response.data2;
                            if(data2 != '')
                            {
                                createGrid2(data2);
                            }
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                        // $('#table-4_processing').addClass('hidden');
                        // $('#table-5_processing').addClass('hidden');
                    },
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false
                });
            });

            $('#save_template2').click(function() {
                $("#save_template").removeClass('reviewrates');
                $("#save_template").click();
                $("#save_template").addClass('reviewrates');
            });

            $("#save_template").click(function(e){
                e.preventDefault();
                if($("#save_template").hasClass('reviewrates')) {
                    var formData = new FormData($('#add-template-form')[0]);
                    var poData = $(document.forms['form-upload']).serializeArray();
                    for (var i = 0; i < poData.length; i++) {
                        if (poData[i].name != 'excel') {
                            formData.append(poData[i].name, poData[i].value);
                        }
                    }
                    $.ajax({
                        url: '{{URL::to('rate_upload/reviewRates')}}', //Server script to process data
                        type: 'POST',
                        dataType: 'json',
                        beforeSend: function () {
                            $('.btn.save').button('loading');
                        },
                        success: function (response) {
                            $('.btn.save').button('reset');
                            if (response.status == 'success') {
                                toastr.success(response.message, "Success", toastr_opts);
                                $("#FileUploadTemplateID").val(response.FileUploadTemplateID);
                                getReviewRates(response.ProcessID,{});
                                $('#ProcessID').val(response.ProcessID);
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
                } else {
                    var formData = new FormData($('#add-template-form')[0]);
                    var poData = $(document.forms['form-upload']).serializeArray();
                    for (var i = 0; i < poData.length; i++) {
                        if (poData[i].name != 'excel') {
                            formData.append(poData[i].name, poData[i].value);
                        }
                    }
                    $.ajax({
                        url: '{{URL::to('rate_upload/storeTemplate')}}', //Server script to process data
                        type: 'POST',
                        dataType: 'json',
                        beforeSend: function () {
                            $('.btn.save').button('loading');
                        },
                        success: function (response) {
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
                }
            });

            $(document).on('click', '#change_enddate', function() {
                var criteria = '';
                var RateTableDIDRateIDs = [];

                var ProcessID = $('#ProcessID').val();

                if($('#selectallbutton-deleted').is(':checked')){
                    criteria = 1;
                }else{
                    var i = 0;
                    $('#table-reviewrates-deleted tr .rowcheckbox:checked').each(function(i, el) {
                        VendorRateID = $(this).val();
                        if(typeof VendorRateID != 'undefined' && VendorRateID != null && VendorRateID != 'null'){
                            RateTableDIDRateIDs[i++] = VendorRateID;
                        }
                    });
                }

                if((RateTableDIDRateIDs.length || criteria==1) && (ProcessID != '' && ProcessID != 'undefined' && ProcessID != null && ProcessID != 'null') ){
                    $('#EndDate').val('');
                    $('#modal-change-selected-enddate').modal('show');
                }
            });

            $("#frm-change-selected-enddate").submit(function(e) {
                e.preventDefault();
                var criteria = '';
                var RateTableDIDRateIDs = [];

                var ProcessID = $('#ProcessID').val();
                var TrunkID   = $('#Trunk').val();

                if($('#selectallbutton-deleted').is(':checked')){
                    criteria = 1;
                }else{
                    var i = 0;
                    $('#table-reviewrates-deleted tr .rowcheckbox:checked').each(function(i, el) {
                        VendorRateID = $(this).val();
                        if(typeof VendorRateID != 'undefined' && VendorRateID != null && VendorRateID != 'null'){
                            RateTableDIDRateIDs[i++] = VendorRateID;
                        }
                    });
                }

                if((RateTableDIDRateIDs.length || criteria==1) && (ProcessID != '' && ProcessID != 'undefined' && ProcessID != null && ProcessID != 'null') ){
                    if(criteria==''){
                        TempRateIDs=RateTableDIDRateIDs.join(",");
                    }
                    if (!confirm('Are you sure you want to change selected rates EndDate?')) {
                        $(".btn.save").button('reset');
                        return;
                    }

                    var OriginationCode = $('#reviewrates-deleted-search input[name="OriginationCode"]').val();
                    var Code            = $('#reviewrates-deleted-search input[name="Code"]').val();
                    var Timezone        = $('#reviewrates-deleted-search select[name="Timezone"]').val();
                    var RateUploadType  = $("input[name=RateUploadType]:checked").val();
                    var VendorID        = $("select[name=Vendor]").val();
                    var CustomerID      = $("select[name=Customer]").val();
                    var RateTableID     = $("select[name=Ratetable]").val();

                    $.ajax({
                        url: '{{URL::to('rate_upload/updateTempReviewRates')}}',
                        data: 'Action=Deleted&TrunkID='+TrunkID+'&TempRateIDs='+TempRateIDs+'&criteria='+criteria+'&ProcessID='+ProcessID+'&Code='+Code+'&OriginationCode='+OriginationCode+'&Timezone='+Timezone+'&RateUploadType='+RateUploadType+'&VendorID='+VendorID+'&CustomerID='+CustomerID+'&RateTableID='+RateTableID+'&'+$('#frm-change-selected-enddate').serialize(),
                        error: function () {
                            toastr.error("error", "Error", toastr_opts);
                        },
                        dataType: 'json',
                        success: function (response) {
                            $(".btn.save").button('reset');
                            if (response.status == 'success') {
                                toastr.success(response.message, "Success", toastr_opts);
                                $('#modal-change-selected-enddate').modal('hide');
                                checked_deleted = '';
                                $("#selectall-deleted").prop("checked", false).prop('disabled', false);
                                var $searchFilter = {};
                                $searchFilter.OriginationCode = OriginationCode;
                                $searchFilter.Code = Code;
                                $searchFilter.Timezone = Timezone;
                                getDeleteRates(ProcessID, $searchFilter);
                            } else {
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                        },
                        type: 'POST'
                    });
                }

                $(".btn.save").button('reset');

                return false;
            });

            $("#reviewrates-new-search,#reviewrates-increased-decreased-search,#reviewrates-deleted-search").submit(function(e) {
                e.preventDefault();
                var $ProcessID = $('#ProcessID').val();
                var OriginationCode, Code, Timezone;
                var $searchFilter = {};

                if($(this).attr('id') == 'reviewrates-new-search') {
                    $searchFilter.OriginationCode = OriginationCode = $("#reviewrates-new-search input[name='OriginationCode']").val();
                    $searchFilter.Code = Code = $("#reviewrates-new-search input[name='Code']").val();
                    $searchFilter.Timezone = Timezone = $("#reviewrates-new-search select[name='Timezone']").val();
                    $searchFilter.City = City = $("#reviewrates-new-search select[name='City']").val();
                    $searchFilter.Tariff = Tariff = $("#reviewrates-new-search select[name='Tariff']").val();
                    $searchFilter.AccessType = AccessType = $("#reviewrates-new-search select[name='AccessType']").val();
                    getNewRates($ProcessID, $searchFilter);
                } else if($(this).attr('id') == 'reviewrates-increased-decreased-search') {
                    $searchFilter.OriginationCode = OriginationCode = $("#reviewrates-increased-decreased-search input[name='OriginationCode']").val();
                    $searchFilter.Code = Code = $("#reviewrates-increased-decreased-search input[name='Code']").val();
                    $searchFilter.Timezone = Timezone = $("#reviewrates-increased-decreased-search select[name='Timezone']").val();
                    $searchFilter.City = City = $("#reviewrates-increased-decreased-search select[name='City']").val();
                    $searchFilter.Tariff = Tariff = $("#reviewrates-increased-decreased-search select[name='Tariff']").val();
                    $searchFilter.AccessType = AccessType = $("#reviewrates-increased-decreased-search select[name='AccessType']").val();
                    getIncreasedDecreasedRates($ProcessID, $searchFilter);
                } else if($(this).attr('id') == 'reviewrates-deleted-search') {
                    $searchFilter.OriginationCode = OriginationCode = $("#reviewrates-deleted-search input[name='OriginationCode']").val();
                    $searchFilter.Code = Code = $("#reviewrates-deleted-search input[name='Code']").val();
                    $searchFilter.Timezone = Timezone = $("#reviewrates-deleted-search select[name='Timezone']").val();
                    $searchFilter.City = City = $("#reviewrates-deleted-search select[name='City']").val();
                    $searchFilter.Tariff = Tariff = $("#reviewrates-deleted-search select[name='Tariff']").val();
                    $searchFilter.AccessType = AccessType = $("#reviewrates-deleted-search select[name='AccessType']").val();
                    getDeleteRates($ProcessID, $searchFilter);
                }
            });

            $('#btn-manage-columns').on('click', function() {
                //columnsMultiSelect.bootstrapDualListbox('destroy');
                $('#columnsMultiSelect').html('');
                columnsMultiSelect.bootstrapDualListbox('refresh', true);

                $.each( all_available_fields, function( key, value ) {
                    columnsMultiSelect.append("<option value="+value+">"+columns_text[value]+"</option>");
                });
                $.each( all_available_timezone_fields, function( key, value ) {
                    columnsMultiSelect.append("<option value="+value+">"+columns_timezone_text[value]+"</option>");
                });
                $.each( all_occupied_fields, function( key, value ) {
                    columnsMultiSelect.append("<option value="+value+" selected>"+columns_text[value]+"</option>");
                });
                $.each( all_occupied_timezone_fields, function( key, value ) {
                    columnsMultiSelect.append("<option value="+value+" selected>"+columns_timezone_text[value]+"</option>");
                });
                columnsMultiSelect.bootstrapDualListbox('refresh', true);
                $('#modal-manage-columns').modal('show');
            });
            $('#btn-change-selected-columns').on('click', function(){
                var add_fields      = $('#columnsMultiSelect').val();
                var remove_fields   = $(all_selectable_fields).not(add_fields).get();
                remove_fields       = remove_fields.concat($(all_selectable_timezone_fields).not(add_fields).get());
                add_fields = add_fields == '' || add_fields == null ? [] : add_fields;
                remove_fields = remove_fields == '' || remove_fields == null ? [] : remove_fields;
                add_fields.sort();
                // check and add if related field in add_fields
                // like if Action column added then add ActionDelete too
                $.each(add_fields, function(key, value) {
                    if(relational_columns[value] != undefined) {
                        $.each(relational_columns[value], function (key_rel, value_rel) {
                            if ($.inArray(value_rel, add_fields) == -1) {
                                add_fields.push(value_rel);
                            }
                        });
                    }
                });
                // add field to remove field array
                // like if MonthlyCost column removed then remove MonthlyCost column from all timezones
                $.each(remove_fields, function(key, value) {
                    if(relational_columns_timezone[value] != undefined) {
                        $.each(relational_columns_timezone[value], function (key_rel, value_rel) {
                            if ($.inArray(value_rel, remove_fields) == -1) {
                                remove_fields.push(value_rel);
                            }
                        });
                    }
                });
                // remove all fields from remove_fields which are in add_fields
                remove_fields = remove_fields.filter( function( el ) {
                    return add_fields.indexOf( el ) < 0;
                });
                // remove fields from html
                $.each(remove_fields, function(key, value) {
                    removeFieldFromMapping(value);
                });
                // remove dynamic empty divs
                fixDynamicColumnsView();
                // add fields to html
                $.each(add_fields, function(key, value) {
                    addFieldToMapping(value);
                });
                $('#modal-manage-columns').modal('hide');
            });
        });

        function createGrid(data){
            fileData = data;
            var tr = $('#table-4 thead tr');
            var body = $('#table-4 tbody');
            tr.empty();
            body.empty();

            $.each( data.columns, function( key, value ) {
                tr.append('<th>'+value+'</th>');
            });

            $('.CurrencyDD').each(function(i, el){
                var self = $(this);
                var label = 'Map From File';
                if(el.name != undefined) {
                    var currency_static_column = el.name.replace('selection[', '').replace(']', '');
                    if (searchKeyByValue(currency_static_column, currency_static_columns_timezone) != false) {
                        self.select2('destroy');
                        self.select2();
                    }
                    rebuildSelectComposite(self, fileData.columns, label);
                }
            });

            // generate select2 with both value from file and from database for selected columns
            $('.DualMapping').each(function(i, el){
                var self = $(this);
                var label = 'Map From File';
                if(el.name != undefined) {
                    var dual_mapping_column = el.name.replace('selection[', '').replace(']', '');
                    if (dual_mapping_columns_static.indexOf(dual_mapping_column) != -1) {
                        self.select2('destroy');
                        self.select2();
                    }
                    rebuildSelectComposite(self, fileData.columns, label);
                }
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
            $("#mapping #tab1 select").each(function(i, el){
                if(el.name !='selection[DateFormat]' && el.name != 'selection[DialCodeSeparator]' && el.name != 'selection[OriginationDialCodeSeparator]' && el.name != 'selection[FromCurrency]'){
                    var this_column = el.name.replace('selection[', '').replace(']', '');
                    // if not component currency field then
                    if (searchKeyByValue(this_column, currency_static_columns_timezone) === false && dual_mapping_columns_static.indexOf(this_column) == -1) {
                        var self = $('#add-template-form [name="' + el.name + '"]');
                        rebuildSelect2(self, data.columns, 'Skip loading');
                    }
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
                            if(key == 'CountryMapping' || key == 'OriginationCountryMapping') {
                                $('#add-template-form').find('input[name="selection['+key+']"]').attr("checked","checked");
                            } else if(typeof $("#add-template-form input[name='selection["+key+"]']").val() != 'undefined') {
                                $('#add-template-form').find('input[name="selection['+key+']"]').val(value);
                            } else if(typeof $("#add-template-form select[name='selection["+key+"]']").val() != 'undefined') {
                                $("#add-template-form [name='selection["+key+"]']").val(value).trigger("change");

                                // hide all timezones sections which are not mapped
                                if(key.search("MonthlyCost") >= 0) {
                                    var id = key.replace("MonthlyCost", "");
                                    if(id != '' && parseInt(id) > 0) {
                                        if(value != '') {
                                            $('#panel-mapping-' + id).removeClass('panel-collapse');
                                            $('#mapping-' + id).css('display', 'block');
                                        } else {
                                            $('#panel-mapping-'+id).addClass('panel-collapse');
                                            $('#mapping-'+id).css('display','none');
                                        }
                                    }
                                }
                            }
                            if(option_value.occupied_fields != undefined && option_value.occupied_fields != '') {
                                var occupied_fields = option_value.occupied_fields.split(',');
                                if ($.inArray(key, all_selectable_fields) != -1 && $.inArray(key, occupied_fields) != -1) {
                                    addFieldToMapping(key);
                                    // reinitialize dynamic select2
                                    // if not dual mapping columns then use reinitializeDynamicSelect
                                    if(dual_mapping_columns_dynamic.indexOf(key) == -1) {
                                        reinitializeDynamicSelect(key, value);
                                    }
                                    // if dual mapping columns then use reinitializeDynamicSelectDual
                                    if(dual_mapping_columns_dynamic.indexOf(key) != -1) {
                                        reinitializeDynamicSelectDual(key, value);
                                    }
                                }
                            }
                            if(option_value.occupied_timezone_fields != undefined && option_value.occupied_timezone_fields != '') {
                                var occupied_timezone_fields = option_value.occupied_timezone_fields.split(',');
                                if (searchKeyByValue(key,relational_columns_timezone) != false && $.inArray(key, occupied_timezone_fields) != -1) {
                                    addFieldToMapping(key);
                                    // reinitialize dynamic select2
                                    reinitializeDynamicSelect(key, value);
                                }
                                if(currency_columns_timezone[key] != undefined) {
                                    // reinitialize dynamic currency select2
                                    reinitializeDynamicSelectDual(currency_columns_timezone[key]);
                                }
                            }

                        });
                    }
                });
            }

            $('#add-template-form').find('[name="start_row"]').val(data.start_row);
            $('#add-template-form').find('[name="end_row"]').val(data.end_row);
            $('#add-template-form').find('[name="TemplateFile"]').val(data.filename);
            $('#add-template-form').find('[name="TempFileName"]').val(data.tempfilename);

            if(typeof data.TemplateType !== 'undefined') {
                $('#add-template-form').find('[name="TemplateType"]').val(data.TemplateType);
            }
        }
        function createGrid2(data){
            var tr = $('#table-5 thead tr');
            var body = $('#table-5 tbody');
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
            $("#mapping #tab2 select").each(function(i, el){
                if(el.name !='selection2[DateFormat]' && el.name != 'selection2[DialCodeSeparator]' && el.name != 'selection2[OriginationDialCodeSeparator]'){
                    var self = $('#add-template-form [name="'+el.name+'"]');
                    rebuildSelect2(self,data.columns,'Skip loading');
                }
            });
            if(data.FileUploadTemplate){
                //alert(JSON.stringify(data.FileUploadTemplate));
                $.each( data.FileUploadTemplate, function( optionskey, option_value ) {

                    if(optionskey == 'Options'){
                        if(option_value.selection2 != undefined) {
                            $.each(option_value.selection2, function (key, value) {
                                if(key == 'CountryMapping' || key == 'OriginationCountryMapping') {
                                    $('#add-template-form').find('input[name="selection['+key+']"]').attr("checked","checked");
                                } else if (typeof $("#add-template-form input[name='selection2[" + key + "]']").val() != 'undefined') {
                                    $('#add-template-form').find('input[name="selection2[' + key + ']"]').val(value)
                                } else if (typeof $("#add-template-form select[name='selection2[" + key + "]']").val() != 'undefined') {
                                    $("#add-template-form [name='selection2[" + key + "]']").val(value).trigger("change");
                                }
                            });
                        }
                    }
                });
            }

            $('#add-template-form').find('[name="start_row_sheet2"]').val(data.start_row);
            $('#add-template-form').find('[name="end_row_sheet2"]').val(data.end_row);
            $('#add-template-form').find('[name="TemplateFile"]').val(data.filename);
            $('#add-template-form').find('[name="TempFileName"]').val(data.tempfilename);

            if(typeof data.TemplateType !== 'undefined') {
                $('#add-template-form').find('[name="TemplateType"]').val(data.TemplateType);
            }
        }

        function getReviewRates($ProcessID, $searchFilter) {
            //$('#modal-reviewrates').modal('show');
            $('#modal-reviewrates').on('show.bs.modal', function () {
                $('#modal-reviewrates .modal-body').css('height',$( window ).height()*0.6);
            });
            $('#modal-reviewrates').modal({backdrop: 'static', keyboard: false});
            $(".btn.save").button('reset');

            //new rates
            getNewRates($ProcessID,$searchFilter);

            //increased-decreased rates
            getIncreasedDecreasedRates($ProcessID,$searchFilter);

            //delete rates
            getDeleteRates($ProcessID,$searchFilter);
        }

        function getNewRates($ProcessID,$searchFilter) {
            var checked_new             = '';
            var OriginationCode         = '';
            var Code                    = '';
            var City                    = '';
            var Tariff                  = '';
            var AccessType              = '';
            var Timezone                = 1;
            var RateTableID             = $('#ratetable').val();
            var RateUploadType          = $("input[name=RateUploadType]:checked").val();

            if($searchFilter.OriginationCode != 'undefined' && $searchFilter.OriginationCode != undefined) {
                OriginationCode = $searchFilter.OriginationCode;
            }
            if($searchFilter.Code != 'undefined' && $searchFilter.Code != undefined) {
                Code = $searchFilter.Code;
            }
            if($searchFilter.Timezone != 'undefined' && $searchFilter.Timezone != undefined) {
                Timezone = $searchFilter.Timezone;
            }
            if($searchFilter.City != 'undefined' && $searchFilter.City != undefined) {
                City = $searchFilter.City;
            }
            if($searchFilter.Tariff != 'undefined' && $searchFilter.Tariff != undefined) {
                Tariff = $searchFilter.Tariff;
            }
            if($searchFilter.AccessType != 'undefined' && $searchFilter.AccessType != undefined) {
                AccessType = $searchFilter.AccessType;
            }

            data_table_new = $("#table-reviewrates-new").dataTable({
                "bProcessing":true,
                "bDestroy": true,
                "bServerSide":true,
                "scrollX":true,
                "sAjaxSource": '{{URL::to('rate_upload/getReviewRates')}}',
                "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox-new.col-xs-1'>'l><'col-xs-6 col-right'<'change-view-new'><'export-data'T>f>r><'gridview'>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                "fnServerParams": function(aoData) {
                    aoData.push({"name":"ProcessID","value":$ProcessID},{"name":"Action","value":"New"},{"name":"Code","value":Code},{"name":"Timezone","value":Timezone},{"name":"OriginationCode","value":OriginationCode},{"name":"RateUploadType","value":RateUploadType},{"name":"RateTableID","value":RateTableID},{"name":"City","value":City},{"name":"Tariff","value":Tariff},{"name":"AccessType","value":AccessType});
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push({"name":"ProcessID","value":$ProcessID},{"name":"Action","value":"New"},{"name":"Code","value":Code},{"name":"Timezone","value":Timezone},{"name":"OriginationCode","value":OriginationCode},{"name":"RateUploadType","value":RateUploadType},{"name":"RateTableID","value":RateTableID},{"name":"City","value":City},{"name":"Tariff","value":Tariff},{"name":"AccessType","value":AccessType});
                },
                "sPaginationType": "bootstrap",
                "aaSorting"   : [[1, 'asc']],
                "oTableTools":
                {
                    "aButtons": [
                        {
                            "sExtends": "download",
                            "sButtonText": "EXCEL",
                            "sUrl": '{{URL::to('rate_upload/getReviewRates/exports/xlsx')}}',
                            sButtonClass: "save-collection btn-sm",
                            "fnClick": function ( nButton, oConfig, oFlash ) {
                                var Action = 'New';
                                var URL = '{{URL::to('rate_upload/getReviewRates/exports/xlsx')}}';
                                exportReviewRates($ProcessID,Action,URL);
                            }
                        },
                        {
                            "sExtends": "download",
                            "sButtonText": "CSV",
                            "sUrl": '{{URL::to('rate_upload/getReviewRates/exports/csv')}}',
                            sButtonClass: "save-collection btn-sm",
                            "fnClick": function ( nButton, oConfig, oFlash ) {
                                var Action = 'New';
                                var URL = '{{URL::to('rate_upload/getReviewRates/exports/csv')}}';
                                exportReviewRates($ProcessID,Action,URL);
                            }
                        }
                    ]
                },
                "aoColumns":
                        [
                            {
                                "bSortable": false,
                                mRender: function(id, type, full) {
                                    return '<div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + id + '" class="rowcheckbox" ></div>';
                                }
                            },//0 TempVendorRateID
                            { "bSortable": true},//1 AccessType
                            { "bSortable": true },//2 OriginationCode
                            { "bSortable": true },//3 Code
                            { "bSortable": true},//4 City
                            { "bSortable": true},//5 Tariff
                            { "bSortable": false},//6 Timezones
                            { "bSortable": true },//7 OneOffCost
                            { "bSortable": true },//8 MonthlyCost
                            { "bSortable": true },//9 CostPerCall
                            { "bSortable": true },//10 CostPerMinute
                            { "bSortable": true },//11 SurchargePerCall
                            { "bSortable": true },//12 SurchargePerMinute
                            { "bSortable": true },//13 OutpaymentPerCall
                            { "bSortable": true },//14 OutpaymentPerMinute
                            { "bSortable": true },//15 Surcharges
                            { "bSortable": true },//16 Chargeback
                            { "bSortable": true },//17 CollectionCostAmount
                            { "bSortable": true },//18 CollectionCostPercentage
                            { "bSortable": true },//19 RegistrationCostPerNumber
                            { "bSortable": true },//20 EffectiveDate
                            { "bSortable": true },//21 EndDate
                        ],
                "fnDrawCallback": function() {
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });
                    $('#table-reviewrates-new tbody').off('click');
                    $('#table-reviewrates-new tbody').on('click', 'tr', function() {
                        if (checked_new =='') {
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
                    $("#selectall-new").click(function(ev) {
                        var is_checked = $(this).is(':checked');
                        $('#table-reviewrates-new tbody tr').each(function(i, el) {
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
                    $(document).on("click",'#selectallbutton-new',function(ev) {
                        if($(this).is(':checked')){
                            checked_new = 'checked=checked disabled';
                            $("#selectall-new").prop("checked", true).prop('disabled', true);
                            $('#table-reviewrates-new tbody tr').each(function(i, el) {
                                $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                $(this).addClass('selected');
                            });
                        }else{
                            checked_new = '';
                            $("#selectall-new").prop("checked", false).prop('disabled', false);
                            $('#table-reviewrates-new tbody tr').each(function(i, el) {
                                $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                                $(this).removeClass('selected');
                            });
                        }
                    });
                    $("#selectcheckbox-new").html('<input type="checkbox" id="selectallbutton-new" name="checkboxselect[]" class="" title="Select All Found Records" />');
                }
            });
        }

        function getIncreasedDecreasedRates($ProcessID,$searchFilter) {
            var OriginationCode         = '';
            var Code                    = '';
            var City                    = '';
            var Tariff                  = '';
            var AccessType              = '';
            var Timezone                = 1;
            var RateTableID             = $('#ratetable').val();
            var RateUploadType  = $("input[name=RateUploadType]:checked").val();

            if($searchFilter.OriginationCode != 'undefined' && $searchFilter.OriginationCode != undefined) {
                OriginationCode = $searchFilter.OriginationCode;
            }
            if($searchFilter.Code != 'undefined' && $searchFilter.Code != undefined) {
                Code = $searchFilter.Code;
            }
            if($searchFilter.Timezone != 'undefined' && $searchFilter.Timezone != undefined) {
                Timezone = $searchFilter.Timezone;
            }
            if($searchFilter.City != 'undefined' && $searchFilter.City != undefined) {
                City = $searchFilter.City;
            }
            if($searchFilter.Tariff != 'undefined' && $searchFilter.Tariff != undefined) {
                Tariff = $searchFilter.Tariff;
            }
            if($searchFilter.AccessType != 'undefined' && $searchFilter.AccessType != undefined) {
                AccessType = $searchFilter.AccessType;
            }

            data_table_increased_decreased = $("#table-reviewrates-increased-decreased").dataTable({
                "bProcessing":true,
                "bDestroy": true,
                "bServerSide":true,
                "scrollX":true,
                "sAjaxSource": '{{URL::to('rate_upload/getReviewRates')}}',
                "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox-new.col-xs-1'>'l><'col-xs-6 col-right'<'change-view'><'export-data'T>f>r><'gridview'>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                "fnServerParams": function(aoData) {
                    aoData.push({"name":"ProcessID","value":$ProcessID},{"name":"Action","value":"IncreasedDecreased"},{"name":"Code","value":Code},{"name":"Timezone","value":Timezone},{"name":"OriginationCode","value":OriginationCode},{"name":"RateUploadType","value":RateUploadType},{"name":"RateTableID","value":RateTableID},{"name":"City","value":City},{"name":"Tariff","value":Tariff},{"name":"AccessType","value":AccessType});
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push({"name":"ProcessID","value":$ProcessID},{"name":"Action","value":"IncreasedDecreased"},{"name":"Code","value":Code},{"name":"Timezone","value":Timezone},{"name":"OriginationCode","value":OriginationCode},{"name":"RateUploadType","value":RateUploadType},{"name":"RateTableID","value":RateTableID},{"name":"City","value":City},{"name":"Tariff","value":Tariff},{"name":"AccessType","value":AccessType});
                },
                "sPaginationType": "bootstrap",
                "aaSorting"   : [[1, 'asc']],
                "oTableTools":
                {
                    "aButtons": [
                        {
                            "sExtends": "download",
                            "sButtonText": "EXCEL",
                            "sUrl": '{{URL::to('rate_upload/getReviewRates/exports/xlsx')}}',
                            sButtonClass: "save-collection btn-sm",
                            "fnClick": function ( nButton, oConfig, oFlash ) {
                                var Action = 'IncreasedDecreased';
                                var URL = '{{URL::to('rate_upload/getReviewRates/exports/xlsx')}}';
                                exportReviewRates($ProcessID,Action,URL);
                            }
                        },
                        {
                            "sExtends": "download",
                            "sButtonText": "CSV",
                            "sUrl": '{{URL::to('rate_upload/getReviewRates/exports/csv')}}',
                            sButtonClass: "save-collection btn-sm",
                            "fnClick": function ( nButton, oConfig, oFlash ) {
                                var Action = 'IncreasedDecreased';
                                var URL = '{{URL::to('rate_upload/getReviewRates/exports/csv')}}';
                                exportReviewRates($ProcessID,Action,URL);
                            }
                        }
                    ]
                },
                "aoColumns":
                        [
                            { "bVisible": false },//0 TempVendorRateID
                            { "bSortable": true},//1 AccessType
                            { "bSortable": true },//2 OriginationCode
                            { "bSortable": true },//3 Code
                            { "bSortable": true},//4 City
                            { "bSortable": true},//5 Tariff
                            { "bSortable": false},//6 Timezones
                            { "bSortable": true },//7 OneOffCost
                            { "bSortable": true },//8 MonthlyCost
                            { "bSortable": true },//9 CostPerCall
                            { "bSortable": true },//10 CostPerMinute
                            { "bSortable": true },//11 SurchargePerCall
                            { "bSortable": true },//12 SurchargePerMinute
                            { "bSortable": true },//13 OutpaymentPerCall
                            { "bSortable": true },//14 OutpaymentPerMinute
                            { "bSortable": true },//15 Surcharges
                            { "bSortable": true },//16 Chargeback
                            { "bSortable": true },//17 CollectionCostAmount
                            { "bSortable": true },//18 CollectionCostPercentage
                            { "bSortable": true },//19 RegistrationCostPerNumber
                            { "bSortable": true },//20 EffectiveDate
                            { "bSortable": true },//21 EndDate
                        ],
                "fnDrawCallback": function() {
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });
                }
            });
        }

        function getDeleteRates($ProcessID,$searchFilter) {
            var checked_deleted ='';
            var OriginationCode         = '';
            var Code                    = '';
            var City                    = '';
            var Tariff                  = '';
            var AccessType              = '';
            var Timezone                = 1;
            var RateTableID             = $('#ratetable').val();
            var RateUploadType  = $("input[name=RateUploadType]:checked").val();

            if($searchFilter.OriginationCode != 'undefined' && $searchFilter.OriginationCode != undefined) {
                OriginationCode = $searchFilter.OriginationCode;
            }
            if($searchFilter.Code != 'undefined' && $searchFilter.Code != undefined) {
                Code = $searchFilter.Code;
            }
            if($searchFilter.Timezone != 'undefined' && $searchFilter.Timezone != undefined) {
                Timezone = $searchFilter.Timezone;
            }
            if($searchFilter.City != 'undefined' && $searchFilter.City != undefined) {
                City = $searchFilter.City;
            }
            if($searchFilter.Tariff != 'undefined' && $searchFilter.Tariff != undefined) {
                Tariff = $searchFilter.Tariff;
            }
            if($searchFilter.AccessType != 'undefined' && $searchFilter.AccessType != undefined) {
                AccessType = $searchFilter.AccessType;
            }

            data_table_deleted = $("#table-reviewrates-deleted").dataTable({
                "bProcessing":true,
                "bDestroy": true,
                "bServerSide":true,
                "scrollX":true,
                "sAjaxSource": '{{URL::to('rate_upload/getReviewRates')}}',
                "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox-deleted.col-xs-1'>'l><'col-xs-6 col-right'<'change-view-deleted'><'export-data'T>f>r><'gridview'>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                "fnServerParams": function(aoData) {
                    aoData.push({"name":"ProcessID","value":$ProcessID},{"name":"Action","value":"Deleted"},{"name":"Code","value":Code},{"name":"Timezone","value":Timezone},{"name":"OriginationCode","value":OriginationCode},{"name":"RateUploadType","value":RateUploadType},{"name":"RateTableID","value":RateTableID},{"name":"City","value":City},{"name":"Tariff","value":Tariff},{"name":"AccessType","value":AccessType});
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push({"name":"ProcessID","value":$ProcessID},{"name":"Action","value":"Deleted"},{"name":"Code","value":Code},{"name":"Timezone","value":Timezone},{"name":"OriginationCode","value":OriginationCode},{"name":"RateUploadType","value":RateUploadType},{"name":"RateTableID","value":RateTableID},{"name":"City","value":City},{"name":"Tariff","value":Tariff},{"name":"AccessType","value":AccessType});
                },
                "sPaginationType": "bootstrap",
                "aaSorting"   : [[1, 'asc']],
                "oTableTools":
                {
                    "aButtons": [
                        {
                            "sExtends": "download",
                            "sButtonText": "EXCEL",
                            "sUrl": '{{URL::to('rate_upload/getReviewRates/exports/xlsx')}}',
                            sButtonClass: "save-collection btn-sm",
                            "fnClick": function ( nButton, oConfig, oFlash ) {
                                var Action = 'Deleted';
                                var URL = '{{URL::to('rate_upload/getReviewRates/exports/xlsx')}}';
                                exportReviewRates($ProcessID,Action,URL);
                            }
                        },
                        {
                            "sExtends": "download",
                            "sButtonText": "CSV",
                            "sUrl": '{{URL::to('rate_upload/getReviewRates/exports/csv')}}',
                            sButtonClass: "save-collection btn-sm",
                            "fnClick": function ( nButton, oConfig, oFlash ) {
                                var Action = 'Deleted';
                                var URL = '{{URL::to('rate_upload/getReviewRates/exports/csv')}}';
                                exportReviewRates($ProcessID,Action,URL);
                            }
                        }
                    ]
                },
                "aoColumns":
                        [
                            {
                                "bSortable": false,
                                mRender: function(id, type, full) {
                                    return '<div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + id + '" class="rowcheckbox" ></div>';
                                }
                            },//0 TempVendorRateID
                            { "bSortable": true},//1 AccessType
                            { "bSortable": true },//2 OriginationCode
                            { "bSortable": true },//3 Code
                            { "bSortable": true},//4 City
                            { "bSortable": true},//5 Tariff
                            { "bSortable": false},//6 Timezones
                            { "bSortable": true },//7 OneOffCost
                            { "bSortable": true },//8 MonthlyCost
                            { "bSortable": true },//9 CostPerCall
                            { "bSortable": true },//10 CostPerMinute
                            { "bSortable": true },//11 SurchargePerCall
                            { "bSortable": true },//12 SurchargePerMinute
                            { "bSortable": true },//13 OutpaymentPerCall
                            { "bSortable": true },//14 OutpaymentPerMinute
                            { "bSortable": true },//15 Surcharges
                            { "bSortable": true },//16 Chargeback
                            { "bSortable": true },//17 CollectionCostAmount
                            { "bSortable": true },//18 CollectionCostPercentage
                            { "bSortable": true },//19 RegistrationCostPerNumber
                            { "bSortable": true },//20 EffectiveDate
                            { "bSortable": true },//21 EndDate
                        ],
                "fnDrawCallback": function() {
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });
                    var toggle = '<button class="btn btn-sm btn-primary grid pull-right change-selected" id="change_enddate" style="margin-right: 30%;"><i class="entypo-pencil"></i> Change Selected</button>';
                    $('.change-view-deleted').html(toggle);

                    $('#table-reviewrates-deleted tbody').off('click');
                    $('#table-reviewrates-deleted tbody').on('click', 'tr', function() {
                        if (checked_deleted =='') {
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
                    $("#selectall-deleted").click(function(ev) {
                        var is_checked = $(this).is(':checked');
                        $('#table-reviewrates-deleted tbody tr').each(function(i, el) {
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
                    $(document).on("click",'#selectallbutton-deleted',function(ev) {
                        if($(this).is(':checked')){
                            checked_deleted = 'checked=checked disabled';
                            $("#selectall-deleted").prop("checked", true).prop('disabled', true);
                            $('#table-reviewrates-deleted tbody tr').each(function(i, el) {
                                $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                $(this).addClass('selected');
                            });
                        }else{
                            checked_deleted = '';
                            $("#selectall-deleted").prop("checked", false).prop('disabled', false);
                            $('#table-reviewrates-deleted tbody tr').each(function(i, el) {
                                $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                                $(this).removeClass('selected');
                            });
                        }
                    });
                    $("#selectcheckbox-deleted").html('<input type="checkbox" id="selectallbutton-deleted" name="checkboxselect[]" class="" title="Select All Found Records" />');
                }
            });
        }

        function exportReviewRates(ProcessID, Action, URL) {
            var ActionID                = Action.toLowerCase();
            var Code                    = $('#reviewrates-'+ActionID+'-search input[name="Code"]').val();
            var OriginationCode         = $('#reviewrates-'+ActionID+'-search input[name="OriginationCode"]').val();
            var Timezone                = $('#reviewrates-'+ActionID+'-search input[name="Timezone"]').val();
            var City                    = $('#reviewrates-'+ActionID+'-search input[name="City"]').val();
            var Tariff                  = $('#reviewrates-'+ActionID+'-search input[name="Tariff"]').val();
            var RateUploadType          = $("input[name=RateUploadType]:checked").val();

            var aoPost = [
                {"name": "ProcessID", "value": ProcessID},
                {"name": "Action", "value": Action},
                {"name": "Code", "value": Code},
                {"name": "OriginationCode", "value": OriginationCode},
                {"name": "Timezone", "value": Timezone},
                {"name": "City", "value": City},
                {"name": "Tariff", "value": Tariff},
                {"name": "RateUploadType", "value": RateUploadType}
            ];

            /* Create an IFrame to do the request */
            nIFrame = document.createElement('iframe');
            nIFrame.setAttribute('id', 'RemotingIFrame');
            nIFrame.style.border = '0px';
            nIFrame.style.width = '0px';
            nIFrame.style.height = '0px';

            document.body.appendChild(nIFrame);
            var nContentWindow = nIFrame.contentWindow;
            nContentWindow.document.open();
            nContentWindow.document.close();

            var nForm = nContentWindow.document.createElement('form');
            nForm.setAttribute('method', 'post');

            /* Add POST data */
            for (var i = 0; i < aoPost.length; i++)
            {
                nInput = nContentWindow.document.createElement('input');
                nInput.setAttribute('name', aoPost[i].name);
                nInput.setAttribute('type', 'text');
                nInput.value = aoPost[i].value;
                nForm.appendChild(nInput);
            }

            nForm.setAttribute('action', URL);

            /* Add the form and the iframe */
            nContentWindow.document.body.appendChild(nForm);

            /* Send the request */
            nForm.submit();
        }

        function getUploadTemplates($RateUploadType) {
            var RateType = $('#form-upload input[name=RateType]').val();
            $.ajax({
                url: '{{URL::to('rate_upload/getUploadTemplates')}}/'+$RateUploadType,
                data: 'RateUploadType='+$RateUploadType+'&RateType='+RateType,
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    if (response.status == 'success') {
                        var html = '';
                        var Templates = response.FileUploadTemplates;
                        //alert (JSON.stringify(Templates));
                        for(key in Templates) {
                            if(Templates[key]["Title"] == 'Select') {
                                html += '<option value="'+Templates[key]["FileUploadTemplateID"]+'" start_row="'+Templates[key]["start_row"]+'" end_row="'+Templates[key]["end_row"]+'" start_row_sheet2="'+Templates[key]["start_row_sheet2"]+'" end_row_sheet2="'+Templates[key]["end_row_sheet2"]+'" importratesheet="'+Templates[key]["importratesheet"]+'" importdialcodessheet="'+Templates[key]["importdialcodessheet"]+'" selected>'+Templates[key]["Title"]+'</option>';
                            } else {
                                html += '<option value="'+Templates[key]["FileUploadTemplateID"]+'" start_row="'+Templates[key]["start_row"]+'" end_row="'+Templates[key]["end_row"]+'" start_row_sheet2="'+Templates[key]["start_row_sheet2"]+'" end_row_sheet2="'+Templates[key]["end_row_sheet2"]+'" importratesheet="'+Templates[key]["importratesheet"]+'" importdialcodessheet="'+Templates[key]["importdialcodessheet"]+'" >'+Templates[key]["Title"]+'</option>';
                            }
                        }
                        $('#uploadtemplate').html(html).trigger('change');
                    } else {
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                },
                error: function () {
                    toastr.error("error", "Error", toastr_opts);
                }
            });
        }

        function getTrunk($RateUploadType,id) {
            return $.ajax({
                url: '{{URL::to('rate_upload/getTrunk')}}/'+$RateUploadType,
                data: 'Type='+$RateUploadType+'&id='+id,
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    if (response.status == 'success') {
                        var html = '';
                        var Trunks = response.trunks;
                        var Trunk  = response.trunk;

                        if(!jQuery.isEmptyObject(Trunks)) {
                            $('#isTrunks').val('1');
                        } else {
                            $('#isTrunks').val('0');
                        }

                        for(key in Trunks) {
                            if(Trunks[key] == 'Select') {
                                html += '<option value="'+key+'" selected>'+Trunks[key]+'</option>';
                            } else {
                                html += '<option value="'+key+'">'+Trunks[key]+'</option>';
                            }
                        }
                        $('#Trunk').html(html).trigger('change');
                    } else {
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                },
                error: function () {
                    toastr.error("error", "Error", toastr_opts);
                }
            });
        }

        function searchKeyByValue(search,arr) {
            for(key in arr) {
                if($.inArray(search,arr[key]) != -1) {
                    return key;
                }
            }
            return false;
        }

        function addFieldToMapping($FieldName) {
            if($.inArray($FieldName,all_selectable_fields) != -1) {
                if($.inArray($FieldName,all_available_fields) != -1) {
                    var html = $('#controls-selection').find('.control-'+$FieldName).html();
                    if(html != '' || html != undefined) {
                        $("#add-template-form .managable select[name='selection["+$FieldName+"]']").select2('destroy');
                        var html_div = '<div class="form-group managable notimezone solo">';
                        html_div += html;
                        html_div += '</div>';
                        if($('#tab1').find('.solo').html() != undefined) {
                            $('#tab1').find('.solo').last().append(html);
                            $('#tab1').find('.solo').removeClass('solo').addClass('duo');
                        } else {
                            $('#tab1').find('.duo').last().after(html_div);
                        }
                        $('#controls-selection').find('.control-'+$FieldName).html('');
                        all_available_fields.splice(all_available_fields.indexOf($FieldName), 1);
                        all_occupied_fields.push($FieldName);
                        $('#occupied_fields').val(all_occupied_fields);

                        // if not dual mapping columns then use reinitializeDynamicSelect
                        if(dual_mapping_columns_dynamic.indexOf($FieldName) == -1) {
                            reinitializeDynamicSelect($FieldName);
                        }
                        // if dual mapping columns then use reinitializeDynamicSelectDual
                        if(dual_mapping_columns_dynamic.indexOf($FieldName) != -1) {
                            reinitializeDynamicSelectDual($FieldName);
                        }
                    }
                }
            }

            if($.inArray($FieldName,all_selectable_timezone_fields) != -1) {
                if($.inArray($FieldName,all_available_timezone_fields) != -1) {
                    if(relational_columns_timezone[$FieldName] != undefined) {
                        $.each(relational_columns_timezone[$FieldName], function (key, $FieldName2) {
                            var TimezonesID = $FieldName2.replace( /[^\d.]/g, '' );
                            var PanelID = '#panel-mapping-'+TimezonesID;
                            var html = $('#controls-selection').find('.control-'+$FieldName2).html();
                            if(html != '' || html != undefined) {
                                $("#add-template-form .managable select[name='selection["+$FieldName2+"]']").select2('destroy');
                                $("#add-template-form .managable select[name='selection["+currency_columns_timezone[$FieldName2]+"]']").select2('destroy');
                                var html_div = '<div class="form-group managable timezone solo_timezone">';
                                html_div += html;
                                html_div += '</div>';

                                if($('#tab1').find(PanelID+' .solo_timezone').html() != undefined) {
                                    $('#tab1').find(PanelID+' .solo_timezone').last().append(html);
                                    $('#tab1').find(PanelID+' .solo_timezone').removeClass('solo_timezone').addClass('duo_timezone');
                                } else {
                                    $('#tab1').find(PanelID+' .duo_timezone').last().after(html_div);
                                }
                                $('#controls-selection').find('.control-'+$FieldName2).html('');

                                var index = all_available_timezone_fields.indexOf($FieldName);
                                if (index !== -1) all_available_timezone_fields.splice(index, 1);

                                if($.inArray($FieldName,all_occupied_timezone_fields) == -1) {
                                    all_occupied_timezone_fields.push($FieldName);
                                }
                                all_occupied_timezone_fields2.push($FieldName2);
                                $('#occupied_timezone_fields').val(all_occupied_timezone_fields2);
                                reinitializeDynamicSelect($FieldName2);

                                if(currency_columns_timezone[$FieldName2] != undefined) {
                                    reinitializeDynamicSelectDual(currency_columns_timezone[$FieldName2]);
                                }
                            }
                        });
                    }
                }
            }

            reinitializePopovers();

            return false;
        }

        function removeFieldFromMapping($FieldName) {
            if($.inArray($FieldName,all_selectable_fields) != -1) {
                if($.inArray($FieldName,all_occupied_fields) != -1) {
                    var html = $('#tab1').find('.control-'+$FieldName+'-controls').html();
                    if(html != '' || html != undefined) {
                        $("#add-template-form .managable select[name='selection["+$FieldName+"]']").select2('destroy');
                        $('#tab1').find('.control-'+$FieldName+'-controls').appendTo($('.control-'+$FieldName));

                        if($.inArray($FieldName,all_selectable_fields) != -1) {
                            all_occupied_fields.splice(all_occupied_fields.indexOf($FieldName), 1);
                            all_available_fields.push($FieldName);
                            $('#occupied_fields').val(all_occupied_fields);
                        }

                        // if not dual mapping columns then use reinitializeDynamicSelect
                        if(dual_mapping_columns_dynamic.indexOf($FieldName) == -1) {
                            reinitializeDynamicSelect($FieldName);
                        }
                        // if dual mapping columns then use reinitializeDynamicSelectDual
                        if(dual_mapping_columns_dynamic.indexOf($FieldName) != -1) {
                            reinitializeDynamicSelectDual($FieldName);
                        }
                    }
                }
            }

            if($.inArray($FieldName,all_selectable_timezone_fields) != -1) {
                if ($.inArray($FieldName, all_occupied_timezone_fields) != -1) {
                    if (relational_columns_timezone[$FieldName] != undefined) {
                        $.each(relational_columns_timezone[$FieldName], function (key, $FieldName2) {
                            var html = $('#tab1').find('.control-' + $FieldName2 + '-controls').html();
                            if (html != '' || html != undefined) {
                                $("#add-template-form .managable select[name='selection[" + $FieldName2 + "]']").select2('destroy');
                                $("#add-template-form .managable select[name='selection[" + currency_columns_timezone[$FieldName2] + "]']").select2('destroy');
                                $('#tab1').find('.control-' + $FieldName2 + '-controls').appendTo($('.control-' + $FieldName2));

                                if ($.inArray($FieldName, all_selectable_timezone_fields) != -1) {
                                    var index = all_occupied_timezone_fields.indexOf($FieldName);
                                    if (index !== -1) all_occupied_timezone_fields.splice(index, 1);
                                    all_occupied_timezone_fields2.splice(all_occupied_timezone_fields2.indexOf($FieldName2), 1);
                                    if ($.inArray($FieldName, all_available_timezone_fields) == -1) {
                                        all_available_timezone_fields.push($FieldName);
                                    }
                                    $('#occupied_timezone_fields').val(all_occupied_timezone_fields2);
                                }
                                reinitializeDynamicSelect($FieldName);

                                if(currency_columns_timezone[$FieldName2] != undefined) {
                                    reinitializeDynamicSelectDual(currency_columns_timezone[$FieldName2]);
                                }
                            }
                        });
                    }
                }
            }
            return false;
        }

        function fixDynamicColumnsView() {
            $('.managable.notimezone').each(function() {
                if($(this).find('label').length == 1 ) {
                    $(this).removeClass('duo').addClass('solo');
                } else if($(this).find('label').length == 0 ) {
                    $(this).remove();
                }
            });
            $('.managable.timezone').each(function() {
                if($(this).find('label').length == 1 ) {
                    $(this).removeClass('duo_timezone').addClass('solo_timezone');
                } else if($(this).find('label').length == 0 ) {
                    $(this).remove();
                }
            });
        }

        function reinitializeDynamicSelect(name,value) {
            $("#add-template-form .managable select[name='selection["+name+"]']").select2('destroy');
            $("#add-template-form .managable select[name='selection["+name+"]']").select2();
            if(name !='DateFormat' && name != 'DialCodeSeparator' && name != 'OriginationDialCodeSeparator' && name != 'FromCurrency'){
                var self = $("#add-template-form .managable select[name='selection["+name+"]']");
                rebuildSelect2(self,fileData.columns,'Skip loading');
            }
            if(value) {
                $("#add-template-form .managable select[name='selection["+name+"]']").val(value).trigger("change");
            }
            if(name=='OriginationCode') {
                $("#add-template-form .managable select[name='selection[OriginationDialCodeSeparator]']").select2('destroy');
                $("#add-template-form .managable select[name='selection[OriginationDialCodeSeparator]']").select2();
            }
        }

        function reinitializeDynamicSelectDual(name,value) {
            $("#add-template-form .managable select[name='selection["+name+"]']").select2('destroy');
            $("#add-template-form .managable select[name='selection["+name+"]']").select2();

            var self = $("#add-template-form .managable select[name='selection["+name+"]']");
            var label = 'Map From File';
            rebuildSelectComposite(self,fileData.columns,label);

            if(value) {
                $("#add-template-form .managable select[name='selection["+name+"]']").val(value).trigger("change");
            }
            if(name=='OriginationCode') {
                $("#add-template-form .managable select[name='selection[OriginationDialCodeSeparator]']").select2('destroy');
                $("#add-template-form .managable select[name='selection[OriginationDialCodeSeparator]']").select2();
            }
        }

        function getRateTableDetails() {
            var id = $('#form-upload select[name=Ratetable]').val();
            var formData = new FormData($('#form-upload')[0]);
            var link = '{{URL::to('rate_upload/[id]/getRateTableDetails')}}';
            link = link.replace('[id]',id);
            show_loading_bar(0);
            $.ajax({
                url:  link,  //Server script to process data
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
                        var RateTable = response.RateTable;

                        if(RateTable['Type'] == '{{$TypeVoiceCall}}' && RateTable['AppliedTo'] == '{{RateTable::APPLIED_TO_VENDOR}}') {
                            $('.vendor_selection_box').show();
                        } else {
                            $('.vendor_selection_box').hide();
                        }
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

        function reinitializePopovers() {
            $('[data-toggle="popover"]').each(function(i, el) {
                var $this = $(el),
                        placement = attrDefault($this, 'placement', 'right'),
                        trigger = attrDefault($this, 'trigger', 'click'),
                        popover_class = $this.hasClass('popover-secondary') ? 'popover-secondary' : ($this.hasClass('popover-primary') ? 'popover-primary' : ($this.hasClass('popover-default') ? 'popover-default' : ''));
                $this.popover({
                    placement: placement,
                    trigger: trigger
                });
                $this.on('shown.bs.popover', function(ev) {
                    var $popover = $this.next();
                    $popover.addClass(popover_class);
                });
            });
        }
    </script>
@stop
@section('footer_ext')
    @parent

    <div class="modal fade" id="modal-fileformat">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Vendor Rate File Format</h4>
                </div>

                <div class="modal-body">
                    <p>All columns are mandatory and the first line should have the column headings.</p>
                    <table class="table responsive">
                        <thead>
                        <tr>
                            <th>OrigInation</th>
                            <th>Prefix</th>
                            <th>One-Off Cost</th>
                            <th>Monthly Cost</th>
                            <th>Cost Per Call</th>
                            <th>Cost Per Minute</th>
                            <th>Surcharge Per Call</th>
                            <th>Surcharge Per Minute</th>
                            <th>Outpayment Per Call</th>
                            <th>Outpayment Per Minute</th>
                            <th>Surcharges</th>
                            <th>Chargeback</th>
                            <th>Collection Cost Amount</th>
                            <th>Collection Cost Percentage</th>
                            <th>Registration Cost Per Number</th>
                            <th>Effective Date</th>
                            <th>End Date</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>9379</td>
                            <td>9379</td>
                            <td>0.001</td>
                            <td>0.001</td>
                            <td>0.001</td>
                            <td>0.001</td>
                            <td>0.001</td>
                            <td>0.001</td>
                            <td>0.001</td>
                            <td>0.001</td>
                            <td>0.001</td>
                            <td>0.001</td>
                            <td>0.001</td>
                            <td>0.001</td>
                            <td>0.001</td>
                            <td> 11-12-2014  12:00:00 AM</td>
                            <td> 15-12-2014  12:00:00 AM</td>
                            <td>I <span data-original-title="Insert" data-content="When action is set to 'I', It will insert new Vendor Rate" data-placement="top" data-trigger="hover" data-toggle="popover" class="label label-info popover-primary">?</span></td>
                        </tr>
                        <tr>
                            <td>9377</td>
                            <td>9377</td>
                            <td>0.002</td>
                            <td>0.002</td>
                            <td>0.002</td>
                            <td>0.002</td>
                            <td>0.002</td>
                            <td>0.002</td>
                            <td>0.002</td>
                            <td>0.002</td>
                            <td>0.002</td>
                            <td>0.002</td>
                            <td>0.002</td>
                            <td>0.002</td>
                            <td>0.002</td>
                            <td> 11-12-2014  12:00:00 AM</td>
                            <td> 15-12-2014  12:00:00 AM</td>
                            <td>U <span data-original-title="Update" data-content="When action is set to 'U',It will replace existing Vendor Rate" data-placement="top" data-trigger="hover" data-toggle="popover" class="label label-info popover-primary">?</span></td>
                        </tr>
                        <tr>
                            <td>9378</td>
                            <td>9378</td>
                            <td>0.003</td>
                            <td>0.003</td>
                            <td>0.003</td>
                            <td>0.003</td>
                            <td>0.003</td>
                            <td>0.003</td>
                            <td>0.003</td>
                            <td>0.003</td>
                            <td>0.003</td>
                            <td>0.003</td>
                            <td>0.003</td>
                            <td>0.003</td>
                            <td>0.003</td>
                            <td> 11-12-2014  12:00:00 AM</td>
                            <td> 15-12-2014  12:00:00 AM</td>
                            <td>D <span data-original-title="Delete" data-content="When action is set to 'D',It will delete existing Vendor Rate" data-placement="top" data-trigger="hover" data-toggle="popover" class="label label-info popover-primary">?</span></td>
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

    <div class="modal fade" id="modal-reviewrates">
        <div class="modal-dialog modal-lg" style="width: 80%;">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Review Rates</h4>
                </div>

                <div class="modal-body">
                    <ul class="nav nav-tabs bordered">
                        <li class="active">
                            <a href="#reviewrates-new" data-toggle="tab" >
                                <span class="hidden-xs">New</span>
                            </a>
                        </li>
                        <li>
                            <a href="#reviewrates-increased-decreased" data-toggle="tab" >
                                <span class="hidden-xs">Increased-Decreased</span>
                            </a>
                        </li>
                        <li>
                            <a href="#reviewrates-deleted" data-toggle="tab" >
                                <span class="hidden-xs">Delete</span>
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade in active" id="reviewrates-new">
                            <div class="row">
                                <div class="col-md-12">
                                    <form role="form" id="reviewrates-new-search" method="get" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
                                        <div class="panel panel-primary panel-collapse" data-collapsed="0">
                                            <div class="panel-heading">
                                                <div class="panel-title">
                                                    Search
                                                </div>

                                                <div class="panel-options">
                                                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                                                </div>
                                            </div>

                                            <div class="panel-body" style="display: none;">
                                                <div class="form-group">
                                                    <label class="col-sm-1 control-label">Access Type</label>
                                                    <div class="col-sm-3">
                                                        {{Form::select('AccessType', $AccessTypeFilter,'',array("class"=>"select2 small"))}}
                                                    </div>
                                                    <label class="col-sm-1 control-label">Origination</label>
                                                    <div class="col-sm-3">
                                                        <input type="text" name="OriginationCode" class="form-control" placeholder="" value="" />
                                                    </div>
                                                    <label class="col-sm-1 control-label">Prefix</label>
                                                    <div class="col-sm-3">
                                                        <input type="text" name="Code" class="form-control" placeholder="" value="" />
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-sm-1 control-label">City</label>
                                                    <div class="col-sm-3">
                                                        {{Form::select('City', $CityFilter,'',array("class"=>"select2 small"))}}
                                                    </div>
                                                    <label class="col-sm-1 control-label">Tariff</label>
                                                    <div class="col-sm-3">
                                                        {{Form::select('Tariff', $TariffFilter,'',array("class"=>"select2 small"))}}
                                                    </div>
                                                    <label class="col-sm-1 control-label">Timezone</label>
                                                    <div class="col-sm-3">
                                                        {{Form::select('Timezone', $AllTimezones,'',array("class"=>"select2 small"))}}
                                                    </div>
                                                </div>
                                                <p style="text-align: right; margin: 0;">
                                                    <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left">
                                                        <i class="entypo-search"></i>
                                                        Search
                                                    </button>
                                                </p>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <table class="table table-bordered datatable" id="table-reviewrates-new">
                                        <thead>
                                        <tr>
                                            <th width="5%" ><input type="checkbox" id="selectall-new" name="checkbox[]" class="" /></th>
                                            <th width="15%" >Access Type</th>
                                            <th width="15%" >Origination</th>
                                            <th width="15%" >Prefix</th>
                                            <th width="15%" >City</th>
                                            <th width="15%" >Tariff</th>
                                            <th width="15%" >Timezones</th>
                                            <th width="15%" >One-Off Cost</th>
                                            <th width="15%" >Monthly Cost</th>
                                            <th width="15%" >Cost Per Call</th>
                                            <th width="15%" >Cost Per Minute</th>
                                            <th width="15%" >Surcharge Per Call</th>
                                            <th width="15%" >Surcharge Per Minute</th>
                                            <th width="15%" >Outpayment Per Call</th>
                                            <th width="15%" >Outpayment Per Minute</th>
                                            <th width="15%" >Surcharges</th>
                                            <th width="15%" >Chargeback</th>
                                            <th width="15%" >Collection Cost Amount</th>
                                            <th width="15%" >Collection Cost Percentage</th>
                                            <th width="15%" >Registration Cost Per Number</th>
                                            <th width="15%" >Effective Date</th>
                                            <th width="15%" >End Date</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade in" id="reviewrates-increased-decreased">
                            <div class="row">
                                <div class="col-md-12">
                                    <form role="form" id="reviewrates-increased-decreased-search" method="get" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
                                        <div class="panel panel-primary panel-collapse" data-collapsed="0">
                                            <div class="panel-heading">
                                                <div class="panel-title">
                                                    Search
                                                </div>

                                                <div class="panel-options">
                                                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                                                </div>
                                            </div>

                                            <div class="panel-body" style="display: none;">
                                                <div class="form-group">
                                                    <label class="col-sm-1 control-label">Access Type</label>
                                                    <div class="col-sm-3">
                                                        {{Form::select('AccessType', $AccessTypeFilter,'',array("class"=>"select2 small"))}}
                                                    </div>
                                                    <label class="col-sm-1 control-label">Origination</label>
                                                    <div class="col-sm-3">
                                                        <input type="text" name="OriginationCode" class="form-control" placeholder="" value="" />
                                                    </div>
                                                    <label class="col-sm-1 control-label">Prefix</label>
                                                    <div class="col-sm-3">
                                                        <input type="text" name="Code" class="form-control" placeholder="" value="" />
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-sm-1 control-label">City</label>
                                                    <div class="col-sm-3">
                                                        {{Form::select('City', $CityFilter,'',array("class"=>"select2 small"))}}
                                                    </div>
                                                    <label class="col-sm-1 control-label">Tariff</label>
                                                    <div class="col-sm-3">
                                                        {{Form::select('Tariff', $TariffFilter,'',array("class"=>"select2 small"))}}
                                                    </div>
                                                    <label class="col-sm-1 control-label">Timezone</label>
                                                    <div class="col-sm-3">
                                                        {{Form::select('Timezone', $AllTimezones,'',array("class"=>"select2 small"))}}
                                                    </div>
                                                </div>
                                                <p style="text-align: right; margin: 0;">
                                                    <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left">
                                                        <i class="entypo-search"></i>
                                                        Search
                                                    </button>
                                                </p>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <table class="table table-bordered datatable" id="table-reviewrates-increased-decreased">
                                        <thead>
                                        <tr>
                                            <th width="5%" ></th>
                                            <th width="15%" >Access Type</th>
                                            <th width="15%" >Origination</th>
                                            <th width="15%" >Prefix</th>
                                            <th width="15%" >City</th>
                                            <th width="15%" >Tariff</th>
                                            <th width="15%" >Timezones</th>
                                            <th width="15%" >One-Off Cost</th>
                                            <th width="15%" >Monthly Cost</th>
                                            <th width="15%" >Cost Per Call</th>
                                            <th width="15%" >Cost Per Minute</th>
                                            <th width="15%" >Surcharge Per Call</th>
                                            <th width="15%" >Surcharge Per Minute</th>
                                            <th width="15%" >Outpayment Per Call</th>
                                            <th width="15%" >Outpayment Per Minute</th>
                                            <th width="15%" >Surcharges</th>
                                            <th width="15%" >Chargeback</th>
                                            <th width="15%" >Collection Cost Amount</th>
                                            <th width="15%" >Collection Cost Percentage</th>
                                            <th width="15%" >Registration Cost Per Number</th>
                                            <th width="15%" >Effective Date</th>
                                            <th width="15%" >End Date</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade in" id="reviewrates-deleted">
                            <div class="row">
                                <div class="col-md-12">
                                    <form role="form" id="reviewrates-deleted-search" method="get" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
                                        <div class="panel panel-primary panel-collapse" data-collapsed="0">
                                            <div class="panel-heading">
                                                <div class="panel-title">
                                                    Search
                                                </div>

                                                <div class="panel-options">
                                                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                                                </div>
                                            </div>

                                            <div class="panel-body" style="display: none;">
                                                <div class="form-group">
                                                    <label class="col-sm-1 control-label">Access Type</label>
                                                    <div class="col-sm-3">
                                                        {{Form::select('AccessType', $AccessTypeFilter,'',array("class"=>"select2 small"))}}
                                                    </div>
                                                    <label class="col-sm-1 control-label">Origination</label>
                                                    <div class="col-sm-3">
                                                        <input type="text" name="OriginationCode" class="form-control" placeholder="" value="" />
                                                    </div>
                                                    <label class="col-sm-1 control-label">Prefix</label>
                                                    <div class="col-sm-3">
                                                        <input type="text" name="Code" class="form-control" placeholder="" value="" />
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-sm-1 control-label">City</label>
                                                    <div class="col-sm-3">
                                                        {{Form::select('City', $CityFilter,'',array("class"=>"select2 small"))}}
                                                    </div>
                                                    <label class="col-sm-1 control-label">Tariff</label>
                                                    <div class="col-sm-3">
                                                        {{Form::select('Tariff', $TariffFilter,'',array("class"=>"select2 small"))}}
                                                    </div>
                                                    <label class="col-sm-1 control-label">Timezone</label>
                                                    <div class="col-sm-3">
                                                        {{Form::select('Timezone', $AllTimezones,'',array("class"=>"select2 small"))}}
                                                    </div>
                                                </div>
                                                <p style="text-align: right; margin: 0;">
                                                    <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left">
                                                        <i class="entypo-search"></i>
                                                        Search
                                                    </button>
                                                </p>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <table class="table table-bordered datatable" id="table-reviewrates-deleted">
                                        <thead>
                                        <tr>
                                            <th width="5%" ><input type="checkbox" id="selectall-deleted" name="checkbox[]" class="" /></th>
                                            <th width="15%" >Access Type</th>
                                            <th width="15%" >Origination</th>
                                            <th width="15%" >Prefix</th>
                                            <th width="15%" >City</th>
                                            <th width="15%" >Tariff</th>
                                            <th width="15%" >Timezones</th>
                                            <th width="15%" >OneOffCost</th>
                                            <th width="15%" >MonthlyCost</th>
                                            <th width="15%" >CostPerCall</th>
                                            <th width="15%" >CostPerMinute</th>
                                            <th width="15%" >SurchargePerCall</th>
                                            <th width="15%" >SurchargePerMinute</th>
                                            <th width="15%" >OutpaymentPerCall</th>
                                            <th width="15%" >OutpaymentPerMinute</th>
                                            <th width="15%" >Surcharges</th>
                                            <th width="15%" >Chargeback</th>
                                            <th width="15%" >CollectionCostAmount</th>
                                            <th width="15%" >CollectionCostPercentage</th>
                                            <th width="15%" >RegistrationCostPerNumber</th>
                                            <th width="15%" >Effective Date</th>
                                            <th width="15%" >End Date</th>
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

                <div class="modal-footer">
                    <button id="save_template2" class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-floppy"></i>
                        Proceed
                    </button>
                    <button type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modal-change-selected-enddate">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <form id="frm-change-selected-enddate" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Change Selected</h4>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">End Date</label>
                                    <input type="text" name="EndDate" id="EndDate" class="form-control datepicker" data-date-format="yyyy-mm-dd" >
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" id="btn-change-selected-enddate"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                            <i class="entypo-floppy"></i>
                            Save
                        </button>
                        <button type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modal-manage-columns">
        <div class="modal-dialog modal-sm" style="width: 50%;">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Manage Columns</h4>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                {{Form::select('columnsMultiSelect[]',[],[],array("id"=>"columnsMultiSelect","class"=>"","multiple"=>"multiple"))}}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" id="btn-change-selected-columns"  class="btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-floppy"></i>
                        Save
                    </button>
                    <button type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal"> <i class="entypo-cancel"></i> Close </button>
                </div>
            </div>
        </div>
    </div>
@stop