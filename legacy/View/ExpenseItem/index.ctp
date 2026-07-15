<!--<section class="content">-->
<div class="row">
    <div class="col-md-12">       
        <div class="box ">
            <div class="box-body">
                <h1 class="page-header">Expense Category</h1>
                    <form class="form-horizontal" method="post" action="" id="importemployeectcform">
                        <div class="row">
                            <div class="form-group">
                                <div class="col-md-12">
                                    <!--<label class="col-sm-5 control-label" for="employee">Choose Employee</label>-->                        
                                    <div class="col-md-12">
                                        <div class="col-sm-2 control-label" for="employee" style="text-align: left;">Search Expense  <span style="padding-left: 60px;">:</span> </div>                        
                                        <div class="col-md-4">
                                        <select id="emp_fkey" class="form-control js-example-basic-single" name="emp_fkey" onchange="filterAttendanceupload(this);"  >

                                        </select>
                                        </div>
                                    </div>    
                                </div>
                                                                    
                            </div>
                        </div>
                    </form>
<table id="acctptable" >

</table>
<div id="empSetupModalForm" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Content will be loaded here from "remote.php" file -->
        </div>
    </div>
</div>
</div>
</div>
</div>
</section>
<script>
   
    function filterEmployees(branch)
    {
        
//        var branch = $('#filterby_branch').val();
        //alert(branch);
        $("#emp_fkey").select2(
                {
                    //closeOnSelect:false,
                    placeholder: "Search Expense ... ",
                    allowClear: true,
                    ajax: {
                        url: livesite + "ExpenseItem/itemfilter/",
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
//        var branch = $('#importemployeectcform #filterby_branch').val();
        var employee = $('#importemployeectcform #emp_fkey').val();
//        var catpkey = $(obj);
//        alert(employee);

        $('#acctptable').datagrid('load', {
            item: employee
        });
//          filterEmployees();

    }
    $(document).ready(function () {
        filterEmployees();

        $('#acctptable').datagrid({
            url: livesite+ 'ExpenseItem/itemlist',
            rownumbers: true,
            title: "Expense Category",
            fitColumns: true,
            singleSelect: true,
            autoRowHeight: false,
            pagination: true,
            width: '100%',
         
            pageSize: 10,
            toolbar: [{
                    iconCls: 'icon-add',
                    text: "New",
                    handler: function () {
                        
                        var url = livesite+'ExpenseItem/form';
						showModalForm(url);
                        //$('#modalDiv').load(url,function(){
                        //    $('#modalDiv').modal('show');
                        //});
                    }
              }, {
                    iconCls: 'icon-edit',
                    text: "Edit",
                    handler: function () {
                        var row = $('#acctptable').datagrid('getSelected');
                        //console.log(row);
                        if (row) {
                            var item_master_pkey = row.expense_item_pkey;
                             
                           //  alert(item_master_pkey);
                            //location.href='stages/addeditstages/'+stages_pkey;
                            var url = livesite+'ExpenseItem/form/'+ item_master_pkey;
							showModalForm(url);
                            //$('#modalDiv').load(url, function () {
                            //    $('#modalDiv').modal('show');
                            //});
                        }
                        else
                        {
                            alert("Please Choose A Row");
                        }
                    }
                },{
                     iconCls: 'icon-cancel',
                    text: "Remove",
                    handler: function () {
                        var row = $('#acctptable').datagrid('getSelected');
                        if (row) {
                                  var r=confirm("Do You Want  To Remove The Selected Item")
                                    if(r==true){
                              // alert("you pressed ok");
                              var item_master_pkey = row.expense_item_pkey;;
                              //alert(row.item_master_pkey);
                              $.ajax({ 
                                 url:livesite+"ExpenseItem/itemdelete",
				 data : {
				 item_master_pkey : item_master_pkey
				 },
				 success : function(response) {
				 //var text = response.responseText;
				 // process server response here
                                  $.notify("Deleted",{type:'danger'});
				  reloadTable('acctptable')
				  }
				});
                             }
                        else
                        {
                            alert("cancelled operation");
                        }
                        }
                       else
                        {
                            alert("Please Choose A Row");
                        }
                    }
                }],
            columns:[[
                    {field: 'expense_item_name', title: 'Expense Name', width: "30%", sortable: true, order: 'asc', editor: 'textbox'},
                    {field: 'category', title: 'Category', width: "30%", sortable: true, order: 'asc', editor: 'textbox'}, 
                    {field: 'description', title: 'Description', width: "39%", sortable: true, order: 'asc', editor: 'textbox'}, 
//                    {field: 'created_by', title: 'Created_by', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                        
                   
                ]],
            });
    })
</script>