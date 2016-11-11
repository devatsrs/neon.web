<link rel="stylesheet" href="<?php echo URL::to('/'); ?>/assets/js/wysihtml5/bootstrap-wysihtml5.css">
<script src="<?php echo URL::to('/'); ?>/assets/js/wysihtml5/wysihtml5-0.4.0pre.min.js"></script>
<script src="<?php echo URL::to('/'); ?>/assets/js/wysihtml5/bootstrap-wysihtml5.js"></script>
<script>
    $(document).ready(function ($) {
        $('#add-new-template-form').submit(function(e){
            e.preventDefault();
            var url = baseurl + '/email_template/storetemplate';
            ajax_update(url,$('#add-new-template-form').serialize());
        })

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
                    if (typeof data_table != 'undefined') {
                        data_table.fnFilter('', 0);
                    }else if($('#add-new-template-form [name="targetElement"]').val() != ''){
                        var targetElement = $($('#add-new-template-form [name="targetElement"]').val());
                        if(targetElement.length>0){
                            $.each(targetElement,function(key,el){
                                rebuildSelect2($(el),response.data,'Select');
                                $(el).val(response.newcreated.TemplateID);
                                $(el).trigger('change');
                            });
                        }else{
                            rebuildSelect2(targetElement,response.data,'Select');
                            $(el).val(response.newcreated.TemplateID);
                            $(el).trigger('change');
                        }
                    }
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
                        <input type="hidden" name="targetElement" />
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