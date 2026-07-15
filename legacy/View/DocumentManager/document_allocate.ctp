<section>
    <div class="row">
        <!-- <div class="modal-dialog" style="width: 100%; max-width: 1200px;"> -->
            <!-- Modal content-->

            <div class="modal-content">
                <div class="modal-header" style="background: #00659f; color: white; display: flex; justify-content: space-between;padding-top:20px;">
                    <h4 class="modal-title" style="margin: 0;margin-top:20px;"> Allocate Document </h4>
                    <button type="button" class="close" id="largeModalForm" onclick="closeModal()" style="align-self: center; margin-top:20px;">&times;</button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <input type="hidden" id="site_pkey" name="site_pkey" value="<?php echo isset($site_pkey) ? $site_pkey : ''; ?>">

                        <div class="col-md-12">
                            <label for="attendance_type" class="col-sm-4 control-label">Employee Name<span class="star">*</span></label>
                            <div>
                                <select id="emp_fkeys" class="form-control js-example-basic-single pull-left" style="width: 40%;" name="emp_fkeys">
                                    <option value="">Select Employee</option>
                                    <?php
                                    foreach ($arr_employees as $value) {
                                        echo '<option value="' . $value['EmployeeDetails']['emp_pkey'] . '" >' . $value['EmployeeDetails']['first_name'] . ' ' . $value['EmployeeDetails']['last_name'] . ' - ' . $value['ei']['employee_id'] . '</option>';
                                    }
                                    ?>
                                </select>
                                <button style="margin-left: 5px;" onclick="allocate_emps();" class="btn btn-primary">Add</button>
                                <button style="margin-left: 5px;" onclick="allocate_all();" class="btn btn-primary">Allocate all</button>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <h4>Allocated Employees</h4>
                            <table class="table table-responsive table-bordered">
                                <tbody id="allocating">
                                    <tr>
                                        <th>Employee Name</th>
                                        <th>Action</th>
                                    </tr>
                                    <?php foreach($arr_employees_allocates as $val) { ?>
                                        <tr>
                                            <td><?php echo $val['emp_details']['first_name'].' '.$val['emp_details']['last_name'] . ' - ' . $val['employee_info']['employee_id'] ; ?></td>
                                            <td><button class="btn btn-danger"><li class="fa fa-remove" onclick="removeemps(this,<?php echo $val['document_allocation']['emp_fkey']; ?>)"></li></button></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div style="text-align: right;">
                        <button type="button" class="btn btn-danger" id="largeModalForm" onclick="closeModal()" style="align-self: center;">Close</button>
                    </div>
                </div>
            </div>
        <!-- </div> -->
    </div>
</section>

<script>
    $('#emp_fkeys').select2();

    function closeModal() {
        $('#largeModalForm').modal('hide');  // Assuming hiding the element means hiding the modal
    }
    
    function allocate_emps() {
    var site_code = $('#site_pkey').val();
    var emps = $('#emp_fkeys').val();

    if (emps) {
        $.ajax({
            url: 'DocumentManager/save_allocate/',
            type: 'POST',
            data: {
                site_code: site_code,
                emps: emps
            },
            success: function (resp) {
                var response = $.parseJSON(resp);
                if (response.success == 0) {
                    $.notify($.parseJSON(resp).msg, {
                        type: 'danger',
                        allow_dismiss: false,
                        z_index: 9999
                    });
                } else {
                    $.notify($.parseJSON(resp).msg, {
                        type: 'success',
                        allow_dismiss: false,
                        z_index: 9999
                    });

                    // Get the selected employee text
                    var selectedEmployee = $('#emp_fkeys option:selected').text();

                    // Append the selected employee to the table
                    var newRow = $('<tr><td>' + selectedEmployee + '</td><td><button class="btn btn-danger"><li class="fa fa-remove" onclick="removeemps(this, ' + emps + ')"></li></button></td></tr>');
                    $('#allocating').append(newRow);

                    // Sort the table rows (excluding the first row, assuming it's the table heading)
                    var rows = $('#allocating tr').not(':first');
                    rows.sort(function(a, b) {
                        var textA = $(a).text().toUpperCase();
                        var textB = $(b).text().toUpperCase();
                        return (textA < textB) ? -1 : (textA > textB) ? 1 : 0;
                    });

                    $('#allocating').append(rows);

                    // Remove the employee from the dropdown
                    $('#emp_fkeys option[value=' + emps + ']').remove();
                }
                reloadTable('documents_manager');
                $('#emp_fkeys').val('').trigger('change');
            }
        });
    } else {
        $.notify("Please choose an employee.", {
            type: 'danger',
            allow_dismiss: false,
            z_index: 9999
        });
    }
}



    //Edited by Akshay on 29-10-2023
    function allocate_all() {
    var site_code = $('#site_pkey').val();
    var emps = $('#emp_fkeys').val();

    $.ajax({
        url: 'DocumentManager/save_allocate/',
        type: 'POST',
        data: {
            site_code: site_code,
            emps: 'all-employees'
        },
        success: function (resp) {
            var response = $.parseJSON(resp);
            if (response.success == 0) {
                $.notify($.parseJSON(resp).msg, {
                    type: 'danger',
                    allow_dismiss: false,
                    z_index: 9999
                });
            } else {
                $.notify($.parseJSON(resp).msg, {
                    type: 'success',
                    allow_dismiss: false,
                    z_index: 9999
                });
                reloadTable('documents_manager');

                // Get the selected employees' text
                var selectedEmployees = [];
                    $('#allocating tr').each(function(index, row) {
                        if (index !== 0) {
                            var employeeName = $(row).find('td:first').text().trim();
                            selectedEmployees.push(employeeName);
                            console.log('selectedEmployees',$(row));
                        }
                    });
                // Filter employees that are not already in the list
                var allEmployees = <?php echo json_encode($arr_employees); ?>;
                var unselectedEmployees =JSON.parse(resp);
                $('#allocating tr:not(:first)').remove();
                // Append unselected employees to the list
                unselectedEmployees.data.forEach(function (emp) {
                    console.log('Response',emp);
                    $('#allocating').append('<tr><td>' + emp.ei.EmpName + ' - ' + emp.ei.employee_id + '</td><td><button class="btn btn-danger"><li class="fa fa-remove" onclick="removeemps(this, ' + emp.ei.emp_pkey + ')"></li></button></td></tr>');
                    
                    $('#emp_fkeys option[value=' + emp.ei.emp_pkey + ']').remove();
                });

            }
        }
    });
}


    
function removeemps(s, emps) {
    var site_code = $('#site_pkey').val();
    var emps = emps;
    $.ajax({
        url: 'DocumentManager/remove_allocate/',
        type: 'POST',
        data: {
            site_code: site_code,
            emps: emps
        },
        success: function (resp) {
            var response = $.parseJSON(resp);
            if (response.success == 0) {
                $.notify($.parseJSON(resp).msg, {
                    type: 'danger',
                    allow_dismiss: false,
                    z_index: 9999
                });
            } else {
                $.notify($.parseJSON(resp).msg, {
                    type: 'success',
                    allow_dismiss: false,
                    z_index: 9999
                });
                $(s).parent().parent().parent().hide();

                // Here, you should make an additional AJAX call to fetch the removed employee's information and add it back to the dropdown.
                $.ajax({
                    url: 'DocumentManager/fetch_employee_details/',
                    type: 'POST',
                    data: {
                        empId: emps
                    },
                    success: function (empDetails) {
                        var employee = $.parseJSON(empDetails);
                        console.log('Employee',employee);
                        $('#emp_fkeys').append($('<option>', {
                            value: employee.EmployeeDetails.emp_pkey,
                           // text: employee.EmployeeDetails.first_name +' '+employee.EmployeeDetails.last_name + ' - '+employee.ei.employee_id   
                           text: employee.ei.EmpName + ' - '+employee.ei.employee_id 
                        }));

                        
                        // Collect all the options in an array and sort them alphabetically
                        var options = $('#emp_fkeys option:not(:first-child)');
                        console.log('Options', options);
                        var arr = options.map(function (_, o) { return { t: $(o).text(), v: o.value }; }).get();
                        arr.sort(function (o1, o2) {
                            return o1.t.localeCompare(o2.t, undefined, { sensitivity: 'base' });
                        });

                        // Remove existing options from the dropdown except first placeholder
                        $('#emp_fkeys option:not(:first-child)').remove();

                        // Append sorted options back to the dropdown
                        $.each(arr, function (_, obj) {
                            $('#emp_fkeys').append($('<option>').text(obj.t).attr('value', obj.v));
                        });
                    }
                });
            }
        }
    });
}

</script>