<style>
    #modalFileInput {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        height: 0 !important;
        width: 0 !important;
        position: absolute !important;
        z-index: -1 !important;
    }

    /* Fixed space for filename, with ellipsis */
    #selected-file-name {
        max-width: 200px;
        /* You can adjust width */
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        text-align: center;
    }

    /* Add fixed spacing between buttons */
    .file-button-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 40px;
        /* controls space between buttons */
    }
</style>
<!-- Upload Modal Content -->
<div class="modal-content">
    <!-- Blue Header -->
    <div class="modal-header bg-primary text-white">
        <h3 class="modal-title">Upload Employee CTC File</h3>
        <!-- <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button> -->
    </div>

    <!-- Modal Body -->
    <div class="modal-body">
        <form id="ctcUploadModalForm" enctype="multipart/form-data">
            <div class="form-group" style="display:flex;justify-content:flex-start;align-items:center;">
                <!-- Edited by Akshay on 15-10-2025 -->
                <div>
                    <input type="radio" id="gross" value="gross" name="monthly" checked>
                    <label for="gross" style="margin-right:5px;">Gross Salary</label>

                    <input type="radio" id="salary_component" value="salary_component" name="monthly">
                    <label for="salary_component">Salary Component</label>
                </div>
                <!-- End -->
            </div>

            <div class="form-group" style="display:flex;justify-content:space-between;align-items:center;">

                <div style="width:30%;">
                    <label class="filterby_branch" style="font-weight:normal;">Branch</label>
                    <div>
                        <select id="filterby_branch" name="filterby_branch" class="form-control" style="width: 200px;" onchange="filterAttendanceupload(this);">
                            <?php
                            if (!isset($payroUser[0]['emp_proff']['payro_priv']) || $payroUser[0]['emp_proff']['payro_priv'] != 1) {
                                if ($is_ho == 1 || $user_group != 2) {
                                    echo '<option value="">All</option>';
                                }
                            }
                            foreach ($arr_branches as $key => $value) {
                                if (isset($payroUser[0]['emp_proff']['emp_branch']) && $payroUser[0]['emp_proff']['payro_priv'] == 1) {
                                    if ($payroUser[0]['emp_proff']['emp_branch'] !== $value['Units']['branch_code']) {
                                        continue;
                                    }
                                }
                                echo '<option value="' . $value['Units']['branch_code'] . '">' . $value['Units']['branch_name'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div style="width:30%;">
                    <label class="form-label" style="font-weight:normal;">Employee</label>
                    <div>
                        <select id="emp_fkey_2" name="emp_fkey" class="form-control" style="width: 200px;" onchange="filterAttendanceupload(this);">
                            <option value="">All</option>
                            <?php
                            foreach ($arr_employees as $employee) { ?>
                                <option value="<?php echo $employee['ei']['emp_pkey'] ?>"><?php echo $employee['ei']['EmpName'] . ' - ' . $employee['ei']['employee_id'] ?></option>
                            <?php }
                            ?>
                        </select>
                    </div>
                </div>

                <div style="width:30%;">
                    <label class="form-label" style="font-weight:normal;">Salary Structure</label>
                    <div>
                        <select id="approved_bys" name="approved_bys" class="form-control" style="width: 200px;" onchange="filterAttendanceupload(this);">
                            <option value="0">All</option>
                            <?php foreach ($arr_structure as $key => $value) { ?>
                                <option value="<?php echo $value['SalaryStructures']['structure_id']; ?>">
                                    <?php echo $value['SalaryStructures']['structure_name'] . ' - ' . $value['SalaryStructures']['structure_eg_amt']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>


            <div class="form-group">

                <label><strong>Select Excel File</strong> <span class="text-danger">*</span></label>

                <div class="d-flex align-items-center mt-1 justify-content-between">

                    <!-- Upload button -->
                    <label for="modalFileInput"
                        class="btn btn-success btn-sm"
                        data-toggle="tooltip"
                        title="Accepted: .xls, .xlsx only">
                        <i class="fa fa-file-excel-o"></i>
                        Upload
                    </label>

                    <!-- File name (center, reduced spacing) -->
                    <span id="selected-file-name"
                        class="text-muted mx-1"
                        style="font-size: 13px; max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; text-align: center;">
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </span>

                    <!-- Download button -->
                    <button type="button"
                        id="btn-downloademployeectcform"
                        class="btn btn-danger btn-sm"
                        onclick="downloadEmployeeCTCForm();"
                        data-toggle="tooltip"
                        title="Download template">
                        <i class="fa fa-file-excel-o"></i> Template
                    </button>
                </div>

                <!-- Hidden file input -->
                <input type="file" name="empctc[]" id="modalFileInput" class="d-none" accept=".xls,.xlsx">


            </div>

            <div class="form-group mt-3">
                <label for="uploadRemark"><strong>Remarks <span style="color: red">*</span></strong></label>
                <input type="text" name="upload_remark" id="uploadRemark" class="form-control" placeholder="Enter  remarks" required>
            </div>
        </form>
    </div>

    <!-- Modal Footer -->
    <div class="modal-footer">
        <button type="button" class="btn btn-primary btn-sm" id="btnSaveUpload">
            <!-- <i class="fa fa-upload"></i> -->
            Upload
        </button>
        <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">
            <!-- <i class="fa fa-times"></i>  -->
            Cancel
        </button>
    </div>
</div>

<script>
    jQuery(document).ready(function() {
        $("#filterby_branch").select2();
        $("#emp_fkey_2").select2();
        $("#approved_bys").select2();
    });

    // Prevent multiple bindings
    $(document).off('click', '#btnSaveUpload').on('click', '#btnSaveUpload', function() {
        const fileInput = document.getElementById('modalFileInput');
        const files = fileInput.files;

        if (files.length === 0) {
            $.notify("Please select a file", {
                type: 'danger',
                z_index: 10000
            });
            return;
        }

        const remark = $('#uploadRemark').val().trim();
        if (remark === '') {
            $.notify("Please enter remarks", {
                type: 'danger',
                z_index: 10000
            });
            return;
        }

        const approvedBy = $('#approved_bys').val(); // From external input
        const uploadType = $('input[name="monthly"]:checked').val(); // From external radio
        // const ctcuploadtype = $('#ctc_upload_type').val() || 2; // From external input
        const ctcuploadtype = 1;

        const formData = new FormData();
        formData.append('empctc[]', files[0]);
        formData.append('remark', remark);
        formData.append('approvedBy', approvedBy);
        formData.append('upload_type', uploadType);
        formData.append('ctcuploadtype', ctcuploadtype);

        const endpoint = (uploadType !== 'gross') ? 'uploadandsaveempctcitem' : 'uploadandsaveempctc';

        $.ajax({
            url: livesite + 'SalaryIncrement/' + endpoint + '/' + ctcuploadtype + '/' + approvedBy,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#largeModalForm').modal('hide'); // Adjust modal ID if needed
                const res = JSON.parse(response);
                $.notify(res.msg || 'Upload complete', {
                    type: res.success ? 'success' : 'danger',
                    z_index: 10000
                });

                $('#att_table').datagrid('reload');
            },
            error: function() {
                $.notify("Upload failed", {
                    type: 'danger',
                    z_index: 10000
                });
            }
        });

    });

    $(document).ready(function() {
        $("#modalFileInput").on("change", function() {
            let fileName = this.files.length ? this.files[0].name : '';
            let shortName = fileName;

            // Truncate if too long
            if (fileName.length > 30) {
                shortName = fileName.substring(0, 27) + "...";
            }

            // Add spaces on both sides
            shortName = "  " + shortName + "              ";

            $('#selected-file-name').text(shortName);
        });
    });

    function filterAttendanceupload(obj) {
        var branch = $('#filterby_branch').val();
        var employee = $('#emp_fkey_2').val()
        var structure = $('#importcomponents #approved_bys').val();
        var activeTabId = $('.tabs-process .tab.active').attr('id'); // example: 'tab1' or 'tab2'
        var status = (activeTabId === 'tab2') ? 'Processed' : 'Not Processed';
        // Edited by Akshay on 11-10-2025
        status = (activeTabId == 'tab0') ? 'Pending' : status;
        var url = (status === 'Pending') ? livesite + "SalaryIncrement/employeelistPending" : livesite + "SalaryIncrement/employeelist";
        filterEmployees();
    }

    // Edited by Akshay on 19-11-2025
    function downloadEmployeeCTCForm() {

        var ctcuploadtype = $('#ctc_upload_type').val();
        var selectedValue = $('input[name="monthly"]:checked').val();
        var salStructure = $("#approved_bys").val();
        if (ctcuploadtype == '') {
            alert('Please select Choose Type ');
            $('#ctc_upload_type').focus();
        }
        var ctcuploadtype = $('#ctc_upload_type').val();
        var branch = $('#filterby_branch').val();
        var employee = $('#emp_fkey_2').val();
        var month = $('#emp_fkey_2').val();
        var arrear = $('input[name="arrear"]:checked').val();
        console.log('branch', branch);
        console.log('employee', employee);
        console.log('salStructure', salStructure);

        var payout = $('#time').val();
        var startdate = $('#effect').val();
        var approvedBy = $('#approved_bys').val();
        ctcuploadtype = (ctcuploadtype === undefined) ? 2 : ctcuploadtype;
        if (branch === '') {
            branch = "0";
        }
        console.log('branch', branch);
        var url = '<?php echo $this->webroot; ?>';
        const safe = (v) => {
            if (v === undefined || v === null || v === '') return 0;
            return isNaN(v) ? v : Number(v);
        };
        var selectedValue = $('input[name="monthly"]:checked').val();
        // alert(selectedValue);
        if (selectedValue !== 'gross') {
            url += 'SalaryIncrement/downloadempctcformatItem/';
            url += safe(employee) + '/' + safe(branch) + '/' + safe(salStructure);
        } else {
            ctcuploadtype = 2;
            url += 'SalaryIncrement/downloadempctcformat/';
            url += safe(ctcuploadtype) + '/' +
                safe(branch) + '/' +
                safe(employee)+ '/' + safe(salStructure);
        }
        console.log('url', url);
        // 🔥 Create hidden download link
        let a = document.createElement('a');
        a.href = url;
        a.download = ""; // important for forcing file download
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }
    // End
</script>