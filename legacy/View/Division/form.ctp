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
            <h4 class="modal-title">Division</h4>  
        </div>
        <div class="modal-body">
            <form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>Division/savedivision" id="divForm">
                <div class="modal-body">
                    <input id="id" name="id" type="hidden"  value="<?php echo $data["id"]; ?>" >
                    <!-- Text input-->


                    <!-- Text input-->
                    <!--//edited by sinsiya -->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="div_code">Division Code<span class="star">*</span></label>  
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <?php if ($data["div_code"] == '') { ?>
                                <input id="div_code" name="div_code"  value="<?php echo $data["div_code"]; ?>" type="text" maxlength="10"   placeholder="Division Code" class="form-control input-md" required="">
                            <?php } else { ?>
                                <input  name="div_code"  value="<?php echo isset($data["div_code"]) ? $data["div_code"] : ''; ?>" type="hidden"  maxlength="10"  placeholder="Division Code" class="form-control input-md" >
                                <input value="<?php echo isset($data["div_code"]) ? $data["div_code"] : ''; ?>" type="text" placeholder="Division Code" class="form-control input-md" disabled>
                            <?php } ?>
                           <p class="warning-message" id="warningMessage"></p> 
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="div_name">Division Name<span class="star">*</span></label>  
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <input id="div_name" name="div_name"  value="<?php echo $data["div_name"]; ?>" type="text" placeholder="Division Name" class="form-control input-md" required="">
                        </div>
                    </div>
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


    $('#div_name').on('change', function () {
        checkIfDivisionExists();
    })

    function checkIfDivisionExists(callback) {
        var div_name = $('#div_name').val();
        var id = $('#id').val();
        $.ajax({
            url: 'Division/checkdivisionexists/' + id,
            type: 'POST',
            data: {
                div_name: div_name
            },
            success: function (resp)
            {
                if (resp > 0) {
                    alert("Division Already Exists!!");
                    $('#div_name').val('');
                } else {
                    if (typeof callback === 'function') {
                        callback.call();
                    }
                }
            }
        });
    }


    $('#div_code').on('change', function () {
        checkIfDivisioncodeExists();
    })

    function checkIfDivisioncodeExists(callback) {
        var div_code = $('#div_code').val();
        var id = $('#id').val();
        $.ajax({
            url: 'Division/checkdivisioncodeexists/' + id,
            type: 'POST',
            data: {
                div_code: div_code
            },
            success: function (resp)
            {
                if (resp > 0) {
                    alert("Division Code Already Exists!!");
                    $('#div_code').val('');
                } else {
                    if (typeof callback === 'function') {
                        callback.call();
                    }
                }
            }
        });
    }





    $(document).ready(function () {
        $('#divForm').parsley();
        var options = {
            success: function (responseText, statusText, xhr, $form) {

                var response = JSON.parse(responseText);
                // alert(response.success);
                if (response.success) {
                    closeModal('dvtable');
                    closeModal('deForm');
                    $.notify("Division Saved Successfully", {
                        type: 'success',
                        allow_dismiss: false
                    });
                }
            }
        };

        // bind to the form's submit event 
        $('#divForm').submit(function () {
            // inside event callbacks 'this' is the DOM element so we first 
            // wrap it in a jQuery object and then invoke ajaxSubmit 
            $(this).ajaxSubmit(options);

            // !!! Important !!! 
            // always return false to prevent standard browser submit and page navigation 
            return false;
        });
    });



</script>