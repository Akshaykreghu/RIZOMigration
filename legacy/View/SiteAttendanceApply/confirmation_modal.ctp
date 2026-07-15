<form class="form-horizontal" method="post" id="confirmation" action="<?php echo $this->webroot; ?>SiteAttendanceApply/saveApproved">
    <input id="sma_pkey1" name="sma_pkey" type="hidden" value="<?php echo $sma_pkey; ?>">
    <input id="site_pkey1" name="site_pkey" type="hidden" value="<?php echo $site_pkey; ?>">

    <div class="modal-header" style="background-color: #00659f; color: white;">
        <h4 class="modal-title" id="exampleModalLabel">Confirmation</h4>
        <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button> -->
    </div>

    <div class="modal-body text-center">
        <?php if (trim($action) == 'Approve') { ?>
            <p>Are you sure you want to approve the changes?</p>
        <?php } elseif(trim($action) == 'Reject'){?>
            <p>Are you sure you want to reject the changes?</p>
            <?php }?>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-danger" onclick="$('#modalForm').modal('hide');">Cancel</button>
        <input type="hidden" name="approve" id="approveInput" value="true">

        <?php if (trim($action) == 'Approve') { ?>
            <input type="hidden" name="remarks" value="Approved by Admin">
            <button type="submit" id="btn-approve1" class="btn btn-primary">&nbsp;&nbsp;Yes&nbsp;&nbsp;</button>
        <?php } elseif (trim($action) == 'Reject') { ?>
            <input type="hidden" name="remarks" value="Rejected by Admin">
            <button type="submit" id="btn-reject1" class="btn btn-primary">&nbsp;&nbsp;Yes&nbsp;&nbsp;</button>
        <?php } ?>
    </div>
</form>





<script>
    $(document).ready(function() {

        // Get the hidden input field
        const approveInput = $("#approveInput");

        // Add click event listeners to the buttons
        $("#btn-approve1").click(function() {
            approveInput.val("true"); // Update the hidden input value to "true" (for Approve)
        });

        $("#btn-reject1").click(function() {
            approveInput.val("false"); // Update the hidden input value to "false" (for Reject)
        });

        // Attach form submission handling using the jQuery Form Plugin
        $('#confirmation').on('submit', function(event) {
            event.preventDefault(); // Prevent the default form submission behavior

            // Get the form
            const form = $(this);

            // Submit the form using AJAX
            form.ajaxSubmit({
                success: function(response) {
                    console.log('Response', response);
                    $('#modalForm').modal('hide');
                    $('#att_table').datagrid('reload');

                    // Handle your notifications and UI updates here
                    // ...
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        });
    });
</script>