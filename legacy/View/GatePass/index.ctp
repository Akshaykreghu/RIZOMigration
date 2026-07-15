<style>
    .form-horizontal .control-label {
        text-align: left;
    }

    .datagrid-cell-c2-download_link {
        width: 300px !important;
        /* Change the width to your desired value */
    }
</style>
<!-- Include jsPDF library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>

<!-- Include jsPDF html2pdf plugin -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.js"></script>

<script src="<?php echo $this->webroot; ?>plugins/ckeditor4/ckeditor4/ckeditor.js" type="text/javascript"></script>
<!--<script src="<?php echo $this->webroot; ?>plugins/ckeditor1/ckeditor.js" type="text/javascript"></script>-->

<?php
// Inside your view file, set the livesite variable using the webroot


?>
<section class="content-header" style="z-index: 900;">
    <h1 style="text-align:left; font-size: 3em;"> Gate Pass Generator </h1>
</section>



<!-- Main content -->
<section class="content" style="z-index: 900;">
    <div class="row">
        <div class="col-md-12">
            <div class="box ">
                <br>
                <div class="box-body">
                    <div class="box-body">
                        <table id="documents_manager" class="table table-bordered table-hover">
                            <tbody>
                            </tbody>
                        </table>
                    </div><!-- /.box-body -->
                </div>

            </div>
        </div>
    </div>
</section>

<script>
    var userGroup = <?php echo json_encode($user_group); ?>;

    var placeholders = [];
    CK_TEMPLATE = '';


    temp = '';



    jQuery(document).ready(function() {
        if (true) {
            $('#documents_manager').datagrid({
                url: livesite + "GatePass/getDocumentsFromDatabase",
                pagination: true,
                singleSelect: true,
                rownumbers: true,
                PostsearchFilter: true,
                pageSize: 10, // Set a default page size

                onBeforeLoad: function(param) {
                    // Update the pageSize based on the selected page list value
                    var selectedPageSize = $('#documents_manager').datagrid('getPager').pagination('options').pageSize;
                    param.rows = selectedPageSize;
                },

                toolbar: [{
                        text: 'New',
                        iconCls: 'icon-add',
                        handler: function() {
                            showLargeModalForm(livesite + 'GatePass/uploadForm')

                        }
                    },

                    {
                        text: 'Edit',
                        iconCls: 'icon-edit',
                        handler: function() {
                            var row = $('#documents_manager').datagrid('getSelected');
                            if (row) {
                                if (row.status == 1) {
                                    console.log('Row', row);
                                    var gate_pass_pkey = row.gate_pass_pkey;
                                    showLargeModalForm(livesite + 'GatePass/savePreview/' + gate_pass_pkey + '/' + 1 + '/' + true);
                                } else {
                                    alert("Pass already issued.");
                                }
                                // reloadTable('documents_manager');
                            } else {
                                //alert("Please choose a store");
                                $.notify('Please choose a document', {
                                    type: 'danger',
                                    allow_dismiss: false
                                });
                            }


                        }
                    },

                    {
                        iconCls: 'icon-remove',
                        text: 'Remove',
                        handler: function() {
                            var rows = $('#documents_manager').datagrid('getSelected');
                            if (rows) {
                                var document_id = rows.gate_pass_pkey;
                                console.log('Rows', rows);
                                if (rows.status != 2) {
                                    if (confirm("Are you sure want to delete ")) {
                                        $.ajax({
                                            url: livesite + "GatePass/deleteFromGrid/" + document_id,

                                            success: function(response) {
                                                // var response = $.parseJSON(response);

                                                if (response.message) {

                                                    if (response.status === 'success') {
                                                        $.notify(response.message, {
                                                            type: 'success',
                                                            allow_dismiss: true
                                                        });

                                                    } else if (response.status === 'failure') {
                                                        $.notify(response.message, {
                                                            type: 'danger', // Or another appropriate type for failure/error
                                                            allow_dismiss: true
                                                        });
                                                    }
                                                }
                                                // reloadTable('documents_manager');
                                            }
                                        });
                                        reloadTable('documents_manager');
                                    }
                                } else {
                                    alert("Cannot remove issued gate pass");
                                }

                            } else {
                                alert("Please select a record to delete")
                            }
                        }
                    }

                ],
                fitColumns: true,
                pageList: [2, 5, 10, 50, 100],
                columns: [
                    [

                        {
                            field: 'gate_pass_no',
                            title: 'Gate Pass No',
                            width: '20%'
                        },
                        {
                            field: 'type',
                            title: 'Type',
                            width: '20%'
                        },
                        {
                            field: 'issued_to',
                            title: 'Issued To',
                            width: '16%'
                        },
                        {
                            field: 'issued_date',
                            title: 'Date',
                            width: '14%'
                        },
                        {
                            field: 'creation_date',
                            title: 'Created Date & Time',
                            width: '14%'
                        },
                        {
                            field: 'download_link',
                            title: 'Document',
                            width: '16%'
                        }


                    ]
                ],
                onSearch: function(s) {
                    var searchValue = $('#searchqupo').val();
                    $('#documents_manager').datagrid('load', {
                        gate_pass_no: searchValue
                    });
                }
            });
        }




    });

    // JavaScript function to display the preview

    // Function to load the PDF preview by sending a document key to the controller


    // Function to initiate the download of the file



    function printGatePass(pkey = 0, pass = false) {
        // Create form element
        var form = document.createElement('form');
        form.style.display = 'none'; // Hide the form

        // Construct the URL
        var url = livesite + 'GatePass/printGatePass/';
        if (pkey) {
            url += pkey;
        }

        // Set form attributes
        form.method = 'GET';
        form.action = url;

        // Append the form to the body
        document.body.appendChild(form);

        // Prevent the default form submission (which causes a page reload)
        form.addEventListener('submit', function(event) {
            event.preventDefault();
        });

        // Submit the form
        form.submit();

        $.notify('Downloading gate pass...', {
                                type: 'success',
                                allow_dismiss: false,
                                className: 'notify-container',
                                z_index: 9999
                            });

        // Remove the form from the body
        document.body.removeChild(form);

    }

    //Download pdf

    function convertHtmlToPdf(htmlContent, fileName) {
        // Use html2pdf library to convert HTML to PDF
        html2pdf(htmlContent, {
            margin: 10, // Set the margin to your desired value (in millimeters)
            filename: fileName,
            html2canvas: {
                scale: 2
            },
            jsPDF: {
                unit: 'mm',
                format: 'a4',
                orientation: 'portrait'
            }
        }).from(htmlContent).output().then(function(pdfBlob) {
            // Save the PDF blob using FileSaver.js
            saveAs(pdfBlob, fileName);
        });
    }
</script>