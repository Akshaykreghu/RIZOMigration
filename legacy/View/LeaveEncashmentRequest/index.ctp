<section class="content-header">
    <h1 class="text-primary-18">My Leave Encashment Requests</h1>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- My Leave Requests -->
            <!-- DIRECT CHAT DANGER -->
            
            <div class="box" id="leave_load">
                
                
            </div><!--/.direct-chat -->
        </div><!-- /.col -->
        <div class="col-md-12">
            <!-- My Leave Requests -->
            <!-- DIRECT CHAT DANGER -->
            
            <div class="box" id="app_loader">
                
                
            </div><!--/.direct-chat -->
        </div><!-- /.col -->
    </div>
</section>
<script>
jQuery(document).ready(function() {
   $('#app_loader').load(livesite+'LeaveEncashmentRequest/form/');
   $('#leave_load').load(livesite+'LeaveEncashmentRequest/loadleave/');
});
</script>