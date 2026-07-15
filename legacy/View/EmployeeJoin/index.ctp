<style>
    /* --- Google Font Import ---

    /* --- General Page Styling --- */
    /* body {
        background-color: #f0f2f5;
        
    }

    */
    /* .tab-content {
        font-family: 'Source Sans Pro', 'Helvetica Neue', Helvetica, Arial, sans-serif;
    } */

    /* Default icon colors */
    a[title="Edit"] {
        color: #1e516e;
    }

    a[title="Delete"] {
        color: #e63946;
    }

    .datagrid-row-selected {
        background-color: #c0c0c0ff !important;
        color: black !important;
    }

    .panel {
        margin-top: 24px;
        margin-left: 20px;
    }


    /* ALL EMPLOYEES */

    /* card */
    .all-card {
        padding: 2px 70px;
        /* margin: 2px 40px; */
        border-radius: 16px;
        box-shadow: 1px 1px 7px 1px #e2e2e2;
        /* display:flex;
    align-items:center;
    justify-content: space-between; */
        /* margin-left:20px; */

    }

    /* .all-card i{
    margin-right:30px;
} */
    #tab_1-1 h2 {
        font-size: 20px;
        font-weight: 400;
        color: #525252;
        margin-left: 20px;
    }

    .buttons {
        margin-top: 36px;
        margin-left: 20px;
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

    #filterPanel {
        border-radius: 8px;
    }

    .panel {
        margin-top: 24px;
        padding-right: 20px;
    }

    /* --- Top Controls Section (Search, Buttons) --- */

    /* Flexbox container for the top controls */
    .custom-controls-container {
        display: flex;
        justify-content: start;
        align-items: center;
        flex-wrap: wrap;
        margin: 25px 20px;
        /* margin-right: 15px; */
    }

    .search-container {
        width: 325px;
        position: relative;
        margin-left: 15px !important;
        margin-right: 15px;
    }

    .buttons-container {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .datagrid-cell {
        white-space: normal !important;
        word-break: break-word !important;
        overflow-wrap: break-word !important;
    }

    .employee_join .form-control:focus {
        outline: none;
        border-color: none;
        box-shadow: none;
    }

    /* Custom Search Input */
    .employee_join #searchqupo.form-control {
        /* background-color: #dfdfdf !important; */
        border-radius: 8px !important;
        border: 1px solid #aaa !important;
        height: 30px;
        padding-left: 40px;
        /* Space for icon */
        font-size: 14px;
        font-weight: 500;
    }

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

    /* Action Buttons (Link, Import, Add Employee, etc.) */
    .import,
    .download,
    .upload,
    .add_employee,
    .onboarding {
        border-radius: 8px !important;
        font-weight: 500 !important;
        font-size: 14px;
        padding: 4px 15px;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
    }

    /* Secondary buttons */
    .import,
    .download,
    .upload {
        background-color: #fff !important;
        border: 1px solid #e0e0e0 !important;
        color: #555 !important;
    }

    .import:hover,
    .download:hover,
    .upload:hover {
        background-color: #f8f8f8 !important;
        border-color: #ccc !important;
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

    /* Primary "+ Add Employee" button */
    .add_employee,
    .onboarding {
        background-color: #1e516e !important;
        color: white !important;
        border: none !important;
        /* margin-right: 14px; */
    }

    .add_employee:hover,
    .onboarding:hover {
        background-color: #143d52 !important;
    }

    label.upload {
        margin: 0;
    }

    td[field="avatar"] .datagrid-cell {
        display: flex;
        align-items: center;
        justify-content: center;
        /* height: auto; */
        padding: 3px 0;


    }

    td[field="avatar"] img {
        width: 15px !important;
        margin-left: 0px !important;
        border-radius: 50% !important;
    }

    .heading {
        display: flex;
        flex-direction: row;
        align-items: end;
        justify-content: space-between;
        margin-left: 20px;
    }

    .home {
        background-color: #ffffffff;
        border-radius: 50px;
        padding: 2px 15px;
        color: #1e516e !important;
        margin-right: 15px;
        color: white;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        border: #1e516e 1px solid;
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

    /* #emptable2{
        margin:0 15px 0 0;
    } */
    .filter-option {
        display: flex;
        align-items: center;
        /* vertical alignment */
        margin-bottom: 15px;
    }

    .filter-option input[type="radio"] {
        margin: 0 8px 0 0;
        /* remove top/bottom gap, add right space */
    }

    .filter-option label {
        margin: 0;
        font-weight: normal;
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

    .tab-content {
        max-width: 120%;
        max-height: 120%;
        overflow-x: hidden;
        /* Default: no scroll */
        overflow-y: hidden;
    }

    @media (max-width: 1000px) {
        .tab-content {
            overflow-x: scroll;
            /* Enable horizontal scroll */
        }
    }

</style>

<div class="employee_join">
    <div style="display:flex; align-items:center; justify-content:space-between;border-bottom:1px solid #dddddd;">
        <ul class="nav nav-tabs mb-0" style="border-bottom:none;">
            <li class="active">
                <a href="#tab_1-1" data-toggle="tab">All Employees</a>
            </li>
            <li>
                <a href="#tab_1-2" data-toggle="tab">Employee Joining</a>
            </li>
        </ul>

        <div class="search-container d-flex align-items-center ms-auto" style="margin-left:auto;">
            <span class="input-group-text" id="basic-addon1" style="border-radius:0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" style="margin-top: 8px;"
                    class="bi bi-search" viewBox="0 0 16 16">
                    <path
                        d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 
                    3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1z
                    M12 6.5a5.5 5.5 0 1 1-11 
                    0 5.5 5.5 0 0 1 11 0" />
                </svg>
            </span>
             <input type="text" id="searchqupo" class="form-control" 
       placeholder="Search by Employee Name or ID...." 
       aria-label="Search">
        </div>
    </div>

    <div class="tab-content">
        <section id="tab_1-1" class="tab-pane fade in active">
            <div class="heading">
                <h1 class="text-primary-18">All Employee</h1>
                <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                    <i class="fa" style="font-size:16px;">&#xf104;</i>
                    Back
                </div>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin:20px 15px 0 15px;gap:10px;">

                <div class="all-card tooltip-container">

                    <i class="fa fa-users" style="color:#008b9c;font-size:24px;"></i>
                    <div class="tooltip-container">
                        <h5 style="font-size:16px;">Total Count</h5>
                        <h5 style="text-align:center;font-size:18px;"><?php echo $totalEmployeeCount; ?></h5>
                    </div>
                </div>


                <div class="all-card tooltip-container">
                    <i id="employeeCardIcon" class="fa fa-user-plus" style="color:#008b9c;font-size:24px;"></i>
                    <div class="tooltip-container">
                        <h5 id="employeeCardHeading" style="font-size:16px;">Active Employees</h5>
                        <h5 id="employeeCardCount" style="text-align:center;font-size:18px;"><?php echo $activeEmployeeCount; ?></h5>
                    </div>
                </div>


                <div class="all-card tooltip-container" id="downloadMissingSalary">

                    <i class="fa fa-user-times" style="color:rgb(210, 114, 25);font-size:24px;"></i>
                    <div class="tooltip-container">
                        <h5 style="font-size:16px;">No Salary Structure</h5>
                        <span class="tooltip-text">Click to Download</span>
                        <h5 style="text-align:center;font-size:18px;"><?php echo $missing_salary; ?></h5>
                    </div>
                </div>

                <div class="all-card tooltip-container" id="joiningthismonth">
                    <i class="fa fa-user-plus" style="color:rgb(155, 28, 28);font-size:24px;"></i>
                    <div class="tooltip-container">
                        <h5 style="font-size:16px;">Joined This Month</h5>
                        <span class="tooltip-text">Click to Download</span>
                        <h5 style="text-align:center;font-size:18px;"><?php echo $joinedThisMonth; ?></h5>
                    </div>
                </div>

            </div>

            <div style="display:flex;justify-content:space-between;" class="buttons-div">
                <div class="buttons">
                    <!-- <button id="btnEdit"><i class="fa fa-pencil" aria-hidden="true" style="color:#1e516e;"></i>Edit</button> -->
                    <button id="btnEdits"><i class="fa fa-pencil" aria-hidden="true" style="color:#1e516e;"></i>View</button>
                    <button id="btnRemove">
                        <i class="fa fa-times" aria-hidden="true" style="color:#dc1010;"></i> Remove
                    </button>

                    <button id="btnActivate" style="display:none;">
                        <i class="fa fa-check" aria-hidden="true" style="color:green;"></i> Activate
                    </button>
                    <button id="btnInfoChange"><i class="fa fa-info-circle" aria-hidden="true" style="color:#4caf50;"></i>History</button>
                    <input type="hidden" id="rsndempid" value="0" name="resigned">
                    <button id="filterBtn"><i class="fa fa-filter" aria-hidden="true" style="color:#d27219;"></i>Filter</button>
                </div>
                <div style="margin-top:36px;margin-right: 15px;">
                    <select id="search_emp_branch" name="search_emp_branch" class="form-control js-example-basic-single" onchange="filterEmployeesIndex()">
                        <option value="">Select Branch</option>
                        <?php
                        foreach ($arr_branches as $key => $value) {
                            echo '<option value="' . $value['branch_code'] . '">' . $value['branch_name'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>

            <table id="emptable2" class="table table-bordered table-hover">

            </table>
        </section>

        <section id="tab_1-2" class="tab-pane fade">
            <section class="heading" style="margin: 0 15px 0 18px;">
                <h1 class="text-primary-18">Employee Joining</h1>
                <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;margin:0;">
                    <i class="fa" style="font-size:16px;">&#xf104;</i>
                    Back
                </div>
            </section>

            <div class="custom-controls-container">

                <div class="buttons-container">

                    <button class="import" onclick="showModalForm(livesite + 'Employee/importProfile')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="12" fill="#28a745" viewBox="0 0 16 16">
                            <path d="M8.5 11.5a.5.5 0 0 1-1 0V7.707L6.354 8.854a.5.5 0 1 1-.708-.708l2-2a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1-.708.708L8.5 7.707z" />
                            <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                        </svg>
                        Import
                    </button>

                    <button class="download" onclick="downloadexcelformat();">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="12" fill="#007bff" class="bi bi-download" viewBox="0 0 16 16">
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5" />
                            <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z" />
                        </svg>
                        Download file
                    </button>

                    <label for="empdatacsv" class="upload">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="12" fill="#6f42c1" class="bi bi-upload" viewBox="0 0 16 16">
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5" />
                            <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708z" />
                        </svg>
                        Upload file
                    </label>
                    <input type="file" id="empdatacsv" name="empdatacsv" accept=".xlsx" style="display:none;" onchange="uploadEmployeeData();" />

                    <button class="add_employee">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="14" fill="#ffffffff" class="bi bi-plus-lg" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2" />
                        </svg>
                        Add Employee
                    </button>

                    <button class="onboarding">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="14" fill="#ffffff" class="bi bi-person-add" viewBox="0 0 16 16">
                            <path d="M8 7a3 3 0 1 0-2.995-3A3 3 0 0 0 8 7z" />
                            <path d="M2 14s-1 0-1-1 1-4 7-4 7 3 7 4-1 1-1 1H2z" />
                            <path d="M13.5 5.5a.5.5 0 0 1 .5-.5H16v1h-2v2h-1V6h-2V5h2v-2h1v2z" />
                        </svg>
                        OnBoarding
                    </button>


                </div>
            </div>
            <section class="content" style="padding:0;">

                <table id="emptable"></table>
            </section>
        </section>
    </div>
</div>

<div id="filterPanel" style="display:none; position:absolute; background:#fff; border:1px solid #ccc; padding:15px; box-shadow:0 2px 5px rgba(0,0,0,0.2); z-index:1000; width:250px;">
    <h5 style="margin-top:0; margin-bottom:20px;"><b>Filter Employees</b></h5>

    <div class="form-group filter-option">
        <input type="radio" name="filtering" value="2" id="filterResigned">
        <label for="filterResigned">Resigned</label>
    </div>

    <div class="form-group filter-option">
        <input type="radio" name="filtering" value="active" id="filterActive" checked>
        <label for="filterActive">Active</label>
    </div>

    <div class="form-group filter-option">
        <input type="radio" name="filtering" value="notice" id="filterNotice">
        <label for="filterNotice">Notice Period</label>
    </div>
 <!-- edited by BINDU 27-03-2026 -->
<div class="form-group filter-option">
    <input type="radio" name="filtering" value="this_month" id="filterThisMonth">
    <label for="filterThisMonth">This Month</label>
</div>

<div class="form-group filter-option">
    <input type="radio" name="filtering" value="previous_month" id="filterPreviousMonth">
    <label for="filterPreviousMonth">Previous Month</label>
</div>
 <!-- edited by BINDU 27-03-2026 END -->

</div>





<script>
    $(document).on("click", "#downloadMissingSalary", function() {
        window.location.href = livesite + "EmployeeJoin/downloadMissingSalary";
    });
    $(document).on("click", "#joiningthismonth", function() {
        window.location.href = livesite + "EmployeeJoin/thisMonthJoining";
    });
    jQuery(document).ready(function() {


        $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            var targetTab = $(this).attr('href');
            console.log('Target tab switched to:', targetTab);


            $('#searchqupo').val('');
            lastSearchValue = '';
            alertShown = false;

            if (targetTab === '#tab_1-2') {

                setTimeout(function() {
                    $('#emptable').datagrid('resize');
                    $('#emptable').datagrid('reload', {
                        emp: '',
                        branch: currentBranch,
                        filter: currentFilter
                    });
                }, 200);
            } else if (targetTab === '#tab_1-1') {

                setTimeout(function() {
                    $('#emptable2').datagrid('resize');
                    $('#emptable2').datagrid('reload', {
                        emp: '',
                        branch: currentBranch,
                        filter: currentFilter
                    });
                }, 200);
            }
        });
        // CLEAN, STABLE employee search + filter + tabs script
        (function($) {
            // Config / state
            let searchTimer = null;
            let lastSearchValue = '';
            let alertShown = false;
            let currentFilter = 'active';
            let currentBranch = '';
            let requestCounter = 0;
            let tableRequestId = {
                '#emptable': 0,
                '#emptable2': 0
            };
            let loadResults = {
                '#emptable': null,
                '#emptable2': null
            };

            // Helper: bind datagrid onLoadSuccess safely
            function bindDataGrid(tableId) {
                try {
                    $(tableId).datagrid({
                        onLoadSuccess: function(data) {
                            const reqId = $(tableId).data('reqId') || 0;
                            console.log(`[${tableId}] onLoadSuccess reqId=${reqId}`, data);
                            if (reqId === tableRequestId[tableId]) {
                                handleDataLoad(data, tableId);
                            } else {
                                console.log(`[${tableId}] stale response ignored (reqId=${reqId}, latest=${tableRequestId[tableId]})`);
                            }
                        }
                    });
                } catch (e) {
                    console.warn('bindDataGrid error for', tableId, e);
                }
            }

            // Start a load for a table: return new reqId
            function startTableLoad(tableId) {
                requestCounter++;
                tableRequestId[tableId] = requestCounter;
                $(tableId).data('reqId', requestCounter);
                loadResults[tableId] = null;
                console.log(`[${tableId}] start load reqId=${requestCounter}`);
                return requestCounter;
            }

            // Load helper that triggers datagrid load + reload and sets reqId
            function loadTableWithParams(tableId, params) {
                const reqId = startTableLoad(tableId);
                try {
                    $(tableId).datagrid('load', params);
                    // small safety reload
                    setTimeout(() => {
                        try {
                            $(tableId).datagrid('reload');
                        } catch (e) {
                            /*ignore*/
                        }
                    }, 50);
                    // ensure reqId stored on DOM
                    $(tableId).data('reqId', reqId);
                } catch (e) {
                    console.warn('loadTableWithParams failed for', tableId, e);
                }
            }

            // Handle data load for latest response
            function handleDataLoad(data, tableId) {
                const hasRows = !!(data && data.rows && data.rows.length > 0);
                loadResults[tableId] = hasRows;
                console.log(`${tableId} -> hasRows=${hasRows}`);
                checkAllResultsIfReady();
            }

            // When both tables have responded, decide if we show alert and/or reset
            function checkAllResultsIfReady() {
                const a = loadResults['#emptable'];
                const b = loadResults['#emptable2'];
                console.log('checkAllResultsIfReady', {
                    a,
                    b,
                    lastSearchValue,
                    alertShown
                });
                if (a === null || b === null) return; // still waiting

                const noData = !a && !b;
                if (noData && lastSearchValue !== '' && !alertShown) {
                    //   alertShown = true;
                    //   console.log('No Employee Found — showing alert and resetting search');
                    //   alert('No Employee Found');
                    //   setTimeout(() => {
                    //     $('#searchqupo').val('');
                    //     lastSearchValue = '';
                    //     // safe reload both tables with cleared search
                    //     reloadDataGrids();
                    //     alertShown = false;
                    //   }, 400);
                }

                // reset for next round
                loadResults['#emptable'] = null;
                loadResults['#emptable2'] = null;
            }

            // Unified reload for both tables using current state
            function reloadDataGrids() {
                console.log('reloadDataGrids', {
                    lastSearchValue,
                    currentFilter,
                    currentBranch
                });
                // ensure handlers are bound (idempotent)
                bindDataGrid('#emptable');
                bindDataGrid('#emptable2');

                // reset load markers
                loadResults['#emptable'] = null;
                loadResults['#emptable2'] = null;

                const params = {
                    emp: lastSearchValue || '',
                    branch: currentBranch,
                    filter: currentFilter
                };

                loadTableWithParams('#emptable', params);
                loadTableWithParams('#emptable2', params);
            }

            // safe reload both without params (used rarely)
            function safeReloadBoth() {
                tableRequestId['#emptable'] = 0;
                tableRequestId['#emptable2'] = 0;
                loadResults['#emptable'] = null;
                loadResults['#emptable2'] = null;
                bindDataGrid('#emptable');
                bindDataGrid('#emptable2');
                setTimeout(() => {
                    try {
                        $('#emptable').datagrid('reload');
                    } catch (e) {}
                    try {
                        $('#emptable2').datagrid('reload');
                    } catch (e) {}
                }, 120);
            }

            // Input sanitization & debounce
            $('#searchqupo').off('input').on('input', function() {
                clearTimeout(searchTimer);
                let raw = $(this).val();
                // allow letters, numbers, spaces only
                let cleaned = raw.replace(/[^A-Za-z0-9 -]/g, '');
                if (raw !== cleaned) $(this).val(cleaned);

                // If raw had only special chars, avoid setting lastSearchValue to empty weirdly
                if (raw.trim() !== '' && cleaned.trim() === '') {
                    console.log('only special characters typed — ignoring');
                    return;
                }

                if (lastSearchValue === cleaned) return;
                lastSearchValue = cleaned;

                searchTimer = setTimeout(() => {
                    reloadDataGrids();
                }, 300);
            });

            // Filter change handler (Active / Notice / Resigned (2))
            $('input[name="filtering"]').off('change').on('change', function() {
                currentFilter = $(this).val();
                updateHeading(currentFilter);
                $('#search_emp_branch').val('');

                // Update branch variable also
                currentBranch = '';
                // Clear search when switching filters? (keeps behaviour consistent)
                // If you prefer to keep search across filters, comment out the next two lines
                $('#searchqupo').val('');
                lastSearchValue = '';
                reloadDataGrids();
            });

            // Branch change handler
            $('#search_emp_branch').off('change').on('change', function() {
                currentBranch = $(this).val();
                reloadDataGrids();
            });

            // Tab switch: resize & reload appropriate table
            $('a[data-toggle="tab"]').off('shown.bs.tab').on('shown.bs.tab', function(e) {
                const targetTab = $(this).attr('href');
                console.log('Target tab switched to:', targetTab);
                // clear search on tab change (keeps UI simple)
                $('#searchqupo').val('');
                lastSearchValue = '';
                alertShown = false;
                // reload the relevant table(s)
                if (targetTab === '#tab_1-2') {
                    setTimeout(() => {
                        try {
                            $('#emptable').datagrid('resize');
                        } catch (e) {}
                        loadTableWithParams('#emptable', {
                            emp: '',
                            branch: currentBranch,
                            filter: currentFilter
                        });
                    }, 200);
                } else if (targetTab === '#tab_1-1') {
                    setTimeout(() => {
                        try {
                            $('#emptable2').datagrid('resize');
                        } catch (e) {}
                        loadTableWithParams('#emptable2', {
                            emp: '',
                            branch: currentBranch,
                            filter: currentFilter
                        });
                    }, 200);
                } else {
                    // default: reload both
                    reloadDataGrids();
                }
            });

            // update heading + icon based on filter
            function updateHeading(filterValue) {
                let headingText = '';
                let iconClass = '';
                if (filterValue === '2') {
                    headingText = 'Resigned Employees';
                    iconClass = 'fa fa-user-times';
                    $('#btnActivate').show();
                    $('#btnRemove').hide();
                } else if (filterValue === 'active') {
                    headingText = 'Active Employees';
                    iconClass = 'fa fa-user-plus';
                    $('#btnRemove').show();
                    $('#btnActivate').hide();
                } else if (filterValue === 'notice') {
                    headingText = 'Notice Period';
                    iconClass = 'fa fa-user';
                    $('#btnRemove').hide();
                    $('#btnActivate').hide();
                }
                 //  <!-- edited by BINDU 27-03-2026 -->
        //  <!-- edited by BINDU 25-03-2026 -->
                else if (filterValue === 'this_month') {
    headingText = 'This Month Joinings';
    iconClass = 'fa fa-calendar';
}

else if (filterValue === 'previous_month') {
    headingText = 'Previous Month Joinings';
    iconClass = 'fa fa-calendar-o';
}
// <!-- edited by BINDU 25-03-2026 END -->
//  <!-- edited by BINDU 27-03-2026 -->
                $('#employeeCardHeading').text(headingText);
                $('#employeeCardIcon').attr('class', iconClass).css({
                    color: '#008b9c',
                    fontSize: '22px'
                });
                $('#filterPanel').hide();
            }

            // initial bindings
            bindDataGrid('#emptable');
            bindDataGrid('#emptable2');

            // initial load
            reloadDataGrids();

        })(jQuery);



        $('.add_employee').on('click', function() {
            tab3Loaded = false;
            showLargeModalForm(livesite + 'EmployeeJoin/setup/0');
        });

        $('.onboarding').on('click', function() {

            var row = $('#emptable').datagrid('getSelected');

            if (!row) {
                $.notify("Please select an employee first!", {
                    type: 'warning',
                    allow_dismiss: true
                });
                return;
            }

            var empId = row.emp_pkey;

            showLargeModalForm(livesite + 'EmployeeJoin/setup/' + empId);
        });



        $('#emptable').datagrid({
            url: livesite + "EmployeeJoin/listjoinedemployees",
            method: 'get',
            queryParams: {
                emp: ''
            },
            fitColumns: true,
            singleSelect: true,
            autoRowHeight: false,
            remoteSort: true,
            pagination: true,
            PostsearchFilter: true,
            rownumbers: true,
            pageSize: 10,

            width: '99%',
            idField: 'emp_pkey',
            columns: [
                [{
                        field: 'avatar',
                        title: 'Image',
                        width: '8%'
                    },
                    {
                        field: 'name',
                        title: 'Name',
                        width: '19%',
                        sortable: true
                    },
                    {
                        field: 'dob',
                        title: 'Date of Birth',
                        width: '12%',
                        sortable: true
                    },
                    {
                        field: 'phone_no',
                        title: 'Phone No',
                        width: '15%',
                        sortable: true
                    },
                    {
                        field: 'mail_id',
                        title: 'Mail Id',
                        width: '21%',
                        sortable: true
                    },
                    {
                        field: 'district',
                        title: 'District',
                        width: '15%',
                        sortable: true
                    },
                    {
                        field: 'action',
                        title: 'Action',
                        width: '8%',
                        align: 'center',
                        formatter: function(value, row, index) {
                            return `
            <a href="javascript:void(0)" onclick="deleteEmployee(${row.emp_pkey})" title="Delete" style="color: #e63946;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                    class="bi bi-trash3-fill" viewBox="0 0 16 16">
                    <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 
                    1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 
                    0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 
                    16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 
                    0 0 0 0-1zM4.5 5.029l.5 8.5a.5.5 
                    0 1 0 .998-.06l-.5-8.5a.5.5 0 1 
                    0-.998.06m3 0l-.5 8.5a.5.5 0 1 
                    0 .998.06l.5-8.5a.5.5 0 1 
                    0-.998-.06m3 .534l.5 8.5a.5.5 
                    0 1 0 .998-.06l-.5-8.5a.5.5 
                    0 1 0-.998.06z"/>
                </svg>
            </a>
        `;
                        }
                    }
                ]
            ]
        });
    });


    function editEmployee(empId) {
        showLargeModalForm(livesite + 'EmployeeJoin/setup/' + empId);
    }

    function deleteEmployee(emp_pkey) {
        if (!confirm("Are you sure you want to delete this employee?")) {
            return;
        }

        $.ajax({
            url: livesite + "EmployeeJoin/deleteJoin",
            type: "POST",
            data: {
                emp_pkey: emp_pkey
            },
            dataType: "json",
            success: function(response) {
                if (response.status === "success") {

                    $.notify(response.message || "Employee deleted successfully", {
                        type: 'success'
                    });

 $('#emptable').datagrid('clearSelections');
        $('#emptable').datagrid('unselectAll');
                    $('#emptable').datagrid('reload');
                } else {
                    // Show error message
                    $.notify(response.message || "Failed to delete employee", {
                        type: 'danger'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error("Delete error:", error);
                $.notify("An error occurred while deleting the employee", {
                    type: 'danger'
                });
            }
        });
    }


    function downloadexcelformat() {
        window.open(livesite + 'Employee/downloadempdataformatjoin', '_blank');
    }
    var currentFilter = null;


    $('input[name="filtering"]').on("change", function() {
        currentFilter = $(this).val();

        console.log(currentFilter);
        filterEmployeesIndex();
    });

    function filterEmployeesIndex() {
        var branch = $('#search_emp_branch').val();
        var employee = $('#hid_filterby_employees').val();
        var designation = $('#filterby_Designation').val();
        var name = $('#rsndempid').val();

        $('#emptable2').datagrid('load', {
            branch: branch,
            employee: employee,
            designation: designation,
            name: name,
            filter: currentFilter
        });
    }

    function filterEmployees(obj) {
        var branch = $('#filterby_branch').val();
        var employee = $('#hid_filterby_employees').val();

        var designation = $('#filterby_Designation').val()

        $('#emptable2').datagrid('load', {
            branch: branch,
            employee: employee,
            designation: designation,
            name: $('#rsndempid').val(),
        });

    }

 $('#emptable2').datagrid({
        url: livesite + "EmployeeJoin/listemployees",
        fitColumns: true,
        singleSelect: true,
        autoRowHeight: false,
        pagination: true,
        PostsearchFilter: true,
        rownumbers: true,
        pageSize: 10,
        width: '99%',
        rowStyler: function(index, row) {
            var style = "";
            if (row.status == '2') {
                style += 'background-color:#e9ecef;color:red;font-style:italic;';
            }
            return style;
        },
        columns: [
            [{
                    field: 'avatar',
                    title: 'Image',
                    width: "8%",
                    sortable: true
                },
                {
                    field: 'emp_company_id',
                    title: 'Employee ID',
                    width: "14%",
                    sortable: true
                },
                {
                    field: 'name',
                    title: 'Full Name',
                    width: "14%",
                    sortable: true
                },
                {
                    field: 'desig_name',
                    title: 'Designation',
                    width: "16%",
                    sortable: true
                },
                {
                    field: 'joining_date',
                    title: 'Joined Date',
                    width: "12%",
                    sortable: true
                },
                {
                    field: 'branch_name',
                    title: 'Branch Name',
                    width: "13%",
                    sortable: true
                },
{
    field: 'profile_completion',
    title: 'Profile Completion',
    width: "14%",
    sortable: true,
    align: 'center',
    formatter: function(value, row, index) {
        var percentage = value || 0;
        var color = '#ef4444'; // default red
        if (percentage >= 100) color = '#22c55e'; // green
        else if (percentage >= 70) color = '#3b82f6'; // blue
        else if (percentage >= 40) color = '#f59e0b'; // amber
        
         return `<div style="width:100%; background:#e0e0e0; border-radius:10px; height:12px; position:relative; overflow:hidden; border:1px solid #ccc;">
                            <div style="width:${percentage}%; background:${color}; height:100%; transition:width 0.5s ease-in-out;"></div>
                            <span style="position:absolute; top:0; left:0; width:100%; text-align:center; font-size:9px; line-height:12px; color:${percentage > 50 ? '#fff' : '#000'}; font-weight:bold;">${percentage}%</span>
                        </div>`;
    }
},
                {
                    field: 'buttons',
                    title: 'Doc',
                    width: "8%",
                    sortable: true
                }
            ]
        ],
    });



    $(document).ready(function() {
        // Edit button
        $('#btnEdit').on('click', function() {
            var row = $('#emptable2').datagrid('getSelected');
            if (row) {
                showLargeModalForm(livesite + 'Employee/setup/' + row.emp_pkey);
            } else {
                alert("Please select a record to edit");
            }
        });
        $('#btnEdits').on('click', function() {
            var row = $('#emptable2').datagrid('getSelected');
            if (row) {
                showLargeModalForm(livesite + 'Employee/setups/' + row.emp_pkey);
            } else {
                alert("Please select a record to View");
            }
        });


        // Remove button
        $('#btnRemove').on('click', function() {
            var row = $('#emptable2').datagrid('getSelected');

            if (!row) {
                alert("Please select a record to delete");
                return;
            }

            if (confirm("Do you want to delete the selected employee(s)?")) {
                $.ajax({
                    url: livesite + "Employee/deleteEmp",
                    type: 'POST',
                    data: {
                        ids: row.emp_pkey
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $.notify(response.msg, {
                                type: 'success'
                            });
                        } else {
                            $.notify(response.msg, {
                                type: 'danger'
                            });
                        }

                        // Reload datagrid
                        $('#emptable2').datagrid('reload');
                        reloadTable('emptable2');
                    },
                    error: function() {
                        $.notify("Something went wrong. Please try again.", {
                            type: 'danger'
                        });
                    }
                });
            }
        });


        $('#btnActivate').on('click', function() {
            var row = $('#emptable2').datagrid('getSelected');
            if (!row) {
                alert("Please select a record to activate");
                return;
            }

            if (!confirm("Do you want to activate the selected employee(s)?")) {
                return;
            }

            $.ajax({
                url: livesite + "EmployeeJoin/activateEmp",
                data: {
                    ids: row.emp_pkey
                },
                success: function(response) {

                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }

                    if (response.success == 1) {
                        $.notify(response.msg, {
                            type: 'success'
                        });

                        // Reload datagrid after successful activation
                        $('#emptable2').datagrid('reload');
                    } else {
                        $.notify(response.msg || "Activation failed", {
                            type: 'danger'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Activation error:", error);
                    $.notify("Network error or server issue", {
                        type: 'danger'
                    });
                }
            });
        });

        // Info Change button
        $('#btnInfoChange').on('click', function() {
            var row = $('#emptable2').datagrid('getSelected');
            if (row) {
                showLargeModalForm(livesite + 'Promo/promotion/' + row.emp_pkey);
            } else {
                alert("Please select a record to update");
            }
        });
    });


    $("#filterBtn").on("click", function(e) {
        e.stopPropagation();
        $("#filterPanel").toggle(); // toggle show/hide

        var btnOffset = $(this).offset();
        $("#filterPanel").css({
            top: btnOffset.top + $(this).outerHeight(),
            left: btnOffset.left
        });
    });

    $(document).on("click", function(e) {
        if (!$(e.target).closest("#filterPanel, #filterBtn").length) {
            $("#filterPanel").hide();
        }
    });

    $('input[name="filtering"]').on("change", function() {
        var filterValue = $(this).val();
        var branch = $('#search_emp_branch').val();

        // Set heading based on selected filter
        var headingText = "";
        if (filterValue === "2") {
            headingText = "Resigned Employees";
            $("#btnActivate").show();
            $("#btnRemove").hide();
        } else if (filterValue === "active") {
            headingText = "Active Employees";
            $("#btnRemove").show();
            $("#btnActivate").hide();
        } else if (filterValue === "notice") {
            headingText = "Notice Period";
            // optional: hide both if needed
            // $("#btnRemove").hide();
            $("#btnActivate").hide();
        }
        // Edited by bindu 27-04-26
        else if (filterValue === "this_month") {
            headingText = "Joined This Month";
            $("#btnRemove").show();
            $("#btnActivate").hide();
        } else if (filterValue === "previous_month") {
            headingText = "Joined Previous Month";
            $("#btnRemove").show();
            $("#btnActivate").hide();
        }
// Edited by bindu 27-04-26 end
        $("#employeeCardHeading").text(headingText);

        // Reload datagrid with filter
        $('#emptable2').datagrid('load', {
            filter: filterValue,
            branch: branch,
        });

        // Update count after data loads
        $('#emptable2').datagrid({
            onLoadSuccess: function(data) {
                $("#employeeCardCount").text(data.total || data.rows.length || 0);
            }
        });

        $("#filterPanel").hide();
    });


    $("#clearFilter").on("click", function() {
        var branch = $('#search_emp_branch').val();

        // Reset "Active" as default checked
        $('input[name="filtering"]').prop("checked", false);
        $('#filterActive').prop("checked", true);

        // Update heading & buttons like in filter
        $("#employeeCardHeading").text("Active Employees");
        $("#btnRemove").show();
        $("#btnActivate").hide();

        // Reload datagrid with active filter + branch
        $('#emptable2').datagrid('load', {
            filter: "active",
            branch: branch
        });

        // Update count after data loads
        $('#emptable2').datagrid({
            onLoadSuccess: function(data) {
                $("#employeeCardCount").text(data.total || data.rows.length || 0);
            }
        });

        // Hide filter panel
        $("#filterPanel").hide();
    });


    $(document).ready(function() {



        $('#search_emp_branch').on('change', function() {
            filterEmployeesIndex();
        });

    });

   /* edited by bindu 20-02-26 */
   $(".home").on("click", function () {

    $("#container").isLoading({
        text: "Loading",
        position: "overlay",
    });

    let url = "";
     var userGroup = <?php echo json_encode($this->Session->read('user_group')); ?>

    if (userGroup == "1") {
        url = livesite + "EmployeeManage/index";
    } 
    else if (userGroup == "2") {
        url = livesite + "EmployeeMenu/addon";
    }

    $("#container").load(url, function () {
        isDashboardShown = false;
    });

});

	/* edited by bindu 20-02-26 */



    function uploadEmployeeData() {
        var fileInput = document.getElementById('empdatacsv');
        var files = fileInput.files;

        if (!files || files.length === 0) {
            $.notify("Please choose a file!", {
                type: 'danger',
                allow_dismiss: false
            });
            return false;
        }

        $('#loaders').fadeIn();

        var formData = new FormData();
        formData.append('empdatacsv', files[0], files[0].name);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', livesite + 'EmployeeJoin/uploadandsaveempdetail', true);

        // Disable upload button while uploading
        $('#uploadBtn').prop('disabled', true);

        xhr.onload = function() {
            $('#loaders').fadeOut();

            try {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);

                    // ✔ SUCCESS message
                    if (response.success == 1) {

                        $.notify(response.msg, {
                            type: 'success'
                        });
                        reloadTable('emptable');

                    } else {

                        $.notify(response.msg || "Import completed with some issues.", {
                            type: 'danger'
                        });
                        if (response.errors) {
    let msg = response.errors.replace(/\.$/, '');
    alert(msg);
}
                    }

                } else {
                    $.notify("Employee import failed, please try again!", {
                        type: 'danger'
                    });
                }

            } catch (e) {
                $.notify("Invalid response from server.", {
                    type: 'danger'
                });
                console.error("Response parse error:", e, xhr.responseText);
            } finally {
                fileInput.value = '';
                $('#uploadBtn').prop('disabled', false);
            }
        };

        xhr.onerror = function() {
            $('#loaders').fadeOut();
            $.notify("Network error! Please check your connection.", {
                type: 'danger'
            });
            fileInput.value = '';
            $('#uploadBtn').prop('disabled', false);
        };

        xhr.send(formData);
    }
</script>