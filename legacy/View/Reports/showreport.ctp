<?php if (isset($arr_reportcriterias)) { ?>
    <form class="form-horizontal" method="post" action="" id="form-showreport">
        <input type="hidden" id="hidden-report-type" name="hidden-report-type" value="<?php echo $type; ?>" />
        <input type="hidden" id="hidden-criterias-count" name="hidden-criterias-count" value="1" />
        <input type="hidden" id="hidden-reportfields" name="hidden-reportfields" value="" />
        <div class="form-group" id="div-criteria1">

            <div>
                <?php if ($type == 'holiday') { ?>

                    <div class="col-md-1"><b>Year</b></div>
                    <div class="col-md-2">



                        <div class="col-md-12" style="padding-left: 4px; padding-right: 4px; ">

                          <!-- edited by athira on 21-06-2025 -->
                            <select id="reportfrom" name="reportfrom" class="form-control">
                                <?php
                                    $startYear = 2019;
                                    $currentYear = date('Y');
                                    for ($year = $currentYear; $year >= $startYear; $year--) {
                                        $value = $year . '-01'; // format like 2022-01
                                        echo "<option value=\"$value\">$year</option>";
                                    }
                                    ?>
                            </select>
                            
                            <!-- end -->
                            <!-- <select id="reportfrom" name="reportfrom" class="form-control">
                                <option value="2022-01">2022</option>
                                <option value="2021-01">2021</option>
                                <option value="2020-01">2020</option>
                                <option value="2019-01">2019</option> -->
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
                            <!-- </select> -->

                        </div>

                    </div>
                <?php } ?>
            </div>


            <input type="hidden" class="hidden-criterias" id="hidden-criteria1" name="hidden-criteria1" value="" />
            <div class="col-md-3" style="font-weight: bold; text-align: right">Criteria <?php
                                                                                        if ($type == 'employee') {
                                                                                            echo "1";
                                                                                        }
                                                                                        ?> :</div>

            <div class="col-md-4">
                <select id="select-criteria1" name="select-criteria1" class="form-control" onchange="loadCriteriaItems(1);">
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
            <div>

            </div>
            <div class="col-md-1">
                <?php if ($type == 'employee') { ?>
                    <a onclick="addOneReportCriteria(1);"><i class="fa fa-plus-circle"></i></a>
                <?php } ?>
                <!--a onclick="removeThisCriteria(1);"><i class="fa fa-minus-circle"></i></a-->



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
                <button type="button" onclick="viewReport();" id="btn-submit" class="btn btn-primary">
                    <li class="fa fa-eye"></li>
                </button>
                <!--<button type="button" onclick="downloadReport('pdf');" id="btn-submit1" class="btn btn-danger"><li class="fa fa-file-pdf-o"></li></button>-->
                <!--edited by megha on 25_07_19 pdf view removed for employee report-->



                <?php if ($type !== 'employee' && $type !== 'shiftpolicy' && $type !== 'leavepolicy' && $type !== 'holiday' && $type != 'salarystructures') { ?>
                    <button type="button" onclick="downloadReport('pdf');" id="btn-submit1" class="btn btn-danger">
                        <li class="fa fa-file-pdf-o"></li>
                    </button>
                <?php
                } else {
                }
                ?>
                <button type="button" onclick="downloadReport('excel');" id="btn-submit2" class="btn btn-success">
                    <li class="fa fa-file-excel-o"></li>
                </button>
            </div>
        </div>
        <div id="reportCon" class="box box-body">

        </div>
    </form>
    <script>
        function downloadReport(mode) {
            var type = $('#hidden-report-type').val();
            var criteria = $('#select-criteria1').val();

            if (criteria != "EmployeeProfessionalDetails") {
                if ($('.checkw').length) {
                    if ($('.checkw').is(':checked')) {

                    } else {
                        alert('Please Choose Criteria items First');
                        return false;
                    }
                } else {
                    alert('Please select a criteria first');
                    return false;
                }
            } else {
                if ($('#reportfrom').val() == "" || $("#reportto").val() == "") {
                    alert('Please choose date');
                    return false;
                }
            }
            var selectany = false;
            var arrReportFieldsChosen = [];
            <?php
            if ($type == 'employee') {
            ?>
                if (reportfields.getRowsNum() < 1) {
                    alert('Please choose any fields');
                    return false;
                }
            <?php
            }
            ?>
            reportfields.forEachRow(function(id) {
                arrReportFieldsChosen.push(id);
            });

            $('#hidden-reportfields').val(arrReportFieldsChosen.join(','));
            $('.checkw').each(function() {
                if ($(this).prop('checked') == true) {
                    selectany = true;
                }
            });
            //        return false;
            if (selectany) {

            } else {
                //            alert("Please Choose Criteria items First");
                //            return false;
            }
            if (criteria) {
                $('#form-showreport').attr('action', livesite + 'Reports/generatereport/' + type + '/' + mode);
                $('#form-showreport').attr('method', 'POST');
                $('#form-showreport').submit();
                loadCriteriaItems(1) //To load dropdown after downloading report
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
            allempfields.clearAndLoad("<?php echo $this->webroot; ?>Reports/listemployeefields", "json");

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
            //added by megha loaders on 10/08/2019
            // $('#loaders').show();
            var type = $('#hidden-report-type').val();
            var container = $("#reportCon");
            var url = livesite + 'Reports/generatereport/' + type;

            //Edited by Akshay on 16-11-2023
            var criteria = $('#select-criteria1').val();
            console.log('Criteria', criteria);
            if (criteria != "EmployeeProfessionalDetails") {
                if ($('.checkw').length) {
                    if ($('.checkw').is(':checked')) {

                    } else {
                        alert('Please Choose Criteria items First');
                        return false;
                    }
                } else {
                    alert('Please select a criteria first');
                    return false;
                }

            } else {
                if ($('#reportfrom').val() == "" || $("#reportto").val() == "") {
                    alert('Please choose date');
                    return false;
                } else if ($('#reportfrom').val() > $("#reportto").val()) {
                    alert('Start date should be less than end date');
                    $('#reportfrom').val('');
                    $('#reportto').val('');
                    return false;
                }
            }

            <?php
            if ($type == 'employee') {
            ?>
                if (reportfields.getRowsNum() < 1) {
                    alert('Please choose any fields');
                    return false;
                }
            <?php
            }
            ?>

            var arrReportFieldsChosen = [];
            reportfields.forEachRow(function(id) {
                arrReportFieldsChosen.push(id);
            });
            $('#hidden-reportfields').val(arrReportFieldsChosen.join(','));
            //        toggleItemsDisplays(1);//Hided by **ARUL P DAS on 2/1/2020
            $('body').addClass('sidebar-collapse');


            if (criteria) {
                //Edited by Akshay on 18-11-2023
                $.post(url, $('#form-showreport').serialize(), function(data) {
                    // Replace the content of the container with the response data
                    container.html(data);

                    // Optionally, you can perform other actions here
                    $('#loaders').hide();
                    // Show your modal here if needed
                }).fail(function() {
                    // Handle errors here
                });
            } else {
                alert("Please select a criteria first");
            }

            $("body, html").animate({
                scrollTop: $("#reportCon").offset().top
            }, 600);
        }

        function toggleItemsDisplays(index) {
            $('#div-items-criteria' + index + ' .panel-body').fadeToggle('slow', function() {
                if ($(this).is(":visible")) {
                    $('#div-items-criteria' + index + ' a .fa').removeClass('fa-plus');
                    $('#div-items-criteria' + index + ' a .fa').addClass('fa-minus');
                } else {
                    $('#div-items-criteria' + index + ' a .fa').removeClass('fa-minus')
                    $('#div-items-criteria' + index + ' a .fa').addClass('fa-plus');
                }
            });
        }

        function loadCriteriaItems(index) {

            //The below forloop works to remove all after forming criterias when change occure in current index criteria.By ***ARUL P DAS on 2/3/2020
            for (var i = index + 1; i <= 5; i++) {
                if ($("#div-criteria" + i).length) {
                    $("#div-criteria" + i).html('');
                    $("#div-criteria" + i).remove();
                }
            }
            //criteria removal ends here
            //This is to display if the indexed criteria already exists(That should early hided). by ***ARUL P DAS on 3/3/2020
            if ($('#div-criteria' + index + ' .col-md-1').is(":hidden")) {
                $('#div-criteria' + index + ' .col-md-1').show();
            }
            var criteria = $('#select-criteria' + index).val();
            $('#hidden-criteria' + index).val(criteria);
            $('#div-items-criteria' + index).load(livesite + 'Reports/loadcriteriaitems/' + index + '/' + criteria);
        }

        function addOneReportCriteria(index) {
            //This is to check whether criteria select or not.If not check, there will be an warning message.By ***ARUL P DAS on 2_3_2020
            if ($('#select-criteria' + index).val() == "") {
                alert('Please select any criteria');
                return false;
            }
            //ENDS

            $('#div-criteria' + index + ' .col-md-1').hide();

            var type = $('#hidden-report-type').val();

            var currentcriteriaschosen = '';
            $('#form-showreport .hidden-criterias').each(function() {
                currentcriteriaschosen += (currentcriteriaschosen == '') ? this.value : ',' + this.value;
            });
            //            $('#form-showreport .link-removecriterias').remove();
            var newIndex = index + 1;
            $('<div class="form-group" id="div-criteria' + newIndex + '"></div>').insertAfter($('#div-criteria' + index));
            $('#div-criteria' + newIndex).load(livesite + 'Reports/addreportcriteria/' + type + '/' + newIndex + '/' + currentcriteriaschosen);

            var count = $('#hidden-criterias-count').val();
            if (count >= 1) {
                $('#hidden-criterias-count').val(parseInt(count) + 1);
            }
        }

        function removeThisCriteria(index) {
            var count = $('#hidden-criterias-count').val();
            $('#hidden-criterias-count').val(parseInt(count) - 1);
            $('#div-criteria' + index).remove();
            $('#div-criteria' + (index - 1) + ' .col-md-1').show(); //This is to show previous criterias plus button. by ***ARUL P DAS on 3/3/2020
        }
    </script>
<?php } ?>