@if(count($data)>0)
<table id="taskGrid" class="table table-bordered datatable dataTable" aria-describedby="taskGrid_info">
  <thead>
    <tr role="row">
      <th>Account</th>
      <th>Revenue</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
  <?php $total = 0; foreach($data as $key =>  $rows){   ?>
    <tr class="<?php if($key%2==0){echo "even";}else{echo "odd";} ?>">
      <td><?php echo $rows->Account; ?></td>
      <td><?php echo $rows->CurrencyCode.$rows->Revenue; ?></td>
      <td></td>
    </tr>
    <?php $total = $total+$rows->Revenue; } ?>
    <tr class="even">
    	<td><strong>Total</strong></td>
        <td><strong><?php echo $rows->CurrencyCode.$total; ?></strong></td>
        <td></td>
    </tr>
    <input type="hidden" name="revenueusertext" id="revenueusertext" value="<?php echo $rows->User; ?>" />
    <input type="hidden" name="revenuedate_range" id="revenuedate_range" value="<?php echo $rows->date_range; ?>" />
    <input type="hidden" name="revenuelisttype" id="revenuelisttype" value="<?php echo $rows->ListType; ?>" />
  </tbody>
</table>
@endif