@extends('layout.main')

@section('content')


<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Accounts</strong>
    </li>
</ol>
<h3>Accounts</h3>

@include('includes.errors')
@include('includes.success')

<p style="text-align: right;">
@if(User::checkCategoryPermission('Account','Add'))
    <a href="{{URL::to('accounts/create')}}" class="btn btn-primary ">
        <i class="entypo-plus"></i>
        Add New
    </a>
@endif    
</p>
<div class="row">
    <div class="col-md-12">
        <form id="account_filter" method=""  action="" class="form-horizontal form-groups-bordered validate" novalidate>
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        Filter
                    </div>
                    <div class="panel-options">
                        <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label for="field-1" class="col-sm-1 control-label">Account Name</label>
                        <div class="col-sm-2">
                            <input class="form-control" name="account_name"  type="text" >
                        </div>
                        <label for="field-1" class="col-sm-1 control-label">Account Number</label>
                        <div class="col-sm-2">
                            <input class="form-control" name="account_number" type="text"  >
                        </div>
                        <label class="col-sm-1 control-label">Contact Name</label>
                        <div class="col-sm-2">
                            <input class="form-control" name="contact_name" type="text" >
                        </div>
                        <label class="col-sm-1 control-label">Tag</label>
                        <div class="col-sm-2">
                            <input class="form-control tags" name="tag" type="text" >
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-1 control-label">Customer</label>
                        <div class="col-sm-1">
                            <p class="make-switch switch-small">
                                <input id="Customer_on_off" name="customer_on_off" type="checkbox" value="1" >
                            </p>
                        </div>
                        <label class="col-sm-1 control-label">Vendor</label>
                        <div class="col-sm-1">
                            <p class="make-switch switch-small">
                                <input id="Vendor_on_off" name="vendor_on_off" type="checkbox" value="1">
                            </p>
                        </div>
                        <label class="col-sm-1 control-label">Active</label>
                        <div class="col-sm-1">
                            <p class="make-switch switch-small">
                                <input id="account_active" name="account_active" type="checkbox" value="1" checked="checked">
                            </p>
                        </div>
                        <label class="col-sm-1 control-label">Status</label>
                        <div class="col-sm-2">
                            {{Form::select('verification_status',Account::$doc_status,Account::VERIFIED,array("class"=>"selectboxit"))}}
                        </div>
                        @if(User::is_admin())
                         <label for="field-1" class="col-sm-1 control-label">Account Owner</label>
                        <div class="col-sm-2">
                            {{Form::select('account_owners',$account_owners,Input::get('account_owners'),array("class"=>"select2"))}}
                        </div>
                        @endif
                    </div>
                    <p style="text-align: right;">
                        <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left">
                            <i class="entypo-search"></i>
                            Search
                        </button>
                    </p>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="clear"></div>
@if(User::checkCategoryPermission('Account','Email,Edit'))
<div class="row hidden dropdown">
    <div  class="col-md-12">
        <div class="input-group-btn pull-right" style="width:70px;">
            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="caret"></span></button>
            <ul class="dropdown-menu dropdown-menu-left" role="menu" style="background-color: #1f232a; border-color: #1f232a; margin-top:0px;">
                @if(User::checkCategoryPermission('Account','Email'))
                <li>
                    <a href="javascript:void(0)" class="sendemail">
                        <i class="entypo-mail"></i>
                        <span>Bulk Email</span>
                    </a>
                </li>
                @endif
                @if(User::checkCategoryPermission('Account','Edit'))
                <li>
                    <a href="javascript:void(0)" id="bulk-tags">
                        <i class="entypo-tag"></i>
                        <span>Bulk Tags</span>
                    </a>
                </li>
                @endif
                @if(User::checkCategoryPermission('Account','Email'))
                <li>
                    <a href="javascript:void(0)" id="bulk-Ratesheet">
                        <i class="entypo-mail"></i>
                        <span>Bulk Rate sheet Email</span>
                    </a>
                </li>
                @endif
            </ul>
        </div><!-- /btn-group -->
    </div>
    <div class="clear"></div>
</div>
@endif
<br>
<!--<p style="text-align: right;">
    <a href="javascript:void(0)" id="selectallbutton" class="btn btn-primary ">
        <i class="entypo-check"></i>
        <span>Select all found Accounts</span>
    </a>
</p>-->
<br />
<table class="table table-bordered datatable hidden" id="table-4">
    <thead>
    <tr>
        <th width="5%"><input type="checkbox" id="selectall" name="checkbox[]" class="" /></th>
        <th width="10%" >No.</th>
        <th width="15%" >Account Name</th>
        <th width="10%" >Name</th>
        <th width="10%">Phone</th>
        <th width="10%">OS</th>
        <th width="10%">Email</th>
        <th width="30%">Actions</th>
    </tr>
    </thead>
    <tbody>



    </tbody>
</table>

<script type="text/javascript">
    var $searchFilter = {};
    var checked = '';
    var view = 1;
    jQuery(document).ready(function ($) {
      

        //["tblAccount.Number",
        // "tblAccount.AccountName",
        // DB::raw("(tblUser.FirstName+' '+tblUser.LastName) as Ownername"),
        // "tblAccount.Phone",
        // "tblAccount.Email",
        // "tblAccount.AccountID",
        // "tblAccount.IsCustomer",
        // "tblAccount.IsVendor",
        // 'tblAccount.VerificationStatus']

        var varification_status = [{{Account::NOT_VERIFIED}},{{Account::VERIFIED}}];
        var varification_status_text = ["{{Account::$doc_status[Account::NOT_VERIFIED]}}","{{Account::$doc_status[Account::VERIFIED]}}"];

        $searchFilter.account_name = $("#account_filter [name='account_name']").val();
        $searchFilter.account_number = $("#account_filter [name='account_number']").val();
        $searchFilter.contact_name = $("#account_filter [name='contact_name']").val();
        $searchFilter.tag = $("#account_filter [name='tag']").val();
        $searchFilter.verification_status = $("#account_filter [name='verification_status']").val();
        $searchFilter.account_owners = $("#account_filter [name='account_owners']").val();
        $searchFilter.customer_on_off = $("#account_filter [name='customer_on_off']").prop("checked");
        $searchFilter.vendor_on_off = $("#account_filter [name='vendor_on_off']").prop("checked");
        $searchFilter.account_active = $("#account_filter [name='account_active']").prop("checked");

                data_table = $("#table-4").dataTable({

                    "bProcessing":true,
                    "bDestroy": true,
                    "bServerSide":true,
                    "sAjaxSource": baseurl + "/accounts/ajax_datagrid",
                    "iDisplayLength": '{{Config::get('app.pageSize')}}',
                    "sPaginationType": "bootstrap",
                    "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'change-view'><'export-data'T>f>r><'gridview'>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                    "aaSorting"   : [[2, 'asc']],
                      "fnServerParams": function(aoData) {
                        aoData.push({"name":"account_name","value":$searchFilter.account_name},{"name":"account_number","value":$searchFilter.account_number},{"name":"tag","value":$searchFilter.tag},{"name":"contact_name","value":$searchFilter.contact_name},{"name":"customer_on_off","value":$searchFilter.customer_on_off},{"name":"vendor_on_off","value":$searchFilter.vendor_on_off},{"name":"account_active","value":$searchFilter.account_active},{"name":"verification_status","value":$searchFilter.verification_status},{"name":"account_owners","value":$searchFilter.account_owners});
                        data_table_extra_params.length = 0;
                        data_table_extra_params.push({"name":"account_name","value":$searchFilter.account_name},{"name":"account_number","value":$searchFilter.account_number},{"name":"tag","value":$searchFilter.tag},{"name":"contact_name","value":$searchFilter.contact_name},{"name":"customer_on_off","value":$searchFilter.customer_on_off},{"name":"vendor_on_off","value":$searchFilter.vendor_on_off},{"name":"account_active","value":$searchFilter.account_active},{"name":"verification_status","value":$searchFilter.verification_status},{"name":"account_owners","value":$searchFilter.account_owners},{"name":"Export","value":1});
                    },
                    "aoColumns":
                    [
                        {"bSortable": false,
                            mRender: function(id, type, full) {
                                return '<div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + id + '" class="rowcheckbox" ></div>';
                            }
                        }, //0Checkbox
                        { "bSortable": true},
                        { "bSortable": true},
                        { "bSortable": true},
                        { "bSortable": true},
                        { "bSortable": true},
                        { "bSortable": true},
                        {
                            "bSortable": false,
                            mRender: function ( id, type, full ) {
                                var action , edit_ , show_ ;
                                action='';
                                edit_ = "{{ URL::to('accounts/{id}/edit')}}";
                                show_ = "{{ URL::to('accounts/{id}/show')}}";
                                customer_rate_ = "{{Url::to('/customers_rates/{id}')}}";
                                vendor_blocking_ = "{{Url::to('/vendor_rates/{id}')}}";

                                edit_ = edit_.replace( '{id}', full[0] );
                                show_ = show_.replace( '{id}', full[0] );
                                customer_rate_ = customer_rate_.replace( '{id}', full[0] );
                                vendor_blocking_ = vendor_blocking_.replace( '{id}', full[0] );
                                action = '';
                                <?php if(User::checkCategoryPermission('Account','Edit')){ ?>
                                action += '<a href="'+edit_+'" class="btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit </a>';
                                <?php } ?>
                                action += '&nbsp;<a href="'+show_+'" class="btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>View </a>';

                                /*full[6] == Customer verified
                                 full[7] == Vendor verified */
                                varification_url =  '{{ URL::to('accounts/{id}/change_verifiaction_status')}}/';
                                varification_url = varification_url.replace('{id}',full[0]);

                                NOT_VERIFIED = varification_url +'{{Account::NOT_VERIFIED}}';
                               
                                VERIFIED = varification_url + '{{Account::VERIFIED}}';

                                 
                                <?php if(User::checkCategoryPermission('Account','Edit')){ ?>
                                /* action += '<select name="varification_status" class="change_verification_status">';
                                 for(var i = 0; i < varification_status.length ; i++){
                                    var selected = "";
                                    if(full[9] == varification_status[i]){
                                        selected = "selected";
                                    }
                                    action += '<option data-id="'+full[0]+'" value="' + varification_status[i] + '" ' + selected   +'     >'+varification_status_text[i]+'</option>';
                                 }
                                 action += '</select>';*/
                                <?php } ?>

                                if(full[7]==1 && full[9]=='{{Account::VERIFIED}}'){
                                    <?php if(User::checkCategoryPermission('CustomersRates','View')){ ?>
                                        action += '&nbsp;<a href="'+customer_rate_+'" class="btn btn-warning btn-sm btn-icon icon-left"><i class="entypo-cancel"></i>Customer</a>';
                                    <?php } ?>
                                }

                                if(full[8]==1 && full[9]=='{{Account::VERIFIED}}'){
                                    <?php if(User::checkCategoryPermission('VendorRates','View')){ ?>
                                        action += '&nbsp;<a href="'+vendor_blocking_+'" class="btn btn-info btn-sm btn-icon icon-left"><i class="entypo-cancel"></i>Vendor</a>';
                                    <?php } ?>
                                }
                                action +='<input type="hidden" name="accountid" value="'+full[0]+'"/>';
                                action +='<input type="hidden" name="address1" value="'+full[10]+'"/>';
                                action +='<input type="hidden" name="address2" value="'+full[11]+'"/>';
                                action +='<input type="hidden" name="address3" value="'+full[12]+'"/>';
                                action +='<input type="hidden" name="city" value="'+full[13]+'"/>';
                                action +='<input type="hidden" name="country" value="'+full[14]+'"/>';
								action +='<input type="hidden" name="PostCode" value="'+full[15]+'"/>';
                                action +='<input type="hidden" name="picture" value="'+full[16]+'"/>';
                                return action;
                            }
                        },
                    ],
            "oTableTools": {
            "aButtons": [
                {
                    "sExtends": "download",
                    "sButtonText": "Export Data",
                    "sUrl": baseurl + "/accounts/ajax_datagrid", //baseurl + "/generate_xls.php",
                    sButtonClass: "save-collection"
                }
            ]
        },
        "fnDrawCallback": function() {
             $(".dataTables_wrapper select").select2({
                minimumResultsForSearch: -1
            });

            $(".dropdown").removeClass("hidden");
            var toggle = '<header>';
            toggle += '   <span class="list-style-buttons">';

            if(view==1){
                var activeurl = baseurl + '/assets/images/grid-view-active.png';
                var desctiveurl = baseurl + '/assets/images/list-view.png';
                toggle += '      <a class="switcher active" id="gridview" href="javascript:void(0)"><img alt="Grid" src="'+activeurl+'"></a>';
                toggle += '      <a class="switcher" id="listview" href="javascript:void(0)"><img alt="List" src="'+desctiveurl+'"></a>';
            }else{
                var activeurl = baseurl + '/assets/images/list-view-active.png';
                var desctiveurl = baseurl + '/assets/images/grid-view.png';
                toggle += '      <a class="switcher" id="gridview" href="javascript:void(0)"><img alt="Grid" src="'+desctiveurl+'"></a>';
                toggle += '      <a class="switcher active" id="listview" href="javascript:void(0)"><img alt="List" src="'+activeurl+'"></a>';
            }
            toggle += '   </span>';
            toggle += '</header>';
            $('.change-view').html(toggle);
            var html = '<ul class="clearfix grid col-md-12">';
            var checkClass = '';
            if($(this).parents('.page-container').hasClass('sidebar-collapsed')) {
                checkClass = '1';
            }else{
                checkClass = '0';
            }
            $('#table-4 tbody tr').each(function (i, el) {
                var childrens = $(this).children();
                if(childrens.eq(0).hasClass('dataTables_empty')){
                    return true;
                }
                var temp = childrens.eq(7).clone();
                $(temp).find('a').each(function () {
                    $(this).find('i').remove();
                    $(this).removeClass('btn btn-icon icon-left');
                    $(this).addClass('label');
                    $(this).addClass('padding-3');
                });
                $(temp).find('.select2-container').remove();
                $(temp).find('select[name="varification_status"]').remove();
                var address1 = $(temp).find('input[name="address1"]').val();
                var address2 = $(temp).find('input[name="address2"]').val();
                var address3 = $(temp).find('input[name="address3"]').val();
                var city = $(temp).find('input[name="city"]').val();
                var country = $(temp).find('input[name="country"]').val();
				var PostCode = $(temp).find('input[name="PostCode"]').val();
				
				
                address1 = (address1=='null'||address1==''?'':''+address1+'<br>');
                address2 = (address2=='null'||address2==''?'':address2+'<br>');
                address3 = (address3=='null'||address3==''?'':address3+'<br>');
                city 	 = (city=='null'||city==''?'':city+'<br>');
				PostCode = (PostCode=='null'||PostCode==''?'':PostCode+'<br>');
                country  = (country=='null'||country==''?'':country);
                var url  = baseurl + '/assets/images/placeholder-male.gif';
                var select = '';
                if (checked != '') {
                    select = ' selected';
                }
				
				//col-xl-2 col-md-4 col-sm-6 col-xsm-12 col-lg-3
				
                if(checkClass=='1')
				{
                    html += '<li class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-xsm-12">';
                }
				else
				{
                    html += '<li class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-xsm-12">';
                }
                html += '  <div class="box clearfix ' + select + '">';
               // html += '  <div class="col-sm-4 header padding-0"> <img class="thumb" alt="default thumb" height="50" width="50" src="' + url + '"></div>';
                html += '  <div class="col-sm-12 header padding-left-1">  <span class="head">' + childrens.eq(2).text() + '</span><br>';
                html += '  <span class="meta complete_name">' + childrens.eq(3).text() + '</span></div>';
                html += '  <div class="col-sm-6 padding-0">';
                html += '  <div class="block">';
                html += '     <div class="meta">Email</div>';
                html += '     <div><a href="javascript:void(0)" class="sendemail">' + childrens.eq(6).text() + '</a></div>';
                html += '  </div>';
                html += '  <div class="cellNo">';
                html += '     <div class="meta">Phone</div>';
                html += '     <div><a href="tel:' + childrens.eq(4).text() + '">' + childrens.eq(4).text() + '</a></div>';
                html += '  </div>';
                html += '  <div>';
                html += '     <div class="meta">Outstanding</div>';
                html += '     <div>' + childrens.eq(5).text() + '</div>';
                html += '  </div>';
                html += '  </div>';
                html += '  <div class="col-sm-6 padding-0">';
                html += '  <div class="block">';
                html += '     <div class="meta">Address</div>';
                html += '     <div class="address account-address">' + address1 + ''+address2+''+address3+''+city+''+PostCode+''+country+'</div>';
                html += '  </div>';
                html += '  </div>';
                html += '  <div class="col-sm-11 padding-0 action">';
                html += '   ' + temp.html();
                html += '  </div>';
                html += ' </div>';
                html += '</li>';
                if (checked != '') {
                    $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                    $(this).addClass('selected');
                } else {
                    $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                    ;
                    $(this).removeClass('selected');
                }
            });

            html += '</ul>';
            $('.gridview').html(html);
            if(view==2){
                $('.gridview').addClass('hidden');
                $('#table-4').removeClass('hidden');
            }else{
                $('#table-4').addClass('hidden');
                $('.gridview').removeClass('hidden');
            }

            $(".change_verification_status").change(function(e) {
                    if (!confirm('Are you sure you want to change verification status?')) {
                        return false;
                    }
                    $('#table-4_processing').hide();
                    $('#table-4_processing').show();

                    var id = $("option:selected", this).attr("data-id");
                    varification_url =  '{{ URL::to('accounts/{id}/change_verifiaction_status')}}/'+ $(this).val();
                    varification_url = varification_url.replace('{id}',id);

                    $.ajax({
                        url: varification_url,
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
            //select all record
            $('#selectallbutton').click(function(){
                if($('#selectallbutton').is(':checked')){
                    checked = 'checked=checked disabled';
                    $("#selectall").prop("checked", true).prop('disabled', true);
                    //if($('.gridview').is(':visible')){
                        $('.gridview li div.box').each(function(i,el){
                            $(this).addClass('selected');
                        });
                    //}else{
                        $('#table-4 tbody tr').each(function (i, el) {
                            $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                            $(this).addClass('selected');
                        });
                    //}
                }else{
                    checked = '';
                    $("#selectall").prop("checked", false).prop('disabled', false);
                    //if($('.gridview').is(':visible')){
                        $('.gridview li div.box').each(function(i,el){
                            $(this).removeClass('selected');
                        });
                    //}else{
                        $('#table-4 tbody tr').each(function (i, el) {
                            $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                            $(this).removeClass('selected');
                        });
                    //}
                }
            });

        }
    });
    $("#account_filter").submit(function(e) {
        e.preventDefault();

        $searchFilter.account_name = $("#account_filter [name='account_name']").val();
        $searchFilter.account_number = $("#account_filter [name='account_number']").val();
        $searchFilter.contact_name = $("#account_filter [name='contact_name']").val();
        $searchFilter.tag = $("#account_filter [name='tag']").val();
        $searchFilter.verification_status = $("#account_filter [name='verification_status']").val();
        $searchFilter.account_owners = $("#account_filter [name='account_owners']").val();
        $searchFilter.customer_on_off = $("#account_filter [name='customer_on_off']").prop("checked");
        $searchFilter.vendor_on_off = $("#account_filter [name='vendor_on_off']").prop("checked");
        $searchFilter.account_active = $("#account_filter [name='account_active']").prop("checked");

        data_table.fnFilter('', 0);
        return false;
    });
    $("#selectcheckbox").append('<input type="checkbox" id="selectallbutton" name="checkboxselect[]" class="" title="Select All Found Records" />');

    $('#AccountStatus').change(function() {
        if ($(this).is(":checked")) {
            data_table.fnFilter(1,0);  // 1st value 2nd column index
        } else {
            data_table.fnFilter(0,0);
        }
    });


    $(".dataTables_wrapper select").select2({
        minimumResultsForSearch: -1
    });

    // Highlighted rows
    $("#table-4 tbody input[type=checkbox]").each(function (i, el) {
        var $this = $(el),
            $p = $this.closest('tr');

        $(el).on('change', function () {
            var is_checked = $this.is(':checked');

            $p[is_checked ? 'addClass' : 'removeClass']('highlight');
        });
    });

    $(document).on('click', '#table-4 tbody tr,.gridview ul li div.box', function() {
        if (checked =='') {
            $(this).toggleClass('selected');
            if($(this).is('tr')) {
                if ($(this).hasClass('selected')) {
                    $(this).find('.rowcheckbox').prop("checked", true);
                } else {
                    $(this).find('.rowcheckbox').prop("checked", false);
                }
            }
        }
    });

    $("#selectall").click(function(ev) {
        var is_checked = $(this).is(':checked');
        $('#table-4 tbody tr').each(function(i, el) {
            if (is_checked) {
                $(this).find('.rowcheckbox').prop("checked", true);
                $(this).addClass('selected');
            } else {
                $(this).find('.rowcheckbox').prop("checked", false);
                $(this).removeClass('selected');
            }
        });
    });

    // Replace Checboxes
    $(".pagination a").click(function (ev) {
        replaceCheckboxes();
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

        $('#BulkMail-form [name="email_template_privacy"]').change(function(e){
            setTimeout(function(){ drodown_reset(); }, 100);
        });

        $("#BulkMail-form [name=template_option]").change(function(e){
            if($(this).val()==1){
                $('#templatename').removeClass("hidden");
            }else{
                $('#templatename').addClass("hidden");
            }
        });

        $("#BulkMail-form").submit(function(e){
            e.preventDefault();
            var SelectedIDs = [];
            var i = 0;
            if($("#BulkMail-form").find('[name="test"]').val()==0){
                if(checked=='') {
                    var SelectedIDs = getselectedIDs();
                    if(SelectedIDs.length==0){
                        $(".save").button('reset');
                        $(".savetest").button('reset');
                        $('#modal-BulkMail').modal('hide');
                        toastr.error('Please select at least one account or select all found accounts.', "Error", toastr_opts);
                        return false;
                    }
                }
                var criteria = JSON.stringify($searchFilter);
                $("#BulkMail-form").find("input[name='criteria']").val(criteria);
                $("#BulkMail-form").find("input[name='SelectedIDs']").val(SelectedIDs.join(","));

                if($("#BulkMail-form").find("input[name='SelectedIDs']").val()!="" && confirm("Are you sure to send mail to selected Accounts")!=true){
                    $(".btn").button('reset');
                    $(".savetest").button('reset');
                    $('#modal-BulkMail').modal('hide');
                    return false;
                }
            }
            var formData = new FormData($('#BulkMail-form')[0]);
            var url = baseurl + "/accounts/bulk_mail"
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
                        data_table.fnFilter('', 0);
                        reloadJobsDrodown(0);
                    }else{
                        toastr.error(response.message, "Error", toastr_opts);
                        $(".save").button('reset');
                        $(".savetest").button('reset');
                    }
                    $('.file-input-name').text('');
                    $('#attachment').val('');
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
            modal.find('.message').wysihtml5({
                "font-styles": true,
                "emphasis": true,
                "lists": true,
                "html": true,
                "link": true,
                "image": true,
                "color": false,
                parser: function(html) {
                    return html;
                }
            });
        });

        $('#modal-BulkMail').on('hidden.bs.modal', function(event){
            var modal = $(this);
            modal.find('.wysihtml5-sandbox, .wysihtml5-toolbar').remove();
            modal.find('.message').show();
        });

        $(document).on('click','#bulk-Ratesheet,.sendemail',function(){
            $("#BulkMail-form [name='template_option']").selectBoxIt().data("selectBox-selectBoxIt").selectOption('');
            //$("#BulkMail-form [name='email_template']").selectBoxIt().data("selectBox-selectBoxIt").selectOption('');
            //$("#BulkMail-form [name='Format']").selectBoxIt().data("selectBox-selectBoxIt").selectOption('');
            $('#BulkMail-form [name="email_template_privacy"]').selectBoxIt().data("selectBox-selectBoxIt").selectOption(0);
            $("#BulkMail-form")[0].reset();
            if($(this).hasClass('sendemail')){
                $("#BulkMail-form [name='type']").val('BAE');
                $("#BulkMail-form [name='Type']").val({{EmailTemplate::ACCOUNT_TEMPLATE}});
                $(".attachment").show();
                $("#test").show();
                $(".CD").hide();

            }else{
                $("#BulkMail-form [name='type']").val('CD');
                $("#BulkMail-form [name='Type']").val({{EmailTemplate::RATESHEET_TEMPLATE}});
                $(".attachment").hide();
                $("#test").hide();
                $(".CD").show();
            }
            //drodown_reset();
            $("#modal-BulkMail").modal({
                show: true
            });
        });

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

        $('#modal-BulkTags').on('hidden.bs.modal', function(event){
            var modal = $(this);
            var el = $('#account_filter').find('[name="tags"]');
            el.siblings('div').remove();
            el.removeClass('select2-offscreen');
            el.select2({tags:{{$tags}}});
        });

        $("#bulk-tags").click(function() {
            var el = $('#modal-BulkTags').find('[name="tags"]');
            el.siblings('div').remove();
            el.removeClass('select2-offscreen');
            el.val('');
            el.select2({tags:{{$tags}}});
            $('#modal-BulkTags').find('[name="SelectedIDs"]').val('');
            $('.save').button('reset');
            $('#modal-BulkTags').modal('show');
        });

        $(document).on('click','.switcher',function(){
            var self = $(this);
            if(self.hasClass('active')){
                return false;
            }
            var activeurl;
            var desctiveurl;
            if(self.attr('id')=='gridview'){
                var activeurl = baseurl + '/assets/images/grid-view-active.png';
                var desctiveurl = baseurl + '/assets/images/list-view.png';
                view = 1;
            }else{
                var activeurl = baseurl + '/assets/images/list-view-active.png';
                var desctiveurl = baseurl + '/assets/images/grid-view.png';
                view = 2;
            }
            self.find('img').attr('src',activeurl);
            self.addClass('active');
            var sibling = self.siblings('a');
            sibling.find('img').attr('src',desctiveurl);
            sibling.removeClass('active');
            $('.gridview').toggleClass('hidden');
            $('#table-4').toggleClass('hidden');
        });


        $("#BulkTag-form").submit(function(e){
            e.preventDefault();
            var SelectedIDs = getselectedIDs();
            if (SelectedIDs.length == 0) {
                $(".save").button('reset');
                $('#modal-BulkTags').modal('hide');
                toastr.error('Please select at least one Account.', "Error", toastr_opts);
                return false;
            }else{
                if(confirm('Do you want to add tags to selected Accounts')){
                    var url = baseurl + "/accounts/bulk_tags";
                    $("#BulkTag-form").find("input[name='SelectedIDs']").val(SelectedIDs.join(","));
                    var formData = new FormData($('#BulkTag-form')[0]);
                    $.ajax({
                        url: url,  //Server script to process data
                        type: 'POST',
                        dataType: 'json',
                        success: function (response) {
                            if(response.status =='success'){
                                toastr.success(response.message, "Success", toastr_opts);
                                $(".save").button('reset');
                                $('#modal-BulkTags').modal('hide');
                                data_table.fnFilter('', 0);
                                reloadJobsDrodown(0);
                            }else{
                                toastr.error(response.message, "Error", toastr_opts);
                                $(".save").button('reset');
                            }
                        },
                        // Form data
                        data: formData,
                        //Options to tell jQuery not to process data or worry about content-type.
                        cache: false,
                        contentType: false,
                        processData: false
                    });
                }
            }
        });

        $(".tags").select2({
            tags:{{$tags}}
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
                    $(el).data("selectBox-selectBoxIt").remove();
                    $.each(data,function(key,value){
                        $(el).data("selectBox-selectBoxIt").add({ value: key, text: value });
                    });
                    $(el).selectBoxIt().data("selectBox-selectBoxIt").selectOption('');
                } else {
                    toastr.error(status, "Error", toastr_opts);
                }
            });
        }
        function editor_reset(data){
            var modal = $("#modal-BulkMail");
            modal.find('.wysihtml5-sandbox, .wysihtml5-toolbar').remove();
            modal.find('.message').show();
            if(!Array.isArray(data)){
                var EmailTemplate = data['EmailTemplate'];
                modal.find('[name="subject"]').val(EmailTemplate.Subject);
                modal.find('.message').val(EmailTemplate.TemplateBody);
            }else{
                modal.find('[name="subject"]').val('');
                modal.find('.message').val('');
            }
            modal.find('.message').wysihtml5({
                "font-styles": true,
                "emphasis": true,
                "lists": true,
                "html": true,
                "link": true,
                "image": true,
                "color": false,
                parser: function(html) {
                    return html;
                }
            });
        }
    });

    function getselectedIDs(){
        var SelectedIDs = [];
        if($('.gridview').is(':visible')){
            $('.gridview li div.selected .action input[name="accountid"]').each(function(i,el){
                AccountID = $(this).val();
                SelectedIDs[i++] = AccountID;
            });
        }else{
            $('#table-4 tr .rowcheckbox:checked').each(function (i, el) {
                leadID = $(this).val();
                SelectedIDs[i++] = leadID;
            });
        }
       return SelectedIDs;
    }


</script>
<style>
    .dataTables_filter label{
        display:none !important;
    }
    .dataTables_wrapper .export-data{
        right: 30px !important;
    }
    #selectcheckbox{
        padding: 15px 10px;
    }
</style>
<link rel="stylesheet" href="assets/js/wysihtml5/bootstrap-wysihtml5.css">
<script src="assets/js/wysihtml5/wysihtml5-0.4.0pre.min.js"></script>
<script src="assets/js/wysihtml5/bootstrap-wysihtml5.js"></script>
@stop

@section('footer_ext')
    @parent
    <div class="modal fade" id="modal-BulkMail">
        <div class="modal-dialog" style="width: 80%;">
            <div class="modal-content">
                <form id="BulkMail-form" method="post" action="" enctype="multipart/form-data">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Bulk Send Email</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row CD">
                            <div class="form-group">
                                <label for="field-1" class="col-sm-2 control-label">Trunk</label>
                                <div class="col-sm-9">
                                    @foreach ($trunks as $index=>$trunk)
                                        @if(!empty($trunk) && !empty($index))
                                            <div class="col-sm-2">
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" name="Trunks[]" value="{{$index}}" >{{$trunk}}
                                                    </label>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="row CD">
                            <div class="form-group">
                                <br />
                                <label for="field-1" class="col-sm-2 control-label">Merge Output file By Trunk</label>
                                <div class="col-sm-5">
                                    <div class="make-switch switch-small" data-on-label="<i class='entypo-check'></i>" data-off-label="<i class='entypo-cancel'></i>" data-animated="false">
                                        <input type="hidden" name="isMerge" value="0">
                                        <input type="checkbox" name="isMerge" value="1">
                                        <input type="hidden" name="sendMail" value="1">
                                        <input type="hidden" name="Format" value="{{RateSheetFormate::RATESHEET_FORMAT_RATESHEET}}">
                                        <input type="hidden" name="Type" value="1">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-Group">
                                <br />
                                <label for="field-1" class="col-sm-2 control-label">Show Template</label>
                                <div class="col-sm-2">
                                    {{Form::select('email_template_privacy',EmailTemplate::$privacy,'',array("class"=>"selectboxit"))}}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-Group">
                                <br />
                                <label for="field-1" class="col-sm-2 control-label">Email Template</label>
                                <div class="col-sm-4">
                                    {{Form::select('email_template',$emailTemplates,'',array("class"=>"selectboxit"))}}
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
                                    <input type="hidden" name="criteria" />
                                    <input type="hidden" name="Type" value="{{EmailTemplate::ACCOUNT_TEMPLATE}}" />
                                    <input type="hidden" name="type" value="BAE" />
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
                            <div class="form-group">
                                <br/>
                                <label for="field-5" class="col-sm-2 control-label">Attachment</label>
                                <div class="col-sm-10">
                                    <input type="file" id="attachment"  name="attachment" class="form-control file2 inline btn btn-primary" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" />
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-Group">
                                <br />
                                <label for="field-1" class="col-sm-2 control-label">Template Option</label>
                                <div class="col-sm-4">
                                    {{Form::select('template_option',$templateoption,'',array("class"=>"selectboxit"))}}
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
                        <button id="test"  class="savetest btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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
    </div>

    <div class="modal fade" id="modal-BulkTags">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="BulkTag-form" method="post" action="">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Bulk Account tags</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-Group">
                                <label class="col-sm-2 control-label">Tag</label>
                                <div class="col-sm-8">
                                    <input class="form-control tags" name="tags" type="text" >
                                    <input type="hidden" name="SelectedIDs" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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