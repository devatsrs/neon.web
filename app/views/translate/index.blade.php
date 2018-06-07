@extends('layout.main')

@section('content')


    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li class="active">
            <strong>Translate</strong>
        </li>
    </ol>
    <h3>Translate</h3>

    <div class="row" style="margin-top: 5px;">
        <div class="col-md-12">
            <form role="form" id="language-search" method="get"  action="{{URL::to('translate/search')}}" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
                <div class="panel panel-primary" data-collapsed="0">
                    <div class="panel-body">
                        <label class="col-sm-1 control-label">Language</label>
                        <div class="col-sm-3">
                            {{ddl_language("language", "user_language", Translation::$default_lang_ISOcode)}}
                        </div>

                        <div class="text-right" >
                            {{--add new table button--}}
                            @if(intval($global_admin))
                            <a class="btn btn-primary btn-sm btn-icon icon-left" id="set_new_label" href="javascript:;" data-toggle="modal" data-target="#set_new_system_name_model">
                                <i class="entypo-plus"></i>
                                Add Label
                            </a>
                            @endif
                            <a class="btn btn-primary btn-sm btn-icon icon-left" id="save_trnslation" href="javascript:;"  data-loading-text="Loading...">
                                <i class="entypo-floppy"></i>
                                Save
                            </a>
                            <a class="btn btn-primary btn-sm btn-icon icon-left" onclick="refresh_label(this);" href="javascript:;" >
                                <i class="entypo-arrows-ccw"></i>
                                Refresh
                            </a>
                        </div>

                       {{-- <div style="text-align: right;padding:10px 0 ">
                            <a class="btn btn-primary btn-sm btn-icon icon-left" id="bulk_set_vendor_rate" href="javascript:;">
                                <i class="entypo-floppy"></i>
                                Add New
                            </a>
                            <a class="btn btn-danger btn-sm btn-icon icon-left" id="changeSelectedVendorRates" href="javascript:;">
                                <i class="entypo-trash"></i>
                                Delete
                            </a>
                        </div>--}}
                    </div>
                </div>
            </form>
        </div>
    </div>


    <table class="table table-bordered datatable loading" id="table-4">
        <thead>
        <tr>
            <th>System Name</th>
            <th>English</th>
            <th>Translation</th>
        </tr>
        </thead>
        <tbody>

        </tbody>
    </table>

    <script type="text/javascript">
        var language;
        var $searchFilter = {};
        var checked='';
        var list_fields  = ['VendorRateID','language','Description','ConnectionFee','Interval1','IntervalN','Rate','EffectiveDate','updated_at','updated_by'];
        var table="";
        jQuery(document).ready(function($) {
            languageTableBind();

            $("#language").change(function(){
                rebindLanguageTable();
            });

            $("#new_system_name_from").submit(function (e) {
                e.preventDefault();
                var system_name = $(this).find("[name='system_name']").val();
                var en_word= $(this).find("[name='en_word']").val();
                if(system_name.trim() !="" && en_word!="" ){
                    $("#new_system_name_from").find(".save.btn").button('loading');
                    $.ajax({
                        url: $(this).attr("action"), //Server script to process data
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            var $form=$("#new_system_name_from")
                            $form.find(".save.btn").button('reset');
                            if (response.status == 'success') {
                                rebindLanguageTable();
                                $form.find("[name='system_name']").val("");
                                $form.find("[name='en_word']").val("");
                                toastr.success(response.message, "Success", toastr_opts);
                            } else {
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                        },
                        data: $(this).serialize(),
                        cache: false

                    });
                }else{
                    toastr.error("Fill All Data", "Error", toastr_opts);
                }

                return false;
            });

            $("#save_trnslation").click(function(){
                var listLabels=new Array();
                loading('.loading',1);
                $(this).button('loading');
                $(".text_language").each(function(){
                    var label=$(this).parent().find("label");
                    var text_val=$(this).val();
                    var language=$(this).attr("data-languages");
                    if(text_val==""){
                        $(this).val(label.html());
                        return false;
                    }
                    if(label.html() != text_val){
                        label.html(text_val);
                        if(language=="en"){
                            label.parents("tr").find("td:eq(1)").html(text_val);
                        }
                        listLabels.push({ "system_name" : label.attr("data-system-name"), "value" : text_val});
                    }
                });

                var postdata={ "language":$("#language").val(), "listLabels":listLabels}

                if(listLabels.length){
                    $.ajax({
                        url: baseurl + "/translate/update",
                        type: 'POST',
                        dataType: 'JSON',
                        success: function(response) {
                            loading('.loading',0);
                            $('#save_trnslation').button('reset');
                            if (response.status == 'success') {
                                toastr.success(response.message, "Success", toastr_opts);
                            } else {
                                toastr.error(response.message, "Error", toastr_opts);
                            }
                        },
                        data: postdata
                    });
                }else{
                    toastr.error("Please Change Translation Column", "Error", toastr_opts);
                    loading('.loading',0);
                    $('#save_trnslation').button('reset');
                }
            });

        });
        function rebindLanguageTable(){
            table.fnDestroy();
            languageTableBind();
        }
        function languageTableBind(){
            $searchFilter.language = language = $("#language").val();
            table =$("#table-4").dataTable( {
                "bDestroy": true, // Destroy when resubmit form
                "bAutoWidth": false,
                "bProcessing": true,
                "sAjaxSource": baseurl + "/translate/search_ajax_datagrid",
                "fnServerParams": function(aoData) {
                    aoData.push({"name": "Language", "value": language});
                    data_table_extra_params.length = 0;
                    data_table_extra_params.push( {"name": "Language", "value": language});
                },
                "sPaginationType": "bootstrap",
                "sDom": "<'row'<'col-xs-6 col-left '<'#selectcheckbox.col-xs-1'>'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "aoColumns":
                        [
                            {}, //1 system name
                            {}, //2 keyword
                            {}, //3 Translation
                        ],
                "oTableTools":
                {
                    "aButtons": [
                        {
                            "sExtends": "download",
                            "sButtonText": "EXCEL",
                            "sUrl": baseurl + "/translate/"+language+"/exports/xlsx",
                            sButtonClass: "save-collection btn-sm"
                        },
                        {
                            "sExtends": "download",
                            "sButtonText": "CSV",
                            "sUrl": baseurl + "/translate/"+language+"/exports/csv",
                            sButtonClass: "save-collection btn-sm"
                        }
                    ]
                },
                "fnDrawCallback": function() {

                    $(".text_delete").click(function(){
                        deleteLanguageData(this);
                    });
                    $(".dataTables_wrapper select").select2({
                        minimumResultsForSearch: -1
                    });
                }

            } );

        }
        function loading(table,bit){
            var panel = jQuery(table).closest('.loading');
            if(bit==1){
                blockUI(panel);
                panel.addClass('reloading');
            }else{
                unblockUI(panel);
                panel.removeClass('reloading');
            }
        }
        function deleteLanguageData(ele){
            if (!confirm("Are you sure?")) {
                return ;
            }
            var label=$(ele).parent().find("label");
            var language=$(ele).attr("data-languages");
            var post_data = { "language" : language, "system_name" : label.attr("data-system-name")};
            $(ele).parents("tr").hide();
            $.ajax({
                url: baseurl + "/translate/single_delete",
                type: 'POST',
                dataType: 'JSON',
                success: function(response) {
                    if (response.status == 'success') {
                        toastr.success(response.message, "Success", toastr_opts);
                    } else {
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                },
                data: post_data
            });

        }
        function refresh_label(ele){
            $(ele).button('loading');
            loading('.loading',1);
            $.ajax({
                url: baseurl + "/translate/refresh_label",
                type: 'POST',
                dataType: 'JSON',
                success: function(response) {
                    loading('.loading',0);
                    $(ele).button('reset');
                    if (response.status == 'success') {
                        toastr.success(response.message, "Success", toastr_opts);
                        rebindLanguageTable();
                    } else {
                        toastr.error(response.message, "Error", toastr_opts);
                    }
                }
            });
        }
    </script>

    <style>
        #table-4_filter label{
            display: block !important;
            margin-right:110px;
        }
        .dataTables_wrapper .export-data{
             right: 30px !important;
         }
    </style>
@stop

@section('footer_ext')
    @parent

    <div class="modal fade" id="set_new_system_name_model">
        <div class="modal-dialog">
            <div class="modal-content">

                <form id="new_system_name_from" method="post" action="{{URL::to('translate/new_system_name')}}">

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Set New Label</h4>
                    </div>

                    <div class="modal-body">
                        <div class="row">

                            <div class="col-md-6">

                                <div class="form-group">
                                    <label for="field-5" class="control-label">System Name *</label>

                                    <input type="text" name="system_name" class="form-control" placeholder="" required>

                                </div>

                            </div>

                            <div class="col-md-6">

                                <div class="form-group">
                                    <label for="field-5" class="control-label">English Word *</label>

                                    <input type="text" name="en_word" class="form-control" placeholder="" required>

                                </div>

                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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

