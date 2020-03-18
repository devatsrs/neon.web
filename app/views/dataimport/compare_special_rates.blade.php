@extends('layout.main')

@section('content')

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li class="active">

            <strong>Compare Special Rates</strong>
        </li>
    </ol>
    <h3>Compare Special Rates</h3><br/>
    <div class="row">
        <div class="col-md-12">
            <form role="form" id="form-filter" name="form-filter" method="post" class="form-horizontal form-groups-bordered" enctype="multipart/form-data">
                <div class="panel panel-primary" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title">
                            Select Customer & Number
                        </div>

                        <div class="panel-options">
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Customer</label>
                            <div class="col-sm-2">
                                {{ Form::select('Customer', $Customers, '', array("class"=>"select2 CustomerDD","NumberDDID"=>"NumberDD")) }}
                            </div>
                            <label class="col-sm-2 control-label">Number</label>
                            <div class="col-sm-2">
                                {{ Form::select('Number', [], '', array("class"=>"select2","id"=>"NumberDD")) }}
                            </div>
                            {{--<label class="col-sm-2 control-label"></label>--}}
                            <div class="col-sm-2 text-center">
                                <button type="button" id="btn-export" class="btn btn-primary btn-sm btn-icon icon-left" disabled="disabled" data-loading-text="Loading...">
                                    <i class="fa fa-download"></i>
                                    Export
                                </button>
                            </div>
                            <div class="col-sm-2 text-center">
                                <button type="button" id="btn-import" class="btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                                    <i class="fa fa-upload"></i>
                                    Import
                                </button>
                            </div>
                        </div>

                        {{--<p style="text-align: right;">
                            <button  type="submit" class="btn upload btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                                <i class="glyphicon glyphicon-circle-arrow-up"></i>
                                Upload
                            </button>
                        </p>--}}
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="panel-title">
                        <b> Access </b>
                    </div>
                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body with-table">
                    <table id="tbl-Access" class="table table-bordered datatable">
                        <thead>
                            <tr>
                                <th>Cost Component</th>
                                <th>Special Rate</th>
                                <th>Default Rate</th>
                                <th>New Pricing</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="panel-title">
                        <b> Package </b>
                    </div>
                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body with-table">
                    <table id="tbl-Package" class="table table-bordered datatable">
                        <thead>
                            <tr>
                                <th>Cost Component</th>
                                <th>Special Rate</th>
                                <th>Default Rate</th>
                                <th>New Pricing</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="panel-title">
                        <b> Termination </b>
                    </div>
                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body with-table">
                    <table id="tbl-Termination" class="table table-bordered datatable">
                        <thead>
                            <tr>
                                <th>Cost Component</th>
                                <th>Special Rate</th>
                                <th>Default Rate</th>
                                <th>New Pricing</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="import_file_modal" >
        <div class="modal-dialog">
            <div class="modal-content">
                <form role="form" id="form-upload" method="post" action="javascript:void(0);" class="form-horizontal form-groups-bordered" enctype="multipart/form-data">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Upload Import File</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Customer</label>
                            <div class="col-sm-4">
                                {{ Form::select('Customer', $Customers, '', array("class"=>"select2 CustomerDD","NumberDDID"=>"NumberDDImport")) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Number</label>
                            <div class="col-sm-4">
                                {{ Form::select('Number', [], '', array("class"=>"select2","id"=>"NumberDDImport")) }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">File Select</label>
                            <div class="col-sm-4">
                                <input type="file" id="excel" type="file" name="excel" class="form-control file2 inline btn btn-primary" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="btn-upload" class="btn upload btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                            <i class="entypo-upload"></i>
                            Upload
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

    <script type="text/javascript">

        var AccessProcessed = 0;
        var PackageProcessed = 0;
        var TerminationProcessed = 0;

        jQuery(document).ready(function ($) {

            $('#btn-import').click(function(ev){
                ev.preventDefault();
                $('#import_file_modal').modal('show');
            });

            $('.CustomerDD').on('change', function(){
                var NumberDDID  = $(this).attr('NumberDDID');
                var CustomerID  = $(this).val();
                if(CustomerID == '') {
                    return false;
                }

                $('#CustomerDD').attr('disable','disable');
                $('#'+NumberDDID).attr('disable','disable');

                show_loading_bar(0);
                $.ajax({
                    url:  '{{URL::to('legacy_rates/getNumbersByCustomer')}}/'+CustomerID,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    beforeSend: function(){
                        //$('.btn.upload').button('loading');
                        show_loading_bar({
                            pct: 50,
                            delay: 5
                        });
                    },
                    afterSend: function(){
                        //console.log("Afer Send");
                    },
                    success: function (response) {
                        show_loading_bar({
                            pct: 100,
                            delay: 2
                        });
                        $('#CustomerDD').removeAttr('disable');
                        $('#'+NumberDDID).removeAttr('disable');
                        if (response.status == 'success') {
                            $('#'+NumberDDID).html('');
                            $('#'+NumberDDID).append($("<option></option>").attr("value","").text("Select"));
                            $.each(response.Numbers, function(key, value) {
                                $('#'+NumberDDID).append($("<option></option>").attr("value",key).text(value));
                            });
                            $('#'+NumberDDID).select2('val', '');
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                    },
                    // Form data
                    data: {},
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            });

            $('#NumberDD').on('change', function(){
                var NumberID = $('#NumberDD').val();
                if(NumberID == '') {
                    return false;
                }

                $('#CustomerDD').attr('disabled','disabled');
                $('#NumberDD').attr('disabled','disabled');
                $('#btn-export').attr('disabled','disabled');

                AccessProcessed = 0;
                PackageProcessed = 0;
                TerminationProcessed = 0;

                rateDataTableAccess(NumberID);
                rateDataTablePackage(NumberID);
                rateDataTableTermination(NumberID);

                IntervalID = setInterval(function () {
                    if(AccessProcessed == 1 && PackageProcessed == 1 && TerminationProcessed == 1) {
                        $('#CustomerDD').removeAttr('disabled');
                        $('#NumberDD').removeAttr('disabled');
                        $('#btn-export').removeAttr('disabled');
                        clearInterval(IntervalID);
                    }
                },1000);
            });

            $('#btn-export').on('click', function(){
                var CustomerID  = $('#CustomerDD').val();
                var NumberID    = $('#NumberDD').val();
                if(CustomerID == '' || NumberID == '') {
                    toastr.error('Please select Customer and Number', "Error", toastr_opts);
                    return false;
                }

                var access_data = [];
                $('#tbl-Access tbody tr').each(function() {
                    var tr = $(this);
                    var row = {};
                    $(this).find('.hiddenRowData').find('input[type=hidden]').each(function() {
                        row[this.name] = this.value;
                    });

                    row['NewRate'] = tr.find('input.txt-access').val();
                    access_data.push(row);
                });

                var package_data = [];
                $('#tbl-Package tbody tr').each(function() {
                    var tr = $(this);
                    var row = {};
                    $(this).find('.hiddenRowData').find('input[type=hidden]').each(function() {
                        row[this.name] = this.value;
                    });

                    row['NewRate'] = tr.find('input.txt-package').val();
                    package_data.push(row);
                });

                var termination_data = [];
                $('#tbl-Termination tbody tr').each(function() {
                    var tr = $(this);
                    var row = {};
                    $(this).find('.hiddenRowData').find('input[type=hidden]').each(function() {
                        row[this.name] = this.value;
                    });

                    row['NewRate'] = tr.find('input.txt-termination').val();
                    termination_data.push(row);
                });

                var data = {};
                data['Access'] = access_data;
                data['Package'] = package_data;
                data['Termination'] = termination_data;

                var formData = new FormData();
                formData.append("data", JSON.stringify(data));

                $('#CustomerDD').attr('disable','disable');
                $('#NumberDD').attr('disable','disable');
                $('#btn-export').attr('disable','disable');
                $('#btn-export').button('loading');

                show_loading_bar(0);
                $.ajax({
                    url: '{{URL::to('legacy_rates/getSpecialRatesByNumber')}}/'+NumberID+'/export',
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        $('#CustomerDD').removeAttr('disabled');
                        $('#NumberDD').removeAttr('disabled');
                        $('#btn-export').removeAttr('disabled');
                        $('#btn-export').button('reset');

                        show_loading_bar({
                            pct: 100,
                            delay: 2
                        });

                        var win = window.open('{{URL::to('legacy_rates/getSpecialRatesByNumber')}}/'+NumberID+'/download', '_blank');
                        if (win) {
                            //Browser has allowed it to be opened
                            win.focus();
                        } else {
                            //Browser has blocked it
                            alert('Please allow popups for this website');
                        }
                    },
                    beforeSend: function(){
                        show_loading_bar({
                            pct: 50,
                            delay: 5
                        });
                    },
                    // Form data
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false
                });
            });

            $("#form-upload").submit(function () {
                var formData = new FormData($('#form-upload')[0]);
                show_loading_bar(0);
                $.ajax({
                    url: '{{URL::to('legacy_rates/getSpecialRatesByNumber/import')}}',  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    beforeSend: function(){
                        $('#btn-upload').button('loading');
                        show_loading_bar(50);
                    },
                    afterSend: function(){
                        //console.log("Afer Send");
                    },
                    success: function (response) {
                        show_loading_bar(100);
                        if(response.status =='success'){
                            $("#form-upload .CustomerDD").select2('val','');
                            $("#form-upload #NumberDDImport").select2('val','');
                            $('#form-upload #excel').val('').trigger('change');
                            toastr.success(response.message, "Success", toastr_opts);
                            $('#import_file_modal').modal('hide');
                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                        $('#btn-upload').button('reset');
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
        });


        function rateDataTableAccess(NumberID) {

            access_data_table = $("#tbl-Access").DataTable({
                "bDestroy": true, // Destroy when resubmit form
                "bProcessing": true,
                "bServerSide": true,
                "scrollX": false,
                "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'change-view'><'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "sAjaxSource": '{{URL::to('legacy_rates/getSpecialRatesByNumber')}}/'+NumberID+'/{{RateTable::RATE_TABLE_TYPE_ACCESS}}',
                "fnServerParams": function(aoData) {
                    aoData.push();
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push();
                },
                "iDisplayLength": 10,
                "sPaginationType": "bootstrap",
                "aaSorting": [],
                "aoColumns":
                        [
                            {
                                "bSortable": false,
                                mRender: function(id, type, full) {
                                    var timezone = full['TimezonesID'] != 1 ? full['TimezoneTitle'] : '';
                                    return full['component'] + ' ' + timezone + ' ' + full['Origination'];
                                }
                            },
                            {
                                "bSortable": false,
                                mRender: function(id, type, full) {
                                    return full['SpecialCost'];
                                }
                            },
                            {
                                "bSortable": false,
                                mRender: function(id, type, full) {
                                    return full['DefaultCost'];
                                }
                            },
                            {
                                "bSortable" : false,
                                "bVisible" : true,
                                mRender: function(id, type, full) {
                                    var action;
                                    action = '<div class = "hiddenRowData" >';
                                    $.each(full, function(component_key, component_value) {
                                        action += '<input type="hidden" name="'+component_key+'" value="'+component_value+'" />';
                                    });
                                    action += '</div>';
                                    action += '<input type="text" name="" value="" class="form-control txt-access" />';

                                    return action;
                                }
                            }
                        ],
                "oTableTools":
                {
                    "aButtons": []
                },
                "fnDrawCallback": function() {
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });
                    AccessProcessed = 1;
                }
            });
            return false;
        }

        function rateDataTablePackage(NumberID) {

            package_data_table = $("#tbl-Package").DataTable({
                "bDestroy": true, // Destroy when resubmit form
                "bProcessing": true,
                "bServerSide": true,
                "scrollX": false,
                "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'change-view'><'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "sAjaxSource": '{{URL::to('legacy_rates/getSpecialRatesByNumber')}}/'+NumberID+'/{{RateTable::RATE_TABLE_TYPE_PACKAGE}}',
                "fnServerParams": function(aoData) {
                    aoData.push();
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push();
                },
                "iDisplayLength": 10,
                "sPaginationType": "bootstrap",
                "aaSorting": [],
                "aoColumns":
                        [
                            {
                                "bSortable": false,
                                mRender: function(id, type, full) {
                                    var timezone = full['TimezonesID'] != 1 ? full['TimezoneTitle'] : '';
                                    return full['component'] + ' ' + timezone;
                                }
                            },
                            {
                                "bSortable": false,
                                mRender: function(id, type, full) {
                                    return full['SpecialCost'];
                                }
                            },
                            {
                                "bSortable": false,
                                mRender: function(id, type, full) {
                                    return full['DefaultCost'];
                                }
                            },
                            {
                                "bSortable" : false,
                                "bVisible" : true,
                                mRender: function(id, type, full) {
                                    var action;
                                    action = '<div class = "hiddenRowData" >';
                                    $.each(full, function(component_key, component_value) {
                                        action += '<input type="hidden" name="'+component_key+'" value="'+component_value+'" />';
                                    });
                                    action += '</div>';
                                    action += '<input type="text" name="" value="" class="form-control txt-package" />';

                                    return action;
                                }
                            }
                        ],
                "oTableTools":
                {
                    "aButtons": []
                },
                "fnDrawCallback": function() {
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });
                    PackageProcessed = 1;
                }
            });
            return false;
        }

        function rateDataTableTermination(NumberID) {
            //var NumberID = $('#NumberDD').val();
            //termination_list_fields = ['Key','SpecialCost','DefaultCost','EUCountry','Country','Type','TimezonesID','TimezoneTitle','DefaultRateTableId','SpecialRateTableId'];

            termination_data_table = $("#tbl-Termination").DataTable({
                "bDestroy": true, // Destroy when resubmit form
                "bProcessing": true,
                "bServerSide": true,
                "scrollX": false,
                "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'change-view'><'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "sAjaxSource": '{{URL::to('legacy_rates/getSpecialRatesByNumber')}}/'+NumberID+'/{{RateTable::RATE_TABLE_TYPE_TERMINATION}}',
                "fnServerParams": function(aoData) {
                    aoData.push();
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push();
                },
                "iDisplayLength": 10,
                "sPaginationType": "bootstrap",
                "aaSorting": [],
                "aoColumns":
                        [
                            {
                                "bSortable": false,
                                mRender: function(id, type, full) {
                                    var EUCountryText = full['EUCountry'] == 1 ? 'Within EU' : 'Outside EU';
                                    return EUCountryText + ' ' + full['Country'] + ' ' + full['Type'];
                                }
                            },
                            {
                                "bSortable": false,
                                mRender: function(id, type, full) {
                                    return full['SpecialCost'];
                                }
                            },
                            {
                                "bSortable": false,
                                mRender: function(id, type, full) {
                                    return full['DefaultCost'];
                                }
                            },
                            {
                                "bSortable" : false,
                                "bVisible" : true,
                                mRender: function(id, type, full) {
                                    var action;
                                    action = '<div class = "hiddenRowData" >';
                                    $.each(full, function(component_key, component_value) {
                                        action += '<input type="hidden" name="'+component_key+'" value="'+component_value+'" />';
                                    });
                                    action += '</div>';
                                    action += '<input type="text" name="" value="" class="form-control txt-termination" />';

                                    return action;
                                }
                            }
                        ],
                "oTableTools":
                {
                    "aButtons": []
                },
                "fnDrawCallback": function() {
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });
                    TerminationProcessed = 1;
                }
            });
            return false;
        }

    </script>
@stop