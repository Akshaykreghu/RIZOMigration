<style>
    .form-horizontal .control-label{

        text-align: left;

    }
</style>

<section class="content-header">
    <h1 style="text-align:left; font-size: 3em;"> Uniform Allocate</h1>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box ">
                <br>
                <div class="box-body">
                    <!-- Employee import form -->
                    <form class="form-horizontal" method="post" action="" id="importemployeectcform">
                        <div class="row">
                            <div class="form-group">
                                <div class="col-sm-4">
                                    <label class="col-sm-5 control-label" for="filterby_branch">Choose Branch</label>
                                    <div class="col-md-7">
                                        <select id="filterby_branch" name="filterby_branch" class="form-control js-example-basic-single" onchange="filterAttendanceupload(this);" >
                                            <option value="">All</option>
                                            <?php foreach ($arr_branchs as $key => $value) { ?>                              
                                                <option  value="<?php echo $value['Units']['branch_code']; ?>"><?php echo $value['Units']['branch_name']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-sm-5 control-label" for="employee">Choose Employee</label>                        
                                    <div class="col-md-7">
                                        <select id="emp_fkey" class="form-control js-example-basic-single" name="emp_fkey" onchange="filterAttendanceupload(this);"  >

                                        </select>

                                    </div>    
                                </div>
                                                                    
                            </div>
                        </div>
                    </form>
                    <div class="box-body">
                        <table id="att_table" class="table table-bordered table-hover">
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
    //filtter using branch 
    function filterEmployees(branch)
    {
        
        var branch = $('#filterby_branch').val();
        //alert(branch);
        $("#emp_fkey").select2(
                {
                    //closeOnSelect:false,
                    placeholder: "All",
                    allowClear: true,
                    ajax: {
                        url: livesite + "Employee/jsons/" + branch,
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


    
    
    function filterAttendanceupload(obj) {
        var branch = $('#importemployeectcform #filterby_branch').val();
        var employee = $('#importemployeectcform #emp_fkey').val()

        $('#att_table').datagrid('load', {
            branch: branch,
            employee: employee,
        });
          filterEmployees();

    }




    jQuery(document).ready(function () {
        
        filterEmployees();
        $("#filterby_branch").select2();
        $("#filterby_month").select2();
        
        var employee = $('#attendanceuploadfilter #emp_fkey').val();

        $('#att_table').datagrid({
            url: livesite + "uniform/lists",
            pagination: true,
            singleSelect: true,
            rownumbers: true,
            queryParams: {
                employee: employee
            },
            toolbar: [{
                    text: 'New',
                    iconCls: 'icon-add',
                    handler: function () {
                        showModalForm(livesite + 'uniform/allocate_form')
                    }
                },
                //{
//				iconCls: 'icon-edit',
//				text:'Edit',
//				handler: function(){
//						var row = $('#att_table').datagrid('getSelected');
//						if (row){
//								    showModalForm(livesite+'uniform/form/' + row.item_pkey);
//						}else{
//						alert("Please select a record to edit")
//						}
//				}
//				},{
//				iconCls: 'icon-remove',
//				text:'Remove',
//				handler: function(){
//					
//								var rows = $('#att_table').datagrid('getSelected');
//								if (rows){
//                                                                             var str_ids = "";
//                                                                             str_ids = rows.item_pkey;
//                                                                             
////                    	 for(var i=0;i<rows.length;i++){
////                    	 	var data = rows[i];
////									 if(str_ids == ""){
////										 str_ids += data.emp_pkey;
////									 }else
////									 {
////										 str_ids += ","+data.emp_pkey;
////									 }
////                    	 }      
//											if (confirm("Do you want to delete the selected employee(s)?")) {
//												
//													$.ajax({
//											 url:  livesite+"Uniform/deleteEmp",
//													data : {
//													ids : str_ids
//													},
//													success : function(response) {
//													//var text = response.responseText;
//													// process server response here
//															reloadTable('att_table')
//														}
//													});
//										
//											}
//							
//								}else{
//						alert("Please select a record to edit")
//						}
//					
//				}
//				}
                                ],
            fitColumns: true,
            pageList: [2, 5, 10, 50, 100],
            columns: [[
                    //{field: 'emp_expenses_pkey', title: '', width: "%"},
                    {field: 'name', title: 'Employee Name', width: "25%"},
                    {field: 'value', title: 'Item Value', width: "25%"},
                    {field: 'date_allocated', title: 'Allocated Date', width: "25%"},
                    {field: 'balance_recover_amt', title: 'Balance recover amount', width: "25%"},
                    //  {field: 'is_credited', title: 'Credited Rate', width: '10%'},
                ]]
        });
    });
</script>