<div class="panel panel-primary" data-collapsed="0">
    <div class="panel-heading">
        <div class="panel-title">
        </div>
        <div class="panel-options">
            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
        </div>
    </div>
    <div class="panel-body">

        <form id="add-margin-form" method="post" action="{{URL::to('rategenerators/rules/'.$id.'/add_margin/'.$RateRuleID)}}">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="field-4" class="control-label">Min Rate</label>
                            <input type="text" name="MinRate" class="form-control" id="field-5" placeholder="">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="field-5" class="control-label">Max Rate</label>
                            <input type="text" name="MaxRate" class="form-control" id="field-5" placeholder="">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="field-5" class="control-label">Add Margin <span class="label label-info popover-primary" data-original-title="Add Margin" data-content="If you want to add percentage value enter i.e. 10p for 10% percentage value" data-placement="bottom" data-trigger="hover" data-toggle="popover">?</span></label>
                            <input type="text" name="AddMargin" class="form-control" id="field-5" placeholder="">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="field-5" class="control-label">Fixed Value</label>
                            <span class="label label-info popover-primary" data-original-title="Fixed Value" data-content="if the rate is between min and max then rate will be changed to the value specified." data-placement="bottom" data-trigger="hover" data-toggle="popover">?</span>
                            <input type="text" name="FixedValue" class="form-control" id="field-5" placeholder="">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <br>
                            <button type="submit"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
                                <i class="entypo-floppy"></i>
                                Add New
                            </button>
                        </div>
                    </div>
                </div>
        </form>


        <div class="clear clearfix"><br></div>
        <div class="clear clearfix"><br></div>

        <div class="form-group">
            <div class="">
                <table class="table table-bordered datatable" id="table-rate-generator-margin">
                    <thead>
                    <tr>
                        <th>Min Rate</th>
                        <th>Max Rate</th>
                        <th>Margin</th>
                        <th>Fixed Value</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                </table>
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
            FixedValue = $(this).prev("div.hiddenRowData").find("input[name='FixedValue']").val();
            RateRuleMarginId = $(this).prev("div.hiddenRowData").find("input[name='RateRuleMarginId']").val();

            $("#edit-margin-form").find("input[name='MinRate']").val(MinRate);
            $("#edit-margin-form").find("input[name='MaxRate']").val(MaxRate);
            $("#edit-margin-form").find("input[name='AddMargin']").val(AddMargin);
            $("#edit-margin-form").find("input[name='RateRuleMarginId']").val(RateRuleMarginId);
            $("#edit-margin-form").find("input[name='FixedValue']").val(FixedValue);

            jQuery('#modal-RateGenerator').modal('show', {backdrop: 'static'});
        });





    });
</script>

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
                            <div class="col-md-6">

                                <div class="form-group">
                                    <label for="field-4" class="control-label">Min Rate</label>
                                    <input type="text" name="MinRate" class="form-control" id="field-5" placeholder="">
                                </div>

                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Max Rate</label>
                                    <input type="text" name="MaxRate" class="form-control" id="field-5" placeholder="">
                                </div>

                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Add Margin <span class="label label-info popover-primary" data-original-title="Add Margin" data-content="If you want to add percentage value enter i.e. 10p for 10% percentage value" data-placement="bottom" data-trigger="hover" data-toggle="popover">?</span></label>
                                    <input type="text" name="AddMargin" class="form-control" id="field-5" placeholder="">
                                </div>

                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Fixed Value</label>
                                    <span class="label label-info popover-primary" data-original-title="Fixed Value" data-content="if the rate is between min and max then rate will be changed to the value specified." data-placement="bottom" data-trigger="hover" data-toggle="popover">?</span>
                                    <input type="text" name="FixedValue" class="form-control" id="field-5" placeholder="">
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
    <script>

        jQuery(document).ready(function ($) {

            var list_fields  = ['MinRate', 'MaxRate', 'AddMargin', 'FixedValue', 'RateRuleMarginId'];

            data_table = $("#table-rate-generator-margin").dataTable({

                "bProcessing":true,
                "bDestroy": true,
                "bServerSide":true,
                "sAjaxSource": baseurl + "/rategenerators/ajax_margin_datagrid",
                "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
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
                            { "bSortable": false },
                            {
                                "bSortable": false,
                                mRender: function ( id, type, full ) {
                                    var action ,delete_ ;

                                    delete_ = "{{ URL::to('rategenerators/rules/'.$RateRuleID.'/delete_margin/{id}')}}";
                                    delete_ = delete_.replace( '{id}', id );

                                    action = '<div class = "hiddenRowData" >';
                                    for (var i = 0; i < list_fields.length; i++) {
                                        var str = '';
                                        str = full[i];
                                        action += '<input disabled type="hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null ? str : '') + '" / >';
                                    }
                                    action += '</div>';
                                    action += ' <a title="Edit" class="edit btn btn-primary btn-sm" id="add-new-margin" href="#"><i class="entypo-pencil"></i>&nbsp;</a>';
                                    action += ' <a title="Delete" class="btn delete btn-danger btn-sm"  href="'+delete_+'"><i class="entypo-trash"></i></a>';

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

                var response = confirm('Are you sure?');
                if (response) {
                    var _url = $(this).attr("href");
                    submit_ajax(_url,"",false);
                }
                return false;

            });
            $("#edit-margin-form").submit(function(e){
                e.preventDefault();
                var _url = $(this).attr("action");
                var formData = new FormData($('#edit-margin-form')[0]);
                submit_ajax_withfile(_url,formData);
            });
            $("#add-margin-form").submit(function(e){


                var MinRate = $("#add-margin-form input[name='MinRate']").val();
                var MaxRate = $("#add-margin-form input[name='MaxRate']").val();
                var AddMargin = $("#add-margin-form input[name='AddMargin']").val();
                var FixedValue = $("#add-margin-form input[name='FixedValue']").val();

                if((typeof MinRate  == 'undefined' || MinRate.trim() == '' ) && (typeof MaxRate  == 'undefined' || MaxRate.trim() == '' )){

                    setTimeout(function(){$('.btn').button('reset');},10);
                    toastr.error("Please Enter a Min Rate or Max Rate", "Error", toastr_opts);
                    return false;

                }
                if((typeof AddMargin  == 'undefined' || AddMargin.trim() == '' ) && (typeof FixedValue  == 'undefined' || FixedValue.trim() == '' )){

                    setTimeout(function(){$('.btn').button('reset');},10);
                    toastr.error("Please Enter a Margin or Fix Rate", "Error", toastr_opts);
                    return false;

                }

                e.preventDefault();
                var _url = $(this).attr("action");
                var formData = new FormData($('#add-margin-form')[0]);
                submit_ajax_withfile(_url,formData);
                return false;
            });
        });
    </script>
@stop