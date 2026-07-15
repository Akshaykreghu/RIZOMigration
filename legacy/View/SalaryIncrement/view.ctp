<style>
    .salary-display {
        font-family: Arial, sans-serif;
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        overflow-x: auto;
    }

    .display-header {
        padding: 15px;
        font-size: 18px;
        text-align: center;
    }

    .display-table {
        border: 1px solid #ddd;
        margin: 10px 0;
    }

    .display-row {
        display: flex;
        border-bottom: 1px solid #eee;
        padding: 0;
        /* changed from 8px 0 to 0 */
        margin: 0;
        /* ensure no extra spacing */
    }

    .display-row div {
        padding: 0px;
        word-wrap: break-word;
    }

    .header-row {
        background-color: #D9D9D9;
        font-weight: bold;
    }

    .display-footer {
        text-align: right;
        padding: 10px;
    }

    .print-btn,
    .close-btn {
        padding: 8px 15px;
        margin-left: 10px;
        cursor: pointer;
        border: 1px solid #ddd;
        background: white;
    }

    .print-btn:hover {
        background: #f5f5f5;
    }

    .close-btn {
        color: #cc0000;
        border-color: #cc0000;
    }

    /* [Keep all your existing styles] */
    /* Add these new remark styles */
    .remark-section {
        display: flex;
        align-items: center;
        margin: 15px 0;
        padding: 8px;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }

    .remark-label {
        font-weight: bold;
        margin-right: 8px;
        min-width: 80px;
    }

    .remark-value {
        flex-grow: 1;
    }

    /* Responsive adjustments */
    @media (max-width: 1200px) {
        .display-row div {
            font-size: 14px;
            padding: 0px;
        }
    }

    .display-table {
        border: 1px solid #ddd;
        margin: 10px 0;
        /* No collapse needed since it's divs */
    }

    .display-row {
        display: flex;
    }

    .display-row>div {
        padding: 5px;
        border: 1px solid #ddd;
        border-bottom: none;
        word-wrap: break-word;
    }

    /* Optional: for header row to have different bg */
    .header-row>div {
        background-color: #D9D9D9;
        font-weight: bold;
    }

    /* Edited by Akshay on 8-11-2025 */
    .text-red {
        color: #ff0000 !important;
    }

    /* End */
</style>
<!-- CSS -->

<!-- Modal Structure -->
<div class="modal-dialog modal-lg">
    <?php
    $formatSafeDate = function ($dateStr, $format) {
        if (empty($dateStr) || $dateStr === '0000-00-00' || strtotime($dateStr) === false) {
            return '';
        }
        return date($format, strtotime($dateStr));
    };
    ?>
    <div class="modal-header" style="background: #00659f; color: white;">
        <h4 class="modal-title">Salary Increment View</h4>
    </div>

    <div class="modal-body">
        <div id="processResponseContainer" style="margin-bottom:15px;"></div>
        <?php if ($structure_change == 'Y'): ?>
            <div style="
                color: #b30000;
                font-weight: bold;
                font-size: 16px;
                text-align: left;
                margin-bottom: 10px;
            ">
                ⚠️ Note: Structure Change has been detected.
            </div>
        <?php endif; ?>

        <?php if (!empty($arr_grouped)): ?>
            <div class="display-table">
                <!-- Header Row -->
                <div class="display-row header-row">
                    <div style="width: 6%">Sl No</div>
                    <div style="width: 35%">Employee Name</div>
                    <div style="width: 20%">Employee ID</div>
                    <div style="width: 20%">Branch</div>
                    <div style="width: 20%">Start Date Effective</div>
                    <div style="width: 20%">Next Increment Date</div>
                    <div style="width: 15%">Arrear</div>
                    <div style="width: 20%">Payout Month</div>
                    <!-- <div style="width: 20%">Remarks</div> -->
                </div>

                <!-- Data Rows -->
                <?php
                $slno = 1;
                foreach ($arr_grouped as $arr_emp_details):
                    foreach ($arr_emp_details as $value):
                        $textClass = ($action == 'Processed' && $value['processed'] == 'N') ? 'text-red' : ''; // Determine if text should be red
                ?>
                        <div class="display-row <?= $textClass ?>">
                            <div style="width: 6%"><?= $slno++ ?></div>
                            <div style="width: 35%"><?= htmlspecialchars(trim($value['EmpName'])) ?></div>
                            <div style="width: 20%"><?= htmlspecialchars($value['employee_id']) ?></div>
                            <div style="width: 20%"><?= htmlspecialchars($value['branch']) ?></div>
                            <div style="width: 20%"><?= $formatSafeDate($value['with_effect_from'], 'd-m-Y') ?></div>
                            <div style="width: 20%"><?= $formatSafeDate($value['next_increment_date'], 'd-m-Y') ?></div>
                            <div style="width: 15%"><?= ($value['arrear_salary'] == 'N') ? 'No' : 'Yes' ?></div>
                            <div style="width: 20%"><?= $formatSafeDate($value['payout_month'], 'F-Y') ?></div>
                            <!-- <div style="width: 20%"><?= htmlspecialchars($remarks) ?></div> -->
                        </div>
                <?php
                    endforeach;
                endforeach;
                ?>
            </div>
        <?php else: ?>
            <div style="
                color: #b30000;
                font-weight: bold;
                font-size: 16px;
                text-align: left;
                margin-bottom: 10px;
            ">
                No increments found.</div>
        <?php endif; ?>

        <?php if (!empty($remarks)): ?>
            <div class="remark-section">
                <span class="remark-label">Remarks:</span>
                <span class="remark-value"><?= htmlspecialchars($remarks) ?></span>
            </div>
        <?php endif; ?>

        <!-- Edited by Akshay on 8-11-2025 -->
        <?php if ($action == 'Processed'): ?>
            <div style="margin-top: 10px; font-size: 14px; color: #6c757d;">
                <strong>Note:</strong> Entries in <span style="color: #ff0000; font-weight: bold;">red color</span> are not yet processed.
            </div>
        <?php endif; ?>
        <!-- End -->
    </div>

    <!-- Modal Footer -->
    <div class="modal-footer" style="text-align: right; width: 100%;">
        <?php if ($action != 'Processed') : ?>
            <?php if (!empty($arr_grouped) || ($structure_change == 'Y')): ?>
                <button type="button" class="btn btn-primary" id="processButton"
                    data-salary-hike-pkey="<?= htmlspecialchars($salary_hike_pkey) ?>"
                    data-is-item="<?= htmlspecialchars($is_item) ?>"
                    data-is-multiple="<?= htmlspecialchars($is_multiple) ?>"
                    data-structure-change="<?= htmlspecialchars($structure_change) ?>">
                    Process
                </button>
            <?php endif; ?>

            <!-- Delete -->
            <button type="button" class="btn btn-danger" id="deleteButton"
                data-salary-hike-pkey="<?= htmlspecialchars($salary_hike_pkey) ?>">
                Delete
            </button>
        <?php endif; ?>

        <?php if (!empty($arr_grouped) || ($structure_change == 'Y')): ?>
            <button type="button" class="btn btn-success btn-md" id="downloadButton"
                data-salary-hike-pkey="<?= htmlspecialchars($salary_hike_pkey) ?>"
                data-is-item="<?= htmlspecialchars($is_item) ?>"
                data-is-multiple="<?= htmlspecialchars($is_multiple) ?>">
                <i class="fa fa-file-excel-o" style="margin-right: 6px; margin-left: 6px;"></i>
            </button>
        <?php endif; ?>

        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
    </div>
</div>

<script>
    $(document).ready(function() {

        $('#processButton').on('click', function() {

            var $btn = $(this);
            $btn.prop('disabled', true).text('Processing...');

            var salary_hike_pkey = $(this).data('salary-hike-pkey');
            var is_item = $(this).data('is-item');
            var is_multiple = $(this).data('is-multiple');
            var structure_change = $(this).data('structure-change');


            function callProcess(structureMessages = '') {

                $.ajax({
                    url: livesite + (is_item === 'Y' ? 'SalaryIncrement/processItem' : 'SalaryIncrement/process'),
                    type: 'POST',
                    data: {
                        salary_hike_pkey: salary_hike_pkey,
                        is_multiple: is_multiple
                    },
                    success: function(response) {

                        var result;

                        try {
                            result = JSON.parse(response);
                        } catch (e) {
                            showFinalPopup('<div class="alert-danger">Invalid server response</div>');
                            return;
                        }

                        let processHtml = '';
                        console.log("result.success", result.success)
                        if (result.success) {
                            processHtml += `
                        <div style="padding:10px;margin-bottom:10px;background:#d4edda;border:1px solid #c3e6cb;border-radius:4px;">
                            <strong>Success</strong><br>
                            Processed successfully
                        </div>`;
                        } else {
                            processHtml += `
                        <div style="padding:10px;margin-bottom:10px;background:#f8d7da;border:1px solid #f5c6cb;border-radius:4px;">
                            <strong>Error</strong><br>
                            ${result.message || 'Processing failed'}
                        </div>`;
                        }

                        if (result.error_message && result.error_message != '') {
                            processHtml += `
                        <div style="padding:10px;margin-bottom:10px;background:#f8d7da;border:1px solid #f5c6cb;border-radius:4px;">
                            ${result.error_message}
                        </div>`;
                        }

                        showFinalPopup(structureMessages, processHtml);

                        $('#att_table').datagrid('reload');

                        $btn.prop('disabled', false).text('Process');

                    },

                    error: function() {
                        showFinalPopup(`
                    <div style="padding:10px;background:#f8d7da;border:1px solid #f5c6cb;border-radius:4px;">
                        Server error occurred
                    </div>`);
                    }

                });
            }


            if (structure_change === 'Y') {

                $.ajax({

                    url: livesite + 'SalaryIncrement/alterSalaryStructure',
                    type: 'POST',
                    data: {
                        salary_hike_pkey: salary_hike_pkey
                    },

                    success: function(response) {

                        var result;

                        try {
                            result = JSON.parse(response);
                        } catch (e) {
                            showFinalPopup("Invalid structure response");
                            return;
                        }

                        let structureMessages = buildStructureHtml(result);

                        callProcess(structureMessages);

                    },

                    error: function() {
                        showFinalPopup("Error while updating structure");
                    }

                });

            } else {

                callProcess();

            }

        });



        function buildStructureHtml(result) {

            let html = '';

            if (result.missing_message && result.missing_message.trim() !== '') {

                html += `
                            <div style="
                                background:white;
                                padding:15px;
                                border-radius:10px;
                                margin-bottom:15px;
                                border-left:5px solid #f0ad4e;
                            ">
                                <strong>Missing Statutory Fields</strong>
                                <div style="margin-top:5px;">
                                    ${result.missing_message}
                                </div>
                            </div>`;
            }

            if (result.wrong_salary_message && result.wrong_salary_message.trim() !== '') {

                html += `
        <div style="
            background:white;
            padding:15px;
            border-radius:10px;
            margin-bottom:15px;
            border-left:5px solid #d9534f;
        ">
            <strong>Incorrect Salary</strong>
            <div style="margin-top:5px;">
                ${result.wrong_salary_message}
            </div>
        </div>`;
            }

            return html;
        }


        function showFinalPopup(structureHtml, processHtml = '') {

            $('#final-popup-overlay').remove();

            const popup = `
        <div id="final-popup-overlay" style="
            position:fixed;
            top:0;
            left:0;
            width:100%;
            height:100%;
            background:rgba(0,0,0,0.4);
            z-index:9999;
            display:flex;
            align-items:center;
            justify-content:center;
        ">

            <div id="final-popup-box" style="
                background:#f5f6f7;
                border-radius:12px;
                width:480px;
                max-width:90%;
                overflow:hidden;
                font-family: Arial, sans-serif;
                box-shadow:0 4px 12px rgba(0,0,0,0.2);
                border:1px solid #ddd;
            ">

                <!-- Header -->
                <div style="
                    background:#00659f;
                    color:white;
                    padding:12px 16px;
                    font-size:15px;
                    font-weight:bold;
                ">
                    Salary Processing Summary
                </div>

                <!-- Body -->
                <div style="padding:15px">
                    ${structureHtml}
                    ${processHtml}
                </div>

                <!-- Footer -->
                <div style="
                    text-align:right;
                    padding:12px 15px;
                    background:white;
                    border-top:1px solid #eee;
                ">

                    <button id="popup-ok"
                        style="
                            padding:6px 16px;
                            border:none;
                            background:#00659f;
                            color:white;
                            border-radius:5px;
                            cursor:pointer;
                        ">
                        OK
                    </button>

                </div>

            </div>

        </div>
    `;

            $('body').append(popup);

            // OK Button
            $('#popup-ok').on('click', function() {
                $('#final-popup-overlay').remove();
                $('.modal').modal('hide');
            });

            // Click Outside Close
            $('#final-popup-overlay').on('click', function(e) {
                if (!$(e.target).closest('#final-popup-box').length) {
                    $('#final-popup-overlay').remove();
                    $('.modal').modal('hide');
                }
            });

        }



        // Download button
        $('#downloadButton').on('click', function() {

            var salary_hike_pkey = $(this).data('salary-hike-pkey');
            var is_item = $(this).data('is-item');
            var is_multiple = $(this).data('is-multiple');

            var url = livesite + (is_item === 'Y' ? 'SalaryIncrement/itemIncerementReport' : 'SalaryIncrement/incerementReport');

            var form = $('<form>', {
                method: 'POST',
                action: url,
                target: '_blank'
            });

            form.append($('<input>', {
                type: 'hidden',
                name: 'salary_hike_pkey',
                value: salary_hike_pkey
            }));

            form.append($('<input>', {
                type: 'hidden',
                name: 'is_multiple',
                value: is_multiple
            }));

            $('body').append(form);
            form.submit();
            form.remove();

        });



        // Delete button
        $('#deleteButton').on('click', function() {

            var salary_hike_pkey = $(this).data('salary-hike-pkey');

            if (!confirm("Are you sure you want to delete this increment record?")) return;

            $.ajax({

                url: livesite + 'SalaryIncrement/deleteIncrement',
                type: 'POST',
                data: {
                    salary_hike_pkey: salary_hike_pkey
                },

                success: function(response) {

                    var result;

                    try {
                        result = JSON.parse(response);
                    } catch (e) {
                        showFinalPopup("Invalid response from server");
                        return;
                    }

                    if (result.success) {

                        showFinalPopup(`
                    <div style="padding:10px;background:#d4edda;border:1px solid #c3e6cb;border-radius:4px;">
                        ${result.message || 'Deleted successfully'}
                    </div>`);

                        $('#att_table').datagrid('reload');

                    } else {

                        showFinalPopup(result.message || "Delete failed");

                    }

                },

                error: function() {
                    showFinalPopup("Server error occurred during delete");
                }

            });

        });

    });
</script>