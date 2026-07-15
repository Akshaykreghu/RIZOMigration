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
</style>

<div class="modal-header" style="background-color: #00659f; color: white; display: flex; justify-content: space-between; align-items: center;">
    <h4 class="modal-title" id="exampleModalLabel" style="text-align: center; flex: 1;">Download Form 16</h4>
    <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="$('#modalForm').modal('hide');">
        <span aria-hidden="true" style="color: white;">&times;</span>
    </button> -->
</div>



<div class="modal-body">
    <!-- <span class="close">&times;</span> -->
    <?php if (count($fileNames) > 0) { ?>
        <table class="striped-table" id="fileDownloadTable" style="margin: 0 auto; width:100%;">

            <thead>
                <tr>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 1;
                $pan  = 0;
                foreach ($fileNames as $file) {

                    $pan = isset($file[0]['tfd']['pan']) ? $file[0]['tfd']['pan'] : '';
                    $finY = isset($file[0]['tfd']['fin_year']) ? $file[0]['tfd']['fin_year'] : '';
                    $formName = isset($file[0]['tfd']['form_name']) ? trim($file[0]['tfd']['form_name']) : '';
                    // debug($formName);
                ?>
                    <tr class="table-row">
                        <td></td>
                        <td style="text-align: center;"><?php echo ($finY); ?></td>
                        <td style="text-align: center;">
                            <div class="btn" onclick="download_form('<?php echo $finY; ?>','<?php echo $emp_pkey; ?>','<?php echo $pan; ?>','single','<?php echo $formName; ?>');">
                                <li class="fa fa-download pull-right" style="font-size: 20px; color: #3c8dbc;"></li>
                            </div>
                        </td>
                    </tr>
                <?php $i++;
                }
                if (true) { ?>
                    <tr>
                        <td></td>
                        <td></td>
                        <!-- <td style="text-align:center;">

                            <button type='button' onclick="download_form('<?php echo $finY; ?>','<?php echo $emp_pkey; ?>','<?php echo $pan; ?>','all');" class='toggleButton btn btn-primary' style=" margin: 0 auto;">Download all</button>

                        </td> -->
                        <td style="text-align:right;">

                            <button type='button'  onclick="$('#modalForm').modal('hide');" class='toggleButton btn btn-danger' style=" margin: 0 auto;">Cancel</button>

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
    function download(fieldName, empPkey) {
        console.log(fieldName);
        var divs = $('.load').children().removeClass('box box-body custom_css').css('border-style', 'none');
        divs.html('');

        var div = $('#loaded' + fieldName.replace(/ /g, "_")).addClass('box box-body custom_css').css('border-style', 'outset');

        // Rest of your code to load the modal content
        // Assuming 'showLargeModalForm' function is available
        showModalForm(livesite + 'Tax/downloadtaxdocument_modal/' + fieldName + '/' + empPkey);
    }


    // function download_form(fieldName, empPkey, pan, count) {
    //     var downloadUrl = livesite + 'Tax/formSixteenDownload/' + fieldName + '/' + empPkey + '/' + pan + '/' + count;

    //     fetch(downloadUrl, {
    //             method: 'GET',
    //         })
    //         .then(response => response.blob())
    //         .then(blob => {
    //             var url = window.URL.createObjectURL(blob);

    //             // Create a temporary <a> element to trigger the download
    //             var a = document.createElement('a');
    //             a.style.display = 'none';
    //             a.href = url;
    //             a.download = 'your_filename_here'; // Set the desired filename
    //             document.body.appendChild(a);

    //             a.click();

    //             // Clean up after the download
    //             window.URL.revokeObjectURL(url);
    //             document.body.removeChild(a);

    //             // Display a notification indicating that the download was successful
    //             $.notify('Document(s) successfully downloaded', {
    //                 type: 'success',
    //                 allow_dismiss: false
    //             });
    //         })
    //         .catch(error => {
    //             console.error('Failed to download:', error);
    //             // Display an error notification if the download fails
    //             $.notify('Failed to download document(s)', {
    //                 type: 'error',
    //                 allow_dismiss: false
    //             });
    //         });
    // }
    function download_form(fieldName, empPkey, pan, count, formName) {

        // console.log('File name', formName);
        var downloadUrl = livesite + 'Tax/formSixteenDownload/' + fieldName + '/' + empPkey + '/' + pan + '/' + count+ '/' + formName;

        // console.log('URL',downloadUrl);

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