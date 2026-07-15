<div class="modal-body">
    <div class="row">
        <div class="col-md-12" style="">
            <?php
            $image = isset($image['0']['emp_expense']['image']) ? $image['0']['emp_expense']['image'] : '';
            //            $path="home/mypayrollmaster/public_html/expense/".$company_code."/";
            $path = "https://v1.mypayrollmaster.online/expense/" . strtolower($company_code) . "/";
            //            echo $this->webroot.$path.$image;
            if ($image != '') {
                $ext = pathinfo($image, PATHINFO_EXTENSION);
            ?>
                <!--                <img src="<?php echo $this->webroot; ?><?php echo $path . $image; ?>">-->

                <?php
                // PDF view is added by Arul on 08-10-2022
                if ($ext == "pdf") {
                ?>
                    <embed src="<?php echo $path . $image; ?>" width="580px" height="500px" />
                <?php
                } else {
                ?>
                    <img src="<?php echo $path . $image; ?>">
                <?php
                }
                ?>
            <?php } else {
            ?>
                <img src="<?php echo $this->webroot; ?>img/noimage.png">
            <?php }
            ?>

        </div>
    </div>

</div>
<div class="modal-footer">
    <button class="btn btn-primary" data-dismiss="modal">Cancel</button>
</div>