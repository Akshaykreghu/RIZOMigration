<section class="content-header">
    <h1>Edited Attendance Reports </h1>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- DIRECT CHAT DANGER -->
            <div class="box ">
                <div class="box-header with-border">
                    <div class="row">
                       
                        <div class="col-md-4" style="float: right;margin-right: 429px;margin-top: 20px;">
                            <select id="filterby_reporttype" name="filterby_reporttype" style="width: 240px;" class="form-control" onchange="changeReportType(this);" >
                                <?php
                                foreach ($arr_reporttypes as $key => $value) {
                                    echo '<option value="' . $key . '">' . $value . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                       
                      
                    </div>
                    <div class="row">
                        <div class="col-md-4" style="margin-top: -22px;">
                            <h3 class="box-title"> Criterias for <label id="current_reporttype"></label></h3>
                        </div>
                    </div>
                </div><!-- /.box-header -->
                <div class="box-body" style="margin-top: 40px;" id="div-reportcriterias">
                    
                </div><!-- /.box-body -->
            </div>
        </div>
    </div>
</section>
<script>
jQuery(document).ready(function() {
    $('#start_date').datepicker({
        format: 'yyyy-mm-dd'
    })
    $("#reportfrom").inputmask("yyyy-mm-dd");
    $('#end_date').datepicker({
        format: 'yyyy-mm-dd'
    })
    $("#reportto").inputmask("yyyy-mm-dd");
});
</script>
<script>
jQuery(document).ready(function() {
    //Load report criterias for default one
    var reporttype = $('#filterby_reporttype').val();
    var reportname = $("#filterby_reporttype option:selected").text();
    $('#current_reporttype').html(reportname);
    $('#div-reportcriterias').load(livesite+'EditedReports/changereporttype/'+reporttype);
});
function changeReportType(obj){
        var reporttype = $(obj).val();
        var reportname = $("#"+obj.id+" option:selected").text();
        $('#current_reporttype').html(reportname);
        $('#div-reportcriterias').load(livesite+'EditedReports/changereporttype/'+reporttype);
}
</script>