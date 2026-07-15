<style>
    /* Highlight colour edited by: Akshay*/
    /* .datagrid-row-over td{
                background:#D0E5F5;
            } */ 
    .datagrid-row-selected td{
        background:#0093FF ;
    }
    /* -- */
    .left-inner-addon {
        position: relative;
    }
    .left-inner-addon input {
        padding-left: 30px;    
    }
    .left-inner-addon i {
        position: absolute;
        padding: 10px 12px;
        pointer-events: none;
    }

    .right-inner-addon {
        position: relative;
    }

    td[field="avatar"] .datagrid-cell {
        width: 86px;
        height: 30px;
    }

    .right-inner-addon input {
        padding-right: 30px;    
    }
    .right-inner-addon i {
        position: absolute;
        right: 0px;
        padding: 10px 12px;
        pointer-events: none;
    }

    .form-horizontal .control-label{

        text-align: left;
        padding-left: 2px;
    }

    .resumebutton {
        font-weight: 600;
        padding-left: 40px;
    }

    .custom-file-upload {
        border: 1px solid #0d0c0c52;
        border-radius: 4px;
        display: inline-block;
        padding: 4px 12px;
        cursor: pointer;
        width: 100%;
        height: 30px;
        text-align: center;
    }
      /* <!-- edited by bindu 21-11-2025 --> */
     .heading {
        display: flex;
        flex-direction: row;
        align-items: end;
        justify-content: space-between;
        /* margin-left: 20px; */
        padding: 15px 0 !important;
    }

    .home {
        background-color: #ffffffff;
        border-radius: 50px;
        padding: 2px 15px;
        color: #1e516e !important;
        margin-right: 15px;
        color: white;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        border: #1e516e 1px solid;
    }
     /* <!-- edited by bindu 21-11-2025 --> */
</style>

<section class="content-header">
     <div class="heading">
    <h1 class="text-primary-18">Add Employee</h1>
       <!-- edited by bindu 21-11-2025 -->
       <?php
$userGroup = $this->Session->read("user_group");

if ($plan != 'basic'){
?>
    <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
        <i class="fa" style="font-size:16px;">&#xf104;</i>
        Back
    </div>
<?php } ?>
     <!-- edited by bindu 21-11-2025 -->
</div>
</section>
  <hr style="margin-top: 8px;margin-bottom: -2px;">
<!-- Main content -->
<section class="content">
    <div class="col-md-12">
        <br>
<!-- edited by athira on 04-02-2025 -->
<?php if ( isset($plan) && $plan !='basic'){?>
     <!-- Edited by Akshay on 2-7-2025 Task #132902-->
                    <div class="form-group row" style="display: flex; justify-content: space-between; align-items: center;">

                        <!-- Left: Branch Dropdown -->
                        <div class="col-md-6">
                            <div class="row">
                                <label class="col-md-2" for="import_emp_branch">Branch:</label>
                                <div class="col-md-4" style="padding-left:0px;">
                                    <select id="import_emp_branch" name="import_emp_branch" class="form-control js-example-basic-single" onchange="filterEmployeesIndex()">
                                        <!-- Edited by Akshay on 5-7-2025 Task #132902 -->
                                        <option value="">All</option>
                                        <!-- End -->
                                        <?php 
                                        foreach ($arr_branches as $key => $value) {
                                            echo '<option value="' . $value['branch_code'] . '">' . $value['branch_name'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Upload & Download Buttons -->
                        <div class="col-md-6 text-right">
                            <form class="form-inline" method="post" action="<?php echo $this->webroot; ?>Employee/saveemployeesetup" id="importemployeeform" style="display:inline;">
                                <input type="file" id="empdatacsv" name="empdatacsv" accept=".xlsx" style="display:none;" onchange="uploadEmployeeData(this);" />
                                <button type="button" id="btn-uploademployeedata" class="btn btn-success" onclick="$('#empdatacsv').click();" data-toggle="tooltip" title="Upload" data-placement="top">
                                    <i class="fa fa-file-excel-o"></i>
                                </button>
                            </form>

                            <button onclick="downloadexcelformat();" class="btn btn-danger" data-toggle="tooltip" title="Download template" data-placement="top">
                                <i class="fa fa-file-excel-o"></i>
                            </button>

                            <div class="active" id="loaders" style="display:none; color: #4a3417; margin-top: 5px;">
                                Please Wait... <i class="fa fa-spinner fa-spin"></i>
                            </div>
                        </div>
                    </div>

                    <!-- End -->
        <?php }?>
        <!-- end -->
        <br><br>
        <!-- edited by bindu 26-08-25 -->
        <div class="box box-primary " style="margin-top: -21px;">
            <div class="box-body" style="    margin-top: -11px;">
                <br>
                <input type="hidden" id="rsndempid" value="0" name="resigned">
                <div class="col-md-7"></div>
            </div>
        <div>
<!-- edited by bindu 26-08-25 end-->
        <!-- <div class="box box-primary " style="margin-top: -21px;">
            <div class="box-body" style="    margin-top: -11px;">
                <br>
                <input type="hidden" id="rsndempid" value="0" name="resigned">
                <div class="col-md-7"></div>
                <div class="col-md-5 pull-right row">
                    <label class="col-md-3" for="filterby_branch">Branch</label>
                    <div class="col-md-1">:</div>
                    <div class="col-md-8">
                        <select id="filterby_branch" name="filterby_branch" class="form-control" onchange="filterEmployees(this);" >
                            <option value="">All</option>
                            <?php foreach ($arr_branches as $key => $value) { ?>                              
                                <option  value="<?php echo $value['branch_code']; ?>"><?php echo $value['branch_name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <br><br>
                
            </div>
           
        </div> -->
         <!-- <input type="hidden" id="rsndempid" value="0" name="resigned"> -->
        <table id="emptable" class="table table-bordered table-hover">

                </table>
    </div>
</div>


<!-- THE BELOW CODE ALL ARE HIDED -->


<!--    <div class="row">
        <div class="col-md-12">
             Employee List 
             DIRECT CHAT DANGER 

            <div class="box box-primary">
                               <div class="box box-header">
<?php
if (isset($missed_prof) && $missed_prof) {
    ?>
                                                                                                                                       <div id="missedDetails" class="alert alert-warning alert-dismissible">
                                                                                                                                           <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                                                                                                                           <h4><i class="icon fa fa-warning"></i> Missed Professional Details of Employees!</h4>
                                                                                                                                           Found Some Mandatory Fields Missing, Please Fill all the mandatory fields of Employee Who Seems in Red Color.
                                                                                                                                       </div>
    <?php
}
?>
                                   <h2>Employees</h2>
                                   <div class="row">
                                            <div class="col-md-4 pull-right">
                                                <label class="col-sm-5" for="filterby_branch">Choose Branch</label>
                                                <div class="col-md-7">
                                                    <select id="filterby_branch" name="filterby_branch" class="form-control" onchange="filterEmployees(this);" >
                                                        <option value="">All</option>
<?php foreach ($arr_branches as $key => $value) { ?>                              
                                                                                                                                                            <option  value="<?php echo $value['branch_code']; ?>"><?php echo $value['branch_name']; ?></option>
<?php } ?>
                                                    </select>
                                                </div>
                                            </div> 
                
                                            <div class="col-md-4 pull-right">
                                                <label class="col-sm-5" for="filterby_employees">Choose Employee</label>                 
                                                <div class="right-inner-addon col-md-7">
                                                   
                
                                                   <input type="search" id="filterby_employees" class="easyui-searchbox" name="filterby_employees" placeholder="Search Your Employee Name" /> 
                                                                                   <input type="hidden" id="hid_filterby_employees" name="hid_filterby_employees"/>
                                                </div>  
                      
                                            </div>
                                            
                                               <div class="col-md-4">
                                                <label class="col-sm-5" for="filterby_Designation">Choose Designation</label>                 
                                                <div class="col-md-7">
                                                    <select id="filterby_Designation" class="form-control" name="filterby_Designation" onchange="filterAttendanceupload(this);" >
                                                        <option value="">All</option>
<?php foreach ($arr_Des as $value) { ?>
                                                                                                                                                            <option value="<?php echo $value['desig_code']; ?>"><?php echo $value['desig_name']; ?></option>
<?php } ?>
                                                    </select>
                                                </div>    
                                            </div> 
                                        </div>
                               </div>
                <div class="box box-body "style="border: white;">

                    <div class="col-md-12" style="padding-bottom: 10px;">
                        <div class="col-md-6" style="font-size: 22px;margin-left: -29px;">Employee List</div>
                        <label class="col-sm-3" for="filterby_branch" style="padding-left: 152px;padding-top: 5px;">Choose Branch : </label>
                        <div class="col-md-3">
                            <select id="filterby_branch" name="filterby_branch" class="form-control" onchange="filterEmployees(this);" >
                                <option value="">All</option>
<?php foreach ($arr_branches as $key => $value) { ?>                              
                                                                                                            <option  value="<?php echo $value['branch_code']; ?>"><?php echo $value['branch_name']; ?></option>
<?php } ?>
                            </select>
                        </div>
                    </div> 
                    <hr style="border-top: 1px solid lightgray;">

                    <input type="hidden" id="rsndempid" value="0" name="resigned">


                    <table id="emptable" class="table table-bordered table-hover">

                    </table>
                </div>
            </div> /.box-body 

        </div>/.direct-chat 
    </div> /.col -->

<!-- THE ABOVE CODE ALL ARE HIDED -->

</section>
<div id="empSetupModalForm" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Content will be loaded here from "remote.php" file -->
        </div>
    </div>
</div>
<script>
    $('#empdatacsv').change(function () {
        var i = $(this).prev('label').clone();
        var file = $('#empdatacsv')[0].files[0].name;
        $(this).prev('label').text(file);
    });
    function filterEmployees(obj) {
        var branch = $('#filterby_branch').val();
        var employee = $('#hid_filterby_employees').val();

        var designation = $('#filterby_Designation').val()

        $('#emptable').datagrid('load', {
            branch: branch,
            employee: employee,
            designation: designation,
            name: $('#rsndempid').val(),
        });

    }
     // Edited by Akshay on 1-7-2025 // Task #132902
    function filterEmployeesIndex(obj) {
        var branch = $('#import_emp_branch').val();
        var employee = $('#hid_filterby_employees').val();

        var designation = $('#filterby_Designation').val()

        $('#emptable').datagrid('load', {
            branch: branch,
            employee: employee,
            designation: designation,
            name: $('#rsndempid').val(),
        });

    }
    // End
   function setwidth() {
        // Target the Add Employee tab's toolbar
        var addEmployeeToolbar = $('.datagrid-toolbar');

        // Check if the checkbox already exists in the Add Employee tab
        if (addEmployeeToolbar.find('#checkrsgnd').length == 0) {
            // Append the checkbox only in the Add Employee tab
            // Edited by Akshay on 1-7-2025 Task #132902
            addEmployeeToolbar.find('tr').append('<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="rsgnemp" id="checkrsgnd" onclick="showrsgnd();"value="0" hidden></td>');
            // End
        }
    }
    function showrsgnd() {
        var branch = $('#filterby_branch').val();
        var employee = $('#filterby_employees').val();

        var designation = $('#filterby_Designation').val();
        if ($('#checkrsgnd').is(":checked"))
        {
            $('#rsndempid').val('1');

        }
        else {
            $('#rsndempid').val('0');

        }
        $('#emptable').datalist('load', {
            name: $('#rsndempid').val(),
            branch: branch,
            employee: employee,
            designation: designation,
        });
    }
    function downloadexcelformat() {
        window.open(livesite + 'Employee/downloadempdataformat', '_blank');
    }
    function getstages(branch) {
        //alert(skey);
        var request = $.ajax({
            url: "Employee/getstages/" + branch
            , method: "POST",
            dataType: "html"
        });


        request.done(function (msg) {
            $('#filterby_employees').html(msg);
        });

        request.fail(function (jqXHR, textStatus) {
            alert("Request failed: " + textStatus);
        });
    }
    function uploadEmployeeData(input) {
        var form = $('#importemployeeform');
        //var fileSelect = $('#empdatacsv');
        var fileSelect = document.getElementById('empdatacsv');
        var empBranch = $('#import_emp_branch').val();

        // The rest of the code will go here...
        var files = fileSelect.files;

        if (empBranch == '') {
            $.notify("Please choose branch!", {
                type: 'danger',
                allow_dismiss: false
            });
             input.value = ""; // Edited by Akshay on 13-9-2025
            return false;
        } else if (files.length == 0) {
            $.notify("Please choose file!", {
                type: 'danger',
                allow_dismiss: false
            });
             input.value = ""; // Edited by Akshay on 13-9-2025
            return false;
        }
        $('#loaders').fadeIn();

        // Create a new FormData object.
        var formData = new FormData();
        // Loop through each of the selected files.

        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            // Add the file to the request.
            formData.append('empdata[]', file, file.name);
        }

        // Set up the request.
        var xhr = new XMLHttpRequest();

        // Open the connection.
        xhr.open('POST', livesite + 'Employee/uploadandsaveempdetails/' + empBranch, true);

        // Set up a handler for when the request finishes.
        xhr.onload = function () {
            if (xhr.status === 200) {
                // File(s) uploaded.
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    console.log(response);
                    //alert(response.msg);
                    $('#empdatacsv').val("");
                    var importedCount = response.imported_count;
                    var strDuplicateEmpKeys = response.duplicate_emp_list.join(',');
                    //alert(response.errors);
                    console.log(strDuplicateEmpKeys);
                    reloadTable('emptable');

                    //var errors = response.errors.join(',');
                    var errors = response.errors.filter(Boolean).join(',');
                    var duplicateId = response.duplicate_id.join(','); //Edited by Akshay on 29-11-2023

                    function showAlert(message) { //Edited by Akshay on 30-11-2023
                        var deferred = $.Deferred();
                        alert(message);
                        deferred.resolve();
                        closeButtonClicked = false;
                        $('#alertsModalForm-content .btn-primary[data-dismiss="modal"]').on('click', function() {
                            showModalForm(livesite + 'Employee/showimportresponse/' + importedCount + '/' + strDuplicateEmpKeys + '/' + encodeURIComponent(strDuplicateEmpKeys));
                        });
                    }
                    //alert(strDuplicateEmpKeys);
                    if (strDuplicateEmpKeys == '') {
                        strDuplicateEmpKeys = 0;
                    } else {
                        // strDuplicateEmpKeys = 1;
                        alert("Some Employee Duplicate Found : " + strDuplicateEmpKeys);
                    }
                    var upload_pkeys = response.upload_pkeys;
                    // showModalForm(livesite+'Employee/showimportresponse/' + upload_pkeys + '/'+ response.success + '/'+encodeURIComponent(strDuplicateEmpKeys) );
                    showModalForm(livesite + 'Employee/showimportresponse/' + importedCount + '/' + strDuplicateEmpKeys + '/' + encodeURIComponent(strDuplicateEmpKeys));
                    if (errors == '') {
                        errors = 0;
                        if (duplicateId != '') { //Edited by Akshay on 29-11-2023
                            showAlert("Some Employees Not Saved Due to UnKnown Designation/Department Code/Duplicate ID/AADHAR Card Number Uploaded, Employee Names :" + duplicateId);
                        }
                    } else {
                        if (duplicateId == '') {
                            if(false){
                                showAlert("Some Employees Not Saved Employee Names :" + errors);
                            }else{
                                showAlert("Some Employees Not Saved Due to UnKnown Designation/Department Code/Duplicate ID/AADHAR Card Number Uploaded, Employee Names :" + errors);
                            }
                        } else {
                            showAlert("Some Employees Not Saved Due to UnKnown Designation/Department Code/Duplicate ID/AADHAR Card Number Uploaded, Employee Names : " + errors +', '+ duplicateId);
                        }
                    }
                    $('#loaders').fadeOut();
                     if (response.success == '1') {
                        if (importedCount > 0) {
                            $.notify(response.msg, {
                                type: 'success',
                                allow_dismiss: false
                            });
                        }else{
                            $.notify('Sorry not able to import Employee data', {
                                type: 'danger',
                                allow_dismiss: false
                            });
                        }
                    }
                    //alert("Some Entries can't Upload, Please check the Designation/Department Code of these employees "+errors);
                    //showModalForm(livesite+'Employee/showimportresponse/'+importedCount+'/'+encodeURIComponent(strDuplicateEmpKeys)+'/'+encodeURIComponent(errors));
                } else {
                    //alert(response.msg);
                    $('#loaders').fadeOut();
                    $.notify(response.msg, {
                        type: 'danger',
                        allow_dismiss: false
                    });
                }
            } else {
                //alert("Employee import failed, please try again.");
                $.notify("Employee import failed, please try again!", {
                    type: 'danger',
                    allow_dismiss: false
                });
            }
        };
       input.value = ""; // Edited by Akshay on 13-9-2025
        // Send the Data.
        xhr.send(formData);
    }

    jQuery(document).ready(function () {
        $("#import_emp_branch").select2();
        $("#filterby_branch").select2();
        function filterAttendanceautocomplete(obj) {
            var branch = $('#filterby_branch').val();
            var employee = obj;

            var designation = $('#filterby_Designation').val()

            $('#emptable').datagrid('load', {
                branch: branch,
                employee: employee,
                designation: designation,
            });

        }

        var usersoptions = {
            url: function (phrase) {
                var branch = $('#filterby_branch').val();
                return livesite + "Employee/getautocompletions?username=" + phrase + "&branch=" + branch;
            },
            getValue: "emp_name",
            list: {
                onClickEvent: function () {
                    //var selectedItem = $('#filterby_employees').getSelectedItemData();
                    //var site_pkey = selectedItem.emp_pkey;
                    filterAttendanceautocomplete($('#hid_filterby_employees').val());
                },
                onKeyEnterEvent: function () {
                    filterAttendanceautocomplete($('#hid_filterby_employees').val());
                },
                onSelectItemEvent: function () {
                    var selectedItem = $('#filterby_employees').getSelectedItemData();
                    var site_pkey = selectedItem.emp_pkey;
                    $('#hid_filterby_employees').val(site_pkey);
                }
            }
        };

        $('#filterby_employees').easyAutocomplete(usersoptions);
        var searchTimer = null;
        $('#emptable').datagrid({
            url: livesite + "Employee/listemployees",
//            title: "Employee",
            fitColumns: true,
            singleSelect: true,
            autoRowHeight: false,
            pagination: true,
            PostsearchFilter: true,
            rownumbers: true,
            pageSize: 10,
            width: '100%',
            rowStyler: function (index, row) {
                var style = "";
                if (row.status == '2') {
                    style += 'background-color:#cac3c3;color:#fff;';
                }
                /* if (row.emp_company_id == null) {
                    style += 'background-color:#551414;color:#fff;';

                } */
                return style;
            },
            toolbar: [{
                    text: 'New',
                    iconCls: 'icon-add',
                    handler: function () {

                        showLargeModalForm(livesite + 'Employee/setup/0');
                    }
                }, {
                    iconCls: 'icon-edit',
                    text: 'Edit',
                    handler: function () {
                        var row = $('#emptable').datagrid('getSelected');
                        if (row) {
                            showLargeModalForm(livesite + 'Employee/setup/' + row.emp_pkey);
                        } else {
                            alert("Please select a record to edit")
                        }
                    }
                },{

                    iconCls: 'icon-remove',
                    text: '<span style="color:red">Remove</span>',
                    handler: function () {

                        var rows = $('#emptable').datagrid('getSelected');
                        if (rows) {
                            var str_ids = "";
                            str_ids = rows.emp_pkey;

//                    	 for(var i=0;i<rows.length;i++){
//                    	 	var data = rows[i];
//									 if(str_ids == ""){
//										 str_ids += data.emp_pkey;
//									 }else
//									 {
//										 str_ids += ","+data.emp_pkey;
//									 }
//                    	 }      
                            if (confirm("Do you want to delete the selected employee(s)?")) {

                                $.ajax({
                                    url: livesite + "Employee/deleteEmp",
                                    data: {
                                        ids: str_ids
                                    },
                                    success: function (response) {
                                        //var text = response.responseText;
                                        // process server response here
                                        reloadTable('emptable')
                                    }
                                });
                            }
                        } else {
                            alert("Please select a record to edit")
                        }
                    }
                }, 
                {
                    iconCls: 'icon-edit',
                    text: 'Active',
                    visible:false,
                    handler: function () {

                        var rows = $('#emptable').datagrid('getSelected');
                     
                        
                        if (rows) {
                            
                            if(rows.status == 2 ){
                               
                            var str_ids = "";
                            str_ids = rows.emp_pkey;

//                    	 for(var i=0;i<rows.length;i++){
//                    	 	var data = rows[i];
//									 if(str_ids == ""){
//										 str_ids += data.emp_pkey;
//									 }else
//									 {
//										 str_ids += ","+data.emp_pkey;
//									 }
//                    	 }      
                            if (confirm("Do you want to activate the selected employee?")) {

                                $.ajax({
                                    url: livesite + "Employee/activeEmp",
                                    data: {
                                        ids: str_ids
                                    },
                                    success: function (response) {
                                        //var text = response.responseText;
                                        // process server response here
                                        //  var text = JSON.stringify(response);
                                       var text = JSON.parse(response);
                                    //     reloadTable('emptable');
                                        if (text.success == '1') {
                                                           
                                                            reloadTable('emptable');
                                                            $.notify(text.msg, {
                                                            type: 'success',
                                                            allow_dismiss: false
                                                        });
                                                    }else{
                                                        $.notify("Error", {
                                                        type: 'danger',
                                                        allow_dismiss: false
                                                        });
                                                    }
                                    }
                                });

                            }

                        }else{
                            
                            alert("Please select a valid record");
                           $("a:focus").css("color", "#444");
                        }
                        } else {
                            alert("Please select a record to edit");
                            $("a:focus").css("color", "#444");// Change active button color
                        }

                    }
                },
//edited by sinsiya

 <?php if (isset($payroUser[0]['emp_proff']['payro_priv']) && $payroUser[0]['emp_proff']['payro_priv'] !== '1') {
?>	
         {
                    iconCls: 'icon-edit',
                    text: 'Promotion',
                    handler: function () {
                        var row = $('#emptable').datagrid('getSelected');
                        if (row) {
                            showLargeModalForm(livesite + 'Promo/promotion/' + row.emp_pkey);
                        } else {
                            alert("Please select a record to edit")
                        }
                    }
                },
<?php }  
 //edited by athira on 07-02-2025
                if ($user_group == 1) { ?>
                    <?php if ($plan != 'basic') { ?> 
                        {
                        iconCls: 'icon-edit',
                        text: 'Employee Info Change', //edited by ASHIN on 25-09-24 
                        handler: function() {
                            var row = $('#emptable').datagrid('getSelected');
                            if (row) {
                                showLargeModalForm(livesite + 'Promo/promotion/' + row.emp_pkey);
                            } else {
                                alert("Please select a record to edit")
                            }
                        }
                    },
                <?php } ?>
                <?php } ?>
                //end
//                                ,'-',{
//				iconCls: 'icon-export',
//				text:'Download Employee Data Format',
//				handler: function(){
//					
//					 window.open(livesite+'Employee/downloadempdataformat','_blank');
//				}
//				}
  // Edited by Akshay on 1-7-2025 Task #132902
                {
                    iconCls: 'icon-man',
                    text: '<span id="btnIncludeResignedLabel">Include Resigned</span>',
                    id: 'btnIncludeResigned',
                    toggle: true,
                    selected: false,
                    handler: function() {
                        var $btn = $('#btnIncludeResigned');
                        var $label = $('#btnIncludeResignedLabel');

                        var isSelected = $btn.hasClass('datagrid-toolbar-selected');

                        if (isSelected) {
                            $btn.removeClass('datagrid-toolbar-selected');
                            $label.text('Include Resigned').css('color', '');
                            $('#checkrsgnd').prop('checked', false);
                        } else {
                            $btn.addClass('datagrid-toolbar-selected');
                            // $label.text('Showing Resigned').css('color', '#0d6efd');
                            $label.text('Showing Resigned').css('color', 'red');
                            $('#checkrsgnd').prop('checked', true);
                        }

                        // ✅ Call your existing filter function
                        showrsgnd();
                    }
                },

                // End
            ],
            fitColumns:true,
                    pageList: [2, 5, 10, 50, 100],
            columns: [
                [
                    {field: 'avatar', title: 'Image', width: "5%", sortable: true},
                    {field: 'emp_company_id', title: 'Employee ID', width: "15%", sortable: true},
                    {field: 'name', title: 'Full Name', width: "20%", sortable: true},
                    {field: 'desig_name', title: 'Designation', width: "20%", sortable: true},
                    {field: 'joining_date', title: 'Joined Date', width: "15%", sortable: true},
                    {field: 'branch_name', title: 'Branch Name', width: "15%", sortable: true},
                    {field: 'buttons', title: 'Doc', width: "8%", sortable: true}
                ]
            ],
            onSearch: function (s) {
             clearTimeout(searchTimer);
             searchTimer = setTimeout(function() {
                $('#emptable').datagrid('load', {
                    emp: $('#searchqupo').val(),
                    name: $('#rsndempid').val(),
                    branch: $('#filterby_branch').val()
                });
              }, 1000);
            },
            onLoadSuccess: function () {
                setwidth();
            }
        });


    });
    
 /* edited by bindu 19-02-26 */
   $(".home").on("click", function () {

    $("#container").isLoading({
        text: "Loading",
        position: "overlay",
    });

    let url = "";
     var userGroup = <?php echo json_encode($this->Session->read('user_group')); ?>

    if (userGroup == "1") {
        url = livesite + "EmployeeManage/index";
    } 
    else if (userGroup == "2") {
        url = livesite + "EmployeeMenu/addon";
    }

    $("#container").load(url, function () {
        isDashboardShown = false;
    });

});

	/* edited by bindu 19-02-26 */
</script>