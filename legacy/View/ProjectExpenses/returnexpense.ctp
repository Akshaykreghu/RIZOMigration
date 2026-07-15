<form class="form-horizontal" style="padding-left:1px; padding-right:8px ; border:1px;margin-bottom:20px;" id="form-user-master" method="post" action="<?php echo $this->webroot; ?>ProjectExpenses/return_save" >
<!--<h1 class="page-header">Edit Expense</h1>-->
  <?php 
$exp = $sum['0']['0']['a'];
$cgst = $sum['0']['0']['b'];
$sgst = $sum['0']['0']['c'];
$igst = $sum['0']['0']['d'];
$total = $sum['0']['0']['e']; ?>  

        <h4 style="padding-top: 20px;padding-left:20px;">Edit Expense</h4>
        <div class="divider"></div>
        <div style="padding-left:75px;padding-bottom:20px;">
         <input type="hidden"  class="form-control" value="<?php echo $data['0']['emp_expense_details']['expense_type_fkey'];?>" name="expense_type" id="expense_type" >
                
<!--        <div class="form-group">
                        <div class="col-md-10">
                            <label for="in_time" class="col-sm-5 control-label">Expense Name<span class="star">*</span></label>
                            <div class="col-md-7">
                               <input type="hidden"  class="form-control" value="<?php echo $data['0']['emp_expense_details']['expense_type_fkey'];?>" name="expense" id="expense" >
                                <input type="text" class="form-control" name="expense_type" id="expense_type" style="background:white;" placeholder="Enter Expense Type"  required="required" list="team_list">
                                <select id="expense_type" name="expense_type" class="form-control" required="required" onchange="filterCategory(this);">
                                    <option value="">[--Select--]</option>
                                     HERE SAVES THE EXPENSE TYPE PKEY INTO EXPENSE TABLE
                                    
                                    <?php
                                    foreach ($expense_type as $type) {
                                        foreach ($type as $t) {
                                    
                                     $selected = ($data['0']['emp_expense_details']['expense_type_fkey'] == $t['expense_type_pkey']) ? 'selected="selected"' : '';
                                            echo '<option value="' . $t['expense_type_pkey'] . '" ' . $selected . '>' . $t["expense_type_name"] . '</option>';
                                       
                                        }
                                    }
                                    ?>
                                </select>
                            </div> 
                        </div>
        </div>-->
<!--        <div class="form-group">
                        <div class="col-md-10">
                            <label for="category" class="col-sm-5 control-label">Category</label>
                            <div class="col-md-7">
                                  <select id="category" class="form-control js-example-basic-single" name="category">
                                      <?php if($data['0']['emp_expense_details']['category_fkey']){ ?>
                                      <option value="<?php echo $data['0']['emp_expense_details']['category_fkey']; ?> " ><?php echo $data['0']['expense_item']['category'];?></option>
                                      <?php } ?>
                                  </select>
                            </div> 
                        </div>
        </div>-->

                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="credit_note" class="col-sm-6 control-label">GST Credit Note No.</label>
                            <div class="col-md-6">
                                <input type="text"  class="form-control" value="" name="credit_note" id="credit_note"   placeholder="GST Credit Note No.">
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="expense_date" class="col-sm-6 control-label">GST Credit Note Date<span class="star">*</span></label>
                            <div class="col-md-6">
                                <input type="text"  class="form-control" value="" required="required" name="expense_date" id="expense_date"   placeholder="Select Date">
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="ref_bill_no" class="col-sm-6 control-label">Reference Bill No.</label>
                            <div class="col-md-6">
                                <input type="text"  class="form-control" value="" name="ref_bill_no" id="ref_bill_no"   placeholder="Reference Bill No.">
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="credit_date" class="col-sm-6 control-label">Reference Bill Date</label>
                            <div class="col-md-6">
                                <input type="text"  class="form-control" value="" name="credit_date" id="credit_date"   placeholder="Select Date">
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="expenses_amount" class="col-sm-6 control-label">Purchase Return Amount<span class="star">*</span></label>
                            <div class="col-md-6">
                                <input type="number" step="0.01" min="0" required="required" max="<?php echo $exp;?>" onchange="add();" class="form-control" value="<?php echo isset($exp)?round($exp,2):'';?>" name="expenses_amount" id="expenses_amount">
                            </div> 
                        </div>
                    </div>
               <div class="form-group">
                         <div class="col-md-10">
                            <label for="cgst" class="col-sm-6 control-label">CGST</label>
                            <div class="col-md-6">
                                <input type="number" step="0.01" min="0" class="form-control" max="<?php echo $cgst;?>" onchange="add();" value="<?php echo isset($cgst)?round($cgst,2):0;?>" name="cgst" id="cgst" >
                            </div> 
                        </div>
                 </div>
               <div class="form-group">
                        <div class="col-md-10">
                            <label for="sgst" class="col-sm-6 control-label">SGST</label>
                            <div class="col-md-6">
                                <input type="number" step="0.01" min="0" class="form-control"  max="<?php echo $sgst;?>" onchange="add();" value="<?php echo isset($sgst)?round($sgst,2):0;?>" name="sgst" id="sgst">
                            </div> 
                        </div>
               </div>
               <div class="form-group">
                        <div class="col-md-10">
                            <label for="igst" class="col-sm-6 control-label">IGST</label>
                            <div class="col-md-6">
                                <input type="number" step="0.01" min="0" class="form-control" max="<?php echo $igst;?>" onchange="add();" value="<?php echo isset($igst)?round($igst,2):0;?>" name="igst" id="igst">
                            </div> 
                        </div>
               </div>
               <div class="form-group">
                        <div class="col-md-10">
                            <label for="total" class="col-sm-6 control-label">Total</label>
                            <div class="col-md-6">
                                <input type="number" step="0.01" class="form-control" max="<?php echo $total;?>" value="<?php echo isset($total)?$total:'';?>" name="total" id="total">
                            </div> 
                        </div>
                         
                    </div>
 
             <div class="form-group">
                        <div class="col-md-10">
                            <label for="payment" class="col-sm-6 control-label">Payment Return</label>
                            <div class="col-md-6">
                                <input type="number" step="0.01"  min="0" onchange="minus();" class="form-control" max="<?php echo $data['0']['emp_expense_details']['payment'];?>" value="" name="payment_total" id="payment_total">
                            </div> 
                        </div>
               </div>
                   <div class="form-group">
                        <div class="col-md-10">
                            <label for="return_date" class="col-sm-6 control-label">Payment Return Date</label>
                            <div class="col-md-6">
                                <input type="text"  class="form-control" value="" name="return_date" id="return_date"   placeholder="Select Date">
                            </div> 
                        </div>
                    </div>
<!--              <div class="form-group">
                        <div class="col-md-10">
                            <label for="payment" class="col-sm-5 control-label">Payment</label>
                            <div class="col-md-7">
                                <input type="number" max="<?php echo $data['0']['emp_expense_details']['balance'];?>" step="0.01" <?php if($data[0]['emp_expense_details']['payment_status'] =="Completed"){ ?>readonly="readonly"<?php } ?>  onchange="minus();" class="form-control" value="" name="payment" id="payment">
                            </div> 
                        </div>
               </div>-->
                    
<!--              <div class="form-group">
                            <div class="col-md-10">
                            <label for="balance" class="col-sm-5 control-label">Balance</label>
                            <div class="col-md-7">
                                <input type="number" step="0.01" readonly="readonly" class="form-control" value="<?php echo isset($data['0']['emp_expense_details']['balance'])?$data['0']['emp_expense_details']['balance']:'0';?>" name="balance" id="balance" >
                            </div> 
                        </div>
                       
                    </div>-->

<!--               <div class="form-group">
                        <div class="col-md-10">
                            <label for="related_party" class="col-sm-5 control-label">Related Party</label>
                            <div class="col-md-7">
                                <input type="text" class="form-control" value="<?php echo isset($data['0']['emp_expense_details']['related_party'])?$data['0']['emp_expense_details']['related_party']:'';?>" name="related_party" id="related_party">
                            </div> 
                        </div>
               </div>-->

  <div class="form-group">
           <div class="  pull-right">
                <input type="hidden" class="form-control" value="<?php echo isset($data['0']['emp_expense_details']['total'])?$data['0']['emp_expense_details']['total']:0;?>" name="paid_total" id="paid_total">
                <input type="hidden" name="pay_status" value="<?php echo isset($data[0]['emp_expense_details']['payment_status'])?$data[0]['emp_expense_details']['payment_status']:'Pending';?>">
                <input type="hidden" name="emp_expense_fkey" id="emp_expense_fkey" value="<?php echo $data['0']['emp_expense_details']['emp_expense_fkey'];?>">
                <input type="hidden" name="expense_details_pkey" id="expense_details_pkey" value="<?php echo $data['0']['emp_expense_details']['expense_details_pkey'];?>">
                <?php //if($data[0]['emp_expense_details']['payment_status'] =="Pending"){ ?>
                <button type="submit" id="btn-submit" class="btn btn-primary pull-right "  style="margin-right:15px;">Update Expense</button>
                <?php //} ?>
          </div>
  </div> 
          </div>
</form>
<script>
   
     function filterCategory(type)
    {
     $("#category").val('');
        var exp = $('#expense_type').val();
        $("#category").select2(
                {
                    //closeOnSelect:false,
                    placeholder: "Select",
                    allowClear: true,
                    ajax: {
                        url: livesite + "ProjectExpenses/category/" + exp,
                        dataType: 'json',
                        delay: 250,
                        data: function (params) { 
                            console.log(params);
//                            return{
//                               id: '<?php echo $data['0']['emp_expense_details']['category_fkey']; ?>', 
//                               a_key: '<?php echo $data['0']['expense_item']['category']; ?>' 
//                            }
                            return {
                                q: params.term, // search term
                                page: params.page
                            };
                        },
                        processResults: function (data, params) {
                            params.page = params.page || 1;
                            return {
                                results: data.items,
                                pagination: {
                                    more: (params.page * 30) < data.total_count
                                }
                            };
                        }
                    },
                    escapeMarkup: function (markup) {
                        return markup;
                    }
                });
    }
    $(document).ready(function () {
   // $('#category').val(<?php echo $data['0']['expense_item']['category'];?>);
   // $('#category').select2('data', {id: '<?php echo $data['0']['emp_expense_details']['category_fkey']; ?>', a_key: '<?php echo $data['0']['expense_item']['category']; ?>'});
    $('#credit_date').datepicker({ 
            format: 'yyyy-mm-dd',       
            autoclose: true,
            //startDate: '+1d',
        });
   $('#expense_date').datepicker({ 
            format: 'yyyy-mm-dd',       
            autoclose: true,
            //startDate: '+1d',
        });
   $('#return_date').datepicker({ 
            format: 'yyyy-mm-dd',       
            autoclose: true,
            //startDate: '+1d',
        });
        });
        function add(){
         var amt = parseFloat($('#expenses_amount').val());
         var sgst = parseFloat($('#cgst').val());
         var cgst = parseFloat($('#cgst').val());
         var igst = parseFloat($('#igst').val());
         if(amt> 0 && sgst > 0){
         var total = amt + sgst + cgst;
         $("#igst").val('');
         $("#sgst").val(sgst);
         }else if(amt> 0 && igst > 0){
         var total = amt + igst;
         $("#sgst").val('');
         $("#cgst").val('');
         $("#igst").val(igst);
         }else if (amt> 0 ){
           var total = amt; 
           $("#sgst").val('');
         }else{
          //var total = amt + sgst + cgst + igst;   
         }
         
         total = parseInt(Math.round(total));
         //$("#sgst").val(sgst);
         $("#total").val(total);
        
    }
     function minus(){
         var total = parseFloat($('#total').val());
         var payment_total = parseFloat($('#payment_total').val());
         var payment = parseFloat($('#payment').val());
         if(total> 0 && payment > 0){
         var balance = total - payment - payment_total;
         }else if(total> 0){
          var balance = total - payment_total;   
         }
         balance = parseFloat(balance);
         if(balance < 0){
         // $("#payment").val('');
         }else{
         $("#balance").val(balance);
         }
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
        showLargeModalForm(livesite + 'ProjectExpenses/show_expense/' + id);
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