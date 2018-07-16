@extends('layout.main')
@section('content')
<ol class="breadcrumb bc-3">
    <li>
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>
        <a href="{{URL::to('accounts')}}">Accounts</a>
    </li>
    <li>
        {{customer_dropbox($id,["IsCustomer"=>1])}}
    </li>
    <li class="active">
        <strong>Customer Rate Sheet Downloads</strong>
    </li>
</ol>

<h3>Customer Rate Sheet Download</h3>


@include('accounts.errormessage')

<ul class="nav nav-tabs bordered">
    <li >
        <a href="{{ URL::to('/customers_rates/'.$id) }}" >
             Customer Rate
        </a>
    </li>
    @if(User::checkCategoryPermission('CustomersRates','Settings'))
     <li>
        <a href="{{ URL::to('/customers_rates/settings/'.$id) }}" >
             Settings
        </a>
    </li>
    @endif
    <li class="active">
        <a href="{{ URL::to('/customers_rates/'.$id.'/download') }}" >
             Download Rate Sheet
        </a>
    </li>
    @if(User::checkCategoryPermission('CustomersRates','History'))
    <li>
        <a href="{{ URL::to('/customers_rates/'.$id.'/history') }}" >
            History
        </a>
    </li>
    @endif
</ul>


<div class="panel panel-primary" data-collapsed="0">
    
    <div class="panel-heading">
        <div class="panel-title">
            Customer Rate Sheet Download
        </div>
        
        <div class="panel-options">
            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
        </div>
    </div>
    
    <div class="panel-body">
        
        <form id="form-download" action="{{URL::to('customers_rates/'.$id.'/process_download')}}" role="form" class="form-horizontal form-groups-bordered">
            <div class="form-group">
                <label for="field-1" class="col-sm-3 control-label">Trunk</label>
                <div class="col-sm-5">
                   @foreach ($trunks as $trunk)
                        @if(!empty($trunk->TrunkID))
                        <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="Trunks[]" value="{{$trunk->TrunkID}}" >{{$trunk->Trunk}}
                            </label>
                        </div>
                        </div>
                        @endif
                   @endforeach
                </div>
            </div>
            <div class="form-group">
                <label for="field-1" class="col-sm-3 control-label">Timezones</label>
                <div class="col-sm-5">
                    @foreach ($Timezones as $key => $value)
                        @if(!empty($key))
                            <div class="col-sm-4">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="Timezones[]" value="{{$key}}" >{{$value}}
                                    </label>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
            <div class="form-group">
                <label for="field-1" class="col-sm-3 control-label">Output format</label>
                <div class="col-sm-5">
 
                   {{ Form::select('Format', $rate_sheet_formates, Input::get('Format') , array("class"=>"select2 small","id"=>"fileformat")) }}
                </div>
            </div>
            <div class="form-group">
                <label for="field-1" class="col-sm-3 control-label">File Type</label>
                <div class="col-sm-5">

                   {{ Form::select('filetype', array(''=>'Select a Type'), Input::get('downloadtype') , array("class"=>"select2","id"=>"filetype",'allowClear'=>'true')) }}
                </div>
            </div>
            <div class="form-group effective">
                <label for="field-1" class="col-sm-3 control-label">Effective</label>
                <div class="col-sm-5">

                    <select name="Effective" class="select2 small" data-allow-clear="true" data-placeholder="Select Effective" id="fileeffective">
                        <option value="Now">Now</option>
                        <option value="Future">Future</option>
                        <option value="CustomDate">Custom Date</option>
                        <option value="All">All</option>
                    </select>
                </div>
            </div>
            <div class="form-group DateFilter" style="display: none;">
                <label for="field-1" class="col-sm-3 control-label">Date</label>
                <div class="col-sm-5">
                    {{ Form::text('CustomDate', date('Y-m-d'), array("class"=>"form-control datepicker","data-date-format"=>"yyyy-mm-dd","placeholder"=>date('Y-m-d'),"data-startdate"=>date('Y-m-d'))) }} {{--  ,"data-enddate"=>date('Y-m-d') --}}
                </div>
            </div>
            <div class="form-group">
                <label for="field-1" class="col-sm-3 control-label">Merge Output file By Trunk</label>
                <div class="col-sm-5">
                    <div class="make-switch switch-small" data-on-label="<i class='entypo-check'></i>" data-off-label="<i class='entypo-cancel'></i>" data-animated="false">
                                <input type="hidden" name="isMerge" value="0">
                                <input type="checkbox" name="isMerge" value="1">
                                <input type="hidden" name="sendMail" value="0">
                                <input type="hidden" name="type" value="CD" />
                    </div>
                </div>
            </div>
            <div style="max-height: 500px; overflow-y: auto; overflow-x: hidden;">
            <h4 >Click <span class="label label-info" onclick="$('.my_account_table-5').toggle();$('#table-5_wrapper').toggle();"    style="cursor: pointer">here</span> to select additional customer accounts for bulk ratesheet download.</h4>

                <div class="row my_account_table-5">
                 @if(User::is_admin())
                    <div class="col-sm-4" style="float: right">
                        {{Form::select('account_owners',$account_owners,Input::get('account_owners'),array("id"=>"account_owners","class"=>"select2"))}}
                        </div>
                     @else
                        <!-- For Account Manager -->
                       <input type="hidden" name="account_owners" value="{{User::get_userID()}}">
                     @endif
                </div>
            <table class="table table-bordered datatable" id="table-5">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectallcust" name="customer[]" class="" /></th>
                        <th>Customer Name</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            </div>

        </form>
        <p style="text-align: right;margin-top: 5px">
            <a href="#" class="btn emailsend hidden btn-primary btn-sm btn-icon icon-left">
                <i class="entypo-mail"></i>
                Send Email
            </a>
            <a href="#" class="btn download btn-primary btn-sm btn-icon icon-left">
                <i class="entypo-floppy"></i>
                Download
            </a>
        </p>
    </div>
    
</div>


<style >
.dataTables_filter label{
    display:block !important;
}
.border_left .dataTables_filter {
  border-left: 1px solid #eeeeee !important;
  border-top-left-radius: 3px;
}
</style>
<script type="text/javascript">
var editor_options 	  =  		{"ratetemplateoptions":true};
jQuery(document).ready(function ($) {

    $('#fileformat').change(function(e){
        if($(this).val()){
            var url = baseurl +'/customers_rates/{{$id}}/customerdownloadtype/'+$(this).val();
            $.ajax({
                url:  url,  //Server script to process data
                type: 'POST',
                success: function (response) {
                    $('#filetype').empty();
                    $('#filetype').append(response);
                    setTimeout(function(){
                        $("#filetype").select2('val','');
                    },200)
                },
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false
            });

            if($(this).val()=='{{RateSheetFormate::RATESHEET_FORMAT_VOS20}}'){
                $('#fileeffective').empty();
                var html ='<option value="Now" selected="selected">Now</option><option value="Future">Future</option><option value="CustomDate">Custom Date</option>';
                $('#fileeffective').append(html).trigger('change');
            }else{
                $('#fileeffective').empty();
                var html ='<option value="Now" selected="selected">Now</option><option value="Future">Future</option><option value="CustomDate">Custom Date</option><option value="All">All</option>';
                $('#fileeffective').append(html).trigger('change');
            }


        }else{
            $('#filetype').empty();
            $("#filetype").select2('val','');
        }
    });

    $(".btn.download").click(function () {
           // return false;
            var formData = new FormData($('#form-download')[0]);
            $.ajax({
                url:  $('#form-download').attr("action"),  //Server script to process data
                type: 'POST',
                dataType: 'json',
                //Ajax events
                beforeSend: function(){
                    $('.btn.download').button('loading');
                },
                afterSend: function(){
                    console.log("Afer Send");
                },
                success: function (response) {
                    if (response.status == 'success') {
                        toastr.success(response.message, "Success", toastr_opts);
                        reloadJobsDrodown(0);
                     } else {
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                    //alert(response.message);
                    $('.btn.download').button('reset');
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

    $(".dataTables_wrapper select").select2({
    minimumResultsForSearch: -1
    });

    // Replace Checboxes
    $(".pagination a").click(function (ev) {
    replaceCheckboxes();
    });
    initCustomerGrid('table-5');
    var first_call = true;
    $("#account_owners").change(function(ev) {
        var account_owners = $(this).val();
        if(account_owners!=""){
            initCustomerGrid('table-5',account_owners);
        }else if(first_call == false ){
            initCustomerGrid('table-5','');
        }
        first_call = false;;

    });

    $(".btn.emailsend").click(function (e) {
        e.preventDefault();
        $("#BulkMail-form [name='email_template']").val('').trigger("change");
        $("#BulkMail-form [name='template_option']").val('').trigger("change");
        $("#BulkMail-form [name='test']").val(0);
        //$("#BulkMail-form [name='Type']").selectBoxIt().data("selectBox-selectBoxIt").selectOption('')
        $("#BulkMail-form")[0].reset();
        $("#modal-BulkMail").modal({
            show: true
        });
    });

    $("#BulkMail-form [name=email_template]").change(function(e){
        var templateID = $(this).val();
        if(templateID>0) {
            var url = baseurl + '/accounts/' + templateID + '/ajax_template';
            $.get(url, function (data, status) {
                if (Status = "success") {
                    editor_reset(data);
                } else {
                    toastr.error(status, "Error", toastr_opts);
                }
            });
        }
    });

    /*$('#BulkMail-form [name="email_template_privacy"]').change(function(e){
        e.preventDefault();
        e.stopPropagation();
        editor_reset(new Array());
    });*/
	
	   $('#BulkMail-form [name="email_template_privacy"]').change(function(e){
		   drodown_reset(); 
   });
   
    function drodown_reset(){
            var privacyID = $('#BulkMail-form [name="email_template_privacy"]').val(); 
            if(privacyID == null){
                return false;
            } 
            var Type = $('#BulkMail-form [name="Type"]').val(); 
            var url = baseurl + '/accounts/' + privacyID + '/ajax_getEmailTemplate/'+Type;
            $.get(url, function (data, status) {
                if (Status = "success") {
                    var modal = $("#modal-BulkMail");
                    var el = modal.find('#BulkMail-form [name=email_template]');
                    rebuildSelect2(el,data,'');
                } else {
                    toastr.error(status, "Error", toastr_opts);
                }
            });
        }

/*    $('#BulkMail-form [name="Type"]').change(function(e){
        var Type =  $('#BulkMail-form [name="Type"]').val();
        var privacyID = $('#BulkMail-form [name="email_template_privacy"]').val();
        if(Type==''){
            Type =0;
        }
        if(privacyID == null || Type == null){
            return false;
        }
        var url = baseurl + '/accounts/' + privacyID + '/ajax_getEmailTemplate/'+Type;
        $.get(url, function (data, status) {
            if (Status = "success") {
                var modal = $("#modal-BulkMail");
                var el = modal.find('#BulkMail-form [name=email_template]');
                rebuildSelect2(el,data,'')
            } else {
                toastr.error(status, "Error", toastr_opts);
            }
        });
    });*/

    $("#BulkMail-form [name=template_option]").change(function(e){
        if($(this).val()==1){
            $('#templatename').removeClass("hidden");

        }else{
            $('#templatename').addClass("hidden");
        }
    });

    $("#BulkMail-form").submit(function(e){
        e.preventDefault();
        if($("#BulkMail-form").find('[name="test"]').val()==0){
            if(confirm("Are you sure to send mail to selected Accounts")!=true){
                $(".btn").button('reset');
                $(".savetest").button('reset');
                $('#modal-BulkMail').modal('hide');
                return false;
            }
        }
        $('#form-download').find('[name="sendMail"]').val(1);
        var formData = new FormData($('#BulkMail-form')[0]);
        var poData = $(document.forms['form-download']).serializeArray();
        for (var i=0; i<poData.length; i++){
            formData.append(poData[i].name, poData[i].value);
        }
        var url = baseurl + "/customers_rates/{{$id}}/process_download";
        $.ajax({
            url: url,  //Server script to process data
            type: 'POST',
            dataType: 'json',
            success: function (response) {
                if(response.status =='success'){
                    toastr.success(response.message, "Success", toastr_opts);
                    $(".save").button('reset');
                    $(".savetest").button('reset');
                    $('#modal-BulkMail').modal('hide');
                    reloadJobsDrodown(0);
                }else{
                    toastr.error(response.message, "Error", toastr_opts);
                    $(".save").button('reset');
                    $(".savetest").button('reset');
                }
                $('#form-download').find('[name="sendMail"]').val(0);
            },
            // Form data
            data: formData,
            //Options to tell jQuery not to process data or worry about content-type.
            cache: false,
            contentType: false,
            processData: false
        });
    });

    $('#modal-BulkMail').on('shown.bs.modal', function(event){
        var modal = $(this);
        show_summernote(modal.find('.message'),editor_options);
    });

    $('#modal-BulkMail').on('hidden.bs.modal', function(event){
        var modal = $(this);
        modal.find('.message').show();
    });

    function editor_reset(data){
        var modal = $("#modal-BulkMail");
        modal.find('.message').show();
        if(!Array.isArray(data)){
            var EmailTemplate = data['EmailTemplate'];
            modal.find('[name="subject"]').val(EmailTemplate.Subject);
            modal.find('.message').val(EmailTemplate.TemplateBody);
        }else{
            modal.find('[name="subject"]').val('');
            modal.find('.message').val('');
        }
        show_summernote(modal.find('.message'),editor_options);
     }

    $("#test").click(function(e){
        e.preventDefault();
        $("#BulkMail-form").find('[name="test"]').val(1);
        $('#TestMail-form').find('[name="EmailAddress"]').val('');
        $('#modal-TestMail').modal({show: true});
    });
    $("#bull-email-account").click(function(e){
        $("#BulkMail-form").find('[name="test"]').val(0);
    });
    $('.lead').click(function(e){
        e.preventDefault();
        var email = $('#TestMail-form').find('[name="EmailAddress"]').val();
        var accontID = $('#TestMail-form').find('[name="accountID"]').val();
        if(email==''){
            toastr.error('Email field should not empty.', "Error", toastr_opts);
            $(".lead").button('reset');
            return false;
        }else if(accontID==''){
            toastr.error('Please select sample account from dropdown', "Error", toastr_opts);
            $(".lead").button('reset');
            return false;
        }
        $('#BulkMail-form').find('[name="testEmail"]').val(email);
        $('#BulkMail-form').find('[name="SelectedIDs"]').val(accontID);
        $("#BulkMail-form").submit();
        $('#modal-TestMail').modal('hide');

    });

    $('#modal-TestMail').on('hidden.bs.modal', function(event){
        var modal = $(this);
        modal.find('[name="test"]').val(0);
    });
    $('#form-download [name="Format"]').change(function(e) {
        if($(this).val() == '{{RateSheetFormate::RATESHEET_FORMAT_RATESHEET}}'){
            $('.emailsend.btn').removeClass('hidden')
            $('.effective').addClass('hidden');
            $('.CustomDate').addClass('hidden');
        }else{
            $('.emailsend.btn').addClass('hidden');
            $('.effective').removeClass('hidden');
            $('.CustomDate').removeClass('hidden');
        }

    });
    $('#fileformat').trigger('change');


    $("#fileeffective").on("change", function() {
        if($(this).val() == "CustomDate") {
            $(".DateFilter").show();
        } else {
            $(".DateFilter").hide();
        }
    });
});
function initCustomerGrid(tableID,OwnerFl){
var OwnerFilter;
    if(typeof OwnerFl != 'undefined'){
        OwnerFilter = OwnerFl ;
    }else{
        OwnerFilter = 0 ;
        if($("[name=account_owners]") != 'undefined')
            OwnerFilter = $("[name=account_owners]").val();
    }
var data_table_new = $("#"+tableID).dataTable({
        "bDestroy": true, // Destroy when resubmit form
        "sDom": "<'row'<'col-xs-12 border_left'f>r>t",
        "bProcessing": false,
        "bServerSide": false,
        "bPaginate": false,
        "fnServerParams": function(aoData) {
            aoData.push({"name": "OwnerFilter", "value": OwnerFilter});
        },
        "sAjaxSource": baseurl + "/customers_rates/{{$id}}/search_customer_grid",
        "aoColumns":
                    [
                        {"bSortable": false, //RateID
                            mRender: function(id, type, full) {
                                return '<div class="checkbox "><input type="checkbox" name="customer[]" value="' + id + '" class="rowcheckbox" ></div>';
                            }
                        },
                        {}
                    ],

        "fnDrawCallback": function() {
            $("#selectallcust").click(function(ev) {
                var is_checked = $(this).is(':checked');
                $('#'+tableID+' tbody tr').each(function(i, el) {
                    if (is_checked) {
                        $(this).find('.rowcheckbox').prop("checked", true);
                        $(this).addClass('selected');
                    } else {
                        $(this).find('.rowcheckbox').prop("checked", false);
                        $(this).removeClass('selected');
                    }
                });
            });
             $(".dataTables_wrapper select").select2({
                minimumResultsForSearch: -1
            });
        }

    });
    $('#'+tableID+' tbody').on('click', 'tr', function() {
        $(this).toggleClass('selected');
        if ($(this).hasClass('selected')) {
            $(this).find('.rowcheckbox').prop("checked", true);
        } else {
            $(this).find('.rowcheckbox').prop("checked", false);
        }
    });

    if(typeof OwnerFl == 'undefined'){

        $('#'+tableID).parents('div.dataTables_wrapper').first().hide();
        $('.my_account_'+tableID).hide()
    }
}

</script>
@stop

@section('footer_ext')
    @parent
    @include('accounts.bulk_email')

<!--    <div class="modal fade" id="modal-BulkMail">
        <div class="modal-dialog" style="width: 80%;">
            <div class="modal-content">
                <form id="BulkMail-form" method="post" action="" enctype="multipart/form-data">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Send Email</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-Group">
                                <br />
                                <label for="field-1" class="col-sm-2 control-label">Email Template Privacy</label>
                                <div class="col-sm-2">
                                    {{Form::select('email_template_privacy',$privacy,'',array("class"=>"select2 small"))}}
                                </div>
                                {{--<label for="field-1" class="control-label col-sm-1">Template Type</label>
                                <div class="col-sm-2">
                                    {{Form::select('Type',$type,'',array("class"=>"selectboxit"))}}
                                </div>--}}
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-Group">
                                <br />
                                <label for="field-1" class="col-sm-2 control-label">Email Template</label>
                                <div class="col-sm-4">
                                    {{Form::select('email_template',$emailTemplates,'',array("class"=>"select2 small"))}}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-Group">
                                <br />
                                <label for="field-1" class="col-sm-2 control-label">Subject</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" id="subject" name="subject" />
                                    <input type="hidden" name="SelectedIDs" />
                                    <input type="hidden" name="test" value="0" />
                                    <input type="hidden" name="testEmail" value="" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-Group">
                                <br />
                                <label for="field-1" class="col-sm-2 control-label">Message</label>
                                <div class="col-sm-10">
                                    <textarea class="form-control message" rows="18" name="message"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-Group">
                                <br />
                                <label for="field-1" class="col-sm-2 control-label">Template Option</label>
                                <div class="col-sm-4">
                                    {{Form::select('template_option',$templateoption,'',array("class"=>"select2 small"))}}
                                </div>
                            </div>
                        </div>
                        <div id="templatename" class="row hidden">
                            <div class="form-Group">
                                <br />
                                <label for="field-5" class="col-sm-2 control-label">New Template Name</label>
                                <div class="col-sm-4">
                                    <input type="text" name="template_name" class="form-control" id="field-5" placeholder="">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button id="bull-email-account" type="submit"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                            <i class="entypo-floppy"></i>
                            Send
                        </button>
                        <button id="test"  class="btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                            <i class="entypo-floppy"></i>
                            Send Test mail
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

    <div class="modal fade" id="modal-TestMail">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="TestMail-form" method="post" action="">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Test Mail Options</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-Group">
                                <br />
                                <label for="field-1" class="col-sm-3 control-label">Email Address</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" name="EmailAddress" />
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-Group">
                                <br />
                                <label for="field-1" class="col-sm-3 control-label">Sample Account</label>
                                <div class="col-sm-4">
                                    {{Form::select('accountID',$accounts,'',array("class"=>"select2"))}}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit"  class="lead btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                            <i class="entypo-floppy"></i>
                            Send
                        </button>
                        <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                            <i class="entypo-cancel"></i>
                            Close
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div> -->
    @stop