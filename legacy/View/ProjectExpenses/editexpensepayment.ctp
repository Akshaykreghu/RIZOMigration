<form class="form-horizontal" style="padding-left:1px; padding-right:8px ; border:1px;margin-bottom:20px;padding-bottom:10px;" id="form-user-master" method="post" action="<?php echo $this->webroot; ?>ProjectExpenses/editpayment_save" >
<!--<h1 class="page-header">Edit Expense</h1>-->
    

        <h4 style="padding-top: 20px;padding-left:20px;">Edit Project Details</h4>
        <div class="divider"></div>
        <div style="padding-left:75px;padding-bottom:20px;">
            <div class="form-group">
            <div class="col-md-10">
                            <label for="in_date" class="col-sm-5 control-label">Project Name<label style="color:red;">*</label></label>
                            <div class="col-md-7">
                                <select id="vendor" name="vendor" class="form-control" required="required">
                                    <option value="">[--Select--]</option>
                                    <?php
                                    foreach ($vendor_list as $list) {
                                        foreach ($list as $l) {
                                            $selected = ($data['0']['emp_expense']['vendor'] == $l["site_pkey"]) ? 'selected="selected"' : '';
                                            echo '<option value="' . $l["site_pkey"] . '" ' . $selected . '>' . $l["site_name"].'-'.$l["site_id"]. '</option>';
                                       
                                        }
                                    }
                                    ?>
                                </select>
                            </div> 
                        </div>
            </div>
             <?php if ($user_group == 1) { ?>
        <div class="form-group">
                        <div class="col-md-10">
                                <label for="in_date" class="col-sm-5 control-label">Choose Employee</label>
                                <div class="col-md-7">
                                    <select id="emp_fkey" class="form-control" name="emp_fkey"  >
                                        <option value="">[--Select--]</option>
                                        <?php
                                        foreach ($arr_employees as $value) {
                                            $selected = ($data['0']['emp_expense']['emp_fkey'] == $value['emp_details']['emp_pkey']) ? 'selected="selected"' : '';
                                            echo '<option value="' . $value['emp_details']['emp_pkey'] . '" ' . $selected . '>' . $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name'] . ' - ' . $value['emp_proff']['emp_company_id'] . '</option>';
                                        }
                                        ?>
                                    </select> 
                                </div>
                            </div>
        </div>
             <?php } ?>
        <div class="form-group">
                        <div class="col-md-10">
                            <label for="beneficiary_fkey" class="col-sm-5 control-label">Beneficiary</label>
                            <div class="col-md-7">
                               <select id="beneficiary_fkey" name="beneficiary_fkey" class="form-control"  >
                                    <option value="">[--Select--]</option>
                                    <?php
                                    foreach ($beneficiary as $ben) {
                                    
                                     $selected = ($data['0']['emp_expense']['beneficiary_fkey'] == $ben['beneficiary']['contact_id']) ? 'selected="selected"' : '';
                                            echo '<option value="' . $ben['beneficiary']['contact_id'] . '" ' . $selected . '>' . $ben['beneficiary']["company_name"] . '</option>';
                                       
                                    }
                                    ?>
                                </select>
                            </div> 
                        </div>
        </div>
             <div class="form-group">
                        <div class="col-md-10">
                            <label for="expense_date" class="col-sm-5 control-label">Bill Date<span class="star">*</span></label>
                            <div class="col-md-7">
                                <input type="text"  class="form-control"  value="<?php echo isset($data['0']['emp_expense']['expense_date'])?$data['0']['emp_expense']['expense_date']:'';?>" name="expense_date" id="expense_date"   placeholder="Select Date" required="required">
                            </div> 
                        </div>
                        
                    </div>

                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="gst_bill_no" class="col-sm-5 control-label">GST Bill No.</label>
                            <div class="col-md-7">
                                  <input type="text"  class="form-control"  value="<?php echo isset($data['0']['emp_expense']['gst_bill_no'])?$data['0']['emp_expense']['gst_bill_no']:'';?>" name="gst_bill_no" id="gst_bill_no" onchange="gst_status();" placeholder="Enter Gst Bill no.">
                         </div> 
                        </div>
                    </div>
               <div class="form-group">
                         <div class="col-md-10">
                            <label for="gst_bill_status" class="col-sm-5 control-label">GST Bill Status</label>
                            <div class="col-md-7">
<!--                             <input type="text"  class="form-control"  value="<?php echo isset($data['0']['emp_expense']['gst_bill_status'])?$data['0']['emp_expense']['gst_bill_status']:'';?>" name="gst_bill_status" id="gst_bill_status"  placeholder="Enter Gst Bill Status.">-->
                           <select id="gst_bill_status"  class="form-control" name="gst_bill_status">
                               <option value="">[--Select--]</option>
                               <?php $statuses = isset($data[0]['emp_expense']['gst_bill_status']) ? $data[0]['emp_expense']['gst_bill_status']: ''; ?>
                               <?php $arr_statuses = array('Completed', 'Pending','Composition','Not Applicable');
                               foreach ($arr_statuses as $value) { ?>
                                <?php
                                if ($value == $statuses) {
                                    $selected = 'selected="selected"';
                                } else {
                                    $selected = '';
                                }
                                ?>
                                <option <?php echo $selected; ?> value="<?php echo $value; ?>"><?php echo $value; ?></option>
                               <?php } ?>
                              </select>
                            </div> 
                        </div>
                 </div>
               <div class="form-group">
                        <div class="col-md-10">
                            <label for="payment" class="col-sm-5 control-label">Amount Paid</label>
                            <div class="col-md-7">
                                <input type="number" step="0.01"  class="form-control" readonly="readonly" onchange="minus();" value="<?php echo $payment;?>" name="payment" id="payment" min="0" max ="<?php echo isset($data['0']['emp_expense']['payment'])?$sum - $data['0']['emp_expense']['payment']:$sum;?>">
                            </div> 
                        </div>
               </div>
               <div class="form-group">
                        <div class="col-md-10">
                            <label for="balance" class="col-sm-5 control-label">Balance</label>
                            <div class="col-md-7">
                                <input type="number" step="0.01" class="form-control" readonly="readonly" value="<?php echo $balance;?>" name="balance" id="balance">
                            </div> 
                        </div>
                         
                    </div>

                    <div class="form-group">
                         <div class="col-md-10">
                            <label for="payment_status" class="col-sm-5 control-label">Payment Status<span class="star">*</span></label>
                            <div class="col-md-7">
                              <select id="payment_status"  class="form-control" readonly="readonly"  name="payment_status" required="required" >
<!--                               <option value="">[--Select--]</option>-->
                               <?php $status = isset($data[0]['emp_expense']['payment_status']) ? $data[0]['emp_expense']['payment_status']: ''; 
                              // $arr_status = array('Completed', 'Pending');
                               $arr_status = array($status);
                               foreach ($arr_status as $value) { ?>
                                <?php
                                if ($value == $status) {
                                    $selected = 'selected="selected"';
                                } else {
                                    $selected = '';
                                }
                                ?>
                                <option <?php echo $selected; ?> value="<?php echo $value; ?>"><?php echo $value; ?></option>
                               <?php } ?>
                              </select>
                               </div> 
                        </div>
                    </div>
                    <div class="form-group">
                <div class="col-md-10">
                    <label style="text-align:left;" class="col-md-5 control-label" >Remarks</label>
                    <div class="col-md-7">
                       <input type="text" class="form-control" value="<?php echo isset($data['0']['emp_expense']['remarks'])? $data['0']['emp_expense']['remarks']:'';?>" name="remarks" id="remarks">
                         </div> 
                </div>
            </div>
        </div>
  <div class="form-group">
           <div class="  pull-right">
                <input type="hidden" name="total" id="total" value="<?php echo $sum;?>">
                <input type="hidden" name="emp_expenses_pkey" id="emp_expenses_pkey" value="<?php echo $data['0']['emp_expense']['emp_expenses_pkey'];?>">
                <button type="submit" id="btn-submit" class="btn btn-primary pull-right "  style="margin-right:15px;">Update Expense</button>
          </div>
  </div> 
          </div>
</form>
<script>
    function gst_status(){
        var bill = $("#gst_bill_no").val();
        if(bill != ''){
           $("#gst_bill_status").val('Completed'); 
           //$("#gst_bill_status").prop("disabled",true);
        }else{
           $("#gst_bill_status").val('');
           //$("#gst_bill_status").prop("disabled",false);  
        }
    }
    $(document).ready(function () {
    $('#expense_date').datepicker({ 
            format: 'yyyy-mm-dd',       
            autoclose: true,
            //startDate: '+1d',
        
        });
  
        });

     function minus(){
         var total = parseFloat($('#total').val());
         var payment = parseFloat($('#payment').val());
         if(total> 0 && payment > 0){
         var balance = total - payment;
         }else if(total> 0){
          var balance = total;   
         }
         balance = parseFloat(balance);
         $("#balance").val(balance);
         
    }
     var options = { 
            success:function(responseText){
                var response = JSON.parse(responseText);
               
                if(response.success == false){
                    closeSmallModalForm();
                }
                else{
                    closeSmallModalForm(); 
                     $.notify("Updated Successfully",{
                    type: 'success',
                    allow_dismiss: false
                });
               var pkey = response.pk;
               showdetails(pkey);
                }              
            }
        }; 
 
        // bind to the form's submit event 
        $('#form-user-master').submit(function() { 
            $(this).ajaxSubmit(options); 
            return false;
        });
        function showdetails(id){
        showLargeModalForm(livesite + 'ProjectExpenses/view_expense/' + id);
                    }
//     $('#form-user-master').on('submit', function (event) {
//            event.preventDefault();
//            
//            if($('#emp_fkey').val() == ''){
//                if ($("#emp_fkey").next(".validation").length == 0) // only add if not added
//                 {
//                $('#emp_fkey').parent().append("<div class='validation' style='color:red;'>Please select employee. </div>");
//                
//                 }
//                 return false;
//            }else{
//                $("#emp_fkey").next(".validation").hide();
//            }
//             if($('#expense_type').val() == ''){
//                if ($("#expense_type").next(".validation").length == 0) // only add if not added
//                 {
//                $('#expense_type').parent().append("<div class='validation' style='color:red;'>Please select expense type.. </div>");
//                
//                 }
//                 return false;
//            }else{
//                $("#expense_type").next(".validation").hide();
//            }
//            if($('#expenses_amount').val() == ''){
//                if ($("#expenses_amount").next(".validation").length == 0) // only add if not added
//                 {
//                $('#expenses_amount').parent().append("<div class='validation' style='color:red;'>Please enter expense amount. </div>");
//                
//                 }
//                 return false;
//            }else{
//                $("#expenses_amount").next(".validation").hide();
//            }
//            if($('#exp_date').val() == ''){
//                if ($("#exp_date").next(".validation").length == 0) // only add if not added
//                 {
//                $('#exp_date').parent().append("<div class='validation' style='color:red;'>Please select expense date. </div>");
//                
//                 }
//                 return false;
//            }else{
//                $("#exp_date").next(".validation").hide();
//            }
//            if($('#payment').val() == ''){
//                if ($("#payment").next(".validation").length == 0) // only add if not added
//                 {
//                $('#payment').parent().append("<div class='validation' style='color:red;'>Please enter payment amount. </div>");
//                
//                 }
//                 return false;
//            }else{
//                $("#payment").next(".validation").hide();
//            }
//            if($('#payment_status').val() == ''){
//                if ($("#payment_status").next(".validation").length == 0) // only add if not added
//                 {
//                $('#payment_status').parent().append("<div class='validation' style='color:red;'>Please select payment status. </div>");
//                
//                 }
//                 return false;
//            }else{
//                $("#payment_status").next(".validation").hide();
//            }
//            $('#form-user-master').ajaxSubmit({
//                success: function (resp) {
//                    $.notify('Updated Successfully.', {
//                        type: 'success',
//                        allow_dismiss: true
//                    });
//                    closeSmallModalForm();
//                }
//                  
//                  var pkey = $.parseJSON(resp).pk;
//                  showLargeModalForm(livesite + 'ProjectExpenses/view_expense/' + pkey);
//            });
//
//        });
</script>