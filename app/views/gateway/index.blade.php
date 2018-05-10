@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li class="active">
        <strong>Gateway</strong>
    </li>
</ol>
<h3>Gateway</h3>

@include('includes.errors')
@include('includes.success')



<p style="text-align: right;">
@if( User::checkCategoryPermission('Gateway','Add') )
    <a href="#" id="add-new-config" class="btn btn-primary ">
        <i class="entypo-plus"></i>
        Add New
    </a>
@endif
</p>
<div class="row">
  <div class="tab-content">
    <div class="tab-pane active" id="customer" >
      <div class="col-md-12">
        <form novalidate class="form-horizontal form-groups-bordered filter validate" method="post" id="gateway_form">
          <div data-collapsed="0" class="panel panel-primary">
            <div class="panel-heading">
              <div class="panel-title">Filter</div>
              <div class="panel-options"> <a data-rel="collapse" href="#"><i class="entypo-down-open"></i></a> </div>
            </div>
            <div class="panel-body">
              <div class="form-group">
                <label class="col-sm-1 control-label" for="field-1">Gateway</label>
                <div class="col-sm-3"> {{ Form::select('Gateway',$gateway,$id,array("class"=>"select2")) }} </div>
              </div>
              <p style="text-align: right;">
                <button class="btn btn-primary btn-sm btn-icon icon-left" type="submit"> <i class="entypo-search"></i> Search </button>
              </p>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<table class="table table-bordered datatable" id="table-4">
    <thead>
    <tr>
        <th width="20%">Gateway Name</th>
        <th width="20%">IP</th>
        <th width="10%">Status</th>
        <th width="20%">Action</th>
    </tr>
    </thead>
    <tbody>


    </tbody>
</table>

<script type="text/javascript">
var selectedID = '{{$id}}';
var $searchFilter = {};
var update_new_url;
var postdata;
    jQuery(document).ready(function ($) {
        public_vars.$body = $("body");

        //show_loading_bar(40);
		 $searchFilter.Gateway = $("#gateway_form [name='Gateway']").val();
        var GatewayName = '{{$GatewayName}}';

        data_table = $("#table-4").dataTable({
            "bDestroy": true,
            "bProcessing":true,
            "bServerSide":true,
            "sAjaxSource": baseurl + "/gateway/ajax_datagrid/type",
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "fnServerParams": function(aoData) {
				  aoData.push({"name":"Gateway","value":$searchFilter.Gateway});
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name":"Export","value":1},{"name":"Gateway","value":$searchFilter.Gateway});
            },
            "aaSorting": [[0, 'asc']],
             "aoColumns":
            [
                {  "bSortable": true },  //0   name', '', '', '
                {  "bSortable": true },  //1   name', '', '', '
                {  mRender: function(status, type, full) {
                                                   if (status == 1)
                                                       return '<i style="font-size:22px;color:green" class="entypo-check"></i>';
                                                   else
                                                       return '<i style="font-size:28px;color:red" class="entypo-cancel"></i>';
                                               }
                }, //2   Status
                {                       //3
                   "bSortable": true,
                    mRender: function ( id, type, full ) {						
                    var GatewayID = full[3]>0?full[3]:'';
                        var action ='';
                         action = '<div class = "hiddenRowData" >';
                         action += '<input type = "hidden"  name = "GatewayID" value = "' + GatewayID + '" / >';
                         action += '<input type = "hidden"  name = "CompanyGatewayID" value = "' + full[4] + '" / >';
                         action += '<input type = "hidden"  name = "Title" value = "' + full[0] + '" / >';
                         action += '<input type = "hidden"  name = "Status" value = "' + full[2] + '" / >';
                         action += '<input type = "hidden"  name = "IP" value = "' +( full[1]!==null?full[1]:'') + '" / >';
                        action += '<input type = "hidden"  name = "TimeZone" value = "' +( full[5]!==null?full[5]:'') + '" / >';
                        action += '<input type = "hidden"  name = "BillingTimeZone" value = "' +( full[6]!==null?full[6]:'') + '" / >';
                         action += '</div>';

                         <?php if(User::checkCategoryPermission('Gateway','Edit') ){ ?>
                            action += ' <a data-name = "'+full[0]+'" data-id="'+ full[3]+'" title="Title" class="edit-config btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>';
                         <?php } ?>
                         <?php if(User::checkCategoryPermission('Gateway','Delete') ){ ?>
                            //action += ' <a data-id="'+ full[4] +'" class="delete-config btn delete btn-danger btn-sm btn-icon icon-left"><i class="entypo-trash"></i>Delete </a>';
                         <?php } ?>
                         if( full[4]>0){
                            action += ' <a data-id="'+ full[4]+'" class="test-connection btn btn-success btn-sm btn-icon icon-left"><i class="entypo-rocket"></i>Test Connection </a>';
                         }
                        return action;
                      }
                  },
            ],
            "oTableTools": {
                "aButtons": [
                    {
                        "sExtends": "download",
                        "sButtonText": "EXCEL",
                        "sUrl": baseurl + "/gateway/ajax_datagrid/xlsx", //baseurl + "/generate_xlsx.php",
                        sButtonClass: "save-collection btn-sm"
                    },
                    {
                        "sExtends": "download",
                        "sButtonText": "CSV",
                        "sUrl": baseurl + "/gateway/ajax_datagrid/csv", //baseurl + "/generate_csv.php",
                        sButtonClass: "save-collection btn-sm"
                    }
                ]
            }, "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
				if(selectedID!='' && selectedID!='0' && iTotal==0){
					$('#add-new-config').click();
				}	
  },
           "fnDrawCallback": function() {
                   //onDelete Click

               $(".btn.delete").click(function (e) {
                   e.preventDefault();
                   var id = $(this).attr('data-id');
                   var url = baseurl + '/gateway/'+id+'/ajax_existing_gateway_cronjob';
                   $('#delete-gateway-form [name="CompanyGatewayID"]').val(id);
                   if(confirm('Are you sure you want to delete selected gateway? All related data like CDR, summary etc will also delete.')) {
                       $.ajax({
                           url: url,
                           type: 'POST',
                           dataType: 'html',
                           success: function (response) {
                               $(".btn.delete").button('reset');
                               if (response) {
                                   $('#modal-delete-gateway .container').html(response);
                                   $('#modal-delete-gateway').modal('show');
                               }else{
                                   $('#delete-gateway-form').submit();
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

                   $(".dataTables_wrapper select").select2({
                       minimumResultsForSearch: -1
                   });
           }

        });



        // Replace Checboxes
        $(".pagination a").click(function (ev) {
            replaceCheckboxes();
        });

    $('#add-new-config').click(function(ev){
        ev.preventDefault();
        $('#CDRMapping').addClass('hidden');
        $('#add-new-config-form').trigger("reset");
        $("#add-new-config-form [name='CompanyGatewayID']").val('');
        $("#add-new-config-form [name='BillingTimeZone']").select2().select2('val','');
        $("#add-new-config-form [name='TimeZone']").select2().select2('val','');
        //$("#GatewayID").select2().select2('val','');
        $("#GatewayID").trigger('change');
        $('#add-new-modal-config h4').html('Add New Gateway');
        $('#add-new-modal-config').modal('show');
    });
    $('table tbody').on('click','.test-connection',function(ev){
        ev.preventDefault();
        ev.stopPropagation();
        $(this).button('loading');
        submit_ajax(baseurl+'/gateway/test_connetion/'+$(this).attr('data-id'),'');
    });

    $('table tbody').on('click','.edit-config',function(ev){
        ev.preventDefault();
        ev.stopPropagation();
        $('#CDRMapping').addClass('hidden');
        $('#add-new-config-form').trigger("reset");
        var prevrow = $(this).prev("div.hiddenRowData");
        $("#add-new-config-form [name='CompanyGatewayID']").val(prevrow.find("input[name='CompanyGatewayID']").val())
        $("#add-new-config-form [name='Title']").val(prevrow.find("input[name='Title']").val())
        $("#add-new-config-form [name='IP']").val(prevrow.find("input[name='IP']").val())
        $("#add-new-config-form [name='TimeZone']").select2().select2('val',prevrow.find("input[name='TimeZone']").val());
        $("#add-new-config-form [name='BillingTimeZone']").select2().select2('val',prevrow.find("input[name='BillingTimeZone']").val());
        if(prevrow.find("input[name='Status']").val() == 1 ){
            $('[name="Status_name"]').prop('checked',true)
        }else{
            $('[name="Status_name"]').prop('checked',false)
        }
        GatewayID = prevrow.find("input[name='GatewayID']").val()>0?prevrow.find("input[name='GatewayID']").val():'other';
        $("#GatewayID").select2().select2('val',GatewayID);
        $("#GatewayID").trigger('change');

        if(GatewayName == 'FTP'){
            $('#CDRMapping').removeClass('hidden');
        }

        $('#add-new-modal-config h4').html('Edit Gateway');
        $('#add-new-modal-config').modal('show');
    });
    $('[name="Status_name"]').change(function(e){
        if($(this).prop('checked')){
            $("#add-new-config-form [name='Status']").val(1);
        }else{
            $("#add-new-config-form [name='Status']").val(0);
        }

    });
    $('#add-new-config-form').submit(function(e){
        e.preventDefault();
        var CompanyGatewayID = $("#add-new-config-form [name='CompanyGatewayID']").val()
        if( typeof CompanyGatewayID != 'undefined' && CompanyGatewayID != ''){
            update_new_url = baseurl + '/gateway/update/'+CompanyGatewayID;
        }else{
            update_new_url = baseurl + '/gateway/create';
        }
        $.ajax({
            url: update_new_url,  //Server script to process data
            type: 'POST',
            dataType: 'json',
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function (response) {
                if(response.status =='success'){
                    toastr.success(response.message, "Success", toastr_opts);
                    $('#add-new-modal-config').modal('hide');
                     data_table.fnFilter('', 0);
                }else{
                    toastr.error(response.message, "Error", toastr_opts);
                }
                $("#config-update").button('reset');
            },
            // Form data
            //data: $('#add-new-config-form').serialize(),
            //Options to tell jQuery not to process data or worry about content-type.
            cache: false
        });
    });
            $('#GatewayID').change(function(e){
                $('#ajax_config_html').html('Loading...<br>');
                if($(this).val() != ''){
                $.ajax({
                    url: baseurl + "/gateway/ajax_load_gateway_dropdown",
                    type: 'POST',
                    success: function(response) {
                        $('#ajax_config_html').html(response);
                        initializeSelect2();
                        $("#RateCDR").trigger('change');
                        if($('#NameFormat').val() == 'IP') {
                            $('#AutoAddIPBox').show();
                        } else {
                            $('#AutoAddIPBox').hide();
                        }
                    },
                    // Form data
                    data: "GatewayID="+$(this).val()+'&CompanyGatewayID='+$("#add-new-config-form [name='CompanyGatewayID']").val(),
                    cache: false
                    });
                }else{
                    $('#ajax_config_html').html('');
                }
            });
			
		 $("#gateway_form").submit(function(e){
            e.preventDefault();
            $searchFilter.Gateway = $("#gateway_form [name='Gateway']").val();
            data_table.fnFilter('', 0);
            return false;
        });
		
		function getQueryVariable(variable) {
  var query = window.location.search.substring(1);
  var vars = query.split("&");
  for (var i=0;i<vars.length;i++) {
    var pair = vars[i].split("=");
    if (pair[0] == variable) {
      return pair[1];
    }
  } 
  alert('Query Variable ' + variable + ' not found');
}

        /*delete gateway*/
        $('#delete-gateway-form').submit(function (e) {
            e.preventDefault();
            if($('#modal-delete-gateway .container').is(':empty')) {
                var CompanyGatewayID = $(this).find('[name="CompanyGatewayID"]').val();
                var url = baseurl + '/gateway/delete/' + CompanyGatewayID;
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if (response.status == 'success') {
                            toastr.success(response.message, "Success", toastr_opts);
                            data_table.fnFilter('', 0);
                            reloadJobsDrodown(0);
                            $('#modal-delete-gateway').modal('hide');
                        } else {
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                        $(".save.GatewaySelect").button('reset');

                    },
                    // Form data
                    data: '',
                    cache: false

                });
            }else{
                //  alert('Please delete cron job first');
                var SelectedIDs = getselectedIDs("cronjob-table");
                if (SelectedIDs.length == 0) {
                    alert('No cron job selected.');
                    $("#gateway-select").button('reset');
                    return false;
                }else{
                    var deleteid = SelectedIDs.join(",");
                    cronjobsdelete('all',deleteid);
                }
            }
        });

        /*not in use*/
        $(document).on('click','.cronjobedelete',function(){
            var deleteid = $(this).attr('data-id');
            if (deleteid == '') {
                $(".save.GatewaySelect").button('reset');
                toastr.error('No cron job selected.', "Error", toastr_opts);
                return false;
            }else{
                cronjobsdelete('select',deleteid);
            }
        });

        function cronjobsdelete(type,deleteid){
            if(confirm('Are you sure you want to delete selected cron job?')){
                var CompanyGatewayID = $('#delete-gateway-form [name="CompanyGatewayID"]').val();
                var url = baseurl + "/gateway/"+CompanyGatewayID+"/deletecronjob";
                var cronjobs = deleteid;
                $('#modal-delete-gateway .container').html('');
                $('#modal-delete-gateway').modal('hide');
                $.ajax({
                    url: url,
                    type:'POST',
                    data:{cronjobs:cronjobs},
                    datatype:'json',
                    success: function(response) {
                        if (response.status == 'success') {
                            toastr.success(response.message,'Success', toastr_opts);
                            var url = baseurl + '/gateway/'+CompanyGatewayID+'/ajax_existing_gateway_cronjob';
                            $('#delete-gateway-form [name="CompanyGatewayID"]').val(CompanyGatewayID);
                            $.ajax({
                                url: url,
                                type: 'POST',
                                dataType: 'html',
                                success: function (response) {
                                    $(".btn.delete").button('reset');
                                    if (response) {
                                        $('#modal-delete-gateway .container').html(response);
                                        $('#modal-delete-gateway').modal('show');
                                    }else{
                                        $('#delete-gateway-form').submit();
                                    }
                                },

                                // Form data
                                //data: {},
                                cache: false,
                                contentType: false,
                                processData: false
                            });
                        }else{
                            toastr.error(response.message, "Error", toastr_opts);
                        }
                    }

                });
            }
        }
        $(document).on('click', '#cronjob-table tbody tr', function() {
            $(this).toggleClass('selected');
            if($(this).is('tr')) {
                if ($(this).hasClass('selected')) {
                    $(this).find('.rowcheckbox').prop("checked", true);
                } else {
                    $(this).find('.rowcheckbox').prop("checked", false);
                }
            }
        });

        $(document).on('click','#selectall',function(){
            if($(this).is(':checked')){
                checked = 'checked=checked';
                $(this).prop("checked", true);
                $(this).parents('table').find('tbody tr').each(function (i, el) {
                    $(this).find('.rowcheckbox').prop("checked", true);
                    $(this).addClass('selected');
                });
            }else{
                checked = '';
                $(this).prop("checked", false);
                $(this).parents('table').find('tbody tr').each(function (i, el) {
                    $(this).find('.rowcheckbox').prop("checked", false);
                    $(this).removeClass('selected');
                });
            }
        });

        $('#cdrtemplatelink').click(function(e){
            e.preventDefault();
            var CompanyGatewayID = $('#add-new-config-form [name="CompanyGatewayID"]').val();
            var url = "{{URL::to('cdr_template/gateway')}}/"+CompanyGatewayID;
            openInNewTab(url);
        });

        function initializeSelect2(){
            $("#ajax_config_html .select2").each(function(i, el) {
                buildselect2(el);
            }).promise().done(function(){
                $('.select2').css('visibility','visible');
            });


            if ($.isFunction($.fn.perfectScrollbar))
            {
                $(".select2-results").niceScroll({
                    cursorcolor: '#d4d4d4',
                    cursorborder: '1px solid #ccc',
                    railpadding: {right: 3}
                });
            }
        }

        function getselectedIDs(table){
            var SelectedIDs = [];
            $('#'+table+' tr .rowcheckbox:checked').each(function (i, el) {
                var cronjob = $(this).val();
                SelectedIDs[i++] = cronjob;
            });
            return SelectedIDs;
        }

        $(document).on('change', '#RateCDR', function() {
            if($('#RateCDR').is(':checked')) {
                $('.cdrrerateaccountsbox').slideDown();
                $('#SpecifyRate').parent().parent().slideDown();
                $('#RateMethod').parent().parent().slideDown();

            } else {
                $('.cdrrerateaccountsbox').slideUp();
                $('#SpecifyRate').parent().parent().slideUp();
                $('#RateMethod').parent().parent().slideUp();

            }
        });

        $(document).on('change', '#NameFormat', function() {
            if($('#NameFormat').val() == 'IP') {
                $('#AutoAddIPBox').slideDown();
            } else {
                $('#AutoAddIPBox').slideUp();
            }
        });

    });
</script>
<style>
.dataTables_filter label{
    display:none !important;
}
.dataTables_wrapper .export-data{
    right: 30px !important;
}
</style>
<!--Only for Delete operation-->
@include('includes.ajax_submit_script', array('formID'=>'' , 'url' => ('')))
@stop


@section('footer_ext')
@parent
<div class="modal fade" id="add-new-modal-config">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="add-new-config-form" method="post" enctype="multipart/form-data">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Add New Config</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Gateway Type</label>
                                {{ Form::select('GatewayID',$gateway,$id, array("class"=>"select2",'id'=>'GatewayID')) }}
                             </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Gateway Title</label>
                                <input name="Title" class="form-control" value="" placeholder="">
                             </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">IP address</label>
                                <input name="IP" class="form-control" value="" placeholder="">
                             </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">Timezone</label>
                                {{Form::select('TimeZone', $timezones, '' ,array("class"=>"form-control select2"))}}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label">BillingTimeZone</label>
                                {{Form::select('BillingTimeZone', $timezones, '' ,array("class"=>"form-control select2"))}}
                            </div>
                        </div>
                    </div>
                <div id="ajax_config_html"></div>
                <div class="row">
                    <label for="field-5" class="control-label col-md-3">Active</label>
                    <div class="clear col-md-3">
                        <p class="make-switch switch-small">
                            <input type="checkbox" checked=""  name="Status_name" value="0">
                        </p>
                        <input type="hidden"  name="Status" value="0">
                    </div>
                </div>
                <div id="CDRMapping" class="row hidden">
                    <label for="field-5" class="control-label col-md-3">CDR Mapping</label>
                    <div class="clear col-md-3">
                        <a id="cdrtemplatelink" href="#" target="_blank" class="btn btn-primary btn-sm btn-icon icon-left">
                            <i class="entypo-link"></i>CDR Mapping
                        </a>
                    </div>
                </div>
                <div class="row"><br></div>
                <div class="modal-footer">
                    <input type="hidden" name="CompanyGatewayID" value="">
                    <button type="submit" id="config-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                        <i class="entypo-floppy"></i>
                        Save

                    </button>
                    <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal">
                        <i class="entypo-cancel"></i>
                        Close
                    </button>
                </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-delete-gateway" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="delete-gateway-form" method="post" >

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Delete Gateway cron job</h4>
                </div>

                <div class="modal-body">
                    <div class="container col-md-12"></div>
                </div>

                <div class="modal-footer">
                    <input type="hidden" name="CompanyGatewayID" value="">
                    <button id="gateway-select" class="save GatewaySelect btn btn-danger btn-sm btn-icon icon-left">
                        <i class="entypo-cancel"></i>
                        Delete
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
