<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-primary panel-table">
            <div class="panel-heading">
                <h3>Notification</h3>
            </div>
            <div class="panel-body white-bg">
                <div class="row">
                    <div class="col-md-12">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#today" data-toggle="tab"><strong>Today</strong></a></li>
                            <li ><a href="#yesterday" data-toggle="tab"><strong>Today -1</strong></a></li>
                            <li ><a href="#yesterday2" data-toggle="tab"><strong>Today -2</strong></a></li>
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
                                                        <th width="30%">Date</th>
                                                        <th width="10%"></th>
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
                                                        <th width="30%">Date</th>
                                                        <th width="10%"></th>
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
                                                        <th width="30%">Date</th>
                                                        <th width="10%"></th>
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
                <div class="text-right">
                    <a href="{{URL::to('alert/history')}}" class="btn btn-primary text-right">View All</a>
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