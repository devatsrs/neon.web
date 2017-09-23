@extends('layout.main')

@section('content')

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{URL::to('/dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li>
            <a href="{{URL::to('/rategenerators')}}">Rate Generator</a>
        </li>

        <li class="active">
            <strong>Update Rate Generator Rule</strong>
        </li>
    </ol>
    <h3>Update Rate Generator Rule</h3>
    <div class="float-right">
        <button type="button"  class="saveall btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
            <i class="entypo-floppy"></i>
            Save
        </button>
        <a href="{{URL::to('rategenerators/'.$id.'/edit')}}" class="btn btn-danger btn-sm btn-icon icon-left">
            <i class="entypo-cancel"></i>
            Close
        </a>
    </div>



    <div class="row">
        <div class="col-md-12">
            <ul class="nav nav-tabs bordered" >
                <li class="active"><a data-toggle="tab" href="#tab-code_description">Destination</a></li>
                <li><a data-toggle="tab" href="#tab-source">Sources</a></li>
                <li><a data-toggle="tab" href="#tab-margin">Margin</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab-code_description">
                    @include('rategenerators.rules.edit_code', array('id', 'RateRuleID', 'rategenerator_rules'))
                </div>
                <div class="tab-pane" id="tab-source">
                       @include('rategenerators.rules.edit_source', array('id', 'RateRuleID', 'rategenerator_sources', 'vendors', 'rategenerator'))
                </div>
                <div class="tab-pane" id="tab-margin">
                     @include('rategenerators.rules.edit_margin', array('id', 'RateRuleID', 'rategenerator_margins'))
                </div>
            </div>

        </div>
    </div>
    <script type="text/javascript">
        jQuery(document).ready(function($) {

            $(".saveall.btn").click(function(e){



                var Code = $("#rategenerator-code-from input[name='Code']").val();
                var Description = $("#rategenerator-code-from input[name='Description']").val();

                if((typeof Code  == 'undefined' || Code.trim() == '' ) && (typeof Description  == 'undefined' || Description.trim() == '' )){

                    setTimeout(function(){$('.btn').button('reset');},10);
                    toastr.error("Please Enter a Code Or Description", "Error", toastr_opts);
                    return false;

                }
                if($("#rategenerator-source-from input[name='AccountIds[]']:checked").length == 0 ) {

                    setTimeout(function(){$('.btn').button('reset');},10);
                    toastr.error("Please a Source", "Error", toastr_opts);
                    return false;
                }


                var _url = $('#rategenerator-code-from').attr("action");
                var formData = $('#rategenerator-code-from').serialize();

                $.post( _url, formData, function( response ) {

                    $(".btn").button('reset');
                    if ( response.status =='success' ) {
                        toastr.success(response.message, "Success", toastr_opts);
                    } else {
                        toastr.error(response.message, "Error", toastr_opts);
                        return false;
                    }

                    //source
                    var _url = $('#rategenerator-source-from').attr("action");
                    var formData = $('#rategenerator-source-from').serialize();

                    $.post( _url, formData, function( response ) {

                        $(".btn").button('reset');
                        if ( response.status =='success' ) {
                            toastr.success(response.message, "Success", toastr_opts);
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                            return false;
                        }

                      });
                });

                return false;

            });


            $(".btn.delete").click(function (e) {

                response = confirm('Are you sure?');

                if (response) {

                    $.ajax({
                        url: $(this).attr("href"),
                        type: 'POST',
                        dataType: 'json',
                        success: function (response) {
                            $(".btn.delete").button('reset');
                            if (response.status == 'success') {
                                if( typeof data_table !=  'undefined'){
                                    data_table.fnFilter('', 0);
                                }
                                toastr.success(response.message, "Success", toastr_opts);
                            } else {
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                        },
                        // Form data
                        //data: {},
                        cache: false,
                        contentType: false,
                        processData: false
                    });
                }
                return false;

            });

        });
    </script>
@stop
