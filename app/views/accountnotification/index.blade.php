<style>
#table-notification_processing{
    position: absolute;
}
</style>
<div class="panel panel-primary" data-collapsed="0">
    <div class="panel-heading">
        <div class="panel-title">
            Notifications
        </div>
        <div class="panel-options">
            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
        </div>
    </div>
    <div class="panel-body">
         <div class="text-right">
              <a  id="add-notification" class=" btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-plus"></i>Add Notification</a>
              <div class="clear clearfix"><br></div>
        </div>
        <div id="notification_filter" method="get" action="#" >
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
                                            <label for="field-1" class="col-sm-1 control-label">Type</label>
                                            <div class="col-sm-2">
                                               {{Form::select('NotificationType',$notificationType,'',array("class"=>"select2 Notification_Type_dropdown"))}}
                                            </div>
                                        </div>
                                        <p style="text-align: right;">
                                            <button class="btn btn-primary btn-sm btn-icon icon-left" id="notification_submit">
                                                <i class="entypo-search"></i>
                                                Search
                                            </button>
                                        </p>
                                    </div>
                                </div>
        </div>
        <table id="table-notification" class="table table-bordered table-hover responsive">
            <thead>
            <tr>
                <th width="20%">Notification</th>
                <th width="30%">Email Addresses</th>
                <th width="10%">Created Date</th>
                <th width="10%">Created By</th>
                <th width="20%">Action</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<script type="text/javascript">
    /**
    * JQuery Plugin for dataTable
    * */
    //var list_fields_activity  = ['Notification_ProductID','Notification_Description'];
    var NotificationType = JSON.parse('{{json_encode(AccountNotification::$type)}}');
    var data_table_char;
    var accountID={{$account->AccountID}};
    var update_new_url;
    var postdata;
    var $search = {};

    jQuery(document).ready(function ($) {
       var data_table_char;
       var list_fields  = ["NotificationType","EmailAddresses","created_at","CreatedBy","AccountNotificationID"];
       var notification_add_url = baseurl + "/accounts/{{$account->AccountID}}/notification/store";
       var notification_edit_url = baseurl + "/accounts/{{$account->AccountID}}/notification/{id}/update";
       var notification_delete_url = baseurl + "/accounts/{{$account->AccountID}}/notification/{id}/delete";
       var notification_datagrid_url = baseurl + "/accounts/{{$account->AccountID}}/notification/ajax_datagrid";

        $search.NotificationType = $('#notification_filter [name="NotificationType"]').val();

        data_table_char = $("#table-notification").dataTable({
            "bDestroy": true,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": notification_datagrid_url,
            "fnServerParams": function (aoData) {
                        aoData.push({"name": "accountID", "value": accountID},
                                {"name": "NotificationType", "value": $search.NotificationType});

                        data_table_extra_params.length = 0;
                        data_table_extra_params.push({"name": "accountID", "value": accountID},
                                {"name": "NotificationType", "value": $search.NotificationType});

                    },
            "bPaginate": false,
            "iDisplayLength": '{{Config::get('app.pageSize')}}',
            "sPaginationType": "bootstrap",
            "aaSorting": [[0, 'asc']],
            "sDom": "<'row'r>",
            "aoColumns": [
                {"bSortable": true,
                    mRender:function(id,type,full){
                        return NotificationType[id];
                    }

                },  // 0 Notification
                {"bSortable": true},  // 1 Email Addresses
                {"bSortable": true},  // 2 Created At
                {"bSortable": true},  // 3 Created By
                {                        // 9 Action
                    "bSortable": false,
                    mRender: function (id, type, full) {
                        action = '<div class = "hiddenRowData" >';
                        for (var i = 0; i < list_fields.length; i++) {
                            action += '<input disabled type = "hidden"  name = "' + list_fields[i] + '"       value = "' + full[i] + '" / >';
                        }
                        action += '</div>';
                        action += ' <a href="' + notification_edit_url.replace("{id}", id) + '" class="edit-notification btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit </a>'
                        action += ' <a href="' + notification_delete_url.replace("{id}", id) + '" class="delete-notification btn btn-danger btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Delete </a>'
                        return action;
                    }
                }
            ],
            "oTableTools": {
                "aButtons": [
                    {
                        "sExtends": "download",
                        "sButtonText": "Export Data",
                        "sUrl": notification_datagrid_url,
                        sButtonClass: "save-collection"
                    }
                ]
            },
            "fnDrawCallback": function () {
                $(".dataTables_wrapper select").select2({
                    minimumResultsForSearch: -1
                });
            }
        });
        $("#notification_submit").click(function(e) {

            e.preventDefault();
            public_vars.$body = $("body");
            $search.NotificationType = $('#notification_filter [name="NotificationType"]').val();
            data_table_char.fnFilter('', 0);
            return false;
        });
                
        $('#notification_submit').trigger('click');
        //inst.myMethod('I am a method');
        $('#add-notification').click(function(ev){
            ev.preventDefault();
            $('#notification-form').trigger("reset");
            $('#modal-notification h4').html('Add Notification');
            $("#notification-form [name='NotificationEmailAddresses']").val('');
            $("#notification-form [name='NotificationType']").selectBoxIt().data("selectBox-selectBoxIt").selectOption('');
            $('.tax').removeClass('hidden');

            $('#notification-form').attr("action",notification_add_url);
            $('#modal-notification').modal('show');
        });
        $('table tbody').on('click', '.edit-notification', function (ev) {
            ev.preventDefault();
            $('#notification-form').trigger("reset");
            var edit_url  = $(this).attr("href");
            $('#notification-form').attr("action",edit_url);
            $('#modal-notification h4').html('Edit Notification');
            var cur_obj = $(this).prev("div.hiddenRowData");
            for(var i = 0 ; i< list_fields.length; i++){
                $("#notification-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                if(list_fields[i] == 'NotificationType'){
                    $("#notification-form [name='"+list_fields[i]+"']").selectBoxIt().data("selectBox-selectBoxIt").selectOption(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                }
            }
            $('#modal-notification').modal('show');
        });
        $('table tbody').on('click', '.delete-notification', function (ev) {
                ev.preventDefault();
                result = confirm("Are you Sure?");
               if(result){
                   var delete_url  = $(this).attr("href");
                   submit_ajax_datatable( delete_url,"",0,data_table_char);
                   data_table_char.fnFilter('', 0);
               }
               return false;
        });

       $("#notification-form").submit(function(e){
           e.preventDefault();
           var _url  = $(this).attr("action");
           submit_ajax_datatable(_url,$(this).serialize(),0,data_table_char);
           data_table_char.fnFilter('', 0);
       });
    });
</script>
@section('footer_ext')
@parent

<div class="modal fade in" id="modal-notification">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="notification-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Additional Charges</h4>
                </div>
                <div class="modal-body">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="field-5" class="control-label">Type</label>
                            {{Form::select('NotificationType',$notificationType,'',array("class"=>"selectboxit product_dropdown"))}}
                            <input type="hidden" name="AccountNotificationID" />
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="field-5" class="control-label">Email Addresses</label>
                            <input type="text" name="EmailAddresses" class="form-control" value="" />
                        </div>
                    </div>
                </div>
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