<!--<style>
    .form-horizontal .control-label{

        text-align: left;

    }
</style>-->
<section class="content-header">
    <h1 style="text-align:left; font-size: 2em;"> <?php echo $arr_loan_master['0']['EmployeeInfo']['EmpName']; ?>'s Loan Details <div class="pull-right"><button onclick="downloadReport();" class="btn btn-danger"><li class="fa fa-file-pdf-o"><span style="padding-left:1px;" class="fa fa-cloud-download"></span></li></button> &nbsp; <button onclick="downloadexcelReport();" class="btn btn-success"><li class="fa fa-file-excel-o"><span style="padding-left:1px;" class="fa fa-cloud-download"></span></li></button></div></h1>
</section>
<!-- Main content -->
<input type="hidden" value="<?php echo isset($arr_loan_master['0']['EmployeeLoan']['emp_loan_pkey'])?$arr_loan_master['0']['EmployeeLoan']['emp_loan_pkey']:'0'; ?>" id="loan_pkey">
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- DIRECT CHAT DANGER -->
            <div class="box ">
                <table class="table ">
                    <thead>
                            <tr>
                                <th>Employee Name</th>
                                <th>Loan Amount</th>
                                <th>Tenure</th>
                                <th>Interest Rate</th>
                                <th>Allocated Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <td><?php echo $arr_loan_master['0']['EmployeeInfo']['EmpName']; ?></td>
                            <td><?php echo $arr_loan_master['0']['EmployeeLoan']['loan_amount']; ?></td>
                            <td><?php echo $arr_loan_master['0']['EmployeeLoan']['tenure']; ?></td>
                            <td><?php echo ($arr_loan_master['0']['EmployeeLoan']['intrest_rate']!=0)?$arr_loan_master['0']['EmployeeLoan']['intrest_rate']:'0'; ?></td>
                            <td><?php echo date('m-Y', strtotime($arr_loan_master['0']['EmployeeLoan']['emi_start_month'])); ?></td>
                       </tbody>
                </table>
                <div class="col-md-12">
                    <!--edited by ASHIN 06-07-24-->                   
                    <h5>Remarks: <?php echo $arr_loan_master['0']['EmployeeLoan']['remarks']; ?></h5>
                    <!-- <h3>Balance Amount: <?php echo ($balance_amount == null)?$arr_loan_master['0']['EmployeeLoan']['loan_amount']:$balance_amount; ?></h3> -->
                    <h3>Amount To be Paid: <?php echo ($balance_amount == null ||$balance_amount<=12)?'0':$balance_amount; ?></h3>
                </div>
                <div class="col-md-12">
                    <?php
                    if($arr_loan_master['0']['EmployeeLoan']['loan_amount']!=0){
                        $completions = ceil(($total/$arr_loan_master['0']['EmployeeLoan']['loan_amount'])*100);
                    }else{
                        $completions = ceil(0);
                    }
                    if($completions>100 || $balance_amount<=12){$completions=100;}
                    ?>
                    <h3>Loan Completion<span class="pull-right"><?php echo $completions; ?>% Completed</span></h3>
<!--                    <div class="progress">

                        <div class="progress-bar progress-bar-green" role="progressbar" aria-valuenow="<?php echo $completions; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $completions; ?>%">
                            <span class="sr-only"><?php echo $completions; ?>% Completed</span>
                        </div>
                    </div>-->
                    <div class="progress progress-xs" style="background:#ff7979; ">
                        <div class="progress-bar progress-bar-warning progress-bar-striped" role="progressbar" aria-valuenow="<?php echo $completions; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $completions; ?>%">
                            <span class="sr-only"><?php echo $completions; ?>% Completed</span>
                        </div>
                    </div>
                </div>
                <div class="box-body">
                    <table class="table ">
                        <thead>
                            <tr>
                                <th>Sl No  </th>
                                <th style="width: 70px;">Month</th>
                                <!-- <th>Creation Date</th> -->
                                <th>Opening Balance</th>
                                <th>EMI</th>
                                <!-- <th>Interest</th> -->
                                <!-- <th>Principal</th> -->
                                <th>Paid Amount</th>
                                <th>Closing Balance</th>
                                <th>Monthly Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <!-- Calculating opening and closing balances in each month. Deducts the amount_paid from the opening balance amount and makes it as closing balance. Atlast makes the opening balance as the closing balance in each iteration.
                    *********ARUL P DAS on 06/11/2019********* -->
                        <tbody>
                            <?php $i = 0;
                            $opening_balance=0;$closing_balance=0; ?>
                            <?php 
                            foreach($arr_loan_data as $loan) { ?>
                            <?php //$i+=1; ?>
                            <?php
                                if($opening_balance==0){$opening_balance=$arr_loan_master[0]['EmployeeLoan']['loan_amount'];}
                                $closing_balance=$opening_balance-$loan['EmployeeLoanInfo']['amount_paid'];

                                if($loan['EmployeeLoanInfo']['loan_emi']=='0' && ($loan['EmployeeLoanInfo']['paid_status']=='A' || $loan['EmployeeLoanInfo']['paid_status']=='P')){
                                //This is to avoid to showing removed emi fields by cause of adding additional payment. By *** ARUL P DAS on 16/11/2019
                                }else{
                                    $i+=1; 
                                    ?>
                                    <tr>
                                    <td><?php echo $i; ?></td>
                                    <td><?php echo date('m-Y', strtotime($loan['EmployeeLoanInfo']['loan_month'])); ?></td>
                                    <td><?php echo $opening_balance; ?></td>
                                    <td><?php echo $loan['EmployeeLoanInfo']['loan_emi']; ?></td>
                                    <!-- <td><?php echo $loan['EmployeeLoanInfo']['interest']; ?></td> -->
                                    <!-- <td><?php echo $loan['EmployeeLoanInfo']['principle']; ?></td> -->
                                    <td><?php echo $loan['EmployeeLoanInfo']['amount_paid']; ?></td>
                                    <td><?php if($closing_balance<=0){echo '0';}else{echo $closing_balance;} ?></td>
                                    <td><?php echo $loan['EmployeeLoanInfo']['remarks']; ?></td>
                                    <td><?php echo isset($loan['EmployeeLoanInfo']['user_remarks'])?$loan['EmployeeLoanInfo']['user_remarks']:''; ?></td>
                                    </tr>
                                    <?php $opening_balance=$closing_balance; ?>
                                    <?php 
                                }
                            }//This is the closing of foreach loop ?>
                        </tbody>
                    </table>
                    
                </div>
            </div>
        </div>
    </div>
</section>
<div class="row">
    <div class="form-group">
        <form id="form-showreport" method="post" action="" ></form>
<!--        <div class="col-md-12" align="right">
            <a href="#" class="btn btn-default" onclick="downloadReport();" ><i class="icon-file"></i>Download As PDF</a>
            <a href="#" class="btn btn-default" onclick="downloadexcelReport();"><i class="icon-file"></i>Download As Excel</a>
        </div>-->
    </div>
</div>

<script>
    
    
  
function downloadReport(type,mode){
    var mode= $('#loan_pkey').val();
    if(mode != ''){
        $('#form-showreport').attr('action',livesite+'EmployeeLoan/downloads/'+mode);
        $('#form-showreport').submit();
    }
    else{
        return false;
    }
}
function downloadexcelReport(type,mode){
    var mode= $('#loan_pkey').val();
    if(mode != ''){
        $('#form-showreport').attr('action',livesite+'EmployeeLoan/downloadexcels/'+mode);
        $('#form-showreport').submit();
    }
    else{
        return false;
    }
}
</script>
<!--<style type="text/css">
    .modal-content {
        width: 750px; left: -80px;
    }
</style>-->