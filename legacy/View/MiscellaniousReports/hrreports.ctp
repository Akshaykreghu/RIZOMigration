<style>
    /* edited by bindu 20-11-2025 */
      .heading {
        display: flex;
        flex-direction: row;
        align-items:end;
        justify-content: space-between;
        /* margin-left: 20px; */
    }

    .home {
        background-color: #ffffffff;
        border-radius: 50px;
        padding: 2px 15px;
        color: #1e516e !important;
        /* margin-right: 15px; */
        color: white;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        border: #1e516e 1px solid;
    }
     /* edited by bindu 20-11-2025 end */
</style>
<section class="content-header heading">
    <!-- edited by athira on 03-07-2025 -->
    <h1 class="text-primary-18"> Leave Reports </h1>
    <!-- end -->
     <!-- /* edited by bindu 20-11-2025 */ -->
      <?php if ($plan !='basic'){?>
     <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
        <i class="fa" style="font-size:16px;">&#xf104;</i>
        Back
    </div>
    <?php } ?>
     <!-- /* edited by bindu 20-11-2025 */ -->
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- DIRECT CHAT DANGER -->
            <div class="box ">
                <div class="box-header with-border">
                    <div class="row">
                           <div class="col-md-4" style="font-size: medium;font-weight: 600;margin-bottom: -22px;margin-top: 17px;" > <span>Choose Report Type &nbsp; : </span></div>
                        <div class="col-md-4" style="float: right;margin-right: 682px;margin-top: -5px;">
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
                        <div class="col-md-4" style="margin-top: -2px;margin-left: 8px;">
                            <h3 class="box-title"> Criterias for <label id="current_reporttype"></label></h3>
                        </div>
                    </div>
                <hr style="margin-top: 2px;margin-bottom: -30px;"><!-- /.box-header -->
                <div class="box-body" id="div-reportcriterias" style="margin-top: 40px;">
                    
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
    
//    $('#filterby_reporttype').select2();
    var reportname = $("#filterby_reporttype option:selected").text();
    $('#current_reporttype').html(reportname);
    $('#div-reportcriterias').load(livesite+'miscellaniousReports/changereporttype/'+reporttype);
});
function changeReportType(obj){
        var reporttype = $(obj).val();
        var reportname = $("#"+obj.id+" option:selected").text();
        $('#current_reporttype').html(reportname);
        $('#div-reportcriterias').load(livesite+'miscellaniousReports/changereporttype/'+reporttype);
}
   $(".home").on("click", function () {

    $("#container").isLoading({
        text: "Loading",
        position: "overlay",
    });

    let url = "";
    var userGroup = <?php echo json_encode($this->Session->read('user_group')); ?>;

    if (userGroup == "1") {
        url = livesite + "Report/index";
    } 
    else if (userGroup == "2") {
        url = livesite + "EmployeeMenu/addon";
    }

    $("#container").load(url, function () {
        isDashboardShown = false;
    });

});
</script>