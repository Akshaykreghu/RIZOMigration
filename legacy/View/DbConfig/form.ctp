       <form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>Department/savedepartment" id="deptForm">
      <div class="modal-body">

<fieldset>

<!-- Form Name -->
<legend>DB Config</legend>
<!-- Text input-->
<div class="form-group">
  <label class="col-md-4 control-label" for="company_code">Company Code Name</label>  
  <div class="col-md-5">
  <input id="company_code" name="company_code"  value="<?php echo $data_db['DbConfig']["company_code"]; ?>" type="text" placeholder="Department Name" class="form-control input-md" required="">
    
  </div>
</div>

<!-- Text input-->
<div class="form-group">
  <label class="col-md-4 control-label" for="company_code">B.D ESSL</label>  
  <div class="col-md-5">
  <input id="company_code" name="company_code"  value="<?php echo $data_db['DbConfig']["company_code"]; ?>" type="text" placeholder="Department Code" class="form-control input-md" required="">
    
  </div>
</div>
<div class="form-group">
  <label class="col-md-4 control-label" for="attendance_type">Attendance Type</label>  
  <div class="col-md-5">
  <input id="attendance_type" name="attendance_type"  value="<?php echo $data_db['DbConfig']["attendance_type"]; ?>" type="text" placeholder="Department Name" class="form-control input-md" required="">
    
  </div>
</div>

<div class="form-group">
  <label class="col-md-4 control-label" for="attendance_format">Attendance Format</label>  
  <div class="col-md-5">
  <input id="attendance_format" name="attendance_format"  value="<?php echo $data_db['DbConfig']["attendance_format"]; ?>" type="text" placeholder="Department Name" class="form-control input-md" required="">
    
  </div>
</div>

<div class="form-group">
  <label class="col-md-4 control-label" for="attendance_format">Attendance Date</label>  
  <div class="col-md-5">
  <input id="attendance_format" name="attendance_format"  value="<?php echo $data_db['DbConfig']["attendance_format"]; ?>" type="text" placeholder="Department Name" class="form-control input-md" required="">
    
  </div>
</div>

<div class="form-group">
  <label class="col-md-4 control-label" for="emp_login">Emp Login</label>  
  <div class="col-md-5">
  <input id="emp_login" name="emp_login"  value="<?php echo $data_db['DbConfig']["emp_login"]; ?>" type="text" placeholder="Emp Login" class="form-control input-md" required="">
    
  </div>
</div>

<div class="form-group">
  <label class="col-md-4 control-label" for="payroll_type">Payroll Type</label>  
  <div class="col-md-5">
  <input id="payroll_type" name="payroll_type"  value="<?php echo $data_db['DbConfig']["payroll_type"]; ?>" type="text" placeholder="Payroll Type" class="form-control input-md" required="">
    
  </div>
</div>

<div class="form-group">
  <label class="col-md-4 control-label" for="dept_name">Email Setup</label>  
  <div class="col-md-5">
  <input id="email_setup" name="email_setup"  value="<?php echo $data_db['DbConfig']["email_setup"]; ?>" type="text" placeholder="Email Setup" class="form-control input-md" required="">
   <!--input id="dept_name" name="dept_name"  value="<?php //echo $data_db['DbConfig']["dept_name"]; ?>" type="text" placeholder="Email Setup" class="form-control input-md" required=""-->
    
  </div>
</div>

<div class="form-group">
  <label class="col-md-4 control-label" for="TDS_setup">TDS Setup</label>  
  <div class="col-md-5">
  <input id="TDS_setup" name="TDS_setup"  value="<?php echo $data_db['DbConfig']["TDS_setup"]; ?>" type="text" placeholder="TDS Setup" class="form-control input-md" required="">
    
  </div>
</div>

<div class="form-group">
  <label class="col-md-4 control-label" for="Salary_date">Salary Date</label>  
  <div class="col-md-5">
  <input id="Salary_date" name="Salary_date"  value="<?php echo $data_db['DbConfig']["Salary_date"]; ?>" type="text" placeholder="Salary Date" class="form-control input-md" required="">
    
  </div>
</div>

<div class="form-group">
  <label class="col-md-4 control-label" for="created_date">Created Date</label>  
  <div class="col-md-5">
          <input id="created_date" name="created_date"  value="<?php echo $data_db['DbConfig']["created_date"]; ?>" type="text" placeholder="Created Date" class="form-control input-md" required="">
   
  </div>
</div>


</fieldset>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </form>   
<script type="text/javascript">
$(document).ready(function() { 
  $('#deptForm').parsley();
    var options = { 
    success:       function(responseText, statusText, xhr, $form){
	closeModal('dpttable');
}
    }; 
 
    // bind to the form's submit event 
    $('#deptForm').submit(function() { 
        // inside event callbacks 'this' is the DOM element so we first 
        // wrap it in a jQuery object and then invoke ajaxSubmit 
        $(this).ajaxSubmit(options); 
 
        // !!! Important !!! 
        // always return false to prevent standard browser submit and page navigation 
        return false; 
    });
    }); 
</script>