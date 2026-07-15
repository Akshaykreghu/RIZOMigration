
   <form  id="attendanceuploadtable" action="<?php echo $this->webroot; ?>Employee/employeesave" method="POST">
                        <div class="modal-body">
                             <h4 class="modal-title"> Employee CTC</h4>  
                       
                               
                         <div class="form-group">
                         <label for="in_date" class="col-sm-4 control-label">Choose Employee<span class="star">*</span></label>
                          <div class="col-sm-8">
                            <select id="emp_fkey" class="form-control" name="emp_fkey"  >
                             <?php
                                        foreach ($arr_employees as $value) {
                                            $selected = ($data['emp_fkey'] == $value['EmployeeDetails']['emp_pkey']) ? 'selected="selected"' : '';
                                            echo '<option value="' . $value['EmployeeDetails']['emp_pkey'] . '" ' . $selected . '>' . $value['EmployeeDetails']['first_name'] . ' '  .$value['EmployeeDetails']['last_name'] .'</option>';
                                        }
                                        ?>
                            </select>
                        </div>    
                            </div>    
                         <div class="form-group">
                         <label for="in_date" class="col-sm-4 control-label">Anual<span class="star">*</span></label>
                         <div class="col-sm-8">
                          <input type="text" required="required" class="form-control" value="<?php echo isset($data['emp_anual_ctc']) ? $data['emp_anual_ctc'] : '' ; ?>"name="emp_anual_ctc" id="emp_anual_ctc" >
                         </div>
                      </div>
<!--                         <div class="form-group">
                         <label for="in_time" class="col-sm-4 control-label">Loan Balance<span class="star">*</span></label>
                         <div class="col-sm-8">
                          <input type="text" required="required" class="form-control" value="<?php //echo $data['emp_loan_balance']  ?>"name="emp_loan_balance" id="emp_loan_balance" >
                         </div>
                      </div>
                         <div class="form-group">
                         <label for="out_date" class="col-sm-4 control-label">Advance<span class="star">*</span></label>
                         <div class="col-sm-8">
                          <input type="text" required="required" class="form-control" value="<?php //echo $data['emp_advance'] ?>"name="emp_advance" id="emp_advance" >
                         </div>
                      </div>
                         <dv class="form-group">
                         <label for="out_time" class="col-sm-4 control-label">Deducted<span class="star">*</span></label>
                         <div class="col-sm-8">
                          <input type="text" required="required" class="form-control" value="<?php //echo $data['emp_tds_deducted'] ?>"name="emp_tds_deducted" id="emp_tds_deducted" >
                         </div>
                      </div> -->
                         <div class="form-group">
                         <label for="out_time" class="col-sm-4 control-label">Start Date Effective<span class="star">*</span></label>
                         <div class="col-sm-8">
                          <input type="text" required="required" class="form-control" onblur="startdatecheck()" value="<?php echo isset($data['start_date_effective']) ?$data['start_date_effective'] : ""; ?>"name="start_date_effective" Name="in_time" id="in_time" >
                         </div>
                      </div> 
                          <div class="form-group">
                         <label for="out_time" class="col-sm-4 control-label">End Date Effective<span class="star">*</span></label>
                         <div class="col-sm-8">
                          <input type="text" onblur="enddatecheck()" required="required" class="form-control" value="<?php echo isset($data['end_date_effective']) ? $data['end_date_effective'] : ""; ?>" name="end_date_effective" Name="out_time" id="out_time" >
                         </div>
                      </div> 
                         <div class="form-group">
                         <div class="col-sm-8">
                         <input type="hidden" required="required" class="form-control" value="<?php echo isset($data['emp_ctc_upload_pkey']) ? $data['emp_ctc_upload_pkey'] : ""; ?>" name="emp_ctc_upload_pkey" id="emp_ctc_upload_pkey" >
                         </div>
                      </div> 
                        </div>

                     <div class="modal-footer">
            
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" onclick="next();" class="btn btn-primary">Next</button>
                     </div>
                       
                    </form>
<script type="text/javascript">
function next()
{

}   
function startdatecheck()
    {
         var startDate = new Date($('#in_time').val());
         var endDate = new Date($('#out_time').val());

                if (startDate > endDate)
                {
                    alert("expected starting date should be less than ending date");
                    $("#in_time").val('');
                }
    }
function enddatecheck()
    {
         var startDate = new Date($('#in_time').val());
         var endDate = new Date($('#out_time').val());

               if (startDate > endDate){
                  alert("expected ending date should be greater than starting date");
                    $("#out_time").val('');
                }
}
$(document).ready(function() { 
	//$("#pincode").inputmask("999");
  $('#attendanceuploadtable').parsley();
    var options = { 
      success:function(responseText, statusText, xhr, $form){
        alert("Attendance Uploaded Successfully");
	closeModal('att_table');
}
    }; 
 
    // bind to the form's submit event 
    $('#attendanceuploadtable').submit(function() { 
        $(this).ajaxSubmit(options);         
        return false;
    });
       
     $('#in_time').datepicker({
     format: 'yyyy-mm-dd',
     onSelect: function (selected) {
            var esdt = new Date(selected);
            var selectedenddate = $("#out_date").val();
            var ecdt = new Date(selectedenddate);
            if(esdt > ecdt){
                alert('Expected In Date Should Be Less Than Expected Out Date');
                $("#in_date").val('');
            }
            
        }
    })
    $('#out_time').datepicker({
        format: 'yyyy-mm-dd',
        onSelect: function (selected) {
            var ecdt = new Date(selected);
            var selectedstartdate = $("#in_date").val();
            var esdt = new Date(selectedstartdate);
             if(esdt > ecdt){
                alert('Expected Out Date Should Be Greater Than Expected In Date');
                $("#out_date").val('');
            }
           
        }
    })
    $("#out_date").inputmask("yyyy-mm-dd");
         }); 
</script>
                       
                      
              
          