<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>

<?php if (count($employee) != 0) { ?>
    <a class="btn btn-danger col-md-12">
        <h4>Alert! Please Retrieve the following assets before Process Full and Final</h4>
    </a>
<?php } ?>

<h2>Assets</h2>
<table class="table">
    <thead>
    <th>Asset</th>
    <th>Type</th>
    <th>Serial Number</th>
    <th>Date allocated</th>
<!--                                <th>Damage Amount</th>
    <th>Action</th>-->
</thead>
<?php if (count($employee) != 0) { ?>
    <?php foreach ($employee as $items) { ?>
        <tr>
            <td><?php echo $items['payroll_master']['month_year']; ?></td>
            <td><?php echo $items['payroll_master']['gross_salary']; ?></td>
            <td><?php echo $items['payroll_master']['net_salary']; ?></td>
            <td><?php echo $items['payroll_master']['approved']; ?></td>
        <!--                                    <td><input type="text" name="depreciation"></td>
            <td><button class="btn btn-primary">Return</button></td>-->
        </tr>    
    <?php } ?>
<?php
} else {
    echo "<tr><td>No Assets Found Here </td></tr>";
}
?>
</table>

<?php if (count($employee) == 0) { ?>
<div class="row" style="margin: auto ; " >
<button class="btn btn-primary pull-right" onclick="process();">Process Full and Final</button>
</div>
<?php } ?>