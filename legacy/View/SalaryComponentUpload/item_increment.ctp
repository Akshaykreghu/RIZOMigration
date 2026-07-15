<!-- Styles -->
<style>
    /* Modal Styles */
    /* .modal-dialog {
    width: 40%;
    margin: 50px auto;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    padding: 20px;
  }

  .modal-content {
    width: 100%;
  } */

    .modal-header {
        text-align: center;
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 20px;
        color: #333;
    }

    .form-row {
        margin-bottom: 15px;
    }

    .form-row label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        font-size: 14px;
        color: #555;
    }

    .form-row input,
    .form-row select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
        color: #333;
    }

    .form-row input[readonly] {
        background-color: #f9f9f9;
    }

    .form-actions {
        text-align: center;
        margin-top: 20px;
    }

    .form-actions .btn {
        padding: 10px 20px;
        border-radius: 5px;
        font-size: 14px;
        cursor: pointer;
        margin: 0 10px;
    }

    .btn-close {
        background-color: #ccc;
        border: none;
        color: #fff;
    }

    .btn-save {
        background-color: #007bff;
        border: none;
        color: #fff;
    }

    .btn:hover {
        opacity: 0.8;
    }
</style>

<!-- Modal structure -->
<!-- Modal content-->
<div class="modal-content">
    <div class="modal-header" style="background: #00659f;color: white; text-align:left;">
        <h4 class="modal-title">Item Increment</h4>
    </div>
    <div class="modal-body">
        <!-- Form starts -->
        <form id="itemIncrementForm" action="<?php echo $this->webroot; ?>SalaryComponentUpload/saveItemIncrement" method="POST">

            <!-- Employee Selection -->
            <div class="form-row">
                <label for="itemSelect">Choose Item: <span style="color:red">*</span></label>
                <select id="itemSelect" name="component" required>
                    <option value="">-- Select --</option>
                    <!-- <option value="1">Basic</option>
                    <option value="11">VDA</option> -->
                    <?
                    foreach ($arr_salary_head_items as $item) {
                        $key = isset($item['salary_head_items']['salary_head_item_pkey']) ? $item['salary_head_items']['salary_head_item_pkey'] : '';
                        $value = isset($item['salary_head_items']['item']) ? TRIM($item['salary_head_items']['item']) : '';
                    ?>
                        <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                    <?php }
                    ?>
                </select>
            </div>

            <!-- Hike -->
            <div class="form-row">
                <label for="percentageHike">Percentage : <span style="color:red">*</span></label>
                <input type="number" id="percentageHike" name="hike" placeholder="Enter percentage" step="0.1" required>
            </div>

            <!-- Date of Effect -->
            <div class="form-row">
                <label for="effectFrom">With Effect From: <span style="color:red">*</span></label>
                <input type="date" id="effectFrom" name="with_effect_from" required>
            </div>

            <!-- Next Increment Date -->
            <div class="form-row">
                <label for="nextIncrementDate">Next Increment Date: </label>
                <input type="date" id="nextIncrementDate" name="next_increment_date">
            </div>


            <!-- Remarks -->
            <div class="form-row">
                <label for="effectFrom">Remarks:</label>
                <input type="text" id="remarks" name="remarks" required>
            </div>

            <!-- Upload File -->
            <div class="form-row">
                <label for="excelFile">Upload File:</label>
                <input type="file" id="excelFile" name="excel_file" accept=".xls,.xlsx">
            </div>


            <!-- Action Buttons -->
            <div class="form-actions" style="text-align:right;">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-save">Save</button>
            </div>

        </form>
        <!-- End Form -->

    </div>

</div>

<!-- Javascript -->
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
<!-- Bootstrap JS (required for modal) -->
<!-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.6.2/js/bootstrap.min.js"></script> -->

<script>
    $(document).ready(function() {
        $('#itemIncrementForm').on('submit', function(e) {
            e.preventDefault();

            var form = $('#itemIncrementForm')[0];
            var formData = new FormData(form);

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false, // important for file upload
                contentType: false, // important for file upload
                success: function(response) {
                    console.log('Response', response.success);

                    if (response.success) {
                        alert("Data saved successfully!");
                        $('#modalForm').modal('hide'); // close the modal

                        // ✅ Load DataGrid with filters
                        var branch = $('#importcomponents #filterby_branches').val();
                        var structure = $('#importcomponents #filterby_structure').val();
                        var emp = $('#importcomponents #emp_fkey1').val();

                        $('#componenttable').datagrid('load', {
                            branch: branch,
                            structure: structure,
                            emp: emp
                        });

                    } else {
                        alert(response.message || "An error occurred while saving.");

                    }
                },
                error: function(xhr, status, error) {
                    console.error(error);
                    alert("Something went wrong. Please try again.");
                }
            });
        });
    });
</script>