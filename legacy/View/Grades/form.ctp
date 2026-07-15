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
            <h4 class="modal-title">Grade</h4>  
        </div>
        <div class="modal-body">
            <form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>Grades/saveGrade" id="gradeForm">
                <div class="modal-body">
                    <input id="id" name="id" type="hidden"  value="<?php echo $data["grade_pkey"]; ?>" >
                    <div class="form-group">
                        <div class="col-md-12">
                            <!--The grade code cannot change when edit the grade. Because the all employee table fields are takes the grade code as its grade key. So we cannot change the grade code ---By Arul 10/10/2019 -->
                            <label class="col-md-4 control-label" for="grade_code">Grade Code<span class="star">*</span>&nbsp;&nbsp;</label>
                            <div class="col-md-1">:</div>
                            <div class="col-md-7">
                                <!--The grade code cannot change when edit the grade. Because the all employee table fields are takes the grade code as its grade key. So we cannot change the grade code ---By Arul 10/10/2019 -->
                                <?php
                                if ($data["grade_name"] != NULL || $data["grade_code"] != NULL) {
                                    ?>
                                    <input value="<?php echo $data["grade_code"]; ?>" type="text" placeholder="Grade Code" class="form-control input-md" disabled>
                                    <input id="grade_code" name="grade_code"  value="<?php echo $data["grade_code"]; ?>" type="hidden">

                                    <?php
                                } else {
                                    ?>
                                            <!-- <input id="grade_code" name="grade_code"  value="<?php echo $data["grade_code"]; ?>" type="text" placeholder="Grade Code" class="form-control input-md" required=""> -->
                                    <input id="grade_code" name="grade_code"  value="" type="text" placeholder="Grade Code" class="form-control input-md" required="">

                                    <?php
                                }
                                ?>

                            </div><!-- This closed division(div) opens in the if else conditions. So one of them is the open div tag. -->
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <label class="col-md-4 control-label" for="grade_name">Grade Name<span class="star">*</span></label>  
                            <div class="col-md-1">:</div>
                            <div class="col-md-7">
                                <input id="grade_name" name="grade_name"  value="<?php echo $data["grade_name"]; ?>" type="text" placeholder="Grade Name" class="form-control input-md" required="">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="save">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">


    $('#grade_name').on('change', function () {
        checkIfGradeExists();
    })
    //This function is used to check whether the entering grade name is already exist or not in grade table. 
    //BY ***ARUL P DAS on 27/11/2019
    function checkIfGradeExists(callback) {
        var grade_name = $('#grade_name').val();
        var id = $('#id').val();
        $.ajax({
            url: 'Grades/checkgradeexists/' + id,
            type: 'POST',
            data: {
                grade_name: grade_name
            },
            success: function (resp)
            {
                // alert(resp);
                if (resp > 0) {
                    alert("Grade Already Exists!!");
                    $('#grade_name').val('');
                } else {
                    if (typeof callback === 'function') {
                        callback.call();
                    }
                }
            }
        });
    }


    $('#grade_code').on('change', function () {
        checkIfGradecodeExists();
    })
    //This function is used to check whether the entering grade code is already exist or not in grade table. 
    //BY ***ARUL P DAS on 27/11/2019
    function checkIfGradecodeExists(callback) {
        var grade_code = $('#grade_code').val();
        var id = $('#id').val();
        $.ajax({
            url: 'Grades/checkgradecodeexists/' + id,
            type: 'POST',
            data: {
                grade_code: grade_code
            },
            success: function (resp)
            {
                if (resp > 0) {
                    alert("Grade Code Already Exists!!");
                    $('#grade_code').val('');
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
        $('#gradeForm').parsley();
        var options = {
            success: function (responseText, statusText, xhr, $form) {
                var response = JSON.parse(responseText);
                // alert(response.success);
                if (response.success) {
//                alert(response.msg); //Hided in 19/12/2019 by **ARUL P DAS
                    closeModal('dpttable');
                    $('#tbl_grade').datagrid('reload');
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
        $('#gradeForm').submit(function () {
            // inside event callbacks 'this' is the DOM element so we first 
            // wrap it in a jQuery object and then invoke ajaxSubmit 

            $(this).ajaxSubmit(options);

            // !!! Important !!! 
            // always return false to prevent standard browser submit and page navigation 
            return false;
        });
    });
</script>