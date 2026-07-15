<div style="background-color: #00649d; color: white; padding: 10px; width:100%;">
    <h3 style="margin: 0px;text-align:center;">&nbsp;Update Attendance</h3>
</div>

<div class="optionselects" style="width:50%;">
    <div class="optionselectsinner" style="width: 300px;margin: 0px;">
        <div style="display:flex; flex-direction:row; justify-content:space-around;">
            <div class="dropdown1" style="display: flex; color: #000; border: none; font-weight: bold; flex-direction: column;margin-top:10px;">
                <span style="border: none;">First Half</span>
                <span style="border: none;">&nbsp;</span>
                <button id="<?php echo $emp_detail_timeattandance_pkey; ?>" onclick="updateStatuses(this)" class="btn btn-default" type="first" value="P" style="margin-bottom:5px;">P</button>

                <button id="<?php echo $emp_detail_timeattandance_pkey; ?>" onclick="updateStatuses(this)" class="btn btn-default" type="first" value="LOP" style="margin-bottom:5px;">LOP</button>

                <?php foreach ($arr_leave as $leave) : ?>
                    <button id="<?php echo $emp_detail_timeattandance_pkey; ?>" onclick="updateStatuses(this)" class="btn btn-default" type="first" value="<?php echo $leave['Head']; ?>" style="margin-bottom:5px;"><?php echo $leave['Head']; ?></button>

                <?php endforeach; ?>
            </div>
            <div class="dropdown1" style="display: flex; color: #000; border: none; font-weight: bold; flex-direction: column; margin-top:10px;">
                <span style="border: none;">Second Half</span>
                <span style="border: none;">&nbsp;</span>
                <button id="<?php echo $emp_detail_timeattandance_pkey; ?>" onclick="updateStatuses(this)" class="btn btn-default" type="second" value="P" style="margin-bottom:5px;">P</button>

                <button id="<?php echo $emp_detail_timeattandance_pkey; ?>" onclick="updateStatuses(this)" class="btn btn-default" type="second" value="LOP" style="margin-bottom:5px;">LOP</button>

                <?php foreach ($arr_leave as $leave) : ?>
                    <button id="<?php echo $emp_detail_timeattandance_pkey; ?>" onclick="updateStatuses(this)" class="btn btn-default" type="second" value="<?php echo $leave['Head']; ?>" style="margin-bottom:5px;"><?php echo $leave['Head']; ?></button>

                <?php endforeach; ?>
            </div>
            <div class="dropdown1" style="display: flex; color: #000; border: none; font-weight: bold; flex-direction: column;margin-top:10px;">
                <span style="border: none;">Full Day</span>
                <span style="border: none;">&nbsp;</span>
                <button id="<?php echo $emp_detail_timeattandance_pkey; ?>" onclick="updateStatuses(this)" class="btn btn-default" type="full" value="P/P" style="margin-bottom:5px;">P</button>

                <button id="<?php echo $emp_detail_timeattandance_pkey; ?>" onclick="updateStatuses(this)" class="btn btn-default" type="full" value="HO" style="margin-bottom:5px;">HO</button>

                <button id="<?php echo $emp_detail_timeattandance_pkey; ?>" onclick="updateStatuses(this)" class="btn btn-default" type="full" value="WO" style="margin-bottom:5px;">WO</button>

                <button id="<?php echo $emp_detail_timeattandance_pkey; ?>" onclick="updateStatuses(this)" class="btn btn-default" type="full" value="LOP" style="margin-bottom:5px;">LOP</button>

                <?php foreach ($arr_leave as $leave) : ?>
                    <button id="<?php echo $emp_detail_timeattandance_pkey; ?>" onclick="updateStatuses(this)" class="btn btn-default" type="full" value="<?php echo $leave['Head']; ?>" style="margin-bottom:5px;"><?php echo $leave['Head']; ?></button>

                <?php endforeach; ?>
            </div>
        </div>
<div style="text-align:center;">
        <button type="button" id="closeModalBtn" class="btn btn-danger" style="margin: 15px;" data-dismiss="modal">Close</button>
</div>   
 </div>
</div>



<script>
    $("#smallModalForm-content").css({
        width: "50%",
        marginLeft: "15%"
    });

    function updateStatuses(_this) {
        var newstatuses = $(_this).attr("value");
      // console.log('newstatuses', newstatuses);
        var statusType = $(_this).attr("type");
        console.log('statusType', statusType);
       // var currentStatuses = <?php echo json_encode($status); ?>;
       
 var currentStatuses = $('#<?php echo $emp_detail_timeattandance_pkey ?>_column').val();
 

        if (!['P', 'P/P', 'P/A', 'A/P', 'A', 'NA', 'WO', 'HO', '/WO'].includes(newstatuses)) {
            // your code here
           // console.log("valid blacndes", $('#leave_' + newstatuses).attr('value'));
            if (statusType == 'full') {
               // console.log("full", parseInt($('#leave_' + newstatuses).attr('value')));
                if (parseFloat($('#leave_' + newstatuses).attr('value')) < 1) {
                    alert("No Leave Balance Available");
                    return false;
                }

            } else {
                if (parseFloat($('#leave_' + newstatuses).attr('value')) < 0.5) {
                    alert("No Leave Balance Available");
                    return false;
                }

            }
        }

        let newStatus = currentStatuses;
        
        if (statusType == 'first') {
// console.log(currentStatuses);
            if (typeof currentStatuses !== 'undefined' && currentStatuses !== null) {
                var secondhalf = currentStatuses.split('/')[1];
            } else {
                var secondhalf = '';
            }
            if (secondhalf) {
                secondhalf = secondhalf.trim();

            } else {
                secondhalf = 'A';
            }
            newStatus = newstatuses + '/' + (secondhalf ? secondhalf : currentStatuses);

        }

        if (statusType == 'second') {
            if (typeof currentStatuses !== 'undefined' && currentStatuses !== null) {
                var fiirsthalf = currentStatuses.split('/')[0];
            } else {
                var fiirsthalf = 'A';
            }
            //fiirsthalf = currentStatuses.split('/')[0];
            fiirsthalf = fiirsthalf.trim();

            newStatus = (fiirsthalf ? fiirsthalf : currentStatuses) + '/' + newstatuses;

        }

        if (statusType == 'full') {
            newStatus = newstatuses;

        }

        $.ajax({
            type: "POST",
            url: livesite + "EditAttendance/chnagestatus",
            data: {
                device_attandance_seq: $(_this).attr('id'),
                status: newStatus,
                statusType: statusType,
                newstatuses: newstatuses,
                currentStatuses: currentStatuses
            },
            dataType: 'json',
            success: function(resp) {
                //var resp = $.parseJSON(resp);
                //$('#editpunches').datagrid('deleteRow', getRowIndex(target));
                if (resp.success == true) {
                    // $.messager.show('Success', "Attandence removed successfully", 'info');
              
                    //$('#<?php echo $emp_detail_timeattandance_pkey ?>_column').text(newstatuses);
                     $('#<?php echo $emp_detail_timeattandance_pkey ?>_column').text(resp.status);
$('#<?php echo $emp_detail_timeattandance_pkey ?>_column').val(resp.status);
                   // console.log('#<?php echo $emp_detail_timeattandance_pkey ?>_column');
                    
//filterRegister1();
                    closeSmallModalForm();

                    //  $('#modalForm').modal('toggle');
                } else {
                    $.messager.alert('Failed', "Error occured while removing record", 'info');
                    $('#modalForm').modal('toggle');
                }
            }
        });

       // console.log((newStatus));
        $(_this).parents(".optionselects").parent().find('strong').html(newStatus);
        // $(_this).parents(".optionselects").css("display", "none");
    }

    $(document).ready(function() {
        // Attach click event handler to the close button
        $('#closeModalBtn').click(function() {
           // $('#modalForm').modal('toggle'); // Toggle the modal
        });
    });
</script>