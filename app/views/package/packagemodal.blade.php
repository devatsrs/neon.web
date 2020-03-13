
<script>
    $(document).ready(function ($) {

        $('#add-new-package-form').submit(function(e){
            e.preventDefault();
            var PackageId = $("#add-new-package-form [name='PackageId']").val();
            if( typeof PackageId != 'undefined' && PackageId != ''){
                update_new_url = baseurl + '/package/update/'+PackageId;
            }else{
                update_new_url = baseurl + '/package/store';
            }

            showAjaxScript(update_new_url, new FormData(($('#add-new-package-form')[0])), function(response){
                $(".btn").button('reset');
                if (response.status == 'success') {
                    checkBoxArray = [];
                    $('#add-new-modal-package').modal('hide');
                    toastr.success(response.message, "Success", toastr_opts);
                    var PackageRefresh = $("#PackageRefresh").val();
                    data_table.fnFilter('', 0);
                }else{
                    toastr.error(response.message, "Error", toastr_opts);
                }
            });
        })
    });
</script>

@section('footer_ext')
    @parent
    <div class="modal fade" id="add-new-modal-package">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="add-new-package-form" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Add New Package</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 package-id">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Package Product ID</label>
                                    <input type="text" name="Name" class="form-control package-text" id="field-5" placeholder="" readonly>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-5" class="control-label">Name</label>
                                    <input type="text" name="Name" class="form-control" id="field-5" placeholder="">
                                    <input type="hidden" name="PackageId" >
                                </div>
                            </div>
                            {{--<div class="col-md-12">--}}
                                {{--<div class="form-group">--}}
                                    {{--<label for="field-1" class="control-label">Currency</label>--}}
                                    {{--{{ Form::select('CurrencyId', Currency::getCurrencyDropdownIDList(), '', array("class"=>"select2 small")) }}--}}
                                {{--</div>--}}
                            {{--</div>--}}
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="field-12" class="control-label">Rate Table</label>
                                    {{ Form::select('RateTableId', $rateTables,'', array("class"=>"select2 small")) }}
                                </div>
                            </div>
                            <br>

                            <div class="col-md-12">
                                <label class="control-label">Status</label>
                                <div class="panel-options">
                                    <div class="make-switch switch-small" >
                                        <input type="checkbox" name="status" checked>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="Package-update"  class="save btn btn-primary btn-sm btn-icon icon-left" data-loading-text="Loading...">
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
    <script>
        $(document).ready(function ($) {
            $("[data-action='showAddModal']").click(function(){
                $("#editRateTableId").val("");
                var defaultVal = "{{$defaultCurrencyId}}";
                setTimeout(function() {
                    $("#add-new-package-form [name='CurrencyId']").val(defaultVal).trigger("change");
                }, 2000);
            });
        });

//        function getRateTableByCurrency(){
//            var currency = $("#add-new-package-form [name='CurrencyId']").val();
//
//            currency = currency != "" ? currency : 0;
//            $.ajax({
//                url: baseurl + '/package/' + currency + '/get_currency_rate_table',
//                type: 'POST',
//                dataType: 'json',
//                cache: false,
//                success: function(response) {
//                    var options = "";
//                    $.each(response, function(x, y){
//                        options += "<option value='"+ x + "'>"+y+"</option>";
//                    });
//                    var rateTableField = $("#add-new-package-form [name='RateTableId']");
//                    rateTableField.html(options);
//                    var editVal = $("#editRateTableId").val();
//                    rateTableField.select2().select2('val', editVal);
//                }
//            });
//        }

//        $(document).ready(function () {
//            $("#add-new-package-form [name='CurrencyId']").change(function () {
//                getRateTableByCurrency();
//            });
//
//        });
    </script>
@stop