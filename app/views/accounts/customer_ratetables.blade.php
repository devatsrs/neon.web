<div class="panel panel-primary" data-collapsed="0" id="Merge-components">
    <div class="panel-heading">
        <div class="panel-title">
            Customer Rate Tables
        </div>
        <div class="panel-options">
            <button type="button" onclick="createCloneRow('ratetableCustomer','getRateCustomerIDs')" id="Service-update" class="btn btn-primary btn-xs add-clone-row-btn" data-loading-text="Loading...">
                <i></i>
                +
            </button>
            <a href="#" data-rel="collapse"><i class="entypo-down-open"></i></a>
        </div>
    </div>
    <div class="panel-body">
        <div class="" style="overflow: auto;">
            <br/>
            <input type="hidden" id="getRateCustomerIDs" name="getRateCustomerIDs" value=""/>
            <table id="ratetableCustomer" class="table table-bordered datatable">
                <thead>
                <tr>
                    <th style="width:250px;">Customer</th>
                    <th style="width:350px !important;">Access Rate Table</th>
                    <th style="width:350px !important;">Package Rate Table</th>
                    <th style="width:320px !important;">Termination Rate Table</th>
                    <th style="width:250px !important;">Access Discount Plan</th>
                    <th style="width:250px !important;">Package Discount Plan</th>
                    <th style="width:250px !important;">Termination Discount Plan</th>

                </tr>
                </thead>
                <tbody id="tbody">
                    <?php
                                        $CalculatedVendorArr=array();
                                        if (isset($CustomerRatetable) && count($CustomerRatetable) > 0)
                                        {
                                        $a = 0;
                                        $hiddenClass='';
                                        ?>
    
                                        @foreach ($CustomerRatetable as $calculatedVendor)
                                            <?php
                                            $a++;
                                            if($a==1){
                                                $hiddenClass='hidden';
                                            }else{
                                                $hiddenClass='';
                                            }
                                            ?>
                                            <tr id="selectedRateRow-{{$a}}">
                                                <td class="Package-Div">
                                                    {{ Form::select('Customer-'.$a, $customers, $calculatedVendor->CustomerID, array("class"=>"select2")) }}
                                                </td>
                                                <td>
                                                   
                                                    {{ Form::select('Access-'.$a, $rate_table, $calculatedVendor->AccessRatetableID, array("class"=>"select2")) }}
                                                    
                                                </td>
                                                <td  class="DID-Div">
                                                    {{ Form::select('Package-'.$a, $package_rate_table, $calculatedVendor->PackageRatetableID, array("class"=>"select2")) }}
                                                </td>
                                                <td  class="DID-Div">
                                                    {{ Form::select('Termination-'.$a, $termination_rate_table, $calculatedVendor->TerminationRatetableID, array("class"=>"select2")) }}
                                                </td>
                                                <td  class="DID-Div">
                                                    {{ Form::select('AccessD-'.$a, $DiscountPlanDID, $calculatedVendor->AccessDiscountPlanID, array("class"=>"select2")) }}
                                                </td>
                                                <td  class="DID-Div">
                                                    {{ Form::select('PackageD-'.$a, $DiscountPlanPACKAGE, $calculatedVendor->PackageDiscountPlanID, array("class"=>"select2")) }}
                                                </td>
                                                <td  class="DID-Div">
                                                    {{ Form::select('TerminationD-'.$a, $DiscountPlan, $calculatedVendor->TerminationDiscountPlanID, array("class"=>"select2")) }}
                                                </td>
                                                
                                                <td>
                                                    {{--<button type="button" onclick="createCloneRow('ratetableSubBox','getRateIDs')" id="rate-update" class="btn btn-primary btn-sm add-clone-row-btn" data-loading-text="Loading...">--}}
                                                        {{--<i></i>--}}
                                                        {{--+--}}
                                                    {{--</button>--}}
                                                    <a onclick="deleteRow(this.id,'ratetableCustomer','getRateCustomerIDs')" id="CustomerCal-{{$a}}" class="btn btn-danger btn-sm" data-loading-text="Loading..." >
                                                        <i></i>
                                                        -
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                        <?php }?>
                </tbody>
            </table>
        </div>
    </div>
</div>