<style>
.form-horizontal-import .control-label {
        text-align: left;
        /*padding-left: 76px;*/
    }

    .callout {
        border-radius: 0.25rem;
        box-shadow: 0 1px 3px rgb(0 0 0 / 12%), 0 1px 2px rgb(0 0 0 / 4%);
        background-color: #fff;
        border-left: 5px solid #e9ecef;
        margin-bottom: 1rem;
        padding: 8px 10px;
    }

    /* Flex row for ID & Email */
    .flex-inputs {
        display: flex;
        gap: 40px;
        align-items: flex-start;
    }

    .flex-inputs .form-group {
        flex: 1;
    }

    .form-horizontal-import {
        border: 1px solid #dfe6e9;
        border-radius: 12px;
        /* looks cleaner than 20% */
        padding: 10px;
        /* add some spacing */

        height: 180px;
        margin: 10px;
    }
    #import-body{
height: 450px;
    }
</style>
<div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header" style="background: #00659f; color: white; ">
            <h4 class="modal-title" style="color:white;font-weight:normal;">Import Profile</h4>
        </div>
        <div class="modal-body" id="import-body">
            <!-- Form starts -->
            <div>
                <p><span style="color:red;margin:10px">*</span>You can get the Profile ID from the MyProfile application, after registration.</p>
            </div>


            <form class="form-horizontal-import" method="post" action="<?php echo $this->webroot; ?>Employee/fetchInfo" id="deptForm">
                <div class="modal-body">
                    <!-- Text input-->

                    <!-- Flex Row -->
                    <div class="flex-inputs">
                        <!-- Profile ID -->
                        <div class="form-group">
                            <label class="control-label" for="profile_id">Profile ID *</label>
                            <input required id="profile_id" name="attr2" type="text" placeholder="Profile ID" class="form-control">
                        </div>

                        <!-- Email -->
                        <div class="form-group">
                            <label class="control-label" for="email">Email *</label>
                            <input required id="email" name="email" type="email" placeholder="Email ID" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="col-md-12">
                                <button type="button" onclick="requestPermissionAccess();" class="btn btn-primary btn-sucees pull-right">Request Access Informations</button>
                            </div>
                        </div>
                    </div>

                    <!-- <div class="form-group">
                        <div class="col-md-12">
                            <label class="col-md-4 control-label" for="profile_id">Branch *</label>
                            <div class="col-md-1">:</div>
                            <div class="col-md-7">
                                <select id="branch_filter" name="import_emp_branch" class="form-control js-example-basic-single" style="width: 100%; " >
                                    <option value="">--Select--</option>
                                    <?php
                                    foreach ($arr_branches as $key => $value) {
                                        echo '<option value="' . $value['branch_code'] . '">' . $value['branch_name'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div> -->

                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="alert alert-danger showMessage" style="padding: 8px; margin: 10px; display: none; " role="alert">

                            </div>
                        </div>
                    </div>

                </div>
                <!-- <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-sucees">Save</button>
                </div> -->
            </form>
            <!-- form ends-->

            <div class="card">
                <div class="card content" id="loadProfileData">

                </div>
            </div>


        </div>

    </div>

</div>
<script type="text/javascript">
   function requestPermissionAccess() {
    var EmpProfileID = $("#profile_id").val();
    // var email = $("#email").val();
    var email = $("#deptForm #email").val();

    // IF email is empty, stop the process
    if (!email) {
        alert("Please enter Email");
        return;
    }

    if (EmpProfileID) {
        // Encode email to safely pass in URL
        var encodedEmail = encodeURIComponent(email);

        // Load into right section
        $("#loadProfileData").load(
            livesite + 'Employee/sendRequestOTP/' + EmpProfileID + '/' + encodedEmail
        );
    }
}


    function getEmployeeInfo() {
        var EmpProfileID = $("#profile_id").val();
        var branch = $("#branch_filter").val();

        if (EmpProfileID && branch) {
            $("#loadProfileData").load(livesite + 'Employee/getprofileinfo/' + EmpProfileID + '/' + branch);
        }

        // $.ajax({
        //     url: livesite + 'Employee/getProfileID/' + EmpProfileID,
        //     success: function (resp) {

        //     }
        // });

    }

    jQuery(document).ready(function() {

        $("#branch_filter").select2();

    });
</script>