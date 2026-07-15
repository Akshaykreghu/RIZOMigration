<?php
/**
 * Menu Allocation Redesign - index.ctp
 */
?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    .nova-container {
        --primary-blue: #1e516e;
        --accent-blue: #3498db;
        /* --bg-gradient: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); */
        --glass-bg: rgba(255, 255, 255, 0.7);
        --glass-border: rgba(255, 255, 255, 0.3);
        
        font-family: 'Inter', sans-serif;
        display: flex;
        height: 100vh;
        overflow: hidden;
        background: var(--bg-gradient);
    }

    /* Sidebar Styling */
    .nova-sidebar {
        width: 300px;
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-right: 1px solid var(--glass-border);
        display: flex;
        flex-direction: column;
        z-index: 10;
        box-shadow: 4px 0 15px rgba(0,0,0,0.05);
    }

    .nova-sidebar-header {
        padding: 24px;
        border-bottom: 1px solid var(--glass-border);
    }

    .nova-logo {
        font-size: 26px;
        font-weight: 800;
        color: var(--primary-blue);
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .nova-search-container {
        position: relative;
    }

    .nova-search-input {
        width: 100%;
        padding: 12px 15px 12px 40px;
        border-radius: 14px;
        border: 1px solid var(--glass-border);
        background: rgba(255,255,255,0.6);
        outline: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 14px;
    }

    .nova-search-input:focus {
        border-color: var(--accent-blue);
        background: #fff;
        box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
    }

    .nova-search-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    .nova-employee-list {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
    }

    .nova-employee-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 16px;
        border-radius: 16px;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-bottom: 8px;
        border: 1px solid transparent;
    }

    .nova-employee-item:hover {
        background: rgba(255,255,255,0.5);
        transform: translateX(4px);
    }

    .nova-employee-item.active {
        background: var(--primary-blue);
        color: white;
        box-shadow: 0 10px 15px -3px rgba(30, 81, 110, 0.4);
    }

    .nova-employee-avatar {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        flex-shrink: 0;
        font-size: 14px;
        overflow: hidden;
    }

    .nova-employee-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .nova-employee-item.active .nova-employee-avatar {
        background: rgba(255,255,255,0.2);
        color: #fff;
    }

    .nova-employee-info {
        flex: 1;
        min-width: 0;
    }

    .nova-employee-name {
        font-weight: 600;
        font-size: 15px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 2px;
    }

    .nova-employee-id {
        font-size: 12px;
        opacity: 0.8;
        font-weight: 500;
    }

    /* Main Content Styling */
    .nova-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .nova-header {
        padding: 10px 10px 10px 30px;
        background: rgba(255,255,255,0.4);
        backdrop-filter: blur(5px);
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--glass-border);
    }

    .nova-breadcrumb {
        font-size: 12px;
        color: #666;
        margin-bottom: 4px;
    }

    .nova-page-title {
        font-size: 20px;
        /* font-weight: 700; */
        color: #333;
        margin: 0;
    }

    .active-emp-name {
        color: var(--accent-blue);
    }

    .nova-content-area {
        flex: 1;
        /* overflow-y: auto; */
        padding: 30px 20px;
    }

    /* Back Button */
    .nova-back {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 20px;
        background: #fff;
        color: var(--primary-blue);
        font-weight: 600;
        border: 1px solid var(--glass-border);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .nova-back:hover {
        background: var(--primary-blue);
        color: #fff;
    }

    /* Loading Spinner */
    .loader-container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 200px;
    }
    .fa-folder-open:before {
    content: none !important;
}
.heading {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;

    }

    .home {
        background-color: #ffffffff;
        border-radius: 50px;
        padding: 3px 15px;
        color: #1e516e !important;
        /* margin-right: 15px; */
        color: white;
        font-weight: normal;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        border: #1e516e 1px solid;
        font-size: 13px;
    }

</style>

<div class="nova-container">
    <!-- Sidebar -->
    <aside class="nova-sidebar">
        <div class="nova-sidebar-header">
            <div class="nova-logo">
                <i class="fa fa-cubes"></i>
                <span style="font-size: 18px;">Menu Allocation</span>
            </div>
            <div class="nova-search-container">
                <i class="fa fa-search nova-search-icon"></i>
                <input type="text" id="empSearch" class="nova-search-input" placeholder="Search Employee...">
            </div>
        </div>
        <div class="nova-employee-list" id="employeeList">
             <div id="empNoData" style="display:none; text-align:center; padding:10px 16px; color:#94a3b8;">
                <i class="fa fa-search" style="font-size:24px; margin-bottom:8px; display:block; opacity:0.4;"></i>
                <span style="font-size:13px; font-weight:500;">No Employee Found</span>
            </div>
            <?php foreach ($arr_employee as $val): ?>
                <div class="nova-employee-item" data-id="<?php echo $val['EmployeeDetails']['emp_pkey']; ?>" onclick="selectEmployee(this)">
                    <div class="nova-employee-avatar">
                        <?php if (!empty($val['EmployeeDetails']['avatar'])): ?>
                            <img src="<?php echo $val['EmployeeDetails']['avatar']; ?>" alt="Avatar">
                        <?php else: ?>
                            <?php echo substr($val['EmployeeDetails']['first_name'], 0, 1) . substr($val['EmployeeDetails']['last_name'], 0, 1); ?>
                        <?php endif; ?>
                    </div>
                    <div class="nova-employee-info">
                        <div class="nova-employee-name"><?php echo $val['EmployeeDetails']['first_name'] . ' ' . $val['EmployeeDetails']['last_name']; ?></div>
                        <div class="nova-employee-id"><?php echo $val['EmployeeDetails']['emp_company_id']; ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="nova-main">
        <header class="nova-header">
            <div>
                <!-- <div class="nova-breadcrumb">Dashboard / Employees / <span id="breadEmp">Selection</span> / Menu Allocation</div> -->
                <h1 class="nova-page-title">Menu Allocation: <span id="activeEmpName" class="active-emp-name">Select Employee</span></h1>
            </div>
           
              <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
            <i class="fa" style="font-size:16px;">&#xf104;</i>
            Back
        </div>
         
        </header>

        <div class="nova-content-area" id="loadArea">
            <div class="loader-container">
                <div style="text-align: center; color: #666;">
                    <i class="fa fa-users" style="font-size: 48px; margin-bottom: 20px; opacity: 0.3;"></i>
                    <p>Please select an employee from the sidebar to manage their menu access.</p>
                </div>
            </div>
        </div>
    </main>
</div>

<input type="hidden" id="emp_pkey" value="">

<script>
    $(document).ready(function () {
        // Employee Search Filter
        var searchTimeout;
        $("#empSearch").on("keyup", function() {
            clearTimeout(searchTimeout);
            var value = $(this).val().toLowerCase();
            searchTimeout = setTimeout(function() {
                $("#employeeList .nova-employee-item").each(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
                var visible = $("#employeeList .nova-employee-item:visible").length;
                $("#empNoData").toggle(visible === 0 && value !== '');
            }, 250);
        });

        // Initialize with first employee if needed, or leave blank
        // var firstEmp = $('.nova-employee-item').first();
        // if(firstEmp.length) selectEmployee(firstEmp[0]);
    });

    function selectEmployee(element) {
        var empId = $(element).data('id');
        var empName = $(element).find('.nova-employee-name').text();
        
        // UI Updates
        $('.nova-employee-item').removeClass('active');
        $(element).addClass('active');
        $('#activeEmpName').text(empName);
        $('#breadEmp').text(empName);
        $('#emp_pkey').val(empId);

        // Load Content
        $('#loadArea').html('<div class="loader-container"><i class="fa fa-spinner fa-spin fa-3x" style="color: #1e516e;"></i></div>');
        $('#loadArea').load(livesite + 'UserAccess/insecs/' + empId);
    }

    // function goBack() {
    //     if(typeof $.prototype.isLoading !== 'undefined'){
    //         $("#container").isLoading({
    //             text: "Loading",
    //             position: "overlay",
    //         });
    //     }
    //     $("#container").load(livesite + "EmployeeManage/index");
    // }

      /* edited by bindu 20-02-26 */
   $(".home").off("click").on("click", function (e) {
    e.preventDefault();

    $("#container").isLoading({
        text: "Loading",
        position: "overlay",
    });

    let url = "";
    var userGroup=<?php echo json_encode($user_group); ?>

    if (userGroup == "1") {
        url = livesite + "EmployeeManage/index";
    } 
    else if (userGroup == "2") {
        let menuType = sessionStorage.getItem('menu_type') || 'standard';
        // console.log(menuType,'hello');
        if (menuType === 'addon') {
            url = livesite + "EmployeeMenu/addon";
        } else {
            url = livesite + "EmployeeMenu/index";
        }
}

    $("#container").load(url, function () {
        isDashboardShown = false;
    });

});

	/* edited by bindu 20-02-26 */
</script>