<style>
    /* #reportCon table,td,th{
  border-style: none;
  border: none;
  } */
  .width1{
    width: 87px;
  }
  .width2{
    width: 82px;
  }
</style>

<?php
if (isset($arr_reportcriterias)) { 
    ?>
    <script>
        $(document).ready(function() {
            $('#date').hide();

            var v = $('#filterby_reporttype').val();
            //debug(v);
            if (true) {
                $('#date').show();
            } else {
                $('#date').hide();
            }

        });
    </script>
    <form class="form-horizontal" method="post" action="" id="form-showreport">
        <input type="hidden" id="hidden-report-type" name="hidden-report-type" value="<?php echo $type; ?>" />
        <input type="hidden" id="hidden-criterias-count" name="hidden-criterias-count" value="1" />
        <input type="hidden" id="hidden-reportfields" name="hidden-reportfields" value="" />
        <div class="form-group" id="div-criteria1">
            <div id="date">




                <?php 
            
                if ($type == "GrossPeriod" || $type == "LOPREPORT") { ?>
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

                <?php } else if ($type == 'Analysis') { ?>
                    <div class="col-md-1"><b>Month&nbsp;:</b></div>
                    <div class="col-md-2" style="padding-left: 0px; padding-right: 0px;">



                        <div class="col-md-12" style="padding-left: 4px; padding-right: 4px; ">

                            <div class="col-md-4" style="padding-left: 4px; padding-right: 4px; ">
                                <input type="text" id="prev_month" name="reportfrom" class="form-control pickerDate width1" placeholder="Select Month" readonly />
                            </div>
                            <div class="col-md-4" style="padding-left: 4px; padding-right: 4px; margin-left:15%;">
                                <input type="text" id="next_month" name="reportto" class="form-control pickerDate width2" style="display: none;" placeholder="Compare To" readonly />
                            </div>





                        </div>

                    </div>
                <? } else { ?>

                    <div class="col-md-1"><b>Month: </b></div>
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
            <div class="col-md-1"><b>Criteria: </b></div>
            <div class="col-md-3">
                <select id="select-criteria1" name="select-criteria1" style="width: 190px;" class="form-control" onchange="loadCriteriaItems(1);">
                    <option value="">--Choose criteria--</option>
                    <?php
                    // Sort the $arr_reportcriterias array by 'reportcriteria_desc' field in ascending order
                    sort($arr_reportcriterias, function ($a, $b) {
                        return strcmp($a['reportcriteria_desc'], $b['reportcriteria_desc']);
                    });

                    foreach ($arr_reportcriterias as $key => $value) {
                        echo '<option value="' . $value['reportcriteria'] . '">' . $value['reportcriteria_desc'] . '</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-4" id="div-items-criteria1">

            </div>
            <?php if ($type == "BankTranferNew") { ?>
                <div class="col-md-2" id="payment" style="display:none;"><b>Payment Mode &nbsp; : </b>
                    <select id="select-payment" name="select-payment" style="width: 140px;" class="form-control">
                        <option value="NEFT">NEFT</option>
                        <option value="RTGS">RTGS</option>
                    </select>
                </div>
            <?php } ?>
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

                <?php if (false) { ?>
                    <button type="button" onclick="viewReport();" id="btn-submit" class="btn btn-primary">
                        <li class="fa fa-eye"></li>
                    </button>
                    <?php } ?>
                    <!-- //edited by amal on 08/08/2019 hide pdf-->
                    <?php if ($type != "Grosssalary" && $type != "Analysis" && $type != "GrosssalaryNew" && $type != "SalaryCombined" && $type != "GrossPeriod" && $type != "SummaryPayroll" && $type != "LOPREPORT" && $type != "MonthlyCTCReport" && $type != "Gross") { ?>
                        <button type="button" onclick="downloadReport('pdf');" id="btn-submit1" class="btn btn-danger">
                            <li class="fa fa-file-pdf-o"></li>
                        </button>
                    <?php } ?>
                    <?php if (false) { ?>
                        <button type="button" onclick="downloadReport('excel');" id="btn-submit2" class="btn btn-success">
                            <li class="fa fa-file-excel-o"></li>
                        </button>
                    <?php } ?>

                <?php //Edited by Akshay on 19-7-2023
                if (false) { ?>
                    <button type="button" onclick="downloadReport('csv');" id="btn-submit3" class="btn btn-danger">
                        <li>.csv</li>
                    </button>
                <?php } ?>
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
            if (selectany) {

            } else {
                alert("Please Choose Criteria items First");
                return false;
            }

            //Edited by Akshay on 6-10-2023
            var prevMonth = $("#prev_month").val();
            var nextMonth = $("#next_month").val();

            if (type === 'Analysis' && (prevMonth === '' || nextMonth === '')) {
                alert("Please Choose the required months");
                return false;
            }
            if (type === 'Analysis' && (prevMonth >= nextMonth)) {
                alert("Second selected month should be greater than first month");
                return false;
            }


            if (criteria) {
                $('#form-showreport').attr('action', livesite + 'SynthiteSalaryReports/generatereport/' + type + '/' + mode);
                $('#form-showreport').submit();
                loadCriteriaItems(1) //To load dropdown after downloading report
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
            allempfields.clearAndLoad("<?php echo $this->webroot; ?>SynthiteSalaryReports/listemployeefields", "json");

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
            //Edited by Akshay on 6-10-2023
            $("#prev_month").change(function() {
                var selectedValue = $(this).val();
                var secondDropdown = $("#next_month");

                if (selectedValue === "") {
                    // If no selection is made, hide the second dropdown
                    secondDropdown.hide();
                } else {
                    // If a selection is made, show the second dropdown
                    secondDropdown.show();
                }
            });

            $('#prev_month').datepicker({
                format: 'yyyy-mm',
                autoclose: true,
                startView: "months",
                minViewMode: "months"
            }).on('changeDate', function(selected) {
                var selectedDate = new Date(selected.date);
                selectedDate.setMonth(selectedDate.getMonth() + 1, 1);
                $('#next_month').datepicker('setStartDate', selectedDate);
            });

            $('#next_month').datepicker({
                format: 'yyyy-mm',
                autoclose: true,
                startView: "months",
                minViewMode: "months"
            });



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

        function viewReport() {
            var type = $('#hidden-report-type').val();
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

            //Edited by Akshay on 6-10-2023
            var prevMonth = $("#prev_month").val();
            var nextMonth = $("#next_month").val();

            if (type === 'Analysis' && (prevMonth === '' || nextMonth === '')) {
                alert("Please Choose the required months");
                return false;
            }
            if (type === 'Analysis' && (prevMonth >= nextMonth)) {
                alert("Second selected month should be greater than first month");
                return false;
            }


            $('#loaders').show();
            var type = $('#hidden-report-type').val();
            //var container = $("#largeModalForm1 #largeModalForm-content1");
            var container = $("#reportCon");
            var url = livesite + 'SynthiteSalaryReports/generatereport/' + type;

            var arrReportFieldsChosen = [];
            var reportfields;
            //        reportfields.forEachRow(function(id){
            //            arrReportFieldsChosen.push(id);
            //        });
            $('#hidden-reportfields').val(arrReportFieldsChosen.join(','));
            toggleItemsDisplay(1);
            $('body').addClass('sidebar-collapse');
            
            //Edited by Akshay on 25-10-2023
            if(type !== 'Grosssalary' && type !== 'Analysis' && type !== 'Salaryslipnew'){
                container.load(url, $('#form-showreport').serialize(), function() {
                $('#loaders').hide();
                //$("#largeModalForm1").modal('show')
                });
            }else{
                $.post(url, $('#form-showreport').serialize(), function(data) {
             // Replace the content of the container with the response data
                container.html(data);

                // Optionally, you can perform other actions here
                $('#loaders').hide();
                // Show your modal here if needed
                }).fail(function() {
                    // Handle errors here
                });
            }




        }

        function loadCriteriaItems(index) {
            var criteria = $('#select-criteria' + index).val();
            $('#hidden-criteria' + index).val(criteria);
            $('#div-items-criteria' + index).load(livesite + 'SynthiteSalaryReports/loadcriteriaitems/' + index + '/' + criteria);
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
            $('#div-criteria' + newIndex).load(livesite + 'SynthiteSalaryReports/addreportcriteria/' + type + '/' + newIndex + '/' + currentcriteriaschosen);

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

<style>
    /* td, th, table {
  border-style: none;
  border: none;
  } */
</style>