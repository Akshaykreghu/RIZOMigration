<?php  if ($mode == 'edit') { ?>
    <form class="form-vertical" method="post" action="<?php echo $this->webroot; ?>ProjectExpenses/grandexpense" id="expenseapproveform">
        <div class="modal-header" style="background:#004d77;color:white;">

            <h4 class="modal-title"><?php echo $arr_expense['0']['emp_expense']['expense_status']; ?></h4>
        </div> 

        <div class="modal-body">
            <input id="expensepkey" name="expensepkey" type="hidden"  value="<?php echo $arr_expense['0']['emp_expense']['emp_expenses_pkey']; ?>" >
            <input id="reject" name="reject" type="hidden"  value="0" >
            <input id="headpkey" name="headpkey" type="hidden"  value="<?php echo isset($expense_type['0']['expense_type']['expense_head_fkey'])?$expense_type['0']['expense_type']['expense_head_fkey']:''; ?>" >
            <div class="form-group">
                <div class="col-md-6">
<!--                    <div style="font-size: 25px; margin-top: -8px; " class="col-md-12"><?php //echo $arr_expense['0']['employee_info']['EmpName']; ?> - <span style="font-size: 20px;"><?php //echo $arr_expense['0']['employee_info']['employee_id']; ?></span></div>
                    <div style="padding-left: 15px;"> <?php //echo $arr_expense['0']['employee_info']['designation']; ?> -<span style="font-size: 15px;"><?php //echo $arr_expense['0']['employee_info']['department']; ?></span></div>-->
                   
               <div style="font-size: 25px; margin-top: -15px; " class="col-md-12"><?php echo $arr_att['0']['site']['site_name']; ?> - <span style="font-size: 20px;"><?php echo $arr_att['0']['site']['site_id']; ?></span></div>
                </div>
<!--                <div class="col-md-6">
                    <div style="text-align:left;" class="col-md-5 control-label" >Branch</div>   
                    <label style="text-align:left;" class="col-md-1">:</label>
                    <span style="padding-left: 15px;"><?php echo $arr_expense['0']['employee_info']['branch']; ?></span>
                </div>
                <div class="col-md-6">
                    <div style="text-align:left;" class="col-md-5 control-label" >Applied Date</div>
                    <label style="text-align:left;" class="col-md-1">:</label>
                    <span style="padding-left: 15px;"><?php echo date('Y-m-d', strtotime($arr_expense['0']['emp_expense']['created_date'])); ?></span>
                </div>-->
            </div>
            <hr style="padding-right: 1px; padding-top: 1px; margin-bottom: -3px;">
            <div class="form-group">
                <div class="row">
                    <div class="col-md-12">
                        <br>
                        <div class="col-md-6">

                            <label style="text-align:left;" class="col-md-5 control-label" >Request ID </label>
                            <label style="text-align:left;" class="col-md-1">:</label>
                            <span style="text-align:left;" class="col-md-6"><?php echo $arr_att['0']['emp_expense']['expense_id']; ?></span>

                        </div>
                        <div class="col-md-6">

                            <label style="text-align:left;" class="col-md-5 control-label" >Beneficiary</label>
                            <label style="text-align:left;" class="col-md-1">:</label>
                            <span style="text-align:left;" class="col-md-6">
                                <?php echo $arr_att['0']['beneficiary']['company_name']; ?>
                            </span>

                        </div>
                    </div>
                </div>
            </div>
            
             <div class="form-group">
                <div class="row">
                    <div class="col-md-12" style="margin-top: -30px;">
                        <br>
                    
                        <div class="col-md-6">

                            <label style="text-align:left;" class="col-md-5 control-label" >GST Bill No.</label>
                            <label style="text-align:left;" class="col-md-1">:</label>
                            <span style="text-align:left; word-wrap: break-word;" class="col-md-6"><?php echo $arr_expense['0']['emp_expense']['gst_bill_no']; ?></span>
                        </div>
                        <div class="col-md-6">

                            <label style="text-align:left;" class="col-md-5 control-label" >GST Bill Status</label>
                            <label style="text-align:left;" class="col-md-1">:</label>
                            <span style="text-align:left;" class="col-md-6"><?php echo $arr_expense['0']['emp_expense']['gst_bill_status']; ?></span>
                        </div> 
                    </div>
                </div>
            </div> 
            <div class="form-group">
                <div class="row">
                    <div class="col-md-12" style="margin-top: -30px;">
                        <br>
                        <div class="col-md-6">

                            <label style="text-align:left;" class="col-md-5 control-label" >Expense Date</label>
                            <label style="text-align:left;" class="col-md-1">:</label>
                            <span style="text-align:left;" class="col-md-6"><?php echo date('d-m-Y',strtotime($arr_expense['0']['emp_expense']['expense_date'])); ?></span>
                        </div>
                        <div class="col-md-6">

                            <label style="text-align:left;" class="col-md-5 control-label" >Expense Type</label>
                            <label style="text-align:left;" class="col-md-1">:</label>
                            <span style="text-align:left;" class="col-md-6"><?php echo $arr_att['0']['expense_type']['expense_type_name']; ?></span>
                        </div>
                        
                    </div>
                </div>
            </div>
             <div class="form-group">
                <div class="row">
                    <div class="col-md-12" style="margin-top: -30px;">
                        <br>
                        <div class="col-md-6">

                            <label style="text-align:left;" class="col-md-5 control-label" >Approved By</label>
                            <label style="text-align:left;" class="col-md-1">:</label>
                            <span style="text-align:left;" class="col-md-6"><?php echo isset($apr_person)?$apr_person[0]["employee_info"]["EmpName"]:$apr_by_admin; ?></span>
                        </div>
                    <div class="col-md-6">

                            <label style="text-align:left;" class="col-md-5 control-label" >Total Amount</label>
                            <label style="text-align:left;" class="col-md-1">:</label>
                            <span style="text-align:left; word-wrap: break-word;" class="col-md-6"><?php echo $arr_expense['0']['emp_expense']['expenses_amount']; ?></span>
                        </div>
                        
                        
                    </div>
                </div>
            </div>
          <div class="form-group">
                <div class="row">
                    <div class="col-md-12" style="margin-top: -30px;">
                        <br>
                    <div class="col-md-6">

                            <label style="text-align:left;" class="col-md-5 control-label" >Payment Status</label>
                            <label style="text-align:left;" class="col-md-1">:</label>
<!--                             <span style="text-align:left;" class="col-md-6">  <select id="payment_status"  class="form-control" name="payment_status" required="required" >
                               <option value="">[--Select--]</option>
                               <?php $status = isset($arr_expense[0]['emp_expense']['payment_status']) ? $arr_expense[0]['emp_expense']['payment_status']: ''; ?>
                               <?php $arr_status = array('Completed', 'Pending');
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
                                 </select></span>-->
                           <span style="text-align:left;" class="col-md-6"><?php echo $arr_expense['0']['emp_expense']['payment_status']; ?></span>
                        </div> 
                        <div class="col-md-6">

                            <label style="text-align:left;" class="col-md-5 control-label" >Amount Paid</label>
                            <label style="text-align:left;" class="col-md-1">:</label>
                            <span style="text-align:left; word-wrap: break-word;" class="col-md-6"><?php echo $arr_expense['0']['emp_expense']['payment']; ?></span>
                        </div>
                        
                    </div>
                </div>
            </div> 
            <div class="form-group">
                <div class="row">
                    <div class="col-md-12" style="margin-top: -30px;">
                        <br>
                         <div class="col-md-6">

                            <label style="text-align:left;" class="col-md-5 control-label" >Balance</label>
                            <label style="text-align:left;" class="col-md-1">:</label>
                            <span style="text-align:left;" class="col-md-6"><?php echo $arr_expense['0']['emp_expense']['balance']; ?></span>
                        </div>
                        <div class="col-md-6">
                            <label style="text-align:left;" class="col-md-5 control-label" >Remarks</label>
                            <label style="text-align:left;" class="col-md-1">:</label>
                            <span style="text-align:left;" class="col-md-6"><?php echo $arr_expense['0']['emp_expense']['remarks']; ?></span>
                        </div>
                        
                       
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <div class="col-md-12" style="margin-top: -30px;">
                        <br>
                        <div class="col-md-6">
                            <label style="text-align:left;" class="col-md-5 control-label" >Applied By</label>
                            <label style="text-align:left;" class="col-md-1">:</label>
                            <span style="text-align:left; word-wrap: break-word;" class="col-md-6"><?php echo $arr_expense['0']['employee_info']['EmpName']; ?></span>
                        </div>
                         <div class="col-md-6">
                            <label style="text-align:left;" class="col-md-5 control-label" for="REMARKS">Remarks By Approved Person</label>
                            <label style="text-align:left;" class="col-md-1">:</label>
                            <div class="col-md-5">
                                <textarea id="REMARKS" rows="2" cols="20" name="REMARKS" type="text" placeholder="Remarks" style=" width: 195px;"  wrap="hard" class="form-control input-md" ></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<!--            <div class="form-group">
                <div class="row">
                    <div class="col-md-12" style="margin-top: -30px;">
                        <br>
                        <div class="col-md-6">

                            <label style="text-align:left;" class="col-md-5 control-label" >Purpose</label>
                            <label style="text-align:left;" class="col-md-1">:</label>
                            <span style="text-align:left; word-wrap: break-word;" class="col-md-6"><?php echo $arr_expense['0']['emp_expense']['purpose']; ?></span>
                        </div>
                    </div>
                </div>
            </div>--> 
            <div class="form-group">
                <div class="row">
                    <div class="col-md-12" style="margin-top: -30px;margin-bottom: -18px;    text-align: right;">
                        <br>
<?php if($arr_expense['0']['emp_expense_details']['expense_details_pkey'] !=''){ ?>
<button type="button" id="btn-img" class="btn btn-info" onclick="showimage(<?php echo $arr_expense['0']['emp_expense_details']['expense_details_pkey'];?>)"><?php echo "Show Image"; ?></button>
<?php } ?>                              
                        <button type="button" id="btn-grandemployeeleave" class="btn btn-danger"  onclick="cancelmodal()"><?php echo "Cancel"; ?></button>
<!--                        <button type="button" id="btn-grandemployeeleave" class="btn btn-info"  onclick="showimage()"><?php echo "Show Image"; ?></button>-->
                        <button type="button" id="btn-approve" class="btn btn-success strict-field" onclick="grandexpense(this)">Approve</button>
                        <button type="button" id="btn-grandemployeeleave" class="btn btn-danger"  onclick="rejectexpense(this)" value="reject"><?php echo "Reject"; ?></button>

                    </div>
                </div>
            </div>
            <!--         <div class="form-group">
                         <div class="row">
                             <div class="col-md-12" style="">
                                        <img class="" style="width: 80px; height: 80px;display: block;margin-left: auto;margin-right: auto;margin-top: -66px;position: relative;" src="<?php echo $arr_expense['0']['emp_expense']['image']; ?>" >
                                    </div>
                         </div>
                     </div>-->
        </div>
    </form>
    <script>
        function showimage(id) {
            showSmallModalForm(livesite + 'ProjectExpenses/showimage/' + id);
        }   
        var empexpenseoptions = {};
        jQuery(document).ready(function () {
            $('#expenseapproveform').parsley();
            empexpenseoptions = {
                success: function (resp) {
                    var success = $.parseJSON(resp).success;
                    var message = $.parseJSON(resp).message;
                    alert(message);
                    if (success) {
                        $('#largeModalForm').modal('hide');
                        reloadTable('empleaverequeststable');
                        reloadTable('empleaverequeststableverified');
                        reloadTable('att_table');
                    }
                }, // post-submit callback
                error: function () {
                    $('#largeModalForm').modal('hide');
                    alert('Sorry for the inconvenience, please contact support');
                }

            };

        });
        function grandexpense(obj) {
            $(obj).html('<li class="fa fa-spinner fa-spin"></li>Please Wait').prop("disabled", "disabled");
            $('.btn').prop("disabled", "disabled");
            $('#expenseapproveform').ajaxSubmit(empexpenseoptions);
        }
        function rejectexpense(obj) {
            $(obj).html('<li class="fa fa-spinner fa-spin"></li>Please Wait').prop("disabled", "disabled");
            $('#reject').val(1);
            $('.btn').prop("disabled", "disabled");

            $('#expenseapproveform').ajaxSubmit(empexpenseoptions);
        }
        function cancelmodal() {
            $('#largeModalForm').modal('hide');
        }
//        function showimage() {
//            showSmallModalForm(livesite + 'ProjectExpenses/showimage/' + <?php echo $arr_expense['0']['emp_expense']['emp_expenses_pkey']; ?>);
//        }
    </script>
<?php } else { ?>
    <form class="form-vertical" id="expenseapproveform">
        <div class="modal-header" style="background:#004d77;color:white;">

            <h4 class="modal-title"><?php echo $arr_expense['0']['emp_expense']['expense_status']; ?></h4>
        </div> 

        <div class="modal-body">
           <input id="expensepkey" name="expensepkey" type="hidden"  value="<?php echo $arr_expense['0']['emp_expense']['emp_expenses_pkey']; ?>" >
            
            <div class="form-group" style="margin-bottom:30px;">
                <div class="col-md-6">
                    <div style="font-size: 25px; margin-top: -8px; " class="col-md-12"><?php echo $arr_att['0']['site']['site_name']; ?> - <span style="font-size: 20px;"><?php echo $arr_att['0']['site']['site_id']; ?></span></div>
               </div>
<!--                <div class="col-md-6">
                    <div style="text-align:left;" class="col-md-5 control-label" >Branch</div>   
                    <label style="text-align:left;" class="col-md-1">:</label>
                    <span style="padding-left: 15px;"><?php echo $arr_expense['0']['employee_info']['branch']; ?></span>
                </div>
                <div class="col-md-6">
                    <div style="text-align:left;" class="col-md-5 control-label" >Applied Date</div>
                    <label style="text-align:left;" class="col-md-1">:</label>
                    <span style="padding-left: 15px;"><?php echo date('Y-m-d', strtotime($arr_expense['0']['emp_expense']['created_date'])); ?></span>
                </div>-->
            </div>
            <hr style="padding-right: 1px; padding-top: 1px; margin-bottom: -3px;">
            <div class="form-group">
                <div class="row">
                    <div class="col-md-12">
                        <br>
                        <div class="col-md-6">

                            <label style="text-align:left;" class="col-md-5 control-label" >Request ID </label>
                            <label style="text-align:left;" class="col-md-1">:</label>
                            <span style="text-align:left;" class="col-md-6"><?php echo $arr_att['0']['emp_expense']['expense_id']; ?></span>

                        </div>
                        <div class="col-md-6">

                            <label style="text-align:left;" class="col-md-5 control-label" >Beneficiary</label>
                            <label style="text-align:left;" class="col-md-1">:</label>
                            <span style="text-align:left;" class="col-md-6">
                                <?php echo $arr_att['0']['beneficiary']['company_name']; ?>
                            </span>

                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <div class="col-md-12" style="margin-top: -30px;">
                        <br>
                        <div class="col-md-6">

                            <label style="text-align:left;" class="col-md-5 control-label" >Expense Date</label>
                            <label style="text-align:left;" class="col-md-1">:</label>
                            <span style="text-align:left;" class="col-md-6"><?php echo $arr_expense['0']['emp_expense']['expense_date']; ?></span>
                        </div>
                        <div class="col-md-6">

                            <label style="text-align:left;" class="col-md-5 control-label" >Approved By</label>
                            <label style="text-align:left;" class="col-md-1">:</label>
                            <span style="text-align:left;" class="col-md-6"><?php echo isset($apr_person)?$apr_person[0]["employee_info"]["EmpName"]:$apr_by_admin; ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <div class="col-md-12" style="margin-top: -30px;">
                        <br>
                    
                        <div class="col-md-6">

                            <label style="text-align:left;" class="col-md-5 control-label" >Purpose</label>
                            <label style="text-align:left;" class="col-md-1">:</label>
                            <span style="text-align:left; word-wrap: break-word;" class="col-md-6"><?php echo $arr_expense['0']['emp_expense']['purpose']; ?></span>
                        </div>
                        <div class="col-md-6">

                            <label style="text-align:left;" class="col-md-5 control-label" >Remarks</label>
                            <label style="text-align:left;" class="col-md-1">:</label>
                            <span style="text-align:left;" class="col-md-6"><?php echo $arr_expense['0']['emp_expense']['remarks']; ?></span>
                        </div> 
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <div class="col-md-12" style="margin-top: -30px;">
                        <br>
                        
                        <div class="col-md-6">
                            <label style="text-align:left;" class="col-md-5 control-label" for="REMARKS">Remarks By Approved Person</label>
                            <label style="text-align:left;" class="col-md-1">:</label>


                            <div class="col-md-5">
                                <textarea id="REMARKS" rows="2" cols="20" name="REMARKS" type="text" placeholder="Remarks" style=" width: 195px;" readonly="" wrap="hard" class="form-control input-md" ><?php echo $arr_expense['0']['emp_expense']['remarks_auth']; ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <div class="col-md-12" style="margin-top: -30px;margin-bottom: -18px;    text-align: right;">
                        <br>
<?php if($arr_expense['0']['emp_expense_details']['expense_details_pkey'] !=''){ ?>
<button type="button" id="btn-img" class="btn btn-info" onclick="showimage(<?php echo $arr_expense['0']['emp_expense_details']['expense_details_pkey'];?>)"><?php echo "Show Image"; ?></button>
<?php } ?>
                        <button type="button" id="btn-grandemployeeleave" class="btn btn-danger"  data-dismiss="modal"><?php echo "Cancel"; ?></button>
                        
<!--                        <button type="button" id="btn-grandemployeeleave" class="btn btn-info"  onclick="showimage()"><?php //echo "Show Image"; ?></button>-->


                    </div>
                </div>
            </div>
        </div>
    </form>
    <script>
        function showimage(id) {
            showSmallModalForm(livesite + 'ProjectExpenses/showimage/' + id);
        }  
//        function showimage() {
//            showSmallModalForm(livesite + 'ProjectExpenses/showimage/' + <?php echo $arr_expense['0']['emp_expense']['emp_expenses_pkey']; ?>);
//        }
    </script>
<?php } ?>
    