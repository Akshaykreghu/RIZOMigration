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
            <!--   <div class="col-md-5">
               <label class="col-md-4 control-label" for="reportfrom">From:</label>
               <div class="col-md-6">
                   <input class="form-control input-md" id="reportfrom" name="reportfrom" value="" type="text" >
               </div>
           </div>
           <div class="col-md-5">
               <label class="col-md-4 control-label" for="reportto">To:</label>
               <div class="col-md-6">
                   <input class="form-control input-md" id="reportto" name="reportto" value="" type="text" >
               </div>
           </div>
            -->
            <?php if ($type == "LeaveLedger") { ?>
                <div class="col-md-4">
                    <div class="col-md-2"><b>From:</b></div>
                    <div class="col-md-4">
                        <select id="reportsfrom" name="reportsfrom" class="form-control">
                            <?php
                            $start_month = strtotime(date('Y-m', strtotime("+1 month", strtotime(date('Y-m')))));
                            for ($i = 0; $i < 60; $i++) {
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
                    <div class="col-md-1"><b>To:</b></div>
                    <div class="col-md-4">
                        <select id="reportsto" name="reportsto" class="form-control">
                            <?php
                            $start_month = strtotime(date('Y-m', strtotime("+1 month", strtotime(date('Y-m')))));
                            for ($i = 0; $i < 60; $i++) {
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
                </div>
            <?php } 
                $company_code=$this->Session->read('company_code');
            if ($type == "LeaveSummary" || $type == "Compoff" || $type == "LOPReport") { ?>
            <!-- end -->
                <div class="col-md-1"><b>Month&nbsp;:</b></div>
                <div class="col-md-2">



                    <div class="col-md-12" style="padding-left: 4px; padding-right: 4px; ">

                        <div class="col-md-6" style="padding-left: 4px; padding-right: 4px; ">
                            <input type="text" id="reporstfrom" name="reportfrom" class="form-control pickerDate" value="<?php echo date("d-m-Y"); ?>" />
                        </div>
                        <div class="col-md-6" style="padding-left: 4px; padding-right: 4px; ">
                            <input type="text" id="reporstto" name="reportto" class="form-control pickerDate" value="<?php echo date("d-m-Y"); ?>" />
                        </div>





                    </div>

                </div>

            <?php }
            // Edited by Akshay on 10-12-2024
            elseif ($type == "MonthlyLeave" || $type == 'LOP') { ?>
                <!-- End -->
                <!-- <div class="col-md-1"><b>Month&nbsp;:</b></div> -->
                <div class="col-md-1"><b>Month:</b></div>

                <div class="col-md-2">

                    <select id="reportfrom" name="reportfrom" class="form-control">

                        <?php $start_month = strtotime(date('Y-m', strtotime("+1 month", strtotime(date('Y-m')))));
                        for ($i = 0; $i < 24; $i++) {
                            $month = date('Y-m', strtotime("-$i month", $start_month));
                            if ($month == date('Y-m')) {
                                echo '<option selected="selected" value="' . $month . '">' . date('M-Y', strtotime("-$i month", $start_month)) . '</option>';
                            } else {
                                echo '<option value="' . $month . '">' . date('M-Y', strtotime("-$i month", $start_month)) . '</option>';
                            }
                        } ?>
                    </select>
                </div>
            <?php }
               //edited by athira on 15-10-2025
              elseif ($type == "LeaveBalance") { ?>
                <div class="col-md-1"><b>Select Date : </b></div>
                <div class="col-md-2">



                    <div class="col-md-12" style="padding-left: 4px; padding-right: 4px; ">

                        <div class="col-md-6" style="padding-left: 4px; padding-right: 4px; ">
                            <input type="text" id="reporstfrom" name="reportfrom" class="form-control pickerDate" value="<?php echo date("d-m-Y"); ?>" />
                        </div>
                    </div>

                </div>
            <?php }
            //end
            
            else { ?>

                <div class="col-md-1"><b>Year</b></div>
                <div class="col-md-2">



                    <div class="col-md-12" style="padding-left: 4px; padding-right: 4px; ">


                        <select id="reportfrom" name="reportfrom" class="form-control">
                            <option value="2025-01">2025</option>
                            <option value="2024-01">2024</option>
                            <option value="2023-01">2023</option>
                            <option value="2022-01">2022</option>
                            <option value="2021-01">2021</option>
                            <option value="2020-01">2020</option>
                            <option value="2019-01">2019</option>
                            <!--                            <option value="2018-01">2018</option>-->
                            <?php
                            /*
                             * By santhosh on 27 Dec 2015
                             */
                            //                            $start_month = strtotime(date('Y-m', strtotime("+1 month", strtotime(date('Y-m')))));
                            //                            for ($i = 0; $i < 36; $i++) {
                            //                                $month = date('Y-m', strtotime("-$i month", $start_month));
                            //                                if ($month == date('Y-m')) {
                            //                                     echo '<option selected="selected" value="' . $month . '">' . date('M-Y', strtotime("-$i month", $start_month)) . '</option>';
                            //                                } else {
                            //                                    echo '<option value="' . $month . '">' . date('M-Y', strtotime("-$i month", $start_month)) . '</option>';
                            //                                }
                            //                            }
                            ?>
                        </select>

                    </div>

                </div>

            <?php } ?>



            <input type="hidden" class="hidden-criterias" id="hidden-criteria1" name="hidden-criteria1" value="" />
            <div class="col-md-1"><b>Criteria &nbsp;:</b></div>
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
            <div class="col-md-3" id="div-items-criteria1">

            </div>
            <!--        <div class="col-md-1">
                        <a onclick="addOneReportCriteria(1);"><i class="fa fa-plus-circle"></i></a>
                        a onclick="removeThisCriteria(1);"><i class="fa fa-minus-circle"></i></a
                    </div>-->
            <!--  <div class="col-md-2" align="right">
                 <?php if ($type == "MonthlyLeave") { ?>
                <button type="button" onclick="viewReport();" id="btn-submit" class="btn btn-primary"><li class="fa fa-eye"></li></button>
                <button type="button" onclick="downloadReport('excel');" id="btn-submit2" class="btn btn-success"><li class="fa fa-file-excel-o"></li></button>
                 <?php } else { ?>
                <button type="button" onclick="viewReport();" id="btn-submit" class="btn btn-primary"><li class="fa fa-eye"></li></button>
                <?php if ($type != "LeaveSummary") { ?>
                <button type="button" onclick="downloadReport('pdf');" id="btn-submit1" class="btn btn-danger"><li class="fa fa-file-pdf-o"></li></button>
                <?php } ?>
                <button type="button" onclick="downloadReport('excel');" id="btn-submit2" class="btn btn-success"><li class="fa fa-file-excel-o"></li></button>
                 <?php } ?>
            </div> -->
            <div class="col-md-2" align="right">
                <?php if ($type == "LeaveLedger") { ?>
                    <button type="button" onclick="downloadReport('pdf');" id="btn-submit1" class="btn btn-danger">
                        <li class="fa fa-file-pdf-o"></li>
                    </button>
                <?php } else { ?>
                    <?php if ($type == "MonthlyLeave") { ?>
                        <button type="button" onclick="viewReport();" id="btn-submit" class="btn btn-primary">
                            <li class="fa fa-eye"></li>
                        </button>
                        <button type="button" onclick="downloadReport('excel');" id="btn-submit2" class="btn btn-success">
                            <li class="fa fa-file-excel-o"></li>
                        </button>
                    <?php } 
                    
                    else { ?>
                        <button type="button" onclick="viewReport();" id="btn-submit" class="btn btn-primary">
                            <li class="fa fa-eye"></li>
                        </button>
                        <!-- edited by athira on 23-06-2025 -->
                        <?php if ($type != "LeaveSummary" && $type !="LeaveBalance" && $type !="MonthlyLeave" && $type !="Compoff" && $type !="LOPReport") { ?>
                        <!-- end -->
                            <button type="button" onclick="downloadReport('pdf');" id="btn-submit1" class="btn btn-danger">
                                <li class="fa fa-file-pdf-o"></li>
                            </button>
                        <?php } ?>
                        <button type="button" onclick="downloadReport('excel');" id="btn-submit2" class="btn btn-success">
                            <li class="fa fa-file-excel-o"></li>
                        </button>
                    <?php } ?>
                <?php } ?>
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
        <?php } ?><div class="form-group">

        </div>
        <div id="reportCon" class="box box-body">

        </div>


    </form>
    <script>
        

        function loadCriteriaItems(index) {
            var criteria = $('#select-criteria' + index).val();
            $('#hidden-criteria' + index).val(criteria);
            $('#div-items-criteria' + index).load(livesite + 'miscellaniousReports/loadcriteriaitems/' + index + '/' + criteria);
               //edited by athira on 15-10-2025
            criteriaChanged();
            //end
        }

        function downloadReport(mode) {
   //edited by athira on 15-10-2025
                var selectedDate = $('#reporstfrom').val();
    if (selectedDate === '') {
        alert("Please select a date before viewing the report.");
        // $('#reporstfrom').focus();
        return false;
    }
                var selectedDate2 = $('#reporstto').val();
    if (selectedDate2 === '') {
        alert("Please select a date before viewing the report.");
        // $('#reporstfrom').focus();
        return false;
    }
//end
            var type = $('#hidden-report-type').val();
            var criteria = $('#select-criteria1').val();
            var selectany = false;

            $('.checkw').each(function() {
                if ($(this).prop('checked') == true) {
                    selectany = true;
                }
            });
            var checkedCount = $('.checkw:checked').length;
            //        return false;

            //if (selectany) {

            //} else {
            //  alert("Please choose criteria items first");
            //return false;
            //}
            if (!selectany) {

                alert("Please choose criteria items first");
                return false;
            }
            if (type === "LeaveLedger") {
                if (checkedCount !== 1) {
                    alert("Please select only one person for Leave Ledger");
                    return false;
                }
            }
            if (criteria) {
                $('#form-showreport').attr('action', livesite + 'miscellaniousReports/generatereport/' + type + '/' + mode);
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
            allempfields.clearAndLoad("<?php echo $this->webroot; ?>miscellaniousReports/listemployeefields", "json");

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
   //edited by athira on 15-10-2025
        function criteriaChanged() {
    var selected = document.getElementById('select-criteria1').value;
    var viewBtn = document.getElementById('btn-submit');

    if (selected === 'Leavestatus') {
        viewBtn.style.display = 'none'; // hide button
    } 
    else{
        viewBtn.style.display = 'inline-block';
    }
}
//end
       jQuery(document).ready(function () {

         //edited by athira on 15-10-2025  

    var reportType = $('#hidden-report-type').val(); // Assuming you have this hidden input

    // Remove Leave Type option for LeaveBalance report
    if (reportType === 'LeaveBalance') {
        $("#select-criteria1 option").each(function () {
            if ($(this).val() === 'LeaveType') { // Assuming value="LeaveType"
                $(this).remove();
            }
        });
    }
//end
    // Parsley validation
    $('#form-showreport').parsley();

       //edited by athira on 15-10-2025
    // Datepickers
    $('#reporstfrom').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
    });

    $('#reporstto').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
    });

 
   function getDateDifferenceInDays(startDate, endDate) {
    const oneDay = 1000 * 60 * 60 * 24;
    // Inclusive difference: add 1 day
    return Math.round((endDate - startDate) / oneDay) + 1;
}


function validateDateRange() {
    var fromDateVal = $('#reporstfrom').val();
    var toDateVal = $('#reporstto').val();

    if (!fromDateVal || !toDateVal) return;

    // Parse dd-mm-yyyy to Date object
    var partsFrom = fromDateVal.split("-");
    var partsTo = toDateVal.split("-");
    var fromDate = new Date(partsFrom[2], partsFrom[1] - 1, partsFrom[0]);
    var toDate = new Date(partsTo[2], partsTo[1] - 1, partsTo[0]);

    // Validate end >= start
    if (toDate < fromDate) {
        alert("End date should be greater than or equal to Start date.");
        $('#reporstto').val('');
        return;
    }

    // Calculate inclusive day difference
    var dayDiff = getDateDifferenceInDays(fromDate, toDate);

    if (reportType === "LeaveSummary" || reportType === "Compoff" || reportType === "LOPReport") {
        if (dayDiff > 60) {
            alert("The date range cannot exceed 60 days.");
            $('#reporstto').val('');
        }
    }
}


    // Trigger validation when either date changes
    $('#reporstfrom, #reporstto').on("change", function () {
        validateDateRange();
    });

});

//end
        function viewReport() {

            //edited by athira on 15-10-2025

            var selectedDate = $('#reporstfrom').val();
    if (selectedDate === '') {
        alert("Please select a date before viewing the report.");
        // $('#reporstfrom').focus();
        return false;
    }
                var selectedDate2 = $('#reporstto').val();
    if (selectedDate2 === '') {
        alert("Please select a date before viewing the report.");
        // $('#reporstfrom').focus();
        return false;
    }
    //end
            var selectany = false;
            $('.checkw').each(function() {
                if ($(this).prop('checked') == true) {
                    selectany = true;
                }
            });
            //        return false;
            if (selectany) {
                $('#loaders').show();
                var type = $('#hidden-report-type').val();
                var criteria = $('#select-criteria1').val();
                if (criteria) {
                    var url = livesite + 'miscellaniousReports/generatereport/' + type;
                    toggleItemsDisplay(1);
                    $('body').addClass('sidebar-collapse');
                    $.post(url, $('#form-showreport').serialize(), function(data) {
                        $('#reportCon').html(data);
                    });
                } else {
                    alert("Please select a criteria first");
                }
                $('#loaders').hide();

            } else {
                alert("Please choose criteria items first");
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
            $('#div-criteria' + newIndex).load(livesite + 'miscellaniousReports/addreportcriteria/' + type + '/' + newIndex + '/' + currentcriteriaschosen);

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