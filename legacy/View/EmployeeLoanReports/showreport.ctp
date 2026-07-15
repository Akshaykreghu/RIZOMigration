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


            <div class="col-md-1"><b>Month&nbsp;: </b></div>
            <div class="col-md-2">
                <?php if ($type == 'Loan') { ?>
                    <div class="col-md-12" style="padding-left: 4px; padding-right: 4px; ">
                        <div class="col-md-6" style="padding-left: 4px; padding-right: 4px; ">
                            <input type="text" id="reporstfrom" name="reportfrom" class="form-control pickerDate" value="<?php echo date("Y-m"); ?>" autocomplete="off" />
                        </div>
                        <div class="col-md-6" style="padding-left: 4px; padding-right: 4px; ">
                            <input type="text" id="reporstto" name="reportto" class="form-control pickerDate" value="<?php echo date("Y-m"); ?>" autocomplete="off" />
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="col-md-12" style="padding-left: 4px; padding-right: 4px; ">
                        <div class="col-md-6" style="padding-left: 4px; padding-right: 4px; ">
                            <input type="text" id="reporstfrom" name="reportfrom" class="form-control pickerDate" value="<?php echo date("d-m-Y"); ?>" readonly autocomplete="off" style="background-color: white" />
                        </div>
                        <div class="col-md-6" style="padding-left: 4px; padding-right: 4px; ">
                            <input type="text" id="reporstto" name="reportto" class="form-control pickerDate" value="<?php echo date("d-m-Y"); ?>" readonly autocomplete="off" style="background-color: white" />
                        </div>
                    </div>
                <?php } ?>
            </div>



            <input type="hidden" class="hidden-criterias" id="hidden-criteria1" name="hidden-criteria1" value="" />
            <div class="col-md-1"><b>Criteria&nbsp;:</b></div>
            <div class="col-md-2">
                <select id="select-criteria1" name="select-criteria1" style="width: 200px;" class="form-control" onchange="loadCriteriaItems(1);">
                    <option value="">--Choose criteria--</option>
                    <?php
                    foreach ($arr_reportcriterias as $key => $value) {
                        echo '<option value="' . $value['reportcriteria'] . '">' . $value['reportcriteria_desc'] . '</option>';
                    }
                    ?>
                </select>
            </div>
            <!--            <div class="col-md-4">

            </div>
            <div class="col-md-4">

            </div>-->
            <div class="col-md-4" id="div-items-criteria1">

            </div>
            <div class="form-group col-md-2">
                <div class="col-md-12" align="right">
                    <button type="button" onclick="viewReport();" id="btn-submit" class="btn btn-primary">
                        <li class="fa fa-eye"></li>
                    </button>
                    <!-- <button type="button" onclick="downloadReport('pdf');" id="btn-submit1" class="btn btn-danger">
                        <li class="fa fa-file-pdf-o"></li>
                    </button> -->
                    <button type="button" onclick="downloadReport('excel');" id="btn-submit2" class="btn btn-success">
                        <li class="fa fa-file-excel-o"></li>
                    </button>
                </div>
            </div>

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

        <!--        <div class="form-group">
                    <div class="col-md-12" align="right">
                        <button type="button" onclick="viewReport();" id="btn-submit" class="btn btn-primary"><li class="fa fa-eye"></li></button>
                        <button type="button" onclick="downloadReport('pdf');" id="btn-submit1" class="btn btn-danger"><li class="fa fa-file-pdf-o"></li></button>
                        <button type="button" onclick="downloadReport('excel');" id="btn-submit2" class="btn btn-success"><li class="fa fa-file-excel-o"></li></button>
                    </div>
                </div>-->
        <div id="reportCon" class="box box-body">

        </div>
    </form>
    <script>
        //Edited by Akshay on 7-8-2024
        function compareDates() {
            var reportFromDate = new Date($('#reporstfrom').val());
            var reportToDate = new Date($('#reporstto').val());
            console.log('reportFromDate', reportFromDate);
            console.log('reportToDate', reportToDate);

            if (reportFromDate > reportToDate) {
                return false;
            } else {
                return true;
            }
        }
        //End
        function downloadReport(mode) {
            //Edited by Akshay on 7-8-2024
            var dateComp = compareDates();
            if (dateComp) {
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
                    $('#form-showreport').attr('action', livesite + 'EmployeeLoanReports/generatereport/' + type + '/' + mode);
                    $('#form-showreport').submit();
                    loadCriteriaItems(1);
                } else {
                    alert("Please select a criteria first");
                }
            } else {
                alert("From date is not before to date");
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
            allempfields.clearAndLoad("<?php echo $this->webroot; ?>EmployeeLoanReports/listemployeefields", "json");

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
            /*$('#reportfrom').datepicker({
             format: 'yyyy-mm-dd'
             })
             $("#reportfrom").inputmask("yyyy-mm-dd");
             $('#reportto').datepicker({
             format: 'yyyy-mm-dd'
             })
             $("#reportto").inputmask("yyyy-mm-dd");*/

            //Edited by Akshay on 7-8-2024
            var type = $('#hidden-report-type').val();
            console.log('Type', type);

            if (type === 'Loan') {

                $('#reporstfrom').datepicker({
                    format: 'yyyy-mm',
                    autoclose: true,
                    minViewMode: 'months'
                }).on('changeDate', function(e) {
                    $('#reporstto').datepicker('setStartDate', e.date);
                    $('#reporstto').datepicker('setDate', e.date);
                });

                $('#reporstto').datepicker({
                    format: 'yyyy-mm',
                    autoclose: true,
                    minViewMode: 'months'
                }).on('changeDate', function(e) {
                    // $('#reporstfrom').datepicker('setEndDate', e.date);
                });

            } else {
                $('#reporstfrom').datepicker({
                    format: 'dd-mm-yyyy',
                    autoclose: true,
                    startView: "months",
                    minViewMode: "months"
                }).on('changeDate', function(e) {
                    var selected = $("#reporstfrom").val();
                    var sdt = new Date(selected);
                    var selectenddate = $("#reporstto").val();
                    var edt = new Date(selectenddate);
                    if (edt < sdt) {
                        alert("From date should be less than To date");
                        $("#reporstfrom").val('');
                    }
                });

                $('#reporstto').datepicker({
                    format: 'dd-mm-yyyy',
                    autoclose: true,
                    startView: "months",
                    minViewMode: "months"
                }).on('changeDate', function(e) {
                    var selected = $("#reporstto").val();
                    var edt = new Date(selected);
                    var selectsdate = $("#reporstfrom").val();
                    var sdt = new Date(selectsdate);
                    if (edt < sdt) {
                        alert("To date should be greater than From date");
                        $("#reporstto").val('');
                        return false;
                    }
                });
            }
            //End
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
            //Edited by Akshay on 7-8-2024
            var dateComp = compareDates();
            console.log('dateComp', dateComp);
            if (dateComp) {


                $('#loaders').show();
                var type = $('#hidden-report-type').val();
                //var container = $("#largeModalForm #largeModalForm-content");
                var container = $("#reportCon");
                var url = livesite + 'EmployeeLoanReports/generatereport/' + type;

                var arrReportFieldsChosen = [];
                var reportfields;
                //        reportfields.forEachRow(function(id){
                //            arrReportFieldsChosen.push(id);
                //        });
                toggleItemsDisplay(1);
                $('body').addClass('sidebar-collapse');
                $('#hidden-reportfields').val(arrReportFieldsChosen.join(','));

                //Edited by Akshay on 6-8-2024
                $.post(url, $('#form-showreport').serialize(), function(response) {
                    container.html(response);
                    $('#loaders').hide();
                });

                //End
            } else {
                alert("From date is not before to date");
            }
        }

        function loadCriteriaItems(index) {
            var criteria = $('#select-criteria' + index).val();
            var type = $('#hidden-report-type').val(); //Here i added the type to the url. This is required for showing criteria of detailed report.That is, Single employee can only show in result.By ***ARUL P DAS on 23/1/2020
            $('#hidden-criteria' + index).val(criteria);
            $('#div-items-criteria' + index).load(livesite + 'EmployeeLoanReports/loadcriteriaitems/' + index + '/' + criteria + '/' + type);
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
            $('#div-criteria' + newIndex).load(livesite + 'EmployeeLoanReports/addreportcriteria/' + type + '/' + newIndex + '/' + currentcriteriaschosen);

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