@extends('layout.main')

@section('filter')
    <div id="datatable-filter" class="fixed new_filter" data-current-user="Art Ramadani" data-order-by-status="1" data-max-chat-history="25">
        <div class="filter-inner">
            <h2 class="filter-header">
                <a href="#" class="filter-close" data-animate="1"><i class="entypo-cancel"></i></a>
                <i class="fa fa-filter"></i>
                Filter
            </h2>
            <form id="lead_filter" method="get"  action="{{URL::to('leads/ajax_datagrid')}}" class="form-horizontal form-groups-bordered validate" novalidate>

                <div class="form-group">
                    <label for="field-1" class="control-label">Company</label>
                    <input class="form-control" name="account_name"  type="text" >
                </div>
                <div class="form-group">
                    <label class="control-label">Contact Name</label>
                    <input class="form-control" name="contact_name" type="text" >
                </div>
                <div class="form-group">
                    <label class="control-label">Tag</label>
                    <input class="form-control leadTags" name="tag" type="text" >
                </div>
                <div class="form-group">
                    <label class="control-label">Active</label><br/>
                    <p class="make-switch switch-small">
                        <input id="account_active" name="account_active" type="checkbox" value="1" checked="checked">
                    </p>
                </div>
                <div class="form-group">
                    @if(User::is_admin())
                        <label for="field-1" class="control-label">Owner</label>
                        {{Form::select('account_owners',$account_owners,Input::get('account_owners'),array("class"=>"select2"))}}
                    @endif
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
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Leads</strong>
    </li>
</ol>
<h3>Leads</h3>

@include('includes.errors')
@include('includes.success')

<div class="clear"></div>
@if(User::checkCategoryPermission('Leads','Email,Edit'))
<div class="row">
    <div  class="col-md-12">
        <div class="input-group-btn pull-right hidden dropdown" style="width:70px;">
            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="caret"></span></button>
            <ul class="dropdown-menu dropdown-menu-left" role="menu" style="background-color: #000; border-color: #000; margin-top:0px;">
                @if(User::checkCategoryPermission('Leads','Email'))
                <li>
                    <a href="javascript:void(0)" class="sendemail">
                        <i class="entypo-mail"></i>
                        <span>Bulk Email</span>
                    </a>
                </li>
                @endif
                @if(User::checkCategoryPermission('Leads','Edit'))
                    <li>
                        <a href="javascript:void(0)" id="bulk-tags">
                            <i class="entypo-tag"></i>
                            <span>Bulk Tags</span>
                        </a>
                    </li>
                @endif
                <li>
                    <a href="{{URL::to('/import/leads')}}">
                        <i class="entypo-user-add"></i>
                        <span>Import</span>
                    </a>
                </li>
            </ul>
        </div><!-- /btn-group -->

        @if(User::checkCategoryPermission('Leads','Add'))
            <a href="{{URL::to('leads/create')}}" class="btn btn-primary pull-right">
                <i class="entypo-plus"></i>
                Add New
            </a>
        @endif
    </div>
    <div class="clear"></div>
</div>
@endif
<br>
<!--<p style="text-align: right;">
    <a href="javascript:void(0)" id="selectallbutton" class="btn btn-primary ">
        <i class="entypo-check"></i>
        <span>Select all found leads</span>
    </a>
</p>
<br />-->
<table class="table table-bordered datatable hidden" id="table-4">
<thead>
<tr>
    <th width="10%"><input type="checkbox" id="selectall" name="checkbox[]" class="" /></th>
    <th width="15%">Company</th>
    <th width="15%">Name</th>
    <th width="15%">Phone</th>
    <th width="15%">Email</th>
    <th width="30%">Actions</th>
</tr>
</thead>
<tbody>
</tbody>
</table>

<script type="text/javascript">
    var editor_options 	 	=  		{"leadoptions":true};
    var $searchFilter = {};
    var checked = '';
    var view = 1;
    var leadview = getCookie('leadview');
    if(leadview=='list'){
        view = 2;
    }
    jQuery(document).ready(function ($) {

        $('#filter-button-toggle').show();

        $searchFilter.account_name = $("#lead_filter [name='account_name']").val();
        $searchFilter.account_number = $("#lead_filter [name='account_number']").val();
        $searchFilter.contact_name = $("#lead_filter [name='contact_name']").val();
        $searchFilter.tag = $("#lead_filter [name='tag']").val();

        $searchFilter.account_owners = $("#lead_filter [name='account_owners']").val();
        $searchFilter.account_active = $("#lead_filter [name='account_active']").prop("checked");
            data_table = $("#table-4").dataTable({

                "bProcessing": true,
                "bServerSide": true,
                "bDestroy": true,
                "sAjaxSource": baseurl + "/leads/ajax_datagrid",
                "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                "fnServerParams": function(aoData) {
                    aoData.push({"name":"account_name","value":$searchFilter.account_name},{"name":"account_number","value":$searchFilter.account_number},{"name":"contact_name","value":$searchFilter.contact_name},{"name":"account_active","value":$searchFilter.account_active},{"name":"account_owners","value":$searchFilter.account_owners},{"name":"tag","value":$searchFilter.tag});
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push({"name":"account_name","value":$searchFilter.account_name},{"name":"account_number","value":$searchFilter.account_number},{"name":"contact_name","value":$searchFilter.contact_name},{"name":"account_active","value":$searchFilter.account_active},{"name":"account_owners","value":$searchFilter.account_owners},{"name":"tag","value":$searchFilter.tag});
                },
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'change-view'><'export-data'T>f>r><'gridview'>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "oTableTools": {},
                "aaSorting": [[0, 'asc']],
                "aoColumns": [
                    {"bSortable": false,
                        mRender: function(id, type, full) {
                            return '<div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + full[4] + '" class="rowcheckbox" ></div>';
                        }
                    }, //0Checkbox
                    {"bSortable": true,
                        mRender: function(id, type, full) {
                            return full[0];
                        }
                    },
                    {"bSortable": true,
                        mRender: function(id, type, full) {
                            return full[1];
                        }
                    },
                    {"bSortable": true,
                        mRender: function(id, type, full) {
                            return full[2];
                        }
                    },
                    {"bSortable": true,
                        mRender: function(id, type, full) {
                            return full[3];
                        }
                    },
                    {
                        "bSortable": true,
                        mRender: function (id, type, full) {
                            var action, edit_, show_;
                            id = full[4];
                            edit_ = "{{ URL::to('leads/{id}/edit')}}";
                            clone_ = "{{ URL::to('leads/{id}/clone')}}";
							show_ = "{{ URL::to('leads/{id}/show')}}";

                            edit_ = edit_.replace('{id}', id);
                            clone_ = clone_.replace('{id}', id);
                            show_ = show_.replace('{id}', id);
                            action = '';
                            <?php if(User::checkCategoryPermission('Opportunity','Add')) { ?>
                            action +='&nbsp;<button class="btn btn-default btn-xs opportunity" title="Add Opportunity" data-id="'+id+'" type="button"> <i class="fa fa-line-chart"></i> </button>';
                            <?php } ?>
                            <?php if(User::checkCategoryPermission('Leads','Edit')) { ?>
                            action +='&nbsp;<button redirecto="'+edit_+'" class="btn btn-default btn-xs" title="Edit" data-id="'+full[0]+'" type="button"> <i class="entypo-pencil"></i> </button>';
                            <?php } ?>
                            <?php if(User::checkCategoryPermission('Leads','Clone')) { ?>
                            //action += '&nbsp;<a href="' + clone_ + '" class="btn btn-default btn-sm btn-icon icon-left"><i class="entypo-users"></i>Clone </a>';
                            <?php } ?>
                            action +='&nbsp;<button redirecto="'+show_+'" class="btn btn-default btn-xs" title="View" data-id="'+full[0]+'" type="button"> <i class="fa fa-eye"></i> </button>';//entypo-info

                            action +='<input type="hidden" name="accountid" value="'+id+'"/>';
                            action +='<input type="hidden" name="address1" value="'+full[7]+'"/>';
                            action +='<input type="hidden" name="address2" value="'+full[8]+'"/>';
                            action +='<input type="hidden" name="address3" value="'+full[9]+'"/>';
                            action +='<input type="hidden" name="city" value="'+full[10]+'"/>';
                            action +='<input type="hidden" name="country" value="'+full[11]+'"/>';
                            action +='<input type="hidden" name="picture" value="'+full[12]+'"/>';
							action +='<input type="hidden" name="PostCode" value="'+full[13]+'"/>';
                            return action;
                        }
                    },
                ],
                "oTableTools": {
                    "aButtons": [
                        {
                            "sExtends": "download",
                            "sButtonText": "EXCEL",
                            "sUrl": baseurl + "/leads/exports/xlsx",
                            sButtonClass: "save-collection btn-sm"
                        },
                        {
                            "sExtends": "download",
                            "sButtonText": "CSV",
                            "sUrl": baseurl + "/leads/exports/csv",
                            sButtonClass: "save-collection btn-sm"
                        }
                    ]
                },
                "fnDrawCallback": function() {
                    $(".dropdown").removeClass("hidden");
                    var toggle = '<header>';
                    toggle += '<span class="list-style-buttons">';
                    if(view==1){
                        toggle += '<a href="javascript:void(0)" title="Grid View" class="btn btn-primary switcher grid active"><i class="entypo-book-open"></i></a>';
                        toggle += '<a href="javascript:void(0)" title="List View" class="btn btn-primary switcher list"><i class="entypo-list"></i></a>';
                    }else{
                        toggle += '<a href="javascript:void(0)" title="Grid View" class="btn btn-primary switcher grid"><i class="entypo-book-open"></i></a>';
                        toggle += '<a href="javascript:void(0)" title="List View" class="btn btn-primary switcher list active"><i class="entypo-list"></i></a>';
                    }
                    toggle +='</span>';
                    toggle += '</header>';
                    $('.change-view').html(toggle);
                    var html = '<ul class="clearfix grid col-md-12">';
                    if($(this).parents('.page-container').hasClass('sidebar-collapsed')) {
                        checkClass = '1';
                    }else{
                        checkClass = '0';
                    }
                     $('#table-4 tbody tr').each(function(i, el) {
                        var childrens = $(this).children();
                         if(childrens.eq(0).hasClass('dataTables_empty')){
                             return true;
                         }
                         var temp = childrens.eq(5).clone();
                         $(temp).find('a').each(function(){
                             $(this).find('i').remove();
                             $(this).removeClass('btn btn-icon icon-left');
                             $(this).addClass('label');
                             $(this).addClass('padding-4');
                         });
                         var address1 	= 	$(temp).find('input[name="address1"]').val();
                         var address2 	= 	$(temp).find('input[name="address2"]').val();
                         var address3 	= 	$(temp).find('input[name="address3"]').val();
                         var city 		= 	$(temp).find('input[name="city"]').val();
                         var country 	= 	$(temp).find('input[name="country"]').val();
						 var PostCode 	= 	$(temp).find('input[name="PostCode"]').val();
                         address1 		= 	(address1=='null'||address1==''?'':address1+'<br>');
                         address2 		= 	(address2=='null'||address2==''?'':address2+'<br>');
                         address3 		= 	(address3=='null'||address3==''?'':address3+'<br>');
                         city 			= 	(city=='null'||city==''?'':city+'<br>');
						 PostCode 		= 	(PostCode=='null'||PostCode==''?'':PostCode+'<br>');
                         country 		= 	(country=='null'||country==''?'':country);
                        var url 		= 	baseurl + '/assets/images/placeholder-male.gif';
                         var select='';
                         if (checked!='') {
                             select = 'selected';
                         }
                         if(checkClass=='1'){
                             html += '<li class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-xsm-12">';
                         }else{
                             html += '<li class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-xsm-12">';
                         }
						 var account_title = childrens.eq(1).text();
				if(account_title.length>22){
					account_title  = account_title.substring(0,22)+"...";	
				}
				
				var account_name = childrens.eq(2).text();
				if(account_name.length>40){
					account_name  = account_name.substring(0,40)+"...";	
				}
						 
                         html += '  <div class="box clearfix ' + select + '">';
                         //html += '  <div class="col-sm-4 header padding-0"> <img class="thumb" alt="default thumb" height="50" width="50" src="' + url + '"></div>';
                         html += '  <div class="col-sm-12 header padding-left-1">  <span class="head">' + account_title + '</span><br>';
                         html += '  <span class="meta complete_name">' + account_name + '</span></div>';
                         html += '  <div class="col-sm-6 padding-0">';
                         html += '  <div class="block">';
                         html += '     <div class="meta">Email</div>';
                         html += '     <div><a href="javascript:void(0)" class="sendemail">' + childrens.eq(4).text() + '</a></div>';
                         html += '  </div>';
                         html += '  <div class="cellNo">';
                         html += '     <div class="meta">Phone</div>';
                         html += '     <div><a href="tel:' + childrens.eq(3).text() + '">' + childrens.eq(3).text() + '</a></div>';
                         html += '  </div>';
                         html += '  </div>';
                         html += '  <div class="col-sm-6 padding-0">';
                         html += '  <div class="block">';
                         html += '     <div class="meta">Address</div>';
                         //html += '     <div class="address">' + address1 + ''+address2+''+address3+''+city+''+country+'</div>';
						 html += '     <div class="address">' + address1 + ''+address2+''+address3+''+city+''+PostCode+''+country+'</div>';
                         html += '  </div>';
                         html += '  </div>';
                         html += '  <div class="col-sm-11 padding-0 action">';
                         html += '   ' + temp.html();
                         html += '  </div>';
                         html += ' </div>';
                         html += '</li>';
                        if (checked!='') {
                            $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                            $(this).addClass('selected');
                        } else {
                            $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);;
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
                        //    }
                        }
                    });
                }
            });
        $("#selectcheckbox").append('<input type="checkbox" id="selectallbutton" name="checkboxselect[]" class="" title="Select All Found Records" />');
        $("#lead_filter").submit(function(e) {
            e.preventDefault();
            $searchFilter.account_name = $("#lead_filter [name='account_name']").val();
            $searchFilter.account_number = $("#lead_filter [name='account_number']").val();
            $searchFilter.contact_name = $("#lead_filter [name='contact_name']").val();
            $searchFilter.tag = $("#lead_filter [name='tag']").val();

            $searchFilter.account_owners = $("#lead_filter [name='account_owners']").val();
            $searchFilter.account_active = $("#lead_filter [name='account_active']").prop("checked");
            data_table.fnFilter('', 0);
            return false;
        });

            $(".dataTables_wrapper select").select2({
                minimumResultsForSearch: -1
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

        $("#BulkMail-form [name=email_template]").change(function(e){
            var templateID = $(this).val();
            if(templateID>0) {
                var url = baseurl + '/leads/' + templateID + '/ajax_template';
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
            if($("#BulkMail-form").find('[name="test"]').val()==0) {
                if (checked == '') {
                    var SelectedIDs = getselectedIDs();
                    if (SelectedIDs.length == 0) {
                        $(".save").button('reset');
						$(".savetest").button('reset');
                        $('#modal-BulkMail').modal('hide');
                        toastr.error('Please select at least one lead or select all found leads.', "Error", toastr_opts);
                        return false;
                    }
                }
                var criteria = JSON.stringify($searchFilter);
                $("#BulkMail-form").find("input[name='criteria']").val(criteria);
                $("#BulkMail-form").find("input[name='SelectedIDs']").val(SelectedIDs.join(","));

                if ($("#BulkMail-form").find("input[name='SelectedIDs']").val() != "" && confirm("Are you sure to send mail to selected leads") != true) {
                    $(".btn").button('reset');
					$(".savetest").button('reset');
                    $('#modal-BulkMail').modal('hide');
                    return false;
                }
            }
            var formData = new FormData($('#BulkMail-form')[0]);
            var url = baseurl + "/leads/bulk_mail"
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

        // Highlighted rows
        $("#table-4 tbody input[type=checkbox]").each(function (i, el) {
            var $this = $(el),
                    $p = $this.closest('tr');

            $(el).on('change', function () {
                var is_checked = $this.is(':checked');
                $p[is_checked ? 'addClass' : 'removeClass']('highlight');
            });
        });

        // Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });

        $('#modal-BulkMail').on('shown.bs.modal', function(event){
            var modal = $(this);
            show_summernote(modal.find(".message"),editor_options);
        });

        $('#modal-BulkMail').on('hidden.bs.modal', function(event){
            var modal = $(this);
        });

        $(document).on('click','.sendemail',function(){
			document.getElementById('BulkMail-form').reset();
			$("#modal-BulkMail").find('.file-input-name').html("");
            $("#BulkMail-form [name='email_template']").val('').trigger("change");
            $("#BulkMail-form [name='template_option']").val('').trigger("change");
            $("#BulkMail-form [name='email_template_privacy']").val(0).trigger("change");
            $("#BulkMail-form")[0].reset();
            $("#modal-BulkMail").modal({
                show: true
            });
        });

        $('#modal-BulkTags').on('hidden.bs.modal', function(event){
            var modal = $(this);
            var el = $('#lead_filter').find('[name="tags"]');
            el.siblings('div').remove();
            el.removeClass('select2-offscreen');
            el.select2({tags:{{$leadTags}}});
        });

        $("#bulk-tags").click(function() {
            var el = $('#modal-BulkTags').find('[name="tags"]');
            el.siblings('div').remove();
            el.removeClass('select2-offscreen');
            el.val('');
            el.select2({tags:{{$leadTags}}});
            $('#modal-BulkTags').find('[name="SelectedIDs"]').val('');
            $('#modal-BulkTags').modal('show');
        });

        $("#BulkTag-form").submit(function(e){
            e.preventDefault();
            var SelectedIDs = getselectedIDs();
            if (SelectedIDs.length == 0) {
                $(".save").button('reset');
                $('#modal-BulkTags').modal('hide');
                toastr.error('Please select at least one lead.', "Error", toastr_opts);
                return false;
            }else{
                if(confirm('Do you want to add tags to selected leads')){
                    var url = baseurl + "/leads/bulk_tags";
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


        $(".leadTags").select2({
            tags:{{$leadTags}}
        });

        $('.opportunityTags').select2({
            tags:{{$opportunityTags}}
        });

        $("#test").click(function(e){
            e.preventDefault();
            $("#BulkMail-form").find('[name="test"]').val(1);
            $('#TestMail-form').find('[name="EmailAddress"]').val('');
            $('#modal-TestMail').modal({show: true});
        });
        $("#mail-send").click(function(e){
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
        $(document).on('click','.switcher',function(){
            var self = $(this);
            if(self.hasClass('active')){
                return false;
            }
            var activeurl;
            var desctiveurl;
            if(self.hasClass('grid')){
                setCookie('leadview','grid','30');
                view = 1;
            }else{
                setCookie('leadview','list','30');
                view = 2;
            }
            self.addClass('active');
            var sibling = self.siblings('a').removeClass('active');
            $('.gridview').toggleClass('hidden');
            $('#table-4').toggleClass('hidden');
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

        function editor_reset(data){
            var modal = $("#modal-BulkMail");
            if(!Array.isArray(data)){
                var EmailTemplate = data['EmailTemplate'];
                modal.find('[name="subject"]').val(EmailTemplate.Subject);
                modal.find('.message').val(EmailTemplate.TemplateBody);
            }else{
                modal.find('[name="subject"]').val('');
                modal.find('.message').val('');
            }
            show_summernote(modal.find(".message"),editor_options);
        }

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
    });

    function setCookie(cname,cvalue,exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays*24*60*60*1000));
        var expires = "expires=" + d.toGMTString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }

    function getCookie(cname) {
        var name = cname + "=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(';');
        for(var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }



</script>
<style>
    .dataTables_filter label{
        display:none !important;
    }
    .dataTables_wrapper .export-data{
        right: 30px !important;
    }
    #selectcheckbox,#datatable-filter-btn-box{
        padding: 15px 10px;
    }
</style>
@include('opportunityboards.opportunitymodal')
@include('accounts.bulk_email')
@stop

@section('footer_ext')
    @parent
    <div class="modal fade" id="modal-BulkMailtemp">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="BulkMail-form" method="post" action="" enctype="multipart/form-data">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Bulk Send Email</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-Group">
                                <br />
                                <label for="field-1" class="col-md-2 control-label">Show Template</label>
                                <div class="col-md-2">
                                    {{Form::select('email_template_privacy',$privacy,'',array("class"=>"select2 small"))}}
                                </div>
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
                                            <input type="hidden" name="criteria" />
                                            <input type="hidden" name="Type" value="{{EmailTemplate::ACCOUNT_TEMPLATE}}" />
                                            <input type="hidden" name="type" value="BAE" />
                                            <input type="hidden" name="ratesheetmail" value="0" />
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
                        <button id="bull-email-account" type="submit" id="mail-send"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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

    

    <div class="modal fade" id="modal-BulkTags">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="BulkTag-form" method="post" action="">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Bulk leads tags</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-Group">
                                <label class="col-sm-2 control-label">Tag</label>
                                <div class="col-sm-8">
                                    <input class="form-control leadTags" name="tags" type="text" >
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