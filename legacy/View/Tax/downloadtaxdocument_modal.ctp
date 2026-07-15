<style>
    .modal {

        position: fixed;
        /* Stay in place */

        left: 0;
        top: 0;
        width: 100%;
        /* Full width */
        height: 100%;
        /* Full height */
        overflow: auto;
        /* Enable scroll if needed */
        background-color: rgba(0, 0, 0, 0.4);
        /* Black background with opacity */
    }

    .modal-content {
        background-color: white;
        padding: 20px;
        border-radius: 5px;
        max-width: 600px;
        /* Adjust this value as needed */
        margin: 0 auto;
        height: 100%;
        /* Full height */
        position: relative;
    }

    .close {
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 20px;
        font-weight: bold;
        color: #333;
    }

    /* Style the close button */
    .close:hover,
    .close:focus {
        color: red;
        cursor: pointer;
    }

    /* Style for table */
    #fileDownloadTable {
        width: 100%;
        border-collapse: collapse;
    }

    /* Style for table headings */
    #fileDownloadTable th {
        text-align: left;
        /* or center or right, as needed */
        padding: 8px;
        /* Adjust padding as needed */
        background-color: #f2f2f2;
        border-bottom: 1px solid #ddd;
    }

    /* Style for table cells */
    #fileDownloadTable td {
        text-align: left;
        /* or center or right, matching with th */
        padding: 8px;
        /* Adjust padding as needed */
        border-bottom: 1px solid #ddd;
    }
</style>




<div class="modal-content white-modal">
    <!-- <span class="close">&times;</span> -->
    <?php if (count($fileNames) > 0) { ?>
        <table id="fileDownloadTable">

            <thead>
                <tr>
                    <th>Sl No</th>
                    <th>File</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 1;
                $pan  = 0;
                foreach ($fileNames as $file) {

                    $pan = isset($file['tfd']['pan']) ? $file['tfd']['pan'] : '';
                    $finY = isset($file['tfd']['fin_year']) ? $file['tfd']['fin_year'] : '';
                ?>
                    <tr>
                        <td><?php echo $i; ?></td>
                        <td><?php echo ($pan . " (" . $finY . ")"); ?></td>
                        <td style="text-align: center;">
                            <div class="btn" onclick="download_form('<?php echo $fieldName; ?>','<?php echo $emp; ?>','<?php echo $pan; ?>','single');">
                                <li class="fa fa-download pull-right" style="font-size: 20px; color: #3c8dbc;"></li>
                            </div>
                        </td>
                    </tr>
                <?php $i++;
                }
                if ($i > 2) { ?>
                    <tr>
                        <td></td>
                        <td></td>
                        <td>

                            <button type='button' onclick="download_form('<?php echo $fieldName; ?>','<?php echo $emp; ?>','<?php echo $pan; ?>','all');" class='toggleButton btn btn-primary' style=" margin: 0 auto;">Download all</button>

                        </td>
                    </tr>
                <?php } ?>
            </tbody>

        </table>
    <?php } else { ?>
        <div style="text-align: center;"> No Documents to download.</div>

    <?php } ?>

</div>




<script>
    function download_form(fieldName, empPkey, pan, count) {


        var downloadUrl = livesite + 'Taxation/formSixteenDownload/' + fieldName + '/' + empPkey + '/' + pan + '/' + count;

        // Create a hidden iframe element to trigger the download
        var iframe = document.createElement('iframe');
        iframe.style.display = 'none';


        iframe.src = downloadUrl;
        document.body.appendChild(iframe);

        // Check if the iframe was added to the DOM
        if (document.body.contains(iframe)) {
            // Display a notification indicating that the iframe was added
            $.notify('Document(s) successfully  downloaded', {
                type: 'success',
                allow_dismiss: false
            });
        } else {
            console.log('Failed to download.');
        }
    }
</script>

