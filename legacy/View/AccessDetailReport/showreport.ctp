<?php if (isset($arr_reportcriterias)) { ?>
    <script>
        $(document).ready(function () {
            $('#date').hide();

            var v = $('#filterby_reporttype').val();
            if (v == 'ReportsAudit' || v == 'FormsAudit' || v == 'LoginAudit')
            {
                $('#date').show();
            }
            else
            {
                $('#date').hide();
            }

        })

    </script>
    <form class="form-horizontal" method="post" action="" id="form-showreport">
        <input type="hidden" id="hidden-report-type" name="hidden-report-type" value="<?php echo $type; ?>" />
        <input type="hidden" id="hidden-criterias-count" name="hidden-criterias-count" value="1" />
        <input type="hidden" id="hidden-reportfields" name="hidden-reportfields" value="" />
        <input type="hidden" id="hidden-report_component" name="hidden-report_component" value="" />
        <div class="form-group" id="div-criteria1">
            <div id="date">

                <?php if ($type == "ReportsAudit" || $type == "FormsAudit" || $type == "LoginAudit") { ?>
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
                        <select id="reportfrom" name="reportfrom"  class="form-control">


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
                <select id="select-criteria1" name="select-criteria1" style="width: 240px;" class="form-control" onchange="loadCriteriaItems(1);" >
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

        <div class="form-group">
            <div class="col-md-12" align="right">
                <button type="button" onclick="viewReport();" id="btn-submit" class="btn btn-primary"><li class="fa fa-eye"></li></button>
<!--                <button type="button" onclick="downloadReport('pdf');" id="btn-submit1" class="btn btn-danger"><li class="fa fa-file-pdf-o"></li></button>
                <button type="button" onclick="downloadReport('excel');" id="btn-submit2" class="btn btn-success"><li class="fa fa-file-excel-o"></li></button>-->
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
            $('.checkw').each(function () {
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
                $('#form-showreport').attr('action', livesite + 'AccessDetailReport/generatereport/' + type + '/' + mode);
                $('#form-showreport').submit();
            }
            else
            {
                alert("Please select a criteria first");
            }
        }
        
        function hasValue(elem) {
            return $(elem).filter(function () {
                return $(this).val();
            }).length > 0;
        }

        jQuery(document).ready(function () {
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

            $('#form-showreport').parsley();

        });
        function viewReport() {
            var type = $('#hidden-report-type').val();
            var criteria = $('#select-criteria1').val();
            if (criteria) {
                //var container = $("#largeModalForm #largeModalForm-content");  
                var container = $("#reportCon");
                var url = livesite + 'AccessDetailReport/generatereport/' + type;
                toggleItemsDisplay(1);
                $('body').addClass('sidebar-collapse');
                container.load(url, $('#form-showreport').serialize(), function () {
                    //$("#largeModalForm").modal('show')
                });
            }
            else
            {
                alert("Please select a criteria first");
            }
        }
        function loadCriteriaItems(index) {
            var criteria = $('#select-criteria' + index).val();
            $('#hidden-criteria' + index).val(criteria);
            $('#div-items-criteria' + index).load(livesite + 'AccessDetailReport/loadcriteriaitems/' + index + '/' + criteria);
        }
        function addOneReportCriteria(index) {
            var type = $('#hidden-report-type').val();

            var currentcriteriaschosen = '';
            $('#form-showreport .hidden-criterias').each(function () {
                currentcriteriaschosen += (currentcriteriaschosen == '') ? this.value : ',' + this.value;
            });
            $('#form-showreport .link-removecriterias').remove();
            var newIndex = index + 1;
            $('<div class="form-group" id="div-criteria' + newIndex + '"></div>').insertAfter($('#div-criteria' + index));
            $('#div-criteria' + newIndex).load(livesite + 'AccessDetailReport/addreportcriteria/' + type + '/' + newIndex + '/' + currentcriteriaschosen);

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