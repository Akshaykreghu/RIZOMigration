<style>
    .form-horizontal .control-label {
        text-align: left;
    }

    .modal-body {
        padding: 2rem;
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

    .choices__inner {
        min-height: 30px !important;
        padding: 2px 6px !important;
        font-size: 12px;
    }

    .choices__list--dropdown .choices__item {
        padding: 2px 6px;
        font-size: 12px;
    }

    .choices__input {
        padding: 2px 4px;
        font-size: 12px;
    }

    .choices__list--multiple {
        max-height: 100px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        flex-wrap: nowrap;
        padding-right: 4px;
    }

    .choices__list--multiple .choices__item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 2px 8px;
        background-color: #005d7f;
        color: white;
        font-size: 12px;
        border-radius: 20px;
        box-sizing: border-box;
        gap: 8px;
        min-height: 28px;
    }

    .choices__list--multiple .choices__item .choices__button {
        all: unset;
        cursor: pointer;
        color: white;
        font-size: 16px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.25);
        margin-left: 8px;
        position: relative;
        text-indent: 100%;
        /* push out text */
        white-space: nowrap;
        overflow: hidden;
        line-height: 20px;
        /* vertically center for inline */
        text-align: center;
        /* horizontally center */
        display: inline-block;
        /* flex breaks text-indent */
    }

    .choices__list--multiple .choices__item .choices__button::before {
        content: "×";
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        text-indent: 0;
        /* make sure this does not get pushed */
    }

    .choices__list--multiple .choices__item .choices__button:hover {
        background-color: rgba(255, 255, 255, 0.4);
    }
</style>


<div class="modal-dialog  modal-lg">
    <div class="modal-content">
        <div class="modal-header" style="background: #00659f; color: white">
            <h4 class="modal-title">Bulk Allocation</h4>
        </div>
        <div class="modal-body">
            <form class="form-horizontal" id="bulkReviewForm">

                <!-- Category -->
                <div class="form-group row">
                    <label class="col-md-3 col-form-label" for="category">Category:<span class="star">*</span></label>
                    <div class="col-md-9">
                        <select name="category" id="Employee_category" class="form-control" required>
                            <option value="" disabled selected hidden>--Select Category--</option>
                            <?php foreach ($arr_category as $category) { ?>
                                <option value="<?php echo $category['category']['category_pkey']; ?>">
                                    <?php echo h($category['category']['category_name']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-group row align-items-center">
                    <label class="col-md-3 col-form-label" for="emp_fkey">Name of the Employee:<span class="star">*</span></label>
                    <div class="col-md-6">
                        <select name="emp_fkey[]" id="Employee_fkey" class="form-control" required multiple>
                            <!-- <option value="" disabled selected hidden>--Select Employee--</option> -->
                            <!-- employee options -->
                        </select>
                    </div>
                    <div class="col-md-3" style="text-align:right;">
                        <button type="button" id="selectAllBtn" class="btn btn-sm btn-primary mb-1">Select All</button>
                        <button type="button" id="deselectAllBtn" class="btn btn-sm btn-secondary">Deselect All</button>
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
                    <label class="col-md-3 col-form-label" for="reporting_officer">Reporting Officer:<span class="star">*</span></label>
                    <div class="col-md-9">
                        <select name="reporting_officer" id="reporting_officer" class="form-control" <?php echo $disabled; ?>>
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
                        <select name="reviewing_officer" id="reviewing_officer" class="form-control" <?php echo $disabled; ?>>
                            <option value="" disabled selected hidden>--Select Reviewing Officer--</option>
                            <?php foreach ($arr_employees as $employee) { ?>
                                <option value="<?php echo $employee['ei']['emp_pkey']; ?>">
                                    <?php echo h($employee['ei']['EmpName'] . ' - ' . $employee['ei']['employee_id']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="modal-footer">
                    <?php if ($edit != 0) { ?>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Submit</button>
                    <?php } ?>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        $('#Employee_fkey').prop('disabled', true); // Initially disable employee select

        // Warn if trying to interact before category selected
        $('#Employee_fkey').on('focus click', function(e) {
            if (!$('#Employee_category').val()) {
                e.preventDefault();
                $.notify('Please select a category first.', {
                    type: 'danger',
                    autoHideDelay: 3000,
                    z_index: 9999
                });
                $('#Employee_category').focus();
                return false;
            }
        });

        let empChoices;

        // Initialize Choices instances
        function initEmpChoices() {
            if (empChoices) empChoices.destroy();
            empChoices = new Choices('#Employee_fkey', {
                removeItemButton: true,
                searchEnabled: true,
                placeholderValue: 'Select Employee(s)...',
                searchPlaceholderValue: 'Type to search...',
                itemSelectText: '',
                classNames: {
                    item: 'choices__item',
                    button: 'choices__button',
                },
                // Custom item template
                itemTemplate: (item) => {
                    return `
            <div class="choices__item">
                ${item.label}
                <button type="button" class="choices__button" aria-label="×">
                    &times;
                </button>
            </div>
        `;
                },
            })

            // Optional: Remove tooltips after slight delay
            setTimeout(() => {
                document.querySelectorAll('.choices__button').forEach(btn => {
                    btn.setAttribute('aria-label', 'Remove item'); // or just ''
                });
            }, 100);
        }

        initEmpChoices(); // Initial call

        const empChoices1 = new Choices('#reporting_officer', {
            searchEnabled: true,
            itemSelectText: '',
        });

        const empChoices2 = new Choices('#reviewing_officer', {
            searchEnabled: true,
            itemSelectText: '',
        });

        $('#Employee_category').change(function() {
            let selectedCategory = $(this).val();
            if (selectedCategory) {
                $('#Employee_fkey').prop('disabled', false); // Enable select
                $.ajax({
                    type: 'POST',
                    url: '<?php echo $this->webroot; ?>SelfReview/getEmployeesByCategory',
                    data: {
                        category: selectedCategory
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        $('#Employee_fkey').html('<option value="">Loading...</option>');
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            let optionsHtml = '';
                            $.each(response.employees, function(index, emp) {
                                optionsHtml += `<option value="${emp.ei.emp_pkey}">${emp.ei.EmpName} - ${emp.ei.employee_id}</option>`;
                            });
                            $('#Employee_fkey').html(optionsHtml);
                            initEmpChoices();
                            $('#Employee_fkey').trigger('change'); // ✅ Trigger change so dependent fields update
                        } else {
                            $('#Employee_fkey').html('<option value="">No employees found</option>');
                            $('#Employee_fkey').trigger('change'); // Also trigger for empty case
                        }
                    },
                    error: function() {
                        // alert('Failed to load employees for the selected category.');
                        $.notify('Failed to load employees for the selected category.', {
                            type: 'danger',
                            autoHideDelay: 3000,
                            z_index: 9999
                        });
                    }
                });
            } else {
                $('#Employee_fkey').prop('disabled', true).html(''); // disable and clear if no category
            }
        });

        $('#selectAllBtn').on('click', function() {
            let allValues = [];
            $('#Employee_fkey option').each(function() {
                if ($(this).val()) {
                    allValues.push($(this).val());
                }
            });
            empChoices.setChoiceByValue(allValues);
            $('#Employee_fkey').trigger('change'); // ✅ Trigger change manually
        });

        $('#deselectAllBtn').on('click', function() {
            empChoices.removeActiveItems();
            $('#Employee_fkey').trigger('change'); // ✅ Trigger change manually
        });

        // Fetch fin year
        $('#Employee_fkey').change(function() {
            const emp_fkey_array = $(this).val(); // Array of selected employee keys

            if (emp_fkey_array && emp_fkey_array.length > 0) {
                $.ajax({
                    type: 'POST',
                    url: '<?php echo $this->webroot; ?>SelfReview/getFinYears',
                    data: {
                        'emp_fkey[]': emp_fkey_array
                    },
                    traditional: true,
                    dataType: 'json',
                    beforeSend: function() {
                        $('#submitBtn').prop('disabled', true).text('Loading...');
                    },
                    success: function(data) {
                        if (data.status === 'success') {
                            let finYearOptions = '<option value="" selected>--Select Financial Year--</option>';
                            if (data.fin_years && data.fin_years.length > 0) {
                                $.each(data.fin_years, function(index, fin_year) {
                                    finYearOptions += `<option value="${fin_year.fin_year}">${fin_year.year_range}</option>`;
                                });
                            }
                            $('#fin_year').html(finYearOptions);
                        } else {
                            $.notify('No financial year data found.', {
                                type: 'danger',
                                z_index: 9999,
                                delay: 4000,
                                placement: {
                                    from: "top",
                                    align: "center"
                                }
                            });
                            $('#fin_year').html('<option value="">--Select Financial Year--</option>');
                        }

                        $('#submitBtn').prop('disabled', false).text('Submit');
                    },
                    error: function() {
                        $.notify('Error fetching financial year.', {
                            type: 'danger',
                            z_index: 9999,
                            delay: 4000,
                            placement: {
                                from: "top",
                                align: "center"
                            }
                        });
                        $('#submitBtn').prop('disabled', false).text('Submit');
                    }
                });
            } else {
                $('#fin_year').html('<option value="">--Select Financial Year--</option>');
            }
        });


        // Disable all except category and employee on load
        disableDependentFields(true);
        // Watch for employee selection
        $('#Employee_fkey').on('change', function() {
            const hasEmployee = $(this).val() && $(this).val().length > 0;
            console.log('hasEmployee', hasEmployee);

            disableDependentFields(!hasEmployee);
        });

        function disableDependentFields(disable) {
            $('#fin_year, #submitBtn')
                .prop('disabled', disable);
        }

        $('#bulkReviewForm').on('submit', function(e) {
            e.preventDefault();

            var category = $('#Employee_category').val();
            const reportingOfficer = $('#reporting_officer').val();
            const reviewingOfficer = $('#reviewing_officer').val();

            // Manual validation for required fields handled by Choices.js
            if (!reportingOfficer || !reviewingOfficer) {
                $.notify('Both Reporting Officer and Reviewing Officer are required.', {
                    type: 'danger',
                    autoHideDelay: 3000,
                    z_index: 9999
                });
                return;
            }

            let actionUrl = '<?php echo $this->webroot; ?>SelfReview/createBulkSelfReview';
            console.log('category', category);
            console.log('category', typeof(category));

            if (category === '3') {
                actionUrl = '<?php echo $this->webroot; ?>HierarchyReview/createBulkHierarchyReview';
            }

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
                        $('#bulkReviewForm')[0].reset();
                        $('#myleaverequeststable').datagrid('reload');
                        $('.modal').modal('hide');
                    } else {
                        let errorMsg = response.message || 'Something went wrong.';

                        if (response.unsaved && response.unsaved.length > 0) {
                            // errorMsg += '<br><strong>Unsaved employees:</strong><br>' + response.unsaved.join('<br>');
                            errorMsg += '\n\nUnsaved employees:\n' + response.unsaved.join('\n');
                        }

                        // $.notify(errorMsg, { // For QA
                        //     type: 'error',
                        //     autoHideDelay: 5000, // longer so user can read
                        //     z_index: 9999,
                        //     allow_dismiss: true
                        // });
                        alert(errorMsg); // For v1
                        $('#myleaverequeststable').datagrid('reload');
                    }
                },
                error: function() {
                    // alert('An unexpected error occurred while submitting the form.');
                    $.notify('An unexpected error occurred while submitting the form.', {
                        type: 'danger',
                        autoHideDelay: 3000,
                        z_index: 9999
                    });
                }
            });
        });
    });
</script>