@extends('layout.main')

@section('content')

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li class="active">

            <strong>Data Import From Template</strong>
        </li>
    </ol>
    <h3>Upload Template File</h3><br/>
    <div class="row">
        <div class="col-md-12">
            <form role="form" id="form-upload" name="form-upload" method="post" class="form-horizontal form-groups-bordered" enctype="multipart/form-data">
                <div class="panel panel-primary" data-collapsed="0">
                    <div class="panel-heading">
                        <div class="panel-title">
                            Upload Template File
                        </div>

                        <div class="panel-options">
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label for="field-1" class="col-sm-2 control-label">Upload (.xls, .xlsx, .csv)</label>
                            <div class="col-sm-1">
                                <input name="excel" id="excel" type="file" class="form-control file2 inline btn btn-primary" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" />
                            </div>
                        </div>

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

    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('.btn.upload').click(function(e){
                e.preventDefault();

                var formData = new FormData($('#form-upload')[0]);
                show_loading_bar(0);
                $.ajax({
                    url:  '{{URL::to('importdata/uploadtemplatefile')}}',  //Server script to process data
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
                        //console.log("Afer Send");
                    },
                    success: function (response) {
                        show_loading_bar({
                            pct: 100,
                            delay: 2
                        });
                        $('.btn.upload').button('reset');
                        if (response.status == 'success') {
                            location.reload();
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
        });
    </script>
@stop