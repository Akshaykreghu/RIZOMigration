<style>
    .white-modal {
        background-color: white;
        padding: 20px;
        border-radius: 5px;
        max-width: 200px;
        /* Adjust this value as needed */
        margin: 0 auto;
        /* Center the form horizontally */
        text-align: center;
        /* Center the contents inside the form */
        /* border: solid; */
    }

    #selectedFileNames,
    #successfulFiles,
    #unsuccessfulFiles {
        margin-left: 10px;
        /* Add some left margin for space */
    }

#alertsModalForm {
  z-index: 1100; 
}
    .file-name,
    .success-file,
    .error-file {
        /* background-color: #f2f2f2; */
        /* Add a background color for shading */
        padding: 5px;
        /* Add padding for spacing within each row */
        margin-bottom: 5px;
        /* Add margin to separate rows */
    }


</style>


<!-- <form class="form-horizontal white-modal" id="taxForm" action="<?php echo $this->webroot; ?>Tax/formSixteen" method="post"> -->
<form class="form-horizontal" style="margin-left: 20px;" method="post" id="taxForm" action="<?php echo $this->webroot; ?>Tax/formSixteen">
    <!-- <div class="modal-header" style="background-color: #00659f; color: white; display: flex; justify-content: space-between; align-items: center;">
        <h4 class="modal-title" style="text-align: center; flex: 1;">&nbsp;</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="$('#smallModalForm').modal('hide');">
            <span aria-hidden="true" style="color: white;">&times;</span>
        </button>
    </div> -->
    <div class="modal-body">
        <input id="emp_pkey" name="emp_pkey" type="hidden" value="<?php echo $emp; ?>">
        <input id="fin_year" name="fin_year" type="hidden" value="<?php echo $fieldName; ?>">

        <fieldset>

            <div class="form-group">
                <div class="col-md-12" style="text-align:center;">

                    <div class="input-group date">
                        <div tabindex="500" class="btn btn-primary btn-file"><i class="glyphicon glyphicon-folder-open" onclick="hideFileSections();"></i> &nbsp;Browse
                            <?php if ($user_group == 1) { ?>
                                <input type="file" class="form-control file" id="taxfile" name="taxfile[]" multiple accept=".pdf">
                            <?php } elseif ($user_group == 2) { ?>
                                <input type="file" class="form-control file" id="taxfile" name="taxfile[]" multiple accept=".pdf">
                            <?php } ?>
                        </div>
                    </div>

                </div>
            </div>


        </fieldset>

    </div>

    <!-- For showing file names -->
    <div id="selectedFileNames"></div>

    <!-- Section for displaying successful files -->
    <!-- <div id="successfulFiles" style="display:none; width:auto;">
        <h4>Succesfully uploaded:</h4>
        <div id="successfulFilesList" style="background-color:#f2f2f2;"></div>
    </div> -->

    <!-- Section for displaying unsuccessful files -->
    <!-- <div id="unsuccessfulFiles" style="display:none; width:auto;">
        <h4 id="unscucess_heading">Not uploaded (PAN not registered):</h4>
        <ul id="unsuccessfulFilesList" style="background-color:#f2f2f2;"></ul>
    </div> -->

    <div class="modal-footer text-align:center">

        <button type="submit" class="btn btn-primary" style="margin-right:25px;">Upload</button>
        <button id="closeButton" type="button" class="btn btn-danger" onclick="$('#smallModalForm').modal('hide');">Cancel</button>
    </div>
</form>


<script type="text/javascript">
    function hideFileSections() {
        // Hide the successful and unsuccessful files sections
        $("#successfulFiles").css('display', 'none');
        $("#successfulFilesHeading").css('display', 'none');
        $("#unsuccessfulFiles").css('display', 'none');
        $("#unsuccessfulFilesHeading").css('display', 'none');
    }


    $(document).ready(function() {
        //Close 
        $('#closeButton').click(function() {

        });

        function closediv() {

        }

        var options = {
            success: function(responseText, statusText, xhr, $form) {

                var response = $.parseJSON(responseText);
                // console.log('Response', response);
                // alert(response.message);
                if(typeof response.succesfull_files !== 'undefined' && response.succesfull_files !== null && response.unsuccesfull_files !== 'undefined' && response.unsuccesfull_files !== null){
                    var failed_message = '';
                    if(response.unsuccesfull_files.length > 0){
                          failed_message = "Form 16 imported failed PAN: " + response.unsuccesfull_files.join(', ');
                    }
                    alert( response.succesfull_files.length + " file(s) imported successfully."+ " \n " + failed_message);
                }
                if (response.status == 1) {

                    // Determine the notification type based on successful files count
                    var notificationType = response.succesfull_files.length > 0 ? 'success' : 'error';
                    var notificationMessage = response.succesfull_files.length > 0 ? response.message : "Document importing not succesfull";

                if(notificationType == 'success'){                    
                    $.notify(notificationMessage, {
                        type: notificationType,
                        allow_dismiss: true

                    });
                }
                    closediv();

                    // Populate the successful files section
                    var successfulFilesList = $("#successfulFilesList");
                    successfulFilesList.empty(); // Clear previous list


                    // Toggle the visibility of successfulFilesList and display data if available
                    $("#successfulFiles").toggle(response.succesfull_files.length > 0);

                    $.each(response.succesfull_files, function(index, file) {
                        var serialNumber = index + 1;
                        successfulFilesList.append('<li class="success-file"> ' + serialNumber + '. ' + file + '</li>');
                    });

                    // Populate the unsuccessful files section
                    var unsuccessfulFilesList = $("#unsuccessfulFilesList");
                    unsuccessfulFilesList.empty(); // Clear previous list

                    $("#unsuccessfulFiles").toggle(response.unsuccesfull_files.length > 0);

                    $.each(response.unsuccesfull_files, function(index, file) {
                        var serialNumber = index + 1;
                        unsuccessfulFilesList.append('<li class="error-file"> ' + serialNumber + '. ' + file + '</li>');
                    });

                } else {

                }
            }
        };

        function showTaxHeadDetailss(obj) {

            // Show the overlay

            var taxHeadPkey = obj;
            var empPkey = $('#empsetuptaxation #emp_pkey').val();
            var url = livesite + 'Employee/showtaxheaddetail/' + empPkey + '/' + taxHeadPkey;

            var container = $("#modalShowTaxHeadDetailForm #modalForm-content")
            container.load(url, function() {
                $("#modalShowTaxHeadDetailForm").modal('show');
            });
        }
        // bind to the form's submit event 
        $('#taxForm').submit(function(event) {
            // event.preventDefault(); // Prevent the default form submission behavior

            var files = $("#taxfile")[0].files; // Get the selected files
            var formData = new FormData(); // Create a new FormData object

            // Loop through the selected files and append them to the FormData object
            for (var i = 0; i < files.length; i++) {
                formData.append('taxfile[' + i + ']', files[i]);
            }

            var value = $("#taxfile").val();
            console.log('Value', value);
            if (value) {
                $(this).ajaxSubmit(options);
                $('#smallModalForm').modal('hide');
                $('#selectedFileNames').hide();

            } else {
                alert("Please Choose a file for Upload.");
            }
            // inside event callbacks 'this' is the DOM element so we first 
            // wrap it in a jQuery object and then invoke ajaxSubmit 


            return false;
        });


        $(".btn-file").on('click', function() {
            hideFileSections(); // Hide the sections when the browse button is clicked
        });

        //For showing file names
        $("#taxfile").on('change', function() {
            var fileNames = [];
            var files = $("#taxfile")[0].files;

            console.log('Files',files.length);

            // Hide the successful and unsuccessful files sections
            $("#successfulFiles").css('display', 'none');
            $("#successfulFilesHeading").css('display', 'none');
            $("#unsuccessfulFiles").css('display', 'none');
            $("#unsuccessfulFilesHeading").css('display', 'none');

            // $("#selectedFileNames").css('display', 'true');

            // Loop through the selected files and extract their names
            for (var i = 0; i < files.length; i++) {
                var fileNameWithNumber = (i + 1) + ". " + files[i].name; // Add serial number
                fileNames.push('<div class="file-name">' + fileNameWithNumber + '</div>'); // Wrap each row with styling
            }

            // Update the content of the selectedFileNames div
            $("#selectedFileNames").html(fileNames.join('')); // Display with styles
        });
    });
</script>