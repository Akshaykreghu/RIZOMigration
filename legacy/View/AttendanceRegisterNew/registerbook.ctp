<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<style>
    table.dataTable thead>tr>th {
        padding-right: 8px;
        text-align: center;
        color: #013f62;
        vertical-align: middle;
    }
    

    
    .pagination{
        position: absolute;
    right: 30px;
    margin: 20px 0 !important;
    }


    table.dataTable tbody>tr>td {
        text-align: center;
        
    }

    .btn {
    padding: 3px 12px;
    }
    div.dataTables_filter label {
    font-weight: bold;
    }
  td{
    font-size:11px !important;
  }

  .pagination{
        position: absolute;
    right: 30px;
    }
  

  /* #LeaveDetailsReports_paginate{
    position: absolute;
    right: 55px;
    bottom: 45%;
  } */

      .custom-btn {
    background-color: #fff !important;
    border: 1px solid #e0e0e0 !important;
    color: #555 !important;
    padding: 3px 16px;
    border-radius: 8px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    transition: all 0.2s;
    margin-top: 12px;
}



    /* Existing styles... */



/* Existing styles... */
    

    .btn .btn-success{
     padding: 3px 12px;   
     margin-top: 4px;
    }
    .form-control {
    border-radius: 3px !important;
    }
    @media (min-width: 768px){
     .form-inline .form-control {
    width: 150px;}}
    div.dataTables_filter {
        margin-top: 0px;
        position: absolute;
        top: 8px;
        right: 75px;
    }

    .dt-buttons {
        position: absolute;
        top: 8px;
        right: 20px;
    }

    .calendercolor {
        background: #cfe3ef;
    }

    .presentcolor {
        background: #a7e7a3;
    }

    .lopcolor {
        background: #ef6b6b;
    }

    /* Edited by Akshay on 22-4-2024 */
    .headcol {
        position: sticky;
        /* Adjust background color as needed */
        z-index: 2;
        /* Ensure it's above other elements */
    }
   /* edited by athira on 01-04-2025 */
    .left1 {
        left: 0px;
        width: 14px !important;
        background-color: #f2f2f2;
        /* border-left: 1px solid black; */
    }


    .left2 {
        left: 31px;
        width: 20px !important;
        background-color: #f2f2f2;
    }

    .left3 {
        left: 67px;
        width: 150px !important;
        background-color: #f2f2f2;
    }

    .left4 {
        left: 234px;
        width:50px !important;
       
    }

    .left5 {
        left: 301px;
        width:50px !important;
       
    }

    .left6 {
        left: 368px;
        width:50px !important;
       
    }

    .left7 {
        left: 435px;
        width:50px !important;
       
    }

    .left8 {
        left: 502px;
        width:50px !important;
       
    }

    .left9 {
        left: 569px;
        width:50px !important;
       
    }

    .left10 {
        left: 636px;
        width:50px !important;
       
    }

    .left11 {
        left: 893px;
    }
    table{
        table-layout:fixed;
        width:100%;
    }
    .sorting_disabled{
        width:55px ;

    }
  /* end */
    .table-bordered>thead>tr>th, .table-bordered>tbody>tr>th, .table-bordered>tfoot>tr>th, .table-bordered>thead>tr>td, .table-bordered>tbody>tr>td, .table-bordered>tfoot>tr>td {
    border: 1px solid #b8b8b8;
    }
    /* Media query for smaller screens */
    @media (max-width: 768px) {

        .table th,
        .table td {
            padding: 6px;
            /* Adjust padding for smaller screens */
        }
    }

    /* Media query for even smaller screens */
    @media (max-width: 576px) {

        .table th,
        .table td {
            padding: 4px;
            /* Further adjust padding for even smaller screens */
        }
    }

   .div-top{
    margin-top: -2px;
}
td.hide-att, th.hide-att {
    display: none;
}
td.hide-id, th.hide-id {
    display: none;
}
td,th{
    vertical-align:middle !important;
}


</style>


<div class="" style="margin-top: 20px;margin-left: 35px;padding-left:10px;display:flex;justify-content:left;align-items:center;">
  <!-- Status -->
  <div style="display:flex; align-items:center;">
    <label for="shift1combo" class="control-label shiftonelabel" style="white-space:nowrap;">
        Status <span style="padding-left:10px;padding-right:10px;">:</span>
    </label>
    
    <select id="shift1combo" class="form-control" style="width:150px;">
        <option value="0">Select</option>
        <option value="P/P">P/P</option>
        <option value="P/LOP">P/LOP</option>
        <option value="LOP/P">LOP/P</option>
        <option value="LOP/LOP">LOP/LOP</option>
    </select>
</div>


  <!-- Option -->
<div style="display:flex; align-items:center;padding-left:10px;">
    <label for="option" class="control-label shiftonelabel" style="white-space:nowrap;">
        Option <span style="padding-left:10px;padding-right:10px;">:</span>
    </label>

    <select id="option" class="form-control" style="width:150px;">
        <option value="all">All Dates</option>
        <option value="blank">LOP Dates</option>
    </select>
</div>


  <!-- Button -->
  <div class="col-md-3 col-sm-6">
    <button class="btn  w-100" style="background-color:#1e516e;border-color:#1e516e;color:#ffffff;" onclick="updateselecteditem()">Bulk Update</button>
  </div>
</div>

<section class="content" style="padding-left:15px;margin-bottom:8px;">
    <div style="display:flex;justify-content:left;align-items:center;">
    <button type="button" id="btn-verify" class="custom-btn">
       <i class="fa fa-check" style="color:green;"></i> Verify
    </button>

      <button id="toggleAttendanceCols" class="btn" style="margin-left: 148px;margin-top: 14px;font-size: 17px;padding:0px 14px;background-color:#1e516e;color:white;">+</button>

      <div style="display:block; margin-left:auto;margin-right:3px;">
    <button class="btn btn-success" onclick="$('#LeaveDetailsReports').DataTable().button('.buttons-excel').trigger();">
    <i class="fa fa-file-excel-o"></i>
</button>
</div>

</div>


    <!-- <div data-options="iconCls:'icon-save'" style="overflow:auto;padding:0px;" class="table table-responsive"> -->
      


  
    <div style="max-height: 1000px;overflow-x: auto; padding: 0;">

      
        <table class="table table-bordered" id="LeaveDetailsReports">
            <thead>
                <th class="headcol left1"> <input type="checkbox" id="selectAll" onclick="toggleAllCheckboxes()"></th>
                <th class="headcol left2">SN.</th>
                <th class="headcol left3" style="white-space: nowrap;"> &nbsp; &nbsp; &nbsp;Employee Name &nbsp; &nbsp; &nbsp;</th>
                <th class="hide-id">Employee ID</th>
                <th class="calendercolor headcol left4">Calendar Days</th>
                <th class="calendercolor headcol left5">Week Off</th>
                <th class="calendercolor headcol left6">Holiday</th>
                <th class="calendercolor headcol left7">Working Days</th>
                <th class="presentcolor headcol left8">Present Days</th>
                <th class="presentcolor headcol left9">Leaves</th>
                <th class="lopcolor headcol left10">LOP</th>

                <?//php 
                 //if (!empty($arr_dates)) {
                                    // foreach ($arr_dates as $d) {
                                    //     echo '<th class="day-header">' . $d . '</th>';
                                    // }
                //}
                ?>

                <?php
if (!empty($att_startdate2) && !empty($att_enddate2)) {
    $start = new DateTime($att_startdate2);
    $end = new DateTime($att_enddate2);

    while ($start <= $end) {
        echo '<th class="day-header">' . $start->format('d') . '</th>';
        $start->modify('+1 day');
    }
}
?>

                <?php
                // if (isset($employee_attendance) && count($employee_attendance) > 0) {
                  
                //     $date = current($employee_attendance);
                //     foreach ($date as $vals) {
                // ?>
                        <!-- <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo substr($vals['emp_detail_timeattandance']['att_date'], 8, 2); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th> -->
                 <?php
                //     }
                // }
                ?>
            </thead>
            <tbody>

                <?php
                $j = 1;
                foreach ($employee_attendance as $val) {
                    // debug($val);
                    // $i += 1;
                    // $name = $val['0']['empdetails']['first_name'] . ' '. $val['0']['empdetails']['middile_name'] .' ' . $val['0']['empdetails']['last_name'];
                    $name = $val['0']['emp_detail_timeattandance']['emp_name'] ;
                    //Edited by Akshay on 22-4-2024
                    $pkey = isset($val['0']['emp_detail_timeattandance']['emp_pkey']) ? $val['0']['emp_detail_timeattandance']['emp_pkey'] : (isset($val['0']['emp']['emp_fkey']) ? $val['0']['emp']['emp_fkey'] : 0);
                    // $edta_pkey =  isset($val['0']['emp_detail_timeattandance']['emp_detail_timeattandance_pkey']) ? $val['0']['emp_detail_timeattandance']['emp_detail_timeattandance_pkey'] : 0;
                ?>
                    <div title="expand" data-options="iconCls:'icon-save'">
                        <tr data-na-ho="<?php echo (float)$val[0]['emp_detail_timeattandance']['na_ho_count']; ?>"
    data-na-wo="<?php echo (float)$val[0]['emp_detail_timeattandance']['na_wo_count']; ?>"
    data-prorate="<?php echo (int)$val[0]['emp_detail_timeattandance']['prorate_code']; ?>"
    data-emptype="<?php echo (int)$val[0]['emp_detail_timeattandance']['emp_type']; ?>"
    >
                            <!-- <td class="headcol left1"> <input type="checkbox" class="otherCheckbox" value="<?php echo $edta_pkey ?>" id="select<?php echo $pkey ?>" onclick="updateSelectAll()"></td> -->
                            <td class="headcol left1"> <input type="checkbox" class="otherCheckbox" value="" id="select<?php echo $pkey ?>" onclick="updateSelectAll()"></td>
                            <td class="headcol left2"><?php echo $j; ?></td>

                            <!-- edited by athira on 01-04-2025 -->
                            
                             <td class="headcol left3" title="<?php echo $name ?>" style="max-width: 136px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <span>
                                    <?php 

                                    // $first_name = $val['0']['empdetails']['first_name'];
                                    // $middle_name = $val['0']['empdetails']['middile_name'];
                                    // $last_name = $val['0']['empdetails']['last_name'];

                                    // $full_name = trim($first_name . ' ' . $middle_name);

                                    $full_name=$val['0']['emp_detail_timeattandance']['emp_name'];
                                    // Check if the length exceeds 20 characters
                                    if (strlen($full_name) > 20) {
                                        $trimmed = substr($full_name, 0, 20);
                                        $last_space_index = strrpos($trimmed, " ");
                                        
                                        if ($last_space_index !== false) {
                                            // Trim at the last space to avoid cutting the middle of a word
                                            $emp_name = substr($trimmed, 0, $last_space_index);
                                        } else {
                                            // If no space found, just take the first 20 characters
                                            $emp_name = $trimmed;
                                        }
                                    } else {
                                        // If first + middle name is within 20 characters, use it as is
                                        $emp_name = $full_name;
                                    }

                                    echo $emp_name;
                                    ?>
                                <!-- end   -->
                                </span>
                                 <i class="fa fa-angle-down pull-right" onclick="showEditOnPopupOption('<?php echo trim($name); ?>', '<?php echo trim($pkey); ?>')" ></i> 
                                </div>
                            </td>
                            <td class="hide-id"><?php echo $val[0]['emp_detail_timeattandance']['emp_company_id']; ?></td>
                            
                            <td class="calendercolor headcol left4"><?php echo $val[0]['emp_detail_timeattandance']['calander_days']; ?></td>
                            <td class="calendercolor headcol left5"><?php echo $val[0]['emp_detail_timeattandance']['weekoff_total']; ?></td>
                            <td class="calendercolor headcol left6"><?php echo $val[0]['emp_detail_timeattandance']['holiday_total']; ?></td>
                            <td class="calendercolor headcol left7"><?php echo $val[0]['emp_detail_timeattandance']['working_days']; ?></td>
                            <td class="presentcolor headcol left8"><?php echo $val[0]['emp_detail_timeattandance']['presant_total']; ?></td>
                            <td class="presentcolor headcol left9"><?php echo $val[0]['emp_detail_timeattandance']['leave_total']; ?></td>
                             <!-- edited by athira on 13-04-2026 -->
                             <td class="lopcolor headcol left10"><?php echo ($val[0]['emp_detail_timeattandance']['prorate_code'] == 2) ? (isset($val[0]['emp_detail_timeattandance']['wd_lop_total']) ? $val[0]['emp_detail_timeattandance']['wd_lop_total'] : 0) : (isset($val[0]['emp_detail_timeattandance']['lop_total']) ? $val[0]['emp_detail_timeattandance']['lop_total'] : 0); ?></td>
                             <!-- ended by athira on 13-04-2026 -->
                            <?php


$dates = [];
$start = new DateTime($att_startdate2);
$end = new DateTime($att_enddate2);
while ($start <= $end) {
    $dates[] = $start->format('Y-m-d');
    $start->modify('+1 day');
}

for ($i = 1; $i <= count($dates); $i++) {
    $field = "FIELD" . $i;

    if (isset($val[0]['emp_detail_timeattandance'][$field])) {
        $value = trim($val[0]['emp_detail_timeattandance'][$field]);
        $emp_pkey = $val[0]['emp_detail_timeattandance']['emp_pkey'];
        $att_date = isset($dates[$i - 1]) ? $dates[$i - 1] : '';

        // ✅ Basic styles
        $style = "text-align:center;white-space:nowrap;font-weight:bold;";

        // ✅ Color based on status
        // Detect if each half is a Policy leave or Indirect status
        $parts = explode('/', $value);
        $w = count($parts) > 1 ? 0.5 : 1;
        $fh_is_policy = false;
        $sh_is_policy = false;
        
        $m = isset($lop_leave_map[$emp_pkey][$att_date]) ? $lop_leave_map[$emp_pkey][$att_date] : [];
        if (!is_array($m)) $m = [];

        // FH check
        $p1 = strtoupper(trim($parts[0]));
        if (strpos($p1, 'LOP') !== false) {
             if (in_array(1, $m) || in_array(3, $m)) $fh_is_policy = true;
        } elseif (!in_array($p1, ['P','A','WO','HO','NA',''])) {
             $fh_is_policy = true;
        }

        // SH check
        $p2 = isset($parts[1]) ? strtoupper(trim($parts[1])) : $p1;
        if (strpos($p2, 'LOP') !== false) {
             if (in_array(2, $m) || in_array(3, $m)) $sh_is_policy = true;
        } elseif (!in_array($p2, ['P','A','WO','HO','NA',''])) {
             $sh_is_policy = true;
        }
        
        // Single flag for overall styling if needed; but granular for data attr
        $isPolicyLeaveOnThisDate = ($fh_is_policy || $sh_is_policy);

        if ($value == 'P' || $value == 'P/P') {
            $style .= "color:white;background-color:#06a226;";
        } elseif (in_array($value, ['A', 'A/A', 'P/A', 'A/P'])) {
            $style .= "color:white;background-color:#06a226;";
        } elseif (!$isPolicyLeaveOnThisDate && ($value === 'LOP/LOP' || $value === 'LOP')) { // 🔴 Exact match for Indirect LOP
            $style .= "color:white;background-color:#e02429;";
        } elseif (!$isPolicyLeaveOnThisDate && strpos($value, 'LOP') !== false) { // 🟠 Full day Indirect combinations
            $style .= "color:white;background-color:#ef8656;"; // choose your color
        } elseif ($value == 'WO' || $value=="WO/WO") {
            $style .= "color:white;background-color:#dcdc00;";
        } elseif ($value == 'HO' || $value =="HO/HO") {
            $style .= "color:white;background-color:#2d2df4;";
        } elseif (trim($value) == '') {
            $style .= "color:white;background:#ebebeb;";
        } elseif ($value == 'NA') {
            $style .= "color:#666;background:#f0f0f0;"; // greyed out look
        }

        $displayValue = trim($value);
        $fhStr = ($fh_is_policy ? 'true' : 'false');
        $shStr = ($sh_is_policy ? 'true' : 'false');
        $isPolicyAttr = "data-ispolicy='{$fhStr},{$shStr}'";

        echo "<td style='{$style}'>";
        echo "<span id='{$emp_pkey}_column_{$i}' {$isPolicyAttr} style='display:inline-block;'>{$displayValue}</span>";

        
$hideArrow = $val[0]['emp_detail_timeattandance']['hide_arrow'][$i]; // boolean

if (!$hideArrow) {
    echo "<i class='fa fa-angle-down pull-right' 
            style='cursor:pointer;margin-left:4px;'
            onclick='attendanceModal(\"{$month}\", \"{$emp_pkey}\", \"{$i}\", \"{$att_date}\")'></i>";
}




        echo "</td>";
    } else {
        echo "<td style='text-align:center;'>-</td>";
    }
}
                                
                            // foreach($arr_dates as $val){
                            //     debug($val);
                            // }
                            // foreach ($val as $value) {
                            //     debug($val);
                            // }
                               
                            //     $newstatus = isset($value['eup']['main_status'])?trim($value['eup']['main_status']):trim($value['status']);
                                
                                
                                
                            // ?>
                               <!-- <td <?php if ($newstatus == 'WO') { ?> style="color:#e5e50c;white-space: nowrap;font-weight:bold;"  <?//php } ?>  -->
                                   <?//php if ($newstatus == 'HO') { ?> style="color:blue;white-space: nowrap;font-weight:bold;" <?//php } ?> 
                                   <?//php if ($newstatus == 'A/A') {  ?> style="color:red;white-space: nowrap;font-weight:bold;" <?//php } ?> 
                                   <?//php if ($newstatus == '') {  ?> style="background:#ebebeb;white-space: nowrap;font-weight:bold;" <?//php } ?> 
                                   <?//php if ($newstatus == 'P/P' || strpos($newstatus, 'P/P') !== false) {  ?> style="color:green;white-space: nowrap;font-weight:bold;" <?php }; ?> 
                                   <?//php if ($newstatus == 'P/A') {  ?> style="color:red;white-space: nowrap;font-weight:bold;" <?//php }; ?> 
                                   <?//php if ($newstatus == 'LOP/A') {  ?> style="color:red;white-space: nowrap;font-weight:bold;" <?//php }; ?> 
                                   <?//php if ($newstatus == 'A/LOP' || $newstatus == 'P/LOP') { ?> style="color:red;white-space: nowrap;font-weight:bold;" <?//php }; ?> 
                                   <?//php if ($newstatus == 'LOP/LOP' || $newstatus == 'LOP' || strpos($newstatus, 'LOP') !== false) { ?> style="color:red;white-space: nowrap;font-weight:bold;" <?//php }; ?> 
                                   <?//php if ($newstatus == 'A/P') { ?> style="color:red;white-space: nowrap;font-weight:bold; " <?//php }; ?>>
                                   <!-- <span id="<?//php echo  $value['emp_detail_timeattandance']['emp_pkey'] . '_column' ?>" style="display: inline-block;"><?php echo $newstatus; ?></span> -->
                                   <?php // if ($value['editable'] == true) { ?>
                                     <!-- <i style="display: inline-block;" onclick="attendanceModal(<?php echo $month; ?>,<?php echo $value['emp_detail_timeattandance']['emp_pkey']; ?>, <?php echo $value['emp_detail_timeattandance']['emp_pkey'] ?> )" class="fa fa-angle-down pull-right"></i> -->
                                     <?php //}else{?>
                              <!-- <span onclick="alert('Attendance Verified');"><i class="fa fa-angle-down pull-right"></i></span> -->
                                 <?php //} ?>
                            <!--      </td> -->
                            <?php
                            // }
                            ?>
                        </tr>
                    </div>
                    

  

                     <tr class="<?php echo trim($name).'in'; ?> expand-row expand-<?php echo $pkey; ?>" 
    id="<?php echo $pkey.'_in'; ?>" style="display:none;">

    <td class="headcol left1"></td>
    <td class="headcol left2"></td>
    <td class="headcol left3" style="text-align:right;">IN</td>
    <td class="hide-id"></td>
    <td class="headcol left4 calendercolor"></td>
    <td class="headcol left5 calendercolor" style="color:#cfe3ef;"></td>
    <td class="headcol left6 calendercolor" style="color:#cfe3ef;"></td>
    <td class="headcol left7 calendercolor" style="color:#cfe3ef;"></td>
    <td class="headcol left8 presentcolor" style="color:#a7e7a3;"></td>
    <td class="headcol left9 presentcolor" style="color:#a7e7a3;"></td>
    <td class="headcol left10 lopcolor" style="color:#ef6b6b;"></td>

    <?php
    for ($i = 1; $i <= count($dates); $i++) {
        $timein = isset($val[0]['emp_detail_timeattandance']['att_in'][$i])
            ? $val[0]['emp_detail_timeattandance']['att_in'][$i]
            : '';
        echo "<td>{$timein}</td>";
    }
    ?>
</tr>

<tr class="<?php echo trim($name).'out'; ?> expand-row expand-<?php echo $pkey; ?>" 
    id="<?php echo $pkey.'_out'; ?>" style="display:none;">

    <td class="headcol left1"></td>
    <td class="headcol left2"></td>
    <td class="headcol left3" style="text-align:right;">OUT</td>
    <td class="hide-id"></td>
    <td class="headcol left4 calendercolor"></td>
    <td class="headcol left5 calendercolor" style="color:#cfe3ef;"></td>
    <td class="headcol left6 calendercolor" style="color:#cfe3ef;"></td>
    <td class="headcol left7 calendercolor" style="color:#cfe3ef;"></td>
    <td class="headcol left8 presentcolor" style="color:#a7e7a3;"></td>
    <td class="headcol left9 presentcolor" style="color:#a7e7a3;"></td>
    <td class="headcol left10 lopcolor" style="color:#ef6b6b;"></td>

    <?php
    for ($i = 1; $i <= count($dates); $i++) {
        
        $timeout = isset($val[0]['emp_detail_timeattandance']['att_out'][$i])
            ? ($val[0]['emp_detail_timeattandance']['att_out'][$i])
            : '';
        echo "<td>{$timeout}</td>";
    }
    ?>
</tr>
<tr class="<?php echo trim($name).'duration'; ?> expand-row expand-<?php echo $pkey; ?>" 
    id="<?php echo $pkey.'_duration'; ?>" style="display:none;">

    <td class="headcol left1"></td>
    <td class="headcol left2"></td>
    <td class="headcol left3" style="text-align:right;">Duration</td>
    <td class="hide-id"></td>
    <td class="headcol left4 calendercolor"></td>
    <td class="headcol left5 calendercolor" style="color:#cfe3ef;"></td>
    <td class="headcol left6 calendercolor" style="color:#cfe3ef;"></td>
    <td class="headcol left7 calendercolor" style="color:#cfe3ef;"></td>
    <td class="headcol left8 presentcolor" style="color:#a7e7a3;"></td>
    <td class="headcol left9 presentcolor" style="color:#a7e7a3;"></td>
    <td class="headcol left10 lopcolor" style="color:#ef6b6b;"></td>

    <?php
    for ($i = 1; $i <= count($dates); $i++) {
        $minutes = $val[0]['emp_detail_timeattandance']['duration'][$i] ;
        echo "<td>{$minutes}</td>";
    }
    ?>
</tr>
                <?php
                $j++;
                }
                ?>
            </tbody>


        </table>
        
    </div>
    <!--  <div id="aa" class="easyui-accordion" style="width:300px;height:200px;">
     <div title="Title1" data-options="iconCls:'icon-save'" style="overflow:auto;padding:10px;">
         <h3 style="color:#0099FF;">Accordion for jQuery</h3>
         <p>Accordion is a part of easyui framework for jQuery. 
         It lets you define your accordion component on web page more easily.</p>
     </div>
     <div title="Title2" data-options="iconCls:'icon-reload',selected:true" style="padding:10px;">
         content2
     </div>
     <div title="Title3">
         content3
     </div>
 </div> -->
</section>

<script type="text/javascript">

    
    function applyCellColor(emp, day, value, isPolicyLeave) {
    let cell = $("#" + emp + "_column_" + day).closest("td");

    // Reset cell color
    cell.css({ "color": "", "background-color": "" });
    if (!value) return;
    value = value.toString().trim();

    // ✅ Special handling for Direct LOP (if updated via policy)
    if (isPolicyLeave && value.includes("LOP")) {
        return; // skip red background
    }

    // ✅ Full day Present
    if (value == "P/P") {
        cell.css({ "color": "white", "background-color": "#06a226" });
    }
    // ✅ Full day Absent
    else if (value === "A") {
        cell.css({ "color": "white", "background-color": "#06a226" });
    }
    // ✅ Full day LOP (Indirect)
    if (value === "LOP/LOP" || value === "LOP") {
        cell.css({ "color": "white", "background-color": "#e02429" });
    }
    // 🟠 Any Half LOP (Indirect)
    else if (value.includes("LOP")) {
        cell.css({ "color": "white", "background-color": "#ef8656" }); 
    }
    // ✅ Full day Weekly Off
    else if (value === "WO" || value ==="WO/WO") {
        cell.css({ "color": "white", "background-color": "#dcdc00" });
    }
    // ✅ Full day Holiday
    else if (value === "HO" || value ==="HO/HO") {
        cell.css({ "color": "white", "background-color": "#2d2df4" });
    }
    // ✅ Empty
    else if (value.trim() === "") {
        cell.css({ "color": "white", "background": "#ebebeb" });
    }
}



    function verifySelectedEmployees() {
    var checkedRowsData = [];

    // Collect selected employees
    $('input[type="checkbox"]:checked').not('#selectAll').each(function() {
        var row = $(this).closest('tr');
        var emp_id = $(this).val(); // emp_detail_timeattandance_pkey
        var empPkey = $(this).attr('id').replace("select", "");

        // Collect daily statuses and policy flags for this employee
        var timesheet = {};
        var policysheet = {};
        row.find('td').each(function(index) {
            if (index > 10) { // skip first 11 columns (SN, Name, ID, totals)
                var dayNum = index - 10;
                var $span = $(this).find('span');
                timesheet[dayNum] = $(this).text().trim();
                policysheet[dayNum] = $span.attr('data-ispolicy') || $span.data('ispolicy') || "false,false";
            }
        });

        checkedRowsData.push({
            emp_id: emp_id,
            empPkey: empPkey,
            timesheet: timesheet,
            policysheet: policysheet
        });
    });

    if (checkedRowsData.length === 0) {
        alert('Please select at least one employee to verify.');
        return;
    }

    $.ajax({
        type: "POST",
        url: livesite + "AttendanceRegisterNew/verifyAttendance",
        data: {
            emp_data: checkedRowsData,
            branch_code: $("#filterby_branch").val(),
            month_year: $("#filterby_month").val()
        },
        dataType: 'json',
        success: function(resp) {
            if (resp.success) {
                $.notify('Selected employee(s) verified successfully!', {
                    type: 'success',
                    allow_dismiss: false
                });
                // filterRegister(); // refresh the table
                filterRegister('N', false, 'Y');
            } else {
                $.notify('Error verifying attendance!', {
                    type: 'danger',
                    allow_dismiss: false
                });
            }
        }
    });
}


// Attach to your verify button
$('#btn-verify').on('click', verifySelectedEmployees);

    $('#aa').accordion({
        animate: true
    });

    $(document).ready(function() {
        $('.in').hide();
        $('.out').hide();
        $('.duration').hide();
        $('.tabset0').pwstabs({
            effect: 'scale', // You can change effects of your tabs container: scale / slideleft / slideright / slidetop / slidedown / none
            defaultTab: 1, // The tab we want to be opened by default
            containerWidth: '100%', // Set custom container width if not set then 100% is used
            tabsPosition: 'horizontal', // Tabs position: horizontal / vertical
            horizontalPosition: 'top', // Tabs horizontal position: top / bottom
            verticalPosition: 'left', // Tabs vertical position: left / right
            responsive: true, // Make tabs container responsive: true / false - boolean
            theme: '',
            rtl: false // Right to left support: true/ false
        });
        /*$('#togglechartweek').on('click', function () {
         $("weekChartRow").show();
         $("monthChartRow").hide();
         
         })
         $('#togglechartmonth').on('click', function () {
         $("weekChartRow").hide();
         $("monthChartRow").show();
         })*/

         if ($.fn.DataTable.isDataTable('#LeaveDetailsReports')) {
 $('#LeaveDetailsReports').DataTable().destroy();
}
        var table=   $('#LeaveDetailsReports').DataTable({
            "paging": true,
            "pageNumber": true,
            "lengthChange": true,
            "searching": true,
            "ordering": false,
            "info": false,
            dom: 'Bfrtip',
            buttons: [
{
    extend: 'excel',

    title: function () {
        var monthVal = $('#filterby_month').val() || $('#filterby_month option:selected').text();
        return 'Attendance Register - ' + monthVal;
    },

    action: function (e, dt, node, config) {
        $("#LeaveDetailsReports tbody tr").each(function () {
            updateLopCount($(this));
        });
        $.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, node, config);
    },

    // exportOptions: {
    //     columns: function (idx) {
    //         if (idx >= 3 && idx <= 9) return false;
    //         return true;
    //     }
    // },
    exportOptions: {
    columns: function (idx) {
        if (idx === 0) return false;          // skip vacant column A
        if (idx === 3) return true;           // include Employee ID
        if (idx >= 4 && idx <= 10) return false; // skip totals (previously 3-9)
        return true;
    }
},

    customize: function (xlsx) {

    var sheet = xlsx.xl.worksheets['sheet1.xml'];
    var styles = xlsx.xl['styles.xml'];

    /* ---------- INJECT CUSTOM TOP+BOTTOM BORDER STYLE ---------- */

    var bordersNode = $('borders', styles);
    var borderCount = parseInt(bordersNode.attr('count'), 10);

    // Top + Bottom only, NO left/right
    bordersNode.append(
        '<border>' +
            '<left/>' +
            '<right/>' +
            '<top style="thin"><color rgb="FF000000"/></top>' +
            '<bottom style="thin"><color rgb="FF000000"/></bottom>' +
            '<diagonal/>' +
        '</border>'
    );
    bordersNode.attr('count', borderCount + 1);
    var newBorderIdx = borderCount;

    // Register new xf style referencing this border
    var cellXfsNode = $('cellXfs', styles);
    var xfCount = parseInt(cellXfsNode.attr('count'), 10);

    cellXfsNode.append(
        '<xf numFmtId="0" fontId="0" fillId="0" borderId="' + newBorderIdx + '" xfId="0" applyBorder="1"/>'
    );
    cellXfsNode.attr('count', xfCount + 1);
    var borderStyleIdx = xfCount; // dynamic index — use this instead of hardcoded '25'

    /* ---------- TITLE CENTER + BOLD ---------- */

    var firstRow = $('row:first', sheet);
    firstRow.find('c').attr('s', '51');

    /* ---------- IN OUT DURATION RIGHT ALIGN ---------- */

    $('row c', sheet).each(function () {
        var cell = $(this);
        var text = cell.find('t').text();

        if (text === 'IN' || text === 'OUT' || text === 'Duration') {
            cell.attr('s', '52');
        }
    });

    /* ---------- TOP + BOTTOM BORDER FOR ALL DATA ROWS ---------- */

    $('row:gt(0)', sheet).each(function () {
        $(this).find('c').each(function () {
            var cell = $(this);
            var text = cell.find('t').text();

            // Skip IN / OUT / Duration cells (they have their own style)
            if (text !== 'IN' && text !== 'OUT' && text !== 'Duration') {
                cell.attr('s', borderStyleIdx); // top+bottom only
            }
        });
    });

}
}
],
            "autoWidth": false,
            "lengthMenu": [
                [40, 50, -1],
                [40, 50, "All"]
            ]
        });

    $('#LeaveDetailsReports').on('draw.dt', function() {
    // Hide Employee ID & hide totals (index 4-10) again after redraw
    $("#LeaveDetailsReports thead th").eq(3).addClass('hide-id');
    $("#LeaveDetailsReports thead th").slice(4, 11).addClass('hide-att');
    $("#LeaveDetailsReports tbody tr").each(function() {
        $(this).find('td').eq(3).addClass('hide-id');
        $(this).find('td').slice(4, 11).addClass('hide-att');
    });

    // Reset toggle button icon
    $("#toggleAttendanceCols").text("+");
});



        $('.buttons-print').ready(function() {
            $('.buttons-print').html('<li class="fa fa-print"></li>').addClass('btn-primary').addClass('btn');;
        });
        $('.buttons-pdf').html('<li class="fa fa-file-pdf-o"></li>').addClass('btn-danger').addClass('btn');;
        $('.buttons-excel').html('<li class="fa fa-file-excel-o"></li>').addClass('btn-success').addClass('btn');

        // Event listener to the two range filtering inputs to redraw on input
        $('#leaverequests-emp-filter, #leaverequests-month-filter').change(function() {
            empleaverequeststable.search(this.value).draw();
        });

        table.on('draw', function() {
    $("#LeaveDetailsReports tbody tr").each(function() {
        updateLopCount($(this));
    });
});
    });

//     $('#LeaveDetailsReports').on('draw.dt', function () {
//     // Uncheck all checkboxes on every page
//     $('#LeaveDetailsReports').find('input[type="checkbox"]').prop('checked', false);

//     // Also reset your "select all"
//     $('#selectAll').prop('checked', false);

//     // Your other code (hide columns)
//     $("#LeaveDetailsReports thead th").slice(3, 10).addClass('hide-att');
//     $("#LeaveDetailsReports tbody tr").each(function() {
//         $(this).find('td').slice(3, 10).addClass('hide-att');
//     });

//     $("#toggleAttendanceCols").text("+");
// });


    //Edited by Akshay on 21-4-2024
    function attendanceModal(month, emp_pkey, dayIndex, att_date) {
//         var currentStatuses = $('#'+edtPkey+'_column').val();
// console.log(currentStatuses);
//         if(currentStatuses =='NA'){
//                    alert("Status cannot change.");
//                    return false;
//           }
        if (true) {
            showSmallModalForm(livesite + 'AttendanceRegisterNew/editpunch/' + month + '/' + emp_pkey + '/' + dayIndex + '/' + att_date );
        } else {
            alert("Please select a record!")
        }
    }

    // function showEditOnPopupOption(name, emp_pkey) {
    //     //Edited by Akshay on 22-4-2024
    //     var id1 = '#' + emp_pkey + "_in";
    //     var id2 = '#' + emp_pkey + "_out";
    //     var id3 = '#' + emp_pkey + "_duration";
    //     // console.log('id1', id1);
    //     $(id1).toggle();
    //     $(id2).toggle();
    //     $(id3).toggle();
    // }

    function showEditOnPopupOption(name, emp_pkey) {
    // IDs of the rows to toggle
    var idIn = '#' + emp_pkey + "_in";
    var idOut = '#' + emp_pkey + "_out";
    var idDuration = '#' + emp_pkey + "_duration";

    // Collapse all other expanded rows first
    $('.expand-row').not(idIn).not(idOut).not(idDuration).hide();

    // Toggle the selected employee's rows
    $(idIn).toggle();
    $(idOut).toggle();
    $(idDuration).toggle();
}


    function updateselecteditem() {
        var status = $("#shift1combo").val();

        if ($('input[type="checkbox"]:checked').not('#selectAll').length === 0) {
            alert('Please choose any employee.');
            return;
        }

        if (status === '0') {
            alert('Please choose any status.');
            return;
        }

        // ✅ Add confirmation alert for bulk update
        var confirmMsg = "Are you sure? Any manual changes (like leaves or status changes) you previously made for these employees will be replaced and lost.";
        if (!confirm(confirmMsg)) {
            return;
        }

        var checkedRowsData = []; // Create an array to store the data of checked rows
        // Find all checkboxes that are checked (excluding the one with id "selectAll")
        $('input[type="checkbox"]:checked').not('#selectAll').each(function() {
            // Get the closest row to the checked checkbox
            var row = $(this).closest('tr');

            // Retrieve data from the row and create a rowData object
            // Build policysheet based on UI span attributes
            var policysheet = {};
            row.find('td').slice(11).each(function(index) {
                var day = index + 1;
                var isPol = $(this).find('span').attr('data-ispolicy');
                if (isPol) {
                    policysheet[day] = isPol;
                }
            });

            var rowData = {
                // Customize these based on your table structure
                'edtaPkey': row.find('.headcol.left1 input[type="checkbox"]').val().trim(),
                'empPkey': row.find('.headcol.left1 input[type="checkbox"]').attr('id').replace("select", ""),
                'policysheet': policysheet
            };

            var shift = $('#shift1combo').val();

            // Push the rowData object to the checkedRowsData array
            checkedRowsData.push(rowData);
        });
        


        $.ajax({
            type: "POST",
            url: livesite + "AttendanceRegisterNew/bulkipdatestatus",
            data: {
                device_attandance_seq: checkedRowsData,
                adstatus: $("#option").val(),
                status: status,
                monthYear: $("#filterby_month").val()
            },
            dataType: 'json',

            success: function(resp) {
    if (resp.success) {
        // edited by athira on 26-02-2026
        let skippedCount = 0;
        resp.updates.forEach(update => {
            if (update.status === 'Skipped - Leave exists') {
                skippedCount++;
                return;
            }

    const empId = update.emp_pkey;
    const fieldNo = update.field.replace('FIELD', '');
    const newValue = update.new ? update.new.trim() : '';

    const $row = $(`#select${empId}`).closest('tr');
    const tdIndex = parseInt(fieldNo) + 11;
    const $cell = $row.find(`td:nth-child(${tdIndex})`);

    let oldValue = $cell.find("span").text().trim();

    // --------------------------------------------
    // ⭐ 1) Initialize cache for this employee
    // --------------------------------------------
    initLeaveCacheForEmployee(empId);

    // helper to find correct key (copy from your working page)
    function findKey(code) {
        if (!code) return null;
        const want = code.trim().toUpperCase();
        const map = window._leaveCache[empId] || {};

        for (let k of Object.keys(map)) {
            if (k && k.toUpperCase() === want) return k;
        }
        for (let k of Object.keys(map)) {
            if (k && k.toUpperCase().startsWith(want)) return k;
        }
        return null;
    }

    function restoreHalf(code) {
        if (!code) return;
        let key = findKey(code);
        if (!key) return;
        let cur = parseFloat(window._leaveCache[empId][key] || 0);
        window._leaveCache[empId][key] = +(cur + 0.5).toFixed(2);
    }

    function deductHalf(code) {
        if (!code) return;
        let key = findKey(code);
        if (!key) return;
        let cur = parseFloat(window._leaveCache[empId][key] || 0);
        let upd = cur - 0.5;
        if (upd < 0) upd = 0;
        window._leaveCache[empId][key] = +upd.toFixed(2);
    }

    // --------------------------------------------
    // ⭐ 2) Split into halves
    // --------------------------------------------
    let [oFH, oSH] = oldValue.includes("/") ? oldValue.split("/") : [oldValue, oldValue];
    let [nFH, nSH] = newValue.includes("/") ? newValue.split("/") : [newValue, newValue];

    oFH = oFH.trim(); oSH = oSH.trim();
    nFH = nFH.trim(); nSH = nSH.trim();

    // --------------------------------------------
    // ⭐ 3) Restore old leave → Deduct new leave
    // --------------------------------------------
    // const skipList = ["P","P/P","P/A","A/P","A","NA","WO","HO","/WO",""];

    // restoreHalf(oFH);
    // restoreHalf(oSH);

    // if (!skipList.includes(newValue)) {
    //     deductHalf(nFH);
    //     deductHalf(nSH);
    // }

    function isLeave(code) {
    if (!code) return false;
    code = code.trim().toUpperCase();

    // exclude non-leaves
    const nonLeaves = ["P","A","LOP","NA","WO","HO",""];
    return !nonLeaves.includes(code);
}

// ✅ Restore ONLY if old was leave
if (isLeave(oFH)) restoreHalf(oFH);
if (isLeave(oSH)) restoreHalf(oSH);

// ✅ Deduct ONLY if new is leave
if (isLeave(nFH)) deductHalf(nFH);
if (isLeave(nSH)) deductHalf(nSH);

    // --------------------------------------------
    // ⭐ 4) Sync buttons (update balance labels)
    // --------------------------------------------
    syncButtonsFromCache(empId);

    // --------------------------------------------
    // ⭐ 5) NOW update UI
    // --------------------------------------------
    $cell.find("span").text(newValue);

    // Get selected option from dropdown
    let selectedOption = $("#option").val();

    // Check if we should treat this as a "LOP date"
    let isBlankDate = selectedOption === "blank";

    let finalValue = newValue;

    let isPolStr = $cell.find("span").attr('data-ispolicy') || $cell.find("span").data('ispolicy') || "false,false";
    let isPolArr = typeof isPolStr === 'string' ? isPolStr.split(",").map(x => x.trim() === 'true') : [false, false];
    
    if (isBlankDate) {
        finalValue = replaceLopPart(oldValue, newValue, isPolArr);
        $cell.find("span").text(finalValue);
    }
    
    let isPolicyLeaveForColor = isPolArr[0] || isPolArr[1];
    applyCellColor(empId, fieldNo, finalValue, isPolicyLeaveForColor);
    updateLopCount($row);

});


        // resp.updates.forEach(update => {

        //     const empId = update.emp_pkey;
        //     const fieldNo = update.field.replace('FIELD', '');
        //     const newValue = update.new ? update.new.trim() : '';

        //     const $row = $(`#select${empId}`).closest('tr');
        //     const tdIndex = parseInt(fieldNo) + 10;
        //     const $cell = $row.find(`td:nth-child(${tdIndex})`);
            
        //     let oldValue = $cell.find("span").text().trim();

            

        //     // ⚠️ Only blank dates should enter this logic
        //     // A blank date = any old value that contains LOP in any position
        //     // Example: LOP, P/LOP, LOP/P, CL/LOP, OD/LOP, LOP/CL etc.
        //     let isBlankDate = oldValue.includes("LOP");

        //     let finalValue = newValue;

        //     if (isBlankDate) {
        //         finalValue = replaceLopPart(oldValue, newValue);
        //     }

        //     // Update the cell UI
        //     $cell.find("span").text(finalValue);

        //     applyCellColor(empId, fieldNo, finalValue);
        //     updateLopCount($row);

        // });

        $('input[type="checkbox"]').prop('checked', false);
        $('#selectAll').prop('checked', false);

        // edited by athira on 26-02-2026
        if (skippedCount > 0) {
            alert(skippedCount + " records were skipped because a leave is already applied for those dates.");
        }
        $.notify('Status updated successfully.', { type: 'success', allow_dismiss: false });
        // ended by athira on 26-02-2026
    }
}




        });
    }


    function initLeaveCacheForEmployee(empKey) {
        // if (!window._leaveCache[empKey]) window._leaveCache[empKey] = {};
          // Create cache root
    if (!window._leaveCache) window._leaveCache = {};

    // Create entry for employee if missing
    if (!window._leaveCache[empKey]) {
        window._leaveCache[empKey] = {}; 
    }

    // Create internal balance map if missing
    if (!window._leaveBalanceMap) window._leaveBalanceMap = {};

    if (!window._leaveBalanceMap[empKey]) {
        window._leaveBalanceMap[empKey] = {};
    }
        // For each leave button, ensure cache has a value; if exists, rewrite label to cached value
        $("button[data-leavekey][data-leavebal]").each(function(){
            var code = (($(this).attr('value')||'')+"").toUpperCase();
            if (!code) return;
            var cached = window._leaveCache[empKey][code];
            if (typeof cached === 'number' && !isNaN(cached)) {
                // rewrite label with cached
                var base = ($(this).text()||'').split('(')[0].trim();
                $(this).text(base + ' (' + formatBalanceLabel(cached) + ')');
                $(this).data('leavebal', cached);
            } else {
                // seed cache from current button
                var dat = $(this).data('leavebal');
                var val = (typeof dat === 'undefined') ? parseFloat(((($(this).text()||'').match(/\(([-0-9.]+)\)/)||[])[1]||0)) : parseFloat(dat)||0;
                window._leaveCache[empKey][code] = val;
            }
        });
    }

    function syncButtonsFromCache(empKey, code) {
        if (!window._leaveCache[empKey]) return;
        var val = window._leaveCache[empKey][code];
        if (typeof val !== 'number' || isNaN(val)) return;
        $("button[data-leavekey][value='"+code+"']").each(function(){
            $(this).data('leavebal', val);
            var base = ($(this).text()||'').split('(')[0].trim();
            $(this).text(base + ' (' + formatBalanceLabel(val) + ')');
        });
    }


function replaceLopPart(oldVal, newVal, isPolicyArr) {
    let [oldFH, oldSH] = oldVal.includes("/") ? oldVal.split("/") : [oldVal, oldVal];
    let [newFH, newSH] = newVal.includes("/") ? newVal.split("/") : [newVal, newVal];
    let [polFH, polSH] = isPolicyArr || [false, false];

    oldFH = oldFH.trim();
    oldSH = oldSH.trim();
    newFH = newFH.trim();
    newSH = newSH.trim();

    // Replace only LOP or blank halves, BUT skip if it's a policy LOP
    if ((oldFH === "LOP" && !polFH) || oldFH === "") oldFH = newFH;
    if ((oldSH === "LOP" && !polSH) || oldSH === "") oldSH = newSH;

    return oldFH + "/" + oldSH;
}

    function toggleAllCheckboxes() {
        // Get the value of the "Select All" checkbox
        var isChecked = $('#selectAll').prop('checked');

        // Set the value of all other checkboxes to match the "Select All" checkbox
        $('.otherCheckbox').prop('checked', isChecked);
    }

    function updateSelectAll() {
        // Check if all other checkboxes are checked
        var allChecked = $('.otherCheckbox:checked').length === $('.otherCheckbox').length;
        //console.log('allChecked', allChecked);
        // Update the "Select All" checkbox accordingly
        $('#selectAll').prop('checked', allChecked);
    }
    $('#shift1combo').select2();
    $('#option').select2();


function updateLopCount(row) {
    let lopCount = 0, leaveCount = 0, workCount = 0;
    let weekOffCount = 0, holidayCount = 0, naCount = 0;

    // ⚡ Dynamically detect all leave codes except P, LOP, NA, WO, HO
    let leaveTypes = new Set();

    row.closest("table").find("td span[id*='_column_']").each(function () {
        let txt = $(this).text().trim().toUpperCase();
        if (!txt) return;
        let parts = txt.includes("/") ? txt.split("/") : [txt];

        parts.forEach(p => {
            p = p.trim();
            if (!p || ["P","LOP","NA","WO","HO"].includes(p)) return;

            // any unknown code → treat as leave type
            leaveTypes.add(p);
        });
    });

    row.find("td span[id*='_column_']").each(function () {
        let status = $(this).text().trim().toUpperCase();
        if (!status) return;

        let parts = status.includes("/") ? status.split("/") : [status];

        parts.forEach(part => {
            part = part.trim();
            if (!part) return;

            let weight = (parts.length === 2) ? 0.5 : 1;

            if (part === "P") workCount += weight;
            else if (part === "LOP") lopCount += weight;
            else if (part === "NA") naCount += weight;
            else if (part === "WO") weekOffCount += weight;
            else if (part === "HO") holidayCount += weight;
            else if (leaveTypes.has(part)) leaveCount += weight; // dynamic leaves
        });
    });

    // TOTAL CALENDAR DAYS
    let totalCalendarDays = row.find("td span[id*='_column_']").length;
   let naHoCount = parseFloat(row.data('na-ho')) || 0;
    let naWoCount = parseFloat(row.data('na-wo')) || 0;
    let prorate_code = parseFloat(row.data('prorate')) || 0;
    let emp_type = parseFloat(row.data('emptype')) || '';

   if (prorate_code !== 2){
      lopCount =lopCount + naCount +(naHoCount + naWoCount);
   }
   else{
    lopCount=lopCount+naCount;
   }

   console.log(lopCount,'na');
    // 🎯 WORKING DAYS = Calendar - (Holiday + Weekoff + NA)
      let workingDays = totalCalendarDays - (weekOffCount + holidayCount);
    // let workingDays = workCount+leaveCount+naCount;

     


    // Update UI
    row.find("td.lopcolor.headcol.left10").text(lopCount);         // LOP
    row.find("td.presentcolor.headcol.left8").text(workCount);     // Present
    row.find("td.presentcolor.headcol.left9").text(leaveCount);    // Leave
    row.find("td.calendercolor.headcol.left5").text(weekOffCount); // Weekoff
    row.find("td.calendercolor.headcol.left6").text(holidayCount); // Holiday
    row.find("td.calendercolor.headcol.left7").text(workingDays);                // Working Days
}




// ✅ Run on page load
$(document).ready(function() {
    $("#LeaveDetailsReports tbody tr").each(function() {
        updateLopCount($(this));
    });

    
});

// hide columns initially
$("#LeaveDetailsReports thead th").eq(3).addClass('hide-id');
$("#LeaveDetailsReports thead th").slice(4, 11).addClass('hide-att');
$("#LeaveDetailsReports tbody tr").each(function() {
    $(this).find('td').eq(3).addClass('hide-id');
    $(this).find('td').slice(4, 11).addClass('hide-att');
});

// toggle button click
$("#toggleAttendanceCols").click(function() {
    let isHidden = $("#LeaveDetailsReports thead th.hide-att").length > 0;

    if (isHidden) {
        // Show columns 4–11 (skip index 3)
        $("#LeaveDetailsReports thead th.hide-att, #LeaveDetailsReports tbody td.hide-att")
            .removeClass('hide-att');
        $(this).text("-");
    } else {
        // Hide columns 4–11 again
        $("#LeaveDetailsReports thead th").slice(4, 11).addClass('hide-att');
        $("#LeaveDetailsReports tbody tr").each(function() {
            $(this).find('td').slice(4, 11).addClass('hide-att');
        });
        $(this).text("+");
    }
});





var dataRows = $('#LeaveDetailsReports tbody tr').filter(function () {
    return $(this).find('td').length > 1; // real data row
}).length;

if (dataRows === 0) {
    // No real data
    $('#selectAll').prop('disabled', true);
    $('#LeaveDetailsReports tbody input[type="checkbox"]').prop('disabled', true);
} else {
    // Real data is present
    $('#selectAll').prop('disabled', false);
    $('#LeaveDetailsReports tbody input[type="checkbox"]').prop('disabled', false);
}



</script>