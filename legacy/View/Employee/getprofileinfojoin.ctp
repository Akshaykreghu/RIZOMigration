<style>
    .form-items {
        margin-top: 12px;
        min-height: 60px;
    }

    h4 {
        margin-left: -10px;
        margin-top: 28px;
        font-weight: bold;
        color: #08659f;
    }
</style>
<!-- edited by bindu 17-11-2025 -->
<form id="submitImportData" method="POST" action="<?php echo $this->webroot; ?>Employee/importEmployeetoEmpjoin">
    <!-- edited by bindu 17-11-2025 end -->
    <h4 style="margin-top: 0; ">Employee Details Informations</h4>

    <!-- <?php $employeeDetails = get_object_vars($arr_reponse->employeeInfo->employeeDetails); ?> -->
    <?php if (!empty($arr_reponse)) { ?>
        <!-- <div class="row">

        
        <?php foreach ($employeeDetails as $key => $value) { ?>
            <div class="col-md-4 form-items">
                <label style="padding: 0; " for="" class="col-md-12"><?= $key; ?></label>
                <p><?= $value ? $value : 'No Data'; ?></p>
                <input type="hidden" value="<?= $value; ?>" name="employeeDetails[<?= $key; ?>]" />
            </div>
        <?php } ?>

    </div>

    <input type="hidden" value="<?= $branchCode; ?>" name="branchCode" /> -->
        <input type="hidden" value="<?= $profileID; ?>" name="profileID" />

        <div class="col-md-4 form-items">
            <label style="padding: 0; " for="" class="col-md-12">User Name</label>
            <p><?= $employeeDetails['firstName']; ?> - <?= $employeeDetails['lastName']; ?></p>
        </div>

        <div class="col-md-4 form-items">
            <label style="padding: 0; " for="" class="col-md-12">Email</label>
            <p><?= $employeeDetails['email']; ?></p>
        </div>

        <div class="col-md-4 form-items">
            <label style="padding: 0; " for="" class="col-md-12">DOB</label>
            <p><?= $employeeDetails['dateOfBirth']; ?></p>
        </div>

        <!-- <h4>Qualifications</h4>
    <div class="row">
        
            <table class="table">
                <thead>
                    <th>Course</th>
                    <th>Field Of Study</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>University</th>
                    <th>Marks</th>
                </thead>
                <tbody>
                <?php foreach ($arr_reponse->employeeInfo->qualification as $key => $value) { ?>
                    <tr>
                        <?php $value = get_object_vars($value); ?>
                        <td><?= $value['course']; ?></td>
                        <td><?= $value['fieldOfStudy']; ?></td>
                        <td><?= $value['fromDate']; ?></td>
                        <td><?= $value['toDate']; ?></td>
                        <td><?= $value['university']; ?></td>
                        <td><?= $value['mark']; ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <div class="col-md-4 ">

            </div>
        

    </div>

    <h4>Documents</h4>
    <div class="row">

        <table class="table">
            <thead>
                <th>Document Type</th>
                <th>Document Number</th>
                <th>Classification</th>
                <th>Name</th>
                <th>Relation</th>
                <th>Remarks</th>
            </thead>
            <tbody>
            <?php foreach ($arr_reponse->employeeInfo->passportVisa as $key => $value) { ?>
                <?php $value = get_object_vars($value); ?>
                <tr>
                    <td><?= $value['documentType']; ?></td>
                    <td><?= $value['documentNumber']; ?></td>
                    <td><?= $value['classification']; ?></td>
                    <td><?= $value['name']; ?></td>
                    <td><?= $value['relation']; ?></td>
                    <td><?= $value['remarks']; ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>

    </div>

    <h4>Family</h4>
    <div class="row">
        
        <table class="table">
            <thead>
                <th>Name</th>
                <th>Date Of Birth</th>
                <th>Gender</th>
                <th>Blood Group</th>
                <th>Relation</th>
                <th>Is Nominee</th>
                <th>Contact Number</th>
                <th>Remarks</th>
            </thead>
            <tbody>
            <?php foreach ($arr_reponse->employeeInfo->family as $key => $value) { ?>
                <tr>
                    <?php $value = get_object_vars($value); ?>
                    <td><?= $value['name']; ?></td>
                    <td><?= $value['dateOfBirth']; ?></td>
                    <td><?= $value['gender']; ?></td>
                    <td><?= $value['bloodGroup']; ?></td>
                    <td><?= $value['relation']; ?></td>
                    <td><?= $value['isNominee']; ?></td>
                    <td><?= $value['contactNumber']; ?></td>
                    <td><?= $value['remarks']; ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div> -->

        <div>
            <button class="btn btn-primary pull-right">Confirm & Import</button>
        </div>

    <?php } else { ?>
        <div class="alert alert-warning">
            No Employee found with this ProfileID
        </div>
    <?php } ?>


</form>

<script>
   var options = {
    success: function(resp) {
        resp = JSON.parse(resp);

        if (resp.success === false) {
     
            $.notify({
                message: resp.error
            }, {
                type: 'danger',
                delay: 3000,
                z_index: 9999
            });

            $('#modalForm').modal('hide');

            return; 
        }

  
        $.notify({
            message: resp.message
        }, {
            type: 'success',
            delay: 3000,
            z_index: 9999
        });
        $('#modalForm').modal('hide');
        reloadTable('emptable');
    }
};



    $('#submitImportData').on('submit', function(event) {
        event.preventDefault();
        if (confirm(" Do You Want  To Save The Form")) {
            $('#submitImportData').ajaxSubmit(options)
        }
    });
</script>