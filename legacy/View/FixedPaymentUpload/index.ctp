

<section class="content-header">
    <h1 class="text-primary-18"> Fixed Payment Upload </h1><!--BY ******ARUL P DAS on 07/11/2019******-->
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- DIRECT CHAT DANGER -->
            <div class="box ">
                <div class="box-header with-border">
                    <!-- <h3 class="box-title"> Fixed Payment Upload </h3> -->
                </div><!-- /.box-header -->
                <div class="box-body">
                    <!-- Employee import form -->
                    <form class="form-horizontal" method="post" action="" id="importfixedpaymentform">
                        <!-------There are three container divisions for select options. By ARUL P DAS on 07/11/2019-------->
                        <div class="form-group"><!---First select option container--->
                            <div class="col-md-4">
                                <label class="col-md-5" for="salary_head_item">Salary Head Item </label>
                                <div class="col-md-7">
                                    <select id="salary_head_item" name="salary_head_item" class="form-control" onchange="filterFixedPaymentupload(this);" >
                                        <option value="">---Select---</option>
                                        <?php foreach ($arr_headitems as $key => $value) { ?>                              
                                            <option  value="<?php echo $value['SalaryHeadItems']['salary_head_item_pkey']; ?>"><?php echo $value['SalaryHeadItems']['item']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="start_month" class="col-sm-5 ">Start Month</label>
                                <div class="col-md-7">
                                    <input type="text" class="form-control dateCalender" placeholder="Start Month" value="" name="start_month" id="start_month" autocomplete="off"><!--Here changed the filter function By **ARUL P DAS on 7.12.19-->
                                </div>
                            </div>
                            <div class="col-md-4">
                                <!-- <label for="end_month" class="col-sm-5 ">End Month</label>
                                <div class="col-md-7">
                                    <input type="text" class="form-control dateCalender"  placeholder="Ending Month" value="" name="end_month" id="end_month" onchange="filterFixedPaymentupload(this);" autocomplete="off">
                                </div> -->
                                &nbsp;
                            </div>
                        </div>
                        <!-----------------Here separates the container division------------------>
                        <div class="form-group"><!---Second select option container--->
                            <div class="col-md-4">
                                <label class="col-sm-5" for="filterby_branch">Choose Branch</label>
                                <div class="col-md-7">
                                    <select id="filterby_branch" name="filterby_branch" class="form-control" onchange="filterFixedPaymentupload(this);" >
                                        <option value="">All</option>
                                        <?php foreach ($arr_branches as $key => $value) { ?>                              
                                            <option  value="<?php echo $value['Units']['branch_code']; ?>"><?php echo $value['Units']['branch_name']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div> 
                            <div class="col-md-4">
                                <label class="col-sm-5" for="emp_fkey">Choose Employee</label>                        
                                <div class="col-md-7">
                                    <select id="emp_fkey" class="form-control js-example-basic-single" name="emp_fkey" onchange="filterFixedPaymentupload(this);" >
                                        <option value="">All</option>
                                        <?php 
                                        //***emp_pkey replaced with emp_company_id in the employee list. By ARUL P DAS on 05-11-2019***
                                            foreach ($emp_list as $key => $value) {
                                            ?>
                                                <option value="<?php echo $value['emp_details']['emp_pkey'];?>"><?php echo $value['emp_details']['first_name'].' '.$value['emp_details']['last_name'].' - '.$value['emp_proff']['emp_company_id'];?></option>
                                            <?php
                                            }
                                        ?>
                                    </select>
                                    <span id="temp"></span>
                                </div>    
                            </div>
                            <div class="col-md-4">
                                <label class="col-sm-5" for="occurance">Occurrence</label>                        
                                <div class="col-md-7">
                                    <select id="occurance" class="form-control" name="occurance" onchange="filterFixedPaymentupload(this);" >
                                        <option value="">---Select---</option>
                                        <option value="3">Monthly</option>
                                        <option value="4">Bi-Monthly</option>
                                        <option value="5">Quarterly</option>
                                        <option value="2">Half-Yealy</option>
                                        <option value="1">Yealy</option>
                                    </select>
                                </div>    
                            </div>   
                        </div>
                        <!-----------------Here separates the container division------------------>
                        <div class="form-group"><!---Third select option container--->
                            <div class="col-md-4">
                                <label class="col-md-5 control-label" for="fixedpaymentcsv">Choose file</label>
                                <div class="col-md-7">
                                    <input id="fixedpaymentcsv" name="fixedpaymentcsv" type="file" class="form-control input-md" >
                                </div>
                            </div>
                            <div class="col-md-4">
                                 <label class="col-md-5 control-label" for="empvarcsv"></label>
                                <div class="col-md-7">
                                    <button type="button" id="btn-uploademployeectc" class="btn btn-success col-md-4" onclick="uploadFixedPaymentForm();"><li class="fa fa-upload"></li></button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                 <label class="col-md-5 control-label" for="empvarcsv"></label>
                                 <div class="col-md-7">
                                    <button type="button" id="btn-downloademployeectcform" class="btn btn-danger col-md-4" onclick="downloadFixedPaymentUploadForm();" ><li class="fa fa-download"></li></button> 
                                 </div>
                            </div>
                        </div>
                    </form>
                    <!----The below division displays the all fixed payment uploads of employees By ***ARUL P DAS---->
                    <div class="box box-primary">
                        <br>
                        <input type="hidden" id="rsndempid" value="0" name="resigned">
                        <table id="att_table" class="table table-bordered table-hover" style="max-height: 500px;">
                            <tbody>
                            </tbody>
                        </table>
                    </div><!-- /.box-body -->
                </div>

            </div>
        </div>
    </div>
</section>

<script>
$(document).ready(function(){
    $('.dateCalender').datepicker({
     format: 'yyyy-mm',
        autoclose: true,
        startView: "months",
        minViewMode: "months"
    
    });

    //The below code is to check whether start month is empty when selecting end month. 
    //Or end month is greater than start month. By ***ARUL P DAS on 3/12/2019
    $('#importfixedpaymentform #end_month').change(function(){
        var end_month=$('#importfixedpaymentform #end_month').val();
        var start_month=$('#importfixedpaymentform #start_month').val();
        if(start_month=='' || start_month==null){
            $.notify('Please choose Start Month first',{
                type: 'danger',
                allow_dismiss: false
            });
            $('#importfixedpaymentform #end_month').val("");
            return false;
        }
        if (end_month<start_month) {
            $.notify('End month should greater than start month',{
                type: 'danger',
                allow_dismiss: false
            });
            $('#importfixedpaymentform #end_month').val("");
            return false;
        }
    });

    //The below code is to check whether start month is greater than end month. By ***ARUL P DAS on 3/12/2019
    $('#importfixedpaymentform #start_month').change(function(){
        var end_month=$('#importfixedpaymentform #end_month').val();
        var start_month=$('#importfixedpaymentform #start_month').val();
        if(end_month=="" || end_month==null){}
        else{
            if (end_month<start_month) {
                $.notify('Start month should less than end month',{
                    type: 'danger',
                    allow_dismiss: false
                });
                $('#importfixedpaymentform #start_month').val("");
                return false;
            }
        }
    });


    //The below code is used to fetch the employee list of choosen branch... BY ***ARUL P DAS on 3/12/2019
    $('#importfixedpaymentform #filterby_branch').change(function(){
        var branch = $('#importfixedpaymentform #filterby_branch').val();
        if (branch=='' || branch==null) {branch='ALL';}//This to set back the employee list to all branches.
        $.ajax({
            url: livesite + "FixedPaymentUpload/branchemployee/"+branch,
            success: function (response) {
                var result='';
                var resp = $.parseJSON(response);
                if (resp.msg>0) {
                    result+='<option value="">All</option>';
                    for(var i=0; i<resp.value.length; i++){
                        result+='<option value="'+resp.value[i].emp_details.emp_pkey+'">'+resp.value[i].emp_details.first_name+' '+resp.value[i].emp_details.last_name+' - '+resp.value[i].emp_proff.emp_company_id+'</option>';
                    }               
                    $('#emp_fkey').html(result);
                }else{
                    result="<option></option>";
                    $('#emp_fkey').html(result);
                }
            }
        });
    });
});
    function filterFixedPaymentupload(obj) {
        var branch = $('#importfixedpaymentform #filterby_branch').val();
        var employee = $('#importfixedpaymentform #emp_fkey').val()
        // var start_month=$('#importfixedpaymentform #start_month').val()//This is avoided by By ***ARUL P DAS
        // var end_month=$('#importfixedpaymentform #end_month').val()//This is avoided by By ***ARUL P DAS
        if (employee==0 || employee=="all") {
            employee="";
        }
        var salhead = $('#importfixedpaymentform #salary_head_item').val()
        var occurance = $('#importfixedpaymentform #occurance').val()


        $('#att_table').datagrid('load', {
            branch: branch,
            employee: employee,
            // start_month: start_month,//This is avoided by By ***ARUL P DAS
            // end_month: end_month,//This is avoided by By ***ARUL P DAS
            salhead: salhead,
            occurance: occurance,
            name: $('#rsndempid').val(),
        });

    }

    function downloadFixedPaymentUploadForm() {
        var salary_head_item = $('#salary_head_item').val();
        if(salary_head_item==""){
            alert('Please select Salary Head Item');
            return false;
        }
        var occurance = $('#occurance').val();
        if(occurance==""){
            alert('Please select Occurrence');
            return false;
        }
        var filterby_branch = $('#filterby_branch').val();
        if (filterby_branch=="" || filterby_branch==null) {filterby_branch="ALL";}//This filter added by **ARUL P DAS
        var start_month = $('#start_month').val();
        var end_month = $('#end_month').val();
        if(end_month!="" || end_month!=null){
            if (start_month=="" || start_month==null) {
//                $.notify('Please select Start Month',{
//                    type:'danger',
//                    allow_dismiss: false
//                });
                alert('Please select Start Month');
                return false;
            }
        }
        var emp_fkey=$('#emp_fkey').val();
        if(emp_fkey=="" || emp_fkey==null || emp_fkey==0){emp_fkey="ALL";}//This is filter by **ARUL P DAS

        // alert(filterby_branch);
        // return false;

        if (salary_head_item != "" & occurance!="") {
            window.open('<?php echo $this->webroot; ?>FixedPaymentUpload/downloadfixedpaymentuploadform/' + salary_head_item + '/' + filterby_branch + '/' + occurance + '/' + emp_fkey + '/' + start_month);
        } else {
            alert("Must select Salary head item and Occurrence");
            return false;
        }
    }
    function uploadFixedPaymentForm() {
        var form = $('#importfixedpaymentform');
        var fileSelect = document.getElementById('fixedpaymentcsv');
        if(fileSelect.value=="" || fileSelect.value==null){
            alert('Please select any file');
            return false;
        }
        var salary_head_item = $('#salary_head_item').val();
        if (salary_head_item != "") {
         } else {
            alert("Select head item");
            return false;
        }
        var salary_head_item = $('#salary_head_item').val();
        var occurance = $('#occurance').val();
        if(occurance=="" || occurance==null){
               alert('Please select Occurrence');
               return false;
           }

        var month_year = $('#start_month').val();
        if(month_year=="" || month_year==null){
            alert('Please select start month');
            return false;
        }
        // alert(month_year);
        var end_year = $('#end_month').val();
        // The rest of the code will go here...
        var files = fileSelect.files;
        // Create a new FormData object.
        var formData = new FormData();
        // Loop through each of the selected files.

        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            // Add the file to the request.
            formData.append('fixedpayment[]', file, file.name);
        }

        // Set up the request.
        var xhr = new XMLHttpRequest();

        // Open the connection.
        xhr.open('POST', livesite + 'FixedPaymentUpload/uploadandsavefixedpayment/' + salary_head_item + '/' + month_year + '/' +occurance, true);

        // Set up a handler for when the request finishes.
        xhr.onload = function () {
            if (xhr.status === 200) {
                // File(s) uploaded.
				$('#fixedpaymentcsv').val("");
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    $.notify(response.msg, {
                        type: 'success',
                        allow_dismiss: false
                    });
                    $('#att_table').datagrid('reload');
                } else {
                    if(response.msg=="paryroll_issue"){
                        var msg="Salary is already processed.\n\n";
                        var i=0;
                        var sl=0;
                        for (i=0; i < response.items.length; i++) { 
                            sl=i+1;
                            msg+=sl+"."+response.items[i]['name']+" - "+response.items[i]['emp_fkey']+"\n";
                        }
                        alert(msg);
                    }else{
                        $.notify(response.msg, {
                            type: 'error',
                            allow_dismiss: false
                        });
                    }
                    $('#att_table').datagrid('reload');
                }
            } else {
                alert("Employee CTC import failed, please check informations given or try again.");
            }
        };

        // Send the Data.
        xhr.send(formData);
    }

    function setwidth(){
        if ($('#checkrsgnd').length == 0) {
        // exists.
           // $('.datagrid-toolbar').find('tr').append('<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name"rsgnemp" id="checkrsgnd" onclick="showrsgnd();" value="0">Include Removed </td>');
   
        }
    }
    function showrsgnd(){
        var branch = $('#importfixedpaymentform #filterby_branch').val();
        var employee = $('#importfixedpaymentform #emp_fkey').val()
        var salhead = $('#importfixedpaymentform #salary_head_item').val()
        var occurance = $('#importfixedpaymentform #occurance').val()
        if($('#checkrsgnd').is(":checked")){
            $('#rsndempid').val('1');
        }else{
            $('#rsndempid').val('0');
        }
        $('#att_table').datagrid('load', {
            branch: branch,
            employee: employee,
            salhead: salhead,
            occurance: occurance,
            name: $('#rsndempid').val(),
        }); 
        // alert(name);
    }

    jQuery(document).ready(function () {
        var employee = $('#importfixedpaymentform #emp_fkey').val();

        $('#emp_fkey').select2();
        
        $('#att_table').datagrid({
            url: livesite + "FixedPaymentUpload/employeelistfixedpayment",
            pagination: true,
            singleSelect: true,
            rownumbers: true,
            queryParams: {
                employee: employee
            },
            rowStyler: function (index, row) {
                var style = "";
                if (row.status == '0') {
                    style += 'background-color:#cac3c3;color:#fff;';
                }
                if(row.emp_company_id == null){
                    style += 'background-color:#551414;color:#fff;';
                    
                }
                return style;
            },
            toolbar: [{
                    text: 'New',
                    iconCls: 'icon-add',
                    handler: function () {
                        showModalForm(livesite + 'FixedPaymentUpload/form')
                    }
                }
                ,'-', {
                    iconCls: 'icon-edit',   
                    text: 'Edit',
                    handler: function () {
                        var row = $('#att_table').datagrid('getSelected');
                        if (row) {
                            showModalForm(livesite + 'FixedPaymentUpload/form/'+row.emp_fixed_component_upload_pkey);
                        } else {
                              alert("Please select a record to edit")
                        }
                    }
                },'-', {
                    iconCls: 'icon-remove',
                    text: 'Remove',
                    handler: function () {
                        var rows = $('#att_table').datagrid('getSelections');
                        if (rows) {
                            if(rows.length<=0){
                                alert('Please select a record to delete');
                                return false;
                            }
                            var str_ids = "";
                            for (var i = 0; i < rows.length; i++) {
                                var data = rows[i];
                                if (str_ids == "") {
                                    str_ids += data.emp_fixed_component_upload_pkey;
                                } else
                                {
                                    str_ids += "," + data.emp_ctc_upload_pkey;
                                }
                            }
                            if (confirm("Are you sure want to delete ")) {

                                $.ajax({
                                    url: livesite + "FixedPaymentUpload/deleteEmployees",
                                    data: {
                                        emp_ctc_upload_pkey: str_ids
                                    },
                                    success: function (response) {

                                        var response = $.parseJSON(response);
                                        if (response.msg) {
                                            $.notify(response.msg, {
                                                type: 'success',
                                                allow_dismiss: true

                                            });
                                            $('#att_table').datagrid('load');
                                        }                                       
                                    }
                                }); 
                            }
                        }
                    }
                }
            ],
            fitColumns: true,
            pageList: [2, 5, 10, 50, 100],
            columns: [[
                    //{field: 'emp_ctc_upload_pkey', title: '', width: "20%"},
                    {field: 'emp_company_id', title: 'Employee ID', width: "11%"},
                    {field: 'empname', title: 'Employee Name', width: "15%"},
                    {field: 'salary_head_item_desc', title: 'Salary Head', width: "15%"},
                    {field: 'uploaded_amount', title: 'Amount', width: '8%'},
                    {field: 'head_operator', title: 'Operator ', width: '8%'},//This is hided in 9/12/2019 by **ARUL P DAS
                    // {field: 'head_type', title: 'Type', width: '7%'},//This is hided in 9/12/2019 by **ARUL P DAS
                    // {field: 'item_part', title: 'Item Part', width: '7%'},//This is hided in 9/12/2019 by **ARUL P DAS
                    {field: 'occurance', title: 'Occurrence', width: '10%'},
                    {field: 'start_month_year', title: 'Start Month ', width: '10%'},
                    {field: 'end_month_year', title: 'End Month ', width: '10%'},
                    {field: 'remarks', title: 'Remarks', width: '20%' },
                    {field: 'created_by', title: 'Created By', width: '15%' },
                ]],
                onLoadSuccess: function () {
                    setwidth();
                }
        });
    });
</script> 