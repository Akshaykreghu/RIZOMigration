<style type="text/css">
    iframe {
    background-image: url("http://localhost/forsight/forsight/img/updateimg.gif");   
    background-repeat: no-repeat;
    background-position: 50% 50%;
}
</style>
<?php
if($status ==2 ){

?>
<script>
    var options = {
        success: function (resp) {
            var response =  $.parseJSON(resp);
            if (response.status == true){
                $('#largeModalForm').modal('hide');
                $('#tickets_table_to_be_approved').datagrid('reload');
                $('#tickets_table_approved').datagrid('reload');
                $.notify("Success", {
                    type: 'success',
                    allow_dismiss: false
                });
                action = '';    
            }
        }  
    };
    $('#ticket_form').on('submit', function (event) {
        event.preventDefault();
        
        if (confirm("Do You Want To Approve The Ticket")) {

            $('#ticket_form').ajaxSubmit(options);
        }    
     
        
    });
     
</script>
<?php
}
?>
<div class="modal-dialog" style="width: 100%; ">
    <div class="modal-content">
        <div class="modal-body">
            <div id="option-save-response" class="">
            <form class="form-horizontal"  method="post" action="<?php echo $this->webroot; ?>Survey/ticketApprove" id="ticket_form" name="ticket_form">
                    <div class="modal-body"> 
                        <div class="form-group form-group-sm">
                            <div class="PDF">
                                <iframe id="report_pdf" name="report_pdf" src="<?php echo $preview_url; ?>" width="100%" height="600">
                                </iframe>
   								
							</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                    	 <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                         <?php 
                            if($status ==2 ){
                         ?>
                         <button type="submit" id="btn-submit" class="btn btn-primary">Approve</button>
                            
                        <?php
                            }
                            else {
                         ?>
                            <a target="_tab" href="<?php echo $download_url; ?>" class="btn btn-primary" >Download</a>
                         <?php
                            }
                        ?>
                    </div>
                    <input  type="hidden" value='<?php echo isset($ticket_id) ? $ticket_id : ""; ?>' name="ticket_id" id="ticket_id" >
                </form>
            </div>
        </div>
    </div>
</div>