
<style>
/*    .boxes {
  margin: auto;
  padding: 50px;
  background: #484848;
}*/

/*Checkboxes styles*/
input[type="checkbox"] { display: none; }

input[type="checkbox"] + label {
  /*display: block;*/
  position: relative;
  padding-left: 30px;
  margin-bottom: 20px;
  font: 14px/20px 'Open Sans', Arial, sans-serif;
  color: black;
  cursor: pointer;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
}

input[type="checkbox"] + label:last-child { margin-bottom: 0; }

input[type="checkbox"] + label:before {
  content: '';
  display: block;
  width: 20px;
  height: 20px;
  border: 2px solid #070da7;
  position: absolute;
  left: 0;
  top: 0;
  opacity: .6;
  -webkit-transition: all .12s, border-color .08s;
  transition: all .12s, border-color .08s;
}

input[type="checkbox"]:checked + label:before {
  width: 10px;
  top: -5px;
  left: 5px;
  border-radius: 0;
  opacity: 1;
  border-top-color: transparent;
  border-left-color: transparent;
  -webkit-transform: rotate(45deg);
  transform: rotate(45deg);
}
</style>
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
<div class="col-md-12 heading">
      <!-- edited by athira on 03-07-2025 -->
    <h1 class="text-primary-18">Company Profile Report</h1>
     <!-- /* edited by bindu 20-11-2025 */ -->
     <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
        <i class="fa" style="font-size:16px;">&#xf104;</i>
        Back
    </div>
     <!-- /* edited by bindu 20-11-2025 */ -->
    <!-- end -->
 
</div>
<div class="box col-md-12" style="padding-top: 2%;padding-left: 2%; border: white">
    <div class="row">
        <input type="checkbox" id="Company" value="0" ><label for="Company" style="margin-left: 20px;">Company Details</label>
  
  <input type="checkbox" id="Branch" value="0" ><label for="Branch" style="margin-left: 20px;">Branch Details</label>
  
  <input type="checkbox" id="Department" value="0" ><label for="Department"style="margin-left: 20px;">Department Details</label>
  <input type="checkbox" id="Designation" value="0" > <label for="Designation"style="margin-left: 20px;">Designation Details</label>
  <input type="checkbox" id="Bank" value="0" ><label for="Bank" style="margin-left: 20px;">Bank Details</label>
  <button type="button" id="view" class="btn btn-primary" style="float: right;margin-right: 80px;position: sticky;"><i class="fa fa-eye"></i> </button>
    </div>
    <div class="row">
        

    </div>
    <div class="col-md-12" id="viewreport">
        
    </div>
    
  
</div>
<script type="text/javascript">
    var company = 0;
    var branch = 0;
    var department = 0;
    var designation = 0;
    var bank = 0;
    $("#Company").on('change', function () {
        if ($("#Company").is(":checked")) {
             company = 1;
            }else{
                company =0; 
            }
            
   });
   $("#Branch").on('change', function () {
        if ($("#Branch").is(":checked")) {
             branch = 1;
            }else{
                branch =0; 
            }
            
   });
   $("#Department").on('change', function () {
        if ($("#Department").is(":checked")) {
             department = 1;
            }else{
                department =0; 
            }
            
   });
   $("#Designation").on('change', function () {
        if ($("#Designation").is(":checked")) {
             designation = 1;
            }else{
                designation =0; 
            }
            
   });
   $("#Bank").on('change', function () {
        if ($("#Bank").is(":checked")) {
            bank = 1;
            }else{
                bank =0; 
            }
            
   });
  
  $("#view").on('click', function () {
//        $.ajax({
            $("#viewreport").load(livesite+"CompanyProfileReport/viewreport/"+ company + "/" + branch +  "/"+ department + "/"+ designation + "/" + bank);
//                url: livesite+'CompanyProfileReport/viewreport/'+ company + '/' + branch +  '/'+ department + '/'+ designation + '/' + bank,
               
//                data: {
//                         x: x
//                     }
               
//            });
         
   });
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
 