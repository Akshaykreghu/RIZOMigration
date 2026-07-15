<script>
    var buttonClicked = ''; // Initialize a flag for the clicked button

    // Attach click event handler for "Approve" button
    $('#btn-approve').on('click', function () {
        // Show a confirmation alert
        if (confirm("Are you sure you want to approve this record?")) {
            buttonClicked = 'approve';
            $('#family').submit(); // Submit the form after confirmation
        }
    });

    // Attach click event handler for "Reject" button
    $('#btn-reject').on('click', function () {
        buttonClicked = 'reject';
    });

    var options = {
        success: function (resp) {
            console.log('Response', resp);
            $('#largeModalForm').modal('hide');
            $('#att_table').datagrid('reload');
            var buttonClickedId = $('button:approve').attr('id'); // Get the ID of the approve button
            var resp = JSON.parse(resp);
            var message = resp.message;

            if (resp.success == true) {
                var respType = 'success';
            } else {
                var respType = 'danger';
            }
            $.notify(message, {
                type: respType,
                allow_dismiss: false
            });
        }
    };

    $('#family').on('submit', function (event) {
        if (buttonClicked === 'approve') {
            // Only proceed if the "Approve" button was clicked
            event.preventDefault();
            $('#family').ajaxSubmit(options);
        }
    });
</script>
