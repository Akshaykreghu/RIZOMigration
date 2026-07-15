<style>
    #empmisspunches {
   font-famly:'Source Sans Pro', 'Helvetica Neue', Helvetica, Arial, sans-serif;
    font-size: 14px;
    border: 1px solid #ccc;
}
#empmisspunches_wrapper{
   margin-top: 30px !important;
}

/* Style table headers */
#empmisspunches thead th {
    background-color: #337ab7;
    color: white;
    text-align: center;
}

/* Style rows */
#empmisspunches tbody td {
    padding: 8px 10px;
}

/* Search box */
.dataTables_filter input {
    height: 35px;
    border-radius: 4px;
    border: 1px solid #ccc;
    padding: 6px 10px;
}

/* Export buttons */
.dt-buttons .btn {
    margin-right: 5px;
    font-weight: bold;
}
</style>
<div class="table-responsive">
    <table class="table no-margin" id="empmisspunches">
<!--edited by sinsiya 13-06-2024-->
        <thead>
            <tr>
                <th>Sl No</th>
                <th>Employee Name</th>
                <th>Employee ID</th>
                <th>Branch </th>
                <th>Missed Attendance Count</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php $i=1; foreach ($results as $key => $empmisspunch) {  ?>
                <tr>
                    <td style="padding-left: 20px !important;"><?php echo $i;?>
                    <td><?php echo $empmisspunch[0]['fullname']; ?></td>
                    <td><?php echo $empmisspunch['emp_info']['employee_id']; ?></td>
                     <td><?php echo $empmisspunch['branch']['branch_name']; ?></td>
                    <td style="padding-left: 80px !important;"><?php echo $empmisspunch[0]["misscount"]; ?> </td>
                   <td><button type="button" onclick="viewEmployeeMissPunches(<?php echo $empmisspunch['ed']['emp_id']; ?>);" class="btn btn-linkedin">View/Edit</button>
                </td>
                </tr>
            <?php $i++;} ?>
        </tbody>
    </table>
</div>

<script>

</script>
