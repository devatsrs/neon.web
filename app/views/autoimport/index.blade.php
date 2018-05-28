@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form novalidate class="form-horizontal form-groups-bordered validate" method="get" id="ratetable_filter">
                <div class="form-group">
                    <label for="Search" class="control-label">Search</label>
                    <input class="form-control" name="Search" id="Search"  type="text" >
                </div>
                <div class="form-group">
                    <label class="control-label">Account</label>
                    {{ Form::select('AccountID',$accounts,Input::get('AccountID'), array("class"=>"select2","id"=>"bulk_AccountID",'allowClear'=>'true')) }}
                </div>
                <div class="form-group">
                    <label class="control-label" for="field-1">Status</label>
                    {{ Form::select('jobStatus', $jobStatus, '', array("class"=>"select2","data-type"=>"trunk")) }}
                </div>

                <div class="form-group">
                    <label for="Search" class="control-label">Type</label>
                    {{Form::select('jobType', $jobTypes, '' ,array("class"=>"form-control select2"))}}
                </div>
                <div class="form-group">
                    <br/>
                    <button type="submit" class="btn btn-primary btn-md btn-icon icon-left">
                        <i class="entypo-search"></i>
                        Search
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop


@section('content')
<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('/dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Auto Import </strong>
    </li>
</ol>
<h3>Auto Import</h3>
<p style="text-align: right;">
@if(User::checkCategoryPermission('AutoRateImport','View'))
    <a href="{{URL::to('/auto_rate_import/import_inbox_setting')}}"  class="btn btn-primary ">
        Import Inbox Settings
    </a>
    <a href="{{URL::to('/auto_rate_import/account_setting')}}"  class="btn btn-primary ">
        Vendor Settings
    </a>
    <a href="{{URL::to('/auto_rate_import/ratetable_setting')}}"  class="btn btn-primary ">
        Rate Table Settings
    </a>
@endif

</p>

<div class="cler row">
    <div class="col-md-12">
        <form role="form" id="form1" method="post" class="form-horizontal form-groups-bordered validate" novalidate>
            <div class="form-group">
                        <div class="col-md-12">
                            <table class="table table-bordered datatable" id="table-4">
                                <thead>
                                    <tr>
                                        <th >Type</th>
                                        <th >Header</th>
                                        <th >Created</th>
                                        <th >Job ID</th>
                                        <th >Status</th>
                                         <th >Action</th>
                                    </tr>
                                </thead>
                                <tbody>


                                </tbody>
                            </table>
                        </div>
                    </div>
        </form>
    </div>
</div>
<script type="text/javascript">
jQuery(document).ready(function($) {

    $('#filter-button-toggle').show();

    var $searchFilter = {};
    var update_new_url;
        $searchFilter.AccountID = $('#ratetable_filter [name="AccountID"]').val();
        $searchFilter.jobStatus = $("#ratetable_filter [name='jobStatus']").val();
        $searchFilter.jobType = $("#ratetable_filter [name='jobType']").val();
		$searchFilter.Search = $('#ratetable_filter [name="Search"]').val();
        $searchFilter.SettingType = 1;
        data_table = $("#table-4").dataTable({
            "bDestroy": true,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": baseurl + "/auto_rate_import/autoimport/ajax_datagrid/1",
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "oTableTools": {},
            "aaSorting": [[2, "desc"]],
            "fnServerParams": function(aoData) {
                aoData.push({"name":"AccountID","value":$searchFilter.AccountID}, {"name":"jobStatus","value":$searchFilter.jobStatus},{"name":"jobType","value":$searchFilter.jobType},{"name":"TypePKID","value":$searchFilter.TypePKID},{"name":"Search","value":$searchFilter.Search});
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name":"jobStatus","value":$searchFilter.jobStatus},{"name":"jobType","value":$searchFilter.jobType},{"name":"TypePKID","value":$searchFilter.TypePKID},{"name":"Search","value":$searchFilter.Search},{"name":"Export","value":1});
            },
            "fnRowCallback": function(nRow, aData) {
                $(nRow).attr("id", "host_row_" + aData[2]);
            },
            "aoColumns":
                    [
                        {},
                        {
                            mRender: function(id, type, full) {
                                var array = id.split("<br>");
                                var subject = array[0];
                                var action = "";
                                action +='<a class="add-new-account-setting" id='+full[5]+' style="margin-left:3px" href="javascript:;" >' +subject+'</i></a>';
                                action +='<br>&nbsp;From : '+array[1]+'&nbsp;('+array[2]+')';
                                if(full[0]=='Vendor Rate Upload'){
                                    action +='<br>&nbsp;Account : <b>'+full[7]+'</b>';
                                }else if(full[0]=='Rate Table Upload'){
                                    action +='<br>&nbsp;Rate Table Name : <b>'+full[7]+'</b>';
                                }else{
                                    action +='<br>&nbsp;';
                                }

                                return action;
                            }
                        },
                        {
                            mRender: function(id, type, full) {
//                                return time_ago(id);
                                return id;
                            }
                        },
                        {
                            mRender: function(id, type, full) {
                                var jobId = id > 0 ? id : ' ';
                                action ='<a onclick=" return showJobAjaxModal(' + id + ');" href="javascript:;" style="margin-left:3px" href="{{URL::to('/jobs/')}}" >' +jobId+'</i></a>';
                                return action;
                            }
                        },
                        {},
                        {
                            mRender: function(id, type, full) {
                                var Status = full[4].toLowerCase();
                                if( Status == 'failed'){
                                    action = ' <button data-id="'+ full[3] +'" title="Job Restart" class="job_restart btn btn-primary btn-sm" type="button" data-loading-text="Loading...">' +
                                            '<i class="glyphicon glyphicon-repeat"></i>' +
                                            '</button>';

                                }else if( Status == 'not match' ){
                                    action = ' <button data-id="'+ full[5] +'" title="Recheck Mail" class="job_recheck btn btn-primary btn-sm" type="button" data-loading-text="Loading...">' +
                                            '<i class="glyphicon glyphicon-repeat"></i>' +
                                            '</button>';//
                                }else{
                                    action= '';
                                }
                                return action;

                            }
                        },
                    ],
                    "oTableTools":
                    {
                        "aButtons": [
                            {
                                "sExtends": "download",
                                "sButtonText": "EXCEL",
                                "sUrl": baseurl + "/auto_rate_import/autoimport/ajax_datagrid/xlsx",
                                sButtonClass: "save-collection btn-sm"
                            },
                            {
                                "sExtends": "download",
                                "sButtonText": "CSV",
                                "sUrl": baseurl + "/auto_rate_import/autoimport/ajax_datagrid/csv",
                                sButtonClass: "save-collection btn-sm"
                            }
                        ]
                    }, 
            "fnDrawCallback": function() {
                $(".dataTables_wrapper select").select2({
                    minimumResultsForSearch: -1
                });

                $(".btn.delete").click(function(e) {
                    e.preventDefault();
                    response = confirm('Are you sure?');
                    //redirect = ($(this).attr("data-redirect") == 'undefined') ? "{{URL::to('/rate_tables')}}" : $(this).attr("data-redirect");
                    if (response) {
                        $(this).text('Loading..');
                        $('#table-4_processing').css('visibility','visible');
                        $.ajax({
                            url: $(this).attr("href"),
                            type: 'POST',
                            dataType: 'json',
                            beforeSend: function(){
                            //    $(this).text('Loading..');
                            },
                            success: function(response) {
                                if (response.status == 'success') {
                                    toastr.success(response.message, "Success", toastr_opts);
                                    data_table.fnFilter('', 0);
                                } else {
                                    toastr.error(response.message, "Error", toastr_opts);
                                    data_table.fnFilter('', 0);
                                }
                                $('#table-4_processing').css('visibility','hidden');
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
                $(".btn.change_status").click(function(e) {
                    //redirect = ($(this).attr("data-redirect") == 'undefined') ? "{{URL::to('/rate_tables')}}" : $(this).attr("data-redirect");
                     $(this).button('loading');
                        $.ajax({
                            url: $(this).attr("href"),
                            type: 'POST',
                            dataType: 'json',
                            success: function(response) {
                                $(this).button('reset');
                                if (response.status == 'success') {
                                    toastr.success(response.message, "Success", toastr_opts);
                                    data_table.fnFilter('', 0);
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
                    return false;
                });
            }
        });

        $("#ratetable_filter").submit(function(e) {
            e.preventDefault();
            $searchFilter.AccountID = $('#ratetable_filter [name="AccountID"]').val();
            $searchFilter.jobStatus = $("#ratetable_filter [name='jobStatus']").val();
            $searchFilter.jobType = $("#ratetable_filter [name='jobType']").val();
			$searchFilter.Search = $('#ratetable_filter [name="Search"]').val();
            data_table.fnFilter('', 0);
            return false;
         });
       /* $(".add-new-account-setting").click(function() {
            alert('hi');

             $("#modal-add-new-account-setting [name='AutoImportSettingID']").val('');
             $('#modal-add-new-account-setting').modal('show', {backdrop: 'static'});
         });*/
         $("#add-new-form").submit(function(ev){
            ev.preventDefault();
            update_new_url = baseurl + '/auto_rate_import/account_setting/store';
            submit_ajax(update_new_url,$("#add-new-form").serialize());
         });

    });


    //Restart a job
    $('table tbody').on('click','.job_restart',function(ev){
        result = confirm("Are you Sure?");
        if(result){
            id = $(this).attr('data-id');
            submit_ajax(baseurl+'/jobs/'+id + '/restart');
            data_table.fnFilter('', 0);
        }
    });
    $('table tbody').on('click','.job_recheck',function(ev){
        result = confirm("Are you Sure?");
        if(result){
            id = $(this).attr('data-id');
            submit_ajax(baseurl+'/auto_rate_import/autoimport/recheckmail/'+id );
            data_table.fnFilter('', 0);
        }
    });

    $('table tbody').on('click','.add-new-account-setting',function(){
            var emailId= $(this).attr("id");
            $.ajax({
                type: "POST",
                url: baseurl + '/auto_rate_import/autoimport/readmail/'+emailId,
                dataType: 'json',
                data: {
                    emailId: emailId
                },
                success: function(data)
                {
                    var edata = data.data;
                    var ele=$("#modal-add-new-account-setting");
                    ele.find('.modal-title').html('<b>'+edata.Subject+'<b> #'+edata.AutoImportID);
                    var cc = edata.CC;
                    cc = cc.length > 0 ? '<br>CC : '+cc : '';
                    $('.mail-date').html('To : '+edata.To+'<br>From : '+edata.From+ cc+'<br>'+time_ago(edata.MailDateTime)+' ('+edata.MailDateTime+')' );

                    var iFrame = $('<iframe class="embed-responsive-item" frameborder="0" allowfullscreen></iframe>');
                    $('.mail-text .mailbody').html(iFrame);
                    var iFrameDoc = iFrame[0].contentDocument || iFrame[0].contentWindow.document;
                    iFrameDoc.write(edata.Description);
                    iFrameDoc.close();

                    var Attachment = edata.Attachment;
                    if(Attachment!=""){
                        var attach = '';
                        try {
                            var attchment_obj = JSON.parse(Attachment);
                            $(".totAttach").html(attchment_obj.length);
                            $.each(attchment_obj, function (index, value) {
                                attach += '<div> <a href="'+ baseurl +'/auto_rate_import/autoimport/'+emailId+'/getAttachment/'+index+'">'+value.filename+'</a></div>';
                            });
                        }
                        catch(err) {
                            var attchment_array = Attachment.split(',');
                            $(".totAttach").html(attchment_array.length);
                            $.each(attchment_array, function (index, value) {
                                attach += '<div> <a href="javascript:void(0)">'+value+'</a></div>';
                            });
                        }
                        $('.attachmentList').html(attach);
                    }
                    $('#modal-add-new-account-setting').modal('show', {backdrop: 'static'});
                  //  $('#myModal').modal({show:true});
                }
            });
    });


    function time_ago(time) {

    switch (typeof time) {
        case 'number':
            break;
        case 'string':
            time = +new Date(time);
            break;
        case 'object':
            if (time.constructor === Date) time = time.getTime();
            break;
        default:
            time = +new Date();
    }
    var time_formats = [
        [60, 'seconds', 1], // 60
        [120, '1 minute ago', '1 minute from now'], // 60*2
        [3600, 'minutes', 60], // 60*60, 60
        [7200, '1 hour ago', '1 hour from now'], // 60*60*2
        [86400, 'hours', 3600], // 60*60*24, 60*60
        [172800, 'Yesterday', 'Tomorrow'], // 60*60*24*2
        [604800, 'days', 86400], // 60*60*24*7, 60*60*24
        [1209600, 'Last week', 'Next week'], // 60*60*24*7*4*2
        [2419200, 'weeks', 604800], // 60*60*24*7*4, 60*60*24*7
        [4838400, 'Last month', 'Next month'], // 60*60*24*7*4*2
        [29030400, 'months', 2419200], // 60*60*24*7*4*12, 60*60*24*7*4
        [58060800, 'Last year', 'Next year'], // 60*60*24*7*4*12*2
        [2903040000, 'years', 29030400], // 60*60*24*7*4*12*100, 60*60*24*7*4*12
        [5806080000, 'Last century', 'Next century'], // 60*60*24*7*4*12*100*2
        [58060800000, 'centuries', 2903040000] // 60*60*24*7*4*12*100*20, 60*60*24*7*4*12*100
    ];
    var seconds = (+new Date() - time) / 1000,
            token = 'ago',
            list_choice = 1;

    if (seconds == 0) {
        return 'Just now'
    }
    if (seconds < 0) {
        seconds = Math.abs(seconds);
        token = 'from now';
        list_choice = 2;
    }
    var i = 0,
            format;
    while (format = time_formats[i++])
        if (seconds < format[0]) {
            if (typeof format[2] == 'string')
                return format[list_choice];
            else
                return Math.floor(seconds / format[2]) + ' ' + format[1] + ' ' + token;
        }
    return time;
}

</script>
@include('includes.errors')
@include('includes.success')
@stop
@section('footer_ext')
@parent
<div class="modal fade" id="modal-add-new-account-setting">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="add-new-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class=" mail-env">


                    <div class="mail-body pull-left" style="width: 100%">
                        <div class="mail-header">
                            <div class="mail-title"></div>
                            <div class="clear mail-date"></div>
                        </div>

                        <div class="mail-text" ><div class="embed-responsive embed-responsive-4by3 mailbody" style="padding-bottom: 22%;padding-top: 22%;"></div></div>
                        <div class="mail-attachments last_data">
                            <h4><i class="entypo-attach"></i> Attachments (<span class="totAttach"></span>) </h4>
                            <div class="attachmentList"></div>
                        </div>
                    </div>



                </div>
                <div class="modal-footer">
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