<section class="content-header">
    <h1 class="text-primary-18"> Expense Reports </h1>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- DIRECT CHAT DANGER -->
            <div class="box ">
                <div class="box-header with-border row">
                    <div class="col-md-4" style="font-size: medium;font-weight: 600;margin-bottom: -22px;margin-top: 17px;" > <span>Choose Report Type &nbsp; : </span></div>
                    <div class="col-md-2" style="margin-top: 12px;">

                        <select id="filterby_reporttype" name="filterby_reporttype" style="margin-left:-145px ; width:350px;" class="form-control" onchange="changeReportType(this);" >
                            <?php
                            foreach ($arr_reporttypes as $key => $value) {
                                echo '<option value="' . $key . '">' . $value . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                </div>
                <div class="row">
                    <div class="col-md-4" style="margin-top: -2px;margin-left: 8px;">
                        <h3 class="box-title"> Criterias for <label id="current_reporttype"></label></h3>
                    </div>
                    <!--<div class="col-md-5">&nbsp;</div>-->
                    <div class="col-md-2" style="margin-top: 12px;">
                        <select id="report_component" name="report_component" style="margin-left:-150px ; width:350px;" class="form-control" onchange="changeReportComponent(this);">
                            <option value="">--Choose Type--</option>
                            <option value="cont">Register Of Contractors (CLRA)</option> 
                        </select>
                    </div>
                </div>
                <hr style="margin-top: 2px;margin-bottom: -30px;"><!-- /.box-header -->
                <div class="box-body" style="margin-top: 40px;" id="div-reportcriterias">

                </div><!-- /.box-body -->
            </div>
        </div>
    </div>
</section>
<script>
    jQuery(document).ready(function () {
        $("#report_component").hide();
        //Load report criterias for default one
        var reporttype = $('#filterby_reporttype').val();
        var reportname = $("#filterby_reporttype option:selected").text();
        $('#current_reporttype').html(reportname);
        $('#div-reportcriterias').load(livesite + 'ExpenseReport/changereporttype/' + reporttype);
    });
    function changeReportType(obj) {
        var reporttype = $(obj).val();
        if (reporttype == "statutory") {
            $("#report_component").show();
        } else {
            $("#report_component").hide();
        }
        var reportname = $("#" + obj.id + " option:selected").text();
        $('#current_reporttype').html(reportname);
        $('#div-reportcriterias').load(livesite + 'ExpenseReport/changereporttype/' + reporttype);
    }
    function changeReportComponent(obj) {
        $("#hidden-report_component").val($(obj).val());
    }
</script>