@extends('layout.main_only_sidebar')
@section('content')

    <!-- Header text-->
<style >
    .white {
        color: #fff;
        font-size: 16px;
    }
    /* Misc */
    hr {
        display: block;
        height: 1px;
        border: 0;
        margin: 1em 0; padding: 0;
    }
    /* Navigation pills */
    .nav-pills > li.active > a,
    .nav-pills > li.active > a:hover,
    .nav-pills > li.active > a:focus {
        color: #2C3E50;
        background-color: #F2F3F4;
        margin-left: 5px;
        line-height: 15px;
    }
    .nav-pills > li > a {
        color: #BFC6C6;
        background-color: #5A6465;
        margin-left: 5px;
        line-height: 15px;
    }
    .nav-pills > li > a:hover,
    .nav-pills > li > a:focus {
        color: #2C3E50;
        background-color: #F2F3F4;
        margin-left: 5px;
        line-height: 15px;
    }
    /* Page sections */

    .page_section h3 {
        font-size: 20px;
        opacity: 0.6;
    }

    /* Incident */

    .incident_time {
        font-size: 12px;
        opacity: 0.5;
    }
    .make_round {
    }
    .make_round_bottom_only {
        border-bottom-left-radius: 4px;
        border-bottom-right-radius: 4px;
    }
</style>

<div class="row">
    <div class="col-md-12">
        <div class="alert alert-success">
            <strong>Latest Updates!</strong>
            @if(!empty($LastUpdated))
                Updated {{\Carbon\Carbon::createFromTimeStamp(strtotime($LastUpdated))->diffForHumans() }}
            @else
                 No Updates Found.
            @endif
        </div>
    </div>
</div>
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
                    <div class="panel-heading make_round post-success">
                        <div class="panel-title white">

                        </div>

                        <div class="panel-options ">
                            <a href="#" class="white delete_post" data-id=""><i class="entypo-trash"></i></a>
                        </div>
                    </div>
                    <div class="panel-body section_border_1 no_top_border make_round make_round_bottom_only">
                        <div class="form-group">
                            <label for="field-1" class="col-md-2 control-label">Title*</label>
                            <div class="col-md-4">
                                <input type="text" name="Title" class="form-control" id="field-1" placeholder="" value="" />
                            </div>

                            <label for="field-1" class="col-md-2 control-label">Type*</label>
                            <div class="col-md-4">
                                {{Form::select('Type',array('post-success'=>'Success','post-error'=>'Error','post-info'=>'Information','post-warning'=>'Warning'),'',array("class"=>"select2 post_type"))}}
                            </div>
                            <div class="col-xs-12 col-md-12">
                                <label for="subject">Detail *</label>
                                <textarea class="form-control" name="Detail" id="txtNote" rows="5" placeholder="Add Note..."></textarea>
                            </div>
                        </div>
                        <input type="hidden" name="NoticeBoardPostID" value="0">

                        <div class="row">
                            <div class="col-md-12"><strong class="incident_time"></strong>
                                <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left save_post pull-right" data-loading-text="loading">
                                    <i class="entypo-floppy"></i>
                                    Save
                                </button>
                            </div>
                        </div>

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

        load_more_updates();
        //$('.save_post').unbind('click');
        $(document).on('click', '.add_post', function(e) {
            e.preventDefault();
            $('#post_form_0').trigger("reset");
            $('.add_new_post').show();
        });
        //$('.delete_post').unbind('click');
        $(document).on('click', '.delete_post', function(e) {
            e.preventDefault();
            var NoticeBoardPostID = $(this).attr('data-id');
            if (confirm("Are you sure?")) {
                $(this).button('loading');
                ajax_json(baseurl + '/delete_post/' + NoticeBoardPostID, $(this).serialize(), function (response) {
                    $(".btn").button('reset');
                    if (response.status == 'success') {
                        toastr.success(response.message, "Success", toastr_opts);
                        $('#post_form_' + NoticeBoardPostID).parents('.page_section').fadeOut().remove();
                    } else {
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                });
            }

        });
        //$('.save_post').unbind('click');
        $(document).on('click', '.save_post', function(e) {
            e.preventDefault();
            $(this).button('loading');
            $('#'+$(this).parents('form').attr('id')).submit();
        });
        //$('.post_form').unbind('submit');
        $(document).on('submit', '.post_form', function(e) {
            e.preventDefault();
            ajax_json(baseurl + '/save_post', $(this).serialize(), function (response) {
                $(".btn").button('reset');
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
            });

        });
        $(document).on('change', '.post_type', function(e) {
            e.preventDefault();
            $(this).parents('form').find('.panel-heading').removeClass('post-success');
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

                        var html_end = '<div>Updates completed</div>';
                        $("#timeline-ul").append(html_end);
                        scroll_more = 0;
                        $('div#last_msg_loader').empty();
                        console.log("Results completed");
                        return false;
                    }
                    ShowToastr("error", response_json.message);
                } else {
                    $("#timeline-ul").append(response1);
                }
                $('div#last_msg_loader').empty();
            }
        });
    }
</script>

@stop
