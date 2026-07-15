<script>
    $.validate({
        form: '#form-contacts-master'
    });
    var options = {
        success: function (resp) {
            $('#modalForm').modal('hide');
            $('#contacttable').datagrid('reload');
            $.notify($.parseJSON(resp).msg, {
                type: 'success',
                allow_dismiss: false
            });
        }  // post-submit callback
    };
    $('#form-contacts-master').on('submit', function (event) {
        event.preventDefault();
        //alert("haiii");
        $('#form-contacts-master').ajaxSubmit(options);
    });
    //function submitForm(){
    //    $('#form-contacts-master').ajaxSubmit(options);
    //}
</script>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title" style="    margin: 5px 28px 0; "><b><?php echo $title; ?></b></h4>
        </div>
        <div class="modal-body">
            <form role="form" id="form-contacts-master" action="<?php echo $this->webroot; ?>Beneficiary/save" method="POST">
                
                
                <div class="row">
                    <div class="col-md-6">
                        <label for="company_name" >Beneficiary Name<span style="color: red"> * </span></label>
                        <input autocomplete="off" type="text" name="company_name" id="company_name" class="form-control" value="<?php echo (isset($arr_contacts[0]['Beneficiary']) ? $arr_contacts[0]['Beneficiary']["company_name"] : ""); ?>"  placeholder="Company Name" required="required">
                    </div>
                    <div class="col-md-6">
                        <Label for="Code" >Code</label>
                        <input autocomplete="off" id="code" type="text" class="form-control" name="code" value="<?php echo (isset($arr_contacts[0]['Beneficiary']['code']) ? $arr_contacts[0]['Beneficiary']['code'] : ""); ?>"  placeholder="Enter Code" >
                    </div>
                   
                </div>
                
                <div class="row">
                     <div class="col-md-6">
                        <Label for="Address" >Reg. No</label>
                        <input autocomplete="off" id="reg" type="text" class="form-control" name="reg" value="<?php echo (isset($arr_contacts[0]['Beneficiary']['reg']) ? $arr_contacts[0]['Beneficiary']['reg'] : ""); ?>"  placeholder="Enter Reg. Number" >
                    </div>
                    <div class="col-md-6">
                        <Label for="Address" >Address</label>
                        <input autocomplete="off" id="address" type="text" class="form-control" name="address" value="<?php echo (isset($arr_contacts[0]['Beneficiary']) ? $arr_contacts[0]['Beneficiary']["address"] : ""); ?>"  placeholder="Enter Address" >
                    </div>
                   
                </div>
                
                <div class="row">
                     <div class="col-md-6">
                        <Label for="City" >City</label>
                        <input autocomplete="off" id="city" type="text" class="form-control" name="city" value="<?php echo (isset($arr_contacts[0]['Beneficiary']) ? $arr_contacts[0]['Beneficiary']["city"] : ""); ?>"  placeholder="Enter city">
                    </div>
                    <div class="col-md-6">
                        <Label for="state" >State</label>
                        <input autocomplete="off" id="state" type="text" class="form-control" name="state" value="<?php echo (isset($arr_contacts[0]['Beneficiary']) ? $arr_contacts[0]['Beneficiary']["state"] : ""); ?>"  placeholder="Enter State">
                    </div>
                    
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <label for="pincode">Pin code</label>
                        <input autocomplete="off" id="pincode" type="text" class="form-control" name="pincode" value="<?php echo (isset($arr_contacts[0]['Beneficiary']) ? $arr_contacts[0]['Beneficiary']["pincode"] : ""); ?>"  placeholder="Enter pincode" >
                    </div>
                    <div class="col-md-6">
                        <Label for="Email" >Email ID</label>
                        <input autocomplete="off" id="email" type="email"  class="form-control" name="email" value="<?php echo (isset($arr_contacts[0]['Beneficiary']) ? $arr_contacts[0]['Beneficiary']["email"] : ""); ?>"  placeholder="Enter email">
                    </div>
                    
                    
                </div>
                
                
                <div class="row">
                    <div class="col-md-6">
                        <label for="relationship">Relationship</label>
                        <select id="relationship"  class="form-control" name="relationship">
                            <option value="">--select--</option>
                            <?php $relationship = isset($arr_contacts[0]['Beneficiary']['relationship']) ? $arr_contacts[0]['Beneficiary']['relationship'] : ''; ?>
                            <?php $arr_relationship = array('Vendor', 'Customer','Supplier','Others',);
                            foreach ($arr_relationship as $value) { ?>
                                <?php
                                if ($value == $relationship) {
                                    $selected = 'selected="selected"';
                                } else {
                                    $selected = '';
                                }
                                ?>
                                <option <?php echo $selected; ?> value="<?php echo $value; ?>"><?php echo $value; ?>  </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <Label for="Phone" >Phone</label>
                        <input autocomplete="off" id="phone" type="text" data-validation="number" class="form-control" name="phone" value="<?php echo (isset($arr_contacts[0]['Beneficiary']) ? $arr_contacts[0]['Beneficiary']["phone"] : ""); ?>"  placeholder="Enter phone" r>
                    </div>
                   
                    
                </div>
                                
                <div class="row">
                     <div class="col-md-6">
                        <Label for="Email" >TAN</label>
                        <input autocomplete="off" id="tin" type="tin" class="form-control" name="tin" value="<?php echo (isset($arr_contacts[0]['Beneficiary']) ? $arr_contacts[0]['Beneficiary']["tin"] : ""); ?>"  placeholder="Enter TAN" >
                    </div>
                    <div class="col-md-6">
                        <Label for="account_no" >PAN No.</label>
                        <input autocomplete="off" id="account_no" type="text" class="form-control" name="account_no" value="<?php echo (isset($arr_contacts[0]['Beneficiary']) ? $arr_contacts[0]['Beneficiary']["account_no"] : ""); ?>"  placeholder="Enter PAN Number">
                    </div>
                    
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <Label for="account_no" >GST No.</label>
                        <input autocomplete="off" id="gst" type="text" class="form-control" name="gst" value="<?php echo (isset($arr_contacts[0]['Beneficiary']) ? $arr_contacts[0]['Beneficiary']["gst"] : ""); ?>"  placeholder="Enter gst">
                    </div>
                    <div class="col-md-6">
                        <Label for="bank_name" >Bank Name</label>
                        <input id="bank_name" autocomplete="off" type="text" class="form-control" name="bank_name" value="<?php echo (isset($arr_contacts[0]['Beneficiary']) ? $arr_contacts[0]['Beneficiary']["bank_name"] : ""); ?>"  placeholder="Enter bank_name">
                    </div>
                    
                    
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <Label for="bank_branch" >Bank Branch</label>
                        <input id="bank_branch" autocomplete="off" type="text" class="form-control" name="bank_branch" value="<?php echo (isset($arr_contacts[0]['Beneficiary']) ? $arr_contacts[0]['Beneficiary']["bank_branch"] : ""); ?>"  placeholder="Enter bank_branch" >
                    </div>
                    <div class="col-md-6">
                        <Label for="ifsc_code" >IFSC Code</label>
                        <input id="ifsc_code" type="text" autocomplete="off" class="form-control" name="ifsc_code" value="<?php echo (isset($arr_contacts[0]['Beneficiary']) ? $arr_contacts[0]['Beneficiary']["ifsc_code"] : ""); ?>"  placeholder="Enter ifsc_code">
                    </div>
                </div>
                 <div class="row">
                <div class="col-md-6">
                        <Label for="account_no" >Account No.</label>
                        <input autocomplete="off" id="account_no" type="text" class="form-control" name="account_no" value="<?php echo (isset($arr_contacts[0]['Beneficiary']) ? $arr_contacts[0]['Beneficiary']["account_no"] : ""); ?>"  placeholder="Enter Account Number">
                    </div>
                 </div>
                
                
                
                <legend><h3>Contact Person Details  </h3></legend>
                
                <div class="row">
                    <div class="col-md-6">
                        <Label for="first_name">First Name</label>
                        <input autocomplete="off" id="first_name" type="text" class="form-control" name="first_name"  value="<?php echo (isset($arr_contacts[0]['Beneficiary']) ? $arr_contacts[0]['Beneficiary']["first_name"] : ""); ?>"  placeholder="First Name">
                    </div>
                    <div class="col-md-6">
                        <Label for="middle_name">Designation</label>
                        <input autocomplete="off" id="c_designation" type="text" class="form-control" name="c_designation" value="<?php echo (isset($arr_contacts[0]['Beneficiary']) ? $arr_contacts[0]['Beneficiary']["c_designation"] : ""); ?>"   placeholder="Designation ">
                    </div>
                </div>
             
                
                
                
                
                <div class="modal-footer">
                    <input type="hidden" name="contact_id" value="<?php echo (isset($arr_contacts[0]['Beneficiary']['contact_id']) ? $arr_contacts[0]['Beneficiary']['contact_id'] : ''); ?>" />
                    <button type="submit" class="btn btn-primary"  >Submit</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>

            </form>
        </div>
    </div>
</div>
