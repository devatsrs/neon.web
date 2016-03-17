@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>
        <a href="{{URL::to('accounts')}}"> </i>Accounts</a>
    </li>
    <li>
        <a href="{{URL::to('accounts/'.$account->AccountID.'/edit')}}"></i>Edit Account({{$account->AccountName}})</a>
    </li>
    <li class="active">

        <strong>Authentication Rule</strong>
    </li>
</ol>
<h3>Authentication Rule</h3>
<p style="text-align: right;">
    @if(User::checkCategoryPermission('AuthenticationRule','Add'))
    <button type="button" id="save_account" class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
        <i class="entypo-floppy"></i>
        Save
    </button>
    @endif

    <a href="{{URL::to('accounts/'.$account->AccountID.'/edit')}}" class="btn btn-danger btn-sm btn-icon icon-left">
        <i class="entypo-cancel"></i>
        Close
    </a>
</p>
<?php $AccountNameFormat = array(''=>'Select Authentication Rule')+GatewayConfig::$AccountNameFormat;?>
@if($account->IsCustomer == 1 )
<div class="row">
    <div class="col-md-12">
        <form novalidate="novalidate" class="form-horizontal form-groups-bordered validate" method="post" id="customer_detail">
            <div data-collapsed="0" class="panel panel-primary">
                <div class="panel-heading">
                    <div class="panel-title">
                        Customer Details
                    </div>
                    <div class="panel-options">
                        <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Authentication Rule

                        </label>
                        <div class="desc col-sm-4">
                            {{Form::select('CustomerAuthRule',$AccountNameFormat,(isset($AccountAuthenticate->CustomerAuthRule)?$AccountAuthenticate->CustomerAuthRule:''),array( "class"=>"selectboxit"))}}
                        </div>
                        <label for="field-1" class="col-sm-2 customer_accountip control-label">Account IP</label>
                        <?php
                            $AccountIPList = array();
                            $CustomerAuthValue = '';
                            if(!empty($AccountAuthenticate->CustomerAuthRule) && $AccountAuthenticate->CustomerAuthRule == 'IP'){
                                $AccountIPList = array_filter(explode(',',$AccountAuthenticate->CustomerAuthValue));
                            }
                            if(!empty($AccountAuthenticate->CustomerAuthValue)){
                                $CustomerAuthValue = $AccountAuthenticate->CustomerAuthValue;
                            }

                        ?>
                        <div class="desc col-sm-4 customer_accountip table_{{count($AccountIPList)}}" >

                            <table class="table table-bordered datatable dataTable customeriptable ">
                                <thead>
                                <tr>

                                    <th>IP</th><th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($AccountIPList))
                                    @foreach($AccountIPList as $row2)
                                        <tr>
                                            <td>
                                                {{$row2}}
                                            </td>
                                            <td>
                                                <a class="btn  btn-danger btn-sm btn-icon icon-left customer-delete-ip"  href="javascript:;" ><i class="entypo-cancel"></i>Delete</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>


                            <a class="btn btn-primary  btn-sm btn-icon icon-left customer-add-ip"  href="javascript:;" ><i class="entypo-plus"></i>Add</a>
                        </div>
                        <label for="field-1" class="col-sm-2 control-label customer_value_other">Value</label>
                        <div class="desc col-sm-4 customer_value_other">
                            <input type="text" class="form-control"  name="CustomerAuthValueText" value="{{$CustomerAuthValue}}" id="CustomerAuthValueText">
                        </div>
                        <input type="hidden" class="form-control"  name="CustomerAuthValue" id="field-1" placeholder="" value="{{$CustomerAuthValue}}" />
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@if($account->IsVendor == 1 )
<div class="row">
    <div class="col-md-12">
        <form novalidate="novalidate" class="form-horizontal form-groups-bordered validate" method="post" id="vendor_detail">
            <div data-collapsed="0" class="panel panel-primary">
                <div class="panel-heading">
                    <div class="panel-title">
                        Vendor Details
                    </div>
                    <div class="panel-options">
                        <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label for="field-1" class="col-sm-2 control-label">Authentication Rule

                        </label>
                        <div class="desc col-sm-4">
                            {{Form::select('VendorAuthRule',$AccountNameFormat,(isset($AccountAuthenticate->VendorAuthRule)?$AccountAuthenticate->VendorAuthRule:''),array( "class"=>"selectboxit"))}}
                        </div>
                        <label for="field-1" class="col-sm-2 vendor_accountip control-label">Account IP</label>
                        <?php
                        $AccountIPList = array();
                        $VendorAuthValue = '';
                        if(!empty($AccountAuthenticate->VendorAuthRule) && $AccountAuthenticate->VendorAuthRule == 'IP'){
                            $AccountIPList = array_filter(explode(',',$AccountAuthenticate->VendorAuthValue));
                        }
                        if(!empty($AccountAuthenticate->VendorAuthValue)){
                            $VendorAuthValue = $AccountAuthenticate->VendorAuthValue;
                        }
                        ?>
                        <div class="desc col-sm-4 vendor_accountip table_{{count($AccountIPList)}}" >

                            <table class="table  table-bordered datatable dataTable vendoriptable ">
                                <thead>
                                <tr>

                                    <th>IP</th><th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($AccountIPList))
                                    @foreach($AccountIPList as $row2)
                                        <tr>
                                            <td>
                                                {{$row2}}
                                            </td>
                                            <td>
                                                <a class="btn  btn-danger btn-sm btn-icon icon-left vendor-delete-ip"  href="javascript:;" ><i class="entypo-cancel"></i>Delete</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>


                            <a class="btn btn-primary  btn-sm btn-icon icon-left vendor-add-ip"  href="javascript:;" ><i class="entypo-plus"></i>Add</a>
                        </div>
                        <label for="field-1" class="col-sm-2 control-label vendor_value_other">Value</label>
                        <div class="desc col-sm-4 vendor_value_other">
                            <input type="text" class="form-control"  name="VendorAuthRuleText" id="VendorAuthRuleText" value="{{$VendorAuthValue}}">
                        </div>
                        <input type="hidden" class="form-control"  name="VendorAuthValue" id="field-1" placeholder="" value="{{$VendorAuthValue}}" />
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
<script type="text/javascript">
    jQuery(document).ready(function($) {
        var acountiptable;
        $('#save_account').click(function(){
            var post_data = $('#vendor_detail').serialize()+'&'+$('#customer_detail').serialize()+'&AccountID='+'{{$account->AccountID}}';
            var post_url = '{{URL::to('accounts/authenticate_store')}}';
            submit_ajax(post_url,post_data);
        });
        $('body').on('click', '.customer-add-ip', function(e) {
            $('#form-addip-modal').find("[name='AccountIP']").val('');
            $("#addip-modal").modal('show');
            acountiptable = 'customeriptable';
        });
        $('body').on('click', '.vendor-add-ip', function(e) {
            $('#form-addip-modal').find("[name='AccountIP']").val('');
            $("#addip-modal").modal('show');
            acountiptable = 'vendoriptable';
        });
        $('body').on('click', '.customer-delete-ip', function(e) {
            e.preventDefault();
            result = confirm("Are you Sure?");
            if(result){
                $(this).parent().parent('tr').remove();
                var nameIDs = $('table.customeriptable tr td:first-child').map(function () {
                    return this.innerHTML.trim();
                }).get().join(',');
                $("#customer_detail [name='CustomerAuthValue']").val(nameIDs);
            }
        });
        $('body').on('click', '.vendor-delete-ip', function(e) {
            e.preventDefault();
            result = confirm("Are you Sure?");
            if(result){
                $(this).parent().parent('tr').remove();
                var nameIDs = $('table.vendoriptable tr td:first-child').map(function () {
                    return this.innerHTML.trim();
                }).get().join(',');
                $("#vendor_detail [name='VendorAuthValue']").val(nameIDs);
            }
        });
        $('#VendorAuthRuleText').keyup(function () {
            $('#vendor_detail').find('[name="VendorAuthValue"]').val($(this).val());
        });
        $('#CustomerAuthValueText').keyup(function () {
            $('#customer_detail').find('[name="CustomerAuthValue"]').val($(this).val());
        });
        @if('{{$AccountAuthenticate->CustomerAuthRule}}' == 'IP')
            $('.customer_accountip').show();
        @endif
        @if('{{$AccountAuthenticate->VendorAuthRule}}' == 'IP')
            $('.vendor_accountip').show();
        @endif

        $('[name="CustomerAuthRule"]').change(function(){
            $('.customer_accountip').hide();
            $('.customer_value_other').hide();
            if($(this).val() == 'Other'){
                $('.customer_value_other').show();
            }else if($(this).val() == 'IP'){
                $('.customer_accountip').show();
            }

        });
        $('[name="VendorAuthRule"]').change(function(){
            $('.vendor_accountip').hide();
            $('.vendor_value_other').hide();
            if($(this).val() == 'Other'){
                $('.vendor_value_other').show();
            }else if($(this).val() == 'IP'){
                $('.vendor_accountip').show();
            }


        });
        $("#form-addip-modal").submit(function(e){
            e.preventDefault();
            var ip=$(this).find("[name='AccountIP']").val().trim();
            var val_ip=0;
            $('table.'+acountiptable+' tr td:first-child').each(function (){
                if(this.innerHTML.trim()==ip){
                    //   toastr.error("Already IP exits.", "Error", toastr_opts);
                    //  val_ip=1;
                }
            });
            if(val_ip==0){

                $.ajax({
                    url: baseurl + '/accounts/validate_ip',
                    type:'POST',
                    data:{ip:ip},
                    datatype:'json',
                    success: function(response) {
                        if (response.status == 'success') {
                            if(acountiptable == 'customeriptable'){
                                claass_deletip = 'customer-delete-ip';
                            }else if(acountiptable == 'vendoriptable'){
                                claass_deletip = 'vendor-delete-ip';
                            }
                            var accoutiphtml = '<tr><td>'+ip+'</td><td><a class="btn  btn-danger btn-sm btn-icon icon-left '+claass_deletip+' "  href="javascript:;" ><i class="entypo-cancel"></i>Delete</a></td></tr>';
                            $('.'+acountiptable).children('tbody').append(accoutiphtml);
                            var nameIDs = $('table.'+acountiptable+' tr td:first-child').map(function () {
                                return this.innerHTML.trim();
                            }).get().join(',');
                            if(acountiptable == 'customeriptable'){
                                $("#customer_detail [name='CustomerAuthValue']").val(nameIDs);
                            }else if(acountiptable == 'vendoriptable'){
                                $("#vendor_detail [name='VendorAuthValue']").val(nameIDs);
                            }

                            $('.'+acountiptable).children('tbody').children('tr').children('td');
                            $("#addip-modal").modal('hide');
                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                        }

                    }
                });

            }
        });
        $('.customer_accountip').hide();
        $('.customer_value_other').hide();
        $('.vendor_value_other').hide();
        $('.vendor_accountip').hide();
        $('[name="CustomerAuthRule"]').trigger('change');
        $('[name="VendorAuthRule"]').trigger('change');
    });

</script>
@stop
@section('footer_ext')
@parent
<div class="modal fade" id="addip-modal" >
    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" id="form-addip-modal" method="post" class="form-horizontal form-groups-bordered" enctype="multipart/form-data">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add IP</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Account IP</label>
                        <div class="col-sm-5">
                            <input name="AccountIP" type="text" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit"  class="btn btn-primary btn-sm btn-icon icon-left">
                        <i class="entypo-floppy"></i>
                        Add
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