<style>
    th {
        border: 1px solid black;
    }

    table {
        margin-top: 6px;
    }

    .table>thead>tr>th {
        vertical-align: middle;
        border-bottom: 1px solid black;
    }

    h1,
    h2,
    h3,
    h4,
    h5,
    h6 {
        margin: 0;
    }
</style>
<div style="position: relative; display: flex; align-items: center; justify-content: center;margin-top: 20px;">

    <!-- Left: Logo -->
    <!-- <div style="position: absolute; left: 0;">
        <img src="<?php echo $company_info[0]['comp_contact_info']['logo']; ?>" alt="Company Logo" style="height: 170px;" />
    </div> -->

    <!-- Center: Company Info -->
    <div style="text-align: center;">
        <h3 style="font-weight: bold; color: rgb(0, 116, 203);margin-bottom:10px;font-size:20px;">
            <?php echo $company_info[0]['comp_contact_info']['business_name']; ?>
        </h3>

        <h4 style="margin-bottom:10px;">(A Govt. of Kerala Public Sector Undertaking)</h4>

        <h4 style="margin-bottom:20px;">
            <?php echo $company_info[0]['comp_contact_info']['address']; ?>
        </h4>
    </div>

</div>
<div>
    <h3 style="font-weight: bold;text-align:center;margin-bottom:10px;font-size:20px;">Workflow - Officer Assessment <?php echo $year; ?></h3>
    <p style="text-align: center; font-size: 14px; font-weight: bold; margin-top: -5px;">
        (Report run by <?php echo $user_id; ?> at <?php echo $date_time; ?>)
    </p>
</div>
<?php
if (!empty($grouped_data)) {
?>
    <div style="overflow-x: auto;">
        <table class="table table-bordered" border="1" style="border:1px solid black;">
            <thead>
                <tr>
                    <th width="auto">Sl No</th>
                    <th width="auto">Employee ID</th>
                    <th width="auto">User ID</th>
                    <th width="auto">Employee Name</th>
                    <th width="auto">Date of Joining</th>
                    <th width="auto">Branch</th>
                    <th width="auto">Department</th>
                    <th width="auto">Designation </th>
                    <th width="auto">Termination Date</th>
                    <th width="auto">Reporting Officer Assessment Status</th>
                    <th width="auto">Reporting Officer Assessment Submission Date & Time</th>
                    <th width="auto">Reviewing Officer Assessment Status</th>
                    <th width="auto">Reviewing Officer Assessment Submission Date & Time</th>
                    <th width="auto">Completion Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($grouped_data)) {
                    $sl_no = 1;
                    foreach ($grouped_data as $emp) { ?>
                        <tr>
                            <td><?php echo $sl_no++; ?></td>
                            <td><?php echo $emp['employee_id']; ?></td>
                            <td><?php echo $emp['user_id'] ?></td>
                            <td><?php echo $emp['employee_name']; ?></td>
                            <td>
                                <?php
                                echo isset($emp['joining_date']) && !empty($emp['joining_date'])
                                    ? date('d-m-Y', strtotime($emp['joining_date']))
                                    : '';
                                ?>
                            </td>
                            <td><?php echo $emp['branch']; ?></td>
                            <td><?php echo $emp['department']; ?></td>
                            <td><?php echo $emp['employee_designation']; ?></td>
                            <td>
                                <?php
                                echo isset($emp['termination_date']) && !empty($emp['termination_date'])
                                    ? date('d-m-Y', strtotime($emp['termination_date']))
                                    : '';
                                ?>
                            </td>
                            <td><?php echo $emp['reporting_status']; ?></td>
                            <td><?php echo $emp['reporting_date']; ?></td>
                            <td><?php echo $emp['reviewing_status']; ?></td>
                            <td><?php echo $emp['reviewing_date']; ?></td>
                            <td><?php echo $emp['completion_status']; ?></td>
                        </tr>
                    <?php }
                } else { ?>
                    <tr>
                        <td colspan="14" style="text-align: center;">No data found</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

<?php } else { ?>

    <div style="font-size: 20px;text-align:left;">
        No data available under the selected criteria</div>
<?php } ?>