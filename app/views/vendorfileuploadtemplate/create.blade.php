@extends('layout.main')

@section('content')

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li>
            <a href="{{URL::to('uploadtemplate')}}">Vendor file upload template</a>
        </li>
        <li class="active">
            <strong>{{$heading}}</strong>
        </li>
    </ol>
    <h3>{{$heading}}</h3>
    @include('includes.errors')
    @include('includes.success')
    @if(!empty($file_name))
    <p style="text-align: right;">
        <button type="button" id="btn-save" class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
            <i class="entypo-floppy"></i>
            Save
        </button>

        <a href="{{URL::to('/uploadtemplate')}}" class="btn btn-danger btn-sm btn-icon icon-left">
            <i class="entypo-cancel"></i>
            Close
        </a>
    </p>
    @endif
    <br>
    <div class="row">
        <div class="col-md-12">

            <form role="form" id="file-form" method="post" action="{{URL::to('uploadtemplate/create')}}" enctype="multipart/form-data" class="form-horizontal form-groups-bordered">
                <div class="panel panel-primary" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title">
                            Upload File
                        </div>

                        <div class="panel-options">
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        </div>
                    </div>

                    <div class="panel-body">
                        <div class="form-group">
                            <label for="field-1" class="col-sm-2 control-label">Template Name</label>
                            <div class="col-sm-4">
                                <input type="text" name="TemplateName" class="form-control"  value="{{$TemplateName}}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="field-1" class="col-sm-2 control-label">Load File:</label>
                            <div class="col-sm-4">
                                <input name="excel" type="file" class="form-control file2 inline btn btn-primary" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" />
                            </div>
                        </div>
                        <p style="text-align: right;">
                            <button type="submit"  class="save btn btn-primary btn-sm btn-icon icon-left">
                                <i class="entypo-floppy"></i>
                                Upload
                            </button>
                        </p>
                    </div>
                </div>
            </form>
            @if(!empty($file_name))
            <form role="form" id="csvimporter-form" method="post" class="form-horizontal form-groups-bordered">
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
                                <input type="text" class="form-control" name="option[Delimiter]" value="{{$csvoption->Delimiter}}" />
                                <input type="hidden" name="TemplateName" />
                                <input type="hidden" name="TemplateFile" value="{{$file_name}}" />
                                <input type="hidden" name="VendorFileUploadTemplateID" value="{{$templateID}}" />
                            </div>
                            <label for="field-1" class="col-sm-2 control-label">Enclosure:</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="option[Enclosure]" value="{{$csvoption->Enclosure}}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Escape:</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="option[Escape]" value="{{$csvoption->Escape}}" />
                            </div>
                            <label for="field-1" class="col-sm-2 control-label">First row:</label>
                            <div class="col-sm-4">
                                {{Form::select('option[Firstrow]', array('columnname'=>'Column Name','data'=>'Data'),$csvoption->Firstrow,array("class"=>"selectboxit"))}}
                            </div>
                        </div>
                        <p style="text-align: right;">
                            <button type="submit"  class="save btn btn-primary btn-sm btn-icon icon-left">
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

                    <div class="panel-body">
                        <div class="form-group">
                            <br />
                            <br />
                            <label for="field-1" class="col-sm-2 control-label">Code*</label>
                            <div class="col-sm-4">
                                {{Form::select('selection[Code]', $columns,(isset($attrselection->Code)?$attrselection->Code:''),array("class"=>"selectboxit"))}}
                            </div>

                            <label for="field-1" class="col-sm-2 control-label">Description*</label>
                            <div class="col-sm-4">
                                {{Form::select('selection[Description]', $columns,(isset($attrselection->Description)?$attrselection->Description:''),array("class"=>"selectboxit"))}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="field-1" class="col-sm-2 control-label">Rate*</label>
                            <div class="col-sm-4">
                                {{Form::select('selection[Rate]', $columns,(isset($attrselection->Rate)?$attrselection->Rate:''),array("class"=>"selectboxit"))}}
                            </div>

                            <label for="field-1" class="col-sm-2 control-label">EffectiveDate*</label>
                            <div class="col-sm-4">
                                {{Form::select('selection[EffectiveDate]', $columns,(isset($attrselection->EffectiveDate)?$attrselection->EffectiveDate:''),array("class"=>"selectboxit"))}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="field-1" class="col-sm-2 control-label">Action</label>
                            <div class="col-sm-4">
                                {{Form::select('selection[Action]', $columns,(isset($attrselection->Action)?$attrselection->Action:''),array("class"=>"selectboxit"))}}
                            </div>
                            <label for="field-1" class="col-sm-2 control-label">Action Insert</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="selection[ActionInsert]" value="{{$attrselection->ActionInsert}}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="field-1" class="col-sm-2 control-label">Action Update</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="selection[ActionUpdate]" value="{{$attrselection->ActionUpdate}}" />
                            </div>
                            <label for="field-1" class="col-sm-2 control-label">Action Delete</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="selection[ActionDelete]" value="{{$attrselection->ActionDelete}}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="field-1" class="col-sm-2 control-label">Interval1</label>
                            <div class="col-sm-4">
                                {{Form::select('selection[Interval1]', $columns,(isset($attrselection->Interval1)?$attrselection->Interval1:''),array("class"=>"selectboxit"))}}
                            </div>

                            <label for=" field-1" class="col-sm-2 control-label">IntervalN</label>
                            <div class="col-sm-4">
                                {{Form::select('selection[IntervalN]', $columns,(isset($attrselection->IntervalN)?$attrselection->IntervalN:''),array("class"=>"selectboxit"))}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for=" field-1" class="col-sm-2 control-label">Connection Fee</label>
                            <div class="col-sm-4">
                                {{Form::select('selection[ConnectionFee]', $columns,(isset($attrselection->ConnectionFee)?$attrselection->ConnectionFee:''),array("class"=>"selectboxit"))}}
                            </div>
                            <label for=" field-1" class="col-sm-2 control-label">Date Format</label>
                            <div class="col-sm-4">
                                {{Form::select('selection[DateFormat]',Company::$date_format ,(isset($attrselection->DateFormat)?$attrselection->DateFormat:''),array("class"=>"selectboxit"))}}
                            </div>
                        </div>
                    </div>
                </div>
            </form>
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
                        <?php $first =1;?>
                        @foreach ($columns as $column)
                            @if($first!=1)
                                <th>{{$column}}</th>
                            @endif
                            <?php $first =0;?>
                        @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($rows as $row)
                            <tr>
                            @foreach ($row as $key=>$item)
                                <td>{{$item}}</td>
                            @endforeach
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>


    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            {{$message}}
            /*$("#btn-save").click(function(e){
                $("#add-template").modal("show");
            });*/
            $("#btn-save").click(function(e){
                e.preventDefault();
                var fullurl = '';
                $("#csvimporter-form").find('[name="TemplateName"]').val($("#file-form").find('[name="TemplateName"]').val());
                if($('#csvimporter-form').find('[name="VendorFileUploadTemplateID"]').val()>0) {
                    fullurl = baseurl + '/uploadtemplate/update';
                }else{
                    fullurl = baseurl + '/uploadtemplate/store';
                }
                var data = new FormData($("#csvimporter-form")[0]);
                $.ajax({
                    url:fullurl, //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        $("#template-save").button('reset');
                        $(".btn").button('reset');
                        if (response.status == 'success') {
                            $('#add-template').modal('hide');
                            toastr.success(response.message, "Success", toastr_opts);
                            window.location = response.redirect;
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                    },
                    data: data,
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            });

        });
    </script>
@stop
@section('footer_ext')
    @parent

    <div class="modal fade" id="add-template">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="add-template-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add New Template</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Template Name</label>
                                <input type="text" name="TemplateName" class="form-control"  value="{{$TemplateName}}" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="template-save"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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
@stop