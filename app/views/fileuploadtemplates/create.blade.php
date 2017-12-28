@extends('layout.main')

@section('content')

    <?php
        $TemplateIDV    = "";
        $TemplateNameV  = "";
        $TemplateTypeV  = "";

        if(isset($template)) {
            $TemplateIDV    = !empty($template->FileUploadTemplateID) ? $template->FileUploadTemplateID : '';
            $TemplateNameV  = !empty($template->Title) ? $template->Title : '';
            $TemplateTypeV  = !empty($template->Type) ? $template->Type : '';
            $post_url       = URL::to('uploadtemplate/'.$TemplateIDV.'/edit');
        } else {
            $post_url       = URL::to('uploadtemplate/create');
        }
        if(isset($TemplateType)) {
            $TemplateTypeV   = !empty($TemplateType) ? $TemplateType : '';
        }
        if(!empty($TemplateName)) {
            $TemplateNameV   = !empty($TemplateName) ? $TemplateName : '';
        }
    ?>

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li>
            <a href="{{URL::to('uploadtemplate')}}">file upload template</a>
        </li>
        <li>
            <a><span>{{upload_template_dropbox($TemplateIDV)}}</span></a>
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

            <form role="form" id="file-form" method="post" action="{{$post_url}}" enctype="multipart/form-data" class="form-horizontal form-groups-bordered">
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
                            <label for="field-1" class="col-sm-2 control-label">Template Type</label>
                            <div class="col-sm-4">
                                {{Form::select('TemplateType', $Types, $TemplateTypeV, array("class"=>"select2 small", "id"=>"TemplateType"))}}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="field-1" class="col-sm-2 control-label">Template Name</label>
                            <div class="col-sm-4">
                                <input type="text" name="TemplateName" class="form-control"  value="{{$TemplateNameV}}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="field-1" class="col-sm-2 control-label">Load File:</label>
                            <div class="col-sm-4">
                                <input name="excel" type="file" class="form-control file2 inline btn btn-primary" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" />
                            </div>
                        </div>
                        <div class="form-group" id="skiprows_box" style="{{ $TemplateTypeV==8 ? '' : 'display: none;' }}">
                            <label for="field-1" class="col-sm-2 control-label" style="text-align: right;">Skips rows from Start</label>
                            <div class="col-sm-3" style="padding-left:40px;">
                                <input name="start_row" type="number" class="form-control" data-label="
                                <i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" style="" placeholder="Skips rows from Start" min="0" value="{{(!empty($attrskiprows->start_row) ? $attrskiprows->start_row : 0)}}">
                            </div>
                            <label class="col-sm-2 control-label" style="text-align: right;">Skips rows from Bottom</label>
                            <div class="col-sm-3">
                                <input name="end_row" type="number" class="form-control" data-label="
                                    <i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" placeholder="Skips rows from Bottom" min="0" value="{{(!empty($attrskiprows->end_row) ? $attrskiprows->end_row : 0)}}">
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
                <div class="panel panel-primary" data-collapsed="0" id="csvimporter-form-data">
                    <div class="panel-heading">
                        <div class="panel-title">
                            Import Rules
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
                                <input type="hidden" name="FileUploadTemplateID" value="{{$TemplateIDV}}" />
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
                                {{Form::select('option[Firstrow]', array('columnname'=>'Column Name','data'=>'Data'),$csvoption->Firstrow,array("class"=>"select2 small"))}}
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
                            <a href="#" data-rel="collapse">{{--<i class="entypo-down-open"></i>--}}</a>
                        </div>
                    </div>

                    <div class="panel-body field-remaping" id="field-remaping-1" style="{{ $TemplateTypeV==1 ? '' : 'display: none;' }}">
                        @include('fileuploadtemplates.customercdrtemplate')
                    </div>
                    <div class="panel-body field-remaping" id="field-remaping-2" style="{{ $TemplateTypeV==2 ? '' : 'display: none;' }}">
                        @include('fileuploadtemplates.vendorcdrtemplate')
                    </div>
                    <div class="panel-body field-remaping" id="field-remaping-3" style="{{ $TemplateTypeV==3 ? '' : 'display: none;' }}">
                        @include('fileuploadtemplates.accounttemplate')
                    </div>
                    <div class="panel-body field-remaping" id="field-remaping-4" style="{{ $TemplateTypeV==4 ? '' : 'display: none;' }}">
                        @include('fileuploadtemplates.leadstemplate')
                    </div>
                    <div class="panel-body field-remaping" id="field-remaping-5" style="{{ $TemplateTypeV==5 ? '' : 'display: none;' }}">
                        @include('fileuploadtemplates.dialstringtemplate')
                    </div>
                    <div class="panel-body field-remaping" id="field-remaping-6" style="{{ $TemplateTypeV==6 ? '' : 'display: none;' }}">
                        @include('fileuploadtemplates.accountipstemplate')
                    </div>
                    <div class="panel-body field-remaping" id="field-remaping-7" style="{{ $TemplateTypeV==7 ? '' : 'display: none;' }}">
                        @include('fileuploadtemplates.itemtemplate')
                    </div>
                    <div class="panel-body field-remaping" id="field-remaping-8" style="{{ $TemplateTypeV==8 ? '' : 'display: none;' }}">
                        @include('fileuploadtemplates.vendorratetemplate')
                    </div>
                    <div class="panel-body field-remaping" id="field-remaping-9" style="{{ $TemplateTypeV==9 ? '' : 'display: none;' }}">
                        @include('fileuploadtemplates.paymenttemplate')
                    </div>
                </div>
            </form>
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

            $(document).on('change', '#TemplateType', function() {
                var TemplateType = $(this).val();

                /*$('.field-remaping').hide();
                $('#field-remaping-'+TemplateType).show();*/

                if(TemplateType == 8) {
                    $('#skiprows_box').show();
                } else {
                    $('#skiprows_box').hide();
                    $('#skiprows_box input[name="start_row"]').val(0);
                    $('#skiprows_box input[name="end_row"]').val(0);
                }
            });

            $("#btn-save").click(function(e){
                e.preventDefault();
                var fullurl = '';
                $("#csvimporter-form").find('[name="TemplateName"]').val($("#file-form").find('[name="TemplateName"]').val());
                if($('#csvimporter-form').find('[name="FileUploadTemplateID"]').val()>0) {
                    fullurl = baseurl + '/uploadtemplate/update';
                }else{
                    fullurl = baseurl + '/uploadtemplate/store';
                }

                //console.log($('#csvimporter-form-data :input'));

                //var data = new FormData($("#csvimporter-form")[0]);
                var data = new FormData();
                $.each($('#csvimporter-form-data :input'), function (obj, input) {
                    if($(input).attr('name') != undefined)
                        data.append($(input).attr('name'), $(input).val());
                });

                var TemplateType = $('#file-form select[name="TemplateType"]').val();

                data.append('start_row', $('#file-form input[name="start_row"]').val());
                data.append('end_row', $('#file-form input[name="end_row"]').val());
                data.append('TemplateType', TemplateType);

                $.each($('#field-remaping-'+TemplateType+' :input'), function (obj, input) {
                    if($(input).attr('name') != undefined)
                        data.append($(input).attr('name'), $(input).val());
                });

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
                                <input type="text" name="TemplateName" class="form-control"  value="{{$TemplateNameV}}" />
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