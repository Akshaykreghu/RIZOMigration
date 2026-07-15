  <!-- /* edited by bindu 29-11-2025 */ -->
   <style>
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
       /* edited by bindu 29-11-2025 end */
   </style>
      <!-- /* edited by bindu 29-11-2025  */ -->
   <section class="content-header heading">
       <!-- edited by athira on 03-07-2025 -->
       <h1 class="text-primary-18"><!-- Employee Devices --> Employee Device Information</h1>
    <?php if ($plan !== 'basic') : ?>
    
    <div class="text-primary-16 home"
         style="display:flex; align-items:center; gap:10px; cursor:pointer;">
        <i class="fa" style="font-size:16px;">&#xf104;</i>
        Back
    </div>

<?php endif; ?>


   </section>
   <hr style="margin:8px 15px -2px 15px;">
   <!-- edited by bindu 29-11-2025 -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <!-- <div class="box-header with-border">
                    <h6 class="box-title">Employee Device Information</h6>
                </div> --><!-- /.box-header -->
                <div class="box-body">
                    <div class="col-md-6"></div>
                    <div class="col-md-5  row">
                        <label class="col-md-3" for="filterby_device">Device Name</label>
                        <div class="col-md-1">:</div>
                        <div class="col-md-8">
                            <select id="filterby_device" name="filterby_device" class="form-control" onchange="filterDevice(this);" >
                                <option value="">All</option>
                                <?php
                                foreach($devices as $device){
                                    $selected = (isset($emp_dev_data['deviceid']) && $emp_dev_data['deviceid'] == $device['devices']['DeviceId']) ? 'selected' : '';
                                    echo "<option value = '".$device['devices']['DeviceId']."' ".$selected.">".$device['devices']['DeviceFName']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                      <div class="col-md-1 pull-right" align="right">
                            <button type="button" id="btn-downloademployeectcform" class="btn btn-danger btn-sm " onclick="downloadEmpDeviceUploadForm()" ><i class="fa fa-download" aria-hidden="true"></i></button>
                        </div>
                    <table id="empdevice" class="table table-hover table-striped">
                    </table>
                </div><!-- /.box-body -->

            </div><!-- /. box -->
        </div><!-- /.col -->
    </div><!-- /.row -->
</section><!-- /.content -->

<script>
    jQuery(document).ready(function() {

        $('#empdevice').datagrid({
            url: livesite + "DeviceEmployeeInfo/listEmpDev",
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
                iconCls: 'icon-edit',
                text: 'Edit',
                handler: function() {
                    var row = $('#empdevice').datagrid('getSelected');
                    if (row) {
                        showModalForm(livesite + 'DeviceEmployeeInfo/editEmpDev/' + row.emp_device_comp_branch_seq);
                    } else {
                        alert("Please select a record to edit")
                    }
                }
            }],
            fitColumns: true,
            pageList: [2, 5, 10, 50, 100],
            columns: [
                [{
                        field: 'emp_username',
                        title: 'Employee ID',
                        width: "16%",
                        sortable: true
                    },
                    {
                        field: 'emp_name',
                        title: 'Employee Name',
                        width: "17%",
                        sortable: true
                    },
                    // {
                    //     field: 'branch_name',
                    //     title: 'Branch Name',
                    //     width: "17%",
                    //     sortable: true
                    // },
                    {
                        field: 'deviceid',
                        title: 'Device ID',
                        width: "16%",
                        sortable: true
                    },
                    {
                        field: 'DeviceFName',
                        title: 'Device Name',
                        width: "17%",
                        sortable: true
                    },
                    {
                        field: 'SerialNumber',
                        title: 'Serial Number',
                        width: "17%",
                        sortable: true
                    },
                    // {
                    //     field: 'DeviceFName',
                    //     title: 'Device Name',
                    //     width: "16%",
                    //     sortable: true
                    // },
                    {
                        field: 'emp_device_id',
                        title: 'Employee Device ID',
                        width: "16%",
                        sortable: true
                    }
                ]
            ],
            onSearch: function(s) {

                $('#empdevice').datagrid('load', {
                    emp: $('#searchqupo').val(),
                    // name: $('#rsndempid').val(),
                    device_id: $('#filterby_device').val()
                });
            },
        });

        $("#filterby_device").select2();

    });

    function filterDevice(obj) {
        var device = $('#filterby_device').val();
        $('#empdevice').datagrid('load', {
            emp: $('#searchqupo').val(),
            device_id:device,
        });

    }
function downloadEmpDeviceUploadForm() {
        
            window.open('<?php echo $this->webroot; ?>DeviceEmployeeInfo/downloadempuploadform/');
        
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