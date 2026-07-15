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


            <div class="col-md-1"><b>Month &nbsp;:</b></div>
            
            <div class="col-md-2">
                <?php if ($type == "Client") { ?>
                <select id="reportfrom" name="reportfrom"  class="form-control">
                   <?php $start_month = strtotime(date('Y-m', strtotime("+1 month", strtotime(date('Y-m')))));
                             for ($i = 0; $i < 18; $i++) {
                                $month = date('Y-m', strtotime("-$i month", $start_month));
                                if ($month == date('Y-m')) {
                                    echo '<option selected="selected" value="' . $month . '">' . date('M-Y', strtotime("-$i month", $start_month)) . '</option>';
                                } else {
                                    echo '<option value="' . $month . '">' . date('M-Y', strtotime("-$i month", $start_month)) . '</option>';
                                } } ?>
                        </select>
                <?php } else{ ?>
                <div class="col-md-12" style="padding-left: 4px; padding-right: 4px; ">
                    <div class="col-md-6" style="padding-left: 4px; padding-right: 4px; ">
                        <input type="text" id="reporstfrom" name="reportfrom" class="form-control pickerDate" value="<?php echo date("Y-m-d"); ?>" />
                    </div>
                    <div class="col-md-6" style="padding-left: 4px; padding-right: 4px; ">
                        <input type="text" id="reporstto" name="reportto" class="form-control pickerDate" value="<?php echo date("Y-m-d"); ?>" />
                    </div>
                </div>
                <?php } ?>
            </div>




            <input type="hidden" class="hidden-criterias" id="hidden-criteria1" name="hidden-criteria1" value="" />
            <div class="col-md-1"><b>Criteria &nbsp;:</b></div>
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
            <!--        <div class="col-md-1">
                        <a onclick="addOneReportCriteria(1);"><i class="fa fa-plus-circle"></i></a>
                        a onclick="removeThisCriteria(1);"><i class="fa fa-minus-circle"></i></a
                    </div>-->


        </div>



        <div class="form-group" id="div-criteria1">

        </div>

        <?php // if ($type == 'AttendanceLocation') { ?>
<!--            <div class="row">
                <div class="col-sm-2">
                </div>
                <div class="col-sm-4">	<div id="allempfields" style="width:100%; height:270px; background-color:white;"></div>
                </div>
                <div class="col-sm-4">	<div id="reportfields" style="width:100%; height:270px; background-color:white;"></div>
                </div>
                <div class="col-sm-2">
                </div>
            </div>-->
        <?php // } ?>
         
        <div id="printPanel" class="form-group">
            <div class="col-md-12" align="right" >
                <!-- <button type="button" onclick="viewReport();" id="btn-submit" class="btn btn-primary"><li class="fa fa-eye"></li></button> -->
<!--                //edited by amal on 08/08/2019 hide pdf-->
                 <?php  if ($type != "AttendanceLocation" && $type != "Client" && $type !="Customer" && $type !="Customervisit") { ?>
                <!-- <button type="button" onclick="downloadReport('pdf');" id="btn-submit1" class="btn btn-danger"><li class="fa fa-file-pdf-o"></li></button> -->
              <?php  } ?>  
                <!-- <button type="button" onclick="downloadReport('excel');" id="btn-submit2" class="btn btn-success"><li class="fa fa-file-excel-o"></li></button> -->

                <button type="button" onclick="handleViewClick();" id="btn-submit" class="btn btn-primary">
    <li class="fa fa-eye"></li>
</button>

<button type="button" onclick="handleExcelClick();" id="btn-submit2" class="btn btn-success">
    <li class="fa fa-file-excel-o"></li>
</button>
              
            </div>
        </div>
         
        <div  class="col-md-12" style="">
            <div class="" id="reportCon">
            </div>
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
            $('#div-items-criteria' + index).load(livesite + 'TrackingReports/loadcriteriaitems/' + index + '/' + criteria);
        }
        function downloadReport(mode) {
            var type = $('#hidden-report-type').val();
            var criteria = $('#select-criteria1').val();
            var selectany = false;
            $('.checkw').each(function(){
               if($(this).prop('checked') == true){
                      selectany = true;
                    }
                 });
    //        return false;
            if(selectany){
                
            }else{
                alert("Please Choose Criteria items First");
                return false;
            }
            if (criteria) {
                $('#form-showreport').attr('action', livesite + 'TrackingReports/generatereport/' + type + '/' + mode);
                $('#form-showreport').submit();
                loadCriteriaItems(1);
            } else
            {
                alert("Please select a criteria first");
            }





        }
    
        jQuery(document).ready(function () {
            

//            $('input[name="reportfrom"]').daterangepicker();
$('#reporstfrom').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                // startView: "months",
                // minViewMode: "months"
            }).on('changeDate', function (e) {
                var selected = $("#reporstfrom").val();
                var sdt = new Date(selected);
                var selectenddate = $("#reporstto").val();
                var edt = new Date(selectenddate);
                if (edt < sdt)
                {
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
            }).on('changeDate', function (e) {
                var selected = $("#reporstto").val();
                var edt = new Date(selected);
                var selectsdate = $("#reporstfrom").val();
                var sdt = new Date(selectsdate);
                if (edt < sdt)
                {
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
            
//            $('#reporstfrom').datepicker({
//                format: 'yyyy-mm-dd',
//                autoclose: true,
//
//            });
//            
//            $('#reporstto').datepicker({
//                format: 'yyyy-mm-dd',
//                autoclose: true,
//
//            });
            
            
            $('#form-showreport').parsley();

            
        });
        function viewReport() {
            var selectany = false;
            $('.checkw').each(function(){
               if($(this).prop('checked') == true){
                      selectany = true;
                    }
                 });
    //        return false;
            if(selectany){
                
            }else{
                alert("Please Choose Criteria items First");
                return false;
            }
            $('#loaders').show();
            var type = $('#hidden-report-type').val();
            //var container = $("#largeModalForm1 #largeModalForm-content1");
            var container = $("#reportCon");
            var url = livesite + 'TrackingReports/generatereport/' + type;

            var arrReportFieldsChosen = [];
            var reportfields;
    //        reportfields.forEachRow(function(id){
    //            arrReportFieldsChosen.push(id);
    //        });
            $('#hidden-reportfields').val(arrReportFieldsChosen.join(','));
            toggleItemsDisplay(1);
            $('body').addClass('sidebar-collapse');
            container.load(url, $('#form-showreport').serialize(), function () {
                $('#loaders').hide();
                //$("#largeModalForm1").modal('show')
            });
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
            $('#div-criteria' + newIndex).load(livesite + 'TrackingReports/addreportcriteria/' + type + '/' + newIndex + '/' + currentcriteriaschosen);

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

<script>
    var reportType = $('#hidden-report-type').val();

function isRestrictedTime() {
    var now = new Date();
    var minutes = now.getHours() * 60 + now.getMinutes();

    var morningStart = 8 * 60;       // 8:00
    var morningEnd = 10 * 60 + 30;   // 10:30

    var eveningStart = 16 * 60;      // 4:00
    var eveningEnd = 18 * 60;        // 6:00

    return (
        (minutes >= morningStart && minutes <= morningEnd) ||
        (minutes >= eveningStart && minutes <= eveningEnd)
    );
}

function showRestrictionMessage() {
    alert("Customer Visit reports are disabled between 8:00–10:30 AM and 4:00–6:00 PM.");
}

// 👁 View button handler
function handleViewClick() {
    if (reportType === "Customervisit" && isRestrictedTime()) {
        showRestrictionMessage();
        return false;
    }
    viewReport();
}

// 📊 Excel button handler
function handleExcelClick() {
    if (reportType === "Customervisit" && isRestrictedTime()) {
        showRestrictionMessage();
        return false;
    }
    downloadReport('excel');
}
 jQuery(document).ready(function() {
        if (reportType === "Customervisit" && isRestrictedTime()) {
    var msg = "Report is available between 10:30 AM to 04:00 PM";

    $('#btn-submit')
        .attr('title', msg)
        .css({ 'cursor': 'not-allowed', 'opacity': '0.6' });

    $('#btn-submit2')
        .attr('title', msg)
        .css({ 'cursor': 'not-allowed', 'opacity': '0.6' });
}
        
    });
</script>