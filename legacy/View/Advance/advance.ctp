<style>
    .form-horizontal .control-label{
        text-align: left;
    }
</style>

<section class="content-header">
    <h1 style="text-align:left; font-size: 3em;"> Employee Advances</h1>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box ">
                <br> <?php //debug($arr_employees); ?> 
                <div class="box-body">
                    <!-- Employee import form -->
                    <form class="form-horizontal" method="post" action="" id="importemployeectcform">
                        <div class="row">
                            <div class="form-group">
                                <div class="col-sm-4">
                                    <label class="col-sm-5 control-label" for="filterby_employee">Choose Employee</label>
                                    <div class="col-md-7">
                                        <select id="filterby_employee" name="filterby_employee" class="form-control js-example-basic-single" onchange="filterEmployee(this);" >
                                            <option value="">All</option>
                                            <?php foreach ($arr_employees as $key => $value) { ?>                              
                                                <option  value="<?php echo $value['EmployeeDetails']['emp_pkey']; ?>"><?php echo $value['0']['name'] . "-" . $value['emp_proff']['emp_company_id']; ?></option>
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

    function filterEmployee(obj) {//This is the site/emp filtering
      //  var site = $('#importemployeectcform #filterby_project').val();
      //  var vehicle = $('#importemployeectcform #filterby_vehicle').val()


        $('#att_table').datagrid('load', {
           emp: $('#filterby_employee').val(),
          //  site: site,
          //  vehicle: vehicle,
        });

    }

    jQuery(document).ready(function () {
        $("#filterby_employee").select2();
        //$("#filterby_vehicle").select2();

        var employee = $('#attendanceuploadfilter #filterby_employee').val();

        $('#att_table').datagrid({
            url: livesite + "Advance/advancelist",
            pagination: true,
            singleSelect: true,
            rownumbers: true,
            onLoadSuccess: function (data) {
            //    loadtabs();
            },
            queryParams: {
                employee: employee
            },
            toolbar: [{
                    text: 'New',
                    iconCls: 'icon-add',
                    handler: function () {
                        showModalForm(livesite + 'Advance/form')
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
                        var $expenseId = row.advance_pkey;
                        showModalForm(livesite + 'Advance/form?advance_pkey=' + row.advance_pkey);
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
                                    str_ids += data.advance_pkey;
                                } else
                                {
                                    str_ids += "," + data.advance_pkey;
                                }
                            }
                            if (confirm("Do you want to delete the selected Record(s)?")) {

                                $.ajax({
                                    url: livesite + "Advance/deleteEmployee",
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
                    {field: 'emp_name', title: 'Employee Name', width: "20%"},
                    {field: 'advance_date', title: 'Date', width: "10%"},
                    {field: 'bank_name', title: 'From Account', width: "29%"},
                    {field: 'amount', title: 'Amount', width: "15%"},
                    {field: 'remarks', title: 'Remarks', width: '25%'}
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
