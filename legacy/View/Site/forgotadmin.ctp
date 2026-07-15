<div class="form-body">
        <div class="website-logo">
            <a href="index.html">
                <div class="logo">
                    <img class="logo-size" src="images/logo-light.svg" alt="">
                </div>
            </a>
        </div>
        <div class="row">
            <div class="img-holder">
                <div class="bg"></div>
                <div class="info-holder">

                </div>
            </div>
            <div class="form-holder">
                <div class="form-content">
                    <div class="form-items">
                        <h3>Password Reset</h3>
<form class="form-horizontal" method="post"  >
                <div class="box-body">
                    <?php if (isset($msg)) { ?>
                        <div class="alert alert-danger alert-dismissable">
<!--                            <h4><i class="icon fa fa-ban"></i> Error!</h4>-->
                            <p><?php echo $msg;  ?></p>
                        </div>
                    <?php } ?> 
                    <div class="form-group">

                        <label for="password" class="col-sm-6 control-label" style="text-align:left;">New Password</label>
                   
                            <input type="password" class="form-control" data-validation="length" data-validation-length="min6" name="password" id="password" placeholder="New Password">
                       
                    </div>
                    <div class="form-group">
                        <label for="password_confirm" class="col-sm-6 control-label" style="text-align:left;">Confirm Password</label>
                       
                            <input type="password" data-validation-error-msg="Password is not matching" data-validation="confirmation" data-validation-confirm="password" class="form-control" id="password_confirm" name="password_confirm" placeholder="Confirm Password">
                     
                    </div>


                </div><!-- /.box-body -->
                <div class="form-button ">
                   <a  href="<?php echo $this->webroot; ?>" class="ibtn">Cancel</a>
                    <button type="submit" class="ibtn pull-right">Reset Password</button>
                </div><!-- /.box-footer -->
             
            </form>
                    </div>
                    <div class="form-sent">
                        <div class="tick-holder">
                            <div class="tick-icon"></div>
                        </div>
                        <h3>Password Reset Successful</h3>
                        <p>Please check your Email</p>
                        <a href="<?php echo $this->webroot; ?>">Login to account</a>
                    </div>
                </div>
            </div>
        </div>
    </div> 