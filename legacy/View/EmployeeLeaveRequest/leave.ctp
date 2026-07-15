<style>
    #firstTable td {
        width: 230px;
        word-break: break-all;
        border: 1px solid white;
        line-height: 1;
    }

    #reportTable {
        width: 50px;
        word-break: break-all;
    }

    .page-break {
        page-break-after: always;
    }
</style>
<?php

$leaveEntryId = $arr_myleaverequests[0]['LeaveRequests']['LEAVEENTRYID']; 
$appliedDate = date('d-m-Y', strtotime($arr_myleaverequests[0]['LeaveRequests']['applied_date'])); 
$fromDate = date('d-m-Y', strtotime($arr_myleaverequests[0]['LeaveRequests']['FROMDATE'])); 
$toDate = date('d-m-Y', strtotime($arr_myleaverequests[0]['LeaveRequests']['TODATE'])); 
if($fromDate == $toDate){
$date=$fromDate;
}else{
$date= $fromDate. ' to ' .$toDate;
}
$FROMHALF=$arr_myleaverequests[0]['LeaveRequests']['FROMHALF'];
$TOHALF=$arr_myleaverequests[0]['LeaveRequests']['TOHALF'];
if($FROMHALF == '1'&& $TOHALF == '2'){
$value='Full Day';
}else if ($FROMHALF == '1' && $TOHALF == '1') {
$value='First Half';
}else if ($FROMHALF == '2' && $TOHALF == '2') {
$value='Second Half';
}
$leaveStatus = $arr_myleaverequests[0]['LeaveRequests']['LEAVESTATUS']; 
$leaveDays = $arr_myleaverequests[0]['LeaveRequests']['leave_days']; 

$leaveType =$arr_myleaverequests[0]['SalaryHeadItems']['leavetype']; 

$employeeName = $arr_myleaverequests[0]['ei']['EmpName']; 
$employeeId = $arr_myleaverequests[0]['ei']['employee_id']; 
$reason=$arr_myleaverequests[0]['LeaveRequests']['Reason'];
$authorizedName = $arr_myleaverequests[0]['ai']['AutherizedName']; 
$approvedName = $arr_myleaverequests[0]['ap']['ApprovedName']; 
$currentYear = date('Y');
$leave_no = $currentYear . '/' . $leaveEntryId;
?>
<page backtop="40mm" backbottom="10mm" backleft="10mm" backright="2mm" style="font-size: 12pt; ">

    <page_header style="margin-top: 5px;">
        <!-- <img style=" margin-left: 40px;margin-top: 40px; width: 70px; height: auto; " src="https://v1.mypayrollmaster.online/<?php echo $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" alt="Company Logo" />
        <h4 style="text-align: center;margin-top: 0px"><?php echo isset($arr_comp_contact_info['CompanyContactInfo']['business_name']) ? $arr_comp_contact_info['CompanyContactInfo']['business_name'] : ''; ?></h4>
        <h4 style="text-align: center;margin-top: 0px;"> <span><?php echo strtoupper($type); ?></span></h4>
        <h4 style="text-align: center;margin-top: 0px;padding-top:0px;"> <span>---------------------------------------------------</span></h4> -->
        <div style="text-align: center;">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 20%;">&nbsp;</td>
                    <td style="width: 60%;">
                        <h4 style="text-align: center;"><?php echo isset($arr_comp_contact_info['CompanyContactInfo']['business_name']) ? $arr_comp_contact_info['CompanyContactInfo']['business_name'] : ''; ?></h4>
                    </td>
                    <td style="width: 20%;text-align:left; padding-left:0px;padding-top:-15px;"><img style="width: auto; height:80px;" src="https://v1.mypayrollmaster.online/<?php echo $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" alt="Company Logo" /></td>
                </tr>
                <tr>
                    <td style="width:20%">&nbsp;</td>
                    <td style="width: 60%;"><span style="border-bottom: 1px dashed #000;">LEAVE / OFF APPLICATION</span></td>
                    <td style="width: 20%;">&nbsp;</td>
                </tr>
                <tr>
                    <td style="width:20%;">&nbsp;</td>
                    <td style="width: 60%; padding-top: -10px;">---------------------------------------</td>
                    <td style="width: 20%; ">&nbsp;</td>
                </tr>
                <tr>
                    <td style="width:20%;">&nbsp;</td>
                    <td style="width: 60%;">&nbsp;</td>
                    <td style="width: 20%; ">&nbsp;</td>
                </tr>
            </table>
        </div>
    </page_header>

    <bookmark title="Sommaire" level="0"></bookmark>
</page>
<!-- Modal content-->
<div class="modal-content" style="margin-top:-50px;" id="content">


    <div class="modal-body page-break">
        <!-- Form starts -->
        <div class="container" style="width:100%;">
            <table id="firstTable" style="margin-left:80px;">
                <tr>
                    <td>Leave No</td>
                    <td colspan="2">:&nbsp;<?php echo $leave_no;?></td>
                </tr>
                <!-- <tr>
                    <td colspan="3">&nbsp;</td>
                </tr> -->
                <tr>
                    <td>Date</td>
                    <td colspan="2">:&nbsp;<?php echo isset($appliedDate) ? $appliedDate : ''; ?></td>
                </tr>
                <!-- <tr>
                    <td colspan="3">&nbsp;</td>
                </tr> -->
                <tr>
                    <td>Leave  Type</td>
              <td colspan="2" style="width:300px;">:&nbsp;<?php echo $leaveType . ' - ' . $date. '  ' . $value;?>
                </td>

                    
                </tr>
                <!-- <tr>
                    <td colspan="3">&nbsp;</td>
                </tr> -->
                <tr>
                    <td>No of Days</td>
                    <td colspan="2">:&nbsp;<?php echo isset($leaveDays) ? $leaveDays : ''; ?></td>
                </tr>
                <!-- <tr>
                    <td colspan="3">&nbsp;</td>
                </tr> -->
                <tr>
                    <td>Staff Name</td>
                    <td style="width: 320px;">:&nbsp;<?php echo isset($employeeName) ? $employeeName.'('.$employeeId.')' : ''; ?></td>
                    <td></td>
                </tr>
                <!-- <tr>
                    <td colspan="3">&nbsp;</td>
                </tr> -->
                <tr>
                    <td>Reason </td>
                    <td colspan="2" style="width:auto;">:&nbsp;<?php echo isset($reason) ? $reason : ''; ?></td>
                </tr>
                <!-- <tr>
                    <td colspan="3">&nbsp;</td>
                </tr> -->
                <tr>
                    <td>Leave Status</td>
                    <td colspan="2" style=" width: 400px;">:&nbsp;<?php echo isset($leaveStatus) ? $leaveStatus : ''; ?> </td>
                     
                </tr>
                <!-- <tr>
                    <td colspan="3">&nbsp;</td>
                </tr> -->
                <!--<tr>
                    <td></td>
                    <td colspan="2" style="padding-top:20px;">&nbsp;Eligible :&nbsp;<span>value</span>&nbsp;&nbsp; Availed :&nbsp;<span>value</span>&nbsp;&nbsp; Balance :&nbsp;<span>value</span></td>
                </tr>-->
                 
                <tr>
                    <td>Sanctioned By</td>
                    <td colspan="2">:&nbsp;<?php echo isset($authorizedName) ? $authorizedName : ''; ?></td>
                </tr>
                <!-- <tr>
                    <td colspan="3">&nbsp;</td>
                </tr> -->
                <tr>
                    <td>Approved By</td>
                    <td colspan="2">:&nbsp;<?php echo isset($approvedName) ? $approvedName : ''; ?></td>
                </tr>
                <!-- <tr>
                    <td colspan="3">&nbsp;</td>
                </tr> -->
                <!-- <tr>
                    <td colspan="3">&nbsp;</td>
                </tr> -->
                 <!-- <tr>
                    <td colspan="3">&nbsp;</td>
                </tr> -->
                 <tr>
                    <td colspan="3">&nbsp;</td>
                </tr>
               
                <tr>
                    <td>&nbsp;</td>
                    <td style="text-align:right;">Security&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    
                    
                </tr>
                <!-- <tr>
                    <td colspan="3">&nbsp;</td>
                </tr> -->
                <tr>
                    <td>Signature of the Authorised Person&nbsp;:</td>
                    <td style="text-align:right;">Date & Time&nbsp;:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td> 
                    
                    <td></td>

                </tr>

            </table>
        </div>
        <!-- form ends-->
    </div>
</div>