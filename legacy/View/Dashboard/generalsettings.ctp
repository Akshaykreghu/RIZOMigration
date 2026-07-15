<div class="modal-body" style="overflow-y: auto;">
    <legend><?php
//    echo $general_settings[0]['genaral_setings']['description'];
        echo $general_settings[0]['genaral_setings']['message'];
        ?></legend>
    <div class="row">
        <div class="col-md-12">
            <div class="box ">
                <div class="box-body">
                    <?php
                    //echo $general_settings[0]['genaral_setings']['message'];
//                    echo $general_settings[0]['genaral_setings']['description'];
                    ?>
                    <br>
                    <fieldset>
                        <table class="table table-bordered" id="punchLateInOut">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Relation</th>
                                    <th>Contact Number</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $settings_fkey = $general_settings[0]['genaral_setings']['settings_pkey'];
                                foreach ($family_details as $member) {
                                    $member_key = $member['emp_family']['emp_family_pkey'];
                                    ?>
                                    <tr>
                                        <td><?php echo $member['emp_family']['name']; ?></td>
                                        <td><?php echo $member['emp_family']['relation']; ?></td>
                                        <td><?php echo $member['emp_family']['contact_number']; ?><br><?php echo $member['emp_family']['alternate_number']; ?></td>
                                        <td>
                                            <?php
                                            if ($member['emp_family']['contact_number'] != null || $member['emp_family']['alternate_number'] != null) {
                                                ?>
                                                <button class="btn btn-primary" onclick="save(<?php echo $member_key; ?>,<?php echo $settings_fkey; ?>)">Set</button>
                                                <?php
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                        <br>
                        <button type="button" class="btn btn-danger"id="never_btn" onclick="$('#modalForm').modal('hide');">Never Remind me</button>
                        <button type="button" class="btn btn-default" id="later_btn" onclick="$('#modalForm').modal('hide');">Remind me later</button>
                    </fieldset>
                    <br>
                    <p style="color: red; text-align: center; font-size: 12px;">* Due to this situation need to update the emergency contact number in your profile settings.<br>If already done the family details then set the emergency number.</p>
                </div>
                <!-- /.box-body -->
                <?php
//                debug($general_settings);
//                debug($family_details);
                ?>
            </div>
        </div>
    </div>    

</div>
<script>
    $(document).ready(function () {
        $('#punchLateInOut').DataTable({
            "paging": false,
            "lengthChange": false,
            "searching": false,
//            dom: 'Bfrtip'
        });
        $('#never_btn').click(function () {
            $.ajax({
                url: livesite + 'dashboard/never_remind_emergency/' +<?php echo $general_settings[0]['genaral_setings']['settings_pkey']; ?>,
                success: function (responseText) {
                    var response = JSON.parse(responseText);
                    if (response.success === true) {
                        $.notify("Marked as never remaind successfully", {
                            type: 'success',
                            allow_dismiss: false
                        });
                    } else {
                        $.notify("Failed to mark", {
                            type: 'danger',
                            allow_dismiss: false
                        });
                    }
                }
            });
        });

        $('#later_btn').click(function () {
            $.ajax({
                url: livesite + 'dashboard/later_remind_emergency/' +<?php echo $general_settings[0]['genaral_setings']['settings_pkey']; ?>,
                success: function (responseText) {
                    var response = JSON.parse(responseText);
                    if (response.success === true) {
                        $.notify("Marked as remaind later successfully", {
                            type: 'success',
                            allow_dismiss: false
                        });
                    } else {
                        $.notify("Failed to mark", {
                            type: 'danger',
                            allow_dismiss: false
                        });
                    }
                }
            });
        });

    });
    function save(family_key, settings_key) {
        $.ajax({
            url: livesite + 'dashboard/save_emergency_contact/' + family_key + '/' + settings_key,
            type: "POST",
            success: function (responseText, statusText, xhr, $form) {
                var response = JSON.parse(responseText);
                if (response.success === true) {
                    $('#modalForm').modal('hide');
                    $.notify("Emergency contact saved successfully", {
                        type: 'success',
                        allow_dismiss: false
                    });
                }
            }
        });
    }
</script>
