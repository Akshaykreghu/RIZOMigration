<section>
    <div class="row">
<!--        <div class="modal-dialog">-->

            <!-- Modal content-->
<!--            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"> Project Expense Details</h4>  
                </div>
                <div class="modal-body">-->
                   
<!--                    <div class="row">-->
                        <div class="col-md-12">
                            <div class="modal-header">
                            <h4>Project Details 
                            <div class="pull-right"><?php if($exp_details['0']['emp_expense']['expense_status'] != 'Rejected') { ?><button onclick="edit_detail(<?php echo $exp_details['0']['emp_expense']['emp_expenses_pkey'];?>)" class="btn btn-info">
                            <li><span style="padding-left:1px;" class="fa fa-pencil">&nbsp;Edit</span></li>
                            </button> &nbsp;  <?php } ?> <button onclick="downloadReport();" class="btn btn-danger">
                            <li class="fa fa-file-pdf-o"><span style="padding-left:1px;" class="fa fa-cloud-download"></span></li>
                            </button> &nbsp; <button onclick="downloadexcelReport();" class="btn btn-success">
                            <li class="fa fa-file-excel-o"><span style="padding-left:1px;" class="fa fa-cloud-download"></span></li></button>
                            </div> </h4> 
                            </div>
                            <div style="padding:20px;">
                            <div class="col-md-6"><b>Request ID : </b><?php echo $exp_details['0']['emp_expense']['expense_id']; ?></div>
                            <div class="col-md-6"><b>Project Name : </b><?php echo $exp_details['0']['site']['site_name'].'-'.$exp_details['0']['site']['site_id']; ?></div>
                            <div class="col-md-6"><b>Beneficiary : </b><?php echo $exp_details['0']["beneficiary"]["company_name"]; ?></div>
                            <div class="col-md-6"><b>Bill Date : </b><?php echo date('d-m-Y',strtotime($exp_details['0']['emp_expense']['expense_date'])); ?></div>
                            <div class="col-md-6"><b>GST Bill No. : </b><?php echo $exp_details['0']['emp_expense']['gst_bill_no']; ?></div>
                            <div class="col-md-6"><b>Employee : </b><?php echo $exp_details['0']['emp_details']['first_name'].' '.$exp_details['0']['emp_details']['last_name']; ?></div>
                            <div class="col-md-6"><b>Remarks : </b><?php echo $exp_details['0']['emp_expense']['remarks']; ?></div>
                            <div class="col-md-6" style="margin-bottom:20px;"><b>GST Bill Status : </b><?php echo $exp_details['0']["emp_expense"]["gst_bill_status"]; ?></div><br><br>
                           <input type="hidden" value="<?php echo isset($exp_details['0']['emp_expense_details']['emp_expense_fkey'])?$exp_details['0']['emp_expense_details']['emp_expense_fkey']:'0'; ?>" id="exp_pkey">
                            <h4 style="text-align:center;">Expense Details </h4>
                            <table class="table table-responsive table-bordered">
                                <tbody id="allocating">
                                    <tr>
                                        <th>Sl.No</th>
<!--                                        <th>Employee</th>-->
                                        <th>Expense</th>
                                        <th>Category</th>
<!--                                        <th>Date</th>-->
                                        <th>Amount</th>
                                        <th>CGST</th>
                                        <th>SGST</th>
                                        <th>IGST</th>
                                        <th>Total</th>
<!--                                        <th>GST Bill No.</th>
                                        <th>GST Bill Status</th>-->
                                        <th>Payment</th>
                                        <th>Balance</th>
<!--                                        <th>Payment Status</th>
                                        <th>Related Party</th>-->
                                        <th>Image</th>
                                        <?php $status = isset($exp_details['0']['emp_expense']['expense_status'])?$exp_details['0']['emp_expense']['expense_status']:'';
                                        if($status != 'Rejected') { ?>
                                        <th>Action</th>
                                        <?php } ?>
                                    </tr>
                                    <?php  $i = 1; $cgst_total = 0; $sgst_total = 0; $igst_total = 0; $amount = 0; $total = 0; $payment =0; $balance = 0;
                                    foreach ($exp_details as $val) {
                                        ?>
                                        <tr>
                                            <td><?php echo $i; ?></td>
                                            <?php if($val['emp_expense_details']['return_status'] == 0){$st = "";}else{$st = " - Return";} ?>
<!--                                        <td><?php echo $val['emp_details']['first_name'].'-'.$val['emp_details']['last_name']; ?></td>-->
                                            <td><?php echo $val['expense_type']['expense_type_name'].$st;?></td>
                                            <td><?php echo $val['expense_item']['category']; ?></td>
<!--                                        <td><?php echo date('d-m-Y',strtotime($val['emp_expense_details']['exp_date'])); ?></td>-->
                                            <td><?php echo $val['emp_expense_details']['exp_amount']; ?></td>
                                            <td><?php echo $val['emp_expense_details']['cgst']; ?></td>
                                            <td><?php echo $val['emp_expense_details']['sgst']; ?></td>
                                            <td><?php echo $val['emp_expense_details']['igst']; ?></td>
                                            <td><?php echo $val['emp_expense_details']['total']; ?></td>
<!--                                        <td><?php echo $val['emp_expense_details']['gst_bill_no']; ?></td>
                                            <td><?php echo $val['emp_expense_details']['gst_bill_status']; ?></td>-->
                                            <td><?php echo $val['emp_expense_details']['payment']; ?></td>
                                            <td><?php echo $val['emp_expense_details']['balance']; ?></td>
<!--                                        <td><?php echo $val['emp_expense_details']['payment_status']; ?></td>
                                            <td><?php echo $val['emp_expense_details']['related_party']; ?></td>-->
                                            <td><?php if($val['emp_expense_details']['return_status'] == 0) { ?><button type="button" id="btn-grandemployeeleave" class="btn btn-info" onclick="showimage(<?php echo $val['emp_expense_details']['expense_details_pkey'];?>)"><?php echo "Show Image"; ?></button><?php } ?></td>
                                            <?php if($exp_details['0']['emp_expense']['expense_status'] != 'Rejected' && $val['emp_expense_details']['return_status'] == 0) { ?> 
                                            <td><button type="button" id="btn-edit" class="btn btn-info" onclick="edit(<?php echo $val['emp_expense_details']['expense_details_pkey'];?>)">Edit</button></td>
                                            <?php }else{?><td></td><?php } ?>
                                        </tr>
                                    <?php $i++; 
                                    $cgst_total = $cgst_total + $val['emp_expense_details']['cgst'];
                                    $sgst_total = $sgst_total + $val['emp_expense_details']['sgst'];
                                    $igst_total = $igst_total + $val['emp_expense_details']['igst'];
                                    $amount = $amount + $val['emp_expense_details']['exp_amount'];
                                    $total = $total + $val['emp_expense_details']['total'];
                                    $payment = $payment + $val['emp_expense_details']['payment'];
                                    $balance = $balance+ $val['emp_expense_details']['balance'];
                                            } ?>
                                        <tr><th colspan="3"  style="text-align: right;">Grand Total</th>
                                            <th><?php echo $amount;?></th>
                                            <th><?php echo $cgst_total;?></th>
                                            <th><?php echo $sgst_total;?></th>
                                            <th><?php echo $igst_total;?></th>
                                            <th><?php echo $total;?></th>
                                            <th><?php echo $payment;?></th>
                                            <th><?php echo $balance;?></th>
                                            <td></td>
                                               <?php if($exp_details['0']['emp_expense']['expense_status'] != 'Rejected') { ?>
                                               <td></td>
                                               <?php } ?>
                                                  <?php //$payment = isset($exp_details['0']['emp_expense']['payment'])?$exp_details['0']['emp_expense']['payment']:'0';?> 
                                        </tr>
                                </tbody>
                             </table>
                            <div class="col-md-10" style="text-align: right;"><b>Total : </b></div><div class="col-md-2">Rs. <?php echo $total; ?></div>
                            <div class="col-md-10" style="text-align: right;"><b>Amount Paid : </b></div><div class="col-md-2">Rs. <?php echo $payment;?></div>
                            <div class="col-md-10" style="text-align: right;"><b>Balance : </b></div><div class="col-md-2">Rs. <?php echo $total - $payment; ?></div>
                            <?php if($exp_details['0']["emp_expense"]["payment_status"]){ ?>
                            <div class="col-md-10" style="text-align: right;"><b>Payment Status : </b></div><div class="col-md-2"> <?php echo $exp_details['0']["emp_expense"]["payment_status"]; ?></div>
                            <?php } ?>
                            </div>
                        </div>
                    </div>
<!--                </div> 
            </div>
        </div>
    </div>-->
</section>
<div class="row">
    <div class="form-group">
        <form id="form-showreport" method="post" action="" ></form>
    </div>
</div>
<script>
 function showimage(id) {
            showSmallModalForm(livesite + 'ProjectExpenses/showimage/' + id);
        }   
 function edit(id){
     showSmallModalForm(livesite + 'ProjectExpenses/editexpense/' + id);
 }  
  function edit_detail(id){
     showSmallModalForm(livesite + 'ProjectExpenses/editexpensepayment/' + id);
 } 
function downloadReport(){
    var mode= $('#exp_pkey').val();
    if(mode != ''){
        $('#form-showreport').attr('action',livesite+'ProjectExpenses/downloads/'+mode);
        $('#form-showreport').submit();
    }
    else{
        return false;
    }
}
function downloadexcelReport(){
    var mode= $('#exp_pkey').val();
    if(mode != ''){
        $('#form-showreport').attr('action',livesite+'ProjectExpenses/downloadexcels/'+mode);
        $('#form-showreport').submit();
    }
    else{
        return false;
    }
}
</script>