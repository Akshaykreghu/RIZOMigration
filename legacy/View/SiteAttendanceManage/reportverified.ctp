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
    td, th {
        text-align: left;
        padding: 8px;
        line-height: 1.42857143;
        vertical-align: top;
        border: 1px solid #B2B2B2;
    }
</style>
<div>
    
   <h3>Attendance Register for <?php echo $month;?></h3>
  <div class="row">
        <div class="col-md-12">
            <div class="box ">
                   
                <div class="box-body">
                  
                        <h4>    <?php echo $branch;?>
                    </h4>
                        <div class="row">
                            <div class="col-md-12">
                                
                            </div>
                        </div>
                  
			    <table>
                           
                              <tr>
                                  <td>Employee Name</td>
                                <?php for($i=1;$i<=31;$i++) {?>
                                   <td><?php echo $i;?></td>
                           <?php }?>
                           <td>Days Present</td>
                           <td>Day On Leave</td>
                           <td>Loss Of Pay</td>
                              </tr>
                           <?php  foreach ($resp_register as $val) {?>
                           
                             <tr>
                                  <td><?php echo $val['emp_name'];?></td>
                                <?php for($i=1;$i<=31;$i++) {?>
                               <?php
                                       
                                  $FIELD='FIELD'.$i;     
                                   $fl=$val[$FIELD]; 
                                   
                                    switch ($fl) {
                case 'P':
                case 'p':
                    $backColor = 'green';
                    $textColor = 'white';
                    break;
                //case 'L':
                //case 'l':
                case 'FDL':
                case 'fdl':
                case 'FHL':
                case 'fhl':
                case 'SHL':
                case 'shl':
                    $backColor = 'orange';
                    $textColor = 'white';
                    break;
                case 'WO':
                case 'wo':
                    $backColor = 'yellow';
                    $textColor = 'black';
                    break;
                case 'HO':
                case 'ho':
                    $backColor = 'blue';
                    $textColor = 'white';
                    break;
                case 'LOP':
                case 'lop':
                    $backColor = 'maroon';
                    $textColor = 'white';
                    break;
                case 'COFF':
                case 'coff':
                case 'WFH':
                case 'wfh':
                case 'NA':
                case 'na':
                case 'OTHERS':
                case 'others':
                    $backColor = 'deepskyblue';
                    $textColor = 'white';
                    break;
                default:
                    $backColor = 'red';
                    $textColor = 'white';
                    break;
            }
                                   
                                   
                                   ?> 
                                   
                                   <td style="color:<?php echo $textColor;?>;background-color:<?php echo $backColor;?> ">   
                                      <?php  echo $fl;?></td>
                           <?php }?>
                           <td><?php echo $val['days_present'];?></td>
                           <td><?php echo $val['days_leave'];?></td>
                           <td><?php echo $val['days_lop'];?></td>
                              </tr>
                              <?php } ?>
                        </table>
		    <br>
                   </div>
               <div class="box-footer">                
                    
                       <table>
                           <tr>
                            <?php foreach ($arr_registerentries as $key => $entry) { ?>
                              
                                    <td style="color:<?php echo $entry['textColor'] ?>;background-color: <?php echo $entry['color'] ?>"><?php echo $key.':'; ?>
                                    <?php echo $entry['label'] ?></td>
                               
                            <?php } ?>
                             </tr>
                      </table>
                    </div>
                </div><!-- /.box-footer -->     
            </div>
        </div>
    </div> 
  


