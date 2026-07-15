<style>
    @keyframes FadeIn {
        from {
            background-color: #7bebbd;
        }
        to {
            background-color: white;
        }
    }
    .FoodConsumptionTable tr {
        background-color: white;
        animation: FadeIn 1.75s ease-in-out forwards;
    }
</style>
<script>

    var options = {
        success: function (resp) {
            $('#largeModalForm').modal('hide');
            $('#att_table').datagrid('reload');
            $.notify("Success", {
                type: 'success',
                allow_dismiss: false
            });
        }  // post-submit callback
    };
    $('#itemform').on('submit', function (event) {
        event.preventDefault();
        if (confirm("Do You Want To Save The Form")) {
            $('#itemform').ajaxSubmit(options);
        }

    });
</script>
<div class="modal-dialog" style="width: 100%; ">
    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title" style="    margin: 5px 28px 0; "><b>PROJECT MASTER</b> </h4>
        </div>
        <div class="modal-body">
            <!-- Form starts -->
            <div id="empsetup-save-response" class="">
                <form class="form-horizontal"  method="post" action="<?php echo $this->webroot; ?>Project/saveProject" id="itemform" name="itemform">
                    <!--  <ul class="nav nav-tabs" role="tablist" >
                         <li role="presentation" class="active">
                         <a href="#div-itemdetails" aria-controls="div-empsetuppersonal" role="tab" data-toggle="tab" id="items">Project Details</a>
                         </li>
                         <li role="presentation">
                         <a href="#div-quantitydetails" id="contct_details" aria-controls="div-empsetupprofessional" role="tab" data-toggle="tab">Contacts Details</a>
                         </li>
                         <li role="presentation">
                             <a href="#div-policy" aria-controls="div-policy" id="policy_tab" role="tab" data-toggle="tab">Add Policy </a>
                         </li>
 
                     </ul> -->

                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="div-itemdetails" name="itemdetails">

                            <div class="modal-body">
                                <input type="hidden" value="<?php echo isset($arr_sites['0']['site']['site_pkey']) ? $arr_sites['0']['site']['site_pkey'] : ''; ?>" id="item_master_pkey" name="site_pkey">    
                                <input type="hidden" value="<?php echo isset($arr_sites['0']['site']['organization_id']) ? $arr_sites['0']['site']['organization_id'] : ''; ?>" id="organization_id" name="organization_id">    

                                <div class="form-group form-group-sm">
                                    <div class="col-sm-6">
                                        <label class="col-sm-5 control-label" for="item_desc">Project ID<lable style="color:red">*</lable></label>
                                        <div class="col-sm-7">
                                            <input class="form-control" placeholder="Please Enter Project ID" type="text" value='<?php echo isset($arr_sites["0"]["site"]["site_id"]) ? $arr_sites["0"]["site"]["site_id"] : ""; ?>' name="project_id" id="project_id" required="required" onchange="checkIfProjectIdExists();">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="col-sm-5 control-label" >Project Name<lable style="color:red">*</lable></label>
                                        <div class="col-sm-7">
                                            <input class="form-control" placeholder="Please Enter Project Name" type="text" value="<?php echo isset($arr_sites['0']['site']['site_name']) ? $arr_sites['0']['site']['site_name'] : ''; ?>" name="project_name" id="project_name" required="required" >
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group form-group-sm">
                                    <div class="col-sm-6">
                                        <label class="col-sm-5 control-label" for="item_desc">Project Details</label>
                                        <div class="col-sm-7">
                                            <input class="form-control" placeholder="Enter Project Details" type="text" value='<?php echo isset($arr_sites["0"]["site"]["address"]) ? $arr_sites["0"]["site"]["address"] : ""; ?>' name="address" id="address"  >
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="col-sm-5 control-label" for="item_desc">Project Management Consultant</label>
                                        <div class="col-sm-7">
                                            <input class="form-control" placeholder="Enter Project Management Consultant" type="text" value='<?php echo isset($arr_sites["0"]["site"]["special_remarks"]) ? $arr_sites["0"]["site"]["special_remarks"] : ""; ?>' name="special_remarks" id="special_remarks" >
                                        </div>
                                    </div>
                                    <div class="col-sm-6"> 
                                        <label class="col-sm-5 control-label" for="branch">Branch</label>
                                        <div class="col-sm-7">
                                            <select class="form-control" name="branch_code" id="branch_code" required="required">
                                                <option value="">Select</option>
                                                <?php
                                                if (isset($arr_branches)) {
                                                    foreach ($arr_branches as $val) {
                                                        ?>
                                                        <option <?php echo (isset($arr_sites["0"]["site"]['branch_code']) && ($val['branches']['branch_code'] == $arr_sites["0"]["site"]['branch_code'])) ? 'selected="selected"' : ''; ?> value="<?php echo $val['branches']['branch_code']; ?>"><?php echo $val['branches']['branch_name']; ?></option>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <legend><h3>Work Order Details  </h3></legend>
                                <div class="form-group form-group-sm">
                                    <div class="col-sm-6">
                                        <label class="col-sm-5 control-label" for="item_desc">Work Order No.</label>
                                        <div class="col-sm-7">
                                            <input class="form-control" placeholder="Please Enter Work Order No." type="text" value='<?php echo isset($arr_sites["0"]["site"]["latitude"]) ? $arr_sites["0"]["site"]["latitude"] : ""; ?>' name="latitude" id="latitude" >
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="col-sm-5 control-label" for="item_desc">Work Order Value</label>
                                        <div class="col-sm-7">
                                            <input class="form-control" placeholder="Please Enter Work Order Value" type="number" step="0.01" value='<?php echo isset($arr_sites["0"]["site"]["longitude"]) ? $arr_sites["0"]["site"]["longitude"] : ""; ?>' name="longitude" id="longitude">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group form-group-sm ">
                                    <div class="col-sm-6">
                                        <label class="col-sm-5 control-label" for="item_desc">Work Details</label>
                                        <div class="col-sm-7">
                                            <input class="form-control" placeholder="Please Enter Work Details" type="text" value='<?php echo isset($arr_sites["0"]["site"]["work_details"]) ? $arr_sites["0"]["site"]["work_details"] : ""; ?>' name="work_details" id="work_details">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="col-sm-5 control-label" for="item_desc">Work Order Date</label>
                                        <div class="col-sm-7">
                                            <input class="form-control" placeholder="Enter Work Order Date" type="text" value='<?php echo isset($arr_sites["0"]["site"]["po_expirydate"]) ? $arr_sites["0"]["site"]["po_expirydate"] : ""; ?>' name="work_order_date" id="work_order_date" readonly >
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group form-group-sm">
                                    <div class="col-sm-6">
                                        <label class="col-sm-5 control-label" for="item_desc">Date of Commencement (As per WO)</label>
                                        <div class="col-sm-7">
                                            <input class="form-control" placeholder="Enter Date of Commencement" type="text" value='<?php echo isset($arr_sites["0"]["site"]["expected_starting_date"]) ? $arr_sites["0"]["site"]["expected_starting_date"] : ""; ?>' name="date_commencement_wo" id="date_commencement_wo" readonly >
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="col-sm-5 control-label" for="item_desc">Date of Commencement (Actual)</label>
                                        <div class="col-sm-7">
                                            <input class="form-control" placeholder="Enter Date of Commencement" type="text" value='<?php echo isset($arr_sites["0"]["site"]["actual_starting_date"]) ? $arr_sites["0"]["site"]["actual_starting_date"] : ""; ?>' name="date_commencement_actual" id="date_commencement_actual" readonly >
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group form-group-sm">
                                    <div class="col-sm-6">
                                        <label class="col-sm-5 control-label" for="item_desc">Date of Completion (As per WO)</label>
                                        <div class="col-sm-7">
                                            <input class="form-control" placeholder="Enter Date of Completion" type="text" value='<?php echo isset($arr_sites["0"]["site"]["expected_compleation_date"]) ? $arr_sites["0"]["site"]["expected_compleation_date"] : ""; ?>' name="date_completion_wo" id="date_completion_wo" readonly >
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="col-sm-5 control-label" for="item_desc">Date of Completion (Actual)</label>
                                        <div class="col-sm-7">
                                            <input class="form-control" placeholder="Enter Date of Completion" type="text" value='<?php echo isset($arr_sites["0"]["site"]["actual_completion_date"]) ? $arr_sites["0"]["site"]["actual_completion_date"] : ""; ?>' name="date_completion_actual" id="date_completion_actual" readonly >
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group form-group-sm">
                                    <div class="col-sm-6">
                                        <label class="col-sm-5 control-label" for="item_desc">Amount Utilised till Date</label>
                                        <div class="col-sm-7">
                                            <input class="form-control" placeholder="Enter Amount Utilised till Date" type="text" value='<?php echo isset($arr_sites["0"]["site"]["allocated_fund"]) ? $arr_sites["0"]["site"]["allocated_fund"] : ""; ?>' name="amountutilised" id="amountutilised">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="col-sm-5 control-label" for="item_desc">Amount Released so far</label>
                                        <div class="col-sm-7">
                                            <input class="form-control" placeholder="Enter Amount Released so far" type="text" value='<?php echo isset($arr_sites["0"]["site"]["released_fund"]) ? $arr_sites["0"]["site"]["released_fund"] : ""; ?>' name="amountreleased" id="amountreleased">
                                        </div>
                                    </div>
                                </div>
                                <legend><h3>Contact Person Details  </h3></legend>
                                <div class="form-group form-group-sm">
                                    <div class="col-sm-6">
                                        <label class="col-sm-5 control-label" for="item_desc">Project Manager</label>
                                        <div class="col-sm-7">
                                            <input style="width: 207px;" class="form-control" placeholder="Type to Search Employee" id="hierarch" name="hierarch1" type="text" value='<?php echo isset($arr_sites["0"]["0"]["manager"]) ? $arr_sites["0"]["0"]["manager"] : ""; ?>' name=""  >
                                            <input type="hidden" id="hierarch1" name="user_pkey"  class="form-control strict-field" value="<?php echo isset($arr_sites["0"]["site"]['user_pkey']) ? $arr_sites["0"]["site"]['user_pkey'] : ''; ?>" style="width:234px; ">
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <label class="col-sm-5 control-label" for="item_desc">Site Engineer</label>
                                        <div class="col-sm-7">
                                            <input style="width: 207px;" class="form-control" placeholder="Type to Search Employee" id="siteengineer" name="siteengineer" type="text" value='<?php echo isset($arr_engineer["0"]["0"]["engineer"]) ? $arr_engineer["0"]["0"]["engineer"] : ""; ?>'   >
                                            <input type="hidden" id="siteengineer1" name="siteengineer1"  class="form-control strict-field" value="<?php echo isset($arr_sites["0"]["site"]['contact_name']) ? $arr_sites["0"]["site"]['contact_name'] : ''; ?>" style="width:234px; ">
                                        </div>
                                    </div>
                                </div>


                                <!-- <div class="form-group form-group-sm">
                                    <label class="col-sm-5 control-label" for="item_category">Item Category</label>
                                        <div class="col-sm-7">
                                            <select id="item_category" class="form-control" name="item_category">
                                 <option value="" >---select---</option> 
                                <?php
                                foreach ($all_category as $value) {
                                    //debug($value);
                                    $selected = ($arr_att['0']['itemmaster']['item_category'] == $value['CategoryMaster']['category_pkey']) ? 'selected="selected"' : '';
                                    echo '<option value="' . $value['CategoryMaster']['category_pkey'] . '" ' . $selected . '>' . $value['CategoryMaster']['description'] . '  </option>';
                                }
                                ?>
                                                                </select>
                                                            </div>
                                                        </div>-->





                            </div>

                            <!--    <div class="pull-right" >
                            <!--<a onclick="next();" class="btn btn-default " ><li class="fa fa-step-backward "></li></a>
                            <a onclick="next(2);" class="btn btn-default " > Next &nbsp; &nbsp; <li class="fa fa-step-forward "></li></a>
                        </div>-->
                        </div>

                        <!--  <div role="tabpanel" class="tab-pane" id="div-quantitydetails" name="quantitydetails">
  
  
                              <div class="modal-body">
  
                                  
                                  <div style="
                                       padding: 23px 2px 11px 5px; ">
  
                                      <div class="form-group form-group-sm">
                                          <div class="col-sm-6">
                                              <label class="col-sm-5 control-label" for="item_desc">Organization <lable style="color:red">*</lable></label>
                                              <div class="col-sm-7">
                                                  <select class="form-control" name="contact_name" id="contact_name" onclick="updatephonenumer(this);" >
                                                      <option value="">Select</option>
                        <?php foreach ($arr_contacts as $val) { ?>
                                                                  
                                                                  <option <?php echo (isset($arr_sites["0"]["contacts"]['contact_id']) && ($val['contacts']['contact_id'] == $arr_sites["0"]["contacts"]['contact_id'])) ? 'selected="selected"' : ''; ?> data-foo="<?php echo $val['contacts']['phone']; ?>" value="<?php echo $val['contacts']['contact_id']; ?>"><?php echo $val['contacts']['company_name']; ?></option>
                        <?php } ?>
                                                  </select>
                                              </div>
                                          </div>
                                          <div class="col-sm-6">
                                              <label class="col-sm-5 control-label" for="item_desc">Customer No.</label>
                                              <div class="col-sm-7">
                                                  <input readonly="readonly" class="form-control" placeholder="Contact Person Number" type="text" value='<?php echo isset($arr_sites["0"]["contacts"]['phone']) ? $arr_sites["0"]["contacts"]['phone'] : ""; ?>' name="customer_contact" id="customer_contact_no"  >
                                              </div>
                                          </div>
                                      </div>
                                      
                                     
  
                                  </div>
  
  
  
                                  <div class="pull-right" style="margin-top: 34px; ">
                                      <a onclick="next(1);" class="btn btn-default " > Back&nbsp; &nbsp; <li class="fa fa-step-backward "></li></a>
                                      <a onclick="next(3);" class="btn btn-default " >Next&nbsp; &nbsp; <li class="fa fa-step-forward "></li></a>
                                  </div>
                              </div>
  
  
                          </div>
                          
                          <div role="tabpanel" class="tab-pane" id="div-policy" name="policy">
                              <div class="col-md-12" style="    margin-top: 29px; ">
  
                              <button type="button" class="btn btn-primary" onclick="loadform();"><li class="fa fa-plus-square-o"></li></button> <label class=""> Add Shift</label>
                              <div style="padding-top: 34px;margin-top: 21px;background: #eee;" id="form2">
  
                              </div>
   
                              <div id="loadDetails">
                                 
                              </div>
                              <div class="modal-footer">
                                  <input type="hidden" value="" id="store_master_pkey" name="store_master_pkey">   
                                  <a onclick="nextback(1);" class="btn btn-default " > Back&nbsp; &nbsp; <li class="fa fa-step-backward "></li></a>
                                  <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                                  <button type="submit" id="btn-submit" class="btn btn-primary">Save</button>
                              </div>
                          </div>
                      </div> -->
                        <div class="modal-footer">
                            <input type="hidden" value="" id="store_master_pkey" name="store_master_pkey">   
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                            <button type="submit" id="btn-submit" class="btn btn-primary">Save</button>
                        </div>



                </form>
                <!-- Tax Head Detail Form -->

            </div>



            <!-- form ends-->
        </div>

    </div>

</div>
<script>
    function loadform() {
        $('#form2').load(livesite + 'Project/form2/');
    }
    function deletetableold(s) {
        $(s).parent().parent().css("display", "none");
        $(s).parent().parent().find('.statuses').val('0');
    }
    function editpolicydetails(s) {
        var siteid = $(s).parent().parent().find('#siteid').val();
        //alert(siteid);
        $('#form2').load(livesite + 'Project/form2/' + siteid);
    }
    function updatephonenumer(s) {
        var selected = $(s).find('option:selected');
        var mobile_number = selected.data('foo');
        $('#customer_contact_no').val(mobile_number);
    }
    function nextback(s) {
        $('#contct_details').trigger('click');
    }
    function next(s) {
        if (s == 1) {
            $('#items').trigger('click');
        } else if (s == 2) {
            if ($('#project_name').val() == '') {
                if ($("#project_name").next(".validation").length == 0) // only add if not added
                {
                    $('#project_name').parent().append("<div class='validation' style='color:red;'>Please Enter Project Name </div>");
                }
                return false;
            } else {
                $("#project_name").next(".validation").hide();
            }
            if ($('#project_id').val() == '') {
                if ($("#project_id").next(".validation").length == 0) // only add if not added
                {
                    $('#project_id').parent().append("<div class='validation' style='color:red;'>Please Enter Project ID </div>");
                }
                return false;
            } else {
                $("#project_id").next(".validation").hide();
            }
            /*    if($('#latitude').val() == ''){
             if ($("#latitude").next(".validation").length == 0) // only add if not added
             {
             $('#latitude').parent().append("<div class='validation' style='color:red;'>Please Enter Work Order No. </div>");
             }
             return false;
             }else{
             $("#latitude").next(".validation").hide();
             }
             if($('#longitude').val() == ''){
             if ($("#longitude").next(".validation").length == 0) // only add if not added
             {
             $('#longitude').parent().append("<div class='validation' style='color:red;'>Please Enter Work Order Value </div>");
             }
             return false;
             }else{
             $("#longitude").next(".validation").hide();
             }
             if($('#address').val() == ''){
             if ($("#address").next(".validation").length == 0) // only add if not added
             {
             $('#address').parent().append("<div class='validation' style='color:red;'>Please Enter Project Details </div>");
             }
             return false;
             }else{
             $("#address").next(".validation").hide();
             }
             if($('#special_remarks').val() == ''){
             if ($("#special_remarks").next(".validation").length == 0) // only add if not added
             {
             $('#special_remarks').parent().append("<div class='validation' style='color:red;'>Please Enter Project Management Consultant </div>");
             }
             return false;
             }else{
             $("#special_remarks").next(".validation").hide();
             }
             if($('#hierarch1').val() == ''){
             if ($("#hierarch1").next(".validation").length == 0) // only add if not added
             {
             $('#hierarch1').parent().append("<div class='validation' style='color:red;'>Please Enter Project Manager </div>");
             }
             return false;
             }else{
             $("#hierarch1").next(".validation").hide();
             }
             if($('#siteengineer1').val() == ''){
             if ($("#siteengineer1").next(".validation").length == 0) // only add if not added
             {
             $('#siteengineer1').parent().append("<div class='validation' style='color:red;'>Please Enter Site Engineer </div>");
             }
             return false;
             }else{
             $("#siteengineer1").next(".validation").hide();
             }
             if($('#work_order_date').val() == ''){
             if ($("#work_order_date").next(".validation").length == 0) // only add if not added
             {
             $('#work_order_date').parent().append("<div class='validation' style='color:red;'>Please Enter Work Order Date </div>");
             }
             return false;
             }else{
             $("#work_order_date").next(".validation").hide();
             }*/
//            if($('#hierarch').val() == ''){
//                if ($("#hierarch").next(".validation").length == 0) // only add if not added
//                 {
//                $('#hierarch').parent().append("<div class='validation' style='color:red;'>Please enter Site Manager  </div>");
//                
//                 }
//                 return false;
//            }else{
//                $("#hierarch").next(".validation").hide();
//            }
            $('#contct_details').trigger('click');
        } else {
            if ($('#contact_name').val() == '') {
                if ($("#contact_name").next(".validation").length == 0) // only add if not added
                {
                    $('#contact_name').parent().append("<div class='validation' style='color:red;'>Please Select Site Manager </div>");
                }
                return false;
            } else {
                $("#contact_name").next(".validation").hide();
            }
            if ($('#customer_contact_no').val() == '') {
                if ($("#customer_contact_no").next(".validation").length == 0) // only add if not added
                {
                    $('#customer_contact_no').parent().append("<div class='validation' style='color:red;'>Please enter Customer Number </div>");
                }
                return false;
            } else {
                $("#customer_contact_no").next(".validation").hide();
            }
            if ($('#customer_name').val() == '') {
                if ($("#customer_name").next(".validation").length == 0) // only add if not added
                {
                    $('#customer_name').parent().append("<div class='validation' style='color:red;'>Please enter Contact Name </div>");

                }
                return false;
            } else {
                $("#customer_name").next(".validation").hide();
            }
            if ($('#customer_contacts').val() == '') {
                if ($("#customer_contacts").next(".validation").length == 0) // only add if not added
                {
                    $('#customer_contacts').parent().append("<div class='validation' style='color:red;'>Please enter Contact Number </div>");

                }
                return false;
            } else {
                $("#customer_contacts").next(".validation").hide();
            }
            $('#policy_tab').trigger('click');
        }
    }
    $(document).ready(function () {
        var usershierarchyoptions = {
            url: function (phrase) {
                var emp = $('#empsetuppersonal #emp_pkey').val();
                return livesite + "Employee/getautohierarchycompletionsvgfs?username=" + phrase + "&emp=" + emp;
                ;
            },
            getValue: "emp_name",
            list: {
                onClickEvent: function () {
                    var selectedItem = $('#hierarch').getSelectedItemData();
                    var site_pkey = selectedItem.emp_pkey;
                    $('#hierarch1').val(site_pkey);
                },
                onKeyEnterEvent: function () {
//                filterAttendanceautocomplete($('#hid_filterby_employees').val());
                },
                onSelectItemEvent: function () {
//                var selectedItem = $('#hierarch').getSelectedItemData();
//                var site_pkey = selectedItem.emp_pkey;
//                $('#hierarch1').val(site_pkey);
                }
            }
        };
        $('#hierarch').easyAutocomplete(usershierarchyoptions);
        $('#hierarch').on('keydown', function () {
            $('#hierarch1').val('');
        });
        var useroptions = {
            url: function (phrase) {
                var emp = $('#empsetuppersonal #emp_pkey').val();
                return livesite + "Employee/getautohierarchycompletionsvgfs?username=" + phrase + "&emp=" + emp;
                ;
            },
            getValue: "emp_name",
            list: {
                onClickEvent: function () {
                    var selectedItem = $('#siteengineer').getSelectedItemData();
                    var site_pkey = selectedItem.emp_pkey;
                    $('#siteengineer1').val(site_pkey);
                },
                onKeyEnterEvent: function () {
//                filterAttendanceautocomplete($('#hid_filterby_employees').val());
                },
                onSelectItemEvent: function () {
//                var selectedItem = $('#siteengineer').getSelectedItemData();
//                var site_pkey = selectedItem.emp_pkey;
//                $('#siteengineer1').val(site_pkey);
                }
            }
        };
        $('#siteengineer').easyAutocomplete(useroptions);
        $('#siteengineer').on('keydown', function () {
            $('#siteengineer1').val('');
        });
        $('#start_date').datepicker({
            dateFormat: 'yyyy-mm-dd'
        });
        $('#end_date').datepicker({
            dateFormat: 'yyyy-mm-dd'
        });
        $('#manu_date').datepicker({
            dateFormat: 'yyyy-mm-dd'
        });
        $('#date_commencement_wo').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });

        $('#date_commencement_actual').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });
        $('#date_completion_wo').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });

        $('#date_completion_actual').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });
        $('#work_order_date').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });


    });
    function checkIfProjectIdExists(callback) {
        var project_id = $('#project_id').val();
        var pkey = $('#item_master_pkey').val();

        $.ajax({
            url: 'Project/checkprojectexists/',
            type: 'POST',
            data: {
                project_id: project_id,
                pkey: pkey
            },
            success: function (resp)
            {
                if (resp > 0) {
                    //$.notify("Project ID. Already Exists!!",{              
                    //  type: 'danger',
                    // allow_dismiss: false
                    // });
                    alert("Project ID. Already Exists!!");
                    $('#project_id').val('');
                } else {
                    if (typeof callback === 'function') {
                        callback.call();
                    }
                }
            }
        });
    }
</script>