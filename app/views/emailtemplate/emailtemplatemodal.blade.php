<link rel="stylesheet" href="<?php echo URL::to('/'); ?>/assets/js/wysihtml5/bootstrap-wysihtml5.css">
<script src="<?php echo URL::to('/'); ?>/assets/js/wysihtml5/wysihtml5-0.4.0pre.min.js"></script>
<script src="<?php echo URL::to('/'); ?>/assets/js/wysihtml5/bootstrap-wysihtml5.js"></script>
<script>
    $(document).ready(function ($) {
        $('#add-new-template-form').submit(function(e){
            e.preventDefault();
            var templateID = $("#add-new-template-form [name='TemplateID']").val();
            if( typeof templateID != 'undefined' && templateID != ''){
                update_new_url = baseurl + '/email_template/'+templateID+'/update';
            }else{
                update_new_url = baseurl + '/email_template/store';
            }
            ajax_update(update_new_url,$('#add-new-template-form').serialize());
        });

        $('#add-new-modal-template').on('shown.bs.modal', function(event){
            var modal = $(this);
            modal.find('.message').wysihtml5({
                "font-styles": true,
                "emphasis": true,
                "leadoptions":false,
                "invoiceoptions":true,
                "Crm":false,
                "lists": true,
                "html": true,
                "link": true,
                "image": true,
                "color": true
            });
        });

        $('#add-new-modal-template').on('hidden.bs.modal', function(event){
            var modal = $(this);
            modal.find('.wysihtml5-sandbox, .wysihtml5-toolbar').remove();
            modal.find('.message').show();
        });
    });

    function ajax_update(fullurl,data) {
        $.ajax({
            url: fullurl, //Server script to process data
            type: 'POST',
            dataType: 'json',
            success: function (response) {
                $("#template-update").button('reset');
                $(".btn").button('reset');
                $('#modal-template').modal('hide');

                if (response.status == 'success') {
                    $('#add-new-modal-template').modal('hide');
                    toastr.success(response.message, "Success", toastr_opts);
                    $('select.add-new-template-dp').each(function(key,el){
                        if($(el).attr('data-active') == 1) {
                            var newState = new Option(response.newcreated.TemplateName, response.newcreated.TemplateID, true, true);
                        }else{
                            var newState = new Option(response.newcreated.TemplateName, response.newcreated.TemplateID, false, false);
                        }
                        // Append it to the select
                        $(el).append(newState).trigger('change');
                        $(el).append($(el).find("option:gt(1)").sort(function (a, b) {
                            return a.text == b.text ? 0 : a.text < b.text ? -1 : 1;
                        }));
                        template_dp_html = '<select class="select2 select2add small form-control visible select2-offscreen" name="InvoiceReminder[TemplateID][]" tabindex="-1" data-active="0">'+$(el).html().replace('<option data-image="1" value="select2-add" disabled="disabled">Add</option>','')+'</select>';
                    });

                } else {
                    toastr.error(response.message, "Error", toastr_opts);
                }
            },
            data: data,
            //Options to tell jQuery not to process data or worry about content-type.
            cache: false
        });
    }
</script>

@section('footer_ext')
    @parent
    <div class="modal fade" id="add-new-modal-template">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="add-new-template-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add New Template</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group">
                                <label for="field-1" class="control-label col-sm-2">Template Name</label>
                                <div class="col-sm-4">
                                    <input type="text" name="TemplateName" class="form-control" id="field-1" placeholder="">
                                    <input type="hidden" name="TemplateID" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group">
                                <br />
                                <label for="field-1" class="control-label col-sm-2">Template Type</label>
                                <div class="col-sm-4">
                                    {{Form::select('Type',$type,'',array("class"=>"select2 small"))}}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group">
                                <br />
                                <label for="field-2" class="control-label col-sm-2">Subject</label>
                                <div class="col-sm-4">
                                    <input type="text" name="Subject" class="form-control" id="field-2" placeholder="">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-Group">
                                    <br />
                                    <label for="field-3" class="control-label">Email Template Body</label>
                                    <textarea class="form-control message" rows="18" id="field-3" name="TemplateBody"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-Group">
                                <br/>
                                <label class="col-sm-2 control-label">Email Template Privacy</label>
                                <div class="col-sm-4">
                                    {{Form::select('Email_template_privacy',$privacy,'',array("class"=>"select2 small"))}}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">

                        <button type="submit" id="template-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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