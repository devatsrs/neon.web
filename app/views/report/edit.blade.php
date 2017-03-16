@extends('layout.main')
@section('content')

    <ol class="breadcrumb bc-3">
        <li>
            <a href="{{action('dashboard')}}"><i class="entypo-home"></i>Home</a>
        </li>
        <li>
            <a href="{{URL::to('report')}}">Report</a>
        </li>
        <li class="active">
            <a href="javascript:void(0)">New Report</a>
        </li>
    </ol>

    @include('includes.errors')
    @include('includes.success')
    {{Form::select('Cube',array(''=>'Select','Summary'=>'Summary'),'',array("class"=>"select2 small"))}}
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-primary" data-collapsed="0">
                <!-- panel head -->
                <div class="panel-heading">
                    <div class="panel-title">Report</div>
                    <div class="panel-options">
                        <a href="#" data-toggle="report_filter" data-collapse-sidebar="1">
                            <i class="fa fa-filter"></i>
                        </a>
                    </div>

                </div>
                <!-- panel body -->
                <div class="panel-body">
                    <form role="form" class="form-horizontal form-groups-bordered">
                        <div class="form-group">
                            <div class="col-sm-3">
                                <label for="field-5" class="control-label">Cube</label>
                                {{Form::select('Cube2',array(''=>'Select','Summary'=>'Summary'),'',array("class"=>"select2 small"))}}
                            </div>
                            <div class="col-sm-9 vertical-border border_left">
                                <label for="field-5" class="control-label">Columns</label>
                                <div id="Columns_Drop" class="form-control ui-widget-content ui-state-default select2-container select2-container-multi">
                                    <ul class=" select2-choices ui-helper-reset"></ul>
                                </div>
                                <label for="field-5" class="control-label">Row</label>
                                <div id="Row_Drop" class="form-control ui-widget-content ui-state-default select2-container select2-container-multi ">
                                    <ul class=" select2-choices ui-helper-reset"></ul>
                                </div>

                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-3 vertical-border border_right">
                                <div class="row">
                                    <div class="col-sm-12 vertical-border border_bottom">
                                        <label for="field-5" class="control-label">Dimension</label>
                                    </div>
                                    <div   class="col-sm-12 vertical-border border_bottom" style="margin-top: 15px;padding-top: 15px">
                                        <div class="nested-list dd with-margins">
                                            <ul id="Dimension" class=" ui-helper-reset ui-helper-clearfix">
                                                <li class="dd-item select2-search-choice dimension">
                                                    <div class="dd-handle">
                                                        Account
                                                    </div>
                                                </li>
                                                <li class="dd-item select2-search-choice dimension">
                                                    <div class="dd-handle">
                                                        Gateway
                                                    </div>
                                                </li>
                                                <li class="dd-item select2-search-choice dimension">
                                                    <div class="dd-handle">
                                                        Trunk
                                                    </div>
                                                </li>
                                                <li class="dd-item select2-search-choice dimension">
                                                    <div class="dd-handle">
                                                        Country
                                                    </div>
                                                </li>
                                                <li class="dd-item select2-search-choice dimension">
                                                    <div class="dd-handle">
                                                        Prefix
                                                    </div>
                                                </li>
                                                <li class="dd-item select2-search-choice dimension">
                                                    <div class="dd-handle">
                                                        Vendor
                                                    </div>
                                                </li>
                                                <li class="dd-item select2-search-choice dimension">
                                                    <div class="dd-handle">
                                                        Currency
                                                    </div>
                                                </li>
                                            </ul>

                                        </div>
                                    </div>
                                    <div class="col-sm-12 vertical-border border_bottom" style="margin-top: 15px;padding-top: 15px">
                                        <label for="field-5" class="control-label">Measures</label>
                                    </div>
                                    <div class="col-sm-12 vertical-border" style="margin-top: 15px;padding-top: 15px">
                                        <div id="list-1" class="nested-list dd with-margins">
                                            <ul id="Measures" class="dd-list">
                                                <li class="dd-item select2-search-choice measures">
                                                    <div class="dd-handle">
                                                        Cost
                                                    </div>
                                                </li>
                                                <li class="dd-item select2-search-choice measures">
                                                    <div class="dd-handle">
                                                        Duration
                                                    </div>
                                                </li>
                                                <li class="dd-item select2-search-choice measures">
                                                    <div class="dd-handle">
                                                        No Of Calls
                                                    </div>
                                                </li>
                                                <li class="dd-item select2-search-choice measures">
                                                    <div class="dd-handle">
                                                        No Of Failed Calls
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-9">
                                <table class="table responsive">
                                    <colgroup span="3"></colgroup>
                                    <thead>
                                    <tr>
                                        <th scope="col">Country</th>
                                        <th scope="col">Account</th>
                                        <th >Cost</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <th rowspan="3" scope="rowgroup">India</th>
                                        <th scope="row">ABC</th>
                                        <td>100</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">DEF</th>
                                        <td>50</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">XYZ</th>
                                        <td>20</td>
                                    </tr>
                                    </tbody>
                                    <tbody>
                                    <tr>
                                        <th rowspan="2" scope="rowgroup">UK</th>
                                        <th scope="row">ABC</th>
                                        <td>40</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">XYZ</th>
                                        <td>80</td>
                                    </tr>
                                    </tbody>
                                </table>
                                <br>
                                <br>
                                <br>
                                <br>

                                <table class="table table-bordered">
                                    <col>
                                    <colgroup span="2"></colgroup>
                                    <colgroup span="2"></colgroup>
                                    <tr>
                                        <td rowspan="2"></td>
                                        <th colspan="2" scope="colgroup">Sippy</th>
                                        <th colspan="2" scope="colgroup">VOS</th>
                                    </tr>
                                    <tr>
                                        <th scope="col">Cost</th>
                                        <th scope="col">No Of Call</th>
                                        <th scope="col">Cost</th>
                                        <th scope="col">No Of Call</th>
                                    </tr>
                                    <tr>
                                        <th scope="row">India</th>
                                        <td>50,000</td>
                                        <td>30,000</td>
                                        <td>100,000</td>
                                        <td>80,000</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">UK</th>
                                        <td>10,000</td>
                                        <td>5,000</td>
                                        <td>12,000</td>
                                        <td>9,000</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="form-group">
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>
@stop