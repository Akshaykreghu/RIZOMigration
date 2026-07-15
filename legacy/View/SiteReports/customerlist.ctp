<style>
    .table , td, th,tr {
        border-style: solid;
        border-color: #d4d4de;
     }
    .modal-content {
        width: 125%   !important;
    }
</style>
<?php if( $mode == ''){ ?>
<div class="modal-body" style="overflow-y:initial; padding-left:3%; padding-right:3%; padding-bottom:3%;" >
     <h3 align="center" style="font-weight:bold; font-size: 30px;"> Customer and Vendor Details</h3>
     <h4 align="center" style="font-weight:bold;">(<?php echo isset($user_id) ? "Report run by " . ($user_id) . " - " . $date_time : ''; ?>)</h4> 
     <div class="row">
        <div class="col-md-12">
         <?php  $i = 0;
                if (count($arr_vendor_summary_for_template) == 0) {
                    echo "<h2>No Data Available With The Selected Criteria</h2>";
                } else {  ?>
    <fieldset>
    <?php   
    foreach($arr_vendor_summary_for_template as $data1) {
    $Companyname = $data1['summary']['0']['contacts']['company_name']; ?>
        <h4 align="left" style="font-weight:bold;"><?php echo isset($user_id) ? "Details of " . $Companyname : ''; ?></h4> 
    <?php 
     foreach($data1['summary'] as $data) { 
    $Regno = isset($data['contacts']['reg'])?$data['contacts']['reg']:'';
    $Address = isset($data['contacts']['address'])?$data['contacts']['address']:'';
    $city= isset($data['contacts']['city'])?$data['contacts']['city']:'';
    $state= isset($data['contacts']['state'])?$data['contacts']['state']:'';
    $pincode= isset($data['contacts']['pincode'])?$data['contacts']['pincode']:'';
    $email= isset($data['contacts']['email'])?$data['contacts']['email']:'';  
    $phone= isset($data['contacts']['phone'])?$data['contacts']['phone']:'';
    $tan= isset($data['contacts']['tin'])?$data['contacts']['tin']:'';
    $pan= isset($data['contacts']['pan_no'])?$data['contacts']['pan_no']:'';
    $gst= isset($data['contacts']['gst'])?$data['contacts']['gst']:'';
    $bank= isset($data['contacts']['bank_name'])?$data['contacts']['bank_name']:'';
    $bankbranch= isset($data['contacts']['bank_branch'])?$data['contacts']['bank_branch']:'';
    $ifsc= isset($data['contacts']['ifsc_code'])?$data['contacts']['ifsc_code']:'';
    $accountnumber= isset($data['contacts']['account_no'])?$data['contacts']['account_no']:'';
    $relationship= isset($data['contacts']['relationship'])?$data['contacts']['relationship']:'';
    $contactperson= isset($data['contacts']['first_name'])?$data['contacts']['first_name']:'';
    $designation= isset($data['contacts']['c_designation'])?$data['contacts']['c_designation']:''; ?>  
    <table class="table ">
        <thead>
  <tr>
   <th>Company Name</th>
   <td><?php echo "$Companyname"; ?></td>
   <th>Reg No</th>
   <td><?php echo $Regno; ?></td>
 </tr>
 <tr>
   <th>Address</th>
   <td colspan="3"><?php echo $Address; ?></td>
</tr>
<tr>
   <th >City</th>
   <td><?php echo $city; ?></td>
    <th >State</th>
   <td><?php echo $state; ?></td>
</tr>
<tr>
   <th>Pincode</th>
   <td><?php echo $pincode; ?></td>
    <th >Email ID</th>
   <td><?php echo $email; ?></td>
</tr>
<tr>
   <th >Phone</th>
   <td><?php echo $phone; ?></td>
    <th >TAN</th>
   <td><?php echo $tan; ?></td>
</tr><tr>
   <th >PAN No</th>
   <td><?php echo $pan; ?></td>
    <th >GST No</th>
   <td><?php echo $gst; ?></td>
</tr>
<tr>
   <th >Bank Name</th>
   <td><?php echo $bank; ?></td>
    <th >Bank Branch</th>
   <td><?php echo $bankbranch; ?></td>
</tr>
<tr>
   <th >IFSC Code</th>
   <td><?php echo $ifsc; ?></td>
   <th >Account No</th>
   <td><?php echo $accountnumber; ?></td>
</tr>
<tr>
   <th >Relationship</th>
   <td colspan="3"><?php echo $relationship; ?></td>
   
</tr>
<tr>
   <th >Contact Person Name</th>
   <td><?php echo $contactperson; ?></td>
   <th>Designation</th>
    <td ><?php echo $designation;; ?></td>
</tr>

</thead>
</tbody>
                        </table>
    <?php } }?> 
                    </fieldset>    
                <?php } ?> 
        </div>
    </div>  
    </div>
  
<?php } else{ ?>
    <?php //echo '<style>'.file_get_contents("css/pdfbootstrap.css").'</style>';     ?>
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
    <?php
    echo $this->element('reportadminheader', array(
        'title' => 'Customer and Vendor Details '));
    ?>
    
            <?php   
    foreach($arr_vendor_summary_for_template as $data1) {
    $Companyname = $data1['summary']['0']['contacts']['company_name']; ?>
        <h4 align="left" style="font-weight:bold;"><?php echo isset($user_id) ? "Details of " . $Companyname : ''; ?></h4> 
    <?php 
     foreach($data1['summary'] as $data) { 
    $Companyname = $data['contacts']['company_name'];
    $Regno = isset($data['contacts']['reg'])?$data['contacts']['reg']:'';
    $Address = isset($data['contacts']['address'])?$data['contacts']['address']:'';
    $city= isset($data['contacts']['city'])?$data['contacts']['city']:'';
    $state= isset($data['contacts']['state'])?$data['contacts']['state']:'';
    $pincode= isset($data['contacts']['pincode'])?$data['contacts']['pincode']:'';
    $email= isset($data['contacts']['email'])?$data['contacts']['email']:'';  
    $phone= isset($data['contacts']['phone'])?$data['contacts']['phone']:'';
    $tan= isset($data['contacts']['tin'])?$data['contacts']['tin']:'';
    $pan= isset($data['contacts']['pan_no'])?$data['contacts']['pan_no']:'';
    $gst= isset($data['contacts']['gst'])?$data['contacts']['gst']:'';
    $bank= isset($data['contacts']['bank_name'])?$data['contacts']['bank_name']:'';
    $bankbranch= isset($data['contacts']['bank_branch'])?$data['contacts']['bank_branch']:'';
    $ifsc= isset($data['contacts']['ifsc_code'])?$data['contacts']['ifsc_code']:'';
    $accountnumber= isset($data['contacts']['account_no'])?$data['contacts']['account_no']:'';
    $relationship= isset($data['contacts']['relationship'])?$data['contacts']['relationship']:'';
    $contactperson= isset($data['contacts']['first_name'])?$data['contacts']['first_name']:'';
    $designation= isset($data['contacts']['c_designation'])?$data['contacts']['c_designation']:''; ?>  
    <table class="table ">
        <tbody>
  <tr>
   <th>Company Name</th>
   <td><?php echo "$Companyname"; ?></td>
   <th>Reg No</th>
   <td><?php echo $Regno; ?></td>
 </tr>
 <tr>
   <th>Address</th>
   <td colspan="3"><?php echo $Address; ?></td>
</tr>
<tr>
   <th >City</th>
   <td><?php echo $city; ?></td>
    <th >State</th>
   <td><?php echo $state; ?></td>
</tr>
<tr>
   <th>Pincode</th>
   <td><?php echo $pincode; ?></td>
    <th >Email ID</th>
   <td><?php echo $email; ?></td>
</tr>
<tr>
   <th >Phone</th>
   <td><?php echo $phone; ?></td>
    <th >TAN</th>
   <td><?php echo $tan; ?></td>
</tr><tr>
   <th >PAN No</th>
   <td><?php echo $pan; ?></td>
    <th >GST No</th>
   <td><?php echo $gst; ?></td>
</tr>
<tr>
   <th >Bank Name</th>
   <td><?php echo $bank; ?></td>
    <th >Bank Branch</th>
   <td><?php echo $bankbranch; ?></td>
</tr>
<tr>
   <th >IFSC Code</th>
   <td><?php echo $ifsc; ?></td>
   <th >Account No</th>
   <td><?php echo $accountnumber; ?></td>
</tr>
<tr>
   <th >Relationship</th>
   <td colspan="3"><?php echo $relationship; ?></td>
 </tr>
<tr>
   <th >Contact Person Name</th>
   <td><?php echo $contactperson; ?></td>
   <th>Designation</th>
    <td ><?php echo $designation;; ?></td>
</tr>
</tbody>
 </table>
<?php }  } 
if (empty($arr_vendor_summary_for_template)) { ?> 
                    <h3 style="text-align:center;color:red;">No Records found under this Criteria</h3>
                <?php } ?> 
     
<?php } ?>