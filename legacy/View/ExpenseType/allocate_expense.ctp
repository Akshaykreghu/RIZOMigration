<section>
    <div class="row">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"> Expense Allocation </h4>  
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <input type="hidden" id="expense_type_pkey" name="expense_type_pkey" value="<?php echo isset($expense_type_pkey) ? $expense_type_pkey : ''; ?>" > 

                        <label for="attendance_type" class="col-sm-4 control-label">Designation Name<span class="star">*</span></label>                         

                        <div class="col-md-8">
                            <!-- onchange="filterAttendanceupload(this);" -->
                            <select id="desig_fkeys" class="form-control js-example-basic-single pull-left" style="width: 60%; " name="desig_fkeys" >
                                <option value="">Select</option>
                                <option value="ALL">All</option>
                                <?php
                                foreach ($arr_designation as $value) {
                                    echo '<option value="' . $value['Designation']['id'] . '" >' . $value['Designation']['desig_name'] . '</option>';
                                }
                                ?>
                            </select>
                            <button style="margin-left: 12px; " onclick="allocate_designation();" class="btn btn-primary">Add</button>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-12">
                            <h4>Allocated Designations </h4>
                            <table class="table table-responsive table-bordered">
                                <tbody id="allocating">
                                    <tr>
                                        <th>Designation Name</th>
                                        <th>Action</th>
                                    </tr>
                                    <?php foreach ($arr_designation_allocates as $val) {
                                        ?>
                                        <tr>
                                            <td><?php echo $val['designation']['desig_name']; ?></td>
                                            <td><button class="btn btn-danger" onclick="removedesignation(this,<?php echo $val['allocate_expense']['designation_fkey']; ?>)"><li class="fa fa-remove"></li></button></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div> 
            </div>
        </div>
    </div>
</section>
<script>
    $('#desig_fkeys').select2();
//    $("#desig_fkeys").select2({
//        placeholder: "Select a designation",
////        initSelection: function (element, callback) {
////        }
//    });

    function allocate_designation() {
        $('button').prop('disabled', true);
        setTimeout(function () {
            $('button').prop('disabled', false);
        }, 1500);
        var expense_type_pkey = $('#expense_type_pkey').val();
        var desigs = $('#desig_fkeys').val();
        if (desigs == "") {
            alert('Select any designation');
            return false;
        }
        $.ajax({
            url: 'ExpenseType/save_allocate/',
            type: 'POST',
            data: {
                expense_type_pkey: expense_type_pkey,
                desigs: desigs
            },
            success: function (resp)
            {
                var response = $.parseJSON(resp);
                if (response.success == 0) {
                    $.notify($.parseJSON(resp).msg, {
                        type: 'danger',
                        allow_dismiss: false,
                        z_index: 1051
                    });
                } else {
                    $.notify($.parseJSON(resp).msg, {
                        type: 'success',
                        allow_dismiss: false,
                        z_index: 1051
                    });
                    if (desigs == "ALL") {
                        closeModal();
                    }
                    $('#allocating').append('<tr><td>' + $('#desig_fkeys  option:selected').html() + '</td><td><button class="btn btn-danger" onclick="removedesignation(this,' + desigs + ')"><li class="fa fa-remove"></li></button></td></tr>');
                    var val = "<option value=''>Select</option>";
                    var val = val + "<option value='ALL'>All</option>";
                    for (var i = 0; i < response.array.length; i++) {
                        val = val + "<option value='" + response.array[i]["Designation"]["id"] + "'>" + response.array[i]["Designation"]["desig_name"] + "</option>"
                    }
                    $('#desig_fkeys').html(val);
                    $("#desig_fkeys").select2("val", "");
                }
            }
        });
    }

    function removedesignation(s, desigs) {
        $('button').prop('disabled', true);
        setTimeout(function () {
            $('button').prop('disabled', false);
        }, 1500);
        var expense_type_pkey = $('#expense_type_pkey').val();
        var desigs = desigs;
        $.ajax({
            url: 'ExpenseType/remove_allocate/',
            type: 'POST',
            data: {
                expense_type_pkey: expense_type_pkey,
                desigs: desigs
            },
            success: function (resp)
            {
                var response = $.parseJSON(resp);
                if (response.success == 0) {
                    $.notify($.parseJSON(resp).msg, {
                        type: 'danger',
                        allow_dismiss: false,
                        z_index: 1051
                    });
                } else {
                    $.notify($.parseJSON(resp).msg, {
                        type: 'success',
                        allow_dismiss: false,
                        z_index: 1051
                    });
                    $(s).parent().parent().hide();
                    var val = "<option value=''>Select</option>";
                    var val = val + "<option value='ALL'>All</option>";
                    for (var i = 0; i < response.array.length; i++) {
                        val = val + "<option value='" + response.array[i]["Designation"]["id"] + "'>" + response.array[i]["Designation"]["desig_name"] + "</option>"
                    }
                    $('#desig_fkeys').html(val);
                }
            }
        });
    }
</script>