<table class="table no-margin" id="empleaverequests">
    <thead>
        <tr>
<!--edited by sinsiya 13-06-2024-->
            <th>Sl No</th>
            <th>Employee Name</th>
            <th>Employee ID</th>
            <th>Branch </th>
            <th>Leave type</th>
            <th>Applied date</th>
            <th>From date</th>
            <th>To date</th>
            <th>Leave status</th>
        </tr>
    </thead>
    <tbody>
        <?php $i=1; foreach ($results as $key => $leaverequest) { ?>
            <tr>
                <td style="padding-left: 20px !important;"><?php echo $i;?></td>
                <td><?php echo $leaverequest[0]["emp_name"]; ?></td>
                <td><?php echo $leaverequest["emp_info"]["employee_id"]; ?></td>
                <td><?php echo $leaverequest["Branch"]["branch_name"]; ?></td>
                <td><?php echo $leaverequest["SalaryHeadItems"]["leave_type"]; ?> </td>
  <!-- edited by ASHIN ON 28-06-24 -->
                <td><?php echo date("d-m-Y", strtotime($leaverequest["LeaveRequests"]["applied_date"])); ?></td>
                <td><?php echo date("d-m-Y", strtotime($leaverequest["LeaveRequests"]["FROMDATE"])); ?></td>
                <td><?php echo date("d-m-Y", strtotime($leaverequest["LeaveRequests"]["TODATE"])); ?></td>
                <td><?php echo $leaverequest["LeaveRequests"]["LEAVESTATUS"]; ?></td>
            </tr>
        <?php $i++;} ?>
    </tbody>
</table>

<script>
    // edited by sinsiya on 18-06-2024
    $(document).ready(function () {
        var empleaverequeststable = $('#empleaverequests').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "columnDefs": [
                { "orderable": false, "targets": 0 } // Disable ordering on the first column (serial number)
            ],
            "drawCallback": function (settings) {
                var api = this.api();
                var start = api.page.info().start;
                api.column(0, { page: 'current' }).nodes().each(function (cell, i) {
                    cell.innerHTML = start + i + 1;
                });
            }
        });

        // Remove sorting classes and disable click events on the first column header
        $('#empleaverequests thead th:eq(0)').removeClass('sorting sorting_asc sorting_desc').off('click');
    });
</script>
