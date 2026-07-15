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
            <h4 class="modal-title">Notice Period</h4>  
        </div>
        <div class="modal-body">
            <!-- Form starts -->
            <form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>NoticePeriod/savenotice_period" id="noticeForm">
                <div class="modal-body">
                    <input id="id" name="id" type="hidden"  value="<?php echo $data["notice_pkey"]; ?>" >
                    <div class="form-group">
                        <div class="col-md-12">
                            <!--The grade code cannot change when edit the grade. Because the all employee table fields are takes the grade code as its grade key. So we cannot change the grade code ---By Arul 10/10/2019 -->
                            <label class="col-md-4 control-label" for="notice_days">Days<span class="star">*</span>&nbsp;&nbsp;</label>
                            <div class="col-md-1">:</div>
                            <div class="col-md-7">
                                <?php
                                if ($data["description"] != NULL || $data["notice_days"] != NULL) {
                                    ?>
                                    <input value="<?php echo $data["notice_days"]; ?>" type="text" placeholder="Days" class="form-control input-md" disabled>
                                    <input id="notice_days" name="notice_days"  value="<?php echo $data["notice_days"]; ?>" type="hidden">

                                    <?php
                                } else {
                                    ?>
                                    <input id="notice_days" name="notice_days"  value="" type="text" placeholder="Days" class="form-control input-md" required="">

                                    <?php
                                }
                                ?>

                            </div><!-- This closed division(div) opens in the if else conditions. So one of them is the open div tag. -->
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <label class="col-md-4 control-label" for="description">Description<span class="star">*</span></label>
                            <div class="col-md-1">:</div>
                            <div class="col-md-7">
                                <input id="description" name="description"  value="<?php echo $data["description"]; ?>" type="text" placeholder="Description" class="form-control input-md" required="">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="save">Save</button>
                </div>
            </form>
            <!-- form ends-->
        </div>

    </div>

</div>
<script type="text/javascript">


    $('#description').on('change', function () {
        checkIfDescExists();
    })
    //This function is used to check whether the entering grade name is already exist or not in grade table. 
    //BY ***ARUL P DAS on 27/11/2019
    function checkIfDescExists(callback) {
        var description = $('#description').val();
        var id = $('#id').val();
        $.ajax({
            url: 'NoticePeriod/checknotice_periodexists/' + id,
            type: 'POST',
            data: {
                description: description
            },
            success: function (resp)
            {
                // alert(resp);
                if (resp > 0) {
                    alert("Day Already Exists!!");
                    $('#description').val('');
                } else {
                    if (typeof callback === 'function') {
                        callback.call();
                    }
                }
            }
        });
    }


    $('#notice_days').on('change', function () {
        checkIfDayExists();
    })
    //This function is used to check whether the entering grade code is already exist or not in grade table. 
    //BY ***ARUL P DAS on 27/11/2019
    function checkIfDayExists(callback) {
        var notice_days = $('#notice_days').val();
        var id = $('#id').val();
        $.ajax({
            url: 'NoticePeriod/checknotice_periodcodeexists/' + id,
            type: 'POST',
            data: {
                notice_days: notice_days
            },
            success: function (resp)
            {
                if (resp > 0) {
                    alert("Day Already Exists!!");
                    $('#notice_days').val('');
                } else {
                    if (typeof callback === 'function') {
                        callback.call();
                    }
                }
            }
        });
    }
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
</script>