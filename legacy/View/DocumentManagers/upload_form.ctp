<div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
        <!-- <div class="modal-header">
            <h4 class="modal-title">Upload File</h4>
        </div> -->
        <div class="modal-header" style="background: #00659f; color: white; display: flex; justify-content: space-between;">
                    <h4 class="modal-title" style="margin: 0;"> Upload File</h4>
                   
                </div>
        <div class="modal-body">
            <!-- Form starts -->
            <div class="container" style="width:100%;">
                <form id="uploadForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="pdfFile" style="display: inline-block; width: 150px;">Choose a File:</label>
                        <input type="file" name="pdfFile" id="pdfFile" accept=".pdf, image/*" required style="display: inline-block;">
                    </div>
                    <div class="form-group" style="text-align:right;">
                        <button type="button" name="cancel" id="cancelButton" class="btn btn-danger">Cancel</button>
                        <button type="button" name="save" id="submitBtn" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
            <!-- form ends-->
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $("#submitBtn").click(function() {
            var formData = new FormData($("#uploadForm")[0]);
            console.log('formDAta',$("#pdfFile").val());
            var inputFile = $("#pdfFile").val();
            if (inputFile === '') {
                $.notify({
                    message: "Please choose a file to Upload."
                }, {
                    type: 'danger'
                });
            } else {
                $.ajax({
                    type: "POST",
                    url: "<?php echo $this->webroot; ?>DocumentManagers/documentUpload",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        var result = JSON.parse(response);
                        if (result.status === 1) {
                            $.notify({
                                message: result.message
                            }, {
                                type: 'success'
                            });
                            // Optionally, you can perform additional actions on success.
                            $("#modalForm").modal("hide");
                            reloadTable('documents_manager');
                        } else {
                            $.notify({
                                message: result.message
                            }, {
                                type: 'danger'
                            });
                            // Optionally, handle the error case.
                        }
                    }
                });
            }
        });

        $("#cancelButton").click(function() {
            // Hide the modal when the Cancel button is clicked
            $("#modalForm").modal("hide");
        });
    });
</script>
