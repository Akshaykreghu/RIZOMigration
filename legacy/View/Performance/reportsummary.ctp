<style>
   th{
        border:1px solid black;
   }
   table{
    margin-top: 6px;
   }
   .table>thead>tr>th {
    vertical-align: middle;
    border:1px solid black !important;
   }
   .table>tbody>tr>td {
    vertical-align: middle;
    border:1px solid black !important;
   }
   h1,h2,h3,h4,h5,h6{
    margin:0;
   }
   .nobr{
    white-space: nowrap;
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
     <h3 style="font-weight: bold;text-align:center;margin-bottom:10px;font-size:20px;">Employee Marks Report</h3>
</div>
 <table class="table" border="1" style="border:1px solid black;">
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
            <th width="auto">Reporting Officer</th>
            <th width="auto">Total Mark by Reporting Officer(out of 100)</th>
            <th width="auto">Reviewing Officer</th>
            <th width="auto">Total Mark by Reviewing Officer(out of 100)</th>
            <th width="auto">Grade</th>
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
                    <td  class="nobr">
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
                    <td><?php echo $emp['reporting_officer']; ?></td>
                    <td><?php echo $emp['reporting_marks']; ?></td>
                    <td><?php echo $emp['reviewing_officer']; ?></td>
                    <td><?php echo $emp['reviewing_marks']; ?></td>
                    <td><?php echo $emp['grade']; ?></td>
                </tr>
        <?php }
        } else { ?>
            <tr><td colspan="14" style="text-align: center;">No data found</td></tr>
        <?php } ?>
    </tbody>
</table>


