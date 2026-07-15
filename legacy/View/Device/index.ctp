<style>
    /* Edited by bindu 24-10-2025 */
    	.heading {
		display: flex;
		flex-direction: row;
		align-items: end;
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

    /* End */
 </style>
 
 <!-- /* edited by bindu 24-10-25 */ -->
<section class="content-header heading"> 
    <h1 class="text-primary-18">Device</h1>
  <?php if ($plan !== 'basic') : ?>
    
    <div class="text-primary-16 home"
         style="display:flex; align-items:center; gap:10px; cursor:pointer;">
        <i class="fa" style="font-size:16px;">&#xf104;</i>
        Back
    </div>

<?php endif; ?>

</section>
<hr style="margin-top: 8px;margin-bottom: -2px;margin-right: 15px;margin-left: 15px;">   
<!-- end -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-body">
                    <div class="col-md-1"></div>
                    <div class="col-md-6">
                    <label class="col-md-3" for="filterby_serial">Serial Number</label>
                        <div class="col-md-1">:</div>
                        <div class="col-md-8">
                            <select id="filterby_serial" name="filterby_serial" class="form-control" onchange="filterDevice(this);" >
                                <option value="">All</option>
                                <?php
                                foreach($devices as $device){
                                    echo "<option value = '".$device['devices']['SerialNumber']."'>".$device['devices']['SerialNumber']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-5 pull-right row">
                        <label class="col-md-3" for="filterby_branch">Branch</label>
                        <div class="col-md-1">:</div>
                        <div class="col-md-8">
                            <select id="filterby_branch" name="filterby_branch" class="form-control" onchange="filterBranch(this);" >
                                <option value="">All</option>
                                <?php
                               
                                foreach($branches as $branch){
                                    $selected = (isset($emp_dev_data['branch_code']) && $emp_dev_data['branch_code'] == $branch['company_branches']['branch_code']) ? 'selected' : '';
                                    echo "<option value = '".$branch['company_branches']['branch_code']."' ".$selected.">".$branch['company_branches']['branch_name']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <table id="device" class="table table-hover table-striped">
                    </table>
                </div><!-- /.box-body -->

            </div><!-- /. box -->
        </div><!-- /.col -->
    </div><!-- /.row -->
</section><!-- /.content -->

<script>
    jQuery(document).ready(function() {

        $('#device').datagrid({
            url: livesite + "Device/listDev",
            //            title: "Employee",
            fitColumns: true,
            singleSelect: true,
            autoRowHeight: false,
            pagination: true,
            PostsearchFilter: true,
            rownumbers: true,
            pageSize: 10,
            width: '100%',
            toolbar: [{
                iconCls: 'icon-add',
                text: 'Add',
                handler: function() {
                    showModalForm(livesite + 'Device/addEditDev');
                }
            },{
                iconCls: 'icon-edit',
                text: 'Edit',
                handler: function() {
                    var row = $('#device').datagrid('getSelected');
                    if (row) {
                        showModalForm(livesite + 'Device/addEditDev/' + row.DeviceId);
                    } else {
                        alert("Please select a record to edit")
                    }
                }
            }],
            fitColumns: true,
            pageList: [2, 5, 10, 50, 100],
            columns: [
                [{
                        field: 'DeviceFName',
                        title: 'Device Name',
                        width: "25%",
                        sortable: true
                    },
                    // {
                    //     field: 'branch_code',
                    //     title: 'Branch Code',
                    //     width: "20%",
                    //     sortable: true
                    // },
                    {
                        field: 'branch_name',
                        title: 'Branch Name',
                        width: "25%",
                        sortable: true
                    },
                    {
                        field: 'SerialNumber',
                        title: 'Serial Number',
                        width: "25%",
                        sortable: true
                    },
                    // {
                    //     field: 'DeviceFName',
                    //     title: 'Device Name',
                    //     width: "16%",
                    //     sortable: true
                    // },
                    {
                        field: 'DeviceLocation',
                        title: 'Device Location',
                        width: "25%",
                        sortable: true
                    }
                ]
            ],
            onSearch: function(s) {

                $('#device').datagrid('load', {
                    emp: $('#searchqupo').val(),
                    // name: $('#rsndempid').val(),
                    branch: $('#filterby_branch').val(),
                    serial_no: $('#filterby_serial').val()
                });
            },
        });

        $("#filterby_serial").select2();
        $("#filterby_branch").select2();

    });

    function filterBranch(obj) {
        var branch = $('#filterby_branch').val();
        var serial_no = $('#filterby_serial').val();
        $('#device').datagrid('load', {
            emp: $('#searchqupo').val(),
            serial_no:serial_no,
            branch:branch,
        });
    }
    function filterDevice(obj) {
        var serial_no = $('#filterby_serial').val();
        var branch = $('#filterby_branch').val();
        $('#device').datagrid('load', {
            emp: $('#searchqupo').val(),
            serial_no:serial_no,
            branch:branch,
        });
    }
    
	 /* edited by bindu 19-02-26 */
   $(".home").on("click", function () {

    $("#container").isLoading({
        text: "Loading",
        position: "overlay",
    });

    let url = "";
    var userGroup = <?php echo json_encode($this->Session->read('user_group')); ?>

    if (userGroup == "1") {
        url = livesite + "CompanySetup/index";
    } 
    else if (userGroup == "2") {
        url = livesite + "EmployeeMenu/addon";
    }

    $("#container").load(url, function () {
        isDashboardShown = false;
    });

});

	/* edited by bindu 19-02-26 */
</script>