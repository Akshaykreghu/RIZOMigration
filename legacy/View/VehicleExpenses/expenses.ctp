<style>
    .form-horizontal .control-label{

        text-align: left;

    }
</style>

<section class="content-header">
    <h1 style="text-align:left; font-size: 3em;"> Vehicle Expenses</h1>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box ">
                <br>
                <div class="box-body">
                    <!-- Employee import form -->
                    <form class="form-horizontal" method="post" action="" id="importemployeectcform">
                        <div class="row">
                            <div class="form-group">
                                <div class="col-sm-4">
                                    <label class="col-sm-5 control-label" for="filterby_project">Choose Project</label>
                                    <div class="col-md-7">
                                        <select id="filterby_project" name="filterby_project" class="form-control js-example-basic-single" onchange="filterVehicleExpense(this);" >
                                            <option value="">All</option>
                                            <?php foreach ($arr_site as $key => $value) { ?>                              
                                                <option  value="<?php echo $value['Site']['site_pkey']; ?>"><?php echo $value['Site']['site_id'] . "-" . $value['Site']['site_name']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-sm-5 control-label" for="filterby_vehicle">Choose Vehicle</label>                        
                                    <div class="col-md-7">
<!--                                        <select id="filterby_vehicle" class="form-control js-example-basic-single" name="filterby_vehicle" onchange="filterVehicleExpense(this);"  >

                                        </select>-->
                                        <select id="filterby_vehicle" name="filterby_vehicle" class="form-control js-example-basic-single" onchange="filterVehicleExpense(this);" >
                                            <option value="">All</option>
                                            <?php foreach ($arr_vehicles as $key => $value) { ?>                              
                                                <option  value="<?php echo $value['vehicle_master']['vehicle_master_pkey']; ?>"><?php echo $value['vehicle_master']['make'] . "-" . $value['vehicle_master']['model_dec']; ?></option>
                                            <?php } ?>
                                        </select>

                                    </div>    
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="box-body" id="maintabtest">
                        <table id="att_table" class="table table-bordered table-hover">
                            <tbody>
                            </tbody>
                        </table>
                    </div><!-- /.box-body -->
                </div>

            </div>
        </div>
    </div>
</section>

<script>

    function filterVehicleExpense(obj) {//This is the site/emp filtering
        var site = $('#importemployeectcform #filterby_project').val();
        var vehicle = $('#importemployeectcform #filterby_vehicle').val()


        $('#att_table').datagrid('load', {
//            emp: $('#expense_status').val(),
            site: site,
            vehicle: vehicle,
        });

    }

    jQuery(document).ready(function () {
        $("#filterby_project").select2();
        $("#filterby_vehicle").select2();

        var employee = $('#attendanceuploadfilter #filterby_vehicle').val();

        $('#att_table').datagrid({
            url: livesite + "VehicleExpenses/vehicleexpenselist",
            pagination: true,
            singleSelect: true,
            rownumbers: true,
            onLoadSuccess: function (data) {
                loadtabs();
            },
            queryParams: {
                employee: employee
            },
            toolbar: [{
                    text: 'New',
                    iconCls: 'icon-add',
                    handler: function () {
                        showModalForm(livesite + 'VehicleExpenses/form')
                    }
                }, {
                    iconCls: 'icon-edit',
                    text: 'Edit',
                    handler: function () {
                        var row = $('#att_table').datagrid('getSelected');
                        if (row == null) {
                            alert('Please select any data');
                            return false;
                        }
                        var $expenseId = row.transportation_expense_pkey;
                        showModalForm(livesite + 'VehicleExpenses/form?transportation_expense_pkey=' + row.transportation_expense_pkey);
                    }
                }, {
                    iconCls: 'icon-remove',
                    text: 'Remove',
                    handler: function () {

                        var rows = $('#att_table').datagrid('getSelections');
                        if (rows.length > 0) {
                            var str_ids = "";
                            for (var i = 0; i < rows.length; i++) {
                                var data = rows[i];
                                if (str_ids == "") {
                                    str_ids += data.transportation_expense_pkey;
                                } else
                                {
                                    str_ids += "," + data.transportation_expense_pkey;
                                }
                            }
                            if (confirm("Do you want to delete the selected Record(s)?")) {

                                $.ajax({
                                    url: livesite + "VehicleExpenses/deleteEmployee",
                                    data: {
                                        ids: str_ids
                                    },
                                    success: function (response) {
                                        //var text = response.responseText;
                                        // process server response here
                                        reloadTable('att_table')
                                    }
                                });

                            }

                        } else {
                            alert("Please select any data");
                        }
                    }
                }
            ],
            fitColumns: true,
            pageList: [2, 5, 10, 50, 100],
            columns: [[
                    {field: 'reg_number', title: 'Vehicle Name', width: "15%"},
                    {field: 'driver_name', title: 'Driver Name', width: "10%"},
                    {field: 'site_name', title: 'Project Name', width: "15%"},
                    {field: 'purpose', title: 'Purpose', width: "10%"},
                    {field: 'starting_date', title: 'Start Date', width: '8%'},
                    {field: 'ending_date', title: 'Ending Date', width: '8%'},
                    {field: 'total_km', title: 'Total KM', width: '7%'},
                    {field: 'total_cost', title: 'Expense Amount', width: '12%'},
                    {field: 'payments', title: 'Paid Total', width: '7%'},
                    {field: 'balance', title: 'Balance', width: '7%'},
                ]],
            onSearch: function (s) {
                $('#att_table').datagrid('load', {
//                    emp: $('#expense_status').val(),
//                    site: $('#filterby_project').val()
                });
            }

        });
    });

</script>