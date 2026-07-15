<style>
    .form-horizontal .control-label{

        text-align: left;

    }
</style>
<section class="content-header">
    <h1 style="text-align:left; " class="text-primary-18"> Employee Loan </h1>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- DIRECT CHAT DANGER -->
            <div class="box ">
                <input type="hidden" value="<?php echo $emp_pkey; ?>" id="emp_pkey">
                <div class="box-body">
                    <select class="form-control" style="width:220px;" onchange="loadloan(this);">
                        <option>Select a Loan</option>
                        <?php foreach ($arr_loan_master as $val) { ?>
                            <option value="<?php echo $val['EmployeeLoan']['emp_loan_pkey']; ?>">Loan Of - <?php echo $val['EmployeeLoan']['loan_amount']." - ".$val['EmployeeLoan']['emi_start_month']; ?></option>
                        <?php } ?>
                    </select>
                    <div id="load"></div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
        function loadloan(ids){
            var loan_pkey = $(ids).val();
           $('#load').load(livesite + "EmployeeLoan/viewloan/"+loan_pkey);
        }
</script>