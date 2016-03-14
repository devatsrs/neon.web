@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>CRM Template</strong>
    </li>
</ol>
<h3>Templates</h3>

@include('includes.errors')
@include('includes.success')

<div class="row">
    <div class="col-md-12">
        <form id="template_filter" method=""  action="" class="form-horizontal form-groups-bordered validate" novalidate="novalidate">
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
                        <label class="col-sm-2 control-label">Template Privacy</label>
                        <div class="col-sm-2">
                            {{Form::select('template_privacy',$privacy,'',array("class"=>"selectboxit"))}}
                        </div>
                        <label class="col-sm-2 control-label">Template Type</label>
                        <div class="col-sm-2">
                            {{Form::select('template_type',$type,'',array("class"=>"selectboxit"))}}
                        </div>
                    </div>
                    <p style="text-align: right;">
                        <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left">
                            <i class="entypo-search"></i>
                            Search
                        </button>
                    </p>
                </div>
            </div>
        </form>
    </div>
</div>

<p style="text-align: right;">
@if(User::can('EmailTemplateController.store'))
    <a href="#" id="add-new-template" class="btn btn-primary ">
        <i class="entypo-plus"></i>
        Add New Template
    </a>
@endif    
</p>
<table class="table table-bordered datatable" id="table-4">
    <thead>
    <tr>
        <th width="20%">Template name</th>
        <th width="20%">Subject</th>
        <th width="15%">Type</th>
        <th width="15%">Created By</th>
        <th width="15%">updated Date</th>
        <th width="15%">Action</th>
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
        //show_loading_bar(40);
        var tempatetype = {{json_encode($type)}};
        $searchFilter.template_privacy = $("#template_filter [name='template_privacy']").val();
        $searchFilter.template_type = $("#template_filter [name='template_type']").val();

        data_table = $("#table-4").dataTable({
            "bDestroy": true,
            "bProcessing":true,
            "bServerSide":true,
            "sAjaxSource": baseurl + "/email_template/ajax_datagrid",
            "iDisplayLength": '{{Config::get('app.pageSize')}}',
            "fnServerParams": function(aoData) {
                aoData.push({"name":"template_privacy","value":$searchFilter.template_privacy},{"name":"type","value":$searchFilter.template_type});
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name":"template_privacy","value":$searchFilter.template_privacy},{"name":"type","value":$searchFilter.template_type});
            },
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[0, 'asc']],
             "aoColumns":
            [
                {  "bSortable": true },  //0  Template Name', '', '', '
                {  "bSortable": true }, //1   CreatedBy
                {  "bSortable": true,
                        mRender: function ( id, type, full ) {
                            return tempatetype[id];
                     }
                 }, //updated Date
                {  "bSortable": true }, //updated Date
                {  "bSortable": true }, //updated Date
                {
                   "bSortable": true,
                    mRender: function ( id, type, full ) {
                         action = '<div class = "hiddenRowData" >';
                         action += '<input type = "hidden"  name = "templateID" value = "' + id + '" / >';
                         action += '</div>';
                        if('{{User::can('EmailTemplateController.update')}}' && '{{User::can('EmailTemplateController.edit')}}') {
                            action += ' <a data-name = "'+full[4]+'" data-id="'+ id +'" class="edit-template btn btn-default btn-sm btn-icon icon-left"><i class="entypo-pencil"></i>Edit </a>';
                        }
                        if('{{User::can('EmailTemplateController.delete')}}') {
                            action += ' <a data-id="'+id+'" class="delete-template btn delete btn-danger btn-sm btn-icon icon-left"><i class="entypo-cancel"></i>Delete </a>';
                        }
                        return action;
                      }
                  },
            ],
            "oTableTools": {
                "aButtons": [
                    {
                        "sExtends": "download",
                        "sButtonText": "Export Data",
                        "sUrl": baseurl + "/email_template/exports", //baseurl + "/generate_xls.php",
                        sButtonClass: "save-collection"
                    }
                ]
            },
           "fnDrawCallback": function() {
                   //After Delete done
                   FnDeleteTemplateSuccess = function(response){

                       if (response.status == 'success') {
                           $("#Note"+response.NoteID).parent().parent().fadeOut('fast');
                           ShowToastr("success",response.message);
                           data_table.fnFilter('', 0);
                       }else{
                           ShowToastr("error",response.message);
                       }
                   }
                   //onDelete Click
                   FnDeleteTemplate = function(e){
                       result = confirm("Are you Sure?");
                       if(result){
                           var id  = $(this).attr("data-id");
                           showAjaxScript( baseurl + "/email_template/"+id+"/delete" ,"",FnDeleteTemplateSuccess );
                       }
                       return false;
                   }
                   $(".delete-template").click(FnDeleteTemplate); // Delete Note
                   $(".dataTables_wrapper select").select2({
                       minimumResultsForSearch: -1
                   });
           }
        });



        // Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });
        $('#template_filter').submit(function(e){
            e.preventDefault();
            $searchFilter.template_privacy = $("#template_filter [name='template_privacy']").val();
            $searchFilter.template_type = $("#template_filter [name='template_type']").val();
            data_table.fnFilter('', 0);
            return false;
        });

    $('#add-new-template').click(function(ev){
        ev.preventDefault();
        $('#add-new-template-form').trigger("reset");
        $("#add-new-template-form [name='TemplateID']").val('');
        $("#add-new-template-form [name='Email_template_privacy']").selectBoxIt().data("selectBox-selectBoxIt").selectOption(0);
        $("#add-new-template-form [name='Type']").selectBoxIt().data("selectBox-selectBoxIt").selectOption('');
        $('#add-new-modal-template h4').html('Add New template');
        $('#add-new-modal-template').modal('show');
    });
    $('table tbody').on('click','.edit-template',function(ev){
        ev.preventDefault();
        ev.stopPropagation();
        $('#add-new-template-form').trigger("reset");

        templateID = $(this).prev("div.hiddenRowData").find("input[name='templateID']").val();
        var url = baseurl + '/email_template/'+templateID+'/edit';
        $.get(url, function(data, status){
            if(Status="success"){
                $('#add-new-template-form').trigger("reset");
                $("#add-new-template-form [name='TemplateID']").val(data['TemplateID']);
                $("#add-new-template-form [name='TemplateName']").val(data['TemplateName']);
                $("#add-new-template-form [name='Subject']").val(data['Subject']);
                $("#add-new-template-form [name='TemplateBody']").val(data['TemplateBody']);
                $("#add-new-template-form [name='Type']").selectBoxIt().data("selectBox-selectBoxIt").selectOption(data['Type']);
                $("#add-new-template-form [name='Email_template_privacy']").selectBoxIt().data("selectBox-selectBoxIt").selectOption(data['Privacy']);
                $('#add-new-modal-template h4').html('Edit template');

                $('#add-new-modal-template').modal('show');
            }else{
                toastr.error(status, "Error", toastr_opts);
            }
        });


        $("#add-new-template-form [name='templateID']").val($(this).attr('data-id'));
        $('#add-new-modal-template h4').html('Edit template');
        $('#add-new-modal-template').modal('show');
    })

    $('#add-new-template-form').submit(function(e){
        e.preventDefault();
        var templateID = $("#add-new-template-form [name='TemplateID']").val();
        if( typeof templateID != 'undefined' && templateID != ''){
            update_new_url = baseurl + '/email_template/'+templateID+'/update';
        }else{
            update_new_url = baseurl + '/email_template/store';
        }
        ajax_update(update_new_url,$('#add-new-template-form').serialize());
    })

    $('#add-new-modal-template').on('shown.bs.modal', function(event){
        var modal = $(this);
        modal.find('.message').wysihtml5({
            "font-styles": true,
            "emphasis": true,
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

function ajax_update(fullurl,data){
//alert(data)
    $.ajax({
        url:fullurl, //Server script to process data
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            $("#template-update").button('reset');
            $(".btn").button('reset');
            $('#modal-template').modal('hide');

            if (response.status == 'success') {
                $('#add-new-modal-template').modal('hide');
                toastr.success(response.message, "Success", toastr_opts);
                if( typeof data_table !=  'undefined'){
                    data_table.fnFilter('', 0);
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
<style>
.dataTables_filter label{
    display:none !important;
}
.dataTables_wrapper .export-data{
    right: 30px !important;
}
</style>
<link rel="stylesheet" href="assets/js/wysihtml5/bootstrap-wysihtml5.css">
<script src="assets/js/wysihtml5/wysihtml5-0.4.0pre.min.js"></script>
<script src="assets/js/wysihtml5/bootstrap-wysihtml5.js"></script>
@stop


@section('footer_ext')
@parent
<div class="modal fade" id="add-new-modal-template">
    <div class="modal-dialog" style="width: 66%;">
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
                                {{Form::select('Type',$type,'',array("class"=>"selectboxit"))}}
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
                                {{Form::select('Email_template_privacy',$privacy,'',array("class"=>"selectboxit"))}}
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
