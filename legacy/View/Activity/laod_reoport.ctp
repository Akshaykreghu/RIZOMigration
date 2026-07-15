
<table id="activityReport" class="table no-margin" style="width:100%">
    <thead>
        <tr>
            <th>Name</th>
            <th>Task</th>
            <th>Work Done</th>
            <th>Project</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Duration</th>
            <!-- <th>Comments</th> -->
        </tr>
    </thead>
    <tbody>
        <?php foreach ($activity_status as $key => $value) { ?>
            <tr>
                <td><?= $value['emp_details']['first_name']; ?> <?= $value['emp_details']['last_name']; ?></td>
                <td><?= $value['atp']['summary']; ?></td>
                <td><?= $value['activity_track']['summary']; ?></td>
                <td><?= $value['ap']['activity_projects_head']; ?></td>
                <td><?= $value['activity_track']['start_time']; ?></td>
                <td><?= $value['activity_track']['end_time']; ?></td>
                <td><?= $value['activity_track']['duration']; ?></td>
                <!-- <td><?= $value['activity_track']['description']; ?></td> -->
            </tr>
        <?php } ?>
    </tbody>
</table>

<script>

    $(document).ready(function () {

        $('#activityReport').DataTable({
            "paging": true,
            "searching": true,
            "ordering": true,
            "info": true,
            dom: 'Bfrtip',
            buttons: [
                // {
                //      extend: 'print',
                //      messageTop: 'My Payroll Master Employees Attendance Report.',
                //      messageBottom: null,
                //      title: 'My Payroll Master - Attendance Report'
                // },
                // {
                //      extend: 'pdf',
                //      messageTop: 'My Payroll Master Employees Attendance Report.',
                //      messageBottom: null,
                //      title: 'My Payroll Master - Attendance Report'
                // },
                {
                     extend: 'excel',
                     messageTop: 'My Payroll Master Activity Report.',
                     messageBottom: null,
                     title: 'My Payroll Master - Activity Report'
                }
            ],
        });

    });

    $('.buttons-print').ready(function () {
        $('.buttons-print').html('<li class="fa fa-print"></li>').addClass('btn-primary').addClass('btn');
        $('.buttons-pdf').html('<li class="fa fa-file-pdf-o"></li>').addClass('btn-danger').addClass('btn');
        $('.buttons-excel').html('<li class="fa fa-file-excel-o"></li>').addClass('btn-success').addClass('btn');
   });     

</script>