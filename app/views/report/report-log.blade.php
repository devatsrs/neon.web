
<div class="modal fade in" id="modal-report-log">
    <div class="modal-dialog" style="width: 70%">
        <div class="modal-content">
            <form id="report-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Log</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Name :</label>
                                <div name="Name"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Sent At : </label>
                                <div name="send_at"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Subject : </label>
                                <div name="Subject"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Message : </label>
                                <div name="Message"></div>
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
<script type="text/javascript">
    jQuery(document).ready(function($) {
        $('table tbody').on('click', '.view-report', function (ev) {
            ev.preventDefault();
            $('#modal-report-log h4').html('View Log');
            var cur_obj = $(this).prev("div.hiddenRowData");
            for (var i = 0; i < list_fields_index.length; i++) {
                $("#report-form [name='" + list_fields_index[i] + "']").html(cur_obj.find("[name='" + list_fields_index[i] + "']").html());
            }
            $('#modal-report-log').modal('show');
        });
    });
</script>