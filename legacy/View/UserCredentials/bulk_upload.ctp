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
            <h4 class="modal-title">Bulk User Access</h4>  
        </div>
        <div class="modal-body">
            <!-- Form starts -->

            <form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>UserCredentials/saveBulkAccess" id="deptForm">
                <div class="modal-body">
                    <!-- Text input-->
                    
                    <div class="form-group">
                        <div class="col-md-12">
                            <label class="col-md-4 control-label" for="user_id">Branch</label>  
                            <div class="col-md-1">:</div>
                            <div class="col-md-7">
                                <select style="width: 100% ; " id="filterby_branch" name="filterby_branch" class="form-control" >
                                    <?php foreach ($arr_branches as $key => $value) { ?>                              
                                        <option  value="<?php echo $value['branch_code']; ?>"><?php echo $value['branch_name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Text input-->
                    <div class="form-group">
                        <div class="col-md-12">
                            <label class="col-md-4 control-label" for="user_id">Password</label>
                            <div class="col-md-1">:</div>
                            <div class="col-md-7">
                              <input type="password" class="form-control input-md" id="password" autocomplete="off" name="password" placeholder="Enter Password" required="true">
                              <a class="togglepassword" title="Show Password " style="position : absolute;top: 5px;right: 25px; " ><li class="fa fa-eye"></li></a>
                     
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="alert alert-danger showMessage" style="padding: 8px; margin: 10px; display: none; " role="alert">
                        
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-submit-access btn-primary">Save</button>
                </div>
            </form>
            <!-- form ends-->
        </div>

    </div>

</div>
<script type="text/javascript">
    $(document).ready(function () {
         $('.togglepassword').click(function () {
            var element = $(this).parent().closest('div').find('input');
            if ($(element).attr('type') == 'password') {
                $(element).attr('type', "text");
            } else {
                $(element).attr('type', "password");
            }
        });  
        $("#filterby_branch").select2();

        $("#dept_code").hide();
        $('#deptForm').parsley();
        var options = {
            success: function (responseText, statusText, xhr, $form) {
                
                var response = $.parseJSON(responseText);
                console.log("response", response);
                $('.btn-submit-access').html('Save');
                if(response.success == 1) {
                    $('#uaccess').datagrid('reload');
                    closeModal('dpttable');
                    $(".showMessage").html('').hide();
                    alert("Access Allocated Successfully. If you don't received the access mail you can login with your User ID and password done in bulk access.")
                } else {
                    $(".showMessage").html(response.msg).show();
                }
            }
        };
        // bind to the form's submit event 
        $('#deptForm').submit(function () {
            if ($('#Active').is(':checked') && $('#dept_code').val() == '') {
                alert("Password Can not be empty");
            }
            else
            {
                $('.btn-submit-access').html('<li class="fa fa-spinner fa-spin"></li> Saving Changes ... ');
                $(this).ajaxSubmit(options);
//                    $('#uaccess').datagrid('reload');
            }
            // inside event callbacks 'this' is the DOM element so we first 
            // wrap it in a jQuery object and then invoke ajaxSubmit 


            // !!! Important !!! 
            // always return false to prevent standard browser submit and page navigation 
            return false;
        });
    });
</script>