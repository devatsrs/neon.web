@extends('layout.main')

@section('content')

<ol class="breadcrumb bc-3">
    <li>
        <a href="{{URL::to('/dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>
        <a href="{{URL::to('/rategenerators')}}">Rate Generator</a>
    </li>
    <li class="active">
        <strong>Update Rate Generator</strong>
    </li>
</ol>
<h3>Update Rate Generator</h3>
<div class="float-right">
    <button type="button"  class="add btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
        <i class="entypo-floppy"></i>
        Add New
    </button>

    <a href="{{URL::to('rategenerators/'.$id.'/edit')}}" class="btn btn-danger btn-sm btn-icon icon-left">
        <i class="entypo-cancel"></i>
        Close
    </a>
</div>
<div class="row">
    <div class="col-md-12">
        <ul class="nav nav-tabs bordered" >
                <li></li>
                <li ><a href="{{URL::to('rategenerators/rules/'.$id.'/edit/'.$RateRuleID)}}">Code</a></li>
                <li ><a href="{{URL::to('rategenerators/rules/'.$id.'/edit_source/'.$RateRuleID)}}">Sources</a></li>
                <li class="active"><a href="{{URL::to('rategenerators/rules/'.$id.'/edit_margin/'.$RateRuleID)}}">Margin</a></li>
            </ul>
        <div class="panel panel-primary" data-collapsed="0">
            <div class="panel-heading">
                <div class="panel-title">
                    Rate Generator Rule Source Information
                </div>
                <div class="panel-options">
                    <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                </div>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <div class="">


                        <table class="table table-bordered datatable" id="table-4">
                            <thead>
                                <tr>
                                    <th>Min Rate</th>
                                    <th>Max Rate</th>
                                    <th>Margin</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    jQuery(document).ready(function($) {


        //Edit Button
        $('body').on('click', '.table .edit.btn',function(ev) {

            MinRate = $(this).prev("div.hiddenRowData").find("input[name='MinRate']").val();
            MaxRate = $(this).prev("div.hiddenRowData").find("input[name='MaxRate']").val();
            AddMargin = $(this).prev("div.hiddenRowData").find("input[name='AddMargin']").val();
            RateRuleMarginId = $(this).prev("div.hiddenRowData").find("input[name='RateRuleMarginId']").val();

            $("#edit-margin-form").find("input[name='MinRate']").val(MinRate);
            $("#edit-margin-form").find("input[name='MaxRate']").val(MaxRate);
            $("#edit-margin-form").find("input[name='AddMargin']").val(AddMargin);
            $("#edit-margin-form").find("input[name='RateRuleMarginId']").val(RateRuleMarginId);

            jQuery('#modal-RateGenerator').modal('show', {backdrop: 'static'});
        });

        $(".add.btn").click(function(ev) {
            jQuery('#modal-RateGenerator-add-margin').modal('show', {backdrop: 'static'});
        });



    });
</script>
@include('includes.errors')
@include('includes.success')

@stop         


@section('footer_ext')
@parent
<div class="modal fade" id="modal-RateGenerator">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="edit-margin-form" method="post" action="{{URL::to('rategenerators/rules/'.$id.'/update_margin/'.$RateRuleID)}}">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Edit Rate Generator Rule Margin</h4>
                </div>

                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-4">

                            <div class="form-group">
                                <label for="field-4" class="control-label">Min Rate</label>
                                <input type="text" name="MinRate" class="form-control" id="field-5" placeholder="">
                            </div>

                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Max Rate</label>
                                <input type="text" name="MaxRate" class="form-control" id="field-5" placeholder="">
                            </div>

                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Add Margin <span class="label label-info popover-primary" data-original-title="Example" data-content="If you want to add percentage value enter i.e. 10p for 10% percentage value" data-placement="bottom" data-trigger="hover" data-toggle="popover">?</span></label>
                                <input type="text" name="AddMargin" class="form-control" id="field-5" placeholder="">
                            </div>

                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <input type="hidden" name="RateRuleMarginId" value="">

                    <button type="submit"  class="save  btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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
<!--Add New Rate Rule Margin-->
<div class="modal fade" id="modal-RateGenerator-add-margin">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="add-margin-form" method="post" action="{{URL::to('rategenerators/rules/'.$id.'/add_margin/'.$RateRuleID)}}">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true" onclick="Javascript: $('.add.btn').button('reset');">&times;</button>
                    <h4 class="modal-title">Add Rate Generator Rule Margin</h4>
                </div>

                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-4">

                            <div class="form-group">
                                <label for="field-4" class="control-label">Min Rate</label>
                                <input type="text" name="MinRate" class="form-control" id="field-5" placeholder="">
                            </div>

                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Max Rate</label>
                                <input type="text" name="MaxRate" class="form-control" id="field-5" placeholder="">
                            </div>

                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="field-5" class="control-label">Add Margin <span class="label label-info popover-primary" data-original-title="Example" data-content="If you want to add percentage value enter i.e. 10p for 10% percentage value" data-placement="bottom" data-trigger="hover" data-toggle="popover">?</span></label>
                                <input type="text" name="AddMargin" class="form-control" id="field-5" placeholder="">
                            </div>

                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit"  class="save  btn btn-primary btn-sm btn-icon icon-left" data-redirect="{{URL::to('rategenerators/rules/'.$id.'/edit_margin/'.$RateRuleID)}}"  >
                        <i class="entypo-floppy"></i>
                        Save
                    </button>
                    <button  type="button" class="btn btn-danger btn-sm btn-icon icon-left" data-dismiss="modal" onclick="Javascript: $('.add.btn').button('reset');">
                        <i class="entypo-cancel"></i>
                        Close
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>

    jQuery(document).ready(function ($) {


            data_table = $("#table-4").dataTable({

            "bProcessing":true,
            "bDestroy": true,
            "bServerSide":true,
            "sAjaxSource": baseurl + "/rategenerators/ajax_margin_datagrid",
            "iDisplayLength": parseInt('{{Config::get('app.pageSize')}}'),
            "fnServerParams": function(aoData) {
                aoData.push({"name":"id","value":{{$id}} },{"name":"RateRuleID","value":{{$RateRuleID}} });
                data_table_extra_params.length = 0;
                data_table_extra_params.push({"name":"id","value":{{$id}} },{"name":"RateRuleID","value":{{$RateRuleID}} });
            },
            "sPaginationType": "bootstrap",
            "oTableTools": {},
            "aoColumns":
            [
                { "bSortable": false },
                { "bSortable": false },
                { "bSortable": false },
                {
                   "bSortable": false,
                    mRender: function ( id, type, full ) {
                        var action ,delete_ ;

                        delete_ = "{{ URL::to('rategenerators/rules/'.$RateRuleID.'/delete_margin/{id}')}}";
                        delete_ = delete_.replace( '{id}', id );


                        action = '<div class="hiddenRowData"><input type="hidden" value="'+full[0]+'" name="MinRate"><input type="hidden" value="'+full[1]+'" name="MaxRate"><input type="hidden" value="'+full[2]+'" name="AddMargin"><input type="hidden" value="'+full[3]+'" name="RateRuleMarginId"></div>';
                        action += '<a class="edit btn btn-primary btn-sm btn-icon icon-left" id="add-new-margin" href="#"><i class="entypo-floppy"></i>Edit</a>';
                        action += ' <a class="btn delete btn-danger btn-sm btn-icon icon-left"  href="'+delete_+'"><i class="entypo-cancel"></i>Delete</a>';

                        return action;
                      }
                  },
            ],
            "fnDrawCallback": function() {
                $(".dataTables_wrapper select").select2({
                    minimumResultsForSearch: -1
                });

            }
        });
        $('body').on('click', '.btn.delete', function (e) {
            e.preventDefault();

            response = confirm('Are you sure?');
            if( typeof $(this).attr("data-redirect")=='undefined'){
                $(this).attr("data-redirect",'{{ URL::previous() }}')
            }
            redirect = $(this).attr("data-redirect");
            if (response) {

                $.ajax({
                    url: $(this).attr("href"),
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        $(".btn.delete").button('reset');
                        if (response.status == 'success') {
                            data_table.fnFilter('', 0);
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

   });


</script>
@include('includes.ajax_submit_script', array('formID'=>'edit-margin-form' , 'url' => ('rategenerators/rules/'.$id.'/update_margin/'.$RateRuleID)))
<?php //@include('includes.ajax_submit_script', array('formID'=>'add-margin-form' , 'url' => ('rategenerators/rules/'.$id.'/add_margin/'.$RateRuleID)))?>
@stop



