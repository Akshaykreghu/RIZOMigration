<style>
  .table,
  td,
  th,
  tr {
    border-style: solid;
    border-color: #d4d4de;


  }

  .modal-content {
    width: 125% !important;

  }
</style>
<?php if ($mode == '') { ?>
  <div class="modal-body" style="overflow-y:auto;  padding-left:3%; padding-right:3%; padding-bottom:3%;">
    <h3 align="center" style="font-weight:bold; font-size: 30px;">Site Rate Details - <?php echo $monthdate; ?></h3>
    <h4 align="center" style="font-weight:bold;"><?php echo isset($user_name) ? "Report run by " . ($user_name) . " - " . $date_time : ''; ?></h4>

    <?php
    $i = 0;
    if (count($arr_siteattendance_for_template) == 0) {
      echo "<h2>No Data Available With The Selected Criteria</h2>";
    } else {
      foreach ($arr_siteattendance_for_template as $value) {
        if (count($value['summary']) !== 0) {
          $i += 1;
    ?>
          <div class="row">
            <div class="col-md-12" style="padding-top:5%;">
              <fieldset>
                <legend>
                  <?php if ($criterias == 'Site') {
                    echo isset($value['summary']['0']['site']['site_name']) ? "Details of - " . $value['summary']['0']['site']['site_name'] : ''; ?>
                  <?php echo isset($value['summary']['0']['site']['site_id']) ? " " . $value['summary']['0']['site']['site_id'] : '';
                  } else if ($criterias == 'EmployeeDetails') {
                    echo isset($value['summary']['0']['emp_details']['first_name']) ? "Details of - " . $value['summary']['0']['emp_details']['first_name'] . ' ' . $value['summary']['0']['emp_details']['last_name'] : '';
                  } else {
                    echo isset($value['summary']['0']['branches']['branch_name']) ? "Details of - " . $value['summary']['0']['branches']['branch_name'] : '';
                  } ?>

                </legend>
              </fieldset>
              <div class="box-body " style="overflow-y:auto; ">
                <fieldset>
                  <table class="table " style="overflow-y:initial;">



                    <thead>

                      <tr>
                        <th colspan="10">Employee Details</th>
                        <th colspan="6">Standard Rate</th>
                        <th colspan="6">Actual Rate</th>
                        <th colspan="4">Variance</th>
                      </tr>
                      <tr>
                        <th>SI No</th>
                        <th>Employee Name</th>
                        <th>Employee ID</th>
                        <th>Branch</th>
                        <th>Designation</th>
                        <th>Client Name</th>
                        <th>Site ID</th>
                        <th>Site Name</th>
                        <th>Shift Policy</th>
                        <th>Shift Hour</th>
                        <th>Days</th>
                        <th>Shift Hour</th>

                        <th>Sales Rate/Hour</th>
                        <th>Total Sales</th>
                        <th>Expense Rate/Hour</th>
                        <th>Total Expense</th>
                        <th>Shift Hour</th>
                        <th>Days</th>
                        <th>Sales Rate</th>
                        <th>Total Sales</th>
                        <th>Expense Rate</th>
                        <th>Total Expense</th>


                        <th>Site Profit</th>
                        <th>Sales</th>
                        <th>Expense</th>
                        <th>Shift Hours</th>

                      </tr>
                    </thead>
                    <tbody>
                      <?php $arr_data = $value['summary'];
                      if (count($arr_data) > 0) {
                        $tot = 0;
                        $i = 1;
                        foreach ($arr_data as $val) {

                          $slno = $i;
                          $sitename = $val['site']['site_name'];
                          $siteid = $val['site']['site_id'];
                          $clientname = $val['contacts']['company_name'];
                          $employeename = $val['emp_details']['first_name'] . ' ' . $val['emp_details']['last_name'];
                          $branch = $val['branches']['branch_name'];
                          $employeeid = $val['emp_proff']['emp_company_id'];
                          $designation = $val['designation']['desig_name'];
                          $shiftpolicy = $val['working_day_time_procedures']['day_time_desc'];
                          $shifthour = $val['0']['perday']; //employee shift hr
                          $shifthours = $val['0']['hours']; //actual shift hr
                          $day = $val['0']['days'];
                          $salesrate = $val['site_transactions']['srate'];
                          // Edited by Akshay on 21-8-2025
                          if (isset($val['site_transactions']['eratess']) && $val['site_transactions']['eratess'] !== null && $val['site_transactions']['eratess'] !== '') {
                            $expenserate = $val['site_transactions']['eratess'];
                          } elseif (isset($val[0]['eratess']) && $val[0]['eratess'] !== null && $val[0]['eratess'] !== '') {
                            $expenserate = $val[0]['eratess'];
                          } else {
                            $expenserate = 0; // or some default value
                          }
                          // End
                          $totalsales = round($shifthours * $salesrate);
                          $totalexpense = round($shifthours * $expenserate);
                          // standard rate
                          $start_date_effective = $val['site_transactions']['start_date_effective'];
                          $end_date_effective = $val['site_transactions']['end_date_effective'];


                          //calculation of standard days
                          $month = $startmonth;
                          $lastDateOfMonth = date("Y-m-t", strtotime($month));
                          $start = '';
                          $end = '';
                          if ($month > $start_date_effective && $lastDateOfMonth < $end_date_effective) {
                            if ($month > $start_date_effective) {
                              $start = $month;
                            } else {
                              $start = $start_date_effective;
                            }
                            if ($lastDateOfMonth < $end_date_effective) {
                              $end = $lastDateOfMonth;
                            } else {
                              $end = $end_date_effective;
                            }
                          } elseif ($month > $start_date_effective || $lastDateOfMonth < $end_date_effective) {
                            if ($month > $start_date_effective) {
                              $start = $month;
                            } else {
                              $start = $start_date_effective;
                            }
                            if ($lastDateOfMonth < $end_date_effective) {
                              $end = $lastDateOfMonth;
                            } else {
                              $end = $end_date_effective;
                            }
                          } else {
                            if ($month > $start_date_effective) {
                              $start = $month;
                            } else {
                              $start = $start_date_effective;
                            }
                            if ($lastDateOfMonth < $end_date_effective) {
                              $end = $lastDateOfMonth;
                            } else {
                              $end = $end_date_effective;
                            }
                          }
                          $startTimeStamp = strtotime($start);
                          $endTimeStamp = strtotime($end);

                          $timeDiff = abs($endTimeStamp - $startTimeStamp);
                          $numberDays = $timeDiff / 86400;  // 86400 seconds in one day
                          $numberDays = intval($numberDays) + 1;
                          $tot = $shifthour * $numberDays; // standard shift hr

                          $sh = $val['site_transactions']['srate']; // standard sales rate/hr

                          // Edited by Akshay on 21-8-2025
                          if (isset($val['site_transactions']['eratess']) && $val['site_transactions']['eratess'] !== null && $val['site_transactions']['eratess'] !== '') {
                            $eh = $val['site_transactions']['eratess'];
                          } elseif (isset($val[0]['eratess']) && $val[0]['eratess'] !== null && $val[0]['eratess'] !== '') {
                            $eh = $val[0]['eratess'];
                          } else {
                            $eh = 0; // or some default value
                          }
                          // End
                          $sts = round($tot * $sh); // standard total sales
                          $ste = round($tot * $eh); //  ''      total expense
                          /////////////varience/////
                          $siteprofit = round($totalsales - $totalexpense);
                          $sales = round($sts - $totalsales);
                          $expense = round($ste - $totalexpense);
                          $shift = $tot - $shifthours;
                      ?>
                          <tr>
                            <td><?php echo $slno; ?></td>
                            <td><?php echo $employeename; ?></td>
                            <td><?php echo $employeeid; ?></td>
                            <td><?php echo $branch; ?></td>
                            <td><?php echo $designation; ?></td>
                            <td><?php echo $clientname; ?></td>
                            <td><?php echo $siteid; ?></td>
                            <td><?php echo $sitename; ?></td>
                            <td><?php echo $shiftpolicy; ?></td>
                            <td><?php echo $shifthour; ?></td>
                            <td><?php echo $numberDays; ?></td>
                            <td><?php echo $tot; ?></td>

                            <td><?php echo $sh; ?></td>
                            <td><?php echo $sts; ?></td>
                            <td><?php echo $eh; ?></td>
                            <td><?php echo $ste; ?></td>
                            <td><?php echo $shifthours; ?></td>
                            <td><?php echo $day; ?></td>
                            <td><?php echo $salesrate; ?></td>
                            <td><?php echo $totalsales; ?></td>
                            <td><?php echo $expenserate; ?></td>
                            <td><?php echo $totalexpense; ?></td>
                            <td><?php echo $siteprofit; ?></td>
                            <td><?php echo $sales; ?></td>
                            <td><?php echo $expense; ?></td>
                            <td><?php echo $shift; ?></td>
                          </tr>
                      <?php $i++;
                        }
                      } ?>
                    </tbody>
                  </table>
                </fieldset>
              </div>
            </div>
          </div>

    <?php }
      }
    } ?>
  </div>
  </div>
  </div>
<?php  } else { ?>
  <?php //echo '<style>'.file_get_contents("css/pdfbootstrap.css").'</style>';     
  ?>
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

    td,
    th {
      text-align: left;
      padding: 8px;
      line-height: 1.42857143;
      vertical-align: top;
      border: 1px solid #B2B2B2;
    }
  </style>
  <?php
  //    echo $this->element('reportadminheader', array(
  //        'title' => 'Site Rate Details '));
  ?>

  <div class="row">
    <div class="col-md-12">
      <div class=" ">
        <?php
        $i = 0;
        if (count($arr_siteattendance_for_template) != 0) {
          foreach ($arr_siteattendance_for_template as $value) {
            if (count($value['summary']) !== 0) {
              $i += 1; ?>
              <fieldset>
                <legend> <?php echo isset($value['summary']['0']['site']['site_name']) ? "Details of - " . $value['summary']['0']['site']['site_name'] : ''; ?><?php echo isset($value['summary']['0']['site']['site_id']) ? " " . $value['summary']['0']['site']['site_id'] : ''; ?>

                </legend>
              </fieldset>
              <div class="box-body " style="overflow-y:auto; ">
                <fieldset>
                  <table class="table " style="overflow-y:initial;">



                    <thead>

                      <tr>
                        <th colspan="10">Employee Details</th>
                        <th colspan="6">Standard Rate</th>
                        <th colspan="6">Actual Rate</th>
                        <th colspan="4">Variance</th>
                      </tr>
                      <tr>
                        <th>SI No</th>
                        <th>Employee Name</th>
                        <th>Employee ID</th>
                        <th>Branch</th>
                        <th>Designation</th>
                        <th>Client Name</th>
                        <th>Site ID</th>
                        <th>Site Name</th>
                        <th>Shift Policy</th>
                        <th>Shift Hour</th>
                        <th>Days</th>
                        <th>Shift Hour</th>
                        <th>Sales Rate/Hour</th>
                        <th>Total Sales</th>
                        <th>Expense Rate/Hour</th>
                        <th>Total Expense</th>
                        <th>Shift Hour</th>
                        <th>Days</th>
                        <th>Sales Rate</th>
                        <th>Total Sales</th>
                        <th>Expense Rate</th>
                        <th>Total Expense</th>
                        <th>Site Profit</th>
                        <th>Sales</th>
                        <th>Expense</th>
                        <th>Shift Hours</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php $arr_data = $value['summary'];
                      if (count($arr_data) > 0) {
                        $tot = 0;
                        $i = 1;
                        foreach ($arr_data as $val) {

                          $slno = $i;
                          $sitename = $val['site']['site_name'];
                          $siteid = $val['site']['site_id'];
                          $clientname = $val['contacts']['company_name'];
                          $employeename = $val['emp_details']['first_name'] . ' ' . $val['emp_details']['last_name'];
                          $branch = $val['branches']['branch_name'];
                          $employeeid = $val['emp_proff']['emp_company_id'];
                          $designation = $val['designation']['desig_name'];
                          $shiftpolicy = $val['working_day_time_procedures']['day_time_desc'];
                          $shifthour = $val['0']['perday']; //employee shift hr
                          $shifthours = $val['0']['hours']; //actual shift hr
                          $day = $val['0']['days'];
                          $salesrate = $val['site_transactions']['srate'];
                          // Edited by Akshay on 21-8-2025
                          if (isset($val['site_transactions']['eratess']) && $val['site_transactions']['eratess'] !== null && $val['site_transactions']['eratess'] !== '') {
                            $expenserate = $val['site_transactions']['eratess'];
                          } elseif (isset($val[0]['eratess']) && $val[0]['eratess'] !== null && $val[0]['eratess'] !== '') {
                            $expenserate = $val[0]['eratess'];
                          } else {
                            $expenserate = 0; // or some default value
                          }
                          // End
                          $totalsales = round($shifthours * $salesrate);
                          $totalexpense = round($shifthours * $expenserate);
                          // standard rate
                          $start_date_effective = $val['site_transactions']['start_date_effective'];
                          $end_date_effective = $val['site_transactions']['end_date_effective'];


                          //calculation of standard days
                          $month = $startmonth;
                          $lastDateOfMonth = date("Y-m-t", strtotime($month));
                          $start = '';
                          $end = '';
                          if ($month > $start_date_effective && $lastDateOfMonth < $end_date_effective) {
                            if ($month > $start_date_effective) {
                              $start = $month;
                            } else {
                              $start = $start_date_effective;
                            }
                            if ($lastDateOfMonth < $end_date_effective) {
                              $end = $lastDateOfMonth;
                            } else {
                              $end = $end_date_effective;
                            }
                          } elseif ($month > $start_date_effective || $lastDateOfMonth < $end_date_effective) {
                            if ($month > $start_date_effective) {
                              $start = $month;
                            } else {
                              $start = $start_date_effective;
                            }
                            if ($lastDateOfMonth < $end_date_effective) {
                              $end = $lastDateOfMonth;
                            } else {
                              $end = $end_date_effective;
                            }
                          } else {
                            if ($month > $start_date_effective) {
                              $start = $month;
                            } else {
                              $start = $start_date_effective;
                            }
                            if ($lastDateOfMonth < $end_date_effective) {
                              $end = $lastDateOfMonth;
                            } else {
                              $end = $end_date_effective;
                            }
                          }
                          $startTimeStamp = strtotime($start);
                          $endTimeStamp = strtotime($end);

                          $timeDiff = abs($endTimeStamp - $startTimeStamp);
                          $numberDays = $timeDiff / 86400;  // 86400 seconds in one day
                          $numberDays = intval($numberDays) + 1;
                          $tot = $shifthour * $numberDays; // standard shift hr

                          $sh = $val['site_transactions']['srate']; // standard sales rate/hr
                          $eh = $val['site_transactions']['eratess']; // standard expense rate/hr
                          $sts = round($tot * $sh); // standard total sales
                          $ste = round($tot * $eh); //  ''      total expense
                          /////////////varience/////
                          $siteprofit = round($totalsales - $totalexpense);
                          $sales = round($sts - $totalsales);
                          $expense = round($ste - $totalexpense);
                          $shift = $tot - $shifthours;
                      ?>
                          <tr>
                            <td><?php echo $slno; ?></td>
                            <td><?php echo $employeename; ?></td>
                            <td><?php echo $employeeid; ?></td>
                            <td><?php echo $branch; ?></td>
                            <td><?php echo $designation; ?></td>
                            <td><?php echo $clientname; ?></td>
                            <td><?php echo $siteid; ?></td>
                            <td><?php echo $sitename; ?></td>
                            <td><?php echo $shiftpolicy; ?></td>
                            <td><?php echo $shifthour; ?></td>
                            <td><?php echo $numberDays; ?></td>
                            <td><?php echo $tot; ?></td>
                            <td><?php echo $sh; ?></td>
                            <td><?php echo $sts; ?></td>
                            <td><?php echo $eh; ?></td>
                            <td><?php echo $ste; ?></td>
                            <td><?php echo $shifthours; ?></td>
                            <td><?php echo $day; ?></td>
                            <td><?php echo $salesrate; ?></td>
                            <td><?php echo $totalsales; ?></td>
                            <td><?php echo $expenserate; ?></td>
                            <td><?php echo $totalexpense; ?></td>
                            <td><?php echo $siteprofit; ?></td>
                            <td><?php echo $sales; ?></td>
                            <td><?php echo $expense; ?></td>
                            <td><?php echo $shift; ?></td>
                          </tr>
                      <?php $i++;
                        }
                      } ?>
                    </tbody>
                  </table>
                </fieldset>
              </div>
          <?php }
          }
        }
        if (empty($arr_siteattendance_for_template)) {
          ?> <!-- /.box-body -->
          <h3 style="text-align:center;color:red;">No Records found under this Criteria</h3>
        <?php } ?>
      </div>
    </div>
  </div>


<?php } ?>