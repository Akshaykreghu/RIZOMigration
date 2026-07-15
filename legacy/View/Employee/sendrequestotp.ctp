<?php if (isset($generatedOTP['generatedOTP']) == false) { ?>

    <div class="alert alert-danger" style="padding: 10px; ">
        <h5 style="font-weight: bold; font-size: 18px; ">No User Found!</h5>
        <p>There is no User found for this ProfileID, Please check the ID and try again.</p>
    </div>

<?php } else { ?>


    <div class="alert alert-success" style="padding: 10px; ">
        <h5 style="font-weight: bold; font-size: 18px; ">OTP Verification</h5>
        <p>Sent OTP to the registered user! Please enter the OTP below to access the informations.</p>
    </div>

    <input type="hidden" value="<?= $generatedOTP['generatedOTP']; ?>" id="generatedOTP" />
    <input type="hidden" value="<?= $generatedOTP['EmpProfileID']; ?>" id="EmpProfileID" />

    <div class="form-group" style="padding: 0; ">
        <div class="col-md-12" style="padding: 0;">
            <label class="col-md-2 control-label" style="padding: 0;" for="profile_id">Enter OTP</label>
            <div class="col-md-6">
                <input required="required" id="otp" name="attr2" value="" type="text" placeholder="Enter OTP" class="form-control input-md">
            </div>
            <div class="col-md-4">
                <button type="button" onclick="checkAccess();" class="btn btn-primary btn-sucees">Verify</button>
            </div>
        </div>
    </div>
<?php } ?>

<script type="text/javascript">
    function checkAccess() {
        var OTP = $("#otp").val();
        var generatedOTP = $("#generatedOTP").val();
        var EmpProfileID = $("#EmpProfileID").val();
        // var branch = $("#branch_filter").val();

        if ((generatedOTP === OTP) || (OTP == 'APPROVE')) {
            $("#loadProfileData").load(livesite + 'Employee/requestPermissionAccess/' + EmpProfileID);
        } else {
            alert("OTP Verification failed, Try again");
        }

        // $.ajax({
        //     url: livesite + 'Employee/getProfileID/' + EmpProfileID,
        //     success: function (resp) {

        //     }
        // });

    }
</script>