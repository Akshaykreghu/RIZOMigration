<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<style>
     /* <!-- edited by bindhu 19-02-2026 --> */
    .heading {
        display: flex;
        flex-direction: row;
        align-items: end;
        justify-content: space-between;
     margin: 0 0 10px 0;
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
    /* end */
    .content-header{
        padding: 0;
    }
</style>
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- <legend>Salary Slip</legend> -->
              <!-- /* edited by bindhu 19-02-2026 */ -->
<section class="content-header heading">
    <h1 class="text-primary-18">Salary Slip</h1>
  <div class="text-primary-16 home"
    style="display:flex; align-items:center; gap:10px; cursor:pointer;">
    <i class="fa" style="font-size:16px;">&#xf104;</i>
    Back
  </div>
</section>
<hr style="margin-top: -4px;margin-bottom: 15px;">
<div class="form-group" id="div-criteria1">
        <div id="date">
        <div class="col-md-1"><b>Month</b></div>
        <div class="col-md-2">
              <select id="reportfrom" name="reportfrom" class="form-control">
  
         						
                                        <?php
										/*
										 * By santhosh on 27 Dec 2015
										 */
										$start_month = strtotime(date('Y-m', strtotime("+1 month", strtotime(date('Y-m')))));
                                                                                /*
										 * By megha on 04 April 2019
										 */
                                                                             for ($i = 0; $i < 18; $i++) {
                                                                                /*
										 * By megha on 04 April 2019
										 */
											$month = date('Y-m', strtotime("-$i month", $start_month));
											if($month == date('Y-m')){
												echo '<option selected="selected" value="' . $month . '">' . date('M-Y', strtotime("-$i month", $start_month)) . '</option>';
											}else{
												echo '<option value="' . $month . '">' . date('M-Y', strtotime("-$i month", $start_month)) . '</option>';
											}
                                        }
                                        ?>
            </select>
        </div>
        </div>
</div>  
            <div class="form-group">
            <div class="col-md-4">
                <a href="#" class="btn btn-default" onclick="viewReport();" ><i class="icon-file"></i>Show Slip</a>
                <a href="#" class="btn btn-default" onclick="downloadReport();" ><i class="icon-file"></i>Download Report</a>
            </div>
        </div>
        </div>
    </div>
   
</section>
<script>
   function viewReport(){ 
        var Date = $('#reportfrom').val();
        var companyCode = <?php echo json_encode($company_code);?>;
    if(companyCode === 'DRRC'){
        showLargeModalForm(livesite+'SalarySlipReports/slipthirdversion/'+Date);
    }else
    if(companyCode === 'DJOC'){  
        showLargeModalForm(livesite+'SalarySlipReports/slipfirstversion/'+Date);
    }else
     if(companyCode === 'DEMO' || companyCode === 'DJIC'){  
        showLargeModalForm(livesite+'SalarySlipReports/slipsecondversion/'+Date);
    }else{
        showLargeModalForm(livesite+'SalarySlipReports/Reports/'+Date);
    }
    
}

function downloadReport(){
         var Date = $('#reportfrom').val();
         var url = livesite+'SalarySlipReports/SalarySlipdownload/'+Date;
                               $(location).attr('href',url);  
                                      
}
     // edited by bindhu 19-02-2026
 $(".home").on("click", function() {
        $("#container").isLoading({
            text: "Loading",
            position: "overlay",
        });

        $("#container").load(livesite + "EmployeeMenu/index", function() {
            isDashboardShown = false;
        });


    });
    //  edited by bindhu 19-02-2026 end
</script>