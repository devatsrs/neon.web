@extends('layout.main')
@section('content')
<ol class="breadcrumb bc-3">
    <li>
        <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
    </li>
    <li>
        <a href="{{URL::to('accounts')}}">Accounts</a>
    </li>
    <li>
        {{customer_dropbox($id,["IsCustomer"=>1])}}
    </li>
    <li class="active">
        <strong>Customer Rate Sheet History</strong>
    </li>
</ol>
<h3>Customer Rate Sheet History</h3>
@include('accounts.errormessage')
<ul class="nav nav-tabs bordered"><!-- available classes "bordered", "right-aligned" -->
    <li>
        <a href="{{ URL::to('/customers_rates/'.$id) }}" >
            Customer Rate
        </a>
    </li>
    @if(User::checkCategoryPermission('CustomersRates','Settings'))
    <li >
        <a href="{{ URL::to('/customers_rates/settings/'.$id) }}" >
            Settings
        </a>
    </li>
    @endif
    @if(User::checkCategoryPermission('CustomersRates','Download'))
    <li>
        <a href="{{ URL::to('/customers_rates/'.$id.'/download') }}" >
            Download Rate Sheet
        </a>
    </li>
    @endif
    <li class="active">
        <a href="{{ URL::to('/customers_rates/'.$id.'/history') }}" >
            History
        </a>
    </li>
</ul>

<table class="table table-bordered datatable" id="table-4">
    <thead>
        <tr>
            <th>Title</th>
            <th>Created Date</th>
            <th>Created by</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>

    </tbody>
</table>



<script type="text/javascript">

    jQuery(document).ready(function($) {

        data_table = $("#table-4").dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": baseurl + "/customers_rates/{{$id}}/history_ajax_datagrid",
            "iDisplayLength": parseInt('{{CompanyConfiguration::get('PAGE_SIZE')}}'),
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aaSorting": [[1, 'desc']],
            "aoColumns":
                    [
                        {}, //0 tblJob.Title
                        {}, //1 tblRateSheetHistory.created_at
                        {},
                        {  // 2 tblJob.JobID
                            mRender: function(id, type, full) {
                                var show_="/customers_rates/{{$id}}/history/{id}/view";
                                show_ = show_.replace('{id}', id); // View id
                                var action='<a  onclick=" return showAjaxModal(\''+show_+'\',\'modal-customer-rate-history\');" href="javascript:;"  title="View"  class="btn btn-default btn-sm"><i class="fa fa-eye"></i></a>';
                                if(full[4] != null && full[5] != null &&  full[5]!= '' && full[5] != 'No data found!'){
                                    var download_= baseurl +"/jobs/"+full[4]+"/downloaoutputfile";
                                    action += ' <a  href="'+  download_ +'" class="btn btn-success btn-sm btn-icon icon-left"><i class="entypo-down"></i>Download</a>';
                                }
                                console.log(full);


                                return action;
                            }
                        },
                    ],
                    "oTableTools":
                    {
                        "aButtons": [
                            {
                                "sExtends": "download",
                                "sButtonText": "EXCEL",
                                "sUrl": baseurl + "/customers_rates/{{$id}}/history_exports/xlsx",
                                sButtonClass: "save-collection btn-sm"
                            },
                            {
                                "sExtends": "download",
                                "sButtonText": "CSV",
                                "sUrl": baseurl + "/customers_rates/{{$id}}/history_exports/csv",
                                sButtonClass: "save-collection btn-sm"
                            }
                        ]
                    }
        });

        $(".dataTables_wrapper select").select2({
            minimumResultsForSearch: -1
        });

        // Replace Checboxes
        $(".pagination a").click(function(ev) {
            replaceCheckboxes();
        });
    });

</script>
@stop            

@section('footer_ext')
@parent
<!-- Job Modal  (Ajax Modal)-->
<div class="modal fade" id="modal-customer-rate-history">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Detail</h4>
            </div>
            <div class="modal-body">
                Content is loading...
            </div>
        </div>
    </div>
</div>
@stop