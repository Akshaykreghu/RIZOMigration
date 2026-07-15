<style>
    .form-horizontal .control-label{

        text-align: left;

    }
</style>
<style type="text/css">
    body {
        line-height: 2em;
    }
    .block-container {
        width: 95%;
        padding: 20px;
        border: #000000 solid thin;
    }
    .sub-head {
        border-bottom: #000000 solid thin;
    }
    .row {
        height: 32px;
    }
    .col-md-4 {
        width: 33.33%;
        float: left;
    }
    .table {
        border: 1px solid #B2B2B2;
        width: 80%;
        max-width: 100%;
        margin-bottom: 20px;
        max-width: 80%;
        margin-bottom: 20px;
        background-color: transparent;
        border-spacing: 0;
        border-collapse: collapse;
    }
    .table td,th{
        margin-top:20px;
        text-align: left;
        padding: 8px;
        line-height: 1.42857143;
        vertical-align: top;
        border: 1px solid #B2B2B2;
    }
    thead{
        border: 1px solid #B2B2B2;
    }
    .row{
        margin-right: -15px;
        margin-left: 26px;
        display: table;
    }
    .col-md-12{
        width: 100%;
        float: left;
        position: relative;
        min-height: 1px;
        padding-right: 15px;
        padding-left: 15px;
    }
    .box{
        position: relative;
        border-radius: 3px;
        background: #ffffff;
        border-top: 3px solid #d2d6de;
        margin-top: 40px;
        width: 100%;
        box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    }
    /*.detail_table table{
        border: 0px;
    }*/
    .detail_table td, th {
        margin-top:10px;
        text-align: left;
        padding: 5px;
        /*line-height: 1.42857143;*/
        /*vertical-align: top;*/
        border: 0px solid #B2B2B2;
    }
</style>
<bookmark>
<h1 style="text-align:left; font-size: 2em;"> <?php echo $arr_loan_master['0']['EmployeeInfo']['EmpName']; ?>'s EMI Details</h1>
<hr>

<div class="row">
    <div class="col-md-12">
        <!-- DIRECT CHAT DANGER -->
        <div>

            <!-- <div class="col-md-12">
                <h3>Balance Amount: <?php echo ($balance_amount == null) ? $arr_loan_master['0']['EmployeeLoan']['loan_amount'] : $balance_amount; ?></h3>
            </div>
            <div class="col-md-12">
                <?php
                $completions = $total * 100 / $arr_loan_master['0']['EmployeeLoan']['loan_amount'];
                ?>
                <h3>Loan Completion<span class="pull-right"><?php echo $completions; ?>% Completed</span></h3>

            </div> -->
            <div class="">
                <div class="col-md-12" style="font-weight: bold;">
                    <!-- <div style="width:50%; "><label>Employee ID : <?php echo $arr_loan_master['0']['EmployeeInfo']['employee_id']; ?></label></div>
                    <div style="width:20%; "><label>Loan Amount: <?php echo $arr_loan_master['0']['EmployeeLoan']['loan_amount']; ?></label></div>
                    <div style="width:20%; "><label>Tenure: <?php echo $arr_loan_master['0']['EmployeeLoan']['tenure']; ?></label></div>
                    <div style="width:20%; "><label>Interest Rate: <?php echo ($arr_loan_master['0']['EmployeeLoan']['intrest_rate']!=0)?$arr_loan_master['0']['EmployeeLoan']['intrest_rate']:'0'; ?></label></div>
                    <div style="width:20%; "><label>Started On: <?php echo $arr_loan_master['0']['EmployeeLoan']['emi_start_month']; ?> </label></div>
                    <div style="width:20%; "><label>Branch: <?php echo $arr_loan_master['0']['EmployeeInfo']['branch']; ?> </label></div>
                    <div style="width:20%; "><label>Department: <?php echo $arr_loan_master['0']['EmployeeInfo']['department']; ?> </label></div>
                    <div style="width:20%; "><label>Designation: <?php echo $arr_loan_master['0']['EmployeeInfo']['designation']; ?> </label></div> -->
                    <table class="detail_table">
                        <tr>
                            <th>Started On</th>
                            <td style="width: 400px;">: <?php echo $arr_loan_master['0']['EmployeeLoan']['emi_start_month']; ?></td>
                            <th>Employee ID </th>
                            <td>: <?php echo $arr_loan_master['0']['EmployeeInfo']['employee_id']; ?></td>
                        </tr>
                        <tr>
                            <th>Loan Amount</th>
                            <td style="width: 400px;">: <?php echo $arr_loan_master['0']['EmployeeLoan']['loan_amount']; ?></td>
                            <th>Department</th>
                            <td>: <?php echo $arr_loan_master['0']['EmployeeInfo']['department']; ?></td>
                        </tr>
                        <tr>
                            <th>Tenure</th>
                            <td style="width: 400px;">: <?php echo $arr_loan_master['0']['EmployeeLoan']['tenure']; ?></td>
                            <th>Designation</th>
                            <td>: <?php echo $arr_loan_master['0']['EmployeeInfo']['designation']; ?></td>
                        </tr>
                        <tr>
                            <th>Interest Rate</th>
                            <td style="width: 400px;">: <?php echo ($arr_loan_master['0']['EmployeeLoan']['intrest_rate']!=0)?$arr_loan_master['0']['EmployeeLoan']['intrest_rate']:'0'; ?></td>
                            <th>Branch</th>
                            <td>: <?php echo $arr_loan_master['0']['EmployeeInfo']['branch']; ?></td>
                        </tr>
                    </table>
                </div>
                <table style="margin-top:20px;" class="table">
                    <thead>
                        <tr style="border: 1px solid black; font-weight: bold;">
                            <td>Sl No</td>
                            <td>Month</td>
                            <!-- <th>Creation Date</th> -->
                            <td>Opening Balance</td>
                            <td>EMI</td>
                            <!-- <th>Interest</th> -->
                            <!-- <th>Principal</th> -->
                            <td>Paid Amount</td>
                            <td>Closing Balance</td>
                            <td>Monthly Status</td>
                            <td>Remarks</td>
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
                                    <td><?php echo $loan['EmployeeLoanInfo']['loan_month']; ?></td>
                                    <td><?php echo $opening_balance; ?></td>
                                    <td><?php echo $loan['EmployeeLoanInfo']['loan_emi']; ?></td>
                                    <!-- <td><?php echo $loan['EmployeeLoanInfo']['interest']; ?></td> -->
                                    <!-- <td><?php echo $loan['EmployeeLoanInfo']['principle']; ?></td> -->
                                    <td><?php echo $loan['EmployeeLoanInfo']['amount_paid']; ?></td>
                                    <td><?php echo $closing_balance; ?></td>
                                    <td><?php echo $loan['EmployeeLoanInfo']['remarks']; ?></td>
                                    <td><?php echo $loan['EmployeeLoanInfo']['user_remarks']; ?></td>
                                </tr>
                                <?php $opening_balance=$closing_balance; ?>
                                <?php 
                            }
                             if($closing_balance ==0){
                                     $closing_balance = 0;
                                      break;
                                        }
                        }//This is the closing of foreach loop ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>
</bookmark>