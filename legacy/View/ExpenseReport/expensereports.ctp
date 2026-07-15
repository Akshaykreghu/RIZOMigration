<style>  
    .table , td, th,tr {
        border-style: solid;
        border-color: #d4d4de;
    }
    .modal-content {
        width: 125%   !important;
    }
</style>
<?php if( $mode == '' ){ ?>
<div class="modal-body" style=" padding-left:1%; padding-right:1%; padding-bottom:1%;">
    <?php  if (count($arr_summary_for_template) <= 0) { ?>
    <div style="font-size: 25px;text-align:center; background-color:#F7D3D2;">
        There is no data available</div>
    <?php } else {
    ?>
   <h2 align="center">Project Expense Report  <?php //echo $report_month; ?></h2>
    <div class="row">
        <div class="col-md-12">
            <div class=""  >
                 <div class="box-body" style="overflow-y:auto;">
                    <fieldset>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Sl. No</th>
                                    <th>Date </th>
                                    <th>Expense Head</th>
                                    <th>Expense Type</th>
                                    <th>Description</th>
                                    <th>Project Name</th>
                                    <th>Zone</th>
                                    <th>Employee Name</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $arr_data  = $arr_summary_for_template['summary']; ?>
                                <?php if(count($arr_data)>0){ $sum = 0;?>
                                <?php 
                                $i=1;
                                foreach($arr_data as $val){ $sum = $sum + $val['amount']; ?>
                                <tr> 
                                    <th><?php echo $i; ?></th>
                                    <td><?php echo $val['exp_date']; ?></td>
                                    <td><?php echo $val['expense_head_name']; ?></td>
                                    <td><?php echo $val['expense_type_name']; ?></td>
                                    <td><?php echo $val['purpose']; ?></td>
                                    <td><?php echo $val['site_name']; ?></td>
                                    <td><?php echo $val['branch']; ?></td>
                                    <td><?php echo $val['emp_name']; ?></td>
                                    <td><?php echo $val['amount']; ?></td>
                                </tr>
                                <?php $i++; } ?>
                                <tr><th  colspan="8">Total</th><th><?php echo $sum; ?></th></tr>
                                <?php }else{ ?>
                                <tr>
                                    <td colspan="9">No data found under this criteria.</td>
                                </tr>  
                                <?php } ?>
                            </tbody>
                        </table>
                    </fieldset>
                </div>
                <?php //} ?> <!-- /.box-body -->
            </div>
        </div>
    </div>  
</div>


<?php }
}else{ ?>
<?php //echo '<style>'.file_get_contents("css/pdfbootstrap.css").'</style>'; ?>
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
    table {
        border: 1px solid #f4f4f4;
        width: 80%;
        max-width: 80%;
        margin-bottom: 20px;
        background-color: transparent;
        border-spacing: 0;
        border-collapse: collapse;
    }
    td, th {
        text-align: left;
        padding: 8px;
        line-height: 1.42857143;
        vertical-align: top;
        border: 1px solid #B2B2B2;
    }
    .h3{
        margin-top: 20px;
        margin-bottom: 10px;
        font-weight: 500;
    }
</style>
<?php
echo $this->element('reportadminheader',array(
'title'=>'Project Expense Report '));
?>
<?php  if (count($arr_summary_for_template) <= 0) { ?>
    <div style="font-size: 25px;text-align:center; background-color:#F7D3D2;">
        There is no data available</div>
    <?php } else {
    ?>
<table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Sl. No</th>
                                    <th>Date </th>
                                    <th>Expense Head</th>
                                    <th>Expense Type</th>
                                    <th>Description</th>
                                    <th>Project Name</th>
                                    <th>Zone</th>
                                    <th>Employee Name</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $arr_data  = $arr_summary_for_template['summary']; ?>
                                <?php if(count($arr_data)>0){ $sum = 0;?>
                                <?php 
                                $i=1;
                                foreach($arr_data as $val){ $sum = $sum + $val['amount']; ?>
                                <tr> 
                                    <th><?php echo $i; ?></th>
                                    <td><?php echo $val['exp_date']; ?></td>
                                    <td><?php echo $val['expense_head_name']; ?></td>
                                    <td><?php echo $val['expense_type_name']; ?></td>
                                    <td><?php echo $val['purpose']; ?></td>
                                    <td><?php echo $val['site_name']; ?></td>
                                    <td><?php echo $val['branch']; ?></td>
                                    <td><?php echo $val['emp_name']; ?></td>
                                    <td><?php echo $val['amount']; ?></td>
                                </tr>
                                <?php $i++; } ?>
                                <tr><th  colspan="8">Total</th><th><?php echo $sum; ?></th></tr>
                                <?php }else{ ?>
                                <tr>
                                    <td colspan="9">No data found under this criteria.</td>
                                </tr>  
                                <?php } ?>
                            </tbody>
                        </table> <?php }  ?>
<?php }  ?> <!-- /.box-body -->
