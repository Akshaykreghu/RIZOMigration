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
            <h4 class="modal-title">Section</h4>  
        </div>
        <div class="modal-body">
            <form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>Section/savesection" id="sectForm">
                <div class="modal-body">
                    <input id="id" name="id" type="hidden"  value="<?php echo $data["id"]; ?>" >
                    <!-- Text input-->


                    <!-- Text input-->
                    <!--//edited by sinsiya on 29/11/2023 Section code change hided-->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="sect_code">Section Code<span class="star">*</span></label>  
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <?php if ($data["section_code"] == '') { ?>
                                <input id="sect_code" name="sect_code"  value="<?php echo $data["section_code"]; ?>" type="text"  maxlength="10" placeholder="Section Code" class="form-control input-md" required="">
                            <?php } else { ?>
                                <input  name="sect_code"  value="<?php echo isset($data["section_code"]) ? $data["section_code"] : ''; ?>" type="hidden" maxlength="10"  placeholder="Section Code" class="form-control input-md" >
                                <input value="<?php echo isset($data["section_code"]) ? $data["section_code"] : ''; ?>" type="text" placeholder="Section Code" class="form-control input-md" disabled>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="sect_name">Section Name<span class="star">*</span></label>  
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <input id="sect_name" name="sect_name"  value="<?php echo $data["section_name"]; ?>" type="text" placeholder="Section Name" class="form-control input-md" required="">
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


    $('#sect_name').on('change', function () {
        checkIfSectionExists();
    })

    function checkIfSectionExists(callback) {
        var sect_name = $('#sect_name').val();
        var id = $('#id').val();
        $.ajax({
            url: 'Section/checksectionexists/' + id,
            type: 'POST',
            data: {
                section_name: sect_name
            },
            success: function (resp)
            {
                if (resp > 0) {
                    alert("Section Already Exists!!");
                    $('#sect_name').val('');
                } else {
                    if (typeof callback === 'function') {
                        callback.call();
                    }
                }
            }
        });
    }


    $('#sect_code').on('change', function () {
        checkIfSectioncodeExists();
    })

    function checkIfSectioncodeExists(callback) {
        var sect_code = $('#sect_code').val();
        var id = $('#id').val();
        $.ajax({
            url: 'Section/checksectioncodeexists/' + id,
            type: 'POST',
            data: {
                section_code: sect_code
            },
            success: function (resp)
            {
                if (resp > 0) {
                    alert("Section Code Already Exists!!");
                    $('#sect_code').val('');
                } else {
                    if (typeof callback === 'function') {
                        callback.call();
                    }
                }
            }
        });
    }





    $(document).ready(function () {
        $('#sectForm').parsley();
        var options = {
            success: function (responseText, statusText, xhr, $form) {

                var response = JSON.parse(responseText);
                // alert(response.success);
                if (response.success) {
                    closeModal('sectable');
                    closeModal('stForm');
                    $.notify("Section Saved Successfully", {
                        type: 'success',
                        allow_dismiss: false
                    });
                }
            }
        };

        // bind to the form's submit event 
        $('#sectForm').submit(function () {
            // inside event callbacks 'this' is the DOM element so we first 
            // wrap it in a jQuery object and then invoke ajaxSubmit 
            $(this).ajaxSubmit(options);

            // !!! Important !!! 
            // always return false to prevent standard browser submit and page navigation 
            return false;
        });
    });
</script>