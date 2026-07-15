<form class="form-horizontal" method="post" id="confirmation" action="<?php echo $this->webroot; ?>Dashboard/convertimage">
    <input id="emp_pkey" name="emp_pkey" type="hidden" value="<?php echo $emp_pkey; ?>">
    <input id="event" name="event" type="hidden" value="<?php echo $event; ?>">

    <div class="modal-header" style="background-color: #00659f; color: white;">
        <h4 class="modal-title" id="exampleModalLabel">Birthday / Anniversary Wishes</h4>
    </div>

    <div class="modal-body text-left">
        <p style="font-size:14px;"></p>Would you like to send a greetings email to <?php echo $emp_name ?>?</p>
    </div>

    <div class="form-group" style="margin-top:10px;">
        <div class="col-md-8">
            <label style="text-align:left;" class="col-md-4 control-label" for="remarks">Remarks <span style="color: red;">*</span></label>
            <div class="col-md-1">:</div>
            <div class="col-md-7">
                <textarea required="required" name="remarks" class="form-control strict-field" pattern="^\d+(st|nd|rd|th)$" id="remarks" maxlength="500" style="width:100%;"></textarea>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="submitBtn">Send</button>
        <button type="button" class="btn btn-danger" onclick="$('#modalForm').modal('hide');">Cancel</button>
    </div>
</form>

<script>
    $(document).ready(function() {
        // Handle form submission
        var eventValue = <?php echo json_encode($event); ?>;
        $("#submitBtn").click(function(event) {
            event.preventDefault(); // Prevent the form from submitting

            // Check if the "Remarks" field is not empty
            if ($("#remarks").val() !== undefined) {
                if ($("#remarks").val().trim() === "") {
                    alert("Please provide remarks."); // You can customize the alert or use a more sophisticated validation
                    return;
                }
            }

            // Submit the form using AJAX if needed
            $.ajax({
                type: "POST",
                url: livesite + "dashboard/convertimage",
                data: $("#confirmation").serialize(),
                success: function(response, textStatus, xhr) {
    if (xhr.status === 303) {
        window.location = xhr.getResponseHeader("Location");
//        $('#modalForm').modal('hide');
//        $('#load_data').load(livesite + 'Dashboard/load_birthdays');
    } else {
        // Handle normal response
        console.log(response);
        $.notify(response, {
            type: "success",
            allow_dismiss: false
        });
        $('#modalForm').modal('hide');
        $('#load_data').load(livesite + 'Dashboard/load_birthdays');
    }
},
                error: function(error) {
                    console.error("Error:", error);
                    $.notify("Error:Message not sent", {
                        type: "danger",
                        allow_dismiss: false
                    });
                }
            });
        });
    });
</script>