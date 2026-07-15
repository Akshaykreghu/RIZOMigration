 <style>
     #editpunchform table tr td {
         padding: 5px;
         width: 100%;
     }
 </style>

 <script>
     function myformatter(date) {
         var y = date.getFullYear();
         var m = date.getMonth() + 1;
         var d = date.getDate();
         return (d < 10 ? ('0' + d) : d) + '/' + (m < 10 ? ('0' + m) : m) + '/' + y;
     }

     function myparser(s) {
         if (!s)
             return new Date();
         var ss = (s.split('-'));
         var y = parseInt(ss[0], 10);
         var m = parseInt(ss[1], 10);
         var d = parseInt(ss[2], 10);
         if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
             return new Date(y, m - 1, d);
         } else {
             return new Date();
         }
     }
 </script>
 <script src="<?php echo $this->webroot; ?>plugins/easyui/jquery.easyui.min.js"></script>



 <script src="<?php echo $this->webroot; ?>plugins/tab/assets/jquery.pwstabs-1.2.1.js"></script>
 <!--<section class="content-header">
    <h1 style="text-align:center; font-size: 3em;">
        <li class="fa fa-building-o"></li>&nbsp;Company Setup
    </h1>
</section>-->

 <section class="content-header">
     <!-- edited by athira on 03-07-2025 -->
    <h1 class="text-primary-18">Company Setup</h1>
    <!-- end -->
     <hr style="margin-top: 8px;margin-bottom: -2px;">
 </section>

 <!-- Main content -->
 <section class="content">

     <div class="col-md-12">
         <br>
         <div class="tabset0" style="height:700px !important;">
             <div data-pws-tab="tab1" data-pws-tab-name="Contact & Complaince Info" data-pws-tab-icon="fa-info">
                 <br>
                 <div class="box box-primary " style="margin-top: -21px;">
                     <div class="box-body" style="    margin-top: -11px;">
                         <br>
                         <div class="col-md-12" id="infoid" style="height:700px !important;">

                         </div>
                     </div>
                 </div>
             </div>
             <div data-pws-tab="tab2" data-pws-tab-name="Branches" data-pws-tab-icon="fa-sitemap">
                 <br>
                 <div class="box box-primary " style="margin-top: -21px;">
                     <div class="box-body" style="    margin-top: -11px;">
                         <br>
                         <table id="brtable" class="table table-bordered table-hover">
                             <thead>
                                 <tr>
                                     <th></th>
                                     <th>Name</th>
                                     <th>Address</th>
                                     <th>City</th>
                                     <th>State</th>
                                     <th>Pincode</th>
                                 </tr>
                             </thead>
                             <tbody>
                             </tbody>
                         </table>
                     </div><!-- /.box-body -->
                 </div>
             </div>
             <div data-pws-tab="tab3" data-pws-tab-name="Departments" data-pws-tab-icon="fa-bars">
                 <br>
                 <div class="box box-primary " style="margin-top: -21px;">
                     <div class="box-body" style="    margin-top: -11px;">
                         <br>
                         <table id="dpttable" class="table table-bordered table-hover">
                             <thead>
                                 <tr>
                                     <th></th>
                                     <th>Name</th>
                                     <th>Code</th>
                                 </tr>
                             </thead>
                             <tbody>
                             </tbody>
                         </table>

                     </div><!-- /.box-body -->

                 </div>
             </div>


             <div data-pws-tab="tab4" data-pws-tab-name="Designation" data-pws-tab-icon="fa-bars">

                 <br>
                 <div class="box box-primary " style="margin-top: -21px;">
                     <div class="box-body" style="    margin-top: -11px;">
                         <br>
                         <table id="designation" class="table table-bordered table-hover">
                             <thead>
                                 <tr>
                                     <th>SL. No</th>
                                     <th>Designation Code</th>
                                     <th>Organisation ID</th>
                                     <th>Designation Name</th>
                                     <th>Status</th>
                                 </tr>
                             </thead>
                             <tbody>
                             </tbody>
                         </table>

                     </div><!-- /.box-body -->

                 </div>
             </div>

             <!-- Edited by Akshay on 9-10-2023 -->
             <div data-pws-tab="tab5" data-pws-tab-name="Category" data-pws-tab-icon="fa-bars">

                 <br>
                 <div class="box box-primary " style="margin-top: -21px;">
                     <div class="box-body" style="    margin-top: -11px;">
                         <br>
                         <table id="tbl_category" class="table table-bordered table-hover">
                             <thead>
                                 <tr>
                                     <th></th>
                                     <th>Category Code</th>
                                     <th>Category Name</th>
                                 </tr>
                             </thead>
                             <tbody>
                             </tbody>
                         </table>

                     </div><!-- /.box-body -->

                 </div>
             </div>

             <div data-pws-tab="tab6" data-pws-tab-name="Grade" data-pws-tab-icon="fa-bars">

                 <br>
                 <div class="box box-primary " style="margin-top: -21px;">
                     <div class="box-body" style="    margin-top: -11px;">
                         <br>
                         <table id="tbl_grade" class="table table-bordered table-hover">
                             <thead>
                                 <tr>
                                     <th></th>
                                     <th>Grade Code</th>
                                     <th>Grade Name</th>
                                 </tr>
                             </thead>
                             <tbody>
                             </tbody>
                         </table>

                     </div>

                 </div>
             </div>

             <div data-pws-tab="tab7" data-pws-tab-name="Notice Period" data-pws-tab-icon="fa-calendar">

                 <br>
                 <div class="box box-primary " style="margin-top: -21px;">
                     <div class="box-body" style="    margin-top: -11px;">
                         <br>
                         <table id="tbl_notice" class="table table-bordered table-hover">
                             <thead>
                                 <tr>
                                     <th></th>
                                     <th>Days</th>
                                     <th>Description</th>
                                 </tr>
                             </thead>
                             <tbody>
                             </tbody>
                         </table>

                     </div><!-- /.box-body -->

                 </div>
             </div>

             <div data-pws-tab="tab8" data-pws-tab-name="Financial Year" data-pws-tab-icon="fa-calendar">
                 <br>
                 <div class="box box-primary " style="margin-top: -21px;">
                     <div class="box-body" style="    margin-top: -11px;">
                         <br>

                         <table id="finyeartable" class="table table-bordered table-hover">
                             <thead>
                                 <tr>
                                     <th></th>
                                     <th>Year</th>
                                     <th>Start Month</th>
                                     <th>End Month</th>
                                 </tr>
                             </thead>
                             <tbody>
                             </tbody>
                         </table>
                     </div><!-- /.box-body -->
                 </div>
                 <!--
                        Form
                -->
                 <div id="finwinform" class="easyui-window" title="Financial Year" data-options="modal:true,closed:true" style="">

                     <div class="easyui-panel" title="" style="width:300px;padding:2px;">
                         <div style="padding:10px 0 0 10px">
                             <form id="finyearform" method="post" action="<?php echo $this->webroot; ?>FinancialYear/save">
                                 <input type="hidden" name="Fin_year_seq" id="Fin_year_seq" />
                                 <table cellpadding="5">
                                     <tr>
                                         <td>Date From:</td>
                                         <td><input class="easyui-datebox" name="finstart" data-options="formatter:myformatter,required:true,parser:myparser"></input></td>
                                     </tr>
                                     <tr>
                                         <td>Date To:</td>
                                         <td> <input class="easyui-datebox" name="finend" data-options="formatter:myformatter,required:true,parser:myparser"></td>
                                     </tr>

                                 </table>
                             </form>
                             <div style="text-align:center;padding:5px">
                                 <a href="javascript:void(0)" class="easyui-linkbutton" onclick="submitFinyearForm()">Submit</a>
                                 <a href="javascript:void(0)" class="easyui-linkbutton" onclick="clearForm();
                                        $('#finwinform').window('close')">Close</a>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>

             <div data-pws-tab="tab9" data-pws-tab-name="Banks" data-pws-tab-icon="fa-university">
                 <br>
                 <div class="box box-primary " style="margin-top: -21px;">
                     <div class="box-body" style="    margin-top: -11px;">
                         <br>

                         <table id="banktable" class="table table-bordered table-hover">
                             <thead>
                                 <tr>
                                     <th></th>
                                     <th>Name</th>
                                     <th>Branch</th>
                                     <th>IFSC Code</th>
                                     <th>Acc #</th>
                                 </tr>
                             </thead>
                             <tbody>
                             </tbody>
                         </table>
                     </div><!-- /.box-body -->

                 </div>

             </div>


             <!--div data-pws-tab="tab7" data-pws-tab-name="DB Config" data-pws-tab-icon="fa-bars">
                              
                               <div class="box ">
      <div class="box-header with-border">
        <h3 class="box-title">DB Configuration</h3>
        <div class="box-tools pull-right">
          <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
        </div>
      </div>
      <div class="box-body">
           <table id="dbtable" class="table table-bordered table-hover">
          <thead>
            <tr>
    <th>SL. No</th>
                                   <th>company_code</th>
                                   <th>biometric_device_essl</th>
                                   <th>attendance_type</th>
                                   <th>attendance_format</th>
                                   <th>attendance_date</th>
                                   <th>emp_login</th>
                                   <th>payroll_type</th>
                                   <th>email_setup</th>
                                   <th>TDS_setup</th>
                                   <th>Salary_date</th>
                                   <th>active</th>
                                   <th>created_date</th>
            </tr>
          </thead>
          <tbody>
              </tbody>
              </table>
        
      </div>
     
    </div>
                              </div>      -->




         </div>
     </div>


 </section>


 <script>
     jQuery(document).ready(function() {
         //        loader('infoid');	


         $("#infoid").load(livesite + "CompanyNew/viewinfo")



         $("#companylogofile").fileinput({
             <?php if (isset($contactinfo['logo']) && $contactinfo['logo'] != null) { ?>
                 initialPreview: [
                     '<img src="<?php echo $this->webroot . $contactinfo['logo']; ?>" class="file-preview-image" alt="<?php echo $contactinfo['business_name']; ?>" title="<?php echo $contactinfo['business_name']; ?>">',
                 ],
             <?php } ?>
             showCaption: false,
             showUpload: false,
             removeLabel: '',
             removeIcon: '<i class="glyphicon glyphicon-remove"></i>',
             removeTitle: 'Cancel or reset changes',
         });
         $("#cinfoedit").on("click", function() {

             if ($("#contactInfoForm").hasClass("infomode")) {

                 $('#companylogofile').fileinput('enable');
                 $("#contactInfoForm").find("input,textarea").each(function() {
                     //	console.log(this)
                     $(this).prop("readonly", false);
                 })

                 $("#contactInfoForm").removeClass("infomode");
                 $("#contactInfoForm").addClass("editmode");
                 $("#cinfosave").prop('disabled', false);
                 $(this).html("Cancel Edit");
             } else {

                 $('#companylogofile').fileinput('enable');
                 $("#contactInfoForm").find("input,textarea").each(function() {
                     console.log(this)
                     $(this).prop("readonly", true);
                 })
                 $("#cinfosave").prop('disabled', true);
                 $("#contactInfoForm").removeClass("editmode");
                 $("#contactInfoForm").addClass("infomode");
                 $("#cinfoedit").html("Edit");
             }

         })

         $('#brtable').datagrid({
             url: livesite + "Branch/listunits",
             pagination: true,
             singleSelect: true,
             width: '96%',
             rownumbers: true,
             toolbar: [{
                 text: 'New',
                 iconCls: 'icon-add',
                 handler: function() {
                     showLargeModalForm(livesite + 'Branch/form')
                 }
             }, {
                 iconCls: 'icon-edit',
                 text: 'Edit',
                 handler: function() {
                     var row = $('#brtable').datagrid('getSelected');
                     if (row) {
                         showLargeModalForm(livesite + 'Branch/form?id=' + row.id)
                     } else {
                         alert("Please select a record to edit")
                     }
                 }
             }, '-', {
                 iconCls: 'icon-remove',
                 text: 'Remove',
                 handler: function() {
                     var rows = $('#brtable').datagrid('getSelections');
                     if (rows) {
                         var str_ids = "";
                         for (var i = 0; i < rows.length; i++) {
                             var data = rows[i];
                             if (str_ids == "") {
                                 str_ids += data.id;
                             } else {
                                 str_ids += "," + data.id;
                             }
                         }
                         if (confirm("Are you sure want to delete ")) {
                             $.ajax({
                                 url: livesite + "Branch/deleteBranches",
                                 data: {
                                     ids: str_ids
                                 },
                                 success: function(response) {
                                     var response = $.parseJSON(response);
                                     if (response.msg) {
                                         $.notify(response.msg, {
                                             type: 'success',
                                             allow_dismiss: true
                                         });
                                     }
                                     reloadTable('brtable')
                                 }
                             });
                         }
                     } else {
                         alert("Please select a record to edit")
                     }
                 }
             }],
             fitColumns: true,
             pageList: [2, 5, 10, 50, 100],
             columns: [
                 [{
                         field: 'branch_name',
                         title: 'Name',
                         width: "20%",
                         sortable: true
                     },
                     {
                         field: 'address',
                         title: 'Address',
                         width: "20%",
                         sortable: true
                     },
                     {
                         field: 'city',
                         title: 'City',
                         width: "10%",
                         sortable: true
                     },
                     {
                         field: 'state',
                         title: 'State',
                         width: "10%",
                         sortable: true
                     },
                     {
                         field: 'pincode',
                         title: 'Pincode',
                         width: "10%",
                         sortable: true
                     },
                     {
                         field: 'LeaveStart',
                         title: 'Leave Year StartDate',
                         width: "15%",
                         sortable: true
                     },
                     {
                         field: 'LeaveEnd',
                         title: 'Leave Year EndDate',
                         width: "15%",
                         sortable: true
                     },
                     {
                         field: 'FinStart',
                         title: 'Financial Year StartDate',
                         width: "15%",
                         sortable: true
                     },
                     {
                         field: 'FinEnd',
                         title: 'Financial Year EndDate',
                         width: "15%",
                         sortable: true
                     },
                 ]
             ]
         });
         $('#dpttable').datagrid({
             url: livesite + "Department/listdepartments",
             singleSelect: true,
             pagination: true,
             width: '96%',
             rownumbers: true,
             toolbar: [{
                 text: 'New',
                 iconCls: 'icon-add',
                 handler: function() {
                     showModalForm(livesite + 'Department/form')
                 }
             }, {
                 iconCls: 'icon-edit',
                 text: 'Edit',
                 handler: function() {
                     var row = $('#dpttable').datagrid('getSelected');
                     if (row) {
                         showModalForm(livesite + 'Department/form?id=' + row.id)
                     } else {
                         alert("Please select a record to edit")
                     }
                 }
             }, '-', {
                 iconCls: 'icon-remove',
                 text: 'Remove',
                 handler: function() {
                     var rows = $('#dpttable').datagrid('getSelected');
                     if (rows) {
                         var str_ids = "";
                         //for(var i=0;i<rows.length;i++){
                         //    var data = rows[i];
                         //    if(str_ids == ""){
                         //        str_ids += data.id;
                         //    }else
                         //    {
                         //        str_ids += ","+data.id;
                         //    }
                         //}
                         if (confirm("Are you sure want to delete ")) {
                             $.ajax({
                                 url: livesite + "Department/deleteDepartment",
                                 data: {
                                     ids: rows.id
                                 },
                                 success: function(response) {
                                     //var text = response.responseText;
                                     // process server response here
                                     var response = $.parseJSON(response);
                                     if (response.msg) {
                                         $.notify(response.msg, {
                                             type: 'success',
                                             allow_dismiss: true
                                         });
                                     }
                                     reloadTable('dpttable')
                                 }
                             });
                         }
                     } else {
                         alert("Please select a record to delete")
                     }
                 }
             }],
             fitColumns: true,
             pageList: [2, 5, 10, 50, 100],
             columns: [
                 [{
                         field: 'dept_code',
                         title: 'Code',
                         width: "50%",
                         sortable: true
                     },
                     {
                         field: 'dept_name',
                         title: 'Department',
                         width: "50%",
                         sortable: true
                     },
                 ]
             ]
         });
         $('#banktable').datagrid({
             url: livesite + "Bank/listbanks",
             pagination: true,
             singleSelect: true,
             rownumbers: true,
             width: '96%',
             toolbar: [{
                 text: 'New',
                 iconCls: 'icon-add',
                 handler: function() {
                     showModalForm(livesite + 'Bank/form')
                 }
             }, {
                 iconCls: 'icon-edit',
                 text: 'Edit',
                 handler: function() {
                     var row = $('#banktable').datagrid('getSelected');
                     if (row) {
                         showModalForm(livesite + 'Bank/form?id=' + row.id)
                     } else {
                         alert("Please select a record to edit")
                     }
                 }
             }, '-', {
                 iconCls: 'icon-remove',
                 text: 'Remove',
                 handler: function() {
                     var rows = $('#banktable').datagrid('getSelected');
                     if (rows) {
                         var str_ids = rows.id;
                         if (confirm("Are you sure want to delete ")) {
                             $.ajax({
                                 url: livesite + "Bank/deleteBank",
                                 data: {
                                     ids: str_ids
                                 },
                                 success: function(response) {
                                     var response = $.parseJSON(response);
                                     if (response.msg) {
                                         $.notify(response.msg, {
                                             type: 'success',
                                             allow_dismiss: true
                                         });
                                     }
                                     reloadTable('banktable')
                                 }
                             });
                         }
                     } else {
                         alert("Please select a record to edit")
                     }
                 }
             }],
             fitColumns: true,
             pageList: [2, 5, 10, 50, 100],
             columns: [
                 [{
                         field: 'bank_name',
                         title: 'Name',
                         width: "25%",
                         sortable: true
                     },
                     {
                         field: 'bank_branch',
                         title: 'Branch',
                         width: "25%",
                         sortable: true
                     },
                     {
                         field: 'ifsc_code',
                         title: 'IFSC Code',
                         width: "25%",
                         sortable: true
                     },
                     {
                         field: 'acct_no',
                         title: 'Acc #',
                         width: "25%",
                         sortable: true
                     },
                 ]
             ]
         });
     });
 </script>

 <script type="text/javascript">
     $(document).ready(function() {
         $.validate({
             modules: 'location, date, security, file'
         });
         var options = {
             success: function(resp) {
                 $.notify($.parseJSON(resp).msg, {
                     type: 'success',
                     allow_dismiss: false

                 });
             } // post-submit callback 

         };
         // bind to the form's submit event 
         $('#contactInfoForm').submit(function() {
             // inside event callbacks 'this' is the DOM element so we first 
             // wrap it in a jQuery object and then invoke ajaxSubmit 
             $(this).ajaxSubmit(options);
             // !!! Important !!! 
             // always return false to prevent standard browser submit and page navigation 
             return false;
         });
         var options2 = {
             //  target:        '#output2',   // target element(s) to be updated with server response 
             //  beforeSubmit:  showRequest,  // pre-submit callback 
             success: function(resp) {
                 $.notify($.parseJSON(resp).msg, {
                     type: 'success',
                     allow_dismiss: false

                 });
             } // post-submit callback 

             // other available options: 
             //url:       url         // override for form's 'action' attribute 
             //type:      type        // 'get' or 'post', override for form's 'method' attribute 
             //dataType:  null        // 'xml', 'script', or 'json' (expected server response type) 
             //clearForm: true        // clear all form fields after successful submit 
             //resetForm: true        // reset the form after successful submit 

             // $.ajax options can be used here too, for example: 
             //timeout:   3000 
         };
         $('#complianceform').submit(function() {
             // inside event callbacks 'this' is the DOM element so we first 
             // wrap it in a jQuery object and then invoke ajaxSubmit 
             $(this).ajaxSubmit(options2);
             // !!! Important !!! 
             // always return false to prevent standard browser submit and page navigation 
             return false;
         });
     });
 </script>
 <script type="text/javascript" charset="utf-8">
     $('#finyeartable').datagrid({
         url: livesite + "FinancialYear/listfinyears",
         pagination: true,
         singleSelect: true,
         rownumbers: true,
         width: '96%',
         toolbar: [{
             text: 'New',
             iconCls: 'icon-add',
             handler: function() {
                 showModalForm(livesite + 'FinancialYear/form')
             }
         }, {
             iconCls: 'icon-edit',
             text: 'Edit',
             handler: function() {
                 var row = $('#finyeartable').datagrid('getSelected');
                 if (row) {
                     showModalForm(livesite + 'FinancialYear/form?id=' + row.Fin_year_seq)
                 } else {
                     alert("Please select a record to edit")
                 }
             }
         }, '-', {
             iconCls: 'icon-remove',
             text: 'Remove',
             handler: function() {
                 var rows = $('#finyeartable').datagrid('getSelected');
                 if (rows) {
                     var str_ids = rows.Fin_year_seq;
                     if (confirm("Are you sure want to delete ")) {
                         $.ajax({
                             url: livesite + "FinancialYear/deleteFinYear",
                             data: {
                                 ids: str_ids
                             },
                             success: function(response) {
                                 var response = $.parseJSON(response);
                                 if (response.msg) {
                                     $.notify(response.msg, {
                                         type: 'success',
                                         allow_dismiss: true
                                     });
                                 }
                                 reloadTable('finyeartable')
                             }
                         });
                     }
                 } else {
                     alert("Please select atleast one record to delete")
                 }
             }
         }],
         fitColumns: true,
         pageList: [2, 5, 10, 50, 100],
         columns: [
             [{
                     field: 'fin_year',
                     title: 'Financial Year',
                     width: "10%",
                     sortable: true
                 },
                 {
                     field: 'Branches',
                     title: 'Branch',
                     width: "20%",
                     sortable: true
                 },
                 {
                     field: 'vattr1',
                     title: 'Type',
                     width: "10%",
                     sortable: true
                 },
                 {
                     field: 'start_month',
                     title: 'Start Month',
                     width: "20%",
                     sortable: true
                 },
                 {
                     field: 'end_month',
                     title: 'End Month',
                     width: "20%",
                     sortable: true
                 },
                 {
                     field: 'Year_status',
                     title: 'Status',
                     width: "20%",
                     sortable: true
                 },
             ]
         ]
     });

     function submitFinyearForm() {
         $('#finyearform').form('submit', {
             onSubmit: function() {
                 return $(this).form('enableValidation').form('validate');
             },
             success: function(data) {
                 clearForm();
                 refreshgrid();
                 $('#finwinform').window('close')
                 $.messager.show('Success', "Financial Year Saved Successfully", 'info');
             }
         });
     }

     function clearForm() {
         $('#Fin_year_seq').val("")
         $('#finyearform').form('clear');
     }
     jQuery(document).ready(function($) {
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
     });
     $('#dbtable').datagrid({
         url: livesite + "DbConfig/listdb",
         pagination: true,
         singleSelect: true,
         rownumbers: true,
         width: '96%',
         toolbar: [{
             text: 'New',
             iconCls: 'icon-add',
             handler: function() {

                 showModalForm(livesite + 'Dbconfig/form')
             }
         }, {
             iconCls: 'icon-edit',
             text: 'Edit',
             handler: function() {
                 var row = $('#dbtable').datagrid('getSelected');
                 if (row) {
                     showModalForm(livesite + 'Dbconfig/form?id=' + row.id)
                 } else {
                     alert("Please select a record to edit")
                 }
             }
         }, '-', {
             iconCls: 'icon-remove',
             text: 'Remove',
             handler: function() {

                 var rows = $('#dbtable').datagrid('getSelected');
                 if (rows) {
                     var str_ids = "";
                     for (var i = 0; i < rows.length; i++) {
                         var data = rows[i];
                         if (str_ids == "") {
                             str_ids += data.id;
                         } else {
                             str_ids += "," + data.id;
                         }
                     }
                     if (confirm("Are you sure want to delete ")) {

                         $.ajax({
                             url: livesite + "Dbconfig/deleteDepartment",
                             data: {
                                 ids: str_ids
                             },
                             success: function(response) {
                                 //var text = response.responseText;
                                 // process server response here
                                 var response = $.parseJSON(response);
                                 if (response.msg) {
                                     $.notify(response.msg, {
                                         type: 'success',
                                         allow_dismiss: true

                                     });
                                 }
                                 reloadTable('dbtable')
                             }
                         });
                     }

                 } else {
                     alert("Please select atleast one record to delete")
                 }

             }
         }],
         fitColumns: true,
         pageList: [2, 5, 10, 50, 100],
         columns: [
             [{
                     field: 'company_code',
                     title: 'Company Code',
                     width: "10%",
                     sortable: true
                 },
                 {
                     field: 'biometric_device_essl',
                     title: 'B.D.Date',
                     width: "10%",
                     sortable: true
                 },
                 {
                     field: 'attendance_type',
                     title: 'Attendance Type',
                     width: "10%",
                     sortable: true
                 },
                 {
                     field: 'attendance_format',
                     title: 'Attendance Format',
                     width: "10%",
                     sortable: true
                 },
                 {
                     field: 'attendance_date',
                     title: 'Attendance Date',
                     width: "10%",
                     sortable: true
                 },
                 {
                     field: 'emp_login',
                     title: 'Employee Login',
                     width: "10%",
                     sortable: true
                 },
                 {
                     field: 'payroll_type',
                     title: 'Payroll Types',
                     width: "10%",
                     sortable: true
                 },
                 {
                     field: 'email_setup',
                     title: 'Email Setup',
                     width: "10%",
                     sortable: true
                 },
                 {
                     field: 'TDS_setup',
                     title: 'TDS Setup',
                     width: "10%",
                     sortable: true
                 },
                 {
                     field: 'Salary_date',
                     title: 'Salary Date',
                     width: "10%",
                     sortable: true
                 },
                 {
                     field: 'active',
                     title: 'Active',
                     width: "10%",
                     sortable: true
                 },
                 {
                     field: 'created_date',
                     title: 'Created Date',
                     width: "10%",
                     sortable: true
                 },
             ]
         ]
     });
     $('#designation').datagrid({
         url: livesite + "Designation/listDesignation",
         pagination: true,
         singleSelect: true,
         width: '96%',
         rownumbers: true,
         toolbar: [{
             text: 'New',
             iconCls: 'icon-add',
             handler: function() {

                 showModalForm(livesite + 'Designation/form')
             }
         }, {
             iconCls: 'icon-edit',
             text: 'Edit',
             handler: function() {
                 var row = $('#designation').datagrid('getSelected');
                 if (row) {
                     showModalForm(livesite + 'designation/form?id=' + row.id)
                 } else {
                     alert("Please select a record to edit")
                 }
             }
         }, '-', {
             iconCls: 'icon-remove',
             text: 'Remove',
             handler: function() {

                 var rows = $('#designation').datagrid('getSelections');
                 if ((rows) != '') {
                     var str_ids = "";
                     for (var i = 0; i < rows.length; i++) {
                         var data = rows[i];
                         if (str_ids == "") {
                             str_ids += data.id;
                         } else {
                             str_ids += "," + data.id;
                         }
                     }
                     if (confirm("Are you sure want to delete ")) {

                         $.ajax({
                             url: livesite + "Designation/deleteDepartment",
                             data: {
                                 ids: str_ids
                             },
                             success: function(response) {
                                 //var text = response.responseText;
                                 // process server response here
                                 var response = $.parseJSON(response);
                                 if (response.msg) {
                                     $.notify(response.msg, {
                                         type: 'success',
                                         allow_dismiss: true

                                     });
                                 }
                                 reloadTable('designation')
                             }
                         });
                     }

                 } else {
                     alert("Please Select a Row!!")
                 }

             }
         }],
         fitColumns: true,
         pageList: [2, 5, 10, 50, 100],
         columns: [
             [{
                     field: 'desig_code',
                     title: 'Code',
                     width: "50%",
                     sortable: true
                 },
                 // {field:'org_id',title:'Organisation ID',width:"20%"},
                 {
                     field: 'desig_name',
                     title: 'Designation',
                     width: "50%",
                     sortable: true
                 },
                 //{field:'status',title:'Status',width:"20%"}

             ]
         ]
     });

     $('#tbl_notice').datagrid({
         url: livesite + "NoticePeriod/listNotice",
         pagination: true,
         singleSelect: true,
         rownumbers: true,
         toolbar: [{
             text: 'New',
             iconCls: 'icon-add',
             handler: function() {
                 showModalForm(livesite + 'NoticePeriod/form/')
             }
         }, {
             text: 'Edit',
             iconCls: 'icon-edit',
             handler: function() {
                 var row = $('#tbl_notice').datagrid('getSelected');
                 if (row) {
                     showModalForm(livesite + 'NoticePeriod/form/' + row.notice_pkey)
                 } else {
                     alert("Please select a record to edit");
                 }
             }
         }, {
             iconCls: 'icon-remove',
             text: 'Delete',
             handler: function() {
                 var row = $('#tbl_notice').datagrid('getSelected');
                 if (row) {
                     if (confirm("Are you sure want to delete ")) {
                         $.ajax({
                             url: livesite + "NoticePeriod/deleteNotice/" + row.notice_pkey,
                             success: function(response) {
                                 var response = $.parseJSON(response);
                                 if (response.success) {
                                     $.notify(response.msg, {
                                         type: 'success',
                                         allow_dismiss: true
                                     });
                                 }
                                 reloadTable('tbl_notice');
                             }
                         });
                     }
                 } else {
                     $.notify('Please Select A record to Delete!', {
                         type: 'danger',
                         allow_dismiss: false
                     });
                 }
             }
         }],
         fitColumns: true,
         pageList: [2, 5, 10, 50, 100],
         columns: [
             [
                 // {field: 'notice_pkey', title: 'PKEY', width: "25%"},
                 {
                     field: 'notice_days',
                     title: 'Days',
                     width: "49%"
                 },
                 {
                     field: 'description',
                     title: 'Description',
                     width: "50%"
                 },
                 // {field: 'status', title: 'Status', width: "25%"},
             ]
         ]
     });

     $('#tbl_grade').datagrid({
         url: livesite + "GradesNew/listGrades",
         pagination: true,
         singleSelect: true,
         rownumbers: true,
         toolbar: [{
             text: 'New',
             iconCls: 'icon-add',
             handler: function() {
                 showModalForm(livesite + 'GradesNew/form/')
             }
         }, {
             text: 'Edit',
             iconCls: 'icon-edit',
             handler: function() {
                 var row = $('#tbl_grade').datagrid('getSelected');
                 if (row) {
                     showModalForm(livesite + 'GradesNew/form/' + row.grade_pkey)
                 } else {
                     alert("Please select a record to edit");
                 }
             }
         }, {
             iconCls: 'icon-remove',
             text: 'Delete',
             handler: function() {
                 var row = $('#tbl_grade').datagrid('getSelected');
                 if (row) {
                     if (confirm("Are you sure want to delete ")) {
                         $.ajax({
                             url: livesite + "GradesNew/deleteGrade/" + row.grade_pkey,
                             success: function(response) {
                                 var response = $.parseJSON(response);
                                 if (response.success) {
                                     $.notify(response.msg, {
                                         type: 'success',
                                         allow_dismiss: true
                                     });
                                 }else{
                                    $.notify(response.msg, {
                                         type: 'danger',
                                         allow_dismiss: true
                                     });
                                 }
                                 reloadTable('tbl_grade');
                             }
                         });
                     }
                 } else {
                     $.notify('Please Select A record to Delete!', {
                         type: 'danger',
                         allow_dismiss: false
                     });
                 }
             }
         }],
         fitColumns: true,
         pageList: [2, 5, 10, 50, 100],
         columns: [
             [
                 // {field: 'grade_pkey', title: 'PKEY', width: "25%"},
                 {
                     field: 'grade_code',
                     title: 'Grade Code',
                     width: "25%"
                 },
                 {
                     field: 'grade_name',
                     title: 'Grade',
                     width: "25%"
                 },
                 {
                     field: 'category_name',
                     title: 'Category',
                     width: "25%"
                 },
                 {
                     field: 'pay_scale',
                     title: 'Pay Scale',
                     width: "25%"
                 },
                 // {field: 'status', title: 'Status', width: "25%"},
             ]
         ]
     });

     //Edited by Akshay on 9-10-2023
     $('#tbl_category').datagrid({
         url: livesite + "Category/listGrades",
         pagination: true,
         singleSelect: true,
         rownumbers: true,
         toolbar: [{
             text: 'New',
             iconCls: 'icon-add',
             handler: function() {
                 showModalForm(livesite + 'Category/form/')
             }
         }, {
             text: 'Edit',
             iconCls: 'icon-edit',
             handler: function() {
                 var row = $('#tbl_category').datagrid('getSelected');
                 if (row) {
                     showModalForm(livesite + 'Category/form/' + row.category_pkey)
                 } else {
                     alert("Please select a record to edit");
                 }
             }
         }, {
             iconCls: 'icon-remove',
             text: 'Delete',
             handler: function() {
                 var row = $('#tbl_category').datagrid('getSelected');
                 if (row) {
                     if (confirm("Are you sure want to delete ")) {
                         $.ajax({
                             url: livesite + "Category/deleteGrade/" + row.category_pkey,
                             success: function(response) {
                                 var response = $.parseJSON(response);
                                 console.log('Response',response);
                                 if (response.success) {
                                     $.notify(response.msg, {
                                         type: 'success',
                                         allow_dismiss: true
                                     });
                                 }else{
                                    $.notify(response.msg, {
                                         type: 'danger',
                                         allow_dismiss: true
                                     });
                                 }
                                 reloadTable('tbl_category');
                             }
                         });
                     }
                 } else {
                     $.notify('Please Select A record to Delete!', {
                         type: 'danger',
                         allow_dismiss: false
                     });
                 }
             }
         }],
         fitColumns: true,
         pageList: [2, 5, 10, 50, 100],
         columns: [
             [
                 // {field: 'grade_pkey', title: 'PKEY', width: "25%"},
                 {
                     field: 'category_code',
                     title: 'Category Code',
                     width: "49%"
                 },
                 {
                     field: 'category_name',
                     title: 'Category',
                     width: "50%"
                 },
                 // {field: 'status', title: 'Status', width: "25%"},
             ]
         ]
     });
 </script>