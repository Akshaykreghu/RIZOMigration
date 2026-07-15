<?php if (isset($arr_reportcriterias)) { ?>
    <script>
        $(document).ready(function() {
            $('#date').hide();

            var v = $('#filterby_reporttype').val();
            // Edited by Akshay on 6-2-2026
            var showDateFor = ['Musterroll', 'wage', 'FactoryMusterroll', 'ServiceRecord'];

            if (showDateFor.includes(v)) {
                $('#date').show();
                $('#btn-submit1').toggle(v === 'ServiceRecord');
            } else {
                $('#date, #btn-submit1').hide();
            }
            // End

        })
    </script>
    <form class="form-horizontal" method="post" action="" id="form-showreport">
        <input type="hidden" id="hidden-report-type" name="hidden-report-type" value="<?php echo $type; ?>" />
        <input type="hidden" id="hidden-criterias-count" name="hidden-criterias-count" value="1" />
        <input type="hidden" id="hidden-reportfields" name="hidden-reportfields" value="" />
        <div class="form-group" id="div-criteria1">
            <div id="date">

                <?php if ($type == "GrossPeriod" || $type == "LOPREPORT") { ?>
                    <div class="col-md-1"><b>Month&nbsp;:</b></div>
                    <div class="col-md-2">



                        <div class="col-md-12" style="padding-left: 4px; padding-right: 4px; ">

                            <div class="col-md-6" style="padding-left: 4px; padding-right: 4px; ">
                                <input type="text" id="reporstfrom" name="reportfrom" class="form-control pickerDate" value="<?php echo date("Y-m", strtotime("-1 months")); ?>" />
                            </div>
                            <div class="col-md-6" style="padding-left: 4px; padding-right: 4px; ">
                                <input type="text" id="reporstto" name="reportto" class="form-control pickerDate" value="<?php echo date("Y-m"); ?>" />
                            </div>





                        </div>

                    </div>

                <?php } else { ?>

                    <div class="col-md-1"><b>Month &nbsp;: </b></div>
                    <div class="col-md-2">
                        <select id="reportfrom" name="reportfrom" class="form-control">


                            <?php
                            /*
                                 * By santhosh on 27 Dec 2015
                                 */
                            $start_month = strtotime(date('Y-m', strtotime("+1 month", strtotime(date('Y-m')))));
                            /*
				 * By megha on 10 April 2019
				*/
                            for ($i = 0; $i < 38; $i++) {
                                /*
				* By megha on 10 April 2019
				 */
                                $month = date('Y-m', strtotime("-$i month", $start_month));
                                if ($month == date('Y-m')) {
                                    echo '<option selected="selected" value="' . $month . '">' . date('M-Y', strtotime("-$i month", $start_month)) . '</option>';
                                } else {
                                    echo '<option value="' . $month . '">' . date('M-Y', strtotime("-$i month", $start_month)) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                <?php } ?>
            </div>
            <input type="hidden" class="hidden-criterias" id="hidden-criteria1" name="hidden-criteria1" value="" />
            <div class="col-md-1"><b>Criteria &nbsp; : </b></div>
            <div class="col-md-3">
                <select id="select-criteria1" name="select-criteria1" style="width: 240px;" class="form-control" onchange="loadCriteriaItems(1);">
                    <option value="">--Choose criteria--</option>
                    <?php
                    foreach ($arr_reportcriterias as $key => $value) {
                        echo '<option value="' . $value['reportcriteria'] . '">' . $value['reportcriteria_desc'] . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-4" id="div-items-criteria1">

            </div>

        </div>

        <?php if ($type == 'employee') { ?>
            <div class="row">
                <div class="col-sm-2">
                </div>
                <div class="col-sm-4">
                    <div id="allempfields" style="width:100%; height:270px; background-color:white;"></div>
                </div>
                <div class="col-sm-4">
                    <div id="reportfields" style="width:100%; height:270px; background-color:white;"></div>
                </div>
                <div class="col-sm-2">
                </div>
            </div>
        <?php } ?>

        <div class="form-group">
            <div class="col-md-12" align="right">
                <!-- Edited by Akshay on 26-11-2024 -->
                <?php
                if (trim($type) == 'Musterroll' && ($company_code == 'HRBL')) { ?>
                    <small id="selection-limit-note" style="color: red; position: absolute; left: 0; margin-left: 10px;">
                        * The print option is only accessible by clicking the 'View' icon in the report form.
                    </small>
                <?php }
                ?>
                <!-- End -->

                <!-- Edited by Akshay on 6-2-2026 -->
                <?php if (trim($type) !== 'ServiceRecord') { ?>
                    <button type="button" onclick="viewReport();" id="btn-submit" class="btn btn-primary">
                        <li class="fa fa-eye"></li>
                    </button>
                <?php } ?>
                <?php if (trim($type) == 'ServiceRecord') { ?>
                    <button type="button" onclick="downloadReport('pdf');" id="btn-submit1" class="btn btn-danger">
                        <li class="fa fa-file-pdf-o"></li>
                    </button>
                <?php } ?>
                <!-- End -->

                <button type="button" onclick="downloadReport('excel');" id="btn-submit2" class="btn btn-success">
                    <li class="fa fa-file-excel-o"></li>
                </button>
                <!-- Edited by Akshay on 26-11-2024 -->
                <?php
                if (trim($type) == 'Musterroll' && ($company_code == 'HRBL')) { ?>
                    <button type="button" id="btn-submit3" class="btn btn-info" style="display: none;">
                        <li class="fa fa-print"></li>
                    </button>
                <?php }
                ?>
                <!-- End -->
            </div>

        </div>
        <div id="reportCon" class="box box-body">

        </div>
    </form>
    <script>
        function downloadReport(mode) {
            var type = $('#hidden-report-type').val();
            var criteria = $('#select-criteria1').val();
            var selectany = false;
            $('.checkw').each(function() {
                if ($(this).prop('checked') == true) {
                    selectany = true;
                }
            });
            //        return false;
            if (selectany) {

            } else {
                alert("Please Choose Criteria items First");
                return false;
            }
            if (criteria) {
                $('#form-showreport').attr('action', livesite + 'StatutoryRegisters/generatereport/' + type + '/' + mode);
                $('#form-showreport').submit();
                $('#btn-submit3').hide(); // Edited by Akshay on 26-11-2024
            } else {
                alert("Please select a criteria first");
            }





        }

        function hasValue(elem) {
            return $(elem).filter(function() {
                return $(this).val();
            }).length > 0;
        }
        <?php if ($type == 'employee') { ?>
            var allempfields;
            allempfields = new dhtmlXGridObject('allempfields');
            allempfields.selMultiRows = true;
            allempfields.setHeader("Fields");
            allempfields.setInitWidths("*");
            allempfields.setColAlign("left");
            allempfields.setColSorting("str");
            allempfields.attachEvent("onDrag", function(sId, tId, sObj, tObj, sInd, tInd) {
                /*var currentReportFields = $('#hidden-reportfields').val().split(',').filter(function(v){return v!==''});
                var arrSelectedFields = sId.split(',');
                var difference = [];
                $.grep(arrSelectedFields, function(el) {
                    console.log(el);
                    if ($.inArray(el, currentReportFields) == -1) difference.push(el);
                })
                var newReportFields = $.unique(difference);
                console.log(newReportFields);
                //$('#hidden-reportfields').val(newReportFields.join(','));*/
                return true;
            });
            allempfields.setMultiLine(false);
            allempfields.enableDragAndDrop(true);
            allempfields.init();
            allempfields.clearAndLoad("<?php echo $this->webroot; ?>StatutoryRegisters/listemployeefields", "json");

            var reportfields;
            reportfields = new dhtmlXGridObject('reportfields');
            reportfields.selMultiRows = true;
            reportfields.setHeader("Report Fields");
            reportfields.setInitWidths("*");
            reportfields.setColAlign("left");
            reportfields.setColSorting("str");
            reportfields.setMultiLine(false);
            reportfields.enableDragAndDrop(true);
            reportfields.init();
            reportfields.attachEvent("onDrag", function(sId, tId, sObj, tObj, sInd, tInd) {
                /*var currentReportFields = $('#hidden-reportfields').val().split(',').filter(function(v){return v!==''});
                var arrSelectedFields = sId.split(',');
                var newReportFields = $.unique($.merge(arrSelectedFields, currentReportFields));
                $('#hidden-reportfields').val(newReportFields.join(','));*/
                return true;
            });
        <?php } ?>
        jQuery(document).ready(function() {
            $('#reporstfrom').datepicker({
                format: 'yyyy-mm',
                autoclose: true,
                startView: "months",
                minViewMode: "months"
            });

            $('#reporstto').datepicker({
                format: 'yyyy-mm',
                autoclose: true,
                startView: "months",
                minViewMode: "months"
            });
            /*$('#reportfrom').datepicker({
                format: 'yyyy-mm-dd'
            })
            $("#reportfrom").inputmask("yyyy-mm-dd");
            $('#reportto').datepicker({
                format: 'yyyy-mm-dd'
            })
            $("#reportto").inputmask("yyyy-mm-dd");*/

            $('#form-showreport').parsley();
            //Edited by Akshay on 26-11-2024
            $('#btn-submit3').click(function() {
                var type = $('#hidden-report-type').val();
                var content = $('#reportCon').html();
                printReportContent(content);
            });
            //End
            /*var options = {
        success : function(responseText, statusText, xhr, $form) {
            var response = JSON.parse(responseText);
            if (response.success) {
                alert(response.message);
            } else {
                alert('Something wrong happened!');
            }
        }
    };
    // bind to the form's submit event
    $('#form-showreport').submit(function(e) {
        //$(this).ajaxSubmit(options);
        e.preventDefault();
        var container = $("#largeModalForm #largeModalForm-content");
        var url = livesite+'Reports/generatereport/employee';
	container.load(url,$(this).serialize(), function() {
            $("#largeModalForm").modal('show')
        });
        return false;
    });*/
        });
        //Edited by Akshay on 26-11-2024
        function printReportContent(content) {
            var printWindow = window.open('', '_blank', 'height=600,width=900');
            printWindow.document.write('<html><head><title>Print Document</title>');
            printWindow.document.write('<style>');
            printWindow.document.write('body { font-size: 10pt; margin: 0; padding: 0; visibility: hidden; border: none; }'); // Remove any body borders
            printWindow.document.write('table { width: 100%; border-collapse: collapse; border: 1px solid black; margin: 0; padding: 0;table-layout: auto; }'); // Table with visible border
            printWindow.document.write('th, td { font-size: 5pt; padding: 1px; text-align: left; border: 1px solid black; }'); // Preserve cell borders
            printWindow.document.write('th { background-color: #f2f2f2; text-align: center; }');
            printWindow.document.write('.single-line { white-space: nowrap; }');
            printWindow.document.write('.align-left { text-align: left !important; padding-left: 80px !important; }');
            printWindow.document.write('.align-nodataleft { text-align: left !important; padding-left: 0px !important; }');
            printWindow.document.write('.align-nodata { font-weight: bold; }');
            printWindow.document.write('body, th, td { box-sizing: border-box; }'); // Ensure proper box sizing
            printWindow.document.write('div, span, p, *:not(table, th, td) { border: none !important; }'); // Remove borders for non-table elements
            printWindow.document.write('@media print { body { visibility: visible; } table { page-break-inside: auto; } }');
            printWindow.document.write('@page { size: A3 landscape; margin: 5mm; }'); // Default A3 landscape layout Edited by Akshay on 5-12-2024
            printWindow.document.write('tr { page-break-inside: avoid; page-break-after: auto; }'); // Avoid breaking rows
            printWindow.document.write(`
                                        td:nth-child(n+14) {
                                            white-space: nowrap; /* Prevent wrapping */
                                            padding: 1px; /* Add 1px padding inside the cell */
                                            margin: 0; /* Remove margin */
                                            border: 1px solid black; /* Ensure consistent borders */
                                            width: 1%; /* Force minimum width based on content */
                                            box-sizing: border-box; /* Ensure padding is included in the width */
                                        }
                                    `); // Apply styling for columns after the 14th column
            printWindow.document.write('</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write(content);
            printWindow.document.write('</body></html>');
            printWindow.document.close();

            printWindow.onload = function() {
                printWindow.print();
                printWindow.close();
            };
        }
        //End
        // Edited by Akshay on 2-8-2025
        function viewReport() {
            var type = $('#hidden-report-type').val();
            var criteria = $('#select-criteria1').val();
            // Edited by Akshay on 21-8-2025
            var selectany = false;
            $('.checkw').each(function() {
                if ($(this).prop('checked') == true) {
                    selectany = true;
                }
            });

            if (!selectany) {
                alert("Please Choose Criteria items First");
                return false;
            }
            // End

            if (criteria) {
                var container = $("#reportCon");
                var url = livesite + 'StatutoryRegisters/generatereport/' + type;
                var payload = $('#form-showreport').serialize();

                toggleItemsDisplay(1);
                $('body').addClass('sidebar-collapse');

                $.post(url, payload, function(response) {
                    container.html(response);
                    $('#btn-submit3').show(); // Edited by Akshay on 26-11-2024
                }).fail(function() {
                    alert("Failed to load the report. Please try again.");
                });
            } else {
                alert("Please select a criteria first");
            }
        }
        // End

        function loadCriteriaItems(index) {
            var criteria = $('#select-criteria' + index).val();
            $('#hidden-criteria' + index).val(criteria);
            $('#div-items-criteria' + index).load(livesite + 'StatutoryRegisters/loadcriteriaitems/' + index + '/' + criteria);
        }

        function addOneReportCriteria(index) {
            var type = $('#hidden-report-type').val();

            var currentcriteriaschosen = '';
            $('#form-showreport .hidden-criterias').each(function() {
                currentcriteriaschosen += (currentcriteriaschosen == '') ? this.value : ',' + this.value;
            });
            $('#form-showreport .link-removecriterias').remove();
            var newIndex = index + 1;
            $('<div class="form-group" id="div-criteria' + newIndex + '"></div>').insertAfter($('#div-criteria' + index));
            $('#div-criteria' + newIndex).load(livesite + 'StatutoryRegisters/addreportcriteria/' + type + '/' + newIndex + '/' + currentcriteriaschosen);

            var count = $('#hidden-criterias-count').val();
            if (count >= 1) {
                $('#hidden-criterias-count').val(parseInt(count) + 1);
            }
        }

        function removeThisCriteria(index) {
            var count = $('#hidden-criterias-count').val();
            $('#hidden-criterias-count').val(parseInt(count) - 1);
            $('#div-criteria' + index).remove();
        }
    </script>
<?php } ?>