<form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>ProjectIncome/save" id="projectincomeform">
<div class="modal-body">
<fieldset>
<!-- Form Name -->
<div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title" style="    margin: 5px 5px 0; "><b><?php echo $title; ?></b></h4>
        </div>
<?php $pkey = isset($data['0']['project_income']["project_income_pkey"])?$data['0']['project_income']["project_income_pkey"]:''; ?>
<div class="modal-body">
<input id="project_income_pkey" name="project_income_pkey" type="hidden"  value="<?php echo isset($data['0']['project_income']["project_income_pkey"])?$data['0']['project_income']["project_income_pkey"]:''; ?>" >
<!-- Text input-->
  <div class="form-group">
        <label for="project_fkey" class="col-sm-4 control-label" style="text-align:left;">Project Name <span class="star">*</span></label>
        <div class="col-md-7">
            <select id="project_fkey" name="project_fkey" class="form-control" required="required">
                <option value="">[--Select--]</option>
                <?php $site = isset($data['0']['site']['site_pkey'])?$data['0']['site']['site_pkey']:'';
                foreach ($arr_site as $list) {
                        foreach ($list as $l) {
                        $selected = ($site == $l['site_pkey']) ? 'selected="selected"' : ''; ?>
                <option value="<?php echo $l["site_pkey"] ?>" <?php echo $selected; ?> ><?php echo $l["site_id"] . ' - ' . $l["site_name"] ?></option>
                <?php
                    }
                }
                ?>
            </select>
        </div> 
</div>
<!-- Text input-->
<div class="form-group">
  <label class="col-md-4 control-label" for="invoice_number" style="text-align:left;">Invoice No. <span class="star">*</span></label>  
  <div class="col-md-7">
  <input id="invoice_number" name="invoice_number"  value="<?php echo isset($data['0']['project_income']["invoice_number"])?$data['0']['project_income']["invoice_number"]:''; ?>" type="text" placeholder="Invoice No." class="form-control input-md" required="required">
  </div>
</div>
<div class="form-group">
  <label class="col-md-4 control-label" for="invoice_date" style="text-align:left;">Invoice Date <span class="star">*</span></label>  
  <div class="col-md-7">
  <input id="invoice_date" name="invoice_date"  value="<?php echo isset($data['0']['project_income']["invoice_date"])?$data['0']['project_income']["invoice_date"]:''; ?>" type="text" placeholder="Invoice Date" class="form-control input-md" required="required">
   </div>
</div>
<div class="form-group">
  <label class="col-md-4 control-label" for="amount" style="text-align:left;">Bill Amount <span class="star">*</span></label>  
  <div class="col-md-7">
      <input id="amount" name="amount" onchange="add();" value="<?php echo isset($data['0']['project_income']["amount"])?$data['0']['project_income']["amount"]:''; ?>" type="number" step="any" placeholder="Bill Amount" class="form-control input-md" required="required">
  </div>
</div>
<div class="form-group">
  <label class="col-md-4 control-label" for="cgst" style="text-align:left;">CGST</label>  
  <div class="col-md-7">
  <input id="cgst" name="cgst" onchange="add();" value="<?php echo isset($data['0']['project_income']["cgst"])?$data['0']['project_income']["cgst"]:''; ?>" type="number" step="any" placeholder="CGST" class="form-control input-md" >
  </div>
</div>
<div class="form-group">
  <label class="col-md-4 control-label" for="sgst" style="text-align:left;">SGST</label>  
  <div class="col-md-7">
  <input id="sgst" name="sgst" readonly="readonly" value="<?php echo isset($data['0']['project_income']["sgst"])?$data['0']['project_income']["sgst"]:''; ?>" type="number" step="any" placeholder="SGST" class="form-control input-md" >
  </div>
</div>
<div class="form-group">
  <label class="col-md-4 control-label" for="sgst" style="text-align:left;">Total</label>  
  <div class="col-md-7">
  <input id="total" name="total" readonly="readonly" value="<?php echo isset($data['0']['project_income']["total"])?$data['0']['project_income']["total"]:''; ?>" type="number" step="any" placeholder="Total" class="form-control input-md" >
  </div>
</div>
<div class="form-group">
  <?php if($pkey > 0) { ?>
  <label class="col-md-4 control-label" for="payments" style="text-align:left;">Total Amount Received</label>  
  <?php }else{ ?>
  <label class="col-md-4 control-label" for="payments" style="text-align:left;">Amount Received</label>  
  <?php } ?>
  <div class="col-md-7">
      <input id="payments" name="payments" <?php if($pkey > 0) { ?>readonly="readonly"<?php } ?> onchange=" add();minus();" min="0" value="<?php echo isset($data['0']['project_income']["payments"])?$data['0']['project_income']["payments"]:'0'; ?>" type="number" step="any" placeholder="Amount Received" class="form-control input-md">
  </div>
</div>
<?php if($pkey > 0) { ?>
<div class="form-group">
  <label class="col-md-4 control-label" for="payamount" style="text-align:left;">Amount Received</label>  
  <div class="col-md-7">
      <input id="payamount" name="payamount" onchange="minus();" value="" min="0" max="<?php echo isset($data['0']['project_income']["balance"])?$data['0']['project_income']["balance"]:''; ?>" type="number" step="any" placeholder="Amount Received" class="form-control input-md">
  </div>
</div>
<div class="form-group">
  <label class="col-md-4 control-label" for="expensedate" style="text-align:left;">Payment Received Date</label>  
  <div class="col-md-7">
  <input id="expensedate" name="expensedate"  value="" type="text" placeholder="Payment Received Date" class="form-control input-md" >
   </div>
</div>
<?php } ?>
<div class="form-group">
  <label class="col-md-4 control-label" for="balance" style="text-align:left;"> Balance</label>  
  <div class="col-md-7">
      <input id="balance" name="balance" readonly="readonly" value="<?php echo isset($data['0']['project_income']["balance"])?$data['0']['project_income']["balance"]:''; ?>" type="number" step="any" placeholder="Balance" class="form-control input-md" required="required">
  </div>
</div>
<div class="form-group">
  <label class="col-md-4 control-label" for="remarks" style="text-align:left;">Description</label>  
  <div class="col-md-7">
  <input id="remarks" name="remarks"  value="<?php echo isset($data['0']['project_income']["remarks"])?$data['0']['project_income']["remarks"]:''; ?>" type="text" placeholder="Description" class="form-control input-md" >
  </div>
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
      function add(){
         var amt = parseFloat($('#amount').val());
         var sgst = parseFloat($('#cgst').val());
         var cgst = parseFloat($('#cgst').val());
         if(amt> 0 && sgst > 0 ){
         var total = amt + sgst + cgst;
         $("#sgst").val(sgst);
//         }else 
//         if (amt> 0 && igst > 0){
//           var total = amt + igst; 
//           $("#sgst").val('');
//           $("#cgst").val('');
//           $("#igst").val(igst);
         }else if (amt> 0 ){
           var total = amt; 
           $("#sgst").val('');
         }else{
          // var total = 0;   
         }
         total = parseInt(Math.round(total));
         $("#total").val(total);
         $("#balance").val(total);
         minus();
    }
    function minus(){
        var payments = parseFloat($('#payments').val());
        var payamount = parseFloat($('#payamount').val());
        var total = parseFloat($('#total').val());
        var balance = parseFloat($('#balance').val());
        var project_income_pkey = parseFloat($('#project_income_pkey').val()); 
        if(payments > 0){
       if(payments <= total){
        if(payamount){
            if(payamount <= balance){
        var balance = total - payments - payamount;
        }
    else{
        alert("Amount received should be less than balance");
        $("#payamount").val('');
        var balance = total - payments; 
    }
        }else{
        var balance = total - payments;    
        }
        $("#balance").val(balance);
    }
    else{
        alert("Amount received should be less than total");
        $("#payments").val('');
    }
    }else if(payamount){
      if(payamount <= balance){
        var balance = total - payments - payamount;
      }
      else{
        alert("Amount received should be less than balance");
        $("#payamount").val('');
        var balance = total - payments; 
      }  
      $("#balance").val(balance);
    }else{
    }
    }
    
    $('#expense_type_code').on('change',function(){
        checkIfExpensecodeExists();
    })
    
    function checkIfExpensecodeExists(callback){        
        var expense_type_code = $('#expense_type_code').val();
        var id = $('#id').val();
        $.ajax({
            url: 'ExpenseType/checkexpensetypecodeexists/'+id,
            type: 'POST',
            data: {
                expense_type_code: expense_type_code
            },
            success: function (resp)
            {
                if(resp > 0){      
                    alert("Expense Code Already Exists!!");
                    $('#expense_type_code').val('');
                }else{
                    if(typeof callback === 'function'){
                        callback.call();
                    }
                }
            }
        });
    }
    
    
    $('#expense_type_name').on('change',function(){
        checkIfExpensenameExists();
    })
    
    function checkIfExpensenameExists(callback){        
        var expense_type_name = $('#expense_type_name').val();
        var id = $('#id').val();
        $.ajax({
            url: 'ExpenseType/checkexpensetypecodeexists'+id,
            type: 'POST',
            data: {
                expense_type_name: expense_type_name
            },
            success: function (resp)
            {
                if(resp > 0){      
                   alert("Expense Name Already Exists!!");
                    $('#expense_type_name').val('');
                }else{
                    if(typeof callback === 'function'){
                        callback.call();
                    }
                }
            }
        });
    }
    
    
    
    
    
$(document).ready(function() { 
  $('#projectincomeform').parsley();
    var options = { 
    success:       function(responseText, statusText, xhr, $form){
        
        var response = JSON.parse(responseText);
       if (response.success) {
            closeModal('dpttable');
            $.notify(response.msg, {
              type: 'success',
              allow_dismiss: true
            });
            $('#modalForm').modal('hide');
        }else{
            alert(response.msg);
//            $.notify(response.msg, {
//              type: 'danger',
//              allow_dismiss: true
//            });
            }
}
    }; 
 
    // bind to the form's submit event 
    $('#projectincomeform').submit(function() { 
        // inside event callbacks 'this' is the DOM element so we first 
        // wrap it in a jQuery object and then invoke ajaxSubmit 
        $(this).ajaxSubmit(options); 
        
        $('#projecttable').datagrid('reload');
        // !!! Important !!! 
        // always return false to prevent standard browser submit and page navigation 
        return false; 
    });
      $('#invoice_date').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true
    });
    $('#expensedate').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true
    });
    }); 
</script>