<?php if (isset($arr_reportcriterias)) { ?>
    <script>
    jQuery(document).ready(function() {
            $('#reporstto').on("change", function () {
                var date_start = new Date($('#reporstfrom').val());
                var date_to = new Date($(this).val());
                if (date_to < date_start) {
                    alert("Date Should be Valid ");
                }
                return false;
            });
    });
    </script>
    <form class="form-horizontal" method="post" action="" id="form-showreport">
        <input type="hidden" id="hidden-report-type" name="hidden-report-type" value="<?php echo $type; ?>" />
        <input type="hidden" id="hidden-criterias-count" name="hidden-criterias-count" value="1" />
        <input type="hidden" id="hidden-reportfields" name="hidden-reportfields" value="" />
        <div class="form-group" id="div-criteria1">
            <?php if ($type == "ProfTax") { ?>
             <div class="col-md-1"><b>Time Period&nbsp;:</b></div>
             <div class="col-md-1">
              <select id="year" name="year"  class="form-control" >
                <?php foreach ($fin_year as $fin){ ?>
                    <option value="<?php echo $fin['fin_year']['fin_year']; ?>"> <?php echo $fin['fin_year']['fin_year']; ?></option> 
                <?php } ?>
              </select>
             </div>
             <div class="col-md-2">
              <select id="report_from" name="report_from"  class="form-control" >
                    <option value="1"> First Half Year</option> 
                    <option value="2"> Second Half Year</option>
              </select>
             </div>
             <?php } else if (($type== "Labour" || $type=="TDS" || $type!="TAX") && $type!= "Variable" && $type!="ESI_NEW" && $type != "Fixed" ){ ?>
         <div class="col-md-4">
                   <div class="col-md-2"><b>From&nbsp;:</b></div>
                   <div class="col-md-4">
                    <select id="reportsfrom" name="reportsfrom" class="form-control">
                    <?php
                    $start_month = strtotime(date('Y-m', strtotime("+1 month", strtotime(date('Y-m')))));
                    for ($i = 0; $i < 20; $i++) {
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
                <div class="col-md-1"><b>To&nbsp;:</b></div>
                <div class="col-md-4">
                <select id="reportsto" name="reportsto" class="form-control">
                    <?php
                   $start_month = strtotime(date('Y-m', strtotime("+1 month", strtotime(date('Y-m')))));
                   for ($i = 0; $i < 20; $i++) {
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
             <!-- Edited by Akshay on 27-5-2024 -->
             <?php } elseif($type == "TAX" || $type == "Variable" || $type == "ESI_NEW" || $type == "ESI" || $type == "PF" || $type == "Fixed"){?>
            <!-- End -->  
                <div class="col-md-1"><b>Month:</b></div>
            
            <div class="col-md-2">

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
                </div>
                <?php } ?>



            <input type="hidden" class="hidden-criterias" id="hidden-criteria1" name="hidden-criteria1" value="" />
            <div class="col-md-1"><b>Criteria:</b></div>
            <!--             //edited by megha on 11_06_19 added tds and pt starts-->
          
 

            <div class="col-md-2">
                <!-- //edited by megha on 11_06_19 added tds and pt ends-->
                <select id="select-criteria1" name="select-criteria1" style="width: 240px;" class="form-control" onchange="loadCriteriaItems(1);" >
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
        </div>


        <div class="form-group" id="div-criteria1">

        </div>

        <?php if ($type == 'employee') { ?>
            <div class="row" style="padding-left: 90px">
                <div class="col-sm-2">
                </div>
                <div class="col-sm-4">	<div id="allempfields" style="width:100%; height:270px; background-color:white;"></div>
                </div>
                <div class="col-sm-4">	<div id="reportfields" style="width:100%; height:270px; background-color:white;"></div>
                </div>
                <div class="col-sm-2">
                </div>
            </div>
        <?php } ?>

        <div class="form-group">
            <div class="col-md-12" align="right">
                 <?php if($type != "ESI_NEW" ){?>
                <button type="button" onclick="viewReport();" id="btn-submit" class="btn btn-primary"><li class="fa fa-eye"></li></button> <?php }?>
                <!-- <button type="button" onclick="downloadReport('pdf');" id="btn-submit1" class="btn btn-danger"><li class="fa fa-file-pdf-o"></li></button> -->
                <?php if($type != "ESI_NEW" ){?>
                <button type="button" onclick="downloadReport('excel');" id="btn-submit2" class="btn btn-success"><li class="fa fa-file-excel-o"></li></button><?php }?>
            </div>
        </div>
        <div id="reportCon" class="box box-body">
        
        </div>
    </form>
    <script>
        function loadCriteriaItems(index) {
            var criteria = $('#select-criteria' + index).val();
            $('#hidden-criteria' + index).val(criteria);
            $('#div-items-criteria' + index).load(livesite + 'VariableReport/loadcriteriaitems/' + index + '/' + criteria);
        }
        function downloadReport(mode){
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
            alert("Please choose criteria items first");
            return false;
        }
	if(criteria){
        $('#form-showreport').attr('action',livesite+'VariableReport/generatereport/'+type+'/'+mode);
        $('#form-showreport').submit();
        loadCriteriaItems(1);
	}
	else
	{
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
            allempfields.attachEvent("onDrag", function (sId, tId, sObj, tObj, sInd, tInd) {
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
            allempfields.clearAndLoad("<?php echo $this->webroot; ?>VariableReport/listemployeefields", "json");

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
            reportfields.attachEvent("onDrag", function (sId, tId, sObj, tObj, sInd, tInd) {
                /*var currentReportFields = $('#hidden-reportfields').val().split(',').filter(function(v){return v!==''});
                 var arrSelectedFields = sId.split(',');
                 var newReportFields = $.unique($.merge(arrSelectedFields, currentReportFields));
                 $('#hidden-reportfields').val(newReportFields.join(','));*/
                return true;
            });
    <?php } ?>
        jQuery(document).ready(function () {
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
        // function viewReport() {
           
        //     var type = $('#hidden-report-type').val();
        //      //edited by megha on 12_6_19 tax, pt
        //     var report_component = $('#report_component').val();
            
        //     //alert(report_component);
            
        //      if (type == "Labour"){
        //          if(report_component == ''){
        //              alert("Please select Report Type.");
        //          }
        //      } 
             //end
           //  function viewReport() {
//            var selectany = false;
//            $('.checkw').each(function(){
//               if($(this).prop('checked') == true){
//                      selectany = true;
//                    }
//                 });
//    //        return false;
//            if(selectany){
//                $('#loaders').show();
//                 var type = $('#hidden-report-type').val();
//	var criteria = $('#select-criteria1').val();
//	if(criteria){
//        //var container = $("#largeModalForm #largeModalForm-content");  
//        var container = $("#reportCon");
//        var url = livesite+'VariableReport/generatereport/'+type;
//        toggleItemsDisplay(1);
//        $('body').addClass('sidebar-collapse');
//	container.load(url,$('#form-showreport').serialize(), function() {
//            //$("#largeModalForm").modal('show')
//        });
//	}
//	else
//	{
//	alert("Please select a criteria first");
//	}
//             $('#loaders').hide();
//              
//            }else{
//                 alert("Please choose criteria items first");
//                return false;
//            }
//              $('#loaders').show();
//            //var container = $("#largeModalForm #largeModalForm-content");
//            var container = $("#reportCon");
//            var url = livesite + 'VariableReport/generatereport/' + type;
//
//            var arrReportFieldsChosen = [];
//            var reportfields;
//    //        reportfields.forEachRow(function(id){
//    //            arrReportFieldsChosen.push(id);
//    //        });
//            $('#hidden-reportfields').val(arrReportFieldsChosen.join(','));
//            toggleItemsDisplay(1);
//            $('body').addClass('sidebar-collapse');
//            container.load(url, $('#form-showreport').serialize(), function () {
//                $('#loaders').hide();
//                //$("#largeModalForm").modal('show')
//            });
//        }
 function viewReport() {
            var selectany = false;
            $('.checkw').each(function(){
               if($(this).prop('checked') == true){
                      selectany = true;
                    }
                 });
            if(selectany){
                $('#loaders').show();
                 var type = $('#hidden-report-type').val();
	var criteria = $('#select-criteria1').val();
	if(criteria){
        var url = livesite+'VariableReport/generatereport/'+type;
        toggleItemsDisplay(1);
        $('body').addClass('sidebar-collapse');
        $.post(url, $('#form-showreport').serialize(), function(data) {
           $('#reportCon').html(data);
         });
	}
	else
	{
	alert("Please select a criteria first");
	}
             $('#loaders').hide();
              
            }else{
                 alert("Please choose criteria items first");
                return false;
            }
        }
        function loadCriteriaItems(index) {
            var criteria = $('#select-criteria' + index).val();
            $('#hidden-criteria' + index).val(criteria);
            $('#div-items-criteria' + index).load(livesite + 'VariableReport/loadcriteriaitems/' + index + '/' + criteria);
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
            $('#div-criteria' + newIndex).load(livesite + 'VariableReport/addreportcriteria/' + type + '/' + newIndex + '/' + currentcriteriaschosen);

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