
    <?php if ($mode == '') { ?>
    <div class="modal-body" style="overflow-y:initial; padding-left:3%; padding-right:3%; padding-bottom:3%;" >
        <div class="row">
            <div class="col-md-12">
                <?php if(count($arr_salary_for_template) == 0){ ?>
                <h3 align="center"><b>FORM NO.12BB</b></h3><h5 align="center"><?php echo "(See rule 26C) " ?></h5>
                <h3 align="center">No data available for the selected criteria.</h3>
                <?php } else{?>
                <?php if(isset($needBranchWiseReport) && $needBranchWiseReport == 1) { //do branchwise listing 
                $i = 0; 
                foreach ($arr_salary_for_template as $val) { 
                    if (count($val) !== 0) { 
                        $i += 1; ?>
                             <div class="box-body " style="overflow-y:auto; ">
                                <fieldset>
                                    <legend><?php echo $val['0']['tax']['0']['summary']['employee_info']['branch']; ?></legend>
                                </fieldset>
                   <?php foreach ($val['0']['tax'] as $value) { 
                    $add = isset($value['summary']['emp_details']['address'])?$value['summary']['emp_details']['address']:'';
                    $city = isset($value['summary']['emp_details']['city'])?$value['summary']['emp_details']['city']:'';
                    $state = isset($value['summary']['emp_details']['state'])?$value['summary']['emp_details']['state']:'';
                    $pincode = isset($value['summary']['emp_details']['pincode'])?$value['summary']['emp_details']['pincode']:'';
                    $Empname = isset($value['Name'])?$value['Name']:'';
                    if($value['summary']['emp_details']['guradian'] != ''){
                        $guradian = $value['summary']['emp_details']['guradian'];
                    }else{
                        $guradian = '.................';
                    }
                    $finyear = isset($value['finyear'])?$value['finyear']:'';
                    $panno = isset($value['summary']['emp_details']['pan_no'])?$value['summary']['emp_details']['pan_no']:'';
                    $designation = isset($value['Designation'])?$value['Designation']:'';
                    $relation_guardian = isset($value['summary']['emp_details']['relation_guardian'])?$value['summary']['emp_details']['relation_guardian']:'';
                    $gender = isset($value['summary']['emp_details']['classification'])?$value['summary']['emp_details']['classification']:'';
                    if($gender == 'male'){
                        $class = 'son';
                    }else if(($gender == 'female') && ($relation_guardian == 'Father')){
                        $class = 'daughter';
                    }else if(($gender == 'female') && ($relation_guardian == 'Husband')){
                        $class = 'wife';
                    }else{
                        $class = 'son/daughter';
                    }?>
                    <h3 align="center"><b>FORM NO.12BB</b></h3><h5 align="center"><?php echo "(See rule 26C) " ?></h5>
                        <div class="box-body " style="overflow-y:auto; ">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr> 
                                     <td colspan="2" style="text-align:left;height: 5%;">1. Name and address of the employee:</td>
                                     <td colspan="2" style="text-align:left;height: 5%;"><?php echo $Empname;?><?php if($add != '') echo ', '.$add; ?><?php if($city != '') echo ', '.$city; ?><?php if($state != '') echo ', '.$state; ?><?php if($pincode != '') echo ', Pincode - '.$pincode; ?></td>
                                    </tr>
                                    <tr>
                                     <td colspan="2" style="text-align:left;height: 5%;">2. Permanent Account Number of the employee:</td>
                                     <td colspan="2" style="text-align:left;height: 5%;"><?php echo $panno;?></td>
                                    </tr>
                                    <tr> 
                                     <td colspan="2" style="text-align:left;height: 5%;">3. Financial year:</td>
                                     <td colspan="2" style="text-align:left;height: 5%;"><?php echo $finyear;?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" style="height: 5%;"><br></td>
                                    </tr>
                                    <tr>
                                     <td colspan="4" style="font-weight: bold; text-align: center; height: 5%;">
                                      <b>Details of claims and evidence thereof</b>
                                     </td>
                                    </tr>
                                    <tr>
                                    <td style="width:5%;text-align: center;">Sl No.</td>
                                    <td style="text-align:center;width:20%;">Nature of claim</td>
                                    <td style="text-align:center;width:10%;">Amount (Rs.)</td>
                                    <td style="text-align:center;width:15%;">Evidence / particulars</td>
                                    </tr>
                                    <tr>
                                    <td style="text-align:center;">(1)</td>
                                    <td style="text-align:center;">(2)</td>
                                    <td style="text-align:center;">(3)</td>
                                    <td style="text-align:center;">(4)</td>
                                    </tr>
                                    <?php $i = 0;
                                    foreach($value['details'] as $val){ 
                                        $name = isset($val['name'])?$val['name']:'';
                                        $details = isset($val['details'])?$val['details']:'';
                                        $i++;
                                        ?>
                                    <tr>
                                        <td><?php echo $i;?></td>
                                        <td><?php echo $name;?><br><?php echo $details; ?>
                                            <br>
                                        <?php $j=1;
                                            foreach($val['subheadvalues'] as $head){
                                              $sub = isset($head['sub_details'])?$head['sub_details']:''; 
                                              if($head['sub_details1'] !=''){
                                              $subdet = $sub.'('.$head['sub_details1'].')';
                                              }else{
                                              $subdet = $sub;
                                              }
                                              $n = intval($j);
                                              $result = '';
                                              // Declare a lookup array that we will use to traverse the number:
                                              $lookup = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400,
                                                             'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40,
                                                             'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
                                                  foreach ($lookup as $roman => $value) 
                                                  {
                                                    // Determine the number of matches
                                                      $matches = intval($n / $value);
                                                    // Store that many characters
                                                      $result .= str_repeat($roman, $matches);
                                                    // Substract that from the number
                                                      $n = $n % $value;
                                                  }
                                              echo '('.strtolower($result).') '. $subdet.'<br>';
                                              $j++; } ?>
                                        </td>
                                    <td><?php echo '';?><br><?php echo ''; ?>
                                            <br>
                                        <?php 
                                            foreach($val['subheadvalues'] as $head){
                                              $rate = isset($head['tax_value'])?$head['tax_value']:''; 
                                              $sub = isset($head['sub_details'])?$head['sub_details']:''; 
                                              if($head['sub_details1'] !=''){
                                              $subdet = $sub.'('.$head['sub_details1'].')';
                                              }else{
                                              $subdet = $sub;
                                              }
                                              $len = strlen($subdet);
                                              $len1 = ceil($len/70);
                                              $break = '';
                                              for($k=0;$k<($len1-1);$k++){
                                                  $break .= "<br>";
                                              }
                                              echo '<div style="text-align:center">'.$rate.'</div>'. $break;
                                             
                                               } ?></td>
                                    <td><?php echo '';?><br><?php echo ''; ?>
                                            <br>
                                        <?php 
                                            foreach($val['subheadvalues'] as $head){
                                              $evidence = isset($head['evidence'])?$head['evidence']:''; 
                                               $sub = isset($head['sub_details'])?$head['sub_details']:''; 
                                              if($head['sub_details1'] !=''){
                                              $subdet = $sub.'('.$head['sub_details1'].')';
                                              }else{
                                              $subdet = $sub;
                                              }
                                              $len = strlen($subdet);
                                              $len1 = ceil($len/70);
                                              $break = '';
                                              for($k=0;$k<($len1-1);$k++){
                                                  $break .= "<br>";
                                              }
                                              echo '<div style="text-align:center">'.$evidence.'</div>'. $break;
                                               } ?></td>
                                    </tr>
                                    <?php } ?>
                                    <tr><td colspan="4" style="text-align:center;">Verification</td></tr>
                                    <tr><td colspan="4" style="text-align:center;">
                <?php echo "I $Empname, $class of $guradian do hereby certify that the information given above is complete and correct.";?></td></tr>
                                    <tr><td colspan="2">Place: <?php echo $city; ?><br>
                                        Date: <?php echo date('d-m-Y'); ?><br>
                                        Designation: <?php echo $designation; ?>
                                    </td>
                                    <td colspan="2"><br>(Signature of the employee) <br><?php echo $Empname;?></td>
                                    </tr>
                                </tbody>
                               </table>
                             </div>
                         <?php } ?>
                        </div>
                <?php }}} else { 
               foreach($arr_salary_for_template as $value){
                    if (count($value) !== 0) { 
                    $add = isset($value['summary']['address'])?$value['summary']['address']:'';
                    $city = isset($value['summary']['city'])?$value['summary']['city']:'';
                    $state = isset($value['summary']['state'])?$value['summary']['state']:'';
                    $pincode = isset($value['summary']['pincode'])?$value['summary']['pincode']:'';
                    $Empname = isset($value['Name'])?$value['Name']:'';
                    if($value['summary']['guradian'] != ''){
                        $guradian = $value['summary']['guradian'];
                    }else{
                        $guradian = '.................';
                    }
                    $finyear = isset($value['finyear'])?$value['finyear']:'';
                    $panno = isset($value['summary']['pan_no'])?$value['summary']['pan_no']:'';
                    $designation = isset($value['Designation'])?$value['Designation']:'';
                    $gender = isset($value['summary']['classification'])?$value['summary']['classification']:'';
                    $relation_guardian = isset($value['summary']['relation_guardian'])?$value['summary']['relation_guardian']:'';
                    if($gender == 'male'){
                        $class = 'son';
                    }else if(($gender == 'female') && ($relation_guardian == 'Father')){
                        $class = 'daughter';
                    }else if(($gender == 'female') && ($relation_guardian == 'Husband')){
                        $class = 'wife';
                    }else{
                        $class = 'son/daughter';
                    }
                    ?> 
                <div class="box-body " style="overflow-y:auto; ">
                            <table class="table table-bordered">
                                <tbody>
                                   <h3 align="center"><b>FORM NO.12BB</b></h3><h5 align="center"><?php echo "(See rule 26C) " ?></h5>
                                    <tr> 
                                     <td colspan="2" style="text-align:left;height: 5%;">1. Name and address of the employee:</td>
                                     <td colspan="2" style="text-align:left;height: 5%;"><?php echo $Empname;?><?php if($add != '') echo ', '.$add; ?><?php if($city != '') echo ', '.$city; ?><?php if($state != '') echo ', '.$state; ?><?php if($pincode != '') echo ', Pincode - '.$pincode; ?></td>
                                    </tr>
                                    <tr>
                                     <td colspan="2" style="text-align:left;height: 5%;">2. Permanent Account Number of the employee:</td>
                                     <td colspan="2" style="text-align:left;height: 5%;"><?php echo $panno;?></td>
                                    </tr>
                                    <tr> 
                                     <td colspan="2" style="text-align:left;height: 5%;">3. Financial year:</td>
                                     <td colspan="2" style="text-align:left;height: 5%;"><?php echo $finyear;?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" style="height: 5%;"><br></td>
                                    </tr>
                                    <tr>
                                     <td colspan="4" style="font-weight: bold; text-align: center; height: 5%;">
                                      <b>Details of claims and evidence thereof</b>
                                     </td>
                                    </tr>
                                    <tr>
                                    <td style="width:5%;text-align: center;">Sl No.</td>
                                    <td style="text-align:center;width:20%;">Nature of claim</td>
                                    <td style="text-align:center;width:10%;">Amount (Rs.)</td>
                                    <td style="text-align:center;width:15%;">Evidence / particulars</td>
                                    </tr>
                                    <tr>
                                    <td style="text-align:center;">(1)</td>
                                    <td style="text-align:center;">(2)</td>
                                    <td style="text-align:center;">(3)</td>
                                    <td style="text-align:center;">(4)</td>
                                    </tr>
                                    <?php $i = 0;
                                    foreach($value['details'] as $val){ 
                                        $name = isset($val['name'])?$val['name']:'';
                                        $details = isset($val['details'])?$val['details']:'';
                                        $i++;
                                        ?>
                                    <tr>
                                        <td><?php echo $i;?></td>
                                        <td><?php echo $name;?><br><?php echo $details; ?>
                                            <br>
                                        <?php $j=1;
                                            foreach($val['subheadvalues'] as $head){
                                              $sub = isset($head['sub_details'])?$head['sub_details']:''; 
                                              if($head['sub_details1'] !=''){
                                              $subdet = $sub.'('.$head['sub_details1'].')';
                                              }else{
                                              $subdet = $sub;
                                              }
                                              $n = intval($j);
                                              $result = '';
                                              // Declare a lookup array that we will use to traverse the number:
                                              $lookup = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400,
                                                             'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40,
                                                             'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
                                                  foreach ($lookup as $roman => $value) 
                                                  {
                                                    // Determine the number of matches
                                                      $matches = intval($n / $value);
                                                    // Store that many characters
                                                      $result .= str_repeat($roman, $matches);
                                                    // Substract that from the number
                                                      $n = $n % $value;
                                                  }
                                              $text = '('.strtolower($result).') '. $subdet.'<br>';
                                              echo $text;
                                              $j++; } ?>
                                        </td>
                                    <td><?php echo '';?><br><?php echo ''; ?>
                                            <br>
                                        <?php 
                                            foreach($val['subheadvalues'] as $head){
                                              $rate = isset($head['tax_value'])?$head['tax_value']:''; 
                                              $sub = isset($head['sub_details'])?$head['sub_details']:''; 
                                              if($head['sub_details1'] !=''){
                                              $subdet = $sub.'('.$head['sub_details1'].')';
                                              }else{
                                              $subdet = $sub;
                                              }
                                              $len = strlen($subdet);
                                              $len1 = ceil($len/70);
                                              $break = '';
                                              for($k=0;$k<($len1-1);$k++){
                                                  $break .= "<br>";
                                              }
                                              echo '<div style="text-align:center">'.$rate.'</div>'. $break;
                                             
                                               } ?></td>
                                    <td><?php echo '';?><br><?php echo ''; ?>
                                            <br>
                                        <?php 
                                            foreach($val['subheadvalues'] as $head){
                                              $evidence = isset($head['evidence'])?$head['evidence']:''; 
                                              $sub = isset($head['sub_details'])?$head['sub_details']:''; 
                                              if($head['sub_details1'] !=''){
                                              $subdet = $sub.'('.$head['sub_details1'].')';
                                              }else{
                                              $subdet = $sub;
                                              }
                                              $len = strlen($subdet);
                                              $len1 = ceil($len/70);
                                              $break = '';
                                              for($k=0;$k<($len1-1);$k++){
                                                  $break .= "<br>";
                                              }
                                              echo '<div style="text-align:center">'.$evidence.'</div>'. $break;
                                               } ?></td>
                                    </tr>
                                    <?php } ?>
                                    <tr><td colspan="4" style="text-align:center;">Verification</td></tr>
                                    <tr><td colspan="4" style="text-align:center;">
                <?php echo "I $Empname, $class of $guradian do hereby certify that the information given above is complete and correct.";?></td></tr>
                                    <tr><td colspan="2">Place: <?php echo $city; ?><br>
                                        Date: <?php echo date('d-m-Y'); ?><br>
                                        Designation: <?php echo $designation; ?>
                                    </td>
                                    <td colspan="2"><br>(Signature of the employee) <br><?php echo $Empname;?></td>
                                    </tr>
                                </tbody>
                               </table>
                             </div>
                            <?php }}}}?>
                           </div>
                          </div>  
                         </div>
    <?php } else { ?>
   <style type="text/css">
    .table-bordered > thead > tr > th, .table-bordered > tbody > tr > th, .table-bordered > tfoot > tr > th, .table-bordered > thead > tr > td,
    .table-bordered > tbody > tr > td, .table-bordered > tfoot > tr > td {
      border: 1px solid #000;
      border-color: #000!important;
    }
    body {
        line-height: 2em;
    }

    .block-container {
        width: 95%;
        padding: 20px;
        /*border: #000000 solid thin;*/
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
        /*border: 1px solid #f4f4f4;*/
        width: 70%;
        max-width: 80%;
        margin-bottom: 20px;
       
        font-weight: normal;
        border: thin;
        /*background-color: transparent;*/
        border-spacing: 0;
        border-collapse: collapse;
    }

    td,
    th {
        text-align: left;
        padding: 1px;
        font-weight: normal;
        /*font-size: 11px;*/
        font-size: 11px;
        /*font-family: serif;*/
        line-height: 1.32857143;
        word-wrap: break-word;
        vertical-align: top;
       
        border: thin;
    }

    
   .pdf-line-break {
    white-space: pre-line;
}

.clearfix::after {
    content: "";
    display: table;
    clear: both;
}
    
</style>
 <?php
  // echo $this->element('reportadminheader', array(
  //     'title' => '<b style="font-size:18px;">FORM NO.12BB</b><br><b style="font-size:13px;">(See rule 26C)</b>'));
    ?>
                <?php if(count($arr_salary_for_template) == 0){ ?>
<!--                <h3 align="center"><b>FORM NO.12BB</b></h3><h5 align="center"><?php echo "(See rule 26C) " ?></h5>-->
                <h3 align="center">No data available for the selected criteria.</h3>
                <?php } else{?>
                <?php if(isset($needBranchWiseReport) && $needBranchWiseReport == 1) { //do branchwise listing 
                $i = 0; 
                foreach ($arr_salary_for_template as $val) { 
                    if (count($val) !== 0) { 
                        $i += 1; ?>
<!--                        <h3><?php //echo $val['0']['tax']['0']['summary']['employee_info']['branch']; ?></h3>-->
                   <?php foreach ($val['0']['tax'] as $value) { 
                    $add = isset($value['summary']['emp_details']['address'])?$value['summary']['emp_details']['address']:'';
                    $city = isset($value['summary']['emp_details']['city'])?$value['summary']['emp_details']['city']:'';
                    $state = isset($value['summary']['emp_details']['state'])?$value['summary']['emp_details']['state']:'';
                    $pincode = isset($value['summary']['emp_details']['pincode'])?$value['summary']['emp_details']['pincode']:'';
                    $Empname = isset($value['Name'])?$value['Name']:'';
                    if($value['summary']['emp_details']['guradian'] != ''){
                        $guradian = $value['summary']['emp_details']['guradian'];
                    }else{
                        $guradian = '.................';
                    }
                    $finyear = isset($value['finyear'])?$value['finyear']:'';
                    $panno = isset($value['summary']['emp_details']['pan_no'])?$value['summary']['emp_details']['pan_no']:'';
                    $designation = isset($value['Designation'])?$value['Designation']:'';
                    $relation_guardian = isset($value['summary']['emp_details']['relation_guardian'])?$value['summary']['emp_details']['relation_guardian']:'';
                    $gender = isset($value['summary']['emp_details']['classification'])?$value['summary']['emp_details']['classification']:'';
                    if($gender == 'male'){
                        $class = 'son';
                    }else if(($gender == 'female') && ($relation_guardian == 'Father')){
                        $class = 'daughter';
                    }else if(($gender == 'female') && ($relation_guardian == 'Husband')){
                        $class = 'wife';
                    }else{
                        $class = 'son/daughter';
                    }?>
 <page  backtop="35mm" backbottom="10mm" backleft="10mm" backright="10mm" style="font-size: 12pt">
                    <page_header>
        <div style="text-align:left; width:100%; ">
            <?php if (isset($arr_comp_contact_info['CompanyContactInfo']['logo']) && !empty($arr_comp_contact_info['CompanyContactInfo']['logo'])) { ?> 
            <div style="width: 20%; margin-left: 20px; font-size: 18px; ">
                <img style=" margin-left: 20px; margin-top: 10px; " src="http://<?php echo $_SERVER['HTTP_HOST'] . $this->webroot . $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" height="100" width="100" class="img-circle" alt="Company Logo" />
            </div>
            <?php } ?>
                <div style="width: 80%; margin-left: 100px; margin-top: 40px; position : absolute ; float: left; font-size: 14px; ">
                  <div style="text-align: center;font-weight: bold;font-size: 14px; ; padding-top: 4px; "><?php echo $arr_comp_contact_info['CompanyContactInfo']['business_name']; ?>
                       </div>
                    <div style="text-align: center ; padding-top: 4px; word-break: break-all;font-size: 12px; "><?php echo $arr_comp_contact_info['CompanyContactInfo']['address']; ?>
                         </div>
                    <div style="text-align: center ; padding-top: 4px;"><?php echo $arr_comp_contact_info['CompanyContactInfo']['city']; ?>
                        ,PIN - <?php echo $arr_comp_contact_info['CompanyContactInfo']['pincode']; ?>
                        ,<?php echo $arr_comp_contact_info['CompanyContactInfo']['state']; ?>
                    </div> <div style="text-align: center ; padding-top: 4px;"> <?php echo "Phone : " . $arr_comp_contact_info['CompanyContactInfo']['phone']; ?>
                        <?php echo ' Email : ' . $arr_comp_contact_info['CompanyContactInfo']['email']; ?>
                    </div>
                </div>
        </div> 
                         <hr>
                    </page_header>
       
        <h3 style="text-align: center;padding-bottom: 20px;padding-top: 10px;"><?php echo '<b style="font-size:18px;">FORM NO.12BB</b><br><b style="font-size:13px;">(See rule 26C)</b>'; ?></h3>
       
                            <table>
                                <tbody>
                                    <tr> 
                                     <td colspan="2" style="text-align:left;width:200px;">1. Name and address of the employee:</td>
                                     <td colspan="2" style="text-align:left;width:300px;"><?php echo $Empname;?><?php if($add != '') echo '<br>'.$add; ?><?php if($city != '') echo '<br>'.$city; ?><?php if($state != '') echo '<br>'.$state; ?><?php if($pincode != '') echo ', Pincode - '.$pincode; ?></td>
                                    </tr>
                                    <tr>
                                     <td colspan="2" style="text-align:left;">2. Permanent Account Number of the employee:</td>
                                     <td colspan="2" style="text-align:left;"><?php echo $panno;?></td>
                                    </tr>
                                    <tr> 
                                     <td colspan="2" style="text-align:left;">3. Financial year:</td>
                                     <td colspan="2" style="text-align:left;"><?php echo $finyear;?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4"><br></td>
                                    </tr>
                                    <tr>
                                     <td colspan="4" style="font-weight: bold; text-align: center;">
                                      <b>Details of claims and evidence thereof</b>
                                     </td>
                                    </tr>
                                    <tr>
                                    <td style="text-align: center;width:35px;">Sl. No.</td>
                                    <td style="text-align:center;width:320px;">Nature of claim</td>
                                    <td style="text-align:center;width:130px;">Amount (Rs.)</td>
                                    <td style="text-align:center;width:170px;">Evidence / particulars</td>
                                    </tr>
                                    <tr>
                                    <td style="text-align:center;">(1)</td>
                                    <td style="text-align:center;">(2)</td>
                                    <td style="text-align:center;">(3)</td>
                                    <td style="text-align:center;">(4)</td>
                                    </tr>
                                    <?php $i = 0;
                                    foreach($value['details'] as $val){ 
                                        $name = isset($val['name'])?$val['name']:'';
                                        $details = isset($val['details'])?$val['details']:'';
                                        $i++;
                                        ?>
                                    <tr>
                                        <td style="width:30px;"><?php echo $i;?></td>
                                        <td style="width:320px;"><?php echo $name;?><br><?php echo $details; ?>
                                            <br>
                                        <?php $j=1;
                                            foreach($val['subheadvalues'] as $head){
                                              $sub = isset($head['sub_details'])?$head['sub_details']:''; 
                                              if($head['sub_details1'] !=''){
                                              $subdet = $sub.'('.$head['sub_details1'].')';
                                              }else{
                                              $subdet = $sub;
                                              }
                                              $n = intval($j);
                                              $result = '';
                                              // Declare a lookup array that we will use to traverse the number:
                                              $lookup = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400,
                                                             'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40,
                                                             'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
                                                  foreach ($lookup as $roman => $value) 
                                                  {
                                                    // Determine the number of matches
                                                      $matches = intval($n / $value);
                                                    // Store that many characters
                                                      $result .= str_repeat($roman, $matches);
                                                    // Substract that from the number
                                                      $n = $n % $value;
                                                  }
                                              echo '('.strtolower($result).') '. $subdet.'<br>';
                                              $j++; } ?>
                                        </td>
                                    <td style="width:130px;"><?php echo '';?><br><?php echo ''; ?>
                                            <br>
                                        <?php 
                                            foreach($val['subheadvalues'] as $head){
                                              $rate = isset($head['tax_value'])?$head['tax_value']:''; 
                                              $sub = isset($head['sub_details'])?$head['sub_details']:''; 
                                              if($head['sub_details1'] !=''){
                                              $subdet = $sub.'('.$head['sub_details1'].')';
                                              }else{
                                              $subdet = $sub;
                                              }
                                              $len = strlen($subdet);
                                              $len1 = ceil($len/70);
                                              $break = '';
                                              for($k=0;$k<($len1-1);$k++){
                                                  $break .= "<br>";
                                              }
                                              echo '<div style="text-align:center">'.$rate.'</div>'. $break;
                                             
                                               } ?></td>
                                    <td style="width:170px;"><?php echo '';?><br><?php echo ''; ?>
                                            <br>
                                        <?php 
                                            foreach($val['subheadvalues'] as $head){
                                              $evidence = isset($head['evidence'])?$head['evidence']:''; 
                                               $sub = isset($head['sub_details'])?$head['sub_details']:''; 
                                              if($head['sub_details1'] !=''){
                                              $subdet = $sub.'('.$head['sub_details1'].')';
                                              }else{
                                              $subdet = $sub;
                                              }
                                              $len = strlen($subdet);
                                              $len1 = ceil($len/70);
                                              $break = '';
                                              for($k=0;$k<($len1-1);$k++){
                                                  $break .= "<br>";
                                              }
                                              echo '<div style="text-align:center">'.$evidence.'</div>'. $break;
                                               } ?></td>
                                    </tr>
                                    <?php } ?>
                                    <tr><td colspan="4" style="text-align:center;">Verification</td></tr>
                                    <tr><td colspan="4" style="text-align:center;">
                <?php echo "I $Empname, $class of $guradian do hereby certify that the information given above is complete and correct.";?></td></tr>
                                    <tr><td colspan="2">Place: <?php echo $city; ?><br>
                                        Date: <?php echo date('d-m-Y'); ?><br>
                                        Designation: <?php echo $designation; ?>
                                    </td>
                                    <td colspan="2"><br>(Signature of the employee) <br><?php echo $Empname;?></td>
                                    </tr>
                                </tbody>
                               </table>
 </page>
                         <?php } ?>
                       
                <?php }}?> <?php } else { 
               foreach($arr_salary_for_template as $value){
                   // if (count($value) !== 0) { 
                    $add = isset($value['summary']['address'])?$value['summary']['address']:'';
                    $city = isset($value['summary']['city'])?$value['summary']['city']:'';
                    $state = isset($value['summary']['state'])?$value['summary']['state']:'';
                    $pincode = isset($value['summary']['pincode'])?$value['summary']['pincode']:'';
                    $Empname = isset($value['Name'])?$value['Name']:'';
                    if($value['summary']['guradian'] != ''){
                        $guradian = $value['summary']['guradian'];
                    }else{
                        $guradian = '.................';
                    }
                    $finyear = isset($value['finyear'])?$value['finyear']:'';
                    $panno = isset($value['summary']['pan_no'])?$value['summary']['pan_no']:'';
                    $designation = isset($value['Designation'])?$value['Designation']:'';
                    $gender = isset($value['summary']['classification'])?$value['summary']['classification']:'';
                    $relation_guardian = isset($value['summary']['relation_guardian'])?$value['summary']['relation_guardian']:'';
                    if($gender == 'male'){
                        $class = 'son';
                    }else if(($gender == 'female') && ($relation_guardian == 'Father')){
                        $class = 'daughter';
                    }else if(($gender == 'female') && ($relation_guardian == 'Husband')){
                        $class = 'wife';
                    }else{
                        $class = 'son/daughter';
                    }
                    ?> 
                <page  backtop="35mm" backbottom="10mm" backleft="10mm" backright="10mm" style="font-size: 12pt">
                    <page_header>
        <div style="text-align:left; width:100%; ">
            <?php if (isset($arr_comp_contact_info['CompanyContactInfo']['logo']) && !empty($arr_comp_contact_info['CompanyContactInfo']['logo'])) { ?> 
            <div style="width: 20%; margin-left: 20px; font-size: 18px; ">
                <img style=" margin-left: 20px; margin-top: 10px; " src="http://<?php echo $_SERVER['HTTP_HOST'] . $this->webroot . $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" height="100" width="100" class="img-circle" alt="Company Logo" />
            </div>
            <?php } ?>
                <div style="width: 80%; margin-left: 100px; margin-top: 40px; position : absolute ; float: left; font-size: 14px; ">
                  <div style="text-align: center;font-weight: bold;font-size: 14px; ; padding-top: 4px; "><?php echo $arr_comp_contact_info['CompanyContactInfo']['business_name']; ?>
                       </div>
                    <div style="text-align: center ; padding-top: 4px; word-break: break-all;font-size: 12px; "><?php echo $arr_comp_contact_info['CompanyContactInfo']['address']; ?>
                         </div>
                    <div style="text-align: center ; padding-top: 4px;"><?php echo $arr_comp_contact_info['CompanyContactInfo']['city']; ?>
                        ,PIN - <?php echo $arr_comp_contact_info['CompanyContactInfo']['pincode']; ?>
                        ,<?php echo $arr_comp_contact_info['CompanyContactInfo']['state']; ?>
                    </div> <div style="text-align: center ; padding-top: 4px;"> <?php echo "Phone : " . $arr_comp_contact_info['CompanyContactInfo']['phone']; ?>
                        <?php echo ' Email : ' . $arr_comp_contact_info['CompanyContactInfo']['email']; ?>
                    </div>
                </div>
        </div> 
                         <hr>
                    </page_header>
       
        <h3 style="text-align: center;padding-bottom: 20px;padding-top: 10px;"><?php echo '<b style="font-size:18px;">FORM NO.12BB</b><br><b style="font-size:13px;">(See rule 26C)</b>'; ?></h3>
       
                    <table>
                                <tbody>
                                   
                                    <tr> 
                                     <td colspan="2" style="text-align:left;">1. Name and address of the employee:</td>
                                     <td colspan="2" style="text-align:left;width:300px;"><?php echo $Empname;?><?php if($add != '') echo '<br>'.$add; ?><?php if($city != '') echo '<br>'.$city; ?><?php if($state != '') echo '<br>'.$state; ?><?php if($pincode != '') echo ', Pincode - '.$pincode; ?></td>
                                    </tr>
                                    <tr>
                                     <td colspan="2" style="text-align:left;">2. Permanent Account Number of the employee:</td>
                                     <td colspan="2" style="text-align:left;"><?php echo $panno;?></td>
                                    </tr>
                                    <tr> 
                                     <td colspan="2" style="text-align:left;">3. Financial year:</td>
                                     <td colspan="2" style="text-align:left;"><?php echo $finyear;?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4"><br></td>
                                    </tr>
                                    <tr>
                                     <td colspan="4" style="font-weight: bold; text-align: center; ">
                                      <b>Details of claims and evidence thereof</b>
                                     </td>
                                    </tr>
                                    <tr>
                                    <td style="text-align: center;width:35px;">Sl. No.</td>
                                    <td style="text-align:center;width:320px;">Nature of claim</td>
                                    <td style="text-align:center;width:130px;">Amount (Rs.)</td>
                                    <td style="text-align:center;width:170px;">Evidence / particulars</td>
                                    </tr>
                                    <tr>
                                    <td style="text-align:center;">(1)</td>
                                    <td style="text-align:center;">(2)</td>
                                    <td style="text-align:center;">(3)</td>
                                    <td style="text-align:center;">(4)</td>
                                    </tr>
                                    <?php $i = 0;
                                    foreach($value['details'] as $val){ 
                                        $name = isset($val['name'])?$val['name']:'';
                                        $details = isset($val['details'])?$val['details']:'';
                                        $i++;
                                        ?>
                                    <tr>
                                        <td style="width:30px;"><?php echo $i;?></td>
                                        <td style="width:320px;"><?php echo $name;?><br><?php echo $details; ?>
                                            <br>
                                        <?php $j=1;
                                            foreach($val['subheadvalues'] as $head){
                                              $sub = isset($head['sub_details'])?$head['sub_details']:''; 
                                              if($head['sub_details1'] !=''){
                                              $subdet = $sub.'('.$head['sub_details1'].')';
                                              }else{
                                              $subdet = $sub;
                                              }
                                              $n = intval($j);
                                              $result = '';
                                              // Declare a lookup array that we will use to traverse the number:
                                              $lookup = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400,
                                                             'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40,
                                                             'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
                                                  foreach ($lookup as $roman => $value) 
                                                  {
                                                    // Determine the number of matches
                                                      $matches = intval($n / $value);
                                                    // Store that many characters
                                                      $result .= str_repeat($roman, $matches);
                                                    // Substract that from the number
                                                      $n = $n % $value;
                                                  }
                                              $text = '('.strtolower($result).') '. $subdet.'<br>';
                                              echo $text;
                                              $j++; } ?>
                                        </td>
                                    <td style="width:130px;"><?php echo '';?><br><?php echo ''; ?>
                                            <br>
                                        <?php 
                                            foreach($val['subheadvalues'] as $head){
                                              $rate = isset($head['tax_value'])?$head['tax_value']:''; 
                                              $sub = isset($head['sub_details'])?$head['sub_details']:''; 
                                              if($head['sub_details1'] !=''){
                                              $subdet = $sub.'('.$head['sub_details1'].')';
                                              }else{
                                              $subdet = $sub;
                                              }
                                              $len = strlen($subdet);
                                              $len1 = ceil($len/70);
                                              $break = '';
                                              for($k=0;$k<($len1-1);$k++){
                                                  $break .= "<br>";
                                              }
                                              echo '<div style="text-align:center">'.$rate.'</div>'. $break;
                                             
                                               } ?></td>
                                    <td style="width:170px;"><?php echo '';?><br><?php echo ''; ?>
                                            <br>
                                        <?php 
                                            foreach($val['subheadvalues'] as $head){
                                              $evidence = isset($head['evidence'])?$head['evidence']:''; 
                                              $sub = isset($head['sub_details'])?$head['sub_details']:''; 
                                              if($head['sub_details1'] !=''){
                                              $subdet = $sub.'('.$head['sub_details1'].')';
                                              }else{
                                              $subdet = $sub;
                                              }
                                              $len = strlen($subdet);
                                              $len1 = ceil($len/70);
                                              $break = '';
                                              for($k=0;$k<($len1-1);$k++){
                                                  $break .= "<br>";
                                              }
                                              echo '<div style="text-align:center">'.$evidence.'</div>'. $break;
                                               } ?></td>
                                    </tr>
                                    <?php } ?>
                                    <tr><td colspan="4" style="text-align:center;">Verification</td></tr>
                                    <tr><td colspan="4" style="text-align:center;">
                <?php echo "I $Empname, $class of $guradian do hereby certify that the information given above is complete and correct.";?></td></tr>
                                    <tr><td colspan="2">Place: <?php echo $city; ?><br>
                                        Date: <?php echo date('d-m-Y'); ?><br>
                                        Designation: <?php echo $designation; ?>
                                    </td>
                                    <td colspan="2"><br>(Signature of the employee) <br><?php echo $Empname;?></td>
                                    </tr>
                                </tbody>
                               </table>
                
             </page><?php }}}?>
<?php } ?>
