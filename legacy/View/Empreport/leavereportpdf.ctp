<?php
echo $this->element('reportempheader',array(
        "emp" => $arr_emp
    ));
?>

    <h3 style="text-align: center;padding-bottom: 10px;padding-top: 10px;">ATTENDANCE REPORTS </h3>
   <h5 style="text-align: right;padding-bottom: 5px;padding-top: 5px;">For the month :<?php echo date('M-Y',strtotime($month)); ?> </h5>  
    <table style="width: 100%;border: 1px;" >
        <tr style="font-weight: bold;border: 1px">
            <td style="border: 1px;width:20%">Date</td>
      <td style="border: 1px;width:10%">Status</td>
            <td style="border: 1px;width:30%">Session</td>
        <td style="border: 1px;width:40%">Remarks</td>
         
</tr>
 <?php
 
    foreach ($arr_data as $val) {
    $count=count($arr_data['rows']);
      for($i=0;$i<$count;$i++) {
       $date=$val[$i]['leave_date'];
        $Leavestatus=$val[$i]['Leavestatus'];
         $leave_session=$val[$i]['leave_session'];
         $Remarks=$val[$i]['Remarks'];
       
       
       ?>
       <tr style="border: 1px">
            <td style="border: 1px;width:20%"><?php echo $date;?></td>
               <td style="border: 1px;width:10%"><?php echo $Leavestatus;?></td>
                  <td style="border: 1px;width:30%"><?php echo $leave_session;?></td>
                    <td style="border: 1px;width:40%"><?php echo $Remarks;?></td>
            </tr>
  <?php } }
    ?>
</table>
    
