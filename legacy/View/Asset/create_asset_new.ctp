<style>
    .heading {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;

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

    #assetTable {
        width: 80vw !important;
    }
</style>

<div class="main-div" style="margin-right: 30px;margin-left :30px;">
    <div class="heading">
        <h1 class="text-primary-18">Asset Allocation</h1>
        <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
            <i class="fa" style="font-size:16px;">&#xf104;</i>
            Back
        </div>
    </div>
    <hr style="margin-top: 8px;margin-bottom: -2px;">

    <div style="margin-top: 20px;">
        <!-- Branch Dropdown -->
        <div class="col-md-5" style="padding: 0;">
            <label class="col-md-4 control-label" for="filterby_branches" style="padding: 0;">Branch</label>
            <div class="col-md-1">:</div>
            <div class="col-md-7">

                <select id="filterby_branches" name="filterby_branches" class="form-control js-example-basic-single"
                    onchange="filterEmployees();">

                    <?php if ($user_group == 1) { ?>
            <option value="0">All</option>
        <?php } ?>
                    <?php foreach ($arr_branches as $branch) { ?>
                        <option value="<?php echo $branch->id; ?>">
                            <?php echo $branch->text; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <!-- Employee Dropdown -->
        <div class="col-md-5" style="padding: 0;">
            <label class="col-md-4 control-label" for="employee">Employee</label>
            <div class="col-md-1">:</div>
            <div class="col-md-7">
                <select id="emp_fkey1" class="form-control js-example-basic-single" name="emp_fkey1"
                    onchange="loadEmployeeAssets();">
                    <!-- Employee list loads dynamically -->
                </select>
            </div>
        </div>
    </div>
    <div style="margin-top: 60px;">

        <button type="button" onclick="showassets();" class="btn btn-primary">
            Add New Asset to Employee
        </button>
        <button type="button" id="edites" class="btn btn-primary">Edit</button>
        <input type="hidden" id="edit_pkey" value="<?php echo isset($edit_pkey) ? $edit_pkey : ''; ?>">

    </div>
    <div id="modalDetailForm" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="modaldetails-content">

            </div>
        </div>
    </div>


    <!-- Asset DataGrid -->
    <div style="margin-top: 20px;">
        <table id="assetTable" class="easyui-datagrid"
            data-options="rownumbers:true,singleSelect:true,pagination:true,fitColumns:true" style="width:100%;">
            <thead>
                <tr>
                    <th data-options="field:'EmpName',width:'20%'">Employee Name</th>
                    <th data-options="field:'EmpCode',width:'15%'">Employee Code</th>
                    <th data-options="field:'Branch',width:'13%'">Branch</th>
                    <th data-options="field:'name',width:'13%'">Asset Name</th>
                    <th data-options="field:'allocated_date',width:'13%'">Allocated Date</th>
                    <th data-options="field:'status',width:'13%'">Condition</th>
                    <th data-options="field:'Type',width:'13%'">Type</th>
                    <!-- <th data-options="field:'model',width:'10%'">Model</th> -->
                    <!-- <th data-options="field:'brand',width:'10%'">Brand</th> -->
                    <!-- <th data-options="field:'serial_no',width:'10%'">Serial No</th> -->
                    <!-- <th data-options="field:'specifications',width:'15%'">Specifications</th> -->
                </tr>
            </thead>


        </table>

    </div>
</div>

<script>
    let selected = null;

    jQuery(document).ready(function () {
        $("#filterby_branches").select2();
        filterEmployees();
        initAssetTable();
    });

    function filterEmployees() {
        var branch = $('#filterby_branches').val();

        $("#emp_fkey1").select2({
            placeholder: "--Select Employee--",
            allowClear: true,
            ajax: {
                url: livesite + "Asset/getEmployeesByBranch",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term,
                        page: params.page || 1,
                        branch: branch
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.items,
                        pagination: {
                            more: (params.page * 30) < data.total_count
                        }
                    };
                }
            }
        });
    }

    function initAssetTable() {
        $('#assetTable').datagrid({
            loadFilter: function (data) {


                let rows = [];
                if (data.rows && Array.isArray(data.rows)) {
                    rows = data.rows;
                } else if (data.data && Array.isArray(data.data)) {
                    rows = data.data;
                } else if (Array.isArray(data)) {
                    rows = data;
                }

                let opts = $(this).datagrid('options');
                let start = (opts.pageNumber - 1) * parseInt(opts.pageSize);
                let end = start + parseInt(opts.pageSize);
                let paginatedRows = rows.slice(start, end);

                return {
                    total: rows.length,
                    rows: paginatedRows
                };
            }
        });
    }


    // function loadEmployeeAssets() {
    //     let empId = $('#emp_fkey1').val();
    //     if (!empId) return;

    //     $.ajax({
    //         url: livesite + "Asset/Emp/" + empId,
    //         type: "GET",
    //         success: function() {
    //             console.log("Emp API Loaded Successfully");

    //             $('#assetTable').datagrid('options').url = livesite + "Asset/listitems/" + empId;
    //             $('#assetTable').datagrid('reload');
    //         },
    //         error: function() {
    //             alert("Error: Unable to fetch employee data.");
    //         }
    //     });

    // }

    $(document).ready(function () {

        $('#filterby_branches').on('change', function () {
            $('#emp_fkey1').val('');

            $('#assetTable').datagrid('loadData', {
                total: 0,
                rows: []
            });
        });
    });
    // edited by bindu 03-01-2026
    function loadEmployeeAssets() {
        let empId = $('#emp_fkey1').val();
        let branchCode = $('#filterby_branches').val();

        // if (!empId) {
        //     alert("Please select an employee first.");
        //     return;
        // }

        let apiUrl = livesite + "Asset/listitems/" + empId + "/" + branchCode;

        $('#assetTable').datagrid('options').url = apiUrl;
        $('#assetTable').datagrid('reload');
    }

    // edited by bindu 03-01-2026 end

    function adjustGridHeight(totalRows) {
        let rowHeight = 40;
        let headerHeight = 40;
        let minHeight = 200;
        let maxHeight = 400;

        let newHeight = (totalRows * rowHeight) + headerHeight;

        if (newHeight < minHeight) newHeight = minHeight;
        if (newHeight > maxHeight) newHeight = maxHeight;

        $('#assetTable').datagrid('resize', {
            height: newHeight
        });
    }

    // edited by bindu 03-01-2026
    function showassets() {
        var emp_pkey = $('#emp_fkey1').val();
        var branch_code = $('#filterby_branches').val();

        if (!emp_pkey || emp_pkey === "0") {
            alert("Please select an employee first.");
            return;
        }

        if (!branch_code) {
            alert("Please select a branch first.");
            return;
        }

        var url = livesite + 'Asset/Allocatenew/' + emp_pkey + '/' + branch_code;

        $("#modalDetailForm #modaldetails-content").load(url, function () {
            $("#modalDetailForm").modal('show');
        });
    }
    // edited by bindu 03-01-2026 end



    $(document).ready(function () {
        let selected = null;


        $('#assetTable').datagrid({
            onClickRow: function (index, row) {
                selected = row.allocate_pkey;
                $('#edit_pkey').val(selected);
            }
        });


        // $('#edites').click(function() {
        //     var emp_pkey = $('#emp_fkey1').val();
        //     var branch_code = $('#filterby_branches').val();
        //     let selectedId = $('#edit_pkey').val();
        //     if (!emp_pkey || emp_pkey === "null" || emp_pkey === "0") {
        //         alert("Please select an employee first.");
        //         return;
        //     }

        //     if (!selectedId || selectedId === "0") {
        //         alert("Please select a row first!");
        //         return;
        //     }


        //     var url = livesite + 'Asset/edit/' + selectedId + '/' + emp_pkey + '/' + branch_code;
        //     $("#modalDetailForm #modaldetails-content").load(url, function() {
        //         $("#modalDetailForm").modal('show');
        //     });

        // });

        $('#edites').click(function () {
            var emp_pkey = $('#emp_fkey1').val();
            var branch_code = $('#filterby_branches').val();
            let selectedId = $('#edit_pkey').val();

            // ✅ Check employee
            if (!emp_pkey || emp_pkey === "null" || emp_pkey === "0") {
                alert("Please select an employee first.");
                return;
            }

            // ✅ Check if a row is selected
            if (!selectedId || selectedId === "0") {
                alert("Please select a row first!");
                return;
            }

            // ✅ Build the edit URL
            var url = livesite + 'Asset/edit/' + selectedId + '/' + emp_pkey + '/' + branch_code;

            // ✅ Load modal and open it
            $("#modalDetailForm #modaldetails-content").load(url, function () {
                $("#modalDetailForm").modal('show');
            });

            // ✅ After modal closes, reload the grid and clear selection
            $('#modalDetailForm').on('hidden.bs.modal', function () {
                // Reload datagrid with updated data
                let apiUrl = livesite + "Asset/listitems/" + emp_pkey + "/" + branch_code;
                $('#assetTable').datagrid('options').url = apiUrl;
                $('#assetTable').datagrid('reload');

                // ✅ Clear the selected row & reset the hidden input
                $('#assetTable').datagrid('clearSelections');
                $('#edit_pkey').val('');
            });
        });

    });

    /* edited by bindu 20-02-26 */
    $(".home").on("click", function () {

        $("#container").isLoading({
            text: "Loading",
            position: "overlay",
        });

        let url = "";
        var userGroup = <?php echo json_encode($user_group); ?>

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

</script>
<script>
    $(document).ready(function () {
        $('#modalDetailForm').on('hidden.bs.modal', function () {
            if (window.parent && window.parent.$) {
                window.parent.$('#assetTable').datagrid('reload');
            }
        });
    });
</script>