<style>
    .form-horizontal .control-label {
        text-align: left;
    }

    .modal-body {
        padding: 2rem;
        /* Increase padding inside the modal body */
    }

    .modal-body .form-group {
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }

    .modal-body label,
    .modal-body input,
    .modal-body select {
        padding-left: 0.3rem;
        padding-right: 0.3rem;
    }

    /* Reduce height of selected item */
    .choices__inner {
        min-height: 30px !important;
        /* Adjust to your desired height */
        padding: 2px 6px !important;
        font-size: 12px;
        /* Optional: smaller font helps reduce height */
    }

    /* Reduce spacing between choices */
    .choices__list--dropdown .choices__item {
        padding: 2px 6px;
        font-size: 12px;
    }

    /* Optional: Adjust input height in multi-select */
    .choices__input {
        padding: 2px 4px;
        font-size: 12px;
    }
</style>


<div class="modal-dialog  modal-lg">
    <div class="modal-content">
        <div class="modal-header" style="background: #00659f; color: white">
            <h4 class="modal-title">Annual Performance Assessment</h4>
        </div>
        <div class="modal-body">
            <form class="form-horizontal" id="selfReviewForm">


                <div class="form-group row">
                    <label class="col-md-3 col-form-label" for="emp_fkey">Name of the Employee:<span class="star">*</span></label>
                    <div class="col-md-9">
                        <select name="emp_fkey" id="Employee_fkey" class="form-control" required>
                            <option value="" disabled selected hidden>--Select Employee--</option>
                            <?php foreach ($arr_employees as $employee) { ?>
                                <option value="<?php echo $employee['ei']['emp_pkey']; ?>">
                                    <?php echo h($employee['ei']['EmpName'] . ' - ' . $employee['ei']['employee_id']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 col-form-label" for="designation">Designation/Post held:<span class="star">*</span></label>
                    <div class="col-md-9">
                        <input type="text" name="designation" id="designation" class="form-control" readonly>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 col-form-label" for="dob">Date of Birth:<span class="star">*</span></label>
                    <div class="col-md-9">
                        <input type="date" name="dob" id="dob" class="form-control" readonly>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 col-form-label" for="doj">Date of Joining:<span class="star">*</span></label>
                    <div class="col-md-9">
                        <input type="date" name="doj" id="doj" class="form-control" readonly>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 col-form-label" for="grade_entry_date">Date of entry into the present grade:<span class="star">*</span></label>
                    <div class="col-md-9">
                        <input type="date" name="grade_entry_date" id="grade_entry_date" class="form-control" readonly>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 col-form-label" for="employment_type">Whether Permanent/Fixed Term Employment:<span class="star">*</span></label>
                    <div class="col-md-9">
                        <input type="text" name="employment_type" id="employment_type" class="form-control" readonly>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 col-form-label" for="department">Department/Section in which served during the year:<span class="star">*</span></label>
                    <div class="col-md-9">
                        <input type="text" name="department" id="department" class="form-control" readonly>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 col-form-label" for="fin_year">Financial Year:<span class="star">*</span></label>
                    <div class="col-md-9">
                        <select name="fin_year" id="fin_year" class="form-control" required <?php echo $disabled; ?>>
                            <option value="" disabled selected hidden>--Select Financial Year--</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 col-form-label" for="absence_period">Period of absence from duty (without pay) during the year:<span class="star">*</span></label>
                    <div class="col-md-9">
                        <input type="text" name="absence_period" id="absence_period" class="form-control" readonly>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 col-form-label" for="reporting_officer">Reporting Officer:<span class="star">*</span></label>
                    <div class="col-md-9">
                        <select name="reporting_officer" id="reporting_officer" class="form-control" required <?php echo $disabled; ?>>
                            <option value="" disabled selected hidden>--Select Reporting Officer--</option>
                            <?php foreach ($arr_employees as $employee) { ?>
                                <option value="<?php echo $employee['ei']['emp_pkey']; ?>">
                                    <?php echo h($employee['ei']['EmpName'] . ' - ' . $employee['ei']['employee_id']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 col-form-label" for="reviewing_officer">Reviewing Officer:<span class="star">*</span></label>
                    <div class="col-md-9">
                        <select name="reviewing_officer" id="reviewing_officer" class="form-control" required <?php echo $disabled; ?>>
                            <option value="" disabled selected hidden>--Select Reviewing Officer--</option>
                            <?php foreach ($arr_employees as $employee) { ?>
                                <option value="<?php echo $employee['ei']['emp_pkey']; ?>">
                                    <?php echo h($employee['ei']['EmpName'] . ' - ' . $employee['ei']['employee_id']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <input type="hidden" id="employee_category" name="employee_category" value="">


                <!-- Footer Buttons -->
                <div class="modal-footer">
                    <?php if ($edit != 0) { ?>
                        <!-- <button type="submit" class="btn btn-primary" id="saveBtn">Save</button> -->
                        <button type="submit" class="btn btn-primary" id="submitBtn">Submit</button>
                    <?php } ?>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
    function fetchAbsencePeriod(emp_fkey, fin_year) {
        if (emp_fkey && fin_year) {
            $.ajax({
                type: 'POST',
                url: '<?php echo $this->webroot; ?>SelfReview/getAbsencePeriod',
                data: {
                    emp_fkey: emp_fkey,
                    fin_year: fin_year
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#absence_period').val(response.absence_period || 0);
                    } else {
                        $('#absence_period').val('');
                        console.warn('Absence data not found');
                    }
                },
                error: function() {
                    alert('Error fetching absence period.');
                }
            });
        }
    }

    $(document).ready(function() {
        if ($('#reporting_officer').hasClass("select2-hidden-accessible")) {
            $('#reporting_officer').select2('destroy');
        }
        if ($('#reviewing_officer').hasClass("select2-hidden-accessible")) {
            $('#reviewing_officer').select2('destroy');
        }
        $('#largeModalForm').modal({
            backdrop: 'static', // Prevents closing the modal by clicking outside
            keyboard: false // Disables closing the modal with the keyboard (ESC key)
        });

        $('.modal').on('click', function(e) {
            // Prevent closing modal if the click is not on a button and is outside .modal-content
            if (!$(e.target).closest('.modal-content').length) {
                e.stopPropagation(); // Prevent closing the modal if clicked outside .modal-content
            }
        });

        const empChoices = new Choices('#Employee_fkey', {
            searchEnabled: true,
            itemSelectText: '',
        });
        const empChoices1 = new Choices('#reporting_officer', {
            searchEnabled: true,
            itemSelectText: '',
        });
        const empChoices2 = new Choices('#reviewing_officer', {
            searchEnabled: true,
            itemSelectText: '',
        });


        $('#Employee_fkey').change(function() {
            var emp_fkey = $(this).val();

            if (emp_fkey) {
                $.ajax({
                    type: 'POST',
                    url: '<?php echo $this->webroot; ?>SelfReview/getEmployeeDetails',
                    data: {
                        emp_fkey: emp_fkey
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        // Disable submit button while request is processing
                        $('#submitBtn').prop('disabled', true).text('Loading...');
                    },
                    success: function(data) {
                        if (data.status === 'success') {
                            $('#designation').val(data.employee.emp_info_designation || '');
                            $('#dob').val(data.employee.date_of_birth || '');
                            $('#doj').val(data.employee.doj || '');
                            $('#grade_entry_date').val(data.employee.grade_entry_date || '');
                            $('#employment_type').val(data.employee.emp_type || '');
                            $('#department').val(data.employee.emp_info_department || '');
                            $('#employee_category').val(data.employee.category || '');

                            // Populate Financial Year
                            let finYearOptions = '<option value="" selected>--Select Financial Year--</option>';
                            if (data.fin_years && data.fin_years.length > 0) {
                                $.each(data.fin_years, function(index, fin_year) {
                                    finYearOptions += `<option value="${fin_year.fin_year}">${fin_year.year_range}</option>`;
                                });
                            }
                            $('#fin_year').html(finYearOptions);

                            // Populate Reporting Officer
                            let reportingOptions = '<option value="" disabled selected hidden>--Select Reporting Officer--</option>';
                            if (data.reporting_officers && data.reporting_officers.length > 0) {
                                $.each(data.reporting_officers, function(index, officer) {
                                    let selected = index === 0 ? 'selected' : '';
                                    reportingOptions += `<option value="${officer.emp_pkey}" ${selected}>${officer.EmpName} - ${officer.employee_id}</option>`;
                                });
                            }
                            // $('#reporting_officer').html(reportingOptions).select2({ width: '100%', placeholder: "--Select Reporting Officer--", allowClear: true, minimumResultsForSearch: Infinity   });;

                            // Populate Reviewing Officer
                            let reviewingOptions = '<option value="" disabled selected hidden>--Select Reviewing Officer--</option>';
                            if (data.reviewing_officers && data.reviewing_officers.length > 0) {
                                $.each(data.reviewing_officers, function(index, officer) {
                                    let selected = index === 0 ? 'selected' : '';
                                    reviewingOptions += `<option value="${officer.emp_pkey}" ${selected}>${officer.EmpName} - ${officer.employee_id}</option>`;
                                });
                            }
                            // $('#reviewing_officer').html(reviewingOptions).select2({ width: '100%', placeholder: "--Select Reporting Officer--", allowClear: true,  minimumResultsForSearch: Infinity  });

                            // Fetch lop days
                            var fin_year = $('#fin_year').val();
                            fetchAbsencePeriod(emp_fkey, fin_year);

                            // ✅ Enable submit button after success
                            $('#submitBtn').prop('disabled', false).text('Submit');
                        } else {
                            alert('No employee data found.');
                        }
                    },
                    error: function() {
                        alert('Error fetching employee data.');
                    }
                });
            }
        });


        // Trigger fetch on financial year change
        $('#fin_year').change(function() {
            var emp_fkey = $('#Employee_fkey').val();
            var fin_year = $(this).val();
            fetchAbsencePeriod(emp_fkey, fin_year);
        });


        $('#selfReviewForm').on('submit', function(e) {
            e.preventDefault(); // prevent normal form submission

            var category = $('#employee_category').val();
            console.log('category', category);

            // Decide URL based on category
            let actionUrl = '<?php echo $this->webroot; ?>SelfReview/createSelfReview';
            if (category === 'hierarchy') {
                actionUrl = '<?php echo $this->webroot; ?>HierarchyReview/createHierarchyReview';
            }
            // actionUrl = '<?php echo $this->webroot; ?>HierarchyReview/createHierarchyReview';
            // actionUrl = '<?php echo $this->webroot; ?>SelfReview/createSelfReview';

            $.ajax({
                type: 'POST',
                url: actionUrl,
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $.notify(response.message, {
                            type: 'success',
                            autoHideDelay: 3000,
                            z_index: 9999
                        });
                        // You can also reset the form or close the modal:
                        $('#selfReviewForm')[0].reset();
                        $('#myleaverequeststable').datagrid('reload');
                        $('.modal').modal('hide');
                        // $('#Employee_fkey').val(null).trigger('change');
                        // $('#yourModalId').modal('hide'); // if using modal
                    } else {
                        $.notify(response.message || 'Something went wrong.', {
                            type: 'error',
                            autoHideDelay: 3000,
                            z_index: 9999
                        });
                    }
                },
                error: function() {
                    alert('An unexpected error occurred while submitting the form.');
                }
            });
        });

    });
</script>