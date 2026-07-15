<style>
    /* // created by Bindhu 17-10-2025 */
    .heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0px 10px 10px 0;
    }

    .heading h3 {
        margin: 0;
        font-size: 20px;
        color: #219be2ff;
        font-weight: 400;
    }

    .select2-container {
        width: 100% !important;
    }

    .add_rule {
        background-color: #1e516e;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 6px 15px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .add_rule:hover {
        background-color: #143d52;
    }

    #btnEdit,
    #btnActivate,
    #btnEdits,
    #btnRemove,
    #btnInfoChange,
    #filterBtn {
        background-color: #fff !important;
        border: 1px solid #e0e0e0 !important;
        color: #555 !important;
    }

    #btnEdit:hover,
    #btnEdits:hover,
    #btnRemove:hover,
    #btnInfoChange:hover,
    #btnActivate:hover,
    #filterBtn:hover {
        background-color: #f8f8f8 !important;
        border-color: #ccc !important;
    }

    .buttons {
        margin: 10px 0;
        display: flex;
        justify-content: start;
        align-items: center;
        gap: 5px;
    }

    .buttons-div select {
        width: 100%;
        border-radius: 8px !important;
        overflow: hidden;
        background-color: #fff !important;
        border: 1px solid #e0e0e0 !important;
        color: #555 !important;
    }

    .buttons button {
        padding: 4px 15px;
        border-radius: 8px;
    }

    .buttons button i {
        margin-right: 10px;
    }

    .datagrid-row-selected {
        background-color: #c0c0c0ff !important;
        color: black !important;
    }
    .datagrid-body {
    overflow-y: hidden !important;
    height: auto !important;
}
.heading {
        display: flex;
        flex-direction: row;
        align-items: end;
        justify-content: space-between;
        /* margin-left: 20px; */
    }

    .home {
        background-color: #ffffffff;
        border-radius: 50px;
        padding: 2px 15px;
        color: #1e516e !important;
        margin-right: 5px;
        color: white;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        border: #1e516e 1px solid;
    }
</style>

<div id="exceptionTabsWrapper">

    <!-- 🧭 Tabs -->
    <ul class="nav nav-tabs" id="exceptionTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="tab1-link" data-toggle="tab" href="#tab1"
                role="tab" aria-controls="tab1" aria-selected="true">
                Exception Rules
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="tab2-link" data-toggle="tab" href="#tab2"
                role="tab" aria-controls="tab2" aria-selected="false">
                Apply Rules
            </a>
        </li>
    </ul>

    <!-- 🧩 Tab Content -->
    <div class="tab-content" id="exceptionTabsContent">

        <!-- 🔹 TAB 1: Exception Rules -->
        <div class="tab-pane fade show active" id="tab1" role="tabpanel" aria-labelledby="tab1-link">
            <div style="text-align: center; padding: 20px;">
                <div class="heading">
                    <h3>Exception Rules</h3>
                    <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                    <i class="fa" style="font-size:16px;">&#xf104;</i>
                    Back
                </div>
                </div>

                <div style="display:flex; justify-content:space-between;" class="buttons-div">
                    <div class="buttons">
                        <button id="btnEdit">
                            <i class="fa fa-pencil" aria-hidden="true" style="color:#1e516e;"></i> Edit
                        </button>
                        <button id="btnRemove">
                            <i class="fa fa-times" aria-hidden="true" style="color:#dc1010;"></i> Remove
                        </button>
                         <button class="add_rule" id="btnCreateRule">
                            Create New Rule
                        </button>
                    </div>

                    <!-- <div style="margin-right: 14px">
                        <button class="add_rule" id="btnCreateRule" class="btn btn-primary">
                            Create New Rule
                        </button>
                    </div> -->
                </div>

                <table id="ruleTable" style="margin: 0 auto; width: 90%; text-align:center;">
                    <!-- table content -->
                </table>
            </div>
        </div>

        <!-- 🔸 TAB 2: Apply Rules -->
        <div class="tab-pane fade" id="tab2" role="tabpanel" aria-labelledby="tab2-link">
            <div style="text-align: center; padding: 20px;">
                <div class="heading">
                    <h3>Apply Rules</h3>
                    <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                    <i class="fa" style="font-size:16px;">&#xf104;</i>
                    Back
                </div>
                </div>

                <form class="form-horizontal" method="post" id="applyRuleForm">
                    <div class="form-group row justify-content-center" style="margin-top: 20px;">

                        <!-- 🏢 Branch -->
                        <div class="col-md-3" style="margin-left:-30px;">
                            <label class="col-md-4 control-label text-left" for="filterby_branch">Branch:</label>
                            <div class="col-md-8">
                                <select id="filterby_branch" name="filterby_branch"
                                    class="form-control select2-searching">
                                    <option value="">-- Select Branch --</option>
                                    <?php
                                    if (!empty($arr_branches)) {
                                        foreach ($arr_branches as $branch) {
                                            echo '<option value="' . $branch['branch_code'] . '">' . $branch['branch_name'] . '</option>';
                                        }
                                    } else {
                                        echo '<option disabled>No branches available</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- 📅 Month -->
                        <div class="col-md-3">
                            <label class="col-md-4 control-label text-left" for="filterby_month">Month:</label>
                            <div class="col-md-8">
                                <select id="filterby_month" name="filterby_month" class="form-control">
                                    <?php
                                    $start_month = strtotime(date('Y-m'));
                                    $selectedDate = date('Y-m');
                                    for ($i = 0; $i < 44; $i++) {
                                        $month = date('Y-m', strtotime("-$i month", $start_month));
                                        $selected = ($month == $selectedDate) ? 'selected' : '';
                                        echo '<option value="' . $month . '" ' . $selected . '>' . date('M-Y', strtotime($month . '-01')) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- ⚙️ Rule -->
                        <div class="col-md-3">
                            <label class="col-md-4 control-label text-left" for="filterby_rule">Rule:</label>
                            <div class="col-md-8">
                                <select id="filterby_rule" name="filterby_rule" class="form-control">
                                    <option value="">-- Select Rule --</option>
                                    <?php
                                    if (!empty($rules)) {
                                        foreach ($rules as $r) {
                                            $rule = $r['ExceptionRule'];
                                            echo '<option value="' . h($rule['exception_id']) . '">' . h($rule['rule_name']) . '</option>';
                                        }
                                    } else {
                                        echo '<option disabled>No rules available</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- ▶️ Apply -->
                        <div class="col-md-3 d-flex gap-2">
                            <button type="button" id="btnApplyRules" class="btn btn-primary">
                                Apply
                            </button>
                            <button type="button" id="btn-submit2" class="btn btn-success">
                                <i class="fa fa-file-excel-o"></i>
                            </button>
                            <button type="button" id="btn-delete" class="btn btn-danger">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>


                    </div>
                </form>

                <!-- 📊 Grid -->
                <div style="margin-top:30px;">
                    <table id="applyRuleGrid" style="width:95%; margin:0 auto;"></table>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- ✅ JS -->
<script>
    $('#filterby_branch, #filterby_month, #filterby_rule').select2();

    // ✅ TAB 1 Grid
    $('#ruleTable').datagrid({
        url: livesite + "ExceptionRule/getAllRules",
        method: 'get',
        fitColumns: true,
        singleSelect: true,
        pagination: true,
        rownumbers: true,
        pageSize: 10,
        width: '99%',
        idField: 'exception_id',
        columns: [
            [{
                    field: 'rule_name',
                    title: 'Rule Name',
                    width: '15%',
                    sortable: true
                },
                {
                    field: 'rule_type',
                    title: 'Rule Type',
                    width: '10%',
                    sortable: true
                },
                {
                    field: 'data_type',
                    title: 'Data Type',
                    width: '10%',
                    sortable: true
                },
                {
                    field: 'exception',
                    title: 'Exception',
                    width: '10%',
                    sortable: true
                },
                {
                    field: 'action',
                    title: 'Action',
                    width: '10%',
                    sortable: true
                },
                {
                    field: 'detect_count',
                    title: 'Leave Deduct Count',
                    width: '13%',
                    sortable: true
                },
                {
                    field: 'leave_type',
                    title: 'Leave Type',
                    width: '10%',
                    sortable: true
                },
                {
                    field: 'reset_status',
                    title: 'Reset',
                    width: '5%',
                    sortable: true
                },
                {
                    field: 'activate_status',
                    title: 'Active',
                    width: '5%',
                    sortable: true
                },
                {
                    field: 'creation_time',
                    title: 'Created On',
                    width: '12%',
                    sortable: true
                }
            ]
        ]
    });

    let selectedRuleId = null;
    let selectedAppliedDate = null;

    $('#applyRuleGrid').datagrid({
        url: livesite + "ExceptionRule/getAppliedList",
        method: 'get',
        fitColumns: true,
        singleSelect: true,
        pagination: true,
        rownumbers: true,
        pageSize: 10,
        width: '99%',
        idField: 'exception_applied_pkey',
        columns: [
            [{
                    field: 'rule_id',
                    title: 'Rule ID',
                    hidden: true
                },
                   {
            field: 'branch_code',
            title: 'Branch Code',
            hidden: true
        },
                {
                    field: 'rule_name',
                    title: 'Rule Name',
                    width: '25%',
                    sortable: true
                },
                {
                    field: 'branch_name',
                    title: 'Branch Name',
                    width: '25%',
                    sortable: true
                },
                {
                    field: 'applied_date',
                    title: 'Applied Date',
                    width: '24%',
                    sortable: true
                },
                {
                    field: 'month_year',
                    title: 'Applied Month',
                    width: '25%',
                    sortable: true
                }
            ]
        ],
        onClickRow: function(index, row) {
            selectedRuleId = row.rule_id;
            selectedAppliedDate = row.applied_date;
             selectedBranchCode = row.branch_code;
            console.log("Selected Rule:", selectedRuleId);
            console.log("Selected Date:", selectedAppliedDate);
            console.log("Branch Code:", selectedBranchCode);
        }
    });

    $('#btn-submit2').on('click', function() {

        const row = $('#applyRuleGrid').datagrid('getSelected');

        if (!row) {
            alert("Please select a row first");
            return;
        }

        const ruleId = row.rule_id; // exception_id from grid
        const appliedDate = row.applied_date; // selected applied date
        const appliedMonth = row.month_year;
        // console.log(appliedMonth);
        // console.log(ruleId, appliedDate,'hello');
        downloadReport('excel', ruleId, appliedDate,appliedMonth);
    });


    $(document).ready(function() {
        $('#exceptionTabs a:first').tab('show');
    });
    $(document).ready(function() {

        // Activate tab switching
        // $('#exceptionTabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        //     const target = $(e.target).attr("href");
        //     $('.tab-pane').removeClass('show active');
        //     $(target).addClass('show active');
        // });

        // $('#exceptionTabs a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
        //     const target = $(e.target).attr("href");

        //     $('.tab-pane').removeClass('show active');
        //     $(target).addClass('show active');

        //     if (target === '#tab2') {
        //         // Reload or resize #applyRuleGrid when the Apply Rules tab is shown
        //         $('#applyRuleGrid').datagrid('resize'); // Resize grid so it calculates layout
        //         $('#applyRuleGrid').datagrid('reload'); // Reload data if needed
        //     }

        //     if (target === '#tab1') {
        //         $('#ruleTable').datagrid('resize');
        //         $('#ruleTable').datagrid('reload');
        //     }
        // });
         $('#exceptionTabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    const target = $(e.target).attr("href");

    $('.tab-pane').removeClass('show active');
    $(target).addClass('show active');

    if (target === '#tab2') {
        $('#applyRuleGrid').datagrid('resize');
        $('#applyRuleGrid').datagrid('reload');

        // Refresh Rule Dropdown
        $.ajax({
            url: livesite + "ExceptionRule/getActiveRulesList",
            type: "GET",
            dataType: "json",
            success: function (data) {
                var $select = $('#filterby_rule');
                var oldValue = $select.val(); // keep previous value

                $select.empty();
                $select.append('<option value="">-- Select Rule --</option>');

                $.each(data, function (i, rule) {
                    $select.append('<option value="' + rule.id + '">' + rule.text + '</option>');
                });

                // Restore previous selection ONLY if exists in list
                if (oldValue && data.some(r => r.id == oldValue)) {
                    $select.val(oldValue);
                }

                // Update Select2 UI (if Select2 is used)
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.trigger('change.select2');
                }
            }
        });
    }

    if (target === '#tab1') {
        $('#ruleTable').datagrid('resize');
        $('#ruleTable').datagrid('reload');
    }
});

        // Apply Select2
        $('#filterby_branch, #filterby_month, #filterby_rule').select2();

        // -----------------------------
        //   🔵 APPLY RULE BUTTON CLICK
        // -----------------------------
     $('#btnApplyRules').on('click', function () {

    const $applyBtn  = $(this);
    const $deleteBtn = $('#btn-delete');
    const $exportBtn = $('#btn-submit2');

    const ruleId = $('#filterby_rule').val();
    const branchCode = $('#filterby_branch').val();
    const month = $('#filterby_month').val();

    if (!ruleId || !branchCode || !month) {
        alert('Please select Rule, Branch, and Month before applying');
        return;
    }

    const monthStart = month + "-01";

    $.ajax({
        url: livesite + "ExceptionRule/applyRule",
        type: "POST",
        data: {
            rule_id: ruleId,
            branch_code: branchCode,
            month_start: monthStart
        },
        dataType: "json",

        // 🔹 Lock all actions
        beforeSend: function () {
            $applyBtn.prop('disabled', true)
                     .addClass('btn-disabled')
                     .text('Applying...');

            $deleteBtn.prop('disabled', true);
            $exportBtn.prop('disabled', true);
        },

        success: function (res) {
            if (res.status === "error") {
                alert(res.message);
                return;
            }

            if (res.status === "success") {
                alert("Rule Applied Successfully!");
                $('#applyRuleGrid').datagrid('reload');
            }
        },

        error: function () {
            alert('Something went wrong while applying the rule.');
        },

        // 🔹 Unlock all actions
        complete: function () {
            $applyBtn.prop('disabled', false)
                     .removeClass('btn-disabled')
                     .text('Apply');

            $deleteBtn.prop('disabled', false);
            $exportBtn.prop('disabled', false);
        }
    });

});


    });

  // Edit + Remove + Create handlers
    $('#btnCreateRule').click(() => showModalForm(livesite + "ExceptionRule/newForm"));
    $('#btnEdit').click(() => {
        const row = $('#ruleTable').datagrid('getSelected');
        if (!row) return alert("Please select a rule to edit");
        showModalForm(livesite + "ExceptionRule/getRuleById/" + row.exception_id);
    });
    $('#btnRemove').click(() => {
        const row = $('#ruleTable').datagrid('getSelected');
        if (!row) return alert("Please select a rule to delete");
        if (confirm("Do you want to delete the selected rule?")) {
            $.post(livesite + "ExceptionRule/deleteRule", {
                id: row.exception_id
            }, (res) => {
                try {
                    res = JSON.parse(res);
                    $.notify(res.message, {
                        type: res.status
                    });
                    if (res.status === 'success') $('#ruleTable').datagrid('reload');
                } catch {
                    $.notify("Unexpected response", {
                        type: 'danger'
                    });
                }
            });
        }
    });
$('#btn-delete').on('click', function () {

    const $deleteBtn = $(this);
    const $trashIcon = $deleteBtn.find('i.fa-trash');

    // Other buttons
    const $applyBtn = $('#btnApplyRules');
    const $exportBtn = $('#btn-submit2');

    const row = $('#applyRuleGrid').datagrid('getSelected');

    if (!row) {
        alert("Please select a row to delete");
        return;
    }

    const confirmMsg =
        '⚠️ WARNING: This action will REVERSE the applied rule!\n' +
        'All attendance regularizations and leave entries applied by this rule will be undone.\n' +
        'Rule: ' + row.rule_name + '\n' +
        'Branch: ' + row.branch_name + '\n' +
        'Month: ' + row.month_year + '\n' +
        'Are you sure you want to proceed?';

    if (!confirm(confirmMsg)) {
        return;
    }

    $.ajax({
        url: livesite + "ExceptionRule/reverseAppliedRule",
        type: "POST",
        data: {
            exception_applied_pkey: row.exception_applied_pkey,
            branch_code: row.branch_code || $('#filterby_branch').val(),
            rule_id: row.rule_id,
            month_year: row.month_year
        },
        dataType: "json",

        // 🔹 Before call
        beforeSend: function () {
            // Disable all buttons
            $deleteBtn.prop('disabled', true);
            $applyBtn.prop('disabled', true);
            $exportBtn.prop('disabled', true);

            // Delete button UI
            $trashIcon.hide();
            $deleteBtn.append(
                '<i class="fa fa-spinner fa-spin loading-spinner"></i>'
            );
        },

        success: function (res) {
            if (res.status === "success") {
                alert(res.message);
                $('#applyRuleGrid').datagrid('reload');
            } else {
                alert("Error: " + res.message);
            }
        },

        error: function (xhr, status, error) {
            alert("Failed to reverse rule: " + error);
        },

        // 🔹 Restore UI
        complete: function () {
            $deleteBtn.prop('disabled', false);
            $applyBtn.prop('disabled', false);
            $exportBtn.prop('disabled', false);

            $deleteBtn.find('.loading-spinner').remove();
            $trashIcon.show();
        }
    });
});

    // console.log(ruleId, appliedDate,'hi');
   function downloadReport(format, ruleId, appliedDate, appliedMonth) {

    console.log(ruleId, appliedDate, appliedMonth, 'download');

    if (!ruleId || !appliedMonth) {
        alert("Missing rule or applied month.");
        return;
    }

    const params = new URLSearchParams({
        rule_id: ruleId,
        applied_date: appliedDate,
        applied_month: appliedMonth,
        format: format
    });

    const url = livesite + "ExceptionRule/downloadExceptionExcel?" + params.toString();

    console.log("Downloading: ", url);

    window.open(url, "_blank");
}

    $(".home").on("click", function () {

    $("#container").isLoading({
        text: "Loading",
        position: "overlay",
    });

    let url = "";
    var userGroup = <?php echo json_encode($this->Session->read('user_group')); ?>;

    if (userGroup == "1") {
        url = livesite + "AttendanceSetup/index";
    } 
    else if (userGroup == "2") {
        url = livesite + "EmployeeMenu/addon";
    }

    $("#container").load(url, function () {
        isDashboardShown = false;
    });

});
    
</script>