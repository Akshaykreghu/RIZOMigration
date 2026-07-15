<style type="text/css">
    body {
        line-height: 2em;
    }
    .block-container {
        width: 95%;
        padding: 20px;
        border: #000000 solid thin;
    }
    .sub-head {
        border-bottom: #000000 solid thin;
    }
    .row {
        height: 32px;
    }
    .col-md-4 {
        width: 33.33%;
        float: left;
    }
    table {
        border: 1px solid #f4f4f4;
        width: 80%;
        max-width: 80%;
        margin-bottom: 20px;
        background-color: transparent;
        border-spacing: 0;
        border-collapse: collapse;
    }
    td, th {
        text-align: left;
        padding: 8px;
        line-height: 1.42857143;
        vertical-align: top;
        border: 1px solid #B2B2B2;
    }
</style>

<?php
//echo $this->element('reportadminheader',array(
//'title'=>'Employee Location Report'));
?>
<h4>For The range of : <?php echo $from_dates - $to_dates;?></h4>
         


<table class="table table-responsive table-striped">
                <tbody>
                    <tr>
                        <th>Slno</th>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Branch</th>
                        <th>Date</th>
                        <th>Location</th>
<!--                        <th>Customer</th>
                        <th>Purpose</th>
                        <th>In/Out</th>-->
                    </tr>
                        <?php $i = 0; ?>
                        <?php foreach($arr_location as $val){
                            $i += 1; ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td><?php echo $val['employee_info']['employee_id']; ?></td>
                                <td><?php echo $val['employee_info']['EmpName']; ?></td>
                                <td><?php echo $val['employee_info']['department']; ?></td>
                                <td><?php echo $val['employee_info']['branch']; ?></td>
                                <td><?php echo $val['mob_user_tracking']['created_time']; ?></td>
                                <td><?php echo $val['mob_user_tracking']['location']; ?></td>
<!--                                <td><?php echo $val['mob_user_locations']['customer_name']; ?></td>
                                <td><?php echo $val['mob_user_locations']['purpose']; ?></td>
                                <td><?php echo ($val['mob_user_locations']['stepinout'] == 'step_in')?"IN":"OUT"; ?></td>-->
                            </tr>
                        <?php } ?>
                </tbody>
    </table>