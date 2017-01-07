<div class="row">
    <div class="col-sm-4">
        <div class="panel panel-default loading">
            <div class="panel-heading">
                <div class="panel-title">Most Dialled Number</div>
            </div>
            <div class="panel-body with-table">
                <table class="table table-bordered table-responsive most-dialled-number">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Number</th>
                        <th>Number of Times Dialled</th>
                        <th>Total Talk Time</th>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="panel panel-default loading">
            <div class="panel-heading">
                <div class="panel-title">Longest Durations Calls</div>
            </div>
            <div class="panel-body with-table">
                <table class="table table-bordered table-responsive long-duration-call">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Number</th>
                        <th>Extension</th>
                        <th>Duration</th>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="panel panel-default loading">
            <div class="panel-heading">
                <div class="panel-title">Most Expensive Calls</div>

            </div>
            <div class="panel-body with-table">
                <table class="table table-bordered table-responsive most-expensive-call">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Number</th>
                        <th>Extension</th>
                        <th>Duration</th>
                        <th>Cost</th>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        /* get calls reports for retail*/
        getMostExpensiveCall();

        /* get calls reports for retail*/
        getMostDailedCall();

        /* get calls reports for retail*/
        getLogestDurationCall();
    });
</script>