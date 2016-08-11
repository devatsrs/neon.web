@extends('layout.main')

@section('content')

    <ol class="breadcrumb bc-3" xmlns="http://www.w3.org/1999/html">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li class="active">
            <a href="javascript:void(0)">Server Info</a>
        </li>
    </ol>

    <style>
        #ServerInfoTab {
            margin:0 0 1px -11px;
        }
        #ServerInfoTab .btn-xs{
            font-size: 8px;
            padding: 1px 3px;
            margin-bottom:3px;
            margin-left:3px;
        }
        #ServerInfoTab span.title{
            font-size: 15px;
        }
        #ServerInfoTabContent .tab-pane{
            height: 1024px;
        }
         iframe{border:0px;}

    </style>

    <h3>Server Info</h3>
    <div class="tab-content" style="height: auto;">
        <div class="tab-pane active" id="customer_rate_tab_content">
            <div class="clear"></div>
            <br>
            @if(User::checkCategoryPermission('Notification','Add'))
                <p style="text-align: right;">
                    <a class=" btn btn-primary btn-sm btn-icon icon-left" id="add-server">
                        <i class="entypo-plus"></i>
                        Add Server
                    </a>
                </p>
            @endif
            <!-- Nav tabs from Bootstrap 3 -->
            <ul id="ServerInfoTab" class="nav nav-tabs" role="tablist">
                <!--<li id="li1" class="active">
                    <a data-toggle="tab" role="tab" href="#tab1">
                        <span class="title">Code Desk</span>
                        <button class="btn btn-default btn-xs edit" type="button"><span class="entypo-pencil"></span></button>
                        <button class="btn btn-danger btn-xs delete" type="button"><span class="entypo-cancel"></span></button>
                    </a>
                </li>-->
            </ul>
            <!-- Tab panes from Bootstrap 3 -->
            <div id="ServerInfoTabContent" class="tab-content" style="height: 1024px;">
               <!-- <div class="tab-pane fade in active" id="tab1"><p>Content tab 1</p></div> -->
            </div>

            <script type="text/javascript">
                var list_fields  = ["ServerInfoID","ServerInfoTitle","ServerInfoUrl"];
                $(document).ready(function() {
                    $('.btn').button('reset');
                    getajaxData();
                    $('#add-server').on('click', function (e) {
                        e.preventDefault();
                        $('#serverinfo-form').trigger("reset");
                        for(var i = 0 ; i< list_fields.length; i++){
                            $("#serverinfo-form [name='"+list_fields[i]+"']").val('');
                        }
                        $('#modal-serverinfo h4').html('Add Server Info');
                        $('#modal-serverinfo').modal('show');
                    });

                    $(document).on('click','.edit', function (e) {
                        e.preventDefault();
                        var cur_obj = $(this).siblings('.hiddenRowData');
                        for(var i = 0 ; i< list_fields.length; i++){
                            $("#serverinfo-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                        }
                        $('#modal-serverinfo h4').html('Edit Server Info');
                        $('#modal-serverinfo').modal('show');
                    });

                    $(document).on('click','.delete', function (e) {
                        e.preventDefault();
                        var cur_obj = $(this).siblings('.hiddenRowData');
                        var ServerInfoID = cur_obj.find('input[name="ServerInfoID"]').val();
                        var url = baseurl + '/serverinfo/'+ServerInfoID+'/delete';
                        if (confirm("Are you sure?")) {
                            $.ajax({
                                url: url,
                                type: 'POST',
                                dataType: 'json',
                                success: function (response) {
                                    if (response.status == 'success') {
                                        toastr.success(response.message, "Success", toastr_opts);
                                        getajaxData();
                                    } else {
                                        toastr.error(response.message, "Error", toastr_opts);
                                    }
                                    $('.btn').button('reset');
                                },
                                // Form data
                                data: {ServerInfoID: ServerInfoID},
                                //Options to tell jQuery not to process data or worry about content-type.
                                cache: false,
                                contentType: false,
                                processData: false
                            });
                        }
                    });

                    $("#serverinfo-form").submit(function (e) {
                        e.preventDefault();
                        var ServerInfoID = $('#serverinfo-form [name="ServerInfoID"]').val();
                        if (ServerInfoID) {
                            var url = baseurl + '/serverinfo/'+ServerInfoID+'/update';
                        } else {
                            var url = baseurl + '/serverinfo/store';
                        }
                        var formData = new FormData($('#serverinfo-form')[0]);
                        $.ajax({
                            url: url,
                            type: 'POST',
                            dataType: 'json',
                            success: function (response) {
                                if (response.status == 'success') {
                                    toastr.success(response.message, "Success", toastr_opts);
                                    getajaxData();
                                } else {
                                    toastr.error(response.message, "Error", toastr_opts);
                                }
                                $('.btn').button('reset');
                                $('#modal-serverinfo').modal('hide');
                            },
                            // Form data
                            data: formData,
                            //Options to tell jQuery not to process data or worry about content-type.
                            cache: false,
                            contentType: false,
                            processData: false
                        });
                        //}
                    });

                    function getajaxData() {
                        $.ajax({
                            url: '{{URL::to('serverinfo/ajax_getdata')}}',  //Server script to process data
                            type: 'POST',
                            dataType: 'json',
                            success: function (response) {
                                if (response.status == 'success') {
                                    if (response.data) {
                                        $('ul#ServerInfoTab').empty();
                                        $('#ServerInfoTabContent').empty();
                                        $.each(response.data, function (index,item) {
                                            builtItem(item);
                                            $('ul#ServerInfoTab li a:first').click();
                                        })
                                    }
                                } else {
                                    toastr.error(response.message, "Error", toastr_opts);
                                }
                            },
                            // Form data
                            data: [],
                            //Options to tell jQuery not to process data or worry about content-type.
                            cache: false,
                            contentType: false,
                            processData: false
                        });
                    }

                    function builtItem(item) {
                        var html = '<li id="li' + item.ServerInfoID + '">';
                        html += '   <a href="#tab' + item.ServerInfoID + '" role="tab" data-toggle="tab">';
                        html += '       <span class="title">' + item.ServerInfoTitle + '</span>';
                        html += '       <div class="hiddenRowData hidden"><input type="hidden" name="ServerInfoID" value="' + item.ServerInfoID + '" /> <input type="hidden" name="ServerInfoTitle" value="' + item.ServerInfoTitle + '" /> <input type="hidden" name="ServerInfoUrl" value="' + item.ServerInfoUrl + '" /> </div>';
                        html += '       <button class="btn btn-default btn-xs edit" type="button"><span class="entypo-pencil"></span></button>';
                        html += '       <button class="btn btn-danger btn-xs delete" type="button"><span class="entypo-cancel"></span></button>';
                        html += '   </a>';
                        html += '</li>';
                        $('ul#ServerInfoTab').append(html);
                        var ifram = '<iframe width="100%;" scrolling="auto" height="100%;" src="' + item.ServerInfoUrl + '"></iframe>';
                        $('#ServerInfoTabContent').append('<div class="tab-pane fade" id="tab' + item.ServerInfoID + '">' + ifram + '</div>');
                    }
                });
            </script>

            @include('includes.errors')
            @include('includes.success')

        </div>
    </div>
@stop
@section('footer_ext')
    @parent

<div class="modal fade in" id="modal-serverinfo">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="serverinfo-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add Server Info</h4>
                </div>
                <div class="modal-body">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="field-5" class="control-label">Title</label>
                            <input type="text" name="ServerInfoTitle" class="form-control" value="" />
                            <input type="hidden" name="ServerInfoID" />
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="field-5" class="control-label">Server url</label>
                            <textarea name="ServerInfoUrl" class="form-control" value="" /></textarea>
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
