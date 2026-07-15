<?php
/**
 * Menu Allocation Redesign - insec.ctp
 */
?>
<style>
    .nova-container .nova-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
    }

    .nova-container .nova-card {
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        height: 500px;
        display: flex;
        flex-direction: column;
    }

    .nova-card-title {
        font-size: 18px;
        font-weight: 700;
        color: var(--primary-blue);
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* Toggle Switch */
    .nova-switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
    }

    .nova-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .nova-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 34px;
    }

    .nova-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked + .nova-slider {
        background-color: var(--accent-blue);
    }

    input:checked + .nova-slider:before {
        transform: translateX(20px);
    }

    /* Small Switch for Children */
    .nova-switch.small {
        width: 36px;
        height: 20px;
    }

    .nova-switch.small .nova-slider:before {
        height: 14px;
        width: 14px;
        left: 3px;
        bottom: 3px;
    }

    .nova-switch.small input:checked + .nova-slider:before {
        transform: translateX(16px);
    }

    /* Menu Item Styling */
    .menu-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 10px;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .menu-item:last-child {
        border-bottom: none;
    }

    .menu-info {
        display: flex;
        align-items: center;
        gap: 16px;
        flex: 1;
        min-width: 0;
    }

    .menu-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        /* background: rgba(30, 81, 110, 0.08); */
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-blue);
        flex-shrink: 0;
        font-size: 16px;
    }

    .menu-details {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
    }

    .menu-name-label {
        font-weight: 600;
        font-size: 14px;
        color: #1e293b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .menu-status-label {
        font-size: 11px;
        color: var(--text-muted);
        font-weight: 500;
    }

    .child-container {
        border-left: 2px solid #f1f5f9;
        margin-left: 18px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }

    /* Addon Features Cards */
    .addon-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 15px;
        flex: 1;
        overflow-y: auto;
        padding-right: 5px;
    }

    .menu-list {
        flex: 1;
        overflow-y: auto;
        padding-right: 5px;
    }

    /* Custom Scrollbar */
    .addon-grid::-webkit-scrollbar, .menu-list::-webkit-scrollbar {
        width: 6px;
    }
    .addon-grid::-webkit-scrollbar-thumb, .menu-list::-webkit-scrollbar-thumb {
        background: rgba(0,0,0,0.1);
        border-radius: 10px;
    }

    .addon-card {
        background: #fff;
        border: 1px solid var(--glass-border);
        border-radius: 12px;
        padding: 15px;
        transition: all 0.3s ease;
    }

    .addon-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    .addon-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .addon-name {
        font-weight: 600;
        font-size: 15px;
    }

    .addon-desc {
        font-size: 12px;
        color: #666;
        margin-bottom: 15px;
        height: 34px;
        overflow: hidden;
    }

    .btn-manage {
        flex: 1;
        padding: 8px;
        border-radius: 8px;
        border: 1px solid var(--accent-blue);
        background: transparent;
        color: var(--accent-blue);
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .btn-manage:hover {
        background: var(--accent-blue);
        color: #fff;
    }

    /* Modal Styling */
    .nova-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.4);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 100;
        backdrop-filter: blur(4px);
    }

    .nova-modal {
        background: #fff;
        width: 400px;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(0,0,0,0.2);
    }

    .nova-modal-header {
        padding: 20px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .nova-modal-body {
        padding: 20px;
        max-height: 400px;
        overflow-y: auto;
    }

    .branch-list-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 0;
    }

    .nova-modal-footer {
        padding: 15px 20px;
        background: #f9f9f9;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .btn-secondary {
        background: #eee;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        cursor: pointer;
    }

    .btn-primary {
        background: var(--accent-blue);
        color: #fff;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        cursor: pointer;
    }
    .label-branch{
        margin-bottom: -5px;
    }

    /* Choice Modal Styling */
    .choice-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        padding-top: 10px;
    }

    .choice-card {
        border: 2px solid #f1f5f9;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }

    .choice-card:hover {
        border-color: var(--accent-blue);
        background: #f8fafc;
    }

    .choice-card i {
        font-size: 24px;
        color: var(--primary-blue);
    }

    .choice-card .choice-title {
        font-weight: 700;
        font-size: 14px;
        color: #1e293b;
    }

    .choice-card .choice-desc {
        font-size: 11px;
        color: #64748b;
        line-height: 1.4;
    }

    /* Search Input Focus Effects */
    .nova-card-title input {
        transition: all 0.3s ease !important;
    }

    .nova-card-title input:focus {
        border-color: var(--accent-blue) !important;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        outline: none;
    }
</style>

<div class="nova-grid">
    <!-- Default System Menus -->
    <div class="nova-card">
        <div class="nova-card-title">
            <span>Standard Menus</span>
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="position: relative; width: 140px;">
                    <i class="fa fa-search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 11px;"></i>
                    <input type="text" id="systemMenuSearch" placeholder="Search..."
                           style="width: 100%; padding: 5px 10px 5px 28px; border-radius: 10px; border: 1px solid #e2e8f0; font-size: 11px; font-weight: 500; outline: none;">
                </div>
                <i class="fa fa-info-circle" style="font-size: 14px; opacity: 0.5;" title="Default menus are auto-allocated. Admin can toggle individual items off."></i>
            </div>
        </div>
        <div class="menu-list">
            <div id="systemMenuNoData" style="display:none; text-align:center; padding:10px 10px; color:#94a3b8;">
                <i class="fa fa-search" style="font-size:22px; margin-bottom:8px; display:block; opacity:0.4;"></i>
                <span style="font-size:12px; font-weight:500;">No Menu Found</span>
            </div>
            <?php foreach($arr_parent as $val): ?>
                <div class="menu-item">
                    <div class="menu-info">
                        <div class="menu-icon"><i class="fa fa-folder-open"></i></div>
                        <div class="menu-details">
                            <div class="menu-name-label"><?php echo $val['EmployeeMenu']['menu_name']; ?></div>
                            <div class="menu-status-label"><?php echo $val['0']['active'] == 'Y' ? 'Enabled' : 'Disabled'; ?></div>
                        </div>
                    </div>
                    <label class="nova-switch">
                            <input type="checkbox" id="parent_<?php echo $val['EmployeeMenu']['menu_id']; ?>" 
                                   data-parent-id="<?php echo $val['EmployeeMenu']['menu_id']; ?>" 
                                   <?php echo $val['0']['active'] == 'Y' ? 'checked' : ''; ?> 
                                   onchange="toggleMenu('<?php echo $val['EmployeeMenu']['menu_id']; ?>', this, '<?php echo $val['EmployeeMenu']['menu_name']; ?>', true)">
                        <span class="nova-slider"></span>
                    </label>
                </div>
                
                    <div class="child-container" id="child_<?php echo $val['EmployeeMenu']['menu_id']; ?>" 
                         style="<?php echo $val['0']['active'] == 'Y' ? '' : 'display: none;'; ?>">
                <!-- Submenus -->
                <?php foreach($arr_child as $value): ?>
                    <?php if($value['EmployeeMenu']['parent_id'] == $val['EmployeeMenu']['menu_id']): ?>
                        <div class="menu-item" style="padding-left: 24px; border-bottom: none;">
                            <div class="menu-info">
                                <div class="menu-icon" style="width: 28px; height: 28px; border-radius: 6px;"><i class="fa fa-level-up fa-rotate-90" style="font-size: 10px; opacity: 0.4;"></i></div>
                                <div class="menu-details">
                                    <div class="menu-name-label" style="font-size: 13px; font-weight: 500;"><?php echo $value['EmployeeMenu']['menu_title']; ?></div>
                                    <div class="menu-status-label" style="font-size: 10px;"><?php echo $value['0']['active'] == 'Y' ? 'Enabled' : 'Disabled'; ?></div>
                                </div>
                            </div>
                            <label class="nova-switch small">
                                <input type="checkbox" <?php echo $value['0']['active'] == 'Y' ? 'checked' : ''; ?>
                                       onchange="toggleMenu('<?php echo $value['EmployeeMenu']['menu_id']; ?>', this, '<?php echo $value['EmployeeMenu']['menu_title']; ?>', false)">
                                <span class="nova-slider"></span>
                            </label>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Add-on Features -->
    <div class="nova-card">
        <div class="nova-card-title">
            <span>Add-on Menus</span>
            <div style="position: relative; width: 160px;">
                <i class="fa fa-search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 11px;"></i>
                <input type="text" id="addonSearch" placeholder="Search..." 
                       style="width: 100%; padding: 5px 10px 5px 28px; border-radius: 10px; border: 1px solid #e2e8f0; font-size: 11px; font-weight: 500; outline: none;">
            </div>
        </div>
        <div class="addon-grid">
            <div id="addonNoData" style="display:none; text-align:center; padding:10px 10px; color:#94a3b8; width: 100%;">
                <i class="fa fa-search" style="font-size:22px; margin-bottom:8px; display:block; opacity:0.4;"></i>
                <span style="font-size:12px; font-weight:500;">No Add-on Found</span>
            </div>
            <?php
                // Safety defaults
                if (!isset($addon_useraccess) || !is_array($addon_useraccess)) { $addon_useraccess = array(); }
                if (!isset($features)         || !is_array($features))         { $features = array(); }

                // Separate allocated and unallocated features — allocated shown first
                $allocated   = array();
                $unallocated = array();

                foreach ($features as $f) {
                    $isAllocated = false;
                    foreach ($addon_useraccess as $ua) {
                        if ($ua['user_access']['menu_id'] == $f['features']['feature_id']) {
                            $isAllocated = true;
                            break;
                        }
                    }
                    if (isset($feature_modes[$f['features']['feature_id']])) {
                        $isAllocated = true;
                    }
                    
                    if ($isAllocated) {
                        $allocated[] = $f;
                    } else {
                        $unallocated[] = $f;
                    }
                }
                $features = array_merge($allocated, $unallocated);
            ?>
            <?php foreach($features as $f): ?>
                <?php
                    $feature     = $f['features'];
                    $isAllocated = false;
                    foreach ($addon_useraccess as $ua) {
                        if ($ua['user_access']['menu_id'] == $feature['feature_id']) {
                            $isAllocated = true;
                            break;
                        }
                    }
                    if (isset($feature_modes[$feature['feature_id']])) {
                        $isAllocated = true;
                    }
                ?>
                <div class="addon-card" id="addon_<?php echo $feature['feature_id']; ?>">
                    <div class="addon-header">
                        <div class="addon-name"><?php echo $feature['feature_name']; ?></div>
                        <label class="nova-switch">
                            <input type="checkbox" <?php echo $isAllocated ? 'checked' : ''; ?>
                                   onchange="toggleFeature('<?php echo $feature['feature_id']; ?>', this)">
                            <span class="nova-slider"></span>
                        </label>
                    </div>
                    <div class="addon-desc"><?php echo $feature['description'] ?: 'Enable advanced ' . $feature['feature_name'] . ' functionality for this employee.'; ?></div>
                    <div class="addon-footer" style="display: <?php echo $isAllocated ? 'flex' : 'none'; ?>; gap: 8px; margin-top: 10px;">
                        <?php 
                            $isHierarchy = isset($feature_modes[$feature['feature_id']]) && $feature_modes[$feature['feature_id']] == 'Y';
                            $manageBtnText = $isHierarchy ? 'Set Branch Wise' : 'Manage Branch';
                            $hierarchyBtnStyle = $isHierarchy ? 'display: none;' : '';
                        ?>
                        <button class="btn-manage" id="btn_manage_<?php echo $feature['feature_id']; ?>" onclick="openBranchModal('<?php echo $feature['feature_id']; ?>', '<?php echo $feature['feature_name']; ?>')">
                            <?php echo $manageBtnText; ?>
                        </button>
                        <button class="btn-manage" id="btn_hierarchy_<?php echo $feature['feature_id']; ?>" 
                                onclick="switchToHierarchy('<?php echo $feature['feature_id']; ?>', '<?php echo $feature['feature_name']; ?>')"
                                style="border-color: #f87171; color: #ef4444; <?php echo $hierarchyBtnStyle; ?>">
                            Set Hierarchy
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Modal for Branch Access -->
<div class="nova-modal-overlay" id="branchModal">
    <div class="nova-modal">
        <div class="nova-modal-header">
            <h3 style="margin:0; font-size: 18px;">Manage Branch Access: <span id="modalFeatureName"></span></h3>
            <i class="fa fa-times" style="cursor:pointer;" onclick="closeBranchModal()"></i>
        </div>
        <div class="nova-modal-body">
            <div style="margin-bottom: 15px;">
                <input type="text" id="branchSearch" placeholder="Search branches..." style="width:100%; padding:8px; border-radius:8px; border:1px solid #ddd;">
            </div>
            <div id="branchListContainer">
                <div class="branch-list-item">
                    <input type="checkbox" id="selectAllBranches" onchange="toggleSelectAllBranches(this)">
                    <label class="label-branch" for="selectAllBranches">All Branches</label>
                </div>
                <div id="branchNoData" style="display:none; text-align:center; padding:10px 10px; color:#94a3b8;">
                    <i class="fa fa-search" style="font-size:18px; margin-bottom:6px; display:block; opacity:0.4;"></i>
                    <span style="font-size:12px; font-weight:500;">No Branch Found</span>
                </div>
                <hr style="margin: 10px 0; opacity: 0.1;">
                <?php foreach($branches as $b): ?>
                    <div class="branch-list-item branch-row">
                        <input type="checkbox" class="branch-check" id="b_<?php echo $b['branches']['branch_code']; ?>" value="<?php echo $b['branches']['branch_code']; ?>" onchange="updateSelectAllState()">
                        <label class="label-branch" style="font-weight:500;" for="b_<?php echo $b['branches']['branch_code']; ?>"><?php echo $b['branches']['branch_name']; ?> (<?php echo $b['branches']['branch_code']; ?>)</label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="nova-modal-footer">
            <button class="btn-secondary" onclick="closeBranchModal()">Cancel</button>
            <button class="btn-primary" onclick="saveBranchAccess()">Save Changes</button>
        </div>
    </div>
</div>

<!-- Modal for Choice -->
<div class="nova-modal-overlay" id="choiceModal">
    <div class="nova-modal">
        <div class="nova-modal-header">
            <h3 style="margin:0; font-size: 18px;">Access Configuration: <span id="choiceFeatureName"></span></h3>
            <i class="fa fa-times" style="cursor:pointer;" onclick="closeChoiceModal()"></i>
        </div>
        <div class="nova-modal-body">
            <p style="font-size: 13px; color: #64748b; margin-bottom: 20px;">Please select how you want to allocate this feature for the employee.</p>
            <div class="choice-grid">
                <div class="choice-card" onclick="handleChoice('branch')">
                    <i class="fa fa-building"></i>
                    <div class="choice-title">Branch Wise</div>
                    <div class="choice-desc">Allocate feature to specific branches. Defaults to all.</div>
                </div>
                <div class="choice-card" onclick="handleChoice('hierarchy')">
                    <i class="fa fa-sitemap"></i>
                    <div class="choice-title">Hierarchy Wise</div>
                    <div class="choice-desc">Feature will follow employee's organizational hierarchy.</div>
                </div>
            </div>
        </div>
        <div class="nova-modal-footer">
            <button class="btn-secondary" onclick="cancelChoice()">Cancel</button>
        </div>
    </div>
</div>

<script>
    var currentFeatureId = null;
    var hasMadeChoice = false;

    // ── Auto-allocate default menus on load ────────────────────────────────
    $(document).ready(function () {
        var empId = $('#emp_pkey').val();
        if (!empId) return;

        // Show a subtle banner while checking
        var $banner = $('<div id="autoAllocBanner" style="' +
            'position:absolute; top:0; left:0; right:0; padding:8px 16px;' +
            'background:rgba(30,81,110,0.08); border-radius:12px 12px 0 0;' +
            'font-size:12px; color:#1e516e; text-align:center; display:none;' +
            '">⚙ Checking default menu allocation...</div>');
        $('.nova-card').first().css('position','relative').prepend($banner);

        $.ajax({
            url: livesite + 'UserAccess/autoAllocateDefault/' + empId,
            type: 'GET',
            success: function(resp) {
                try {
                    var r = JSON.parse(resp);
                    if (r.status === 'allocated') {
                        // Menus were just allocated — reload the insec panel to show them
                        $.notify('Default menus have been automatically allocated.', {
                            type: 'success',
                            allow_dismiss: false
                        });
                        setTimeout(function() {
                            $('#loadArea').load(livesite + 'UserAccess/insecs/' + empId);
                        }, 800);
                    }
                } catch(e) {}
            }
        });
    });
    // ──────────────────────────────────────────────────────────────────────

    function toggleMenu(menuId, el, name, isParent) {
        var active = $(el).is(':checked') ? 'Y' : 'N';
        var url = active == 'Y' ? 'userAccess/save/' : 'userAccess/delete/';
        var empId = $('#emp_pkey').val();

        if (isParent) {
            if (active == 'Y') {
                $('#child_' + menuId).slideDown(300);
                // Turn on all child menus when parent is enabled
                $('#child_' + menuId + ' .nova-switch input:not(:checked)').each(function() {
                    var childEl = $(this);
                    childEl.prop('checked', true);
                    var childMenuId = childEl.attr('onchange').match(/'([^']+)'/)[1];
                    $.ajax({
                        url: livesite + 'userAccess/save/' + empId + '/' + childMenuId,
                        success: function() {
                            childEl.closest('.menu-item').find('.menu-status-label').text('Enabled');
                        }
                    });
                });
            } else {
                $('#child_' + menuId).slideUp(300);
                // Turn off all child menus when parent is disabled
                $('#child_' + menuId + ' .nova-switch input:checked').each(function() {
                    var childEl = $(this);
                    childEl.prop('checked', false);
                    var childMenuId = childEl.attr('onchange').match(/'([^']+)'/)[1];
                    $.ajax({
                        url: livesite + 'userAccess/delete/' + empId + '/' + childMenuId,
                        success: function() {
                            childEl.closest('.menu-item').find('.menu-status-label').text('Disabled');
                        }
                    });
                });
            }
            $(el).closest('.menu-item').find('.menu-status-label').text(active == 'Y' ? 'Enabled' : 'Disabled');
        } else {
            // SUBMENU SYNC
            var parentContainer = $(el).closest('.child-container');
            if (parentContainer.length) {
                var parentId = parentContainer.attr('id').split('_')[1];
                var parentInput = $('#parent_' + parentId);
                
                // If ID selector fails, try relative selector
                if (!parentInput.length) {
                    parentInput = parentContainer.prev('.menu-item').find('.nova-switch input');
                }

                if (active == 'Y') {
                    // If child turned ON -> parent must be ON
                    if (parentInput.length && !parentInput.prop('checked')) {
                        parentInput.prop('checked', true);
                        parentInput.closest('.menu-item').find('.menu-status-label').text('Enabled');
                        if (!parentContainer.is(':visible')) parentContainer.slideDown(300);
                    }
                } else {
                    // If child turned OFF -> check if ANY other submenus are still ON
                    var activeChildren = parentContainer.find('.nova-switch input:checked').not(el).length;
                    
                    if (activeChildren === 0) {
                        // All submenus are OFF -> Turn off the parent
                        if (parentInput.length && parentInput.prop('checked')) {
                            parentInput.prop('checked', false);
                            parentInput.closest('.menu-item').find('.menu-status-label').text('Disabled');
                            if (parentContainer.is(':visible')) parentContainer.slideUp(300);
                            
                            // Also notify server to explicitly disable parent
                            $.ajax({
                                url: livesite + 'userAccess/delete/' + empId + '/' + parentId
                            });
                        }
                    }
                }
            }
        }



        $.ajax({
            url: livesite + url + empId + '/' + menuId,
            success: function(resp){
                $.notify((active == 'Y' ? 'Enabled ' : 'Disabled ') + name, {
                    type: 'success',
                    allow_dismiss: false
                });
                $(el).closest('.menu-item').find('.menu-status-label').text(active == 'Y' ? 'Enabled' : 'Disabled');
            }
        });
    }



    function toggleFeature(featureId, el) {
        var active = $(el).is(':checked') ? 'Y' : 'N';
        var empId = $('#emp_pkey').val();
        var featureName = $(el).closest('.addon-card').find('.addon-name').text();

        if (active == 'Y') {
            currentFeatureId = featureId;
            hasMadeChoice = false; // Reset choice flag
            $('#choiceFeatureName').text(featureName);
            $('#choiceModal').css('display', 'flex');
        } else {
            $.ajax({
                url: livesite + 'UserAccess/saveFeatureAccess',
                type: 'POST',
                data: { user_fkey: empId, feature_id: featureId, active: 'N' },
                success: function(resp){
                    var r = JSON.parse(resp);
                    $.notify(r.msg, { type: 'success', allow_dismiss: false });
                    $('#addon_' + featureId + ' .addon-footer').hide();
                }
            });
        }
    }

    function handleChoice(type) {
        var empId = $('#emp_pkey').val();
        var featureName = $('#choiceFeatureName').text();
        var localFeatureId = currentFeatureId;
        
        hasMadeChoice = true;
        closeChoiceModal();
        
        if (type == 'hierarchy') {
            $.ajax({
                url: livesite + 'UserAccess/saveFeatureAccess',
                type: 'POST',
                data: { user_fkey: empId, feature_id: localFeatureId, active: 'Y', mode: 'hierarchy' },
                success: function(resp){
                    $.notify('Hierarchy Access Enabled', { type: 'success', allow_dismiss: false });
                    updateAddonButtons(localFeatureId, 'hierarchy');
                }
            });
        } else {
            // Branch Wise selected
            $.ajax({
                url: livesite + 'UserAccess/saveFeatureAccess',
                type: 'POST',
                data: { user_fkey: empId, feature_id: localFeatureId, active: 'Y', mode: 'branch' },
                success: function(resp){
                    updateAddonButtons(localFeatureId, 'branch');
                    openBranchModal(localFeatureId, featureName);
                }
            });
        }
    }

    function switchToHierarchy(featureId, featureName) {
        var empId = $('#emp_pkey').val();
        var performSwitch = function() {
            $.ajax({
                url: livesite + 'UserAccess/saveFeatureAccess',
                type: 'POST',
                data: { user_fkey: empId, feature_id: featureId, active: 'Y', mode: 'hierarchy' },
                success: function(resp){
                    $.notify('Switched to Hierarchy Access', { type: 'success', allow_dismiss: false });
                    updateAddonButtons(featureId, 'hierarchy');
                }
            });
        };

        if (typeof $.confirm === 'function') {
            $.confirm({
                title: 'Switch to Hierarchy?',
                content: 'This will remove all specific branch selections and apply hierarchy-wise access for ' + featureName + '.',
                theme: 'modern',
                buttons: {
                    confirm: {
                        text: 'Switch',
                        btnClass: 'btn-red',
                        action: function () {
                            performSwitch();
                        }
                    },
                    cancel: function () {}
                }
            });
        } else {
            if (confirm('Switch to Hierarchy? This will remove all specific branch selections for ' + featureName + '.')) {
                performSwitch();
            }
        }
    }

    function updateAddonButtons(featureId, mode) {
        $('#addon_' + featureId + ' .addon-footer').css('display', 'flex');
        if (mode == 'hierarchy') {
            $('#btn_manage_' + featureId).text('Set Branch Wise').show();
            $('#btn_hierarchy_' + featureId).hide();
        } else {
            $('#btn_manage_' + featureId).text('Manage Branch').show();
            $('#btn_hierarchy_' + featureId).show();
        }
    }

    function closeChoiceModal() {
        if (!hasMadeChoice) {
            // If user closed with X without picking, default to branch-wise
            var empId = $('#emp_pkey').val();
            var localFeatureId = currentFeatureId;
            $.ajax({
                url: livesite + 'UserAccess/saveFeatureAccess',
                type: 'POST',
                data: { user_fkey: empId, feature_id: localFeatureId, active: 'Y', mode: 'branch' },
                success: function(resp){
                    $.notify('Default Branch-wise Access Applied', { type: 'success', allow_dismiss: false });
                    updateAddonButtons(localFeatureId, 'branch');
                }
            });
        }
        $('#choiceModal').hide();
    }

    function cancelChoice() {
        var featureId = currentFeatureId;
        hasMadeChoice = true; // Prevents the closeChoiceModal default logic
        
        $('#addon_' + featureId + ' .nova-switch input').prop('checked', false);
        $('#addon_' + featureId + ' .addon-footer').hide();
        $('#choiceModal').hide();
        $.notify('Feature allocation cancelled', { type: 'info', allow_dismiss: false });
    }

    function openBranchModal(featureId, featureName) {
        currentFeatureId = featureId;
        $('#modalFeatureName').text(featureName);
        $('#branchModal').css('display', 'flex');
        
        var empId = $('#emp_pkey').val();
        
        // Reset and Load branch-feature data
        $('.branch-check').prop('checked', false);
        $('#selectAllBranches').prop('checked', false);
        
        $.ajax({
            url: livesite + 'UserAccess/getFeatureBranches/' + empId + '/' + featureId,
            success: function(resp){
                var data = JSON.parse(resp);
                var allocated = data.branches;
                var isHierarchy = data.is_hierarchy;
                
                if (isHierarchy == 'Y') {
                    // Changing from Hierarchy to Branch
                    updateAddonButtons(featureId, 'branch');
                    $('.branch-check').prop('checked', true); 
                    $('#selectAllBranches').prop('checked', true);
                } else {
                    updateAddonButtons(featureId, 'branch');
                    if (allocated && allocated.length > 0) {
                        allocated.forEach(function(branchCode) {
                            $('#b_' + branchCode).prop('checked', true);
                        });
                        updateSelectAllState();
                    } else {
                        $('.branch-check').prop('checked', true);
                        $('#selectAllBranches').prop('checked', true);
                    }
                }
            }
        });
    }

    function updateSelectAllState() {
        var total = $('.branch-check').length;
        var checked = $('.branch-check:checked').length;
        $('#selectAllBranches').prop('checked', total === checked);
    }

    function closeBranchModal() {
        $('#branchModal').hide();
    }

    function toggleSelectAllBranches(el) {
        $('.branch-check').prop('checked', $(el).is(':checked'));
    }

    // function saveBranchAccess() {
    //     var selectedBranches = [];
    //     $('.branch-check:checked').each(function() {
    //         selectedBranches.push($(this).val());
    //     });

    //     var empId = $('#emp_pkey').val();

    //     $.ajax({
    //         url: livesite + 'UserAccess/saveBranchAccess',
    //         type: 'POST',
    //         data: { user_fkey: empId, feature_id: currentFeatureId, branches: selectedBranches },
    //         success: function(resp){
    //             var r = JSON.parse(resp);
    //             $.notify(r.msg, { type: 'success', allow_dismiss: false });
    //             updateAddonButtons(currentFeatureId, 'branch');
    //             closeBranchModal();
    //         }
    //     });
    // }

    function saveBranchAccess() {
    var selectedBranches = [];

    $('.branch-check:checked').each(function() {
        selectedBranches.push($(this).val());
    });

    if (selectedBranches.length === 0) {
        $.notify("Please select at least one branch", { 
            type: 'danger', 
            allow_dismiss: false 
        });
        return; // stop here
    }

    var empId = $('#emp_pkey').val();

    $.ajax({
        url: livesite + 'UserAccess/saveBranchAccess',
        type: 'POST',
        data: { 
            user_fkey: empId, 
            feature_id: currentFeatureId, 
            branches: selectedBranches 
        },
        success: function(resp){
            var r = JSON.parse(resp);
            $.notify(r.msg, { type: 'success', allow_dismiss: false });
            updateAddonButtons(currentFeatureId, 'branch');
            closeBranchModal();
        }
    });
}

    $("#branchSearch").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $(".branch-row").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
        var visible = $(".branch-row:visible").length;
        $("#branchNoData").toggle(visible === 0 && value !== '');
    });

    $("#addonSearch").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $(".addon-card").filter(function() {
            var name = $(this).find('.addon-name').text().toLowerCase();
            var desc = $(this).find('.addon-desc').text().toLowerCase();
            $(this).toggle(name.indexOf(value) > -1 || desc.indexOf(value) > -1);
        });
        var visible = $(".addon-card:visible").length;
        $("#addonNoData").toggle(visible === 0 && value !== '');
    });

    $("#systemMenuSearch").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        var anyVisible = false;

        $(".menu-list > .menu-item").each(function() {
            var parentItem = $(this);
            var parentText = parentItem.find('.menu-name-label').first().text().toLowerCase();
            var parentId   = parentItem.find('[data-parent-id]').data('parent-id');
            var childContainer = $('#child_' + parentId);

            var hasMatchingChild = false;
            if (childContainer.length) {
                childContainer.find('.menu-item').each(function() {
                    var childText = $(this).find('.menu-name-label').text().toLowerCase();
                    if (childText.indexOf(value) > -1) {
                        $(this).show();
                        hasMatchingChild = true;
                    } else {
                        $(this).hide();
                    }
                });
            }

            if (parentText.indexOf(value) > -1 || hasMatchingChild) {
                parentItem.show();
                anyVisible = true;
                if (hasMatchingChild && value !== '') {
                    childContainer.show();
                } else if (value === '') {
                    var isChecked = parentItem.find('input[type="checkbox"]').is(':checked');
                    if (isChecked) childContainer.show(); else childContainer.hide();
                }
            } else {
                parentItem.hide();
                childContainer.hide();
            }
        });

        $("#systemMenuNoData").toggle(!anyVisible && value !== '');
    });

</script>
