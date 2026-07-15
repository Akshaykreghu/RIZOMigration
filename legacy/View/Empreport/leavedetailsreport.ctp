<style>
    div.dataTables_filter {
    text-align: right;
    /*margin-right: 77px;*/
    /*MARGIN-TOP: 10px;*/
}

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
    .content-header{
        padding: 0;
    }
    /* end */
</style>
<section class="content">
    <!-- <h1  style="text-align: left;padding-left: 2px;font-size: 30px; margin-top: 1px;">Leave Detailed Report </h1> -->

   <!-- /* edited by bindhu 19-02-2026 */ -->
<section class="content-header heading">
    <h1 class="text-primary-18"> Leave Details Report</h1>
  <div class="text-primary-16 home"
    style="display:flex; align-items:center; gap:10px; cursor:pointer;">
    <i class="fa" style="font-size:16px;">&#xf104;</i>
    Back
  </div>
</section>
 <!-- /* edited by bindhu 19-02-2026 end */ -->
    <!-- <hr style="margin-top: -4px;margin-bottom: 15px;"> -->
    <div class="box box-primary">
        <div class="col-md-12">  
            
             <div class="form-group">
                 <!-- <div class="col-md-1">
                     <a href="<?php echo $this->webroot; ?>Empreport/leavedetailsreport/excel" class="btn btn-success" style="float: right;margin-top: 10px;margin-bottom: -40px;margin-right: 41px;"><i class="fa fa-file-excel-o"></i></a>
                 </div> -->
            
        </div>
            <!--<a href="#" class="btn btn-default" onclick="downloadReport('Salaryslip','excel');"><i class="icon-file"></i>Download As Excel</a>-->

            <div class="table table-responsive">
                <table class="table table-bordered" id="LeaveDetailsReports" name="LeaveDetailsReports">
                    <thead>
                        <th>Sl No</th>
                        <th>Applied Date</th>
                        <th>Leave Status</th>
                        <th>Leave Type</th>
                        <th>From Date</th>
                        <!--<th>From Half</th>-->
                        <th>To Date</th>
                        <!--<th>To Half</th>-->
                        <th>Authorized By</th>
                        <th>Authorized Date</th>
                        <th>Approved By</th>
                        <th>Approved Date</th>
                        <th>Reason</th>
                        <!--                <th>Contact Number</th>-->
                        <th>Duties Handover To</th>
                        <!--<th>Remarks</th>-->
                        <th>Leave Days</th>
                        <!--<th>Message</th>-->


                    </thead>
                    <tbody>
                        <?php
                        $i = 0;

                        foreach ($arr_leave_details as $val) {
                            //debug($arr_leave_details);
                            //autharized check

                            if ($val['leaveentries']['ISAutherized'] == 0) {
                                $data = "NO";
                            } else {
                                $data = "YES";
                            }
                            //from haif    
                            if ($val['leaveentries']['FROMHALF'] == 1) {
                                $fromhalf = "First Half";
                            } else {
                                $fromhalf = "Second Half";
                            }
                            //to half
                            if ($val['leaveentries']['TOHALF'] == 1) {
                                $tohalf = "First Half";
                            } else {
                                $tohalf = "Second Half";
                            }
                            //leave approved    
                            if ($val['leaveentries']['ISAPPROVED'] == 0) {
                                $leaveapproved = "NO";
                            } else {
                                $leaveapproved = "yes";
                            }
                            $i++;
                        ?>






                            <?php echo '<tr>
                            <td>' . $i . '</td>
                  <td>' . $val['leaveentries']['applied_date'] . '</td>

                  <td>' . $val['leaveentries']['LEAVESTATUS'] . '</td>
                  <td>' . $val['LeaveType']['item'] . '</td>    
                  <td>' . $val['leaveentries']['FROMDATE'] . "-" . $fromhalf . '</td>
                 
                  <td>' . $val['leaveentries']['TODATE'] . "-" . $tohalf . '</td>
                
                  <td>' . $val[0]['Autherizedby'] . '</td>
                  <td>' . $val['leaveentries']['Autherized_date'] . '</td>
                  <td>' . $val['0']['APPROVEDBY'] . '</td>
                  <td>' . $val['leaveentries']['APPROVED_date'] . '</td>
                  <td>' . $val['leaveentries']['Reason'] . '</td>
                  
                  <td>' . $val['leaveentries']['contact_person'] . '</td>
                 
                  <td>' . $val['leaveentries']['leave_days'] . '</td>
                 
                 
                 
               
     </tr>'; ?>
                        <?php } ?>
                    </tbody>


                </table>
            </div>
        </div>
    </div>
    <div class="row">

    </div>
</section>

<script type="text/javascript">
    $(document).ready(function() {
        $('.tabset0').pwstabs({
            effect: 'scale', // You can change effects of your tabs container: scale / slideleft / slideright / slidetop / slidedown / none
            defaultTab: 1, // The tab we want to be opened by default
            containerWidth: '100%', // Set custom container width if not set then 100% is used
            tabsPosition: 'horizontal', // Tabs position: horizontal / vertical
            horizontalPosition: 'top', // Tabs horizontal position: top / bottom
            verticalPosition: 'left', // Tabs vertical position: left / right
            responsive: true, // Make tabs container responsive: true / false - boolean
            theme: '',
            rtl: false // Right to left support: true/ false
        });
        /*$('#togglechartweek').on('click', function () {
         $("weekChartRow").show();
         $("monthChartRow").hide();
         
         })
         $('#togglechartmonth').on('click', function () {
         $("weekChartRow").hide();
         $("monthChartRow").show();
         })*/
        $('#LeaveDetailsReports').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false
        });


        // Event listener to the two range filtering inputs to redraw on input
        $('#leaverequests-emp-filter, #leaverequests-month-filter').change(function() {
            empleaverequeststable.search(this.value).draw();
        });
    });
    
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