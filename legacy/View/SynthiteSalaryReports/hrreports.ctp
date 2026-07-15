<section class="content-header">
    <h1> SynthiteSalary </h1>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- DIRECT CHAT DANGER -->
            <div class="box ">
                <div class="box-header with-border">
                    <div class="row">
                         <div class="col-md-3 col-lg-2" style="font-size: medium;font-weight: 600;margin-bottom: -22px;margin-top: -2px;" > <span>Choose Report</div>
                        <div class="col-md-6 col-lg-3" style="float: right;margin-right: 60%;margin-top: -2px;">
                            <select id="filterby_reporttype" style="width: 240px;" name="filterby_reporttype" class="form-control" onchange="changeReportType(this);" >
                                <?php
                                foreach ($arr_reporttypes as $key => $value) {
                                    echo '<option value="' . $key . '">' . $value . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                      
                      
                    </div>
               
                </div>
                 <div class="row">
                        <div class="col-md-6" style="margin-top: -2px;margin-left: 8px;">
                            <h3 class="box-title"> Criterias for <label id="current_reporttype"></label></h3>
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
$(document).ready(function() {
    //Load report criterias for default one
    var reporttype = $('#filterby_reporttype').val();
    var reportname = $("#filterby_reporttype option:selected").text();
    $('#current_reporttype').html(reportname);
    $('#div-reportcriterias').load(livesite+'SynthiteSalaryReports/changereporttype/'+reporttype);
});
function changeReportType(obj){
        var reporttype = $(obj).val();
        var reportname = $("#"+obj.id+" option:selected").text();
        $('#current_reporttype').html(reportname);
        $('#div-reportcriterias').load(livesite+'SynthiteSalaryReports/changereporttype/'+reporttype);
}
</script>