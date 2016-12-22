@extends('layout.main')
@section('content')
<br />
<div class="row">
<!------/*Recent Due Rate Sheet*/---->
    <?php if( User::is_admin() ){ ?>
    <div class="col-sm-6">
        <?php
        /* Recent Due Rate Sheet */
        //$TotalDueCustomer = $dashboardData['data']['TotalDueCustomer'];//VendorRate::getRecentDueVendorRates();
        //$TotalDueVendor = $dashboardData['data']['TotalDueVendor'];
        ?>
            <div class="panel panel-primary panel-table">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h3>Recent Due Rate Sheet</h3>
                        <span>Rate sheets due in next 2 days</span>
                    </div> 

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        <a href="#" data-rel="reload"><i class="entypo-arrows-ccw"></i></a>
                        <a href="#" data-rel="close"><i class="entypo-cancel"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <table id="duesheets" class="table table-responsive">
                        <thead>
                        <tr>
                            <th  colspan="3">Customer</th>
                            <th colspan="3">Vendor</th>
                        </tr>
                        <tr>
                            <th>Yesterday</th>
                            <th >Today</th>
                            <th >Tommorow</th>
                            <th>Yesterday</th>
                            <th >Today</th>
                            <th >Tommorow</th>
                        </tr>
                        </thead>

                        <tbody>
                        <tr>

                        </tr>
                        <tr>
                            <td colspan="3">
                                @if(User::checkCategoryPermission('Account','View'))
                                    <a href="{{URL::to('accounts/due_ratesheet?accounttype='.AccountApproval::CUSTOMER)}}" class="btn btn-primary text-right">View All</a>
                                @endif
                            </td>
                            <td colspan="3">
                                @if(User::checkCategoryPermission('Account','View'))
                                    <a href="{{URL::to('accounts/due_ratesheet?accounttype='.AccountApproval::VENDOR)}}" class="btn btn-primary text-right">View All</a>
                                @endif
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
    </div>
    <?php }
    ?>
    <?php if(User::checkCategoryPermission('Leads','View')){?>
        <div class="col-sm-6">
                <div class="panel panel-primary panel-table">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <h3>Recent Leads</h3>
                            <span>Recently Added Leads</span>
                        </div>

                        <div class="panel-options">
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                            <a href="#" data-rel="reload"><i class="entypo-arrows-ccw"></i></a>
                            <a href="#" data-rel="close"><i class="entypo-cancel"></i></a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <table id="leads" class="table table-responsive">
                            <thead>
                            <tr>
                                <th >Lead Name</th>
                                <th >Phone</th>
                                <th >Email</th>
                                <th >Created By</th>
                                <th >Created</th>
                            </tr>
                            </thead>

                            <tbody>
                            </tbody>
                        </table>
                        <div class="text-right">
                            <a href="{{URL::to('/leads')}}" class="btn btn-primary text-right">View All</a>
                        </div>
                    </div>
                </div>
        </div>
        <?php }
        ?>
    <div class="clear"></div>
    <div class="col-sm-6">
        <div class="panel panel-primary panel-table">
            <div class="panel-heading">
                <div class="panel-title">
                    <h3>Jobs (Pending Jobs:)</h3>
                    <span>Jobs logged by me </span>
                </div>

                <div class="panel-options">
                     <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    <a href="#" data-rel="reload"><i class="entypo-arrows-ccw"></i></a>
                    <a href="#" data-rel="close"><i class="entypo-cancel"></i></a>
                </div>
            </div>
            <div class="panel-body">
                <table id="jobs" class="table table-responsive">
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Created By</th>
                            <th class="text-center">Created Time</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                @if(User::checkCategoryPermission('Jobs','View'))
                <div class="text-right">
                    <a href="{{URL::to('/jobs')}}" class="btn btn-primary text-right">View All</a>
                </div>
                @endif
            </div>
        </div>

    </div>
    <div class="col-sm-6">
            <div class="panel panel-primary panel-table">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h3>Last 7 Days Processed Files</h3>
                        <span>Jobs Processed</span>
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        <a href="#" data-rel="reload"><i class="entypo-arrows-ccw"></i></a>
                        <a href="#" data-rel="close"><i class="entypo-cancel"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <table id="processedFile" class="table table-responsive">
                        <thead>
                        <tr>
                            <th>Job Title</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Created By</th>
                            <th class="text-center">Created Time</th>
                        </tr>
                        </thead>

                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
    </div>
    <div class="clear"></div>
    <!---Recent Accounts-->
    <?php if(User::checkCategoryPermission('Account','View')){?>
    <div class="col-sm-6">
            <div class="panel panel-primary panel-table">
                <div class="panel-heading">
                    <div class="panel-title">
                        <h3>Recent Accounts</h3>
                        <span>Recently Added Accounts</span>
                    </div>

                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                        <a href="#" data-rel="reload"><i class="entypo-arrows-ccw"></i></a>
                        <a href="#" data-rel="close"><i class="entypo-cancel"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <table id="accounts" class="table table-responsive">
                        <thead>
                        <tr>
                            <th >Account Name</th>
                            <th >Phone</th>
                            <th >Email</th>
                            <th >Created By</th>
                            <th >Created</th>
                        </tr>
                        </thead>

                        <tbody>
                        </tbody>
                    </table>
                    <div class="text-right">
                        <a href="{{URL::to('/accounts')}}" class="btn btn-primary text-right">View All</a>
                    </div>
                </div>
            </div>
    </div>
    <?php }
    ?>


</div>
<script>

    $(document).ready(function(){
        var load = {
            duesheet: function(){
                var table = $('#duesheets');
                loadingUnload(table,1);
                var url = baseurl+'/dashboard/ajax_get_recent_due_sheets';
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        var TotalDueCustomerarray = response.TotalDueCustomerarray;
                        var TotalDueCustomerarray = response.TotalDueVendorarray;
                        html = '';
                        table.find('tbody>tr').eq(0).html('');
                        if(TotalDueCustomerarray.length > 0){
                            $.each(TotalDueCustomerarray,function(i,el){
                                html+=el;
                            });
                        }else{
                            html = '<td colspan="3">No Records found.</td>';
                        }
                        if(TotalDueCustomerarray.length > 0){
                            $.each(TotalDueCustomerarray,function(i,el){
                                html+=el;
                            });
                        }else{
                            html += '<td colspan="3">No Records found.</td>';
                        }
                        table.find('tbody>tr').eq(0).html(html);
                        loadingUnload(table,0)
                    },
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            },
            recentleads:function(){
                var table = $('#leads');
                loadingUnload(table,1);
                var url = baseurl+'/dashboard/ajax_get_recent_leads';
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        var leads = response.leads;
                        html = '';
                        table.find('tbody').html('');
                        if(leads.length > 0){
                            for (i = 0; i < leads.length; i++) {
                                var url = leads[i]["Accounturl"];
                                var AccountName = leads[i]["AccountName"];
                                html +='<tr>';
                                html +='  <td><a href="'+url+'">'+AccountName+'</a></td>';
                                html +='      <td>'+leads[i]["Phone"]+'</td>';
                                html +='      <td>'+leads[i]["Email"]+'</td>';
                                html +='      <td>'+leads[i]["created_by"]+'</td>';
                                html +='      <td>'+leads[i]["daydiff"]+'</td>';
                                html +='</tr>';
                            }
                        }else{
                            html = '<td colspan="3">No Records found.</td>';
                        }
                        table.find('tbody').html(html);
                        loadingUnload(table,0);
                    },
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            },
            jobs:function(){
                var table = $('#jobs');
                loadingUnload(table,1);
                var url = baseurl+'/dashboard/ajax_get_jobs';
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        var jobs = response.Jobs;
                        html = '';
                        table.parents('.panel-primary').find('.panel-title h3').html('Jobs (Pending Jobs:'+response.JobsCount[0]['totalpending']+')');
                        table.find('tbody').html('');
                        if(jobs.length > 0){
                            for (i = 0; i < jobs.length; i++) {
                                var j_class ="";
                                if(jobs[i]["Status"] == 'Success')        { j_class=  'progress-bar-success'; }
                                else if(jobs[i]["Status"] == 'Failed')       { j_class=  'progress-bar-danger'; }
                                else if(jobs[i]["Status"] == 'In Progress')   { j_class=  'progress-bar-warning'; }
                                else if(jobs[i]["Status"] == 'Completed')     { j_class=  'progress-bar-info'; }
                                else if(jobs[i]["Status"] == 'Pending')       { j_class=  'progress-bar-important'; }
                                html +='<tr>';
                                html +='      <td><a href="javascript:;" onclick="return showJobAjaxModal('+jobs[i]["JobID"]+');">'+jobs[i]["Title"]+'</a></td>';
                                html +='      <td>'+jobs[i]["Status"]+'</td>';
                                html +='      <td>'+jobs[i]["CreatedBy"]+'</td>';
                                html +='      <td>'+jobs[i]["daydiff"]+'</td>';
                                html +='</tr>';
                            }
                        }else{
                            html = '<td colspan="3">No Records found.</td>';
                        }
                        table.find('tbody').html(html);
                        loadingUnload(table,0);
                    },
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            },
            jobFiles:function(){
                var table = $('#processedFile');
                loadingUnload(table,1);
                var url = baseurl+'/dashboard/ajax_get_processed_files';
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        var jobFiles = response.jobFiles;
                        html = '';
                        table.find('tbody').html('');
                        if(jobFiles.length > 0){
                            for (i = 0; i < jobFiles.length; i++) {
                                var j_class ="";
                                if(jobFiles[i]["Status"] == 'Success')        { j_class=  'progress-bar-success'; }
                                else if(jobFiles[i]["Status"] == 'Failed')       { j_class=  'progress-bar-danger'; }
                                else if(jobFiles[i]["Status"] == 'In Progress')   { j_class=  'progress-bar-warning'; }
                                else if(jobFiles[i]["Status"] == 'Completed')     { j_class=  'progress-bar-info'; }
                                else if(jobFiles[i]["Status"] == 'Pending')       { j_class=  'progress-bar-important'; }
                                html +='<tr>';
                                html +='      <td><a href="javascript:;" onclick="return showJobAjaxModal('+jobFiles[i]["JobID"]+');">'+jobFiles[i]["Title"]+'</a></td>';
                                html +='      <td>'+jobFiles[i]["Status"]+'</td>';
                                html +='      <td>'+jobFiles[i]["CreatedBy"]+'</td>';
                                html +='      <td>'+jobFiles[i]["daydiff"]+'</td>';
                                html +='</tr>';
                            }
                        }else{
                            html = '<td colspan="3">No Records found.</td>';
                        }
                        table.find('tbody').html(html);
                        loadingUnload(table,0);
                    },
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            },
            accounts:function(){
                var table = $('#accounts');
                loadingUnload(table,1);
                var url = baseurl+'/dashboard/ajax_get_recent_accounts';
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        var accounts = response.accounts;
                        html = '';
                        table.find('tbody').html('');
                        if(accounts.length > 0){
                            for (i = 0; i < accounts.length; i++) {
                                var url = accounts[i]["Accounturl"];
                                var AccountName = accounts[i]["AccountName"];
                                html +='<tr>';
                                html +='  <td><a href="'+url+'">'+AccountName+'</a></td>';
                                html +='      <td>'+accounts[i]["Phone"]+'</td>';
                                html +='      <td>'+accounts[i]["Email"]+'</td>';
                                html +='      <td>'+accounts[i]["created_by"]+'</td>';
                                html +='      <td>'+accounts[i]["daydiff"]+'</td>';
                                html +='</tr>';
                            }
                        }else{
                            html = '<td colspan="3">No Records found.</td>';
                        }
                        table.find('tbody').html(html);
                        loadingUnload(table,0);
                    },
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                });
            }

        };

        load.duesheet();
        load.recentleads();
        load.jobs();
        load.jobFiles();
        load.accounts();
        //load.missingAccounts();

        $('body').on('click', '.panel > .panel-heading > .panel-options > a[data-rel="reload"]', function(e){
            e.preventDefault();
            var id = $(this).parents('.panel-primary').find('table').attr('id');
            if(id=='duesheets'){
                load.duesheet();
            }else if(id=='leads'){
                load.recentleads();
            }else if(id=='jobs'){
                load.jobs();
            }else if(id=='processedFile'){
                load.jobFiles();
            }else if(id=='accounts'){
                load.accounts();
            }else if(id=='missingAccounts'){
                load.missingAccounts();
            }
        });

        function loadingUnload(table,bit){
            var panel = jQuery(table).closest('.panel');
            if(bit==1){
                blockUI(panel);
                panel.addClass('reloading');
            }else{
                unblockUI(panel)
                panel.removeClass('reloading');
            }
        }
    });

</script>
@stop