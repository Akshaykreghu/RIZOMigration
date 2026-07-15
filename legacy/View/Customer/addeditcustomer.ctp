<script>
     $.validate({
  form : '#form-customer-master'
   });
    var options = { 
        success: function(resp){
            $('#modalDiv').modal('hide');
            $('#customertable').datagrid('reload');
            $.notify($.parseJSON(resp).msg,{
                type: 'success',
                allow_dismiss: false
            });
        }  // post-submit callback
    }; 
    $('#form-customer-master').on('submit',function(event){
         event.preventDefault();
         //alert("haiii");
         $('#form-customer-master').ajaxSubmit(options);
    });
    //function submitForm(){
    //    $('#form-vendor-master').ajaxSubmit(options);
    //}
</script>
<div class="modal-dialog" style="width:800px;">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-header"><?php echo $title; ?></h4>
        </div>
      <div class="modal-body">
          <!-- Form starts -->
  <div id="" class="">
      <form class="form-horizontal" id="form-customer-master" action="<?php echo $this->webroot;?>Customer/save" method="POST" >
                    
          
           
            
          
                    <div class="modal-body">
                       <div class="form-group">
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" >First Name<label style="color:red;">*</label></label>
                                <div class="col-md-8">
                                    <input autocomplete="off" id="first_name" type="text" class="form-control" name="first_name"  value="<?php echo (isset($arr_contacts[0]['Contacts']) ? $arr_contacts[0]['Contacts']["first_name"] : ""); ?>"   required="required">
                                </div>
                            </div>
                        
                            <div class="col-md-6">
                            <label style="text-align:left;" class="col-md-4 control-label" >Last Name</label>
                                <div class="col-md-8">
                                    <input autocomplete="off" type="text" name="last_name" id="last_name" class="form-control" value="<?php  echo (isset($arr_contacts[0]['Contacts']) ? $arr_contacts[0]['Contacts']["last_name"] : ""); ?>"   >
                                </div> 
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-6">
                            <label style="text-align:left;" class="col-md-4 control-label" >Middle name</label>
                                <div class="col-md-8">
                                    <input autocomplete="off" id="middle_name" type="text" class="form-control" name="middle_name" value="<?php  echo (isset($arr_contacts[0]['Contacts']) ? $arr_contacts[0]['Contacts']["middle_name"] : ""); ?>"   >
                                </div>
                            </div>
                       
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" >Company Name<label style="color:red;">*</label></label>
                                <div class="col-md-8">
                                     <input autocomplete="off" type="text" name="company_name" id="company_name" class="form-control" value="<?php  echo (isset($arr_contacts[0]['Contacts']) ? $arr_contacts[0]['Contacts']["company_name"] : ""); ?>"   required="required">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-6">
                            <label style="text-align:left;" class="col-md-4 control-label" for="">Email Id</label>
                                <div class="col-md-8">
                                     <input autocomplete="off" id="email" type="email" data-validation="email" class="form-control" name="email" value="<?php  echo (isset($arr_contacts[0]['Contacts']) ? $arr_contacts[0]['Contacts']["email"] : ""); ?>"   >
                                </div>
                            </div>
                       
                            <div class="col-md-6">
                            <label style="text-align:left;" class="col-md-4 control-label" for="">Phone</label>
                                <div class="col-md-8">
                                    <input autocomplete="off" id="phone" type="text" data-validation="number" class="form-control" name="phone" value="<?php  echo (isset($arr_contacts[0]['Contacts']) ? $arr_contacts[0]['Contacts']["phone"] : ""); ?>"   >
                                </div>
                            </div>
                        </div>
                         <div class="form-group">
                            <div class="col-md-6">
                            <label style="text-align:left;" class="col-md-4 control-label" for="">Address</label>
                                <div class="col-md-8">
                                    <input autocomplete="off" id="address" type="text" class="form-control" name="address" value="<?php  echo (isset($arr_contacts[0]['Contacts']) ? $arr_contacts[0]['Contacts']["address"] : ""); ?>"   >
                                </div>
                            </div>
                        
                            <div class="col-md-6">
                            <label style="text-align:left;" class="col-md-4 control-label" for="">City</label>
                                <div class="col-md-8">
                                     <input autocomplete="off" id="city" type="text" class="form-control" name="city" value="<?php  echo (isset($arr_contacts[0]['Contacts']) ? $arr_contacts[0]['Contacts']["city"] : ""); ?>"   >
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-6">
                            <label style="text-align:left;" class="col-md-4 control-label" for="">State</label>
                                <div class="col-md-8">
                                     <input autocomplete="off" id="state" type="text" class="form-control" name="state" value="<?php  echo (isset($arr_contacts[0]['Contacts']) ? $arr_contacts[0]['Contacts']["city"] : ""); ?>"   >
                                </div>
                            </div>
                      
                            <div class="col-md-6">
                            <label style="text-align:left;" class="col-md-4 control-label" for="">Pine Code</label>
                                <div class="col-md-8">
                                     <input autocomplete="off" id="pincode" type="text" data-validation="number" class="form-control" name="pincode" value="<?php  echo (isset($arr_contacts[0]['Contacts']) ? $arr_contacts[0]['Contacts']["pincode"] : ""); ?>"  >
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-6">
                            <label style="text-align:left;" class="col-md-4 control-label" for="">Payment Type</label>
                            <div class="col-md-8">
                                <select id="payment_type" class="form-control"  name="payment_type" required="required" >

                                    <option value="CASH" <?php echo (isset($arr_contacts[0]['Contacts']['payment_type']) && (($arr_contacts[0]['Contacts']['payment_type']) == 'CASH' || ($arr_contacts[0]['Contacts']['payment_type']) == 'CASH') ) ? 'selected="selected"' : ''; ?>>CASH</option>
                                    <option value="CREDIT" <?php echo (isset($arr_contacts[0]['Contacts']['payment_type']) && (($arr_contacts[0]['Contacts']['payment_type']) == 'CREDIT' || ($arr_contacts[0]['Contacts']['payment_type']) == 'CREDIT') ) ? 'selected="selected"' : ''; ?>>CREDIT</option>
                                </select>                            
                            </div>
                            </div>
                      
                            <div class="col-md-6">
                            <label style="text-align:left;" class="col-md-4 control-label" for="">Payment Mode</label>
                            <div class="col-md-8">
                                <select id="payment_mode" class="form-control"  name="payment_mode" required="required" >

                                    <option value="CASH" <?php echo (isset($arr_contacts[0]['Contacts']['payment_mode']) && (($arr_contacts[0]['Contacts']['payment_mode']) == 'CASH' || ($arr_contacts[0]['Contacts']['payment_mode']) == 'CASH') ) ? 'selected="selected"' : ''; ?>>CASH</option>
                                    <option value="BANK" <?php echo (isset($arr_contacts[0]['Contacts']['payment_mode']) && (($arr_contacts[0]['Contacts']['payment_mode']) == 'BANK' || ($arr_contacts[0]['Contacts']['payment_mode']) == 'BANK') ) ? 'selected="selected"' : ''; ?>>BANK</option>
                                </select>                       
                            </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" >Price Type</label>
                                <div class="col-md-8">

                                    <select required="required" id="product_rate_type" class="form-control"  name="product_rate_type">

                                        <option value="">---select---</option>
                                        <option value="1"  <?php echo ( isset($arr_contacts['0']['Contacts']['product_rate_type']) &&  $arr_contacts['0']['Contacts']['product_rate_type']==1) ? 'selected="selected"' : ''; ?>  >Retail</option>
                                        <option value="2"  <?php echo ( isset($arr_contacts['0']['Contacts']['product_rate_type']) &&  $arr_contacts['0']['Contacts']['product_rate_type'] ==2) ? 'selected="selected"' : ''; ?> >Wholesale</option>
                                        <option value="3"  <?php echo ( isset($arr_contacts['0']['Contacts']['product_rate_type']) && $arr_contacts['0']['Contacts']['product_rate_type'] == 3) ? 'selected="selected"' : ''; ?>  >C & F</option>
                                        <option value="4"  <?php echo ( isset($arr_contacts['0']['Contacts']['product_rate_type']) && $arr_contacts['0']['Contacts']['product_rate_type'] == 4) ? 'selected="selected"' : ''; ?> >Special 1</option>
                                        <option value="5"  <?php echo ( isset($arr_contacts['0']['Contacts']['product_rate_type']) && $arr_contacts['0']['Contacts']['product_rate_type'] == 5) ? 'selected="selected"' : ''; ?>  >Special 2</option>

                                    </select> 
                                    </div>
                            </div>
                            <div class="col-md-6">
                            <label style="text-align:left;" class="col-md-4 control-label" for="">PAN No</label>
                                <div class="col-md-8">
                                    <input id="pan_no" autocomplete="off" type="text" class="form-control" name="pan_no" value="<?php  echo (isset($arr_contacts[0]['Contacts']) ? $arr_contacts[0]['Contacts']["pan_no"] : ""); ?>"  >
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-6">
                            <label style="text-align:left;" class="col-md-4 control-label" for="">IFSC Code</label>
                                <div class="col-md-8">
                                     <input id="ifsc_code" type="text" autocomplete="off"   class="form-control" name="ifsc_code" value="<?php  echo (isset($arr_contacts[0]['Contacts']) ? $arr_contacts[0]['Contacts']["ifsc_code"] : ""); ?>"   >
                                </div>
                            </div>
                        
                            <div class="col-md-6">
                            <label style="text-align:left;" class="col-md-4 control-label" for="">Account Number</label>
                                <div class="col-md-8">
                                    <input autocomplete="off" id="account_no" type="text" data-validation="number" class="form-control" name="account_no" value="<?php  echo (isset($arr_contacts[0]['Contacts']) ? $arr_contacts[0]['Contacts']["account_no"] : ""); ?>"   >
                                </div>
                            </div>
                        </div>
                        
                       
                    <div class="modal-footer ">
                        <input type="hidden" name="contact_id" value="<?php  echo (isset($arr_contacts[0]['Contacts']['contact_id']) ? $arr_contacts[0]['Contacts']['contact_id'] : ''); ?>" />
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                        <button type="submit" id="btn-submit" class="btn btn-primary">Save</button>
                    </div>
        </div>
                            

        
    </form>
    <!-- Tax Head Detail Form -->
   
</div>
          
               
          
<!-- form ends-->
      </div>
           
    </div>

</div>
