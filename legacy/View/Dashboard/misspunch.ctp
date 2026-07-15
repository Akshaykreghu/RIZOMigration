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
                   <td><button type="button" onclick="viewEmployeeMissPunches(<?php echo $empmisspunch['ed']['emp_id']; ?>);" class="btn btn-linkedin">View/Edit</button></td>
                </tr>
            <?php $i++;} ?>
        </tbody>
    </table>
</div><!-- /.table-responsive -->
<script>
    $(document).ready(function () {
        // edited by sinsiya on 18-06-2024
        var empmisspunchestable = $('#empmisspunches').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": true,
            "order": [[1, "desc"]],
            "autoWidth": false,
   //edited by ASHIN on 28-06-24         
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
        
   //edited by ASHIN on 28-06-24       
        // Remove sorting classes and disable click events on the first column header
        $('#empmisspunches thead th:eq(0)').removeClass('sorting sorting_asc sorting_desc').off('click');
    });
</script>