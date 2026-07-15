<style>
    /* Define styles for the odd rows */
    .striped-table .table-row:nth-child(odd) {
        background-color: #f2f2f2;
        /* Light shade for odd rows */
    }

    /* Define styles for the even rows */
    .striped-table .table-row:nth-child(even) {
        background-color: #ffffff;
        /* White background for even rows */
    }

    /* Adjust the position of the small modal to the right */
    .modal-dialog.custom-dialog {
        position: absolute;
        left: 25.9%;
    }
</style>

<div class="modal-header" style="background-color: #00659f; color: white; display: flex; justify-content: space-between; align-items: center;">
    <h4 class="modal-title" id="exampleModalLabel" style="text-align: center; flex: 1;">Upload Form 16</h4>
    <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="$('#modalForm').modal('hide');">
        <span aria-hidden="true" style="color: white;">&times;</span>
    </button> -->
</div>



<div class="modal-body ">
    <table style="margin: 0 auto; width:100%;" class="striped-table">
        <tr class="table-row">
            <!-- <td style="text-align:center; padding-left:4px">Year&nbsp;&nbsp;&nbsp;</td> -->
            <td style="text-align:center; padding-right: 7px;"><?php echo $currentYear . " - " . $nextYear; ?></td>
            <?php $fieldName = $currentYear . "-" . $nextYear; ?>
            <td style="text-align: center;">
                <div class="btn" onclick="addnewtaxdocument('<?php echo $fieldName; ?>',<?php echo $emp_pkey; ?>);">
                    <li id="my_button1" class="fa fa-upload pull-right" style="font-size:20px;color:#3c8dbc;"></li>
                </div>
            </td>
        </tr>

        <tr class="table-row">
            <!-- <td></td> -->
            <td style="text-align:center; padding-right: 7px;"><?php echo ($currentYear - 1) . " - " . $currentYear; ?></td>
            <?php $fieldName = ($currentYear - 1) . "-" . $currentYear; ?>
            <td style="text-align: center;">
                <div class="btn" onclick="addnewtaxdocument('<?php echo $fieldName; ?>',<?php echo $emp_pkey; ?>);">
                    <li id="my_button2" class="fa fa-upload pull-right" style="font-size:20px;color:#3c8dbc;"></li>
                </div>
            </td>
        </tr>
    </table>
</div>
<div class="modal-footer text-align:center">
    <button id="closeButton" type="button" class="btn btn-danger" onclick="$('#modalForm').modal('hide');">Close</button>
    <!-- <button type="submit" class="btn btn-primary" style="margin-right:25px;">Upload</button> -->
</div>
<script>
    function addnewtaxdocument(fieldName, empPkey) {
        console.log(fieldName);
        var divs = $('.load').children().removeClass('box box-body custom_css').css('border-style', 'none');
        divs.html('');

        var div = $('#loaded' + fieldName.replace(/ /g, "_")).addClass('box box-body custom_css').css('border-style', 'outset');

        // Rest of your code to load the modal content
        // Assuming 'showLargeModalForm' function is available
        showSmallModalForm(livesite + 'Tax/addnewtaxdocument_modal/' + fieldName + '/' + empPkey);

        // Add the custom-dialog class to the small modal
        $('#smallModalForm .modal-dialog').addClass('custom-dialog');
    }
</script>