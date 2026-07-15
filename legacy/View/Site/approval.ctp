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
                        <h3>Leave Approval Request </h3>
            <form class="form-horizontal" method="post">
                <div class="box-body">
                    <?php if (isset($msg)) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <p><?php echo $msg;  ?></p>
                        </div>
                    <?php } ?> 
                    <div class="form-group">
                        <label for="password" class="col-sm-6 control-label" style="text-align:left;">Enter Remarks</label>
                        <input type="remarks" class="form-control" data-validation="length" data-validation-length="min6" name="remarks" id="remarks" placeholder="Enter Remarks">
                    </div>
                    <div class="form-group">
                        <label for="action" class="col-sm-6 control-label" style="text-align:left;">Select Action</label>
<select class="form-control" data-validation="length" name="status" id="status">
<option type="text" value="Approved">Approve</option>
<option type="text" value="Rejected">Reject</option></select>
                          </div>
                </div><!-- /.box-body -->
                <div class="form-button ">
                    <a href="<?php echo $this->webroot; ?>" class="ibtn">Cancel</a>
                    <button type="submit" class="ibtn pull-right" onclick="sendmessage();">Submit</button>
                </div><!-- /.box-footer -->
            </form>
            </div>
                    <div class="form-sent">
                        <div class="tick-holder">
                            <div class="tick-icon"></div>
                        </div>
                        <h3>Submitted Successfully.</h3>
                        <a href="<?php echo $this->webroot; ?>">Login to account</a>
                    </div>
                </div>
            </div>
        </div>
    </div> 
   <script>
function sendmessage(){
var status = $('#status').val();
alert("Leave " + status + " Successfully.");
}
   </script>