
<div class="modal-body">
    <div class="row">
        <div class="col-md-12" style="">
            <?php
            //$image = isset($image['0']['emp_expense']['image']) ? $image['0']['emp_expense']['image'] : '';
             $image = isset($image['0']['emp_expense_details']['image']) ? $image['0']['emp_expense_details']['image'] : '';
//            $path="home/mypayrollmaster/public_html/expense/".$company_code."/";
             $path = "https://mypayrollmaster.online/project/" . strtolower($company_code) . "/";
           
            if ($image != '') {
                ?>
    <!--                <img src="<?php echo $this->webroot; ?><?php echo $path . $image; ?>">-->
                <img src="<?php echo $path . $image; ?>" class="img-responsive" >
            <?php } else {
                ?>
                <img src="<?php echo $this->webroot; ?>img/noimage.png" class="img-responsive">
            <?php }
            ?>

        </div>
    </div>

</div>
<div class="modal-footer">
    <button class="btn btn-primary" data-dismiss="modal">Cancel</button>
</div>


