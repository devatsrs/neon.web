<style>
#table-oneofcharge_processing{
    position: absolute;
}
</style>
<div class="panel panel-primary" data-collapsed="0">
    <div class="panel-heading">
        <div class="panel-title">
            Additional Charges
        </div>
        <div class="panel-options">
            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
        </div>
    </div>
    <div class="panel-body">
        <div id="oneofcharge_filter" method="get" action="#" >
                                <div class="panel panel-primary panel-collapse" data-collapsed="1">
                                    <div class="panel-heading">
                                        <div class="panel-title">
                                            Filter
                                        </div>
                                        <div class="panel-options">
                                            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
                                        </div>
                                    </div>
                                    <div class="panel-body" style="display: none;">
                                        <div class="form-group">
                                            <label for="field-1" class="col-sm-1 control-label">Item</label>
                                            <div class="col-sm-2">
                                               {{Form::select('OneOfCharge_ProductID',$products,'',array("class"=>"select2 OneOfCharge_product_dropdown"))}}
                                            </div>
                                            <label for="field-1" class="col-sm-1 control-label">Description</label>
                                            <div class="col-sm-2">
                                                <input type="text" name="OneOfCharge_Description" class="form-control" value="" />
                                            </div>
                                            <label for="field-1" class="col-sm-1 control-label">Date</label>
                                            <div class="col-sm-2">
                                                <input type="text" name="OneOfCharge_Date" class="form-control datepicker"  data-date-format="yyyy-mm-dd" value=""   />
                                            </div>
                                            <div class="col-sm-3">
                                                <p style="text-align: right;">
                                                    <button class="btn btn-primary btn-sm btn-icon icon-left" id="oneofcharge_submit">
                                                        <i class="entypo-search"></i>
                                                        Search
                                                    </button>
                                                </p>
                                            </div>

                                        </div>

                                    </div>
                                </div>
        </div>
        <div class="text-right">
            <a  id="add-oneofcharge" class=" btn btn-primary btn-sm btn-icon icon-left"><i class="entypo-plus"></i>Add New</a>
            <div class="clear clearfix"><br></div>
        </div>
        <div class="dataTables_wrapper">
            <table id="table-oneofcharge" class="table table-bordered table-hover responsive">
                <thead>
                <tr>
                    <th width="20%">Item</th>
                    <th width="20%">Description</th>
                    <th width="2%">Quantity</th>
                    <th width="5%">Price</th>
                    <th width="10%">Date</th>
                    <th width="2%">Tax Amount</th>
                    <th width="5%">Created Date</th>
                    <th width="15%">Created By</th>
                    <th width="20%">Action</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
	var decimal_places = '{{$decimal_places}}';
    /**
    * JQuery Plugin for dataTable
    * */
    //var list_fields_activity  = ['OneOfCharge_ProductID','OneOfCharge_Description'];
    $("#oneofcharge_filter [name=OneOfCharge_ProductID]").val('');
    $("#oneofcharge_filter").find('[name="OneOfCharge_Description"]').val('');
    $("#oneofcharge_filter [name=OneOfCharge_Date]").val('');
    var data_table_char;
    var account_id='{{$account->AccountID}}';
    var ServiceID='{{$ServiceID}}';
    var update_new_url;
    var postdata;
    var $search = {};

    jQuery(document).ready(function ($) {
       var data_table_char;
       var list_fields  = ["Name","Description", "Qty" ,"Price","Date","TaxAmount","created_at","CreatedBy","AccountOneOffChargeID","ProductID","TaxRateID","TaxRateID2"];
       var getProductInfo_url = baseurl + "/accounts/{{$account->AccountID}}/oneofcharge/{id}/ajax_getproductinfo";
       var oneofcharge_add_url = baseurl + "/accounts/{{$account->AccountID}}/oneofcharge/store";
       var oneofcharge_edit_url = baseurl + "/accounts/{{$account->AccountID}}/oneofcharge/{id}/update";
       var oneofcharge_delete_url = baseurl + "/accounts/{{$account->AccountID}}/oneofcharge/{id}/delete";
       var oneofcharge_datagrid_url = baseurl + "/accounts/{{$account->AccountID}}/oneofcharge/ajax_datagrid";

        $search.OneOfCharge_ProductID = $("#oneofcharge_filter [name=OneOfCharge_ProductID]").val();
        $search.OneOfCharge_Description = $("#oneofcharge_filter").find('[name="OneOfCharge_Description"]').val();
        $search.OneOfCharge_Date = $("#oneofcharge_filter").find('[name="OneOfCharge_Date"]').val();

        data_table_char = $("#table-oneofcharge").dataTable({
            "bDestroy": true,
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": oneofcharge_datagrid_url,
            "fnServerParams": function (aoData) {
                        aoData.push({"name": "account_id", "value": account_id},
                                {"name": "ServiceID", "value": ServiceID},
                                {"name": "OneOfCharge_ProductID", "value": $search.OneOfCharge_ProductID},
                                {"name": "OneOfCharge_Description", "value": $search.OneOfCharge_Description},
                                {"name": "OneOfCharge_Date", "value": $search.OneOfCharge_Date});

                        data_table_extra_params.length = 0;
                        data_table_extra_params.push({"name": "account_id", "value": account_id},
                                {"name": "ServiceID", "value": ServiceID},
                                {"name": "OneOfCharge_ProductID", "value": $search.OneOfCharge_ProductID},
                                {"name": "OneOfCharge_Description", "value": $search.OneOfCharge_Description},
                                {"name": "OneOfCharge_Date", "value": $search.OneOfCharge_Date});

                    },
            "bPaginate": true,
            "iDisplayLength": 10,
            "sPaginationType": "bootstrap",
            "aaSorting": [[0, 'asc']],
            "sDom": "<'row'<'col-xs-6 col-left 'l><'col-xs-6 col-right'f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aoColumns": [
                {"bSortable": true},  // 0 Name
                {"bSortable": true},  // 1 Description
                {"bSortable": true},  // 2 Qty
                {"bSortable": true},  // 3 Price
                {"bSortable": true,   // 4 date
                    mRender: function (id, type, full) {
                        var ar = id.split(' ');
                        return ar[0];
                    }
                },
                {"bSortable": true},  // 5 Tax Amount
                {"bSortable": true},  // 6 Created at
                {"bSortable": true},  // 7 CreatedBy
                {                        // 9 Action
                    "bSortable": false,
                    mRender: function (id, type, full) {
                        action = '<div class = "hiddenRowData" >';
                        for (var i = 0; i < list_fields.length; i++) {
                            var str = '';
                            str = full[i];
                            if(list_fields[i]=='Date'){
                                var ar = str.split(' ');
                                str = ar[0];
                            }
                            action += '<input disabled type = "hidden"  name = "' + list_fields[i] + '"       value = "' + (full[i] != null ? str : '') + '" / >';
                        }
                        action += '</div>';
                        action += ' <a href="' + oneofcharge_edit_url.replace("{id}", id) + '" title="Edit" class="edit-oneofcharge btn btn-default btn-sm"><i class="entypo-pencil"></i>&nbsp;</a>'
                        action += ' <a href="' + oneofcharge_delete_url.replace("{id}", id) + '" title="Delete" class="delete-oneofcharge btn btn-danger btn-sm"><i class="entypo-trash"></i></a>'
                        return action;
                    }
                }
            ],
            "oTableTools": {
                "aButtons": [
                    {
                        "sExtends": "download",
                        "sButtonText": "Export Data",
                        "sUrl": oneofcharge_datagrid_url,
                        sButtonClass: "save-collection"
                    }
                ]
            },
            "fnDrawCallback": function () {
                $(".dataTables_wrapper select").select2({
                    minimumResultsForSearch: -1
                });
            }
        });
        $("#oneofcharge_submit").click(function(e) {

            e.preventDefault();
            public_vars.$body = $("body");
            $search.OneOfCharge_ProductID = $("#oneofcharge_filter [name=OneOfCharge_ProductID]").val();
            $search.OneOfCharge_Description = $("#oneofcharge_filter").find('[name="OneOfCharge_Description"]').val();
            $search.OneOfCharge_Date = $("#oneofcharge_filter").find('[name="OneOfCharge_Date"]').val();
            data_table_char.fnFilter('', 0);
            return false;
        });
                
        $('#oneofcharge_submit').trigger('click');
        //inst.myMethod('I am a method');
        $('#add-oneofcharge').click(function(ev){
                ev.preventDefault();
                $('#oneofcharge-form').trigger("reset");
                $('#modal-oneofcharge h4').html('Add Additional Charge');
                $("#oneofcharge-form [name=ProductID]").select2().select2('val',"");
                $("#oneofcharge-form [name='TaxRateID']").val(0).trigger("change");
				$("#oneofcharge-form [name='TaxRateID2']").val(0).trigger("change");
                $('.tax').removeClass('hidden');

                $('#oneofcharge-form').attr("action",oneofcharge_add_url);
                $('#modal-oneofcharge').modal('show');
        });
        $('table tbody').on('click', '.edit-oneofcharge', function (ev) {
                ev.preventDefault();
                $('#oneofcharge-form').trigger("reset");
                var edit_url  = $(this).attr("href");
                $('#oneofcharge-form').attr("action",edit_url);
                $('#modal-oneofcharge h4').html('Edit Additional Charge');
                var cur_obj = $(this).prev("div.hiddenRowData");
                for(var i = 0 ; i< list_fields.length; i++){
                    $("#oneofcharge-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val());
                    if(list_fields[i] == 'ProductID'){
                        $("#oneofcharge-form [name='"+list_fields[i]+"']").select2().select2('val',cur_obj.find("input[name='"+list_fields[i]+"']").val());
                    }else if(list_fields[i] == 'TaxRateID'){
                        $("#oneofcharge-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val()).trigger("change");
                    }else if(list_fields[i] == 'TaxRateID2'){
                        $("#oneofcharge-form [name='"+list_fields[i]+"']").val(cur_obj.find("input[name='"+list_fields[i]+"']").val()).trigger("change");
                    }
                }
                $('#modal-oneofcharge').modal('show');
        });
        $('table tbody').on('click', '.delete-oneofcharge', function (ev) {
                ev.preventDefault();
                result = confirm("Are you Sure?");
               if(result){
                   var delete_url  = $(this).attr("href");
                   submit_ajax_datatable( delete_url,"",0,data_table_char);
                   //data_table_char.fnFilter('', 0);
               }
               return false;
        });

       $("#oneofcharge-form").submit(function(e){
           e.preventDefault();
           var _url  = $(this).attr("action");
           
		   /*tax1 start*/
		   var option = $("#oneofcharge-form [name='TaxRateID'] option:selected");
           var Status = option.attr('data-status');
           var Amount = option.attr('data-amount');
           var TaxAmount1 = 0;
           var TotalPrice = parseFloat($('#oneofcharge-form [name="Price"]').val().replace(/,/g,'')) * parseInt($('#oneofcharge-form [name="Qty"]').val());
           if (Status == 1) {
               TaxAmount1 = parseFloat(Amount);
           } else {
               TaxAmount1 = (TotalPrice * Amount)/100;
           }
		   /*tax1 end*/
		   
		   
		    /*tax2 start*/
		   var option2 = $("#oneofcharge-form [name='TaxRateID2'] option:selected");
           var Status2 = option2.attr('data-status');
           var Amount2 = option2.attr('data-amount');
           var TaxAmount2 = 0;
           var TotalPrice2 = parseFloat($('#oneofcharge-form [name="Price"]').val().replace(/,/g,'')) * parseInt($('#oneofcharge-form [name="Qty"]').val());
           if (Status2 == 1) {
               TaxAmount2 = parseFloat(Amount2);
           } else {
               TaxAmount2 = (TotalPrice2 * Amount2)/100;
           }
		   /*tax2 end*/
		   
		    var tax_final  = 	parseFloat(TaxAmount1+TaxAmount2);
		   
           $('#oneofcharge-form [name="TaxAmount"]').val(tax_final.toFixed(parseInt(decimal_places)));
           //submit_ajax_datatable(_url,$(this).serialize(),0,data_table_char);
           $.ajax({
               url:_url, //Server script to process data
               type: 'POST',
               dataType: 'json',
               success: function(response) {
                   $(".btn").button('reset');
                   if (response.status == 'success') {
                       if(typeof response.warning != 'undefined' && response.warning != '') {
                           toastr.warning(response.warning, "Error", toastr_opts);
                       }
                       $('.modal').modal('hide');
                       toastr.success(response.message, "Success", toastr_opts);
                       if( typeof data_table_char !=  'undefined'){
                           data_table_char.fnFilter('', 0);
                       }

                   } else {
                       toastr.error(response.message, "Error", toastr_opts);
                   }

               },
               data: $(this).serialize(),
               //Options to tell jQuery not to process data or worry about content-type.
               cache: false
           });

           //data_table_char.fnFilter('', 0);
       });
	   
	   $("#oneofcharge-form [name='TaxRateID']").change(function(e) {
         check_same_tax();
    });
	
	$("#oneofcharge-form [name='TaxRateID2']").change(function(e) {		
         check_same_tax();
    });
	
	function check_same_tax(){
		var tax1val = $("#oneofcharge-form [name='TaxRateID']").val();
		var tax2val = $("#oneofcharge-form [name='TaxRateID2']").val(); 
		if(tax1val > 0 &&  (tax1val == tax2val)){
			toastr.error($("#oneofcharge-form [name='TaxRateID'] option:selected").text()+" already applied", "Error", toastr_opts);
		}
	}   
	   
       $('#oneofcharge-form [name="ProductID"]').change(function(e){
           id = $(this).val();
           getProductinfo(id);
        });

        function getProductinfo(id){
            if(id>0) {
                var url = getProductInfo_url.replace("{id}", id);
                $.ajax({
                    url: url,  //Server script to process data
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        $('#oneofcharge-form').find('[name="Description"]').val(response.Description);
                        $('#oneofcharge-form').find('[name="Price"]').val(response.Amount);
                    },
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false
                });
            }
        }
        function TotalPrice(){
            var total = $('#oneofcharge-form').find('[name="productPrice"]').val() * $('#oneofcharge-form').find('[name="Qty"]').val();
            $('#oneofcharge-form').find('[name="Price"]').val(total);
        }

        $(document).on("keypress",".Qty",function (event) {
            return isDecimal(event, this)
        });

    });

    function isDecimal(evt, element) {
        var charCode = (evt.which) ? evt.which : event.keyCode
        if (
                //(charCode != 45 || $(element).val().indexOf('-') != -1) &&      // “-” CHECK MINUS, AND ONLY ONE.
        (charCode != 46 || $(element).val().indexOf('.') != -1) &&      // “.” CHECK DOT, AND ONLY ONE.
        (charCode < 48 || charCode > 57)
        ) {
            return false;
        }
        return true;
    }
</script>
<!--@include('includes.ajax_data_grid')-->
@section('footer_ext')
@parent

<div class="modal fade in" id="modal-oneofcharge">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="oneofcharge-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Additional Charges</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                        <div class="form-group">
                            <label for="field-5" class="control-label">One of charge</label>
                            {{Form::select('ProductID',$products,'',array("class"=>"select2 product_dropdown"))}}

                            <input type="hidden" name="AccountOneOffChargeID" />
                            <input type="hidden" name="TaxAmount" />
                            <input type="hidden" name="ServiceID" value="{{$ServiceID}}">
                        </div>
                    </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                        <div class="form-group">
                            <label for="field-5" class="control-label">Description</label>
                            <input type="text" name="Description" class="form-control" value="" />
                        </div>
                    </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                        <div class="form-group">
                            <label for="field-5" class="control-label">Qty</label>
                            <input type="text" name="Qty" class="form-control Qty" value="1" data-min="1" />
                        </div>
                    </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                        <div class="form-group">
                            <label for="field-5" class="control-label">Date</label>
                            <input type="text" name="Date" class="form-control datepicker"  data-date-format="yyyy-mm-dd" value=""   />
                        </div>
                    </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                        <div class="form-group">
                            <label for="field-5" class="control-label">Price</label>
                            <input type="text" name="Price" class="form-control" value="0"   />
                        </div>
                    </div>
                    </div>
                    <div class="row tax">
                        <div class="col-md-12">
                        <div class="form-group">
                            <label for="field-5" class="control-label">Tax 1</label>
                            {{Form::SelectExt(
                                        [
                                        "name"=>"TaxRateID",
                                        "data"=>$taxes,
                                        "selected"=>'',
                                        "value_key"=>"TaxRateID",
                                        "title_key"=>"Title",
                                        "data-title1"=>"data-amount",
                                        "data-value1"=>"Amount",
                                        "data-title2"=>"data-status",
                                        "data-value2"=>"FlatStatus",
                                        "class" =>"select2 small TaxRateID",
                                        ]
                                )}}
                        </div>
                    </div>
                    </div>
                    
                    <div class="row tax">
                        <div class="col-md-12">
                        <div class="form-group">
                            <label for="field-5" class="control-label">Tax 2</label>
                            {{Form::SelectExt(
                                        [
                                        "name"=>"TaxRateID2",
                                        "data"=>$taxes,
                                        "selected"=>'',
                                        "value_key"=>"TaxRateID",
                                        "title_key"=>"Title",
                                        "data-title1"=>"data-amount",
                                        "data-value1"=>"Amount",
                                        "data-title2"=>"data-status",
                                        "data-value2"=>"FlatStatus",
                                        "class" =>"select2 small TaxRateID2",
                                        ]
                                )}}
                        </div>
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