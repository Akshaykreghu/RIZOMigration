<style>
    th, td {
        padding: 5px;
        word-wrap: break-word;      /* Older browser support */
        word-break: break-word;     /* Modern support */
        overflow-wrap: break-word;  /* Ensures content wraps */
        white-space: normal;        
    }

    table {
        margin: 6px 0;
        table-layout: fixed;        /* Necessary for controlled layout */
        width: 100%;
        border-collapse: collapse;
    }

    th {
        font-weight: bold;
    }

    td {
        min-width: 80px;
        max-width: 200px;           /* Prevents excessive stretching */         /* Improves readability */
    }
    h1,h2,h3,h4,h5,h6{
    margin:0;
   }
</style>


<div style="position: relative; display: flex; align-items: center; justify-content: center;padding:40px 0;">
    <!-- Left: Logo -->
    <div style="position: absolute; left: 1.5%;">
        <img src="<?php echo $company_info[0]['comp_contact_info']['logo']; ?>" alt="Company Logo" style="height: 140px;" />
    </div>

    <!-- Center: Company Info -->
    <div style="text-align: center;">
        <h4 style="font-weight: bold; color: rgb(0, 116, 203);margin-bottom:10px;font-size:20px;">
            <?php echo $company_info[0]['comp_contact_info']['business_name']; ?>
        </h4>
        <h4 style="margin-bottom:10px;">(A Govt. of Kerala Public Sector Undertaking)</h4>
        <h4 style="margin-bottom:10px;"><?php echo $company_info[0]['comp_contact_info']['address']; ?></h4>
    </div>
</div>



<div style="overflow-x: auto; width: 100%;">
    <h3 style="font-weight: bold; text-align: center; font-size: 20px;">Employee Marks Report</h3>
    <table align="center" border="1">
        <thead>
            <tr>
                <th style="width: 3%;vertical-align:middle;">Sl No</th>
                <th style="width: 10%;vertical-align:middle;">Employee ID</th>
                <th style="width: 10%;vertical-align:middle;">User ID</th>
                <th style="width: 10%;vertical-align:middle;">Employee Name</th>
                <th style="width: 8%;vertical-align:middle;">Date of Joining</th>
                <th style="width: 8%;vertical-align:middle;">Termination Date</th>
                <th style="width: 10%;vertical-align:middle;">Reporting Officer</th>
                <th style="width: 8%;vertical-align:middle;">Total Mark by Reporting Officer(out of 100)</th>
                <th style="width: 10%;vertical-align:middle;">Reviewing Officer</th>
                <th style="width: 8%;vertical-align:middle;">Total Mark by Reviewing Officer(out of 100)</th>
                <th style="width: 8%;vertical-align:middle;">Grade</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($grouped_data)) {
                $sl_no = 1;
                foreach ($grouped_data as $emp) { ?>
                    <tr>
                        <td style="width: 3%;vertical-align:middle;"><?php echo $sl_no++; ?></td>
                        <td style="width: 10%;vertical-align:middle;"><?php echo $emp['employee_id']; ?></td>
                        <td style="width: 10%;vertical-align:middle;"><?php echo $emp['user_id']; ?></td>
                        <td style="width: 10%;vertical-align:middle;"><?php echo $emp['employee_name']; ?></td>
                        <td style="width: 8%;vertical-align:middle;"><?php echo date('d-m-Y', strtotime($emp['joining_date'])); ?></td>
                        <td style="width: 8%;vertical-align:middle;"><?php echo isset($emp['termination_date']) && !empty($emp['termination_date']) 
                                ? date('d-m-Y', strtotime($emp['termination_date'])) 
                                : ''; ?>
                        </td>
                        <td style="width: 10%;vertical-align:middle;"><?php echo $emp['reporting_officer']; ?></td>
                        <td style="width: 8%;vertical-align:middle;"><?php echo $emp['reporting_marks']; ?></td>
                        <td style="width: 10%;vertical-align:middle;"><?php echo $emp['reviewing_officer']; ?></td>
                        <td style="width: 8%;vertical-align:middle;"><?php echo $emp['reviewing_marks']; ?></td>
                        <td style="width: 8%;vertical-align:middle;"><?php echo $emp['grade']; ?></td>
                    </tr>
            <?php }
            } else { ?>
                <tr><td colspan="11" style="text-align: center;">No data found</td></tr>
            <?php } ?>
        </tbody>
    </table>
</div>
