<div class="panel panel-primary" data-collapsed="0">
    <div class="panel-heading">
        <div class="panel-title">
            Emails
        </div>
        <div class="panel-options">
            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
        </div>
    </div>
    <div class="panel-body">
        <div class="text-right">
            <a  id="SendMail" class=" btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-plus"></i>Send Email</a>
            <div class="clear clearfix"><br></div>
        </div>
            <table class="table table-bordered datatable" id="table-email_log">
                <thead>
                <tr>
                    <th width="15%">Email From</th>
                    <th width="15%">Email To</th>
                    <th width="15%">Subject</th>
                    <th width="15%">Date</th>
                    <th width="15%">Sent by</th>
                    <th width="25%">Action</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <script type="text/javascript">
                var $searchFilter = {};
                var update_new_url;
                var postdata;
                jQuery(document).ready(function ($) {
                        public_vars.$body = $("body");
                    data_table_email_log = $("#table-email_log").dataTable({
                        "bDestroy": true,
                        "bProcessing": true,
                        "bServerSide": true,
                        "sAjaxSource": baseurl + "/accounts/{{$account->AccountID}}/activities/ajax_datagrid_email_log",
                        "fnServerParams": function (aoData) {
                        },
                        "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
                        "sPaginationType": "bootstrap",
                        "sDom": "<'row'r>",
                        "aaSorting": [[0, 'asc']],
                        "aoColumns": [
                            {"bSortable": false},  // 1 from
                            {"bSortable": false},  // 2 to
                            {"bSortable": false},  // 3 Subject
                            {"bSortable": false},  // 4 Date
                            {"bSortable": false},  // 5 Sent By
                            {                       //  4  Action
                                "bSortable": false,
                                mRender: function (id, type, full) {
                                    var delete_ = "{{ URL::to('/accounts/'.$account->AccountID.'/activities/{id}/delete_email_log/')}}";
                                    var view_ = "{{ URL::to('/accounts/'.$account->AccountID.'/activities/{id}/view_email_log/')}}";
                                    delete_ = delete_.replace('{id}', id);
                                    view_ = view_.replace('{id}', id);
                                    action = ' <a href="' + view_ + '" title="View" class="view-Email btn btn-default btn-sm"><i class="fa fa-eye"></i></a>'
                                    action += ' <a href="' + delete_ + '" title="Delete" class="btn delete-Email btn-danger btn-sm"><i class="entypo-trash"></i></a>'
                                    return action;
                                }
                            }
                        ],
                        "oTableTools": {
                            "aButtons": [
                                {
                                    "sExtends": "download",
                                    "sButtonText": "Export Data",
                                    "sUrl": baseurl + "/products/ajax_datagrid", //baseurl + "/generate_xls.php",
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

                        // Replace Checboxes
                        $(".pagination a").click(function (ev) {
                            replaceCheckboxes();
                        });

                        $('table tbody').on('click', '.view-Email', function (ev) {
                            ev.preventDefault();
                            $('#viewmessage-form').trigger("reset");
                            $.ajax({
                                url: $(this).attr("href"),  //Server script to process data
                                type: 'POST',
                                dataType: 'json',
                                success: function (response) {
                                    $("#emailfrom").text(response.Emailfrom);
                                    $("#emailto").text(response.EmailTo);
                                    $("#Date").text(response.created_at);
                                    $("#message").html(response.Message);
                                },
                                // Form data
                                data: '',
                                //Options to tell jQuery not to process data or worry about content-type.
                                cache: false
                            });
                            $('#modal-viewmessage').modal('show');
                        });


                    $('#SendMail').click(function (ev) {
                        ev.preventDefault();
                        $("#SendEmail-form")[0].reset();
                        $('#modal-SendEmail h4').html('Add New Email');
                        $('#modal-SendEmail').modal('show');
                    });


                    $('#SendEmail-form').submit(function(e){
                        e.preventDefault();
                        var url = "{{ URL::to('/accounts/'.$account->AccountID.'/activities/sendemail/')}}";
                        $.ajax({
                            url: url,  //Server script to process data
                            type: 'POST',
                            dataType: 'json',
                            success: function (response) {
                                if(response.status =='success'){
                                    toastr.success(response.message, "Success", toastr_opts);
                                    $('#modal-SendEmail').modal('hide');
                                    data_table_email_log.fnFilter('', 0);
                                }else{
                                    toastr.error(response.message, "Error", toastr_opts);
                                }
                                $("#send-email-button").button('reset');
                            },
                            // Form data
                            data: $('#SendEmail-form').serialize(),
                            //Options to tell jQuery not to process data or worry about content-type.
                            cache: false
                        });
                    });

                    // Replace Checboxes
                    $(".pagination a").click(function (ev) {
                        replaceCheckboxes();
                    });

                    $('body').on('click', '.delete-Email', function (e) {
                        e.preventDefault();

                        response = confirm('Are you sure?');
                        if (response) {

                            $.ajax({
                                url: $(this).attr("href"),
                                type: 'POST',
                                dataType: 'json',
                                success: function (response) {
                                    $(".btn.delete").button('reset');
                                    if (response.status == 'success') {
                                        toastr.success(response.message, "Success", toastr_opts);
                                        data_table_email_log.fnFilter('', 0);
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
                        }
                        return false;
                    });

                    $('#modal-SendEmail').on('shown.bs.modal', function(event){
                        var modal = $('#modal-SendEmail');
                        show_summernote(modal.find('.message'),{"Tickets":true,});
                    });

                    $('#modal-SendEmail').on('hidden.bs.modal', function(event){
                        var modal = $('#modal-SendEmail');
                        modal.find('.message').show();
                    });

                });
            </script>

            @include('includes.errors')
            @include('includes.success')

        </div>
    </div>

</div>
@section('footer_ext')
    @parent

    <div class="modal fade in" id="modal-viewmessage">
        <div class="modal-dialog" style="width: 40%;">
            <div class="modal-content">
                <form id="viewmessage-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">View Email</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-Group">
                                <br />
                                <label for="field-1" class="col-sm-3 control-label">Email from</label>
                                <div class="col-sm-5" id="emailfrom">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-Group">
                                <br />
                                <label for="field-1" class="col-sm-3 control-label">Email to</label>
                                <div class="col-sm-5" id="emailto">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-Group">
                                <br />
                                <label for="field-1" class="col-sm-3 control-label">Date</label>
                                <div class="col-sm-5" id="Date">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-Group">
                                <br />
                                <label for="field-1" class="col-sm-3 control-label">Message</label>
                                <div class="col-sm-9" id="message">
                                </div>
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

    <div class="modal fade" id="modal-SendEmail">
        <div class="modal-dialog" style="width: 80%;">
            <div class="modal-content">
                <form id="SendEmail-form" method="post" action="" enctype="multipart/form-data">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add new mail</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-Group">
                                <br />
                                <label for="field-1" class="col-sm-2 control-label">Subject</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" id="subject" name="Subject" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-Group">
                                <br />
                                <label for="field-1" class="col-sm-2 control-label">Message</label>
                                <div class="col-sm-10">
                                    <textarea class="form-control message" rows="18" name="Message"></textarea>
                                </div>
                            </div>
                        </div>
                        <!--<div class="row">
                            <div class="form-group">
                                <br/>
                                <label for="field-5" class="col-sm-2 control-label">Attachment</label>
                                <div class="col-sm-10">
                                    <input type="file" id="attachment"  name="attachment" class="form-control file2 inline btn btn-primary" data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i>&nbsp;   Browse" />
                                </div>
                            </div>
                        </div>-->
                    </div>
                    <div class="modal-footer">
                        <button id="send-email-button" type="submit"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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
@stop


