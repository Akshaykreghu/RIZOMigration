<style>
/* Legend container stacked vertically */
.legend-container {
    display: none; /* hidden by default */
    flex-direction: column;
    gap: 8px;
    width: 180px;
    z-index:999;
    position:absolute;
    top:38px;
    background-color: #dddddd;
    padding: 10px;
    border-radius: 5px;
}
.legend-item {
    display: flex;
    align-items: center;
    gap: 5px;
    background: #f9f9f9;
    padding: 5px 8px;
    border-radius: 4px;
    border: 1px solid #ddd;
    width: 100%;
}
.legend-box {
    width: 30px;
    height: 25px;
    color: white;
    text-align: center;
    line-height: 25px;
    font-weight: 600;
    border-radius: 3px;
}

.heading { display: flex; flex-direction: row; align-items: center; justify-content: space-between; }

/* Status colors */
.legend-present { background-color: #06a226; }
.legend-holiday { background-color: #2d2df4; }
.legend-weekoff { background-color: #dcdc00; color: black; }
.legend-absent { background-color: #e02429; }
.legend-abovemin { background-color: #34F593; }
.legend-belowmin { background-color: #FF967E; }

.heading {
        display: flex;
        flex-direction: row;
        align-items: end;
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
    }/* edited by bindu 22-08-25 */
</style>

<section class="content-header heading">
      
        <!-- /* edited by bindu 22-08-25 */ -->
    <h1 class="text-primary-18">Attendance Register</h1>
 <?php if ($plan !== 'basic') : ?>
    
    <div class="text-primary-16 home"
         style="display:flex; align-items:center; gap:10px; cursor:pointer;">
        <i class="fa" style="font-size:16px;">&#xf104;</i>
        Back
    </div>

<?php endif; ?>
                
    <!-- end -->
   
</section>
<hr style="margin-top: 8px;margin-bottom: -2px;margin-right: 15px;margin-left: 15px;">
<!-- Filters and Help Section -->
<section class="content" style="min-height:0px;padding-left:0px;">
    <div class="row" id="div-row" style="display:flex; align-items:center;margin-left:20px;">
        <!-- Filters: 2/3 width -->
        <div class="col-md-10" style="margin-left:0;">
            <div class="box-body" id="div-reportcriterias" style="display:flex; flex-wrap:wrap; align-items:center;">
                <div style="display:flex; align-items:center;margin-right:10px;">
                    <label class="filter-label" for="filterby_month">Month <span style="padding-left:10px;padding-right:10px;">:</span></label>
                    <div class="div-top">
                        <select id="filterby_month" name="filterby_month" style="width:150px;">
                            <?php  
                            $start_month = strtotime(date('Y-m', strtotime("+1 month", strtotime(date('Y-m')))));
                            for ($i = 0; $i < 38; $i++) {
                                $month = date('Y-m', strtotime("-$i month", $start_month));
                                echo '<option value="' . $month . '"' . ($month == date('Y-m') ? ' selected' : '') . '>' . date('M-Y', strtotime("-$i month", $start_month)) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div style="display:flex; align-items:center;margin-right:10px;">
                    <label class="filter-label" for="filterby_branch">Branch<span style="padding-left:10px;padding-right:10px;">:</span></label>
                    <div class="div-top">
                        <select id="filterby_branch" name="filterby_branch" class="form-control" style="width:150px;" onchange="filterEmployees(this);">
                            <?php foreach ($arr_branches as $value) { ?>
                                <option value="<?php echo $value['branch_code']; ?>"><?php echo $value['branch_name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div style="display:flex; align-items:center;margin-right:10px;">
                    <label class="filter-label" for="emp_fkey">Employee<span style="padding-left:10px;padding-right:10px;">:</span></label>
                    <div class="div-top">
                        <select id="emp_fkey2" class="js-example-basic-employee" style="width:150px;"></select>
                    </div>
                </div>

                <div>
                    <button type="button" class="btn  btn-process"  style="background-color:#1e516e;border-color:#1e516e;color:#ffffff;padding:3.2px 12px;">Process</button>
                </div>

                <div id="loadTime" style="text-align: right;font-size: 12px;color: rgb(0 166 90);margin-top: 5px;margin-left: 15px;"></div>
            </div>
        </div>

        <!-- Help button and legend: 1/3 width -->
        <div class="col-md-2" style="display:flex; flex-direction:column; align-items:flex-end;">
            <button type="button" id="legend-toggle" class="btn btn-info" style="margin-bottom:10px;padding:3px 12px;">
                <i class="fa fa-info-circle" aria-hidden="true"></i> Help
            </button>

            <div class="legend-container" id="legend-container">
                <div class="legend-item"><div class="legend-box legend-present">P</div> Present</div>
                <div class="legend-item"><div class="legend-box legend-holiday">HO</div> Holiday</div>
                <div class="legend-item"><div class="legend-box legend-weekoff">WO</div> Week Off</div>
                <div class="legend-item"><div class="legend-box legend-absent">LOP</div> Absent</div>
                <!-- <div class="legend-item"><div class="legend-box legend-abovemin">AWT</div> Above Min Working</div>
                <div class="legend-item"><div class="legend-box legend-belowmin">BMW</div> Below Min Working</div> -->
            </div>
        </div>
    </div>
</section>

          <!-- Tabs -->
<div id="attendanceTabsWrapper" style="display:none; margin-top:15px;">
    <ul class="nav nav-tabs" id="attendanceTabs" role="tablist" style="margin-left:15px;">
        <li class="nav-item">
            <a class="nav-link active" id="tab1-link" data-toggle="tab" href="#tab1" role="tab" aria-controls="tab1" aria-selected="true" >Not Verified</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="tab2-link" data-toggle="tab" href="#tab2" role="tab" aria-controls="tab2" aria-selected="false" >Verified</a>
        </li>
    </ul>
    <div class="tab-content" id="attendanceTabsContent">
        <!-- Tab 1 -->
        <div class="tab-pane fade show active" id="tab1" role="tabpanel" aria-labelledby="tab1-link" style="margin-top:15px;">
            <div id="load">
                <!-- Table will be loaded here after Process -->
            </div>
        </div>

        <!-- Tab 2 -->
        <div class="tab-pane fade" id="tab2" role="tabpanel" aria-labelledby="tab2-link" style="margin-top:15px;">
    <div id="summaryView"></div>
</div>

    </div>
</div>
<!-- Fullscreen loadertimesheet Overlay -->
<div id="loadertimesheet" style="
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
">
  <div style="text-align:center;">
    <img src="<?php echo $this->webroot; ?>img/updateimg.gif" alt="Loading..." style="width:120px; height:auto;">
  </div>
</div>


<script>
var livesite = '<?php echo $this->webroot; ?>';
    document.getElementById('legend-toggle').addEventListener('click', function() {
    var legend = document.getElementById('legend-container');
    var div_row = document.getElementById('div-row');
    if (legend.style.display === 'flex') {
        legend.style.display = 'none';
    } else {
        legend.style.display = 'flex';
        div_row.style.alignItems='center';
    }
});

document.addEventListener('click', function (e) {
    // If click is NOT inside legend and NOT the toggle button
     var legend = document.getElementById('legend-container');
    if (!e.target.closest('#legend-container') && !e.target.closest('#legend-toggle')) {
        legend.style.display = 'none';
    }
});

    function filterEmployees(branch)
    {
        var branch = $('#filterby_branch').val();
        var resigned = ($('#resigned_check').is(":checked")) ? 1 : 0;

          $(".js-example-basic-employee").select2(
                {
                    allowClear: true,
                    ajax: {
                        url: livesite + "AttendanceRegisterNew/jsons/" + branch + "/" + resigned,
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term,
                                page: params.page
                            };
                        },
                        processResults: function (data, params) {
                            params.page = params.page || 1;
                            return {
                                results: data.items,
                                pagination: {
                                    more: (params.page * 30) < (data.total_count || 0)
                                }
                            };
                        }
                    },
                    escapeMarkup: function (markup) {
                        return markup;
                    }
                });
    }
      function filtersitemonth(month){
         var month = $('#filterby_month').val();
        //alert(branch);
        $(".js-example-basic-site").select2(
                {
                    //closeOnSelect:false,
                    allowClear: true,
                    ajax: {
                        url: livesite + "AttendanceRegisterNew/getbranches",
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term, // search term
                                page: params.page
                            };
                        },
                        processResults: function (data, params) {
                            // parse the results into the format expected by Select2
                            // since we are using custom formatting functions we do not need to
                            // alter the remote JSON data, except to indicate that infinite
                            // scrolling can be used
                            params.page = params.page || 1;

                            return {
                                results: data.items,
                                pagination: {
                                    more: (params.page * 30) < data.total_count
                                }
                            };
                        }
                    },
                    escapeMarkup: function (markup) {
                        return markup;
                    }
                });
    }
    function refresh(t)
    {
        var elem = $(t).attr('id');
//        alert(elem);
        if(elem === 'filterby_month'){
            $('#emp_fkey2').val('');
            $('#filterby_branch').val('');
        }
        if(elem === 'filterby_branch'){
            $('#emp_fkey2').val('');
        }
//        $('#emp_fkey2').val('');
        filterEmployees();
        filtersitemonth();
    }
    
    function processRegisterS()
    {

    }
    // function filterRegister(obj)
    // {
    //     $('#loadertimesheet').show();
    //     var month = $('#filterby_month').val();
    //     if ($('#emp_fkey2').val() != "0" || $('#emp_fkey2').val() != "")
    //     {
    //         var emp_fkey = $('#emp_fkey2').val();
    //     } else
    //     {
    //         var emp_fkey = 0;
    //     }
    //     if ($('#filterby_branch').val() != "")
    //     {
    //         var branch = $('#filterby_branch').val();
    //     } else
    //     {
    //         var branch = 0;
    //     }
    //     $('#load').load(livesite + 'AttendanceRegisterNew/registerbook/' + month + '/' + emp_fkey + '/' + branch , function () {
    //         $('#loadertimesheet').hide();
    //     });
    // }


 function filterRegister(isdelete = 'Y', activateTab = true, skipProc = 'N') {
     $('#loadertimesheet').show();

     const month = $('#filterby_month').val();
     const emp_fkey = $('#emp_fkey2').val() || 0;
     const branch = $('#filterby_branch').val() || 0;
     const urlBase = livesite + 'AttendanceRegisterNew/registerbook/' + month + '/' + emp_fkey + '/' + branch + '/';
       console.log('employee',$('#select2-emp_fkey-container').title);

     $('#attendanceTabsWrapper').show(); // show tabs

     // Only for NOT VERIFIED tab and skipProc = 'N'
     if(isdelete === 'Y' && skipProc === 'N') {

        $.post(livesite + 'AttendanceRegisterNew/checkprocessingstatus', { branch, month }, function(resp) {
    const res = JSON.parse(resp);
    if(res.success === 0){
        alert(res.message);
        $('#loadertimesheet').hide();
        return;
    }

     const pkey = res.pkey; // store this pkey

    const startTime = performance.now();

    $('#load').load(urlBase + 'Y/' + skipProc, function() {
        const endTime = performance.now();
        const duration = ((endTime - startTime) / 1000).toFixed(2);

        // Pass pkey here
         $.post(livesite + 'AttendanceRegisterNew/markprocesscomplete', { pkey, duration }, function(resp) {
             $('#loadTime').text('Attendance loaded in ' + duration + ' seconds').show();
             $('#loadertimesheet').hide();
        });
     });
 });

     } else {
        // For VERIFIED tab or skipProc, just load without timing

        // Always reload both tabs every time
 $('#load').load(urlBase + 'Y/' + skipProc);     // Not Verified
 // $('#summaryView').load(urlBase + 'N/' + skipProc);  // Verified

 $('#summaryView').load(urlBase + 'N/' + skipProc, function() {
     console.log("Verified tab refreshed");
 });


$('#loadertimesheet').hide();

//         // if(!$('#load').data('loaded')) {
//         //     $('#load').data('loaded', true);
//         //     $('#load').load(urlBase + 'Y/' + skipProc, function() {
//         //         $('#loadertimesheet').hide();
//         //     });
//         //     $('#summaryView').load(urlBase + 'N/' + skipProc);
//         // } else {
//         //     $('#loadertimesheet').hide();
//         // }
 }

//     // Show selected tab
    if (isdelete === 'Y') {
        if (activateTab) $('#tab1-link').tab('show');
     } else {
         if (activateTab) $('#tab2-link').tab('show');
    }
 
}






// Process button → default to Not Verified
// $('.btn-process').on('click', function () {
//     filterRegister('Y');
// });

$('.btn-process').on('click', function() {
        filterRegister('Y', true);
          if (window._leaveCache) {
        window._leaveCache = {};
    }
    
});


$(document).ready(function () {

//     $('#attendanceTabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
//     const target = $(e.target).attr("href"); // e.g. #tab1 or #tab2
//     $('.tab-pane').removeClass('show active');
//     $(target).addClass('show active');
// });

$('#attendanceTabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        const target = $(e.target).attr("href"); // #tab1 or #tab2

        $('.tab-pane').removeClass('show active');
        $(target).addClass('show active');
    });


 

    // Handle tab changes correctly
    // $('#tab1-link').on('shown.bs.tab', function () {
    //     // NOT VERIFIED tab → isdelete='Y'
    //     filterRegister('Y', false);
    // });

    // $('#tab2-link').on('shown.bs.tab', function () {
    //     // VERIFIED tab → isdelete='N'
    //     filterRegister('N', false);
    // });

    $('#tab1-link').on('click', function () {
    filterRegister('Y', false,'Y');
    
});

$('#tab2-link').on('click', function () {
    filterRegister('N', false,'Y');
});

});





    function filterRegister1() 
    {
        //$('#loadertimesheet').show();
        var month = $('#filterby_month').val();
        if ($('#emp_fkey2').val() != "0" || $('#emp_fkey2').val() != "")
        {
            var emp_fkey = $('#emp_fkey2').val();
        } else
        {
            var emp_fkey = 0;
        }
        if ($('#filterby_branch').val() != "")
        {
            var branch = $('#filterby_branch').val();
        } else
        {
            var branch = 0;
        }
        $('#load').load(livesite + 'AttendanceRegisterNew/registerbook/' + month + '/' + emp_fkey + '/' + branch , function () {
            //$('#loadertimesheet').hide();
        });
    }
    
    $(document).ready(function () {
        $('#filterby_month').select2();
        $('#filterby_branch').select2();
        refresh();
        $('#loadertimesheet').hide();
        filterEmployees();
        var usersoptions = {
            url: function (phrase) {
                return "UserCredentials/getusers?username=" + phrase;
            },
            getValue: "full_name",
            list: {
                onClickEvent: function () {
                    var selectedItem = $("#user").getSelectedItemData();
                    var emp_fkey = selectedItem.emp_pkey;
                    $('#emp_pkey').val(selectedItem.emp_pkey);
                     var branch = $('#filterby_branch').val();
                    var month = $('#filterby_month').val();
                    $('#load').load(livesite + 'AttendanceRegisterNew/registerbook/' + month + '/' + emp_fkey + '/' + branch);


                }
            }
        };


        $('#user').easyAutocomplete(usersoptions);
        $('#tt').tree({
        });
         //added by megha on 8_6_19 resigned employee data
         $('input[type="checkbox"]').click(function(){
             filterEmployees();
         });
         //end
    })
    	 $(".home").on("click", function () {

    $("#container").isLoading({
        text: "Loading",
        position: "overlay",
    });

    let url = "";
    var userGroup = <?php echo json_encode($this->Session->read('user_group')); ?>;

    if (userGroup == "1") {
        url = livesite + "AttendanceSetup/index";
    } 
    else if (userGroup == "2") {
        url = livesite + "EmployeeMenu/addon";
    }

    $("#container").load(url, function () {
        isDashboardShown = false;
    });

});
</script>