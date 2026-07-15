
<?php 
//debug($company);
//debug($arr_comp_complaince_info);
//debug($branch);
//debug($department);
//debug($designation);
//debug($contactinfo);
//debug($arr_branches);
//debug($arr_dept);
//debug($arr_desig);
//debug($arr_banks);?>
<div id="content">
    <button type="button" class="btn btn-danger btn-md" id="cmd" style="float: right; margin-top: -60px;"><i class="fa fa-file-pdf-o"></i> </button>
<?php if($company == 1){?>

<div >
                    
<!--                    <font style="text-align: center; padding-top: 4px; "><?php echo $contactinfo['business_name']; ?>

                    </font>-->
                    <div style="width: 100%;text-align: center;"><h1><b><?php echo $contactinfo['business_name']; ?></b></h1></div>
                    <div style="width: 100%;text-align: center;"><h5><b><?php echo $contactinfo['address']; ?></b></h5></div>
                    <div style="width: 100%;text-align: center;"><h5><b><?php echo $contactinfo['city']; ?> ,PIN - <?php echo $contactinfo['pincode']; ?>,<?php echo $contactinfo['state']; ?></b></h5></div>
                    <div style="width: 100%;text-align: center;"><h5><b><?php echo "Phone : " . $contactinfo['phone']; ?>,<?php echo ' Email : ' . $contactinfo['email']; ?></b></h5></div>
                    <div style="width: 100%;text-align: center;"><h5><b>________________________________________________________________________________________________</b></h5></div> 
                    
                </div>

       
    <div style="width: 100%;padding-bottom: 15px;padding-left: 237px;">
        <font><b>Address</b></font>
         <font style="width: 10%;padding-left: 174px;">:</font>
         <font style="padding-left: 10%;"><?php echo isset($contactinfo['address']) ? $contactinfo['address'] : '' ?> </font>
    </div>  
    <div style="width: 100%;padding-bottom: 15px;padding-left: 237px;">
        <font><b>City</b></font>
         <font style="width: 10%;padding-left: 197px;">:</font>
         <font style="padding-left: 10%;"> <?php echo isset($contactinfo['city']) ? $contactinfo['city'] : '' ?> </font>
    </div> 
    <div style="width: 100%;    padding-bottom: 15px;padding-left: 237px;">
        <font><b>State</b></font>
         <font style="width: 10%;padding-left: 190px;">:</font>
         <font style="padding-left: 10%;"> <?php echo isset($contactinfo['state']) ? $contactinfo['state'] : '' ?></font>
    </div>
    <div style="width: 100%;    padding-bottom: 15px;padding-left: 237px;">
        <font><b>Zip Code</b></font>
         <font style="width: 10%;padding-left: 168px;">:</font>
         <font style="padding-left: 10%;"><?php echo isset($contactinfo['pincode']) ? $contactinfo['pincode'] : '' ?></font>
    </div>
    <div style="width: 100%;    padding-bottom: 15px;padding-left: 237px;">
        <font><b> Contact Number</b></font>
         <font style="width: 10%;padding-left: 120px;">:</font>
         <font style="padding-left: 10%;"> <?php echo isset($contactinfo['phone']) ? $contactinfo['phone'] : '' ?></font>
    </div>
    <div style="width: 100%;    padding-bottom: 15px;padding-left: 237px;">
        <font><b>  Email</b></font>
         <font style="width: 10%;padding-left: 187px;">:</font>
         <font style="padding-left: 10%;"><?php echo isset($contactinfo['email']) ? $contactinfo['email'] : '' ?></font>
    </div>
    <div style="width: 100%;padding-left: 237px;    padding-bottom: 15px">
        <font><b>Fax </b></font>
         <font style="width: 10%;padding-left: 200px;">:</font>
         <font style="padding-left: 10%;"><?php echo isset($contactinfo['fax']) ? $contactinfo['fax'] : '' ?></font>
    </div>
    <div style="width: 100%;    padding-bottom: 15px;padding-left: 237px;">
        <font><b>CIN No</b></font>
         <font style="width: 10%;padding-left: 180px;">:</font>
         <font style="padding-left: 10%;"><?php echo isset($arr_comp_complaince_info['ComplianceInfo']['cin_no']) ? $arr_comp_complaince_info['ComplianceInfo']['cin_no'] :'';?></font>
    </div>
    <div style="width: 100%;    padding-bottom: 15px;padding-left: 237px;">
        <font><b>PAN No</b></font>
         <font style="width: 10%;padding-left: 177px;">:</font>
         <font style="padding-left: 10%;"><?php echo isset($arr_comp_complaince_info['ComplianceInfo']['pan_no']) ? $arr_comp_complaince_info['ComplianceInfo']['pan_no'] :'';?></font>
    </div>
    <div style="width: 100%;    padding-bottom: 15px;padding-left: 237px;">
        <font><b>TAN No</b></font>
         <font style="width: 10%;padding-left: 178px;">:</font>
         <font style="padding-left: 10%;"> <?php echo isset($arr_comp_complaince_info['ComplianceInfo']['tan_no']) ? $arr_comp_complaince_info['ComplianceInfo']['tan_no'] :'';?></font>
    </div>
    <div style="width: 100%;    padding-bottom: 15px;padding-left: 237px;">
        <font><b>GST No</b></font>
         <font style="width: 10%;padding-left: 178px;">:</font>
         <font style="padding-left: 10%;"> <?php echo isset($arr_comp_complaince_info['ComplianceInfo']['service_tax']) ? $arr_comp_complaince_info['ComplianceInfo']['service_tax'] :'';?></font>
    </div>
    <div style="width: 100%;    padding-bottom: 15px;padding-left: 237px;">
        <font><b>ESI No</b></font>
         <font style="width: 10%;padding-left: 183px;">:</font>
         <font style="padding-left: 10%;"> <?php echo isset($arr_comp_complaince_info['ComplianceInfo']['emp_state_ins_no']) ? $arr_comp_complaince_info['ComplianceInfo']['emp_state_ins_no'] :'';?></font>
    </div>
    <div style="width: 100%;    padding-bottom: 15px;padding-left: 237px;">
        <font><b>Prof Tax No(Co.)</b></font>
         <font style="width: 10%;padding-left: 122px;">:</font>
         <font style="padding-left: 10%;"><?php echo isset($arr_comp_complaince_info['ComplianceInfo']['pt_no_co']) ? $arr_comp_complaince_info['ComplianceInfo']['pt_no_co'] :'';?></font>
    </div>
    
    <div style="width: 100%;    padding-bottom: 15px;padding-left: 237px;">
        <font><b>Prof Tax No(Dir.)</b></font>
         <font style="width: 10%;padding-left: 122px;">:</font>
         <font style="padding-left: 10%;"><?php echo isset($arr_comp_complaince_info['ComplianceInfo']['pt_no_dir']) ? $arr_comp_complaince_info['ComplianceInfo']['pt_no_dir'] :'';?></font>
    </div>
    <div style="width: 100%;    padding-bottom: 15px;padding-left: 237px;">
        <font><b> Prof Tax No(Emp.)</b></font>
         <font style="width: 10%;padding-left: 112px;">:</font>
         <font style="padding-left: 10%;"><?php echo isset($arr_comp_complaince_info['ComplianceInfo']['pt_no_emp']) ? $arr_comp_complaince_info['ComplianceInfo']['pt_no_emp'] :'';?> </font>
    </div>
    
    
    
    

<?php }?>
<?php if($branch == 1){
    $i=1;?>
    <div >
                    
<!--                    <font style="text-align: center; padding-top: 4px; "><?php echo $contactinfo['business_name']; ?>

                    </font>-->
                    <div style="width: 100%;text-align: center;"><h1><b><?php echo $contactinfo['business_name']; ?></b></h1></div>
                    <div style="width: 100%;text-align: center;"><h5><b><?php echo $contactinfo['address']; ?></b></h5></div>
                    <div style="width: 100%;text-align: center;"><h5><b><?php echo $contactinfo['city']; ?> ,PIN - <?php echo $contactinfo['pincode']; ?>,<?php echo $contactinfo['state']; ?></b></h5></div>
                    <div style="width: 100%;text-align: center;"><h5><b><?php echo "Phone : " . $contactinfo['phone']; ?>,<?php echo ' Email : ' . $contactinfo['email']; ?></b></h5></div>
                    <div style="width: 100%;text-align: center;"><h5><b>________________________________________________________________________________________________</b></h5></div> 
                    
                </div>
     <div style="width: 100%;padding-top: 10px;margin-left: 230px;"><h2>Branch Details</h2></div>
<!--         <div style="width: 100%;border-top: 1px solid #050505;padding-bottom: 10px;"></div>-->
    <?php foreach ($arr_branches as $value) {?>
       
        <div style="width: 100%;padding-bottom: 15px;margin-left: 230px;">
            <font><h3><b><?php echo $i;?>. <?php echo isset($value['Units']['branch_name']) ? $value['Units']['branch_name'] : '' ?></b></h3></font>
        </div>
       <div style="width: 100%;padding-bottom: 15px;margin-left: 230px;">
        <font><b>Address</b></font>
         <font style="width: 10%;padding-left: 174px;">:</font>
         <font style="padding-left: 10%;"><?php echo isset($value['Units']['address']) ? $value['Units']['address'] : '' ?> </font>
    </div>  
    <div style="width: 100%;padding-bottom: 15px;margin-left: 230px;">
        <font><b>City</b></font>
         <font style="width: 10%;padding-left: 197px;">:</font>
         <font style="padding-left: 10%;"><?php echo isset($value['Units']['city']) ? $value['Units']['city'] : '' ?> </font>
    </div> 
    <div style="width: 100%;   margin-left: 230px; padding-bottom: 15px">
        <font><b>State</b></font>
         <font style="width: 10%;padding-left: 190px;">:</font>
         <font style="padding-left: 10%;"><?php echo isset($value['Units']['state']) ? $value['Units']['state'] : '' ?></font>
    </div>
    <div style="width: 100%;  margin-left: 230px;  padding-bottom: 15px">
        <font><b>Zip Code</b></font>
         <font style="width: 10%;padding-left: 168px;">:</font>
         <font style="padding-left: 10%;"><?php echo isset($value['Units']['pincode']) ? $value['Units']['pincode'] : '' ?></font>
    </div>
    <div style="width: 100%; margin-left: 230px;   padding-bottom: 15px">
        <font><b>Latitude</b></font>
         <font style="width: 10%;padding-left: 169px;">:</font>
         <font style="padding-left: 10%;"><?php echo isset($value['Units']['latitude']) ? $value['Units']['latitude'] : '' ?></font>
    </div>
    <div style="width: 100%;  margin-left: 230px;  padding-bottom: 15px">
        <font><b>Longitude</b></font>
         <font style="width: 10%;padding-left: 160px;">:</font>
         <font style="padding-left: 10%;"><?php echo isset($value['Units']['longitude']) ? $value['Units']['longitude'] : '' ?></font>
    </div> 
    <div style="width: 100%;  margin-left: 230px;  padding-bottom: 15px">
        <font><b>Leave Year Start Date</b></font>
         <font style="width: 10%;padding-left: 93px;">:</font>
         <font style="padding-left: 10%;"><?php echo isset($value['Leave_year']['LeaveStart']) ? $value['Leave_year']['LeaveStart'] : '0000:00:00' ?></font>
    </div>   
    <div style="width: 100%; margin-left: 230px;   padding-bottom: 15px">
        <font><b>Leave Year End Date</b></font>
         <font style="width: 10%;padding-left: 100px;">:</font>
         <font style="padding-left: 10%;"> <?php echo isset($value['Leave_year']['LeaveEnd']) ? $value['Leave_year']['LeaveEnd'] : '0000:00:00' ?></font>
    </div> 
    <div style="width: 100%;  margin-left: 230px;  padding-bottom: 15px">
        <font><b>Financial Year Start Date</b></font>
         <font style="width: 10%;padding-left: 72px;">:</font>
         <font style="padding-left: 10%;"><?php echo isset($value['Fin_year']['FinStart']) ? $value['Fin_year']['FinStart'] : '0000:00:00' ?></font>
    </div>  
    <div style="width: 100%;  margin-left: 230px;  padding-bottom: 15px">
        <font><b>Financial Year Start Date</b></font>
         <font style="width: 10%;padding-left: 72px;">:</font>
         <font style="padding-left: 10%;"><?php echo isset($value['Fin_year']['FinEnd']) ? $value['Fin_year']['FinEnd'] : '0000:00:00' ?></font>
    </div>      
        
         
   <?php $i++; }
}
?>
<?php if($department == 1){
    $i=1;?>
    <div >
                    
<!--                    <font style="text-align: center; padding-top: 4px; "><?php echo $contactinfo['business_name']; ?>

                    </font>-->
                    <div style="width: 100%;text-align: center;"><h1><b><?php echo $contactinfo['business_name']; ?></b></h1></div>
                    <div style="width: 100%;text-align: center;"><h5><b><?php echo $contactinfo['address']; ?></b></h5></div>
                    <div style="width: 100%;text-align: center;"><h5><b><?php echo $contactinfo['city']; ?> ,PIN - <?php echo $contactinfo['pincode']; ?>,<?php echo $contactinfo['state']; ?></b></h5></div>
                    <div style="width: 100%;text-align: center;"><h5><b><?php echo "Phone : " . $contactinfo['phone']; ?>,<?php echo ' Email : ' . $contactinfo['email']; ?></b></h5></div>
                    <div style="width: 100%;text-align: center;"><h5><b>________________________________________________________________________________________________</b></h5></div> 
                    
                </div>
    <div style="width: 100%;margin-left: 232px;"><h2>Department Details</h2></div>
    
    <?php foreach ($arr_dept as $value) {?>
        
         <div style="width: 100%;padding-bottom: 10px;margin-left: 232px;">
            <font> <?php echo $i;?>. <?php echo isset($value['Departments']['dept_name']) ? $value['Departments']['dept_name'] : '' ?> - <?php echo isset($value['Departments']['dept_code']) ? $value['Departments']['dept_code'] : '' ?></font>
        
        </div>
        
   <?php $i++; }

}
?>
<?php if($designation == 1){
    $i=1;?>
    <div >
                    
<!--                    <font style="text-align: center; padding-top: 4px; "><?php echo $contactinfo['business_name']; ?>

                    </font>-->
                    <div style="width: 100%;text-align: center;"><h1><b><?php echo $contactinfo['business_name']; ?></b></h1></div>
                    <div style="width: 100%;text-align: center;"><h5><b><?php echo $contactinfo['address']; ?></b></h5></div>
                    <div style="width: 100%;text-align: center;"><h5><b><?php echo $contactinfo['city']; ?> ,PIN - <?php echo $contactinfo['pincode']; ?>,<?php echo $contactinfo['state']; ?></b></h5></div>
                    <div style="width: 100%;text-align: center;"><h5><b><?php echo "Phone : " . $contactinfo['phone']; ?>,<?php echo ' Email : ' . $contactinfo['email']; ?></b></h5></div>
                    <div style="width: 100%;text-align: center;"><h5><b>________________________________________________________________________________________________</b></h5></div> 
                    
                </div>                                                 
    <div style="width: 100%;margin-left: 234px;"><h2>Designation Details</h2></div>
    
     
   
   <?php  foreach ($arr_desig as $value) {?>
        <div style="width: 100%;padding-bottom: 10px;padding-top: 15px;margin-left: 234px;">
        <font ><?php echo $i;?>. <?php echo isset($value['Designation']['desig_name']) ? $value['Designation']['desig_name'] : '' ?> - <?php echo isset($value['Designation']['desig_code']) ? $value['Designation']['desig_code'] : '' ?></font>
         
    </div> 
    
    
        
   <?php $i++; }

}
?>
<?php if($bank == 1){
    $i=1;?>
    <div >
                    <div style="width: 100%;text-align: center;"><h1><b><?php echo $contactinfo['business_name']; ?></b></h1></div>
                    <div style="width: 100%;text-align: center;"><h5><b><?php echo $contactinfo['address']; ?></b></h5></div>
                    <div style="width: 100%;text-align: center;"><h5><b><?php echo $contactinfo['city']; ?> ,PIN - <?php echo $contactinfo['pincode']; ?>,<?php echo $contactinfo['state']; ?></b></h5></div>
                    <div style="width: 100%;text-align: center;"><h5><b><?php echo "Phone : " . $contactinfo['phone']; ?>,<?php echo ' Email : ' . $contactinfo['email']; ?></b></h5></div>
                    <div style="width: 100%;text-align: center;"><h5><b>________________________________________________________________________________________________</b></h5></div> 
                    
                </div>
    <div style="width: 100%;margin-left: 233px;"><h2>Bank Details</h2></div>
    <font></font>
    
    <!--<hr style="border-color: black;">-->
   <?php  foreach ($arr_banks as $value) {?>
        
        <div style="width: 100%;margin-left: 233px;">
            <h3><b><?php echo $i;?>.<?php echo isset($value['Banks']['bank_name']) ? $value['Banks']['bank_name'] : '' ?></b></h3> 
        <hr>
        </div>
    <div style="width: 100%;margin-left: 233px;">
        <font><b>Bank Branch</b></font>
         <font style="width: 10%;padding-left: 97px;">:</font>
         <font style="padding-left: 10%;"><?php echo isset($value['Banks']['bank_branch']) ? $value['Banks']['bank_branch'] : '' ?> </font>
    </div>  
    <div style="width: 100%;margin-left: 233px;">
        <font><b>IFSC Code</b></font>
         <font style="width: 10%;padding-left: 113px;">:</font>
         <font style="padding-left: 10%;"><?php echo isset($value['Banks']['ifsc_code']) ? $value['Banks']['ifsc_code'] : '' ?> </font>
    </div> 
    <div style="width: 100%;    padding-bottom: 15px;margin-left: 233px;">
        <font><b>Account Number</b></font>
         <font style="width: 10%;padding-left: 72px;">:</font>
         <font style="padding-left: 10%;"><?php echo isset($value['Banks']['acct_no']) ? $value['Banks']['acct_no'] : '' ?></font>
    </div> 

   <?php $i++; }

}
?>
    <!--<div id="editor"></div>-->
<!--<button id="cmd">generate PDF</button>-->
</div>
<div id="editor"></div>

<script type="text/javascript">
    var doc = new jsPDF();
var specialElementHandlers = {
    '#editor': function (element, renderer) {
        return true;
    }
};

$('#cmd').click(function () {
    doc.fromHTML($('#content').html(), 20, 5, {
        'width': 170,
        'height': 800,
            'elementHandlers': specialElementHandlers
    });
    
    doc.save('CompanyProfileReport.pdf');
});
</script>