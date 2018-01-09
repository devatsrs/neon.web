@extends('layout.customer.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="#"><i class="entypo-home"></i>Profile</a>
    </li>
    <li class="active">
        <strong>Edit Profile</strong>
    </li>
</ol>
<h3>Edit Account</h3>
@include('includes.errors')
@include('includes.success')

<p style="text-align: right;">
    <button type="button"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
        <i class="entypo-floppy"></i>
        Save
    </button>

    <a href="{{URL::to('customer/profile')}}" class="btn btn-danger btn-sm btn-icon icon-left">
        <i class="entypo-cancel"></i>
        Close
    </a>
</p>
<br>
<div class="row">
<div class="col-md-12">
    <form role="form" id="account-from" method="post" action="{{URL::to('customer/profile/update')}}" class="form-horizontal form-groups-bordered" enctype="multipart/form-data">


            <div class="panel panel-primary" data-collapsed="0">
            <div class="panel-heading">
                <div class="panel-title">
                    Account Details
                </div>

                <div class="panel-options">
                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                </div>
            </div>

            <div class="panel-body">


                <div class="form-group">
                    <label class="col-sm-2 control-label">Password</label>
                    <div class="col-sm-4">
                        <input class="hide">
                        <input type="password" class="hide">
                            <input type="password" class="form-control"  name="password" id="field-1" placeholder="" value=""/>
                    </div>

                    <label for="field-1" class="col-sm-2 control-label">Picture</label>
                    <div class="col-sm-4">
                            <input id="picture" type="file" name="Picture" class="form-control file2 inline btn btn-primary" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" />
                            @if(Customer::get_customer_picture_url(Customer::get_accountID()) !='')
                            <img src="{{ Customer::get_customer_picture_url(Customer::get_accountID()) }}" alt="" class="img-circle" width="44" />
                            @endif

                    </div>
                </div>

            </div>
        </div>

        <div class="panel panel-primary" data-collapsed="0">
            <div class="panel-heading">
                <div class="panel-title">
                    Address Information
                </div>

                <div class="panel-options">
                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                </div>
            </div>

            <div class="panel-body">
                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">Address Line 1</label>
                    <div class="col-sm-4">
                        <input type="text" name="Address1" class="form-control" id="field-1" placeholder="" value="{{$account->Address1}}" />
                    </div>

                    <label for="field-1" class="col-sm-2 control-label">City</label>
                    <div class="col-sm-4">
                        <input type="text" name="City" class="form-control" id="field-1" placeholder="" value="{{$account->City}}" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">Address Line 2</label>
                    <div class="col-sm-4">
                        <input type="text" name="Address2" class="form-control" id="field-1" placeholder="" value="{{$account->Address2}}" />
                    </div>

                    <label for="field-1" class="col-sm-2 control-label">Post/Zip Code</label>
                    <div class="col-sm-4">
                        <input type="text" name="PostCode" class="form-control" id="field-1" placeholder="" value="{{$account->PostCode}}" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="field-1" class="col-sm-2 control-label">Address Line 3</label>
                    <div class="col-sm-4">
                        <input type="text" name="Address3" class="form-control" id="field-1" placeholder="" value="{{$account->Address3}}" />
                    </div>

                    <label for=" field-1" class="col-sm-2 control-label">*Country</label>
                    <div class="col-sm-4">

                    {{Form::select('Country', $countries, $account->Country ,array("class"=>"form-control select2"))}}
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $(".save.btn").click(function (ev) {
            $("#account-from").submit();
        });
        $('select[name="BillingCycleType"]').on( "change",function(e){
            var selection = $(this).val();
            $(".billing_options input, .billing_options select").attr("disabled", "disabled");
            $(".billing_options").hide();
            console.log(selection);
            switch (selection){
                case "weekly":
                        $("#billing_cycle_weekly").show();
                        $("#billing_cycle_weekly select").removeAttr("disabled");
                        break;
                case "monthly_anniversary":
                        $("#billing_cycle_monthly_anniversary").show();
                        $("#billing_cycle_monthly_anniversary input").removeAttr("disabled");
                        break;
                case "in_specific_days":
                        $("#billing_cycle_in_specific_days").show();
                        $("#billing_cycle_in_specific_days input").removeAttr("disabled");
                        break;
                case "subscription":
                        $("#billing_cycle_subscription").show();
                        $("#billing_cycle_subscription input").removeAttr("disabled");
                        break;
            }
        });
        $('select[name="BillingCycleType"]').trigger( "change" );


        $('.upload-doc').click(function(ev){
                    ev.preventDefault();

                    $("#form-upload [name='AccountApprovalID']").val($(this).attr('data-id'));
                    $('#upload-modal-account h4').html('Upload '+$(this).attr('data-title')+' Document');
                    $('#upload-modal-account').modal('show');
                });
                $('#form-upload').submit(function(ev){
                ev.preventDefault();
                 var formData = new FormData($('#form-upload')[0]);
                    $.ajax({
                        url: baseurl + '/accounts/upload/{{$account->AccountID}}',  //Server script to process data
                        type: 'POST',
                        dataType: 'json',
                        beforeSend: function(){
                            $('.btn.upload').button('loading');
                        },
                        afterSend: function(){
                            console.log("Afer Send");
                        },
                        success: function (response) {
                            if(response.status =='success'){
                                toastr.success(response.message, "Success", toastr_opts);
                                $('#upload-modal-account').modal('hide');
                                var url3 = baseurl+'/accounts/download_doc/'+response.LastID;
                                var delete_doc_url = baseurl+'/accounts/delete_doc/'+response.LastID;
                                var filename = response.Filename;

                                if($('.table_'+$("#form-upload [name='AccountApprovalID']").val()).html().trim() === ''){
                                    $('.table_'+$("#form-upload [name='AccountApprovalID']").val()).html('<table class="table table-bordered datatable dataTable "><thead><tr><th>File Name</th><th>Action</th></tr></thead><tbody class="doc_'+$("#form-upload [name='AccountApprovalID']").val()+'"></tbody></table>');
                                }
                                var down_html = $('.doc_'+$("#form-upload [name='AccountApprovalID']").val()).html()+'<tr><td>'+filename+'</td><td><a class="btn btn-success btn-sm btn-icon icon-left"  href="'+url3+'" title="" ><i class="entypo-down"></i>Download</a> <a class="btn  btn-danger delete-doc btn-sm btn-icon icon-left"  href="'+delete_doc_url+'" title="" ><i class="entypo-trash"></i>Delete</a></td></tr>';
                                $('.doc_'+$("#form-upload [name='AccountApprovalID']").val()).html(down_html);
                                if(response.refresh){
                                    setTimeout(function(){window.location.reload()},1000);
                                }

                            }else{
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                            $('.btn.upload').button('reset');
                        },
                        // Form data
                        data: formData,
                        //Options to tell jQuery not to process data or worry about content-type.
                        cache: false,
                        contentType: false,
                        processData: false
                    });
                });
                @if($account->Status != Account::VERIFIED)
                $(document).ajaxSuccess(function( event, jqXHR, ajaxSettings, ResponseData ) {
                    //Reload only when success message.
                    if (ResponseData.status != undefined &&  ResponseData.status == 'success') {
                        setTimeout(function(){window.location.reload()},1000);
                    }
                });
                @endif

            $('body').on('click', '.delete-doc', function(e) {
                e.preventDefault();
                result = confirm("Are you Sure?");
                if(result){
                    submit_ajax($(this).attr('href'),'AccountID=AccountID')
                    $(this).parent().parent('tr').remove();
                }
            });


            @if ($account->VerificationStatus == Account::NOT_VERIFIED)
                $(".btn-toolbar .btn").first().button("toggle");
          
            @elseif ($account->VerificationStatus == Account::VERIFIED)
                $(".btn-toolbar .btn").last().button("toggle");
            @endif
    });
</script>
<style>
    .hide{ display:none; }
</style>
@include('includes.ajax_submit_script', array('formID'=>'account-from' , 'url' => ('customer/profile/update')))

@stop
@section('footer_ext')
@parent

@stop