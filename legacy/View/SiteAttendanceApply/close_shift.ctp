<?php if (!empty($siteatt_data)) { ?>
    <div id="shift_close">
        <button class="btn bg-red btn-flat margin" onclick="close_shift();" type="button" value="" id="button_close"> Close Shift</button>
    </div>
<?php } ?>
<?php $user_group = $this->Session->read('user_group');
if ($user_group == 1) {
    if ($shift_open == 0) { ?>
        <div id="shift_open">
            <button class="btn bg-red btn-flat margin" onclick="open_shift();" type="button" value="" id="button_open"> Open Shift</button>
        </div>
        <!-- This is to edit closed shift -->
        <div id="shift_edit">
            <button class="btn bg-blue btn-flat margin" onclick="edit_shift();" type="button" value="" id="button_edit"> Edit Shift</button>
        </div>
<?php }
} ?>
<!-- Edit shift by arul on 13-03-23 -->
<script>
    function close_shift() {
        var site_fkey = $('#filterby_branch').val();
        var day_time_seq_fkey = $('#filterby_shift').val();
        var att_date = $('#filterby_date').val();
        $.ajax({
            url: livesite + 'SiteAttendanceApply/shift_closure/',
            method: 'POST',
            data: {
                site_fkey: site_fkey,
                day_time_seq_fkey: day_time_seq_fkey,
                att_date: att_date
            },
            success: function(resp) {
                var response = JSON.parse(resp);
                // alert(response.message);
                if (response.success === 0) {
                    $.notify(" Can't close shift. Please check out all check in punchings.!", {
                        type: 'danger',
                        allow_dismiss: false
                    });
                    return false;
                } else {
                    $.notify("Shift closed successfully.", {
                        type: 'success',
                        allow_dismiss: false
                    });
                    $('#shift_close').hide();
                    $('#filterby_shift').val('');
                    lod_desig();

                }

            }
        });
    }

    function open_shift() {
        if (confirm("All data entered will be removed.Do you want to open the closed shift? ")) {
            var site_fkey = $('#filterby_branch').val();
            var day_time_seq_fkey = $('#filterby_shift').val();
            var att_date = $('#filterby_date').val();
            $.ajax({
                url: livesite + 'SiteAttendanceApply/shift_open/',
                method: 'POST',
                data: {
                    site_fkey: site_fkey,
                    day_time_seq_fkey: day_time_seq_fkey,
                    att_date: att_date
                },
                success: function(resp) {
                    var response = JSON.parse(resp);
                    // alert(response.message);
                    //                        if(response.success === 0){
                    //                         $.notify(" Can't close shift. Please check out all check in punchings.!",{
                    //                         type: 'danger',
                    //                         allow_dismiss: false
                    //                         });
                    //                         return false;
                    //                        }else{
                    $.notify("Shift opened successfully.", {
                        type: 'success',
                        allow_dismiss: false
                    });
                    $('#shift_open').hide();
                    $('#filterby_shift').val('');
                    lod_desig();
                    //}

                }
            });
        }
    }


    // This is to edit closed shift by arul on 13-3-23
    function edit_shift() {
        if (confirm("Do you want to edit the closed shift? ")) {
            var site_fkey = $('#filterby_branch').val();
            var day_time_seq_fkey = $('#filterby_shift').val();
            var att_date = $('#filterby_date').val();
            $('#shift_edit').hide();
            $('#shift_open').hide();
            load_closed_sites();
            closure_shift();
        }
    }
</script>