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
                    <!-- Update the Save button type to "button" and add an onclick attribute to call the function -->
                    <button type="button" class="btn btn-primary" id="save" onclick="processPayrollWithRemarks();">Save</button>
                </div>
            </form>
            <!-- form ends-->
        </div>
    </div>
</div>
<script type="text/javascript">


    //After the form submission, the succes or failed message returns to the below function..
    //BY ***ARUL P DAS on 27/11/2019
    $(document).ready(function () {
        $('#noticeForm').parsley();
        var options = {
            success: function (responseText, statusText, xhr, $form) {
                var response = JSON.parse(responseText);
                // alert(response.success);
                if (response.success) {
                    //                alert(response.msg); //Hided in 19/12/2019 by **ARUL P DAS
                    closeModal('dpttable');
 $("#smallModalForm").modal('hide');
                    $('#tbl_notice').datagrid('reload');
                }
                if (response.success == false) {
                    if ($('#id').val() != '') {
                        // alert(response.msg+$('#id').val());
                        closeModal('dpttable');
                    }
                }
            }
        };
        // bind to the form's submit event 
        $('#noticeForm').submit(function () {
            // inside event callbacks 'this' is the DOM element so we first 
            // wrap it in a jQuery object and then invoke ajaxSubmit 

            $(this).ajaxSubmit(options);

            // !!! Important !!! 
            // always return false to prevent standard browser submit and page navigation 
            return false;
        });
    });

            //Form submission
            function processPayrollWithRemarks() {
        var remarks = $('#remarks').val();
                // Check if the Remarks field is empty
        if (remarks.trim() === '') {
            alert('Remarks cannot be empty!');
            return; // Prevent further execution of the function
        }
closeModal('addreversalremarks');
 $("#smallModalForm").modal('hide');
        // Call the processPayroll() function in showprovisionalpayroll.ctp and pass the remarks to it
        parent.removePayrollEntry(remarks);
        $('#remove1').modal('hide');
    }
</script>