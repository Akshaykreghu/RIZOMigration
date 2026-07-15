<?php if ($mode == '') {  ?>
  

<?php } else { ?>
  <style type="text/css">
    body {
        line-height: 1.5em; /* Adjusted line height to fit more content */
    }
/*edited by ASHIN on 08-08-24*/
    .block-container {
        width: 100%;
        padding: 10px; /* Reduced padding */
        border: #000000 solid thin;
    }

    .sub-head {
        border-bottom: #000000 solid thin;
    }

    .row {
        height: auto; /* Adjusted height for auto adjustment */      /*edited by ASHIN on 09-08-24*/
    }

    .col-md-4 {
        width: 33.33%;
        float: left;
    }

    table {
        border: 1px solid #000000;
        width: 80%;
        margin-bottom: 20px; /* Reduced margin-bottom */
        background-color: transparent;
        border-spacing: 0;
        border-collapse: collapse;
    }

    td,
    th {
        text-align: center;
        padding: 2px; /* Reduced padding */
        line-height: 1; /* Adjusted line height */
        vertical-align: top;
        border: 1px solid #000000;
    }
    
       
      
       th.ip-no, td.ip-no {
        width: 15%; 
        }
        th.ip-name, td.ip-name {
        width: 20%; 
        }
        th.wages-paid, td.wages-paid {
        width: 30%; 
        }
       
        th.reason, td.reason {
        width: 30%; 
        }
        
  
</style>
<?php
echo $this->element('reportadminheader', array(
    'title' => 'ESI Monthly Contribution - ' . $mname . "  "  . $year . '<br> (Report Run by ' . $user_id . ' at ' . $date_time .')'
));

$hasData = false; // Initialize as false

if (count($arr_salary_for_template) > 0) { ?>
    <div class="box-body" style="padding-top:10px;">
        <table class="table table-bordered" align="center">
            <thead>
                <tr>
                    <th class="ip-no">IP Number</th>
                    <th class="ip-name">IP Name</th>
                    <th class="wages-paid">No of Days for which wages paid/payable during the month</th>
                    <th class="monthly-wages">Total Monthly Wages</th>
                    <th class="reason">Reason Code for Zero workings days(numeric only; provide 0 for all other reasons- Click on the link for reference)</th>
                    <th class="last_working">Last Working Day</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($arr_salary_for_template as $values) {
                    if (!empty($values)) {
                        $hasData = true; // Set true if data is found
                        ?>
                        <tr>
                            <td class="ip-no"><?php echo $values['IPNO']; ?></td>
                            <td class="ip-name"><?php echo $values['IPNAME']; ?></td>
                            <td class="wages-paid"><?php echo $values['NOOFWORKINGDAYS']; ?></td>
                            <td class="monthly-wages"><?php echo $values['TOTALMONTHLYWAGES']; ?></td>
                            <td class="reason"><?php echo $values['REASONCODE']; ?></td>
                            <td class="last_working"><?php echo $values['working_date']; ?></td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </tbody>
        </table>
       
    </div>
    <?php
}

if (!$hasData) { // Display message only if no data is found
    ?>
    <div style="font-size: 16px; text-align:left;">
        No data available under the selected criteria
    </div>
    <?php
}
}
?>
