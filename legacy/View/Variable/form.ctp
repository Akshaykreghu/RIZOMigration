<style>

    .form-horizontal .control-label {
        text-align: left;
        /*padding-left: 76px;*/
    }
    .form-group{
        height:34px; 
    }
    .modal-content{
        /* new custom width */
        width: 95% !important;
        /* must be half of the width, minus scrollbar on the left (30px) */
    }
</style>
<div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header" style="background: #00659f;color: white">
            <h4 class="modal-title">Variable Salary Upload</h4>  
        </div>
        <div class="modal-body">
            <form class="form-horizontal" id="attendanceuploadtable" action="<?php echo $this->webroot; ?>Variable/VariableSave" method="POST">

                <div class="modal-body">
                    <div class="form-group">
                        <div class="col-md-12">
                            <label for="in_date" class="col-md-4 control-label">Choose Employee<span class="star">*</span></label>
                            <div class="col-md-1">:</div>
                            <div class="col-md-7">
                                <!--                          <select id="emp_fkey" class="form-control" name="emp_fkey" onchange="loadExistingCTCInfo();" >-->
                                <select id="emp_fkey1" class="form-control js-example-basic-single" style="width: 100%" name="emp_fkey1" required="required" >
                                    <option value="">[--Select--]</option>
                                    <?php if (isset($arr_leaveupoload['0']['EmployeeVariableUpload']['emp_fkey'])) { ?>
                                        <option selected="selected" value="<?php echo $arr_leaveupoload['0']['EmployeeVariableUpload']['emp_fkey']; ?>"><?php echo $arr_leaveupoload['0']['Employee']['first_name'] . ' ' . $arr_leaveupoload['0']['Employee']['last_name']; ?></option>
                                    <?php } ?>

                                </select>
                            </div>    
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-12">
                            <label for="month" class="col-md-4 control-label ">Choose Month</label>
                            <div class="col-md-1">:</div>
                            <div class="col-md-7">
                                <select id="filterby_month" name="filterby_month" class="form-control" >                                                 
                                    <?php
                                    for ($i = -1; $i < 18; $i++) {
                                        $selected = '';
                                        if ($arr_leaveupoload['0']['EmployeeVariableUpload']['month_year'] == date('m-Y', strtotime("-$i month", strtotime(date('M-Y')))))
                                            $selected = 'selected="selected"';
                                        echo '<option ' . $selected . ' value="' . date('m-Y', strtotime("-$i month", strtotime(date('M-Y')))) . '">' . date('M-Y', strtotime("-$i month", strtotime(date('M-Y')))) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <label class="col-md-4 control-label" for="first_name">Salary Head Item </label>
                            <div class="col-md-1">:</div>
                            <div class="col-md-7">
                                <select id="salary_head_item" name="salary_head_item" class="form-control" >

                                    <?php
                                    foreach ($arr_headitems as $key => $value) {

                                        $selected = '';
                                        if ($arr_leaveupoload['0']['EmployeeVariableUpload']['salary_head_item_fkey'] == $value['SalaryHeadItems']['salary_head_item_pkey'])
                                            $selected = 'selected="selected"';
                                    ?>
                                        <option value="<?php echo $value['SalaryHeadItems']['salary_head_item_pkey']; ?>" <?php echo $selected; ?>>
                                            <?php echo $value['SalaryHeadItems']['item']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <label for="in_amt" class="col-md-4 control-label">Amount<span class="star">*</span></label>
                            <div class="col-md-1">:</div>
                            <div class="col-md-7">
                                <input type="text" required="required"     class="form-control" value="<?php echo isset($arr_leaveupoload['0']['EmployeeVariableUpload']['uploaded_amount']) ? $arr_leaveupoload['0']['EmployeeVariableUpload']['uploaded_amount'] : '0'; ?>" name="amount" id="amount" >
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <label for="remarks" class="col-md-4 control-label">Remark</label>
                            <div class="col-md-1">:</div>
                            <div class="col-md-7">
                                <textarea name="remark" class="form-control"  id="remark" ><?php echo isset($arr_leaveupoload['0']['EmployeeVariableUpload']['remarks']) ? $arr_leaveupoload['0']['EmployeeVariableUpload']['remarks'] : ''; ?></textarea>
                            </div>
                        </div>
                    </div> 
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="variable_upload_pkey" name="variable_upload_pkey" value="<?php echo isset($arr_leaveupoload['0']['EmployeeVariableUpload']['emp_variables_upload_pkey']) ? $arr_leaveupoload['0']['EmployeeVariableUpload']['emp_variables_upload_pkey'] : 0; ?>" >
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>

        </div>

    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {

        $(".js-example-basic-single").select2(
                {
                    //closeOnSelect:false,
                    placeholder: "All",
                    allowClear: true,
                    ajax: {
                        url: livesite + "Attendanceregister/jsons/",
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term, // search term
                                page: params.page
                            };
                        },
                        processResults: function (data, params) {
                            // parse the results into the format expected by Select2
                            // since we are using custom formatting functions we do not need to
                            // alter the remote JSON data, except to indicate that infinite
                            // scrolling can be used
                            params.page = params.page || 1;

                            return {
                                results: data.items,
                                pagination: {
                                    more: (params.page * 30) < data.total_count
                                }
                            };
                        }
                    },
                    escapeMarkup: function (markup) {
                        return markup;
                    }
                });

        //$("#pincode").inputmask("999");
        $('#attendanceuploadtable').parsley();
        var options = {
            success: function (responseText, statusText, xhr, $form) {
                var response = JSON.parse(responseText);
                if (response.success == 1) {
                    alert("Variable upload  Successfully");
                    closeModal('att_table');
                }
                else {
                    alert(response.msg);
                }
            }
        };

        // bind to the form's submit event 
        $('#attendanceuploadtable').submit(function () {
            $(this).ajaxSubmit(options);
            return false;
        });



    });
</script>



