@extends($extends)
@section('content')

    <!-- Header text-->
<style >

    /* Misc */
    hr {
        display: block;
        height: 1px;
        border: 0;
        margin: 1em 0; padding: 0;
    }

    /* Page sections */
    .panel{
        margin-bottom: 5px !important;
    }
    .page_section h3 {
        font-size: 20px;
        opacity: 0.6;
    }

    /* Incident */

    .incident_time {
        font-size: 12px;
    }
    .make_round {
    }
    .make_round_bottom_only {
        border-bottom-left-radius: 4px;
        border-bottom-right-radius: 4px;
    }
</style>

@if(Session::get('customer') == 1)
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-success">
            <strong>@lang('routes.CUST_PANEL_PAGE_NOTIFICATIONS_MSG_LATEST_UPDATES')</strong>
            @if(!empty($LastUpdated))
                {{cus_lang("CUST_PANEL_PAGE_NOTICEBOARD_MSG_UPDATED")}} {{\Carbon\Carbon::createFromTimeStamp(strtotime($LastUpdated))->diffForHumans() }}
            @else
                 @lang('routes.CUST_PANEL_PAGE_NOTICEBOARD_MSG_NO_UPDATES_FOUND')
            @endif
        </div>
    </div>
</div>
@endif
<p style="text-align: right;">
@if(User::checkCategoryPermission('NoticeBoardPost','Add'))
    <a href="#" class="btn btn-primary add_post ">
        <i class="entypo-plus"></i>
        Add New
    </a>
@endif
</p>
@if(Session::get('customer') == 0)
<div class="add_new_post">
    <div class="row page_section incident">
        <div class="col-md-12" >
            <form id="post_form_0" method=""  action="" class="form-horizontal post_form form-groups-bordered validate" novalidate>
                <div class="panel panel-default make_round">
                    <div class="panel-heading make_round " data-rel="collapse" data-collapsed="1">
                        <div class="panel-title">

                        </div>
                        @if(User::checkCategoryPermission('NoticeBoardPost','Edit'))
                            <div class="panel-options ">
                                <a href="#" class="save_post" data-original-title="Save" title="" data-placement="top" data-toggle="tooltip"><i class="entypo-floppy"></i></a>
                            </div>
                        @endif
                    </div>
                    <div class="panel-body section_border_1 no_top_border make_round make_round_bottom_only">
                        <div class="row">
                            <label for="field-1" class="col-md-2 control-label">Title*</label>
                            <div class="col-md-4">
                                <input type="text" name="Title" class="form-control" id="field-1" placeholder="" value="" />
                            </div>

                            <label for="field-1" class="col-md-2 control-label">Type*</label>
                            <div class="col-md-4">
                                {{Form::select('Type',array('post-none'=>'None','post-error'=>'Error','post-info'=>'Information','post-warning'=>'Warning'),'',array("class"=>"select2 post_type"))}}
                            </div>
                            <div class="col-xs-12 col-md-12">
                                <label for="subject">Detail *</label>
                                <textarea class="form-control" name="Detail" id="txtNote" rows="5" placeholder="Add Note..."></textarea>
                            </div>
                        </div>
                        <input type="hidden" name="NoticeBoardPostID" value="0">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
<div id="timeline-ul">

</div>
<div id="last_msg_loader"></div>

<script>
    var scroll_more 	  =  		1;
    $(document).ready(function() {
        show_summerinvoicetemplate($("[name=Detail]"));
        load_more_updates();
        if(window.location.hash) {
            var hash = window.location.hash.substring(1); //Puts hash in variable, and removes the # character
            var $selected_panel = $('#post_form_'+hash).find('[data-rel="collapse"]').closest('.panel');
            if($selected_panel.length) {
                var $first_panel = $('#timeline-ul [data-rel="collapse"]:first').closest('.panel');
                $first_panel.children('.panel-body, .table').hide();
                $first_panel.attr('data-collapsed', 1);
                $selected_panel.children('.panel-body, .table').show();
                $selected_panel.attr('data-collapsed', 0);

            }
        }
        //$('.save_post').unbind('click');
        $(document).on('click', '[data-rel="collapse"]', function(ev)
        {
            ev.preventDefault();

            var $this = $(this),
                    $panel = $this.closest('.panel'),
                    $body = $panel.children('.panel-body, .table'),
                    do_collapse = !$panel.hasClass('panel-collapse');

            if ($panel.is('[data-collapsed="1"]'))
            {
                $panel.attr('data-collapsed', 0);
                $body.hide();
                do_collapse = false;
            }

            if (do_collapse)
            {
                $body.slideUp('normal', fit_main_content_height);
                $panel.addClass('panel-collapse');
            }
            else
            {
                $body.slideDown('normal', fit_main_content_height);
                $panel.removeClass('panel-collapse');
            }
        });

        $(document).on('click', '.add_post', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('#post_form_0').trigger("reset");
            $('.add_new_post').show();
        });
        //$('.delete_post').unbind('click');
        $(document).on('click', '.delete_post', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var NoticeBoardPostID = $(this).attr('data-id');
            if (confirm("Are you sure?")) {
                $(this).button('loading');
                ajax_json(baseurl + '/delete_post/' + NoticeBoardPostID, $(this).serialize(), function (response) {
                    $(".delete_post").button('reset');

                    if (response.status == 'success') {
                        toastr.success(response.message, "Success", toastr_opts);
                        $('#post_form_' + NoticeBoardPostID).parents('.page_section').fadeOut().remove();
                    } else {
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                    first_open();
                });
            }

        });
        //$('.save_post').unbind('click');
        $(document).on('click', '.save_post', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).button('loading');
            $('#'+$(this).parents('form').attr('id')).submit();
        });
        //$('.post_form').unbind('submit');
        $(document).on('submit', '.post_form', function(e) {
            e.preventDefault();
            ajax_json(baseurl + '/save_post', $(this).serialize(), function (response) {
                $(".save_post").button('reset');
                if (response.status == 'success') {
                    toastr.success(response.message, "Success", toastr_opts);
                    if(response.html != ''){
                        $('#post_form_0').trigger("reset");
                        $('.add_new_post').hide();
                        $("#timeline-ul").prepend(response.html)
                    }
                    if($("#timeline-ul .page_section").length == 0)
                    {
                        var html_end  ='Results completed';
                        $("#timeline-ul").append(html_end);
                    }
                } else {
                    toastr.error(response.message, "Error", toastr_opts);
                }

                $('select.select2').select2();
                $('select.select2').addClass('visible');
                first_open();
            });

        });
        $(document).on('change', '.post_type', function(e) {
            e.preventDefault();
            $(this).parents('form').find('.panel-heading').removeClass('post-none');
            $(this).parents('form').find('.panel-heading').removeClass('post-error');
            $(this).parents('form').find('.panel-heading').removeClass('post-info');
            $(this).parents('form').find('.panel-heading').removeClass('post-warning');
            $(this).parents('form').find('.panel-heading').first().addClass($(this).val());
        });
        $('.add_new_post').hide();
    });
    $(window).scroll(function(){
        if ($(window).scrollTop() == $(document).height() - $(window).height()){

            setTimeout(function() {
                load_more_updates();
            }, 1000);
        }
    });
    function load_more_updates() {
        if ($("#timeline-ul").length == 0) {
            return false;  //it doesn't exist
        }
        if (scroll_more == 0) {
            return false;
        }

        var count = $("#timeline-ul .page_section").length;
        var url_scroll = "{{ URL::to('get_mor_updates')}}";
        $('div#last_msg_loader').html('<img src="' + baseurl + '/assets/images/bigLoader.gif">');

        $.ajax({
            url: url_scroll + "?scrol=" + count,
            type: 'POST',
            dataType: 'html',
            async: false,
            data: '',
            success: function (response1) {
                if (isJson(response1)) {

                    var response_json = JSON.parse(response1);
                    if (response_json.scroll == 'end') {
                        if ($(".timeline-end").length > 0) {
                            scroll_more = 0;
                            return false;
                        }

                        //var html_end = '<div>Updates completed</div>';
                        //$("#timeline-ul").append(html_end);

                        scroll_more = 0;
                        $('div#last_msg_loader').empty();
                        console.log("Results completed");
                        return false;
                    }
                    ShowToastr("error", response_json.message);
                } else {
                    $("#timeline-ul").append(response1);
                }
                first_open();
                $('div#last_msg_loader').empty();
            }
        });
    }

    function first_open(){
        var $first_panel = $('#timeline-ul [data-rel="collapse"]:first').closest('.panel');
        $first_panel.children('.panel-body, .table').show();
        $first_panel.attr('data-collapsed', 0);
    }
</script>

@stop
