<script>



</script>
<div class="modal-header" style="background: #00659f;color: white">
    <h4 class="modal-title">Site Approval</h4>
</div>

<form class="form-horizontal" method="post" id="familys" action="<?php echo $this->webroot; ?>SiteAttendanceApply/saveApproved">

    <input id="sma_pkey" name="sma_pkey" type="hidden" value="<?php echo $sma_pkey; ?>">
    <input id="site_pkey" name="site_pkey" type="hidden" value="<?php echo $site_pkey; ?>">
    <div class="col-xs-12">
        <div class="col-xs-12">

            <h3 style="text-align: center;">Edited Site Details</h3>
            <hr style="border-top: 1px solid #cec1c1; ">
            <div class="form-group">
                <div class="col-md-6 ">
                    <label style="text-align:left; border-right: 1px solid #b6b3b3;" class="col-md-12 control-label" for="designation">Current Configurations &nbsp;</label>

                </div>
                <div class="col-md-6">
                    <label style="text-align:left;" class="col-md-12 control-label" for="designation">New Configurations &nbsp;</label>
                </div>
            </div>

            <hr>

            <?php foreach ($arr_site_edited as $details) {

                $field_name = isset($details['sma']['fieldname']) ? $details['sma']['fieldname'] : '';
                $old_value =  isset($details['sma']['old_value']) ? $details['sma']['old_value'] : '';
                $new_value =  isset($details['sma']['new_value']) ? $details['sma']['new_value'] : '';

                switch (trim($field_name)) {
                    case 'site_name':
                        $field_name = 'Site Name';
                        break;
                    case 'site_id':
                        $field_name = 'Site Code';
                        break;
                    case 'latitude':
                        $field_name = 'Latitude';
                        break;
                    case 'longitude':
                        $field_name = 'Longitude';
                        break;
                    case 'address':
                        $field_name = 'Address';
                        break;
                    case 'branch_code':
                        $field_name = 'Branch';
                        break;
                    case 'payment_mode':
                        $field_name = 'Payment Mode';
                        break;
                    case 'user_pkey':
                        $field_name = 'Site Manager';
                        break;
                    case 'special_remarks':
                        $field_name = 'Remarks';
                        break;
                    case 'min_days_before':
                        $field_name = 'Minimum Days';
                        break;
                    case 'contact_name':
                        $field_name = 'Company Name';
                        break;
                    case 'customer_name':
                        $field_name = 'Contact Person Name';
                        break;
                    case 'customer_contact':
                        $field_name = 'Contact Number';
                        break;
                    default:
                        $field_name = '';
                        break;
                }
                if (trim($field_name) != '') {
            ?>
                    <div class="form-group">
                        <div class="col-md-6">
                            <label style="text-align:left;" class="col-md-4 control-label" for="designation"><?php echo $field_name; ?> &nbsp;</label>
                            <label style="text-align:left; word-wrap: break-word;" class="col-md-8 control-label" for="designation">:&nbsp;<?php echo $old_value; ?> &nbsp;</label>
                        </div>
                        <div class="col-md-6">
                            <label style="text-align:left;" class="col-md-4 control-label" for="site_name"><?php echo $field_name; ?>&nbsp;</label>
                            <label style="text-align:left; word-wrap: break-word;" class="col-md-8 control-label" for="designation">:&nbsp;<?php echo $new_value; ?> &nbsp;</label>
                        </div>
                    </div>
                <?php } ?>

                <hr>
            <?php } ?>
            <?php if (isset($arr_shift_edit) && count($arr_shift_edit) > 0) {
            ?>
                <hr style="border-top: 1px solid #cec1c1; ">
                <h3>Edited Shift Configuration</h3>
                <hr style="border-top: 1px solid #cec1c1; ">
            <?php } ?>
            <?php foreach ($arr_shift_edit as $datas) { ?>
                <hr>
                <?php $shift_name = isset($datas['shift_name']) ? $datas['shift_name'] : '' ?>
                <h4>Shift :&nbsp;<?php echo $shift_name; ?></h4>
                <?php
                foreach ($datas as $data) {
                    // debug($data);
                    $shift_field_name = isset($data['fieldname']) ? $data['fieldname'] : '';
                    $shift_old_value = isset($data['old_value']) ? $data['old_value'] : '';
                    $shift_new_value = isset($data['new_value']) ? $data['new_value'] : '';

                    switch (trim($shift_field_name)) {
                        case 'designation_id':
                            $shift_field_name = 'Designation';
                            break;
                        case 'emp_count':
                            $shift_field_name = 'Number of Employees';
                            break;
                        case 'srate':
                            $shift_field_name = 'Sales Rate';
                            break;
                        case 'eratess':
                            $shift_field_name = 'Expense Rate';
                            break;
                        case 'start_date_effective':
                            $shift_field_name = 'Start Date';
                            break;
                        case 'end_date_effective':
                            $shift_field_name = 'End Date';
                            break;
                        default:
                            $shift_field_name = '';
                            break;
                    }
                    if (trim($shift_field_name) != '') {
                ?>
                        <hr>
                        <div class="form-group">
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="designation"><?php echo $shift_field_name; ?> &nbsp;</label>
                                <label style="text-align:left;" class="col-md-8 control-label" for="designation">:&nbsp;<?php echo $shift_old_value; ?> &nbsp;</label>
                            </div>
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="designation"><?php echo $shift_field_name; ?> &nbsp;</label>
                                <label style="text-align:left;" class="col-md-8 control-label" for="designation">:&nbsp;<?php echo $shift_new_value; ?> &nbsp;</label>
                            </div>

                        </div>
            <?php
                    }
                }
            } ?>

            <!-- New Site -->
            <?php
            if (count($arr_new_site) > 0) { ?>

                <hr style="border-top: 1px solid #cec1c1; ">
                <h3 style="margin-left: 17px">New Site Allocation</h3>
                <hr>
                <?php foreach ($arr_new_site as $details) {

                    $field_name = isset($details['sma']['fieldname']) ? $details['sma']['fieldname'] : '';
                    $new_value =  isset($details['sma']['new_value']) ? $details['sma']['new_value'] : '';

                    switch (trim($field_name)) {
                        case 'site_name':
                            $field_name = 'Site Name';
                            break;
                        case 'site_id':
                            $field_name = 'Site Code';
                            break;
                        case 'latitude':
                            $field_name = 'Latitude';
                            break;
                        case 'longitude':
                            $field_name = 'Longitude';
                            break;
                        case 'address':
                            $field_name = 'Address';
                            break;
                        case 'branch_code':
                            $field_name = 'Branch';
                            break;
                        case 'payment_mode':
                            $field_name = 'Payment Mode';
                            break;
                        case 'user_pkey':
                            $field_name = 'Site Manager';
                            break;
                        case 'special_remarks':
                            $field_name = 'Remarks';
                            break;
                        case 'min_days_before':
                            $field_name = 'Minimum Days';
                            break;
                        case 'contact_name':
                            $field_name = 'Company Name';
                            break;
                        case 'customer_name':
                            $field_name = 'Contact Person Name';
                            break;
                        case 'customer_contact':
                            $field_name = 'Contact Number';
                            break;
                        default:
                            $field_name = '';
                            break;
                    }
                    if (trim($field_name) != '') {
                ?>
                        <div class="form-group">
                            <div class="col-md-18" style="margin-left: 17px;">
                                <label style="text-align:left;" class="col-md-4 control-label" for="site_name"><?php echo $field_name; ?>&nbsp;</label>
                                <label style="text-align:left;" class="col-md-8 control-label" for="designation">:&nbsp;<?php echo $new_value; ?> &nbsp;</label>
                            </div>
                        </div>
                    <?php } ?>

                    <hr>
                <?php } ?>
            <?php } ?>
            <!-- New Shift -->
            <?php
            if (count($arr_shift_new) > 0) { ?>
                <hr style="border-top: 1px solid #cec1c1; ">
                <h3 style="margin-left: 17px;">New Shift Configuration</h3>
                <?php
                foreach ($arr_shift_new as $data) {
                    $shift_name = isset($data['wd']['day_time_desc']) ? $data['wd']['day_time_desc'] : '';
                    $designation = isset($data['desig']['desig_name']) ? $data['desig']['desig_name'] : '';
                    $sales_r = isset($data['st']['srate']) ? $data['st']['srate'] : '';
                    $exp_r = isset($data['st']['eratess']) ? $data['st']['eratess'] : '';
                    $strt_d = isset($data['st']['start_date_effective']) ? $data['st']['start_date_effective'] : '';
                    $end_d = isset($data['st']['end_date_effective']) ? $data['st']['end_date_effective'] : '';
                    $emp_no = isset($data['st']['emp_count']) ? $data['st']['emp_count'] : '';
                ?>
                    <hr>
                    <h4 style="margin-left: 17px;"> Shift: &nbsp; <?php echo $shift_name; ?> <? ?></h4>
                    <hr>
                    <div class="form-group">

                        <div class="col-md-18" style="margin-left: 17px;">
                            <label style="text-align:left;" class="col-md-4 control-label" for="designation">Designation &nbsp;</label>
                            <label style="text-align:left;" class="col-md-8 control-label" for="designation">:&nbsp;<?php echo $designation; ?></label>
                        </div>

                    </div>
                    <div class="form-group">

                        <div class="col-md-18" style="margin-left: 17px;">
                            <label style="text-align:left;" class="col-md-4 control-label" for="designation">Sales Rate &nbsp;</label>
                            <label style="text-align:left;" class="col-md-8 control-label" for="designation">:&nbsp;<?php echo $sales_r; ?></label>
                        </div>

                    </div>
                    <div class="form-group">

                        <div class="col-md-18" style="margin-left: 17px;">
                            <label style="text-align:left;" class="col-md-4 control-label" for="designation">Expense Rate</label>
                            <label style="text-align:left;" class="col-md-8 control-label" for="designation">:&nbsp;<?php echo $exp_r; ?></label>
                        </div>

                    </div>
                    <div class="form-group">

                        <div class="col-md-18" style="margin-left: 17px;">
                            <label style="text-align:left;" class="col-md-4 control-label" for="designation">Start Date &nbsp;</label>
                            <label style="text-align:left;" class="col-md-8 control-label" for="designation">:&nbsp;<?php echo $strt_d; ?> </label>
                        </div>

                    </div>
                    <div class="form-group">

                        <div class="col-md-18" style="margin-left: 17px;">
                            <label style="text-align:left;" class="col-md-4 control-label" for="designation">End Date &nbsp;</label>
                            <label style="text-align:left;" class="col-md-8 control-label" for="designation">:&nbsp;<?php echo $end_d; ?></label>
                        </div>

                    </div>
                    <div class="form-group">

                        <div class="col-md-18" style="margin-left: 17px;">
                            <label style="text-align:left;" class="col-md-4 control-label" for="designation">Number of Employees &nbsp;</label>
                            <label style="text-align:left;" class="col-md-8 control-label" for="designation">:&nbsp;<?php echo $emp_no; ?></label>
                        </div>

                    </div>

                <?php } ?>
            <?php } ?>
            <!-- Deleted Shift -->
            <?php
            if (count($arr_shift_delete) > 0) { ?>
                <hr style="border-top: 1px solid #cec1c1; ">
                <h3>To Be Deleted Shift Configuration</h3>
                <?php
                foreach ($arr_shift_delete as $data) {
                    $shift_name = isset($data['wd']['day_time_desc']) ? $data['wd']['day_time_desc'] : '';
                    $designation = isset($data['desig']['desig_name']) ? $data['desig']['desig_name'] : '';
                    $sales_r = isset($data['st']['srate']) ? $data['st']['srate'] : '';
                    $exp_r = isset($data['st']['eratess']) ? $data['st']['eratess'] : '';
                    $strt_d = isset($data['st']['start_date_effective']) ? $data['st']['start_date_effective'] : '';
                    $end_d = isset($data['st']['end_date_effective']) ? $data['st']['end_date_effective'] : '';
                    $emp_no = isset($data['st']['emp_count']) ? $data['st']['emp_count'] : '';
                ?>
                    <hr>
                    <h4> Shift: &nbsp; <?php echo $shift_name; ?> <? ?></h4>
                    <hr>
                    <div class="form-group">

                        <div class="col-md-18">
                            <label style="text-align:left;" class="col-md-4 control-label" for="designation">Designation &nbsp;</label>
                            <label style="text-align:left;" class="col-md-8 control-label" for="designation">:&nbsp;<?php echo $designation; ?></label>
                        </div>

                    </div>
                    <div class="form-group">

                        <div class="col-md-18">
                            <label style="text-align:left;" class="col-md-4 control-label" for="designation">Sales Rate &nbsp;</label>
                            <label style="text-align:left;" class="col-md-8 control-label" for="designation">:&nbsp;<?php echo $sales_r; ?></label>
                        </div>

                    </div>
                    <div class="form-group">

                        <div class="col-md-18">
                            <label style="text-align:left;" class="col-md-4 control-label" for="designation">Expense Rate</label>
                            <label style="text-align:left;" class="col-md-8 control-label" for="designation">:&nbsp;<?php echo $exp_r; ?></label>
                        </div>

                    </div>
                    <div class="form-group">

                        <div class="col-md-18">
                            <label style="text-align:left;" class="col-md-4 control-label" for="designation">Start Date &nbsp;</label>
                            <label style="text-align:left;" class="col-md-8 control-label" for="designation">:&nbsp;<?php echo $strt_d; ?> </label>
                        </div>

                    </div>
                    <div class="form-group">

                        <div class="col-md-18">
                            <label style="text-align:left;" class="col-md-4 control-label" for="designation">End Date &nbsp;</label>
                            <label style="text-align:left;" class="col-md-8 control-label" for="designation">:&nbsp;<?php echo $end_d; ?></label>
                        </div>

                    </div>
                    <div class="form-group">

                        <div class="col-md-18">
                            <label style="text-align:left;" class="col-md-4 control-label" for="designation">Number of Employees &nbsp;</label>
                            <label style="text-align:left;" class="col-md-8 control-label" for="designation">:&nbsp;<?php echo $emp_no; ?></label>
                        </div>

                    </div>

                <?php } ?>
            <?php } ?>
            <hr>
            <?php
            if (trim($status) == 'Pending') { ?>
                <div class="form-group">
                    <div class="col-md-6">
                        <label style="text-align:left;" class="col-md-4 control-label" for="middile_name">Remarks <span style="color: red;">*</span></label>
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <textarea required="required" name="remarks" class="form-control strict-field" id="remarks_field" maxlength="100"></textarea>
                        </div>
                    </div>
                </div>
            <?php } ?>


        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-danger" onclick="$('#largeModalForm').modal('hide');">Cancel</button>
        <?php
        if (trim($status) == 'Pending') { ?>
            <!-- <input type="hidden" name="approve" id="approveInput" value="true"> -->
            <input type="hidden" name="approve" id="approveInput">
            <button type="button" id="btn-approve" class="btn btn-primary">Approve</button>
            <button type="button" id="btn-reject" class="btn btn-danger">Reject</button>
        <?php } ?>
    </div>

</form>

<script>
    $(document).ready(function() {
        // Function to show a message and hide the modal
        function showMessageAndHideModal(message, type) {

            $.notify(message, {
                type: type ? 'success' : 'danger',
                allow_dismiss: false
            });
            $('#largeModalForm').modal('hide');
            $('#att_table').datagrid('reload');

        }

        // Attach click event handler for "Approve" button
        $('#btn-approve').on('click', function() {
            var required = $('#remarks_field').val();
            if (required !== '') {
                var approveValue = 'true';
                var requestData = {
                    sma_pkey: $('#sma_pkey').val(),
                    site_pkey: $('#site_pkey').val(),
                    approve: approveValue,
                    remarks: $('#remarks_field').val()
                };

                $.ajax({
                    type: 'POST',
                    url: '<?php echo $this->webroot; ?>SiteAttendanceApply/saveApproved',
                    data: requestData,
                    success: function(response) {
                        var data = JSON.parse(response); // Parse the response data
                        var message = data.message; // Extract the message
                        var type = data.success;
                        showMessageAndHideModal(message, type);
                    }
                });
            } else {
                alert('Remarks column cannot be blank');
            }

        });

        // Attach click event handler for "Reject" button
        $('#btn-reject').on('click', function() {
            var required = $('#remarks_field').val();
            if (required !== '') {
                var approveValue = 'false';
                var requestData = {
                    sma_pkey: $('#sma_pkey').val(),
                    site_pkey: $('#site_pkey').val(),
                    approve: approveValue,
                    remarks: $('#remarks_field').val()
                };
                $.ajax({
                    type: 'POST',
                    url: '<?php echo $this->webroot; ?>SiteAttendanceApply/saveApproved',
                    data: requestData,
                    success: function(response) {
                        console.log('Response', response);
                        var data = JSON.parse(response); // Parse the response data
                        var message = data.message; // Extract the message
                        var type = data.success;
                        showMessageAndHideModal(message, type);
                    }
                });
            } else {
                alert('Remarks column cannot be blank');
            }

        });





        var usershierarchyoptions = {
            url: function(phrase) {
                var emp = $('#empsetuppersonal #emp_pkey').val();
                return livesite + "Employee/getautohierarchycompletionsvgfs?username=" + phrase + "&emp=" + emp;;
            },
            getValue: "emp_name",
            list: {
                onClickEvent: function() {
                    var selectedItem = $('#hierarch').getSelectedItemData();
                    var site_pkey = selectedItem.emp_pkey;
                    $('#hierarch1').val(site_pkey);
                },
                onKeyEnterEvent: function() {

                },
                onSelectItemEvent: function() {

                }
            }
        };

        $('#hierarch').easyAutocomplete(usershierarchyoptions);

        $('#hierarch').on('keydown', function() {
            $('#hierarch1').val('');
        });


    });
</script>