<style>
    .form-horizontal .control-label {
        text-align: left;
    }

    .custom-file-upload {
        border: 1px solid #0d0c0c52;
        border-radius: 4px;
        display: inline-block;
        padding: 4px 12px;
        cursor: pointer;
        width: 100%;
        height: 30px;
        text-align: center;
    }

    .tabs-process {
        display: flex;
        justify-content: left;
        align-items: center;
        margin-top: 40px;
        margin-bottom: -5px;

    }

    .tabs-process .tab {
        background-color: white !important;
        padding: 15px 25px;
        border: 1px solid #1d615f !important;
        border-bottom: none !important;
        margin: 0px 0px;
    }

    .tabs-process .active {
        background-color: #1d615f !important;
        color: white;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    /* Dropdown container */
    .custom-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        background: #fff;
        border: 1px solid #ccc;
        border-radius: 6px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
        width: 150px;
        z-index: 1000;
    }

    /* Dropdown items */

    .dg-btn {
        background-color: #fff;
        border: 1px solid #ddd;
        color: #1e516e;
        font-weight: 500;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
    }

    .dg-btn:hover {
        background-color: #f8f8f8 !important;
        border-color: #ccc !important;
    }

    /* Filter Panel styling */
    /* Make the filter panel always appear near the button */
    #filterPanel {
        display: none;
        position: absolute;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 15px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        width: 200px;
    }


    #filterPanel .form-group {
        margin-bottom: 8px;
    }

    #filterPanel input[type="radio"] {
        margin-right: 6px;
    }

    #filterPanel label {
        font-weight: 500;
        color: #333;
        cursor: pointer;
    }


    /* Clear button */
    .btn-clear {
        background-color: #2a78c5;
        /* blue */
        color: #fff;
        border: none;
        border-radius: 4px;
        padding: 6px 12px;
        font-weight: 500;
        cursor: pointer;
        margin-top: 8px;
        width: 100%;
        text-align: center;
    }

    .btn-clear:hover {
        background-color: #205a99;
    }

    #filterPanel .form-group {
        display: flex;
        align-items: center;
        /* vertically centers radio + label */
        gap: 6px;
        margin-bottom: 8px;
    }

    #filterPanel .form-group label {
        margin: 0;
        font-size: 14px;
        /* optional: keep consistent text size */
        position: relative;
        top: 2px;
        /* 👈 moves label slightly downward */
    }


    /* End */
    /* edit by bindu 14-11-2025 */
    .heading {
        display: flex;
        flex-direction: row;
        align-items: center;
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

    /* edit by bindu 14-11-2025 end */

    /* Edited by Akshay on 19-11-2025 */
    .custom-btn {
        border-radius: 8px !important;
        font-weight: 600 !important;
        font-size: 13px;
        padding: 4px 16px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: 0.2s;
        border: 1px solid #e0e0e0 !important;
        color: #555;
        background-color: #fff;
    }

    .custom-btn:hover {
        background: #f5f5f5;
    }

    /* End */

    /* Edited by Akshay in 20-11-2025 */
    .input-group-text {
        position: absolute;
        top: 50%;
        left: 12px;
        transform: translateY(-50%);
        background-color: transparent !important;
        border: none !important;
        padding: 0 !important;
        z-index: 4;
    }

    .search-container {
        width: 325px;
        position: relative;
        margin-left: 15px !important;
        margin-right: 10px;
    }

    /* Custom Search Input */
    #searchqupo.form-control {
        /* background-color: #dfdfdf !important; */
        border-radius: 8px !important;
        border: 1px solid #aaa !important;
        height: 30px;
        padding-left: 40px;
        /* Space for icon */
        font-size: 14px;
        font-weight: 500;
    }

    .all-card {
        padding: 2px 70px;
        border-radius: 16px;
        box-shadow: 1px 1px 7px 1px #e2e2e2;
    }

    .all-card {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 20px;
        border-radius: 16px;
        box-shadow: 1px 1px 7px 1px #e2e2e2;
        background-color: #fff;
        width: 250px;
        /* width: 20%; */
        min-width: 220px;
        /* To avoid collapsing on small screens */
        transition: 0.3s ease;
    }

    .all-card:hover {
        box-shadow: 2px 2px 10px 2px #d1d1d1;
    }

    .card-content {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .all-card .title {
        font-size: 16px;
        margin: 0;
        color: #333;
    }

    .all-card .count {
        font-size: 18px;
        font-weight: bold;
        /* margin-top: 5px; */
        color: #008b9c;
        text-align: left;
    }

    .all-card i {
        font-size: 28px;
        color: #008b9c;
        margin-left: 15px;
    }

    .tooltip-container {
        position: relative;
        display: inline-block;
    }

    .tooltip-container .tooltip-text {
        visibility: hidden;
        width: 140px;
        /* adjust width as needed */
        background-color: #555;
        color: #fff;
        text-align: center;
        border-radius: 5px;
        padding: 5px 8px;
        position: absolute;
        z-index: 1;
        bottom: 95%;
        /* position above */
        left: 50%;
        transform: translateX(-50%);
        opacity: 0;
        transition: opacity 0.3s;
    }

    /* .bi .bi-search{
        color :grey;
    } */

    .tooltip-container .tooltip-text::after {
        content: '';
        position: absolute;
        top: 100%;
        /* arrow at bottom of tooltip */
        left: 50%;
        transform: translateX(-10%);
        border-width: 5px;
        border-style: solid;
        border-color: #555 transparent transparent transparent;
    }

    .tooltip-container:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
    }

.filter-active {
    background-color: #e0e0e0 !important; /* light grey */
    border-color: #bfbfbf !important;
}


    /* End */
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<section class="content">
    <!-- /* edit by bindu 14-11-2025 */ -->
    <!-- <div class="col-md-12"> -->
    <div class="row mt-2 mb-3">
        <!-- Right Side -->
        <div class="col-md-12 d-flex justify-content-end align-items-center" style="padding-left:75%;">
            <div class="input-group" style="width: 100%; margin-left:0; padding-right:15px;">
                <span class="input-group-text" style="border-radius:0 0 0 0;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                        class="bi bi-search" viewBox="0 0 16 16"
                        style="margin-left: 4px; margin-top: 4px;">
                        <path
                            d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 
                                3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1z
                                M12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                    </svg>
                </span>
                <input type="text" id="searchqupo" class="form-control" placeholder="Search by Employee Name...">
            </div>
        </div>
    </div>
    <!-- </div> -->
    <br>
    <div class="heading" style="padding-left: 15px; padding-right:15px;">
        <h1 class="text-primary-18">Salary Management</h1>
        <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
            <i class="fa" style="font-size:16px;">&#xf104;</i>
            Back
        </div>
    </div>
    <!-- /* edit by bindu 14-11-2025 end */ -->
    <!-- Dashboard -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin:20px 15px 0 15px;gap:10px;">


        <div class="all-card tooltip-container">
            <i id="employeeDueIcon" class="fa fa-clock-o" style="color:#008b9c;font-size:24px;"></i>
            <div class="tooltip-container">
                <h5 id="employeeDueHeading" style="font-size:16px;">Revision Due</h5>
                <h5 id="employeeDue" style="text-align:center;font-size:18px;"></h5>
            </div>
        </div>

        <div class="all-card tooltip-container">
            <i id='employeeOverDueIcon' class="fa fa-warning" style="color:rgb(155, 28, 28);font-size:24px;"></i>
            <div class="tooltip-container">
                <h5 id='employeeOverDueHeading' style="font-size:16px;">Revision Overdue</h5>
                <h5 id='employeeOverDue' style="text-align:center;font-size:18px;"></h5>
            </div>
        </div>

        <div class="all-card tooltip-container">
            <i class="fa fa-user-times" style="color:rgb(210, 114, 25);font-size:24px;"></i>
            <div class="tooltip-container">
                <h5 style="font-size:16px;">No Salary Structure</h5>
                <h5 id='employeeNoStructure' style="text-align:center;font-size:18px;"></h5>
            </div>
        </div>

    </div>

    <br>
    <div class="col-md-12">
        <!-- Employee import form -->


        <!-- End -->

        <!-- Edited by Akshay on 8-11-2025 -->
        <div class="tabs-process">
            <!-- Edited by Akshay on 11-10-2025 -->
            <div id="tabPending" class="tab active" onclick="switchTab('tabPending', 'tab0')">Pending</div>
            <!-- End -->
            <div id="tabNotProcessed" class="tab" onclick="switchTab('tabNotProcessed', 'tab1')" style="margin-left:5px;">Not Processed</div>
            <div id="tabProcessed" class="tab" onclick="switchTab('tabProcessed', 'tab2')" style="margin-left:5px;">Processed</div>
        </div>
        <!-- End -->

        <!-- Edited by Akshay on 11-10-2025 -->
        <div id="tab0" class="tab-content active">
        </div>
        <!-- End -->

        <div id="tab1" class="tab-content active">
            <!-- <p> Not Processed</p> -->
        </div>

        <div id="tab2" class="tab-content">
            <!-- <p>Processed</p> -->
        </div>


        <br>


        <!-- Edited by Akshay on 21-10-2025 -->
        <div id="customToolbar" style="margin-bottom:10px; display:flex; align-items:center; gap:8px;">
            <button id="uploadModal" class="dg-btn">
                <i class="fa fa-upload" style="color:#4caf50;"></i> Upload
            </button>
            <button id="salaryIncrementBtn" class="dg-btn">
                <i class="fa fa-pencil" style="color:#1e516e;"></i> Salary Update
            </button>

            <button id="salaryManagetBtn" class="dg-btn">
                <!-- First page -->
                <i class="fa fa-pencil" style="color:#1e516e;"></i> Salary Update
            </button>

            <button id="viewBtn" class="dg-btn">
                <i class="fa fa-info-circle" style="color:#1e516e;"></i> View
            </button>

            <!-- Filter Button -->
            <div style="display:inline-block; position:relative;">
                <button id="filterBtn" class="dg-btn">
                    <i class="fa fa-filter" style="color:#d27219;"></i> Filter
                </button>

                <!-- Filter Panel -->
                <div id="filterPanel" style="display:none; width:200px; position:absolute; top:100%; left:0; background:#fff; border:1px solid #ddd; border-radius:6px; padding:15px; box-shadow:0 2px 6px rgba(0,0,0,0.15); z-index:1000;">
                    <h5 style="margin-top:0; margin-bottom:12px; font-weight:600; color:#333;">Filter Employees</h5>

                    <div class="form-group">
                        <input type="radio" name="filtering" value="Due" id="filterResigned">
                        <label for="filterResigned">Due</label>
                    </div>

                    <div class="form-group">
                        <input type="radio" name="filtering" value="Overdue" id="filterActive">
                        <label for="filterActive">Overdue</label>
                    </div>

                    <div class="form-group">
                        <input type="radio" name="filtering" value="NoStructure" id="filterNotice">
                        <label for="filterNotice">No Structure</label>
                    </div>

                    <button id="clearFilter" class="btn-clear">Clear</button>
                </div>
            </div>

        </div>
        <!-- End -->

        <table id="att_table" class="table table-bordered table-hover">
            <tbody>
            </tbody>
        </table>
        <!-- </div> -->
        <!-- /.box-body -->
    </div>
</section>
<style type="text/css">
    .pws_tabs_list {
        min-height: 900px;
    }
</style>
<script>
    function switchTab(tabId, contentId) {
        // Remove active class from all tabs and contents
        document.querySelectorAll('.tab').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));

        // Add active class to clicked tab and corresponding content
        document.getElementById(tabId).classList.add('active');
        document.getElementById(contentId).classList.add('active');
        $('#filterBtn').removeClass('filter-active');

        // Determine status from contentId
        let status = '';
        if (contentId === 'tab0') {
            status = 'Pending';
            $('#salaryManagetBtn, #filterBtn, #statusList').show(); // Edited by Akshay on 11-10-2025
            $('#salaryIncrementBtn').hide();
            $('#uploadModal').show();
            $('#viewBtn').hide();
            $('#filterPanel input[name="filtering"]').prop('checked', false); // Edited by Akshay on 21-10-2025
            $('#att_table').datagrid('showColumn', 'next_increment_date');
            $('#att_table').datagrid('showColumn', 'salary_structure');
            $('#att_table').datagrid('hideColumn', 'created_date');
            $('#att_table').datagrid('hideColumn', 'download');
            $('#att_table').datagrid('hideColumn', 'modification_date');
        } else if (contentId === 'tab1') {
            status = 'Not Processed';
            $('#salaryManagetBtn, #filterBtn, #statusList').hide(); // Edited by Akshay on 11-10-2025
            $('#salaryIncrementBtn').show(); // Show when "Not Processed"
            $('#uploadModal').show();
            $('#viewBtn').show();
            // Edited by Akshay on 9-10-2025
            $('#att_table').datagrid('showColumn', 'created_date');
            $('#att_table').datagrid('showColumn', 'salary_structure');
            $('#att_table').datagrid('hideColumn', 'download');
            $('#att_table').datagrid('hideColumn', 'modification_date');
            $('#att_table').datagrid('hideColumn', 'next_increment_date');
            // End
        } else if (contentId === 'tab2') {
            status = 'Processed';
            $('#salaryManagetBtn, #filterBtn, #statusList').hide(); // Edited by Akshay on 11-10-2025
            $('#viewBtn').show();
            $('#salaryIncrementBtn').hide(); // Hide when "Processed"
            $('#uploadModal').hide();
            // Edited by Akshay on 9-10-2025
            $('#att_table').datagrid('showColumn', 'modification_date');
            $('#att_table').datagrid('showColumn', 'download');
            $('#att_table').datagrid('hideColumn', 'created_date');
            $('#att_table').datagrid('hideColumn', 'salary_structure');
            $('#att_table').datagrid('hideColumn', 'next_increment_date');
            // End
        }


        // Reload datagrid with corresponding status
        // Edited by Akshay on 11-10-2025
        var url = (status === 'Pending') ? livesite + "SalaryIncrement/employeelistPending" : livesite + "SalaryIncrement/employeelist";
        $('#att_table').datagrid('options').url = url;
        // End
        var employee = $('#searchqupo').val();
        $('#att_table').datagrid('load', {
            emp: employee,
            status: status
        });
    }


    function handleFileSelect() {
        const input = document.getElementById('empctccsv');
        const label = document.getElementById('selectedFileName');
        if (input.files.length > 0) {
            label.textContent = input.files[0].name;
            uploadEmployeeCTC(); // Trigger upload function
        } else {
            label.textContent = '';
        }
    }

    function filterEmployees() {


        var branch = $('#filterby_branch').val();
        var structure = $('#approved_bys').val();
        var fileInput = $('#empctccsv');
        if ($('#approved_bys').val() !== '') {
            fileInput.prop('disabled', false);
        } else {
            fileInput.prop('disabled', true);
        }

        $("#emp_fkey_2").select2({
            placeholder: "--Select--",
            allowClear: true,
            ajax: {
                url: livesite + "SalaryIncrement/jsons/" + branch,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term,
                        page: params.page
                    };
                },
                processResults: function(data, params) {
                    var selectElement = this.$element;
                    var selectId = selectElement.attr('id');

                    params.page = params.page || 1;

                    return {
                        results: data.items,
                        pagination: {
                            more: (params.page * 30) < data.total_count
                        }
                    };
                }
            },
            escapeMarkup: function(markup) {
                return markup;
            }
        });
    }

    function startdatcheck() {
        var startDate = new Date($('#time').val());
        var endDate = new Date($('#out_time').val());

        if (startDate > endDate) {
            alert("expected starting date should be less than ending date");
            $("#time").val('');
        }
    }

    function enddatecheck() {
        var startDate = new Date($('#time').val());
        var endDate = new Date($('#out_time').val());

        if (startDate > endDate) {
            alert("expected ending date should be greater than starting date");
            $("#out_time").val('');
        }
    }
    $('#time').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
        startView: "days",
        minViewMode: "days"
    })

    $('#out_time').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
    })
    $("#out_date").inputmask("yyyy-mm-dd");
    $('#y').on('click', function() {

        if (this.value === 'Y') {
            $('#date').css("display", "block");
        }
    });
    $('#z').on('click', function() {

        if (this.value === 'N') {
            $('#date').css("display", "none");
        }
    });
    // AMAL END
    function startdateeffect() {
        var startDateeffect = new Date($('#effect').val());
        var endDateeffect = new Date($('#end_time').val());

        if (startDateeffect > endDateeffect) {
            alert("expected starting date should be less than ending date");
            $("#effect").val('');
        }
    }

    function enddatecheck() {
        var startDateeffect = new Date($('#effect').val());
        var endDateeffect = new Date($('#end_time').val());

        if (startDateeffect > endDateeffect) {
            alert("expected ending date should be greater than starting date");
            $("#end_time").val('');
        }
    }
    $('#effect').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        startView: "months",
        minViewMode: "months"
    })

    $('#end_time').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
    })
    $("#end_date").inputmask("yyyy-mm-dd");


    // Edited by Akshay on 8-10-2025
    function downloadSalary(salary_hike_pkey) {
        const url = `${livesite}SalaryIncrement/download/${salary_hike_pkey}`;
        window.location.href = url; // simplest way to trigger the download
    }





    // End

    function uploadEmployeeCTC() {


        var form = $('#importemployeectcform');
        var fileSelect = document.getElementById('empctccsv');
        var ctcuploadtype = ($('#ctc_upload_type').val() === undefined) ? 2 : $('#ctc_upload_type').val();
        var files = fileSelect.files;
        var approvedBy = $('#approved_bys').val();


        if (files.length == 0) {
            $.notify("please choose any file to upload!", {
                type: 'danger',
                allow_dismiss: false,
                z_index: 9999
            });
            return false;
        }


        var files = fileSelect.files;

        var formData = new FormData();

        for (var i = 0; i < files.length; i++) {
            var file = files[i];

            formData.append('empctc[]', file, file.name);

        }

        var xhr = new XMLHttpRequest();

        //Edited by Akshay on 26-6-2025
        var selectedValue = $('input[name="monthly"]:checked').val(); // get current selection
        var endpoint = (selectedValue !== 'gross') ? 'uploadandsaveempctcitem' : 'uploadandsaveempctc';

        xhr.open(
            'POST',
            livesite + 'SalaryIncrement/' + endpoint + '/' + ctcuploadtype + '/' + approvedBy,
            true
        );
        // End

        xhr.onload = function() {
            if (xhr.status === 200) {
                $('#empctccsv').val("");
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    $('#att_table').datagrid('reload');
                    $.notify(response.msg, {
                        type: 'success',
                        allow_dismiss: false,
                        z_index: 9999
                    });
                } else {
                    $('#att_table').datagrid('reload');
                    $.notify(response.msg, {
                        type: 'error',
                        allow_dismiss: false,
                        z_index: 9999
                    });
                }
            } else {
                alert("Employee Gross Salary import failed, please check informations given or try again.");
            }
        };

        $('#empctccsv').val('');
        $("#filterby_branch").select2("val", "");
        $("#emp_fkey_2").select2("val", "");

        xhr.send(formData);
    }


    jQuery(document).ready(function() {

        filterEmployees();

        // Edited by Akshay on 20-11-2025
        let searchTimer;
        $('#searchqupo').on('input', function() {
            clearTimeout(searchTimer);
            $('#filterBtn').removeClass('filter-active');
            let searchValue = $(this).val().trim();

            searchTimer = setTimeout(function() {
                $('#att_table').datagrid('load', {
                    emp: searchValue // send name search param
                });
            }, 300); // wait 300ms after typing stops
        });
        // End


        // Open modal on Upload button click
        $('#uploadModal').click(function() {
            showLargeModalForm(livesite + 'SalaryIncrement/fileUpload');
        });

        // Edited by Akshay on 19-11-2025
        $('#salaryIncrementBtn').on('click', function() {
            showLargeModalForm(livesite + 'SalaryIncrement/salaryIncrementForm');
        });
        // End

        var employee = $('#attendanceuploadfilter #emp_fkey_2').val();
        var branch = $('#attendanceuploadfilter #filterby_branch').val();
        var structure = $('#importcomponents #approved_bys').val();

        // Edited by Akshay on 21-10-2025
        let prevSelectedIndex = null;
        // Initialize DataGrid without toolbar
        $('#att_table').datagrid({
            url: livesite + "SalaryIncrement/employeelistPending",
            pagination: true,
            autoRowHeight: false,
            singleSelect: true,
            PostsearchFilter: true,
            rownumbers: true,
            pageSize: 10,
            width: '100%',
            queryParams: {
                status: 'Pending'
            },
            fitColumns: true,
            pageList: [2, 5, 10],
            columns: [
                [{
                        field: 'empname',
                        title: 'Employee Name',
                        width: "20%"
                    },
                    {
                        field: 'empid',
                        title: 'Employee ID',
                        width: "15%"
                    },
                    {
                        field: 'next_increment_date',
                        title: 'Next Increment Date',
                        width: "18%"
                    },
                    {
                        field: 'created_date',
                        title: 'Created Date',
                        width: "18%"
                    },
                    {
                        field: 'modification_date',
                        title: 'Processed Date',
                        width: "30%"
                    },
                    {
                        field: 'branch',
                        title: 'Branch',
                        width: '18%'
                    },
                    {
                        field: 'salary_structure',
                        title: 'Salary Structure',
                        width: '28%'
                    },
                    {
                        field: 'download',
                        title: 'Download',
                        width: '16%',
                        formatter: function(value, row, index) {
                            return `<div style="text-align:center;">
                  <a class="resumebutton" href="javascript:void(0);" onclick="downloadSalary('${row.salary_hike_pkey}')">
                    <li class="fa fa-file-excel-o"></li>
                  </a>
                </div>`;
                        }
                    }
                ]
            ],
            rowStyler: function(index, row) {
                if (row.status === 'NoStructure') {
                    return 'background-color: #f08080; color: white;';
                } else if (row.status === 'Overdue') {
                    return 'color: red;';
                }
            },
            // Edited by Akshay on 22-10-2025
            onSelect: function(index, row) {
                const panel = $(this).datagrid('getPanel');

                // Restore previous row color
                if (prevSelectedIndex !== null && prevSelectedIndex !== index) {
                    const prevRow = $(panel).find(`tr[datagrid-row-index="${prevSelectedIndex}"]`);
                    const prevData = $(this).datagrid('getRows')[prevSelectedIndex];

                    if (prevData.status === 'NoStructure') {
                        prevRow.css('background-color', '#f08080').css('color', 'white');
                    } else if (prevData.status === 'Overdue') {
                        prevRow.css('background-color', '').css('color', 'red');
                    } else {
                        prevRow.css('background-color', '').css('color', '');
                    }
                }

                // Apply selection style to current row
                const tr = $(panel).find(`tr[datagrid-row-index="${index}"]`);
                tr.css('background-color', '#3399ff').css('color', 'white');

                prevSelectedIndex = index;
            },

            onLoadSuccess: function(data) {
                $.ajax({
                    url: livesite + "SalaryIncrement/getSummaryValue",
                    type: "POST",
                    dataType: "json",
                    success: function(result) {
                        $('#employeeNoStructure').text(result.pending);
                        $('#employeeDue').text(result.due);
                        $('#employeeOverDue').text(result.over_due); // corrected key
                    }
                });
            }


            // End
        });

        // 🔹 Button Handlers
        $('#salaryIncrementBtn').on('click', function() {
            showLargeModalForm(livesite + 'SalaryIncrement/salaryIncrementForm');
        });

        $('#salaryManagetBtn').on('click', function() {
            var row = $('#att_table').datagrid('getSelected');
            if (!row) {
                alert('Please select an employee first!');
                return;
            }
            showLargeModalForm(
                livesite + 'SalaryIncrement/salaryIncrementForm' +
                '?emp_fkey=' + encodeURIComponent(row.emp_fkey) +
                '&status=' + encodeURIComponent(row.status)
            );
        });

        $('#viewBtn').on('click', function() {
            var row = $('#att_table').datagrid('getSelected');
            if (!row) {
                alert('Please select a row to process.');
                return;
            }
            showLargeModalForm(
                livesite + 'SalaryIncrement/viewSalaryIncrementForm/' +
                row.salary_hike_pkey + '/' + encodeURIComponent(row.empname.trim())
            );
        });

        // 🔹 Filter change event
        $('#statusFilter').on('change', function() {
            $('#att_table').datagrid('load', {
                status: $(this).val()
            });
        });

        // End 

        $('#viewBtn').hide();
        $('#salaryIncrementBtn').hide();
        $('#att_table').datagrid('hideColumn', 'download');
        $('#att_table').datagrid('hideColumn', 'modification_date');
        $('#att_table').datagrid('hideColumn', 'created_date'); // Edited by Akshay on 11-10-2025




        // Edited by Akshay on 21-10-2025
        // Toggle dropdown on button click
        $('#filterBtn').on('click', function(e) {
            e.stopPropagation();
            var btnOffset = $(this).offset();
            var btnHeight = $(this).outerHeight();
            $('#filterPanel').css({
                // top: btnOffset.top + btnHeight + 4 + 'px',
                top: '0px',
                left: (btnOffset.left / 2.5) + 'px'
            }).toggle();
        });

        // Hide filter panel if clicked outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#filterPanel, #filterBtn').length) {
                $('#filterPanel').hide();
            }
        });

        // Apply filter on radio selection
        $('#filterPanel input[name="filtering"]').on('change', function() {
            $('#searchqupo').val('');
            $('#filterBtn').addClass('filter-active'); // show hint
            var value = $(this).val();
            $('#att_table').datagrid('load', {
                status: value
            });
            $('#filterPanel').hide();
        });

        // Clear filter
        $('#clearFilter').on('click', function() {
            $('#filterPanel input[name="filtering"]').prop('checked', false);
            $('#att_table').datagrid('load', {
                status: ''
            });
            $('#filterBtn').removeClass('filter-active'); // remove hint
            $('#filterPanel').hide();
        });
        // End

    });


    $(".home").on("click", function () {

    $("#container").isLoading({
        text: "Loading",
        position: "overlay",
    });

    let url = "";
    var userGroup = <?php echo json_encode($this->Session->read('user_group')); ?>;

    if (userGroup == "1") {
        url = livesite + "SalaryProcessing/index";
    } 
    else if (userGroup == "2") {
        url = livesite + "EmployeeMenu/addon";
    }

    $("#container").load(url, function () {
        isDashboardShown = false;
    });

});
</script>