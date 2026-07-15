<style>
    .form-horizontal .control-label {
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
</style>
<div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header" style="background: #00659f; color: white; ">
            <h4 class="modal-title">Import Profile</h4>  
        </div>
        <div class="modal-body">
            <!-- Form starts -->
            <div class="callout callout-info" style="background-color: #fff !important; ">
                <h5 style="color: #000; font-weight: 800; ">Please Enter the Profile ID of Employee you want to import!</h5>
                <p style="color: #000; ">You can get the Profile ID from the MyProfile application, after registration.</p>
            </div>

            <form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>Employee/fetchInfo" id="deptForm">
                <div class="modal-body">
                    <!-- Text input-->
             
                    <div class="form-group">
                        <div class="col-md-12">
                            <label class="col-md-2 control-label" for="profile_id">Profile ID *</label>
                            <div class="col-md-10">
                                <input required="required" id="profile_id" name="attr2"  value="" type="text" placeholder="Profile ID" class="form-control input-md" >
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-12">
                            <label class="col-md-2 control-label" for="profile_id">Email *</label>
                            <div class="col-md-10">
                                <input required="required" id="email" name="email"  value="" type="text" placeholder="Email ID" class="form-control input-md" >
                            </div>
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
        var email = $("#email").val();

        if(EmpProfileID ) {
            $("#loadProfileData").load(livesite + 'Employee/sendRequestOTP/' + EmpProfileID + '/' + email);
        }

        // $.ajax({
        //     url: livesite + 'Employee/getProfileID/' + EmpProfileID,
        //     success: function (resp) {

        //     }
        // });

    }

    function getEmployeeInfo() {
        var EmpProfileID = $("#profile_id").val();
        var branch = $("#branch_filter").val();

        if(EmpProfileID && branch  ) {
            $("#loadProfileData").load(livesite + 'Employee/getprofileinfo/' + EmpProfileID + '/' + branch);
        }

        // $.ajax({
        //     url: livesite + 'Employee/getProfileID/' + EmpProfileID,
        //     success: function (resp) {

        //     }
        // });

    }

    jQuery(document).ready(function () {

        $("#branch_filter").select2();
        
    });
</script>