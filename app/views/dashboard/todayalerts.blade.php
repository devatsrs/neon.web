<div class="row">
    <div class="col-md-12">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#today" data-toggle="tab">Today</a></li>
            <li ><a href="#yesterday" data-toggle="tab">Yesterday</a></li>
            <li ><a href="#yesterday2" data-toggle="tab">Today -2 days</a></li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="today" >
                <div class="row">
                    <div class="col-sm-12">
                        <div class="panel panel-default loading">
                            <div class="panel-body with-table">
                                <table class="table table-bordered table-responsive today-alerts">
                                    <thead>
                                    <tr>
                                        <th width="30%">Name</th>
                                        <th width="30%">Type</th>
                                        <th width="30%">Created Date</th>
                                        <th width="10%">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="yesterday" >
                <div class="row">
                    <div class="col-sm-12">
                        <div class="panel panel-default loading">
                            <div class="panel-body with-table">
                                <table class="table table-bordered table-responsive yesterday-alerts">
                                    <thead>
                                    <tr>
                                        <th width="30%">Name</th>
                                        <th width="30%">Type</th>
                                        <th width="30%">Created Date</th>
                                        <th width="10%">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="yesterday2" >
                <div class="row">
                    <div class="col-sm-12">
                        <div class="panel panel-default loading">
                            <div class="panel-body with-table">
                                <table class="table table-bordered table-responsive yesterday2-alerts">
                                    <thead>
                                    <tr>
                                        <th width="30%">Name</th>
                                        <th width="30%">Type</th>
                                        <th width="30%">Created Date</th>
                                        <th width="10%">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var todays_alert = 1;
    var list_fields_index  = ["Name","AlertType","send_at","Subject","Message"];

</script>
@include('notification.alert-log')