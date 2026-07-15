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
    .table tr,th{
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
    .balance th {
        margin-top:10px;
        text-align: left;
        padding: 5px;
        /*line-height: 1.42857143;*/
        /*vertical-align: top;*/
        border: 0px solid #B2B2B2;
    }
</style>
<bookmark>
    <div class="col-md-12">
    <h1 style="text-align:center; font-size: 2em;">Project Expenses</h1>
    </div>
<!--<h1 style="text-align:center; font-size: 2em;"> <?php echo $arr_data['0']['site']['site_name'].'-'.$arr_data['0']['site']['site_id']; ?>  </h1>-->
<hr>
<div class="row">
    <div style="font-weight: bold;">
<!--                    <h3>Basic Details </h3>-->
                    <div>Employee : <?php echo $arr_data['0']['emp_details']['first_name'].' '.$arr_data['0']['emp_details']['last_name']; ?></div><br>
                    <div>Beneficiary : <?php echo $arr_data['0']["beneficiary"]["company_name"]; ?></div><br>
                    <div>Remarks : <?php echo $arr_data['0']['emp_expense']['remarks']; ?></div><br>
                    
                    <h3 style="text-align:center;"> <?php echo $arr_data['0']['site']['site_name'].'-'.$arr_data['0']['site']['site_id']; ?></h3>
<!--                    <div>Purpose : <?php echo $arr_data['0']['emp_expense']['purpose']; ?></div><br>-->
                    <div>Request ID :<?php echo $arr_data['0']['emp_expense']['expense_id']; ?></div><br>
                    <div>Bill Date :<?php echo date('d-m-Y',strtotime($arr_data['0']['emp_expense']['expense_date'])); ?></div><br>
                    <div>GST Bill No. :<?php echo $arr_data['0']['emp_expense']['gst_bill_no']; ?></div><br>
                    <div>GST Bill Status :<?php echo $arr_data['0']["emp_expense"]["gst_bill_status"]; ?></div><br>
<!--                    <div>Total Expense : <?php echo $arr_data['0']['emp_expense']['expenses_amount']; ?></div><br>
                    <div>Payment : <?php echo $arr_data['0']['emp_expense']['payment']; ?></div><br>
                    <div>Balance : <?php echo $arr_data['0']['emp_expense']['balance']; ?></div><br>
                    <div>Payment Status : <?php echo $arr_data['0']['emp_expense']['payment_status']; ?></div><br>-->
                    
                    
                </div>
    <h3 style="text-align:center;">Expense Details </h3>
                <table style="margin-top:20px;width:1000px;" class="table">
                    <thead>
                        <tr style="border: 1px solid black; font-weight: bold;">
                                        <th style="width:10px;">Sl. No.</th>
<!--                                        <th>Employee</th>-->
                                        <th style="width:120px;">Expense</th>
                                        <th style="width:80px;">Category</th>
                                        <th style="width:100px;">Related Party</th>
<!--                                        <th>Date</th>-->
                                        <th style="width:60px;">Amount</th>
                                        <th style="width:40px;">CGST</th>
                                        <th style="width:40px;">SGST</th>
                                        <th style="width:40px;">IGST</th>
                                        <th style="width:60px;">Total</th>
<!--                                        <th>GST Bill No.</th>
                                        <th>GST Bill Status</th>-->
                                        <th style="width:60px;">Payment</th>
                                        <th style="width:60px;">Balance</th>
                                  <!--        <th>Payment Status</th>-->
                                        
                        </tr>
                    </thead>
                  <tbody>
                        <?php $i = 0; $cgst_total = 0; $sgst_total = 0; $igst_total = 0; $amount = 0; $total = 0; $payment =0; $balance = 0; ?>
                        <?php 
                        foreach($arr_data as $val) { ?>
                        <?php $i+=1; 
                                ?>
                               <tr>
                                            <td><?php echo $i; ?></td>
<!--                                            <td><?php echo $val['emp_details']['first_name'].'-'.$val['emp_details']['last_name']; ?></td>-->
                                            <td><?php echo $val['expense_type']['expense_type_name'];?></td>
                                            <td><?php echo $val['expense_item']['category']; ?></td>
                                            <td><?php echo $val['emp_expense_details']['related_party']; ?></td>
<!--                                            <td><?php echo $val['emp_expense_details']['exp_date']; ?></td> -->
                                            <td><?php echo $val['emp_expense_details']['exp_amount']; ?></td>
                                            <td><?php echo $val['emp_expense_details']['cgst']; ?></td>
                                            <td><?php echo $val['emp_expense_details']['sgst']; ?></td>
                                            <td><?php echo $val['emp_expense_details']['igst']; ?></td>
                                            <td><?php echo $val['emp_expense_details']['total']; ?></td>
<!--                                            <td><?php echo $val['emp_expense_details']['gst_bill_no']; ?></td>
                                            <td><?php echo $val['emp_expense_details']['gst_bill_status']; ?></td>-->
                                            <td><?php echo $val['emp_expense_details']['payment']; ?></td>
                                            <td><?php echo $val['emp_expense_details']['balance']; ?></td>
                                          <!--   <td><?php echo $val['emp_expense_details']['payment_status']; ?></td>-->
                                            
                                </tr>
                                <?php 
                                    $cgst_total = $cgst_total + $val['emp_expense_details']['cgst'];
                                    $sgst_total = $sgst_total + $val['emp_expense_details']['sgst'];
                                    $igst_total = $igst_total + $val['emp_expense_details']['igst'];
                                    $amount = $amount + $val['emp_expense_details']['exp_amount'];
                                    $total = $total + $val['emp_expense_details']['total'];
                                    $payment = $payment + $val['emp_expense_details']['payment'];
                                    $balance = $balance+ $val['emp_expense_details']['balance'];
                                            } ?>
                                <tr><th colspan="4" style="text-align: right;">Grand Total</th>
                                            <th><?php echo $amount;?></th>
                                            <th><?php echo $cgst_total;?></th>
                                            <th><?php echo $sgst_total;?></th>
                                            <th><?php echo $igst_total;?></th>
                                            <th><?php echo $total;?></th>
                                            <th><?php echo $payment;?></th>
                                            <th><?php echo $balance;?></th>
                                        </tr>
                    </tbody>
                </table>
                <table class="balance" style="width:1000px;">
				<tr >
					<th style="width:900px;text-align: right;"><span contenteditable>Total</span></th>
					<th><span data-prefix>Rs. </span><span><?php echo $total;?></span></th>
				</tr>
				<tr >
					<th style="width:900px;text-align: right;"><span contenteditable>Amount Paid</span></th>
					<th><span data-prefix>Rs. </span><span contenteditable><?php echo isset($payment)?$payment:'0'; ?></span></th>
				</tr>
				<tr > 
					<th style="width:900px;text-align: right;"><span contenteditable>Balance</span></th>
					<th><span data-prefix>Rs. </span><span><?php echo isset($balance)?$balance:$total;?></span></th>
				</tr>
                                <?php if($arr_data['0']["emp_expense"]["payment_status"]){ ?>
                                <tr > 
					<th style="width:900px;text-align: right;"><span contenteditable>Payment Status </span></th>
                                        <th><span data-prefix></span><span><?php echo $arr_data['0']["emp_expense"]["payment_status"]; ?></span></th>
				</tr>
                              <?php } ?>
			</table>
                   
            </div>
</bookmark>

			