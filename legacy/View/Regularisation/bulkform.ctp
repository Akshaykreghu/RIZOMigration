<style>
    .form-horizontal .control-label {
        text-align: left;
    }
</style>
<!-- Structure re-edited by *** ARUL P DAS *** on 7/12/2020 -->
<div class="modal-content">

    <div class="modal-header" style="background: #00659f;color: white">
        <h4 class="modal-title">Attendance</h4>
    </div>
    <div class="modal-body">
        <!-- Attendance Adding Form -->
        <form class="form-horizontal" id="editpunchform" method="post" action="<?php echo $this->webroot; ?>Regularisation/bulkupdate_self">
            <?php //echo ($self_login && $hierarchy_person) ? 'bulkupdate_self' : 'bulksavenew' 
            ?>

            <input type="hidden" name="hierarchy_head" id="hierarchy_head" value="<?php echo $hierarchy_person; ?>" />
            <input type="hidden" name="empid" id="empid" value="<?php echo $empid; ?>" />
            <input type="hidden" name="site_t_fkey" id="site_t_fkey" value="<?php echo $site_t_fkey; ?>" />
            <input type="hidden" name="att_date" id="att_date" value="<?php echo $att_date; ?>" />

            <?php
            if (!$hierarchy_person) {
            ?>
                <span style="color:red">You have no hierarchy person. Please contact the administrator to assign one.</span>
            <?php
            } else {
            ?>
                <div class="form-group col-md-12">
                    <label class="col-md-4 control-label">Attendance Dates</label>
                    <div class="col-md-1">:</div>
                    <div class="col-md-7">
                        <b>
                            <?php
                            $date_arr = [];
                            foreach (explode(",", $att_date) as $date) {
                                array_push($date_arr, date('d-m-Y', strtotime($date)));
                            }
                            echo implode(",", $date_arr);
                            ?>
                        </b>
                    </div>
                </div>

                <div class="form-group col-md-12">
                    <label class="col-md-4 control-label" for="LOGTIME">Time <span class="star">*</span></label>
                    <div class="col-md-1">:</div>
                    <div class="col-md-7">
                        <input id="LOGTIME" name="LOGTIME" placeholder="HH:MM:SS" value="" type="text" class="form-control input-md" required="" readonly>
                    </div>
                </div>

                <!-- Text input-->
                <div class="form-group col-md-12">
                    <label class="col-md-4 control-label" for="C1">Direction <span class="star">*</span></label>
                    <div class="col-md-1">:</div>
                    <div class="col-md-7">
                        <select name="C1" class="form-control">
                            <option value="in">In</option>
                            <option value="out">Out</option>
                        </select>
                    </div>
                </div>

                <!-- Text input-->
                <div class="form-group col-md-12">
                    <label class="col-md-4 control-label" for="C3">Remarks <span class="star">*</span></label>
                    <div class="col-md-1">:</div>
                    <div class="col-md-7">
                        <input name="C3" value="" type="text" placeholder="Remarks" class="form-control input-md" required="">
                        <span> You must enter a valid remarks. </span>
                    </div>
                </div>
            <?php
            }
            ?>

            <div style="text-align: right;padding:15px;">
                <button type="button" class="btn btn-danger" data-dismiss="modal" onclick="clearForm();">Close</button>
                <button type="submit" class="btn btn-primary" <?= (!$hierarchy_person) ? 'disabled' : '' ?>>Save</button>
            </div>
        </form>

    </div>



</div> <!-- Closing of Modal Content -->
<script type="text/javascript">
    $(document).ready(function() {


        // $('.tabset-regularisation').pwstabs({
        //     effect: 'scale', // You can change effects of your tabs container: scale / slideleft / slideright / slidetop / slidedown / none
        //     defaultTab: 1, // The tab we want to be opened by default
        //     containerWidth: '100%', // Set custom container width if not set then 100% is used
        //     tabsPosition: 'horizontal', // Tabs position: horizontal / vertical
        //     horizontalPosition: 'top', // Tabs horizontal position: top / bottom
        //     verticalPosition: 'left', // Tabs vertical position: left / right
        //     responsive: false, // Make tabs container responsive: true / false - boolean
        //     theme: '',
        //     rtl: false // Right to left support: true/ false
        // });

        // myInterval = setInterval(() => { // This is to click first tab when load the modal. by Arul on 13-09-22
        //     console.log("clicked" + $("#modalForm").css('display'));
        //     if ($("#modalForm").css('display') == "block") {
        //         $('[data-tab-id="tab1"]').trigger('click');
        //         clearInterval(myInterval); // after clicked, the loop exits here.
        //     }
        // }, 100);

        $('#LOGTIME').timepicker({
            format: 'hh:mm:ss',
            showMeridian: false,
            showSeconds: true,
            autoclose: true
        });

        $('#editpunchform').parsley();
        var options = {
            success: function(responseText, statusText, xhr, $form) {
                var response = JSON.parse(responseText);
                if (response.success == false) {
                    alert(response.msg);
                    clearForm();
                    // closeSmallModalForm();
                    $('#modalForm').modal('hide');
                    refresheditpunchgrid();
                } else {
                    console.log(response);
                    clearForm();
                    $('#modalForm').modal('hide');
                    refresheditpunchgrid();
                    $.notify("New Attandence Submitted Successfully", {
                        type: 'success',
                        allow_dismiss: false
                    });
                }
            }
        };

        // bind to the form's submit event 
        $('#editpunchform').submit(function() {
            $(this).ajaxSubmit(options);
            return false;
        });
    });

    function clearForm() {
        $('#empid').val("")
        $('#editpunchform').form('clear');
    }
</script>