<style>
    #selectcheckbox{
        padding: 15px 10px;
    }
    #table-service_processing{
        position: absolute;
    }
    #cloneservicetable_filter label{
        display:block !important;
    }
</style>
<div class="panel panel-primary" data-collapsed="0">
    <div class="panel-heading">
        <div class="panel-title">
            Services
        </div>
        <div class="panel-options">
            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
        </div>
    </div>
    <div class="panel-body">
        <div id="service_filter" method="get" action="#" >
            <div class="panel panel-primary panel-collapse" data-collapsed="1">
                <div class="panel-heading">
                    <div class="panel-title">
                            Filter
                    </div>
                    <div class="panel-options">
                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                    </div>
                </div>
                <div class="panel-body" style="display: none;">
                    <div class="form-group">
                        <label for="field-1" class="col-sm-1 control-label">Name</label>
                        <div class="col-sm-2">
                            <input type="text" name="ServiceName" class="form-control" value="" />
                        </div>
                        <label for="field-1" class="col-sm-1 control-label">Active</label>
                        <div class="col-sm-2">
                            <p class="make-switch switch-small">
                                <input id="ServiceActive" name="ServiceActive" type="checkbox" value="1" checked="checked" >
                            </p>
                        </div>
                        <div class="col-sm-6">
                            <p style="text-align: right">
                            <button class="btn btn-primary btn-sm btn-icon icon-left" id="service_submit">
                                <i class="entypo-search"></i>
                                Search
                            </button>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row ropdown">
            <div  class="col-md-12">
                <div class="input-group-btn pull-right" style="width:70px;">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="caret"></span></button>
                    <ul class="dropdown-menu dropdown-menu-left" role="menu" style="background-color: #000; border-color: #000; margin-top:0px;">
                        @if(User::checkCategoryPermission('AccountService','Add'))
                            <li>
                                <a href="javascript:void(0)" id="add-services">
                                    <i class="entypo-plus"></i>&nbsp;Add New
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" id="clone-services">
                                    <i class="fa fa-clone"></i>&nbsp;&nbsp;&nbsp;Clone
                                </a>
                            </li>
                        @endif
                        @if(User::checkCategoryPermission('AccountService','Edit'))
                            <li>
                                <a href="javascript:void(0)" data-name="active" id="active-services">
                                    <i class="entypo-check"></i>&nbsp;Active
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)" data-name="deactive" id="deactive-services">
                                    <i class="entypo-minus-circled"></i>&nbsp;Deactivate
                                </a>
                            </li>
                        @endif
                        @if(User::checkCategoryPermission('AccountService','Delete'))
                            <li>
                                <a href="javascript:void(0)" data-name="delete" id="delete-services">
                                    <i class="entypo-trash"></i>&nbsp;Delete
                                </a>
                            </li>
                        @endif
                    </ul>
                </div><!-- /btn-group -->
            </div>
            <div class="clear"></div>
        </div>
        <div id="last_msg_loader" style="display: table; position: absolute; padding: 10px; text-align: center; left: 50%; top: auto; margin: 71px auto; z-index: 999;"></div>
        <div class="dataTables_wrapper">
            <table id="table-service" class="table table-bordered datatable">
                <thead>
                <tr>
                    <th width="5%"><input type="checkbox" id="selectall" name="checkbox[]" class="" /></th>
                    <th width="35%">Service Name</th>
                    <th width="30%">Service Title</th>
                    <th width="30%">Action</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <script type="text/javascript">
            /**
            * JQuery Plugin for dataTable
            * */
          //  var list_fields_activity  = ['ServiceName','InvoiceDescription','StartDate','EndDate'];      
            $("#service_filter").find('[name="ServiceName"]').val('');
            var data_table_service;
            var account_id='{{$account->AccountID}}';
            var update_new_url;
            var postdata;
            var checked='';

            jQuery(document).ready(function ($) {
				
		    var list_fields  = ["ServiceID", "ServiceName"];
            public_vars.$body = $("body");
            var $searchService = {};
            var service_edit_url = baseurl + "/accountservices/{{$account->AccountID}}/edit/{id}";
            var service_delete_url = baseurl + "/accountservices/{{$account->AccountID}}/{id}/delete";
            var service_datagrid_url = baseurl + "/accountservices/{{$account->AccountID}}/ajax_datagrid";
            var service_export_url = baseurl + "/accountservices/{{$account->AccountID}}/exports";
            var service_clone_url = baseurl + "/accountservices/{{$account->AccountID}}/clone";

            $("#service_submit").click(function(e) {                
                e.preventDefault();
                 
                    $searchService.ServiceName = $("#service_filter").find('[name="ServiceName"]').val();
                    $searchService.ServiceActive = $("#service_filter").find("[name='ServiceActive']").prop("checked");
                        data_table_service = $("#table-service").dataTable({
                            "bDestroy": true,
                            "bProcessing":true,
                            "bServerSide": true,
                            "sAjaxSource": service_datagrid_url,
                            "fnServerParams": function (aoData) {
                                aoData.push({"name": "account_id", "value": account_id},
                                        {"name": "ServiceName", "value": $searchService.ServiceName},
                                        {"name": "ServiceActive", "value": $searchService.ServiceActive});

                                data_table_extra_params.length = 0;
                                data_table_extra_params.push({"name": "account_id", "value": account_id},
                                        {"name": "ServiceName", "value": $searchService.ServiceName},
                                        {"name": "ServiceActive", "value": $searchService.ServiceActive},
                                        {"name":"Export","value":1});

                            },
                            "iDisplayLength": 10,
                            "sPaginationType": "bootstrap",
                            "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                            "aaSorting": [[0, 'asc']],
                            "aoColumns": [
                                {
                                    "bSortable": false, //Account
                                    mRender: function (id, type, full) {
                                        var chackbox = '<div class="checkbox "><input type="checkbox" name="checkbox[]" value="' + full[0] + '" class="rowcheckbox" ></div>';
                                        if($('#Recall_on_off').prop("checked")){
                                            chackbox='';
                                        }
                                        return chackbox;
                                    }
                                }, //1   CurrencyDescription
                                {  "bSortable": true },  // 0 Service Name
                                {  "bSortable": false },  // 0 Service Name
                               // {  "bSortable": false },  // 1 Service Status
                                //{  "bSortable": false },  // 2 Service ID
                                //{  "bSortable": false },  // 3 Account Service ID
                                {                        // 10 Action
                           "bSortable": false,
                            mRender: function ( id, type, full ) {
                                var Active_Card = baseurl + "/accountservices/{id}/changestatus/active";
                                var DeActive_Card = baseurl + "/accountservices/{id}/changestatus/deactive";
                                 action = '';
                                <?php if(User::checkCategoryPermission('AccountService','Edit')) { ?>
                                 if (full[3]=="1") {
                                    action += ' <button href="' + DeActive_Card.replace("{id}",full[0]) + '" title=""  class="btn activeservice btn-danger btn-sm tooltip-primary" data-original-title="Deactivate" title="" data-placement="top" data-toggle="tooltip" data-loading-text="Loading..."><i class="entypo-minus-circled"></i></button>';
                                 } else {
                                    action += ' <button href="' + Active_Card.replace("{id}",full[0]) + '" title="" class="btn deactiveservice btn-success btn-sm tooltip-primary" data-original-title="Activate" title="" data-placement="top" data-toggle="tooltip" data-loading-text="Loading..."><i class="entypo-check"></i></button>';
                                 }
                                 action += ' <a href="' + service_edit_url.replace("{id}",full[0]) +'" class="edit-service btn btn-default btn-sm tooltip-primary" data-original-title="Edit" title="" data-placement="top" data-toggle="tooltip"><i class="entypo-pencil"></i></a>';
                                <?php } ?>
                                        action += ' <a data-id=' +full[0] +' class="clone-service btn-default btn btn-sm tooltip-primary" data-original-title="Clone" title="" data-placement="top" data-toggle="tooltip"><i class="fa fa-clone"></i></a>';
                                <?php if(User::checkCategoryPermission('AccountService','Delete')) { ?>
                                    action += ' <a href="' + service_delete_url.replace("{id}",full[0]) +'" class="delete-service btn btn-danger btn-sm tooltip-primary" data-original-title="Delete" title="" data-placement="top" data-toggle="tooltip" data-loading-text="Loading..."><i class="entypo-trash"></i></a>';
                                <?php } ?>
                                 return action;
                            }
                          }
                         ],
                        "oTableTools": {
                            "aButtons": [
                                {
                                    "sExtends": "download",
                                    "sButtonText": "EXCEL",
                                    "sUrl": service_export_url + "/xlsx", //baseurl + "/generate_xls.php",
                                    sButtonClass: "save-collection btn-sm"
                                },
                                {
                                    "sExtends": "download",
                                    "sButtonText": "CSV",
                                    "sUrl": service_export_url + "/csv", //baseurl + "/generate_csv.php",
                                    sButtonClass: "save-collection btn-sm"
                                }
                            ]
                        },
                        "fnDrawCallback": function() {
                           $(".dataTables_wrapper select").select2({
                               minimumResultsForSearch: -1
                           });

                            $("#table-service tbody input[type=checkbox]").each(function (i, el) {
                                var $this = $(el),
                                        $p = $this.closest('tr');

                                $(el).on('change', function () {
                                    var is_checked = $this.is(':checked');

                                    $p[is_checked ? 'addClass' : 'removeClass']('selected');
                                });
                            });


                            $('#selectall').removeClass('hidden');

                            //select all record
                            $('#selectallbutton').click(function(){
                                if($('#selectallbutton').is(':checked')){
                                    checked = 'checked=checked disabled';
                                    $("#selectall").prop("checked", true).prop('disabled', true);
                                    $('#table-service tbody tr').each(function (i, el) {
                                        $(this).find('.rowcheckbox').prop("checked", true).prop('disabled', true);
                                        $(this).addClass('selected');
                                    });
                                }else{
                                    checked = '';
                                    $("#selectall").prop("checked", false).prop('disabled', false);
                                    $('#table-service tbody tr').each(function (i, el) {
                                        $(this).find('.rowcheckbox').prop("checked", false).prop('disabled', false);
                                        $(this).removeClass('selected');
                                    });
                                }
                            });

                            $(".clone-service").click(function(e){
                                e.preventDefault();
                                var self = $(this);
                                var serviceid = self.attr('data-id');

                                $('#cloneservices-form').trigger("reset");
                                $('#modal-clone-services h4').html('Clone Services');
                                $('#cloneservicetable').find('tbody tr').each(function (i, el) {
                                    var self = $(this);
                                    if (self.hasClass('selected')) {
                                        $(this).find('input[type="checkbox"]').prop("checked", false);
                                        $(this).removeClass('selected');
                                    }
                                });
                                $("#cloneservices-form").find("input[name='criteria']").val('');
                                $("#cloneservices-form").find("input[name='CloneID']").val(serviceid);
                                $('#modal-clone-services').modal('show');

                                initCustomerGrid();

                            });
                         }

                        });
                        $("#selectcheckbox").append('<input type="checkbox" id="selectallbutton" name="checkboxselect[]" class="" title="Select All Found Records" />');
                    });


                $(document ).ready(function (){
                        $('#service_submit').trigger('click');
                });
                //$('#service_submit').trigger('click');
                //inst.myMethod('I am a method');
                $('#add-services').click(function(ev){
                        ev.preventDefault();
                        $('#service-form').trigger("reset");
                        $('#modal-services h4').html('Add services');
                        $('#servicetable').find('tbody tr').each(function (i, el) {
                            var self = $(this);
                            if (self.hasClass('selected')) {
                                $(this).find('input[type="checkbox"]').prop("checked", false);
                                $(this).removeClass('selected');
                            }
                        });
                        $('#modal-services').modal('show');                        
                });
              
                $('table tbody').on('click', '.delete-service', function (ev) {
                        ev.preventDefault();
                        result = confirm("Are you Sure?");
                       if(result){
                           $(this).button('loading');
                           var delete_url  = $(this).attr("href");
                           submit_ajax_datatable( delete_url,"",0,data_table_service);
                            //data_table_service.fnFilter('', 0);
                           //console.log('delete');
                          // $('#service_submit').trigger('click');
                       }
                       return false;
                });


                $(document).on('click', '#active-services , #deactive-services,#delete-services', function (ev) {
                    ev.preventDefault();
                    var self = $(this);
                    var action = self.attr('data-name');

                    var criteria='';
                    var CloneIDs = [];
                    var record = '';
                    var result = '';
                    var url ='';
                    $('#table-service tr .rowcheckbox:checked').each(function(i, el) {
                        record = 1;
                    });
                    if(record==1){

                        if($('#selectallbutton').is(':checked')){
                            criteria = JSON.stringify($searchService);
                        }else{
                            var i = 0;
                            $('#table-service tr .rowcheckbox:checked').each(function(i, el) {
                                CloneID = $(this).val();
                                CloneIDs[i++] = CloneID;
                            });
                        }
                        var accountid='{{$account->AccountID}}';
                        if(action=='active' || action=='deactive'){
                            result = confirm('Are you sure you want to change status?');
                            url = baseurl + '/accountservices/' + accountid + '/bulk_change_status';
                        }
                        if(action=='delete'){
                            result = confirm('Are you sure you want to delete?');
                            url =  baseurl + '/accountservices/' + accountid + '/bulk_delete';
                        }
                        if(result){
                            var ServiceID = CloneIDs.join(",");
                            var data = 'ServiceID='+ServiceID+'&action='+action+'&Criteria='+criteria;
                            $('div#last_msg_loader').html('<img src="'+baseurl+'/assets/images/loader-2.gif">');
                            $.ajax({
                                url: url, //Server script to process data
                                type: 'POST',
                                dataType: 'json',
                                success: function (response) {
                                    $('div#last_msg_loader').empty();
                                    if (response.status == 'success') {
                                        toastr.success(response.message, "Success", toastr_opts);
                                        data_table_service.fnFilter('', 0);
                                    } else {
                                        toastr.error(response.message, "Error", toastr_opts);
                                    }

                                },
                                // Form data
                                data: data,
                                //Options to tell jQuery not to process data or worry about content-type.
                                cache: false

                            });

                        }

                    }else{
                        alert('No service selected');
                    }

                });


                $('table tbody').on('click', '.activeservice , .deactiveservice', function (ev) {
                    ev.preventDefault();
                    var data='accountid='+'{{$account->AccountID}}}';

                    var self = $(this);
                    var text = (self.hasClass("activeservice")?'Active':'Disable');
                    result = confirm('Are you sure you want to change status?');
                    if(result){
                        $(this).button('loading');
                        var delete_url  = $(this).attr("href");
                        submit_ajax_datatable( delete_url,data,0,data_table_service);
                        //data_table_service.fnFilter('', 0);
                        //console.log('delete');
                        // $('#service_submit').trigger('click');
                    }
                    return false;
                });

               $("#service-form").submit(function(e){

                   e.preventDefault();
                   var _url  = $(this).attr("action");
                   submit_ajax_datatable(_url,$(this).serialize(),0,data_table_service);
                   data_table_service.fnFilter('', 0);
                   //console.log('edit');
                  // $('#service_submit').trigger('click');
               });

                $('.selectallservices').on('click', function () {
                    var self = $(this);
                    var is_checked = $(self).is(':checked');
                    self.parents('table').find('tbody tr').each(function (i, el) {
                        if (is_checked) {
                            if ($(this).is(':visible')) {
                                $(this).find('input[type="checkbox"]').prop("checked", true);
                                $(this).addClass('selected');
                            }
                        } else {
                            $(this).find('input[type="checkbox"]').prop("checked", false);
                            $(this).removeClass('selected');
                        }
                    });
                });

                $(document).on('click', '#services-form .table tbody tr', function () {
                    var self = $(this);
                    if (self.hasClass('selected')) {
                        $(this).find('input[type="checkbox"]').prop("checked", false);
                        $(this).removeClass('selected');
                    } else {
                        $(this).find('input[type="checkbox"]').prop("checked", true);
                        $(this).addClass('selected');
                    }
                });

                $("#services-form").submit(function (e) {
                    e.preventDefault();
                    var post_data = $(this).serialize();
                    var AccountID = $(this).find("[name=AccountID]").val();
                    var _url = baseurl + '/accountservices/' + AccountID + '/addservices';
                    submit_ajax_datatable(_url,post_data,0,data_table_service);
                    //data_table_service.fnFilter('', 0);
                });

                // Select all
                $("#selectall").click(function(ev) {
                    var is_checked = $(this).is(':checked');
                    $('#table-service tbody tr').each(function(i, el) {
                        if (is_checked) {
                            $(this).find('.rowcheckbox').prop("checked", true);
                            $(this).addClass('selected');
                        } else {
                            $(this).find('.rowcheckbox').prop("checked", false);
                            $(this).removeClass('selected');
                        }
                    });
                });

                $('#clone-services').click(function(e){
                    e.preventDefault();
                    var criteria='';
                    var CloneIDs = [];
                    var record = '';
                    $('#table-service tr .rowcheckbox:checked').each(function(i, el) {
                        record = 1;
                    });
                    if(record==1){
                        if($('#selectallbutton').is(':checked')){
                            criteria = JSON.stringify($searchService);
                        }else{
                            var i = 0;
                            $('#table-service tr .rowcheckbox:checked').each(function(i, el) {
                                CloneID = $(this).val();
                                CloneIDs[i++] = CloneID;
                            });
                        }
                        $('#cloneservices-form').trigger("reset");
                        $('#modal-clone-services h4').html('Clone Services');
                        $('#cloneservicetable').find('tbody tr').each(function (i, el) {
                            var self = $(this);
                            if (self.hasClass('selected')) {
                                $(this).find('input[type="checkbox"]').prop("checked", false);
                                $(this).removeClass('selected');
                            }
                        });
                        $("#cloneservices-form").find("input[name='criteria']").val(criteria);
                        $("#cloneservices-form").find("input[name='CloneID']").val(CloneIDs.join(","));
                        $('#modal-clone-services').modal('show');

                        initCustomerGrid();
                    }else{
                        alert('No service selected');
                    }
                    return false;
                });

                $(document).on('click', '#cloneservices-form .table tbody tr', function () {
                    var self = $(this);
                    if (self.hasClass('selected')) {
                        $(this).find('input[type="checkbox"]').prop("checked", false);
                        $(this).removeClass('selected');
                    } else {
                        $(this).find('input[type="checkbox"]').prop("checked", true);
                        $(this).addClass('selected');
                    }
                });

                $("#cloneservices-form").submit(function(e){
                    e.preventDefault();
                    var url  = baseurl + "/accountservices/{{$account->AccountID}}/cloneservice";
                    submit_ajax_datatable(url,$(this).serialize(),0,data_table_service);
                    //data_table_service.fnFilter('', 0);

                    return false;
                });

                function initCustomerGrid(){
                    first_call = true;

                    var data_table_new = $("#cloneservicetable").dataTable({
                        "bDestroy": true, // Destroy when resubmit form
                        "sDom": "<'row'<'col-xs-12 border_left'f>r>t",
                        "bProcessing": false,
                        "bServerSide": false,
                        "bPaginate": false,
                        "fnServerParams": function(aoData) {
                        },
                        "sAjaxSource": baseurl + "/accountservices/"+account_id+"/search_accounts_grid",
                        "aoColumns":
                                [
                                    {"bSortable": false, //AccountID
                                        mRender: function(id, type, full) {
                                            return '<div class="checkbox "><input type="checkbox" name="AccountID[]" value="' + id + '" class="rowcheckbox" ></div>';
                                        }
                                    },
                                    {}
                                ],

                        "fnDrawCallback": function() {
                            $(".selectallAccounts").click(function(ev) {
                                var is_checked = $(this).is(':checked');
                                $('#cloneservicetable tbody tr').each(function(i, el) {
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
                }

            });


            </script>
    </div>
</div>



@section('footer_ext')
@parent

<div class="modal fade in" id="modal-services">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="services-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add Services</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <table id="servicetable" class="table table-bordered datatable">
                                    <thead>
                                    <tr>
                                        <td width="10%">
                                            <div class="checkbox">
                                                <input name="checkbox[]" class="selectallservices" type="checkbox">
                                            </div>
                                        </td>
                                        <td width="90%">Service Name</td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($services as $service)
                                    <tr class="draggable">
                                        <td>
                                            <div class="checkbox">
                                                <input name="ServiceID[]" value="{{$service->ServiceID}}" type="checkbox">
                                            </div>
                                        </td>
                                        <td>{{$service->ServiceName}}</td>
                                    </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
                <input type="hidden" name="AccountID" value="{{$account->AccountID}}">
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary print btn-sm btn-icon icon-left" data-loading-text="Loading...">
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

<div class="modal fade in" id="modal-clone-services">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="cloneservices-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Clone Services</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <span style="font-size:15px;">Select Sections To Clones</span>
                            </div>
                        </div>
                    <div class="row">
                        <div class="col-md-12">

                            <div class="form-group">
                                <div class="checkbox ">
                                    <label><input id="rd-1" name="ServiceTitle" checked="" value="1" type="checkbox">ServiceTitle</label>
                                    &nbsp;&nbsp;
                                    <label><input id="rd-1" name="ServiceDescription" checked="" value="1" type="checkbox">ServiceDescription</label>
                                    &nbsp;&nbsp;
                                    <label><input id="rd-1" name="Subscription" checked="" value="1" type="checkbox">Subscription</label>
                                    &nbsp;&nbsp;
                                    <label><input id="rd-1" name="Additional" checked="" value="1" type="checkbox">Additional Charges</label>
                                    &nbsp;&nbsp;
                                    <label><input id="rd-1" name="Billing" checked="" value="1" type="checkbox">Billing</label>
                                    &nbsp;&nbsp;
                                    <label><input id="rd-1" name="Tariff" checked="" value="1" type="checkbox">Tariff</label>

                                </div>
                                <div class="checkbox ">
                                    <label><input id="rd-1" name="DiscountPlan" checked="" value="1" type="checkbox">Discount Plan</label>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div style="max-height: 500px; overflow-y: auto; overflow-x: hidden;">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <table class="table table-bordered datatable" id="cloneservicetable">
                                    <thead>
                                    <tr>
                                        <th><input type="checkbox" class="selectallAccounts" name="checkbox[]" /></th>
                                        <th>Account Name</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
                <input type="hidden" name="ServiceAccountID" value="{{$account->AccountID}}">
                <input type="hidden" name="criteria">
                <input type="hidden" name="CloneID">
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary print btn-sm btn-icon icon-left" data-loading-text="Loading...">
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