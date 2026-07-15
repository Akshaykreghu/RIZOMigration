<style>
    .form-horizontal .control-label {
        text-align: left;
        /*padding-left: 76px;*/
    }
</style>
<div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header" style="background: #00659f;color: white">
            <h4 class="modal-title">Designation</h4>  
        </div>
        <div class="modal-body">
            <form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>Designation/save" id="desgForm">
                <div class="modal-body">

                    <input id="id" name="id" type="hidden"  value="<?php echo $data["id"]; ?>" >
                    <!-- Text input-->   
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="desig_code">Designation Code<span class="star">*</span></label>  
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <input id="desig_code" name="desig_code"  value="<?php echo isset($data["desig_code"]) ? $data["desig_code"] : ''; ?>" type="text" placeholder="Designation Code" class="form-control input-md" required="">

                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="desig_name">Designation Name<span class="star">*</span></label>  
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <input id="desig_name" name="desig_name"  value="<?php echo isset($data["desig_name"]) ? $data["desig_name"] : ''; ?>" type="text" placeholder="Designation Name" class="form-control input-md" required="">

                        </div>
                    </div>

                    <!-- Text input-->

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">

    $('#desig_name').on('change', function () {
        checkIfDesignationExists();
    })

    function checkIfDesignationExists(callback) {
        var desig_name = $('#desig_name').val();
        var id = $('#id').val();
        $.ajax({
            url: 'Designation/checkdesignationexists/' + id,
            type: 'POST',
            data: {
                desig_name: desig_name
            },
            success: function (resp)
            {
                if (resp > 0) {
                    alert("Designation Already Exists!!");
                    $('#desig_name').val('');
                } else {
                    if (typeof callback === 'function') {
                        callback.call();
                    }
                }
            }
        });
    }



    $('#desig_code').on('change', function () {
        checkIfDesignationcodeExists();
    })

    function checkIfDesignationcodeExists(callback) {
        var desig_code = $('#desig_code').val();
        var id = $('#id').val();
        $.ajax({
            url: 'Designation/checkdesignationcodeexists/' + id,
            type: 'POST',
            data: {
                desig_code: desig_code
            },
            success: function (resp)
            {
                if (resp > 0) {
                    alert("Designation Code Already Exists!!");
                    $('#desig_code').val('');
                } else {
                    if (typeof callback === 'function') {
                        callback.call();
                    }
                }
            }
        });
    }







    $(document).ready(function () {
        $('#desgForm').parsley();
        var options = {
            success: function (responseText, statusText, xhr, $form) {

                var response = JSON.parse(responseText);
                // alert(response);
                if (response.success) {
                    closeModal('designation');
                      //edited by athira on 19-02-2025
                      $.notify("Designation Saved Successfully", {
                        type: 'success',
                        allow_dismiss: false
                    });
                    //end
                }
            }
        };
        // bind to the form's submit event 
        $('#desgForm').submit(function () {
            // inside event callbacks 'this' is the DOM element so we first 
            // wrap it in a jQuery object and then invoke ajaxSubmit 
            $(this).ajaxSubmit(options);

            // !!! Important !!! 
            // always return false to prevent standard browser submit and page navigation 
            return false;
        });
    });
</script>