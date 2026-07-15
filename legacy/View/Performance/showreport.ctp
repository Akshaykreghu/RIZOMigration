<?php if (isset($arr_reportcriterias)) { ?>
    <form class="form-horizontal" method="post" action="" id="form-showreport">
        <input type="hidden" id="hidden-report-type" name="hidden-report-type" value="<?php echo $type; ?>" />
        <input type="hidden" id="hidden-criterias-count" name="hidden-criterias-count" value="1" />
        <input type="hidden" id="hidden-reportfields" name="hidden-reportfields" value="" />
        <input type="hidden" class="hidden-criterias" id="hidden-criteria1" name="hidden-criteria1" value="" />

        <!-- Criteria & Month Selection -->
        <div class="form-group row align-items-center" style="margin-bottom: 1rem; display: flex; flex-wrap: nowrap;">

            <!-- Year Label -->
            <label class="col-auto col-form-label pr-2"><b>Year:</b></label>

            <!-- Year Dropdown -->
            <div class="col-md-2 px-1">
                <select id="reportsfrom" name="reportsfrom" class="form-control">
                    <?php
                    // Edited by Akshay on 2-6-2025
                    foreach ($fin_years as $fin_year) {
                        $value = $fin_year['fin_year']['fin_year'];
                        $label = $fin_year[0]['year_range'];
                        echo "<option value=\"$value\">$label</option>";
                    }
                    // End
                    ?>
                </select>
                <!-- Edited by Akshay on 22-5-2025 -->
                <input type="hidden" name="reportsfrom_text" id="reportsfrom_text">
                <!-- End -->
            </div>

            <!-- Criteria Label -->
            <label for="select-criteria1" class="col-auto col-form-label pl-3 pr-2"><b>Criteria:</b></label>

            <!-- Criteria Dropdown -->
            <div class="col-md-3 px-1">
                <select id="select-criteria1" name="select-criteria1" class="form-control" onchange="loadCriteriaItems(1);">
                    <option value="">-- Choose criteria --</option>
                    <?php
                    foreach ($arr_reportcriterias as $value) {
                        echo '<option value="' . $value['reportcriteria'] . '">' . $value['reportcriteria_desc'] . '</option>';
                    }
                    ?>
                </select>
            </div>

            <!-- Employee listing (div-items-criteria1) -->
            <div class="col px-1" id="div-items-criteria1" style="min-width: 200px;"></div>

        </div>


        <!-- Employee-Specific Fields -->
        <?php if ($type == 'employee') { ?>
            <div class="row mt-2">
                <div class="col-sm-2"></div>
                <div class="col-sm-4">
                    <div id="allempfields" style="width:100%; height:270px; background-color:white;"></div>
                </div>
                <div class="col-sm-4">
                    <div id="reportfields" style="width:100%; height:270px; background-color:white;"></div>
                </div>
                <div class="col-sm-2"></div>
            </div>
        <?php } ?>

        <!-- Buttons -->
        <div class="form-group">
            <div class="col-md-12 text-right" style="margin-top:30px;">
                <!-- edited by athira on 09-06-2025 -->
                <?php if ($type == 'AnnualPerformance') { ?>
                    <button type="button" onclick="downloadReport('pdf');" class="btn btn-danger"><i class="fa fa-file-pdf-o"></i></button>
                <?php } else { ?>
                    <button type="button" onclick="viewReport();" class="btn btn-primary"><i class="fa fa-eye"></i></button>
                    <?php
                    if ($type != 'StaffAssessment' && $type != 'SelfAppraisal') { ?>
                        <button type="button" onclick="downloadReport('pdf');" class="btn btn-danger"><i class="fa fa-file-pdf-o"></i></button>
                    <?php }
                    ?>
                    <button type="button" onclick="downloadReport('excel');" class="btn btn-success"><i class="fa fa-file-excel-o"></i></button>
                <?php } ?>
                <!-- end -->
            </div>
        </div>

        <div id="reportCon" class="box box-body"></div>
    </form>

    <!-- JS Scripts -->
    <script>
        jQuery(document).ready(function() {
            $('#form-showreport').parsley();

            // Edited by Akshay on 22-5-2025
            var selectedText = $('#reportsfrom option:selected').text();

            // End

            <?php if ($type == 'employee') { ?>
                // DHTMLX Grid for Employee Fields
                var allempfields = new dhtmlXGridObject('allempfields');
                allempfields.setHeader("Fields");
                allempfields.setInitWidths("*");
                allempfields.setColAlign("left");
                allempfields.setColSorting("str");
                allempfields.setMultiLine(false);
                allempfields.selMultiRows = true;
                allempfields.enableDragAndDrop(true);
                allempfields.init();
                allempfields.clearAndLoad("<?php echo $this->webroot; ?>Performance/listemployeefields", "json");

                var reportfields = new dhtmlXGridObject('reportfields');
                reportfields.setHeader("Report Fields");
                reportfields.setInitWidths("*");
                reportfields.setColAlign("left");
                reportfields.setColSorting("str");
                reportfields.setMultiLine(false);
                reportfields.selMultiRows = true;
                reportfields.enableDragAndDrop(true);
                reportfields.init();
            <?php } ?>
        });

        // Load Criteria Items
        function loadCriteriaItems(index) {
            var criteria = $('#select-criteria' + index).val();
            $('#hidden-criteria' + index).val(criteria);
            $('#div-items-criteria' + index).load(livesite + 'Performance/loadcriteriaitems/' + index + '/' + criteria);
        }

        function viewReport() {
            $('#loaders').show();
            // Get the selected option's text and assign it to the hidden input
            var selectedText = $('#reportsfrom option:selected').text();
            $('#reportsfrom_text').val(selectedText);
            var type = $('#hidden-report-type').val();
            var criteria = $('#select-criteria1').val();
            var selected = $('.checkw:checked').length > 0;
            var container = $("#reportCon");
            var url = livesite + 'Performance/generatereport/' + type;

            $('#hidden-reportfields').val([]);

            if (!selected) {
                $('#loaders').hide();
                alert("Please choose criteria items first");
                return;
            }

            if (!criteria) {
                $('#loaders').hide();
                alert("Please select a criteria");
                return;
            }

            // Now it's safe to collapse sidebar
            $('body').addClass('sidebar-collapse');

            $.post(url, $('#form-showreport').serialize(), function(response) {
                    container.html(response);
                })
                .always(function() {
                    $('#loaders').hide();
                });
        }

        // Download Report
        function downloadReport(mode) {
            var type = $('#hidden-report-type').val();
            var criteria = $('#select-criteria1').val();
            var selected = $('.checkw:checked').length > 0;

            // Get the selected option's text and assign it to the hidden input
            var selectedText = $('#reportsfrom option:selected').text();
            $('#reportsfrom_text').val(selectedText);

            if (!selected) {
                alert("Please choose criteria items first");
                return;
            }
            if (!criteria) {
                alert("Please select a criteria");
                return;
            }

            $('#form-showreport').attr('action', livesite + 'Performance/generatereport/' + type + '/' + mode);
            $('#form-showreport').submit();
            loadCriteriaItems(1);
        }
    </script>
<?php } ?>