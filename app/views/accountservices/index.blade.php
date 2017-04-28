<style>

#table-service_processing{
    position: absolute;
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
        @if(User::checkCategoryPermission('AccountService','Add'))
        <div class="text-right">
            <a  id="add-services" class=" btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-plus"></i>Add New</a>
            <div class="clear clearfix"><br></div>
        </div>
        @endif
        <div class="dataTables_wrapper">
            <table id="table-service" class="table table-bordered datatable">
                <thead>
                <tr>
                    <th width="60%">Service Name</th>
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

            jQuery(document).ready(function ($) {
				
		    var list_fields  = ["ServiceID", "ServiceName"];
            public_vars.$body = $("body");
            var $searchService = {};
            var service_edit_url = baseurl + "/accountservices/{{$account->AccountID}}/edit/{id}";
            var service_delete_url = baseurl + "/accountservices/{{$account->AccountID}}/{id}/delete";
            var service_datagrid_url = baseurl + "/accountservices/{{$account->AccountID}}/ajax_datagrid";
                
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
                                        {"name": "ServiceActive", "value": $searchService.ServiceActive});

                            },
                            "iDisplayLength": 10,
                            "sPaginationType": "bootstrap",
                            "sDom": "<'row'<'col-xs-6 col-left 'l><'col-xs-6 col-right'f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                            "aaSorting": [[0, 'asc']],
                            "aoColumns": [
                                {  "bSortable": true },  // 0 Service Name
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
                                 if (full[1]=="1") {
                                    action += ' <button href="' + DeActive_Card.replace("{id}",full[2]) + '" title=""  class="btn activeservice btn-danger btn-sm tooltip-primary" data-original-title="Deactivate" title="" data-placement="top" data-toggle="tooltip" data-loading-text="Loading..."><i class="entypo-minus-circled"></i></button>';
                                 } else {
                                    action += ' <button href="' + Active_Card.replace("{id}",full[2]) + '" title="" class="btn deactiveservice btn-success btn-sm tooltip-primary" data-original-title="Activate" title="" data-placement="top" data-toggle="tooltip" data-loading-text="Loading..."><i class="entypo-check"></i></button>';
                                 }
                                 action += ' <a href="' + service_edit_url.replace("{id}",full[2]) +'" class="edit-service btn btn-default btn-sm tooltip-primary" data-original-title="Edit" title="" data-placement="top" data-toggle="tooltip"><i class="entypo-pencil"></i></a>';
                                <?php } ?>
                                <?php if(User::checkCategoryPermission('AccountService','Edit')) { ?>
                                    action += ' <a href="' + service_delete_url.replace("{id}",full[2]) +'" class="delete-service btn btn-danger btn-sm tooltip-primary" data-original-title="Delete" title="" data-placement="top" data-toggle="tooltip" data-loading-text="Loading..."><i class="entypo-trash"></i></a>';
                                <?php } ?>
                                 return action;
                            }
                          }
                         ],
                        "oTableTools": {
                            "aButtons": [
                                {
                                    "sExtends": "download",
                                    "sButtonText": "Export Data",
                                    "sUrl": service_datagrid_url,
                                    sButtonClass: "save-collection"
                                }
                            ]
                        },
                        "fnDrawCallback": function() {
                           $(".dataTables_wrapper select").select2({
                               minimumResultsForSearch: -1
                           });
                         }

                        });                          
                    });
                    

                $('#service_submit').trigger('click');
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
@stop