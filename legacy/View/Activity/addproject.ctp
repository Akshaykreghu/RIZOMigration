<style>
    .form-horizontal .control-label {
        text-align: left;
        /*padding-left: 76px;*/
    }

    input.form-control, textarea.form-control {
        border-radius: 4px !important;
        /* border: 1px solid #aaa; */
    }

    .parsley-errors-list {
        list-style: none;
        color: #f44336;
    }

    .showTransfers, .subTasks {
        display: none;
    }

    .showTransfers.show {
        display: block;
    }

    .durationList {
        margin: 0;
        padding: 5px;
        text-align: right;
        font-size: 20px;
        font-weight: bold;
        margin-top: -18px;
        color: #666;
    }


</style>
<div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content customized-contents">
        <div class="modal-header" style="background: #00659f;color: white">
            <h4 class="modal-title">Add New Project</h4>  
        </div>
        <div class="modal-body">
            <!-- Form starts -->

            <?php if($this->Session->read('emp_fkey')) {
                $isAdmin = false;
            } else {
                $isAdmin = true;
            } ?>

            <form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>Activity/save2" id="deptForm">
                <div class="modal-body" style="margin: 0 20px; ">
                    <!-- Text input-->

                    <div class="form-group">
                        <label class="control-label" for="user_id">Project Name</label>  
                        <div style="margin-top: 10px; ">
                            <input id="summary" name="activity_projects_head" value="<?= isset($editData['ActivityProjects']['activity_projects_head']) ? $editData['ActivityProjects']['activity_projects_head']: ''; ?>" type="text" placeholder="Enter Project Name" class="form-control input-md" required="true">
                        </div>
                    </div>

                    <input type="hidden" value="<?= isset($editData['ActivityProjects']['activity_projects_pkey']) ? $editData['ActivityProjects']['activity_projects_pkey']: ''; ?>" name="activity_projects_pkey" id="activity_projects_pkey" />
                    <input type="hidden" value="Admin" name="created_by" id="created_by" />

                    <div class="form-group">
                        <label class="control-label" for="user_id">Project Description</label>  
                        <div style="margin-top: 10px; ">
                            <input id="summary" name="activity_projects_desc" value="<?= isset($editData['ActivityProjects']['activity_projects_desc']) ? $editData['ActivityProjects']['activity_projects_desc']: ''; ?>" type="text" placeholder="Enter Project Description" class="form-control input-md" required="true">
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" style="font-weight: bold; min-width: 88px; " class="btn btn-danger" data-dismiss="modal">Close</button>
                    <button type="submit" style="font-weight: bold; min-width: 88px; "class="btn btn-submit-access btn-primary">Save</button>
                </div>
            </form>
            <!-- form ends-->
        </div>

    </div>

</div>
<script type="text/javascript">
    $(document).ready(function () {

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
                    
                    if(response.isSubTask) {
                        
                        if (confirm("Do you want to open the subtask just created?")) {
                            showModalForm(livesite + 'Activity/add?id=' + response.fkey);
                        }
                        
                    } else {
                        alert("Activity Saved Successfully!")
                    }

                } else {
                    $(".showMessage").html(response.msg).show();
                }
            }
        };
        // bind to the form's submit event 
        $('#deptForm').submit(function () {
            if ($('#start_time').val() > $('#end_time').val()) {
                alert("Start Time and End Time are not valid! ");
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