<?php  echo $this->element('reportempheader',array(
       "emp" => $arr_emp
   ));
?>

    <h3 style="text-align: center;padding-bottom: 10px;padding-top: 10px;">LEAVE DETAILS REPORTS </h3>
    
<style type="text/css">
    body {
        line-height: 2em;
    }
    .block-container {
       
        padding: 20px;
        border: #000000 solid thin;
    }
    .sub-head {
        border-bottom: #000000 solid thin;
    }
    .row {
        height: 32px;
    }
   
    table {
        border: 2px solid #f4f4f4;
       
        margin-bottom: 20px;
        background-color: transparent;
        border-spacing: 0;
        border-collapse: collapse;
    }
    td, th {
       
        text-align: left;
        padding: 8px;
        line-height: 1.42857143;
        vertical-align: top;
        border: 1px solid #B2B2B2;
    }
</style>



    <table style="border: 1px solid black; margin-left: -30px;">
      <tr style="font-weight: bold;border-bottom: 1px solid black; height: 60px;">
    <th style="width: 8px;">Sl.No</th>      
    <th >Applied Date</th>
   
    <th style="width: 30px;">Leave Type</th>
    <th >From Date</th>
    <th style="width: 30px;">From Half</th>
    <th >To Date</th>
    <th style="width: 30px;">To Half</th>
<!--     <th style="width: 45px;">Is Authorized</th>
 -->   <!--  <th style="width: 60px;" >Authorized By</th> -->
    <th style="width: 50px;">Authorized Date</th>
    <th style="width: 50px;">Authorized By</th>
    <!-- <th style="width: 43px;">Is Approved</th> -->
   <!--  <th style="width: 50px;">Approved By</th> -->
   <th style="width: 50px;">Approved Date</th>
    <th style="width: 60px;">Approved By</th>
     <th style="width: 50px;">Reason</th>
    <th style="width: 50px;">Contact Number</th>
    <th style="width: 50px;">Duty Handover to</th>
    <th style="width: 50px;">Remarks</th>
    <th style="width: 20px;">Leave Days</th>
    <th >Leave Status</th>
  
   
</tr>
<?php  $i = 1; ?>
    <?php
    //debug($arr_leave_details);
    foreach ($arr_leave_details as $val) {
       
//autharized check
         if($val['leaveentries']['ISAutherized'] ==0)
        {
            $data="NO";
        }
        else 
           {
            $data="YES";
           }
 //from haif    
           if($val['leaveentries']['FROMHALF']==1)
           {
               $fromhalf="First Half";
           }
           else
           {
               $fromhalf="Second Half";
           }
//to half
           if( $val['leaveentries']['TOHALF']==1)
              {
               $tohalf="First Half";
              }
            else
            {
               $tohalf="Second Half"; 
            }
//leave approved    
            if($val['leaveentries']['ISAPPROVED'] ==0)
            {
                $leaveapproved="NO";
            }
            else 
                { 
                $leaveapproved="yes";
                }
       ?>
    
     

     
        

        <?php echo'<tr style="border: 1px">
                 <td >' . $i. '</td>
                  <td >' . $val['leaveentries']['applied_date'] . '</td>
                  
                  <td>' . $val['LeaveType']['item'] . '</td>   
                  <td >' . $val['leaveentries']['FROMDATE'] . '</td>
                  <td style="width: 30px;">' . $fromhalf . '</td>
                  <td >' . $val['leaveentries']['TODATE'] . '</td>
                  <td style="width: 30px;">' .$tohalf. '</td>
                
                  
                  <td >' . $val['leaveentries']['Autherized_date'] . '</td>
                  <td >'. $val['0']['Autherizedby'] . '</td>
                <td>' . $val['leaveentries']['APPROVED_date'] . '</td>
                  <td style="width: 60px;">' . $val['0']['APPROVEDBY'] . '</td>
                 <td style="width: 90px;">' . $val['leaveentries']['Reason'] . '</td>
                  <td style="width: 50px;">' . $val['leaveentries']['contact_No'] . '</td>
                  <td style="width: 50px;">' . $val['leaveentries']['contact_person'] . '</td>
                  <td style="width: 50px;">' . $val['leaveentries']['REMARKS'] . '</td>
                  <td style="width: 20px;">' . $val['leaveentries']['leave_days'] . '</td>
                  <td >' . $val['leaveentries']['LEAVESTATUS'] . '</td>
             
                 
                 
               
     </tr>'; ?>
    
    <?php  $i++; } ?>


</table>
