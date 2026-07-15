<style>
    #loaders {
        display: block;
        /* Change to block to ensure it's visible for testing */
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 9999;
        background: rgba(255, 255, 255, 0.8);
        padding: 20px;
        border-radius: 8px;
        text-align: center;
    }
</style>

<?php if (isset($arr_reportcriterias)) { ?>
    <script>
        //jQuery(document).ready(function() {
        //  $('#reportfrom').datepicker({
        //       format: 'yyyy-mm-dd HH:MM:ss'
        //  })
        // $("#reportfrom").inputmask("yyyy-mm-dd");
        // $('#reportto').datepicker({
        //      format: 'yyyy-mm-dd HH:MM:ss'
        //    })
        // $("#reportto").inputmask("yyyy-mm-dd");
        //});
    </script>
    <form class="form-horizontal" method="post" action="" id="form-showreport">
        <input type="hidden" id="hidden-report-type" name="hidden-report-type" value="<?php echo $type; ?>" />

        <input type="hidden" id="hidden-criterias-count" name="hidden-criterias-count" value="1" />
        <input type="hidden" id="hidden-reportfields" name="hidden-reportfields" value="" />

        <div class="form-group" id="div-criteria1">

            <div class="col-md-1"><b>Month &nbsp;:</b></div>
            <div class="col-md-2">
                <?php if ($type == "Dashboard" || $type == "regularisation") { ?>

                    <div class="col-md-12" style="padding-left: 4px; padding-right: 4px;">
                        <div class="col-md-6" style="padding-left: 4px; padding-right: 4px;">
                            <input type="text" id="reporstfrom" name="reportfrom" class="form-control pickerDate" value="<?php echo date('Y-m-d'); ?>" autocomplete="off" />
                        </div>
                        <div class="col-md-6" style="padding-left: 4px; padding-right: 4px;">
                            <input type="text" id="reporstto" name="reportto" class="form-control pickerDate" value="<?php echo date('Y-m-d'); ?>" autocomplete="off" />
                        </div>
                    </div>


                <?php } elseif ($type == "nonpunched" || $type == "nonattendance") { ?>
                    <div class="col-md-12" style="padding-left: 4px; padding-right: 4px;">
                        <div class="col-md-6" style="padding-left: 4px; padding-right: 4px;">
                            <input type="text" id="reporstfrom" name="reportfrom" class="form-control pickerDate" value="<?php echo date('d-m-Y'); ?>" autocomplete="off" />
                        </div>
                        <div class="col-md-6" style="padding-left: 4px; padding-right: 4px;">
                            <input type="text" id="reporstto" name="reportto" class="form-control pickerDate" value="<?php echo date('d-m-Y'); ?>" autocomplete="off" />
                        </div>
                    </div>
                <?php } else { ?>

                    <select id="reportfrom" name="reportfrom" class="form-control">


                        <?php
                        /*
                         * By santhosh on 27 Dec 2015
                         */
                        $start_month = strtotime(date('Y-m', strtotime("+1 month", strtotime(date('Y-m')))));
                        for ($i = 0; $i < 70; $i++) {
                            $month = date('Y-m', strtotime("-$i month", $start_month));
                            if ($month == date('Y-m')) {
                                echo '<option selected="selected" value="' . $month . '">' . date('M-Y', strtotime("-$i month", $start_month)) . '</option>';
                            } else {
                                echo '<option value="' . $month . '">' . date('M-Y', strtotime("-$i month", $start_month)) . '</option>';
                            }
                        }
                        ?>
                    </select>

                <?php } ?>
            </div>




            <input type="hidden" class="hidden-criterias" id="hidden-criteria1" name="hidden-criteria1" value="" />
            <div class="col-md-1"><b>Criteria &nbsp;:</b></div>
            <div class="col-md-2">
                <select id="select-criteria1" name="select-criteria1" style="width: 210px;" class="form-control" onchange="loadCriteriaItems(1);">
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
            <div id="printPanel" class="form-group col-md-2">
                <div class="col-md-12" align="right">
                    <button type="button" onclick="viewReport();" id="btn-submit" class="btn btn-primary">
                        <li class="fa fa-eye"></li>
                    </button>
                    <?php if ($type != "Dashboard") { ?>
                        <!--//edited by amal on 08/08/2019 hide pdf-->
                        <?php if (($type != "Attendance") && ($type != "DetailedAttendance") &&  ($type != "VerifiedAttendance") && ($type != "regularisation") && ($type != "nonpunched") && ($type != "nonattendance")) {
                            if ($type != "OvertimeReport") { ?>
                                <!-- <button type="button" onclick="downloadReport('pdf');" id="btn-submit1" class="btn btn-danger">
                                    <li class="fa fa-file-pdf-o"></li>
                                </button> -->
                        <?php }
                        } ?>
                        <button type="button" onclick="downloadReport('excel');" id="btn-submit2" class="btn btn-success">
                            <li class="fa fa-file-excel-o"></li>
                        </button>
                    <?php } ?>
                </div>

            </div>
            <!--        <div class="col-md-1">
                        <a onclick="addOneReportCriteria(1);"><i class="fa fa-plus-circle"></i></a>
                        a onclick="removeThisCriteria(1);"><i class="fa fa-minus-circle"></i></a
                    </div>-->


        </div>



        <div class="form-group" id="div-criteria1">

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

        <!--    <div id="printPanel" class="form-group">
            <div class="col-md-12" align="right">
                <button type="button" onclick="viewReport();" id="btn-submit" class="btn btn-primary"><li class="fa fa-eye"></li></button>
              <?php if ($type != "Dashboard") { ?>
                <!--//edited by amal on 08/08/2019 hide pdf--
               <?php if ($type != "VerifiedAttendance") { ?>
                <button type="button" onclick="downloadReport('pdf');" id="btn-submit1" class="btn btn-danger"><li class="fa fa-file-pdf-o"></li></button>
               <?php } ?>
                <button type="button" onclick="downloadReport('excel');" id="btn-submit2" class="btn btn-success"><li class="fa fa-file-excel-o"></li></button>
              <?php } ?>  
            </div>
          
        </div> -->

        <div id="reportCon" class="box box-body">

        </div>
    </form>
    <div id="largeModalForm1" class="modal fade">
        <div class="modal-dialog modal-lg" style="width: 90%">
            <div class="modal-content" id="largeModalForm-content1">
                <!-- Content will be loaded here from "remote.php" file -->
            </div>
        </div>
    </div>
    <script>
        function loadCriteriaItems(index) {
            var criteria = $('#select-criteria' + index).val();
            $('#hidden-criteria' + index).val(criteria);
            $('#div-items-criteria' + index).load(livesite + 'attendanceReports/loadcriteriaitems/' + index + '/' + criteria);
        }

        function downloadReport(mode) {
            var type = $('#hidden-report-type').val();
            var criteria = $('#select-criteria1').val();
            var selectany = false;
            $('.checkw').each(function() {
                if ($(this).prop('checked') == true) {
                    selectany = true;
                }
            });

            // Edited by Akshay on 15-2-2025
            if (type === "nonpunched" || type === "nonattendance") {
                var fromInput = $("#reporstfrom").val().trim();
                var toInput = $("#reporstto").val().trim();

                if (fromInput === "" || toInput === "") {
                    alert("Please fill in both date fields before submitting.");
                    return false;
                }
            }
            // End

            if (selectany) {

            } else {
                alert("Please Choose Criteria items First");
                return false;
            }
            if (criteria) {
                $('#form-showreport').attr('action', livesite + 'attendanceReports/generatereport/' + type + '/' + mode);
                $('#form-showreport').submit();
                loadCriteriaItems(1);
            } else {
                alert("Please select a criteria first");
            }





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
            allempfields.clearAndLoad("<?php echo $this->webroot; ?>attendanceReports/listemployeefields", "json");

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


            var type = <?php echo (json_encode($type)) ?>;
            if (type === "nonpunched" || type === "nonattendance") {
                function parseDate(dateStr) {
                    let parts = dateStr.split("-");
                    return new Date(parts[2], parts[1] - 1, parts[0]); // Convert DD-MM-YYYY to YYYY-MM-DD
                }

                function validateDateRange(changedField) {
                    let fromDateStr = $("#reporstfrom").val().trim();
                    let toDateStr = $("#reporstto").val().trim();

                    if (fromDateStr === "" || toDateStr === "") return;

                    let fromDate = parseDate(fromDateStr);
                    let toDate = parseDate(toDateStr);
                    console.log('fromDate', fromDate);
                    console.log('toDate', toDate);

                    if (fromDate > toDate) {
                        alert("From date should not be less than to date.");
                        $("#reporstto").val("");
                        $("#reporstfrom").val(""); 
                    }

                    let differenceInDays = (toDate - fromDate) / (1000 * 3600 * 24);
                    console.log('differenceInDays', differenceInDays);

                    if (differenceInDays >= 31) {
                        alert("Total number of days should not be greater than 31");

                        if (changedField === "from") {
                            $("#reporstto").val(""); // Clear "To Date" if "From Date" exceeds range
                        } else if (changedField === "to") {
                            $("#reporstfrom").val(""); // Clear "From Date" if "To Date" exceeds range
                        }
                    }
                }

                $('#reporstfrom').datepicker({
                    format: 'dd-mm-yyyy',
                    autoclose: true
                }).on('changeDate', function() {
                    validateDateRange("from");
                });

                $('#reporstto').datepicker({
                    format: 'dd-mm-yyyy',
                    autoclose: true
                }).on('changeDate', function() {
                    validateDateRange("to");
                });
            } else {
                $('#reporstfrom').datepicker({
                    format: 'yyyy-mm-dd',
                    autoclose: true,
                    // startView: "months",
                    // minViewMode: "months"
                }).on('changeDate', function(e) {
                    var selected = $("#reporstfrom").val();
                    var sdt = new Date(selected);
                    var selectenddate = $("#reporstto").val();
                    var edt = new Date(selectenddate);
                    if (edt < sdt) {
                        alert("From date should be less than To date");
                        $("#reporstfrom").val('');
                    } else {
                        if (edt != null) {
                            var Difference_In_Time = edt.getTime() - sdt.getTime();
                            var Difference_In_Days = Difference_In_Time / (1000 * 3600 * 24);
                            if (Difference_In_Days > 31) {
                                alert("Total number of days should be less than 31");
                                $("#reporstfrom").val('');
                            }
                        }
                    }
                });

                $('#reporstto').datepicker({
                    format: 'yyyy-mm-dd',
                    autoclose: true,
                    // startView: "months",
                    // minViewMode: "months"
                }).on('changeDate', function(e) {
                    var selected = $("#reporstto").val();
                    var edt = new Date(selected);
                    var selectsdate = $("#reporstfrom").val();
                    var sdt = new Date(selectsdate);
                    if (edt < sdt) {
                        alert("To date should be greater than From date");
                        $("#reporstto").val('');
                        return false;
                    } else {
                        if (sdt != null) {
                            var Difference_In_Time = edt.getTime() - sdt.getTime();
                            var Difference_In_Days = Difference_In_Time / (1000 * 3600 * 24);
                            if (Difference_In_Days > 31) {
                                alert("Total number of days should be less than 31");
                                $("#reporstto").val('');
                            }
                        }
                    }
                });
            }


            $('#form-showreport').parsley();


        });

        function viewReport() {
            var selectany = false;
            $('.checkw').each(function() {
                if ($(this).prop('checked') == true) {
                    selectany = true;
                }
            });

            // Edited by Akshay on 15-2-2025
            var type = <?php echo (json_encode($type)) ?>;
            if (type === "nonpunched" || type === "nonattendance") {
                var fromInput = $("#reporstfrom").val().trim();
                var toInput = $("#reporstto").val().trim();

                if (fromInput === "" || toInput === "") {
                    alert("Please fill in both date fields before submitting.");
                    return false;
                }
            }
            // End

            if (selectany) {
                $('#loaders').attr('style', 'display: block !important;');
                $('#loaders').show();
                var type = $('#hidden-report-type').val();
                //var container = $("#largeModalForm1 #largeModalForm-content1");
                var criteria = $('#select-criteria1').val();
                if (criteria) {
                    var container = $("#reportCon");
                    var url = livesite + 'attendanceReports/generatereport/' + type;

                    var arrReportFieldsChosen = [];
                    var reportfields;
                    $('#hidden-reportfields').val(arrReportFieldsChosen.join(','));
                    toggleItemsDisplay(1);
                    $('body').addClass('sidebar-collapse');
                    // container.load(url, $('#form-showreport').serialize(), function () {
                    // Serialize the form data
                    var formData = $('#form-showreport').serialize();

                    // Use $.post() method instead of $.load()
                    $.post(url, formData, function(response) {
                        container.html(response); // Replace container content with the response
                        $('body').addClass('sidebar-collapse');
                    }).always(function() {
                        // Hide the loader after the request completes (success or error)
                        $('#loaders').hide();
                    });
                } else {
                    alert("Please select a criteria first");
                }


            } else {
                alert("Please Choose Criteria items first");
                return false;
            }

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
            $('#div-criteria' + newIndex).load(livesite + 'attendanceReports/addreportcriteria/' + type + '/' + newIndex + '/' + currentcriteriaschosen);

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
        // changes anukrishnan 17-01-2025 open 
        // function toggleSections() {
        //     var selectedValue = document.getElementById('select-type').value;
        //     var periodSelect = document.getElementById("period_select");
        //     var reportFromSelect = document.getElementById("reportfrom");
        //     // const reportMonthDiv = document.querySelector(".col-md-1.report_month");
        //     const reportMonthDiv = document.querySelector(".col-md-1.report_month b");

        //     if (selectedValue === "period") {
        //         periodSelect.style.display = "block";
        //         reportFromSelect.style.display = "none";
        //         reportMonthDiv.textContent = "Period :";
        //         // reportMonthDiv.style.display = "none";
        //     } else {
        //         periodSelect.style.display = "none";
        //         reportFromSelect.style.display = "block";
        //         reportMonthDiv.textContent = "Month :";
        //         // reportMonthDiv.style.display = "block";
        //     }
        // }

        // $('#reporstfrom1').datepicker({
        //     format: 'dd-mm-yyyy',
        //     autoclose: true,
        // }).on('changeDate', function (e) {
        //     var selected = $("#reporstfrom1").val();
        //     var sdt = new Date(selected);
        //     var selectenddate = $("#reporstto1").val();
        //     var edt = new Date(selectenddate);
        //     if (edt < sdt)
        //     {
        //         alert("From date should be less than To date");
        //         $("#reporstfrom1").val('');
        //     }
        // });

        // $('#reporstto1').datepicker({
        //     format: 'dd-mm-yyyy',
        //     autoclose: true,
        // }).on('changeDate', function (e) {
        //     var selected = $("#reporstto1").val();
        //     var edt = new Date(selected);
        //     var selectsdate = $("#reporstfrom1").val();
        //     var sdt = new Date(selectsdate);
        //     if (edt < sdt)
        //     {
        //         alert("To date should be greater than From date");
        //         $("#reporstto1").val('');
        //         return false;
        //     }
        // });

        // changes anukrishnan 17-01-2025 close 
    </script>
<?php } ?>