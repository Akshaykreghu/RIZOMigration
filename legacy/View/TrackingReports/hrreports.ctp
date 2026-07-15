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
    <h1 class="text-primary-18"> Tracking Reports </h1>
    <!-- end -->
 <!-- /* edited by bindu 20-11-2025 */ -->
     <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
        <i class="fa" style="font-size:16px;">&#xf104;</i>
        Back
    </div>
     <!-- /* edited by bindu 20-11-2025 */ -->
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- DIRECT CHAT DANGER -->
            <!--<div class="box ">-->
                <!--<div class="box-header with-border">-->
                    <div class="row">
                         <div class="col-md-4" style="" > <span>Choose Report Type &nbsp; : </span></div>
                        <div class="col-md-4" style="">
                            <select id="filterby_reporttype" style="width: 240px;" name="filterby_reporttype" class="form-control" onchange="changeReportType(this);" >
                                <?php
                                foreach ($arr_reporttypes as $key => $value) {
                                    echo '<option value="' . $key . '">' . $value . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                      
                      
                    </div>
               
                <!--</div>-->
                 <div class="row">
                        <div class="col-md-4" style="margin-top: -2px;margin-left: 8px;">
                            <h3 class="box-title"> Criterias for <label id="current_reporttype"></label></h3>
                        </div>
                    </div>
                <hr style="margin-top: 2px;margin-bottom: -30px;"><!-- /.box-header -->
                <div class="" style="margin-top: 40px;" id="div-reportcriterias">
                    
                </div><!-- /.box-body -->
            <!--</div>-->
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
    //$("#showleavedetails").html('<li class="fa fa-spinner fa-spin" style="margin-left: 5em;margin-top: 2em;font-size: 50px;"></li>');
    $('#div-reportcriterias').load(livesite+'TrackingReports/changereporttype/'+reporttype);
});
function changeReportType(obj){
    
        var reporttype = $(obj).val();
        var reportname = $("#"+obj.id+" option:selected").text();
        $('#current_reporttype').html(reportname);
        $('#div-reportcriterias').load(livesite+'TrackingReports/changereporttype/'+reporttype);
}
/* edited by bindu 21-02-26 */
$(document).on("click", ".home", function () {

        $("#container").isLoading({
            text: "Loading",
            position: "overlay",
        });

        let url = "";
        var userGroup = <?php echo json_encode($this->Session->read('user_group')); ?>;
        var menuType = sessionStorage.getItem('menu_type');

        if (userGroup == "1") {
            url = livesite + "Report/index";
        }
        else if (userGroup == "2") {
            if (menuType === 'addon') {
                url = livesite + "EmployeeMenu/addon";
            } else {
                url = livesite + "EmployeeMenu/index";
            }
        }
        else {
            url = livesite + "Dashboard/index"; // fallback safety
        }

        $("#container").load(url, function () {
            isDashboardShown = false;
        });

    });

/* edited by bindu 21-02-26 end */
</script>