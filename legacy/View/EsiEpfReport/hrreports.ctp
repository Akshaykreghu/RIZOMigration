<style>
    /* edited by bindu 20-11-2025 */
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
        /* margin-right: 15px; */
        color: white;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        border: #1e516e 1px solid;
    }

    /* edited by bindu 20-11-2025 end */
</style>
<section class="content-header heading">
    <!-- edited by athira on 03-07-2025 -->
    <h1 class="text-primary-18">Statutory Upload</h1>
    <!-- end -->
    <!-- /* edited by bindu 20-11-2025 */ -->
    <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
        <i class="fa" style="font-size:16px;">&#xf104;</i>
        Back
    </div>
    <!-- /* edited by bindu 20-11-2025 */ -->
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- DIRECT CHAT DANGER -->
            <div class="box ">
                <div class="box-header with-border">
                    <div class="row">
                        <div class="col-md-4" style="font-size: medium;font-weight: 600;margin-bottom: -22px;margin-top: 17px;">
                            <span>Choose Report Type &nbsp; : </span>
                        </div>
                        <div class="col-md-4" style="float: right;margin-right:560px;margin-top: -2px;">
                            <select id="filterby_reporttype" name="filterby_reporttype" style="width: 240px;" class="form-control" onchange="changeReportType(this);">
                                <?php
                                foreach ($arr_reporttypes as $key => $value) {
                                    echo '<option value="' . $key . '">' . $value . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12" style="margin-top: -2px;margin-left: 8px;">
                        <h3 class="box-title"><label id="current_reporttype"></label></h3>
                    </div>
                </div>
                <hr style="margin-top: 2px;margin-bottom: 10px;">
                <div class="row">
                    <div class="col-md-3">
                        <label class="col-md-6 control-label" for="filterby_month">Month &nbsp; : </label>
                        <div class="col-md-6">
                            <form class="form-horizontal" method="post" action="" id="form-showreport">
                                <select id="filterby_month" name="filterby_month" onchange="hidebut();" class="form-control">

                                    <?php
                                    $start_month = strtotime(date('Y-m', strtotime("+1 month", strtotime(date('Y-m')))));
                                    for ($i = 0; $i < 10; $i++) {
                                        $month = date('Y-m', strtotime("-$i month", $start_month));
                                        if ($month == date('Y-m')) {
                                            echo '<option selected="selected" value="' . $month . '">' . date('M-Y', strtotime("-$i month", $start_month)) . '</option>';
                                        } else {
                                            echo '<option value="' . $month . '">' . date('M-Y', strtotime("-$i month", $start_month)) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </form>
                        </div>
                    </div>

                    <!-- Edited by Akshay on 8-5-2026 -->
                    <div class="col-md-5">

                        <label class="col-md-3 control-label">
                            Branch &nbsp; :
                        </label>

                        <div class="col-md-9">

                            <!-- Dropdown -->
                            <div class="dropdown" style="width:100%;">

                                <button
                                    type="button"
                                    data-toggle="dropdown"
                                    id="branchDropdownBtn"
                                    style="
                                    width:100%;
                                    text-align:left;
                                    background:#fff;
                                    border:1px solid #ccc;
                                    height:30px;
                                    padding-right:30px;
                                    position:relative;">

                                    Select Branch

                                    <span class="caret"
                                        style="position:absolute;
                                        right:10px;
                                        top:50%;
                                        margin-top:-2px;"></span>
                                </button>

                                <ul class="dropdown-menu"
                                    style="width:100%; padding:10px; max-height:300px; overflow-y:auto;">

                                    <!-- Select All -->
                                    <li>
                                        <label style="width:100%;">
                                            <input type="checkbox" id="select_all_branch">
                                            <b>Select All</b>
                                        </label>
                                    </li>

                                    <li class="divider"></li>

                                    <?php
                                    foreach ($arr_branches as $branch) {

                                        $branch_code = $branch['branches']['branch_code'];
                                        $branch_name = $branch['branches']['branch_name'];
                                    ?>

                                        <li>
                                            <label style="width:100%; font-weight:normal;">

                                                <input type="checkbox"
                                                    class="branch_checkbox"
                                                    value="<?php echo $branch_code; ?>">

                                                <?php echo $branch_name; ?>

                                            </label>
                                        </li>

                                    <?php } ?>

                                </ul>
                            </div>

                            <!-- Hidden field -->
                            <input type="hidden"
                                id="filterby_branch"
                                name="filterby_branch">

                        </div>
                    </div>
                    <!-- End -->
                    <!-- --------------------------------------------------------------------------------------------------------------- -->
                    <div class="col-md-4" style="display: none;" id="criterias">
                        <label class="col-md-4 control-label" for="report_component">Criteria &nbsp;: </label>
                        <div class="col-md-4">
                            <select id="report_component" name="report_component" style="width:250px;" class="form-control" onchange="changeCriteria()">
                                <option value="">--Choose Type--</option>

                                <option value="pf"> EPF - Contribution with PF Salary </option>
                                <option value="actual"> EPF - Contribution with Actual Salary</option>
                            </select>
                        </div>
                    </div>
                    <!-- --------------------------------------------------------------------------------------------------------------- -->

                </div>

                <!-- Edited by Akshay on 8-5-2026 -->
                <div class="row" style="margin-top: 7%;">
                    <div id="generate-button" class="col-md-12">

                        <div style="display:flex; justify-content:flex-end; align-items:center; gap:10px; flex-wrap:wrap;">

                            <!-- Generate Button -->
                            <button class="btn btn-primary" onclick="download('excel');">
                                Generate
                            </button>

                            <!-- Download Section -->
                            <div id="downloadpf">
                            </div>

                            <!-- PDF Button -->
                            <div id="esi-pdf" style="display:none;">
                                <button type="button"
                                    onclick="downloadReport('pdf');"
                                    id="btn-submit1"
                                    class="btn btn-danger">
                                    <li class="fa fa-file-pdf-o"></li>
                                </button>
                            </div>

                        </div>

                    </div>
                </div>
                <!-- End -->
            </div>
        </div>
</section>

<script>
    jQuery(document).ready(function() {
        //Load report criterias for default one
        var reporttype = $('#filterby_reporttype').val();
        var reportname = $("#filterby_reporttype option:selected").text();
        $('#current_reporttype').html(reportname);

    });

    function changeReportType(obj) {
        // var reporttype = $(obj).val();
        var reportname = $("#" + obj.id + " option:selected").text();
        $('#current_reporttype').html(reportname);
        $('#downloadpf').html('');
        // console.log(reportname);
        $('#generate-button').hide();
        if (reportname != 'EPF - Contribution') {
            $('#generate-button').show();
            $('#criterias').hide();
        } else {
            $('#generate-button').hide();
            $('#criterias').show();
        }
        $('#esi-pdf').hide();
        //edited by sinsiya on  14-08-2024
        if (reportname == 'ESI- Monthly Contribution') {
            $('#esi-pdf').show();
            $('#downloadpf').hide();
        }
    }

    function changeCriteria() {
        var reporttype = $('#report_component').val();

        if (reporttype != '') {
            $('#generate-button').show();
        } else {
            $('#generate-button').hide();
        }
    }

    function hidebut() {
        $('#downloadpf').html('');
    }

    // Edited by Akshay on 8-5-2026
    function download(mode) {

        var month = $('#filterby_month').val();
        var reporttype = $('#filterby_reporttype').val();
        var branch = $('#filterby_branch').val();
        var subcat = '';

        if (reporttype == 'epf_contr') {
            subcat = $('#report_component').val();
        }

        if (!branch || branch.length === 0) {
            alert('Please select a branch');
            return false;
        }

        $('#downloadpf').show();
        $('#downloadpf').html('Loading. please wait...');

        $.post(
            livesite + 'EsiEpfReport/generatereport', {
                type: reporttype,
                month: month,
                mode: mode,
                subcat: subcat,
                branch: branch
            },
            function(data) {
                $('#downloadpf').html(data);
            }
        );
    }
    // End

    // Edited by Akshay on 11-5-2026
    function downloadReport(mode) {

        var reporttype = $('#filterby_reporttype').val();
        var month = $('#filterby_month').val();
        var branch = $('#filterby_branch').val();
        var subcat = '';

        if (reporttype == 'epf_contr') {
            subcat = $('#report_component').val();
        }

        if (!branch || branch.length === 0) {
            alert('Please select branch');
            return false;
        }


        $('#downloadpf').show();
        $('#downloadpf').html('Loading. please wait...');

        var url = livesite + 'EsiEpfReport/generatereportPdf' +
            '?type=' + encodeURIComponent(reporttype) +
            '&month=' + encodeURIComponent(month) +
            '&mode=' + encodeURIComponent(mode) +
            '&subcat=' + encodeURIComponent(subcat) +
            '&branch=' + encodeURIComponent(branch);

        window.location.href = url;
        setTimeout(function() {
            $('#downloadpf').hide();
        }, 3000);
    }
    // End

    $(".home").on("click", function() {

        $("#container").isLoading({
            text: "Loading",
            position: "overlay",
        });

        let url = "";
        var userGroup = <?php echo json_encode($this->Session->read('user_group')); ?>;

        if (userGroup == "1") {
            url = livesite + "Report/index";
        } else if (userGroup == "2") {
            url = livesite + "EmployeeMenu/addon";
        }

        $("#container").load(url, function() {
            isDashboardShown = false;
        });

    });

    // Edited by Akshay on 12-5-2026
    $(document).ready(function() {

        // Prevent dropdown from closing
        $('.dropdown-menu').on('click', function(e) {
            e.stopPropagation();
        });

        // Select All
        $('#select_all_branch').change(function() {

            $('.branch_checkbox').prop(
                'checked',
                $(this).prop('checked')
            );

            updateBranchValues();
        });

        // Individual checkbox
        $('.branch_checkbox').change(function() {

            var total = $('.branch_checkbox').length;
            var checked = $('.branch_checkbox:checked').length;

            $('#select_all_branch').prop(
                'checked',
                total == checked
            );

            updateBranchValues();
        });

    });


    function updateBranchValues() {

        var selected = [];

        $('.branch_checkbox:checked').each(function() {

            selected.push($(this).val());

        });

        // Store comma separated values
        $('#filterby_branch').val(selected.join(','));

        // Update button text
        if (selected.length > 0) {

            $('#branchDropdownBtn').html(
                selected.length +
                ' Branch Selected' +
                '<span class="caret" style="position:absolute; right:10px; top:50%; margin-top:-2px;"></span>'
            );

        } else {

            $('#branchDropdownBtn').html(
                'Select Branch' +
                '<span class="caret" style="position:absolute; right:10px; top:50%; margin-top:-2px;"></span>'
            );
        }

        hidebut();
    }
    // End
</script>