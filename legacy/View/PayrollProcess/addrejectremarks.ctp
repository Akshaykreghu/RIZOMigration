<style>
    .form-horizontal .control-label {
        text-align: left;
        /*padding-left: 76px;*/
    }
</style>

<div id="remove1" class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header" style="background: #00659f;color: white">
            <h4 class="modal-title">Update Remarks</h4>
        </div>
        <div class="modal-body">
            <!-- Form starts -->
            <form class="form-horizontal" id="noticeForm">
                <div class="modal-body">
                    <div class="form-group">
                        <div class="col-md-12">
                            <label class="col-md-4 control-label" for="description">Remarks<span class="star">*</span></label>
                            <div class="col-md-1">:</div>
                            <div class="col-md-7">
                                <input id="remarks" name="remarks" type="text" placeholder="Remarks" class="form-control input-md" required="">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    <!-- Change the button type to "button" and add an onclick event -->
                    <button type="button" class="btn btn-primary" id="save">Save</button>
                </div>
            </form>
            <!-- form ends-->
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#noticeForm').parsley();

        // Get the PHP variables into JavaScript
        var payrollPkey = <?php echo json_encode($payroll_pkey); ?>;
        var status = <?php echo json_encode($status); ?>;

        // Handle form submission using JavaScript
        $('#save').click(function() {
            var remarks = $('#remarks').val();

            // Check if the Remarks field is empty
            if (remarks.trim() === '') {
                alert('Remarks cannot be empty!');
                return; // Prevent further execution of the function
            }

            // AJAX submit the form data
            $.ajax({
                type: 'POST',
                url: livesite + 'PayrollProcess/rejectPayrollEntry/' + payrollPkey + '/' + status, 
                data: $('#noticeForm').serialize(),
                success: function(response) {
                    var responseData = JSON.parse(response);
                    console.log('responseData',responseData);
                    if (responseData.success) {
                        closeModal('dpttable');
                        $("#smallModalForm").modal('hide');
                        $('#payrolltable').datagrid('reload');
                    }
                }
            });

            // Prevent the default form submission
            return false;
        });
    });
</script>