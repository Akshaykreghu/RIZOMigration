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
            <h4 class="modal-title">Category</h4>  
        </div>
        <div class="modal-body">
            <form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>Category/saveGrade" id="categoryForm">
                <div class="modal-body">
                    <input id="id" name="id" type="hidden"  value="<?php echo $data["category_pkey"]; ?>" >
                    <div class="form-group">
                        <div class="col-md-12">
                            <!--The category code cannot change when edit the category. Because the all employee table fields are takes the category code as its category key. So we cannot change the category code ---By Arul 10/10/2019 -->
                            <label class="col-md-4 control-label" for="category_code">Category Code<span class="star">*</span>&nbsp;&nbsp;</label>
                            <div class="col-md-1">:</div>
                            <div class="col-md-7">
                                <!--The category code cannot change when edit the category. Because the all employee table fields are takes the category code as its category key. So we cannot change the category code ---By Arul 10/10/2019 -->
                                <?php
                                if ($data["category_name"] != NULL || $data["category_code"] != NULL) {
                                    ?>
                                    <input value="<?php echo $data["category_code"]; ?>" type="text" placeholder="Category Code" class="form-control input-md" disabled>
                                    <input id="category_code" name="category_code"  value="<?php echo $data["category_code"]; ?>" type="hidden">

                                    <?php
                                } else {
                                    ?>
                                            <!-- <input id="category_code" name="category_code"  value="<?php echo $data["category_code"]; ?>" type="text" placeholder="Category Code" class="form-control input-md" required=""> -->
                                    <input id="category_code" name="category_code"  value="" type="text" placeholder="Category Code" class="form-control input-md" required="">

                                    <?php
                                }
                                ?>

                            </div><!-- This closed division(div) opens in the if else conditions. So one of them is the open div tag. -->
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <label class="col-md-4 control-label" for="category_name">Category Name<span class="star">*</span></label>  
                            <div class="col-md-1">:</div>
                            <div class="col-md-7">
                                <input id="category_name" name="category_name"  value="<?php echo $data["category_name"]; ?>" type="text" placeholder="Category Name" class="form-control input-md" required="">
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


    $('#category_name').on('change', function () {
        checkIfGradeExists();
    })
    //This function is used to check whether the entering category name is already exist or not in category table. 
    //BY ***ARUL P DAS on 27/11/2019
    function checkIfGradeExists(callback) {
        var category_name = $('#category_name').val();
        var id = $('#id').val();
        $.ajax({
            url: 'Category/checkcategoryexists/' + id,
            type: 'POST',
            data: {
                category_name: category_name
            },
            success: function (resp)
            {
                // alert(resp);
                if (resp > 0) {
                    alert("Category Already Exists!!");
                    $('#category_name').val('');
                } else {
                    if (typeof callback === 'function') {
                        callback.call();
                    }
                }
            }
        });
    }


    $('#category_code').on('change', function () {
        checkIfGradecodeExists();
    })
    //This function is used to check whether the entering category code is already exist or not in category table. 
    //BY ***ARUL P DAS on 27/11/2019
    function checkIfGradecodeExists(callback) {
        var category_code = $('#category_code').val();
        var id = $('#id').val();
        $.ajax({
            url: 'Category/checkcategorycodeexists/' + id,
            type: 'POST',
            data: {
                category_code: category_code
            },
            success: function (resp)
            {
                if (resp > 0) {
                    alert("Category Code Already Exists!!");
                    $('#category_code').val('');
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
        $('#categoryForm').parsley();
        var options = {
            success: function (responseText, statusText, xhr, $form) {
                var response = JSON.parse(responseText);
                // alert(response.success);
                if (response.success) {
//                alert(response.msg); //Hided in 19/12/2019 by **ARUL P DAS
                    closeModal('dpttable');
                    $('#tbl_category').datagrid('reload');
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
        $('#categoryForm').submit(function () {
            // inside event callbacks 'this' is the DOM element so we first 
            // wrap it in a jQuery object and then invoke ajaxSubmit 

            $(this).ajaxSubmit(options);

            // !!! Important !!! 
            // always return false to prevent standard browser submit and page navigation 
            return false;
        });
    });
</script>