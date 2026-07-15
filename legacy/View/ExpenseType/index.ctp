<section class="content-header">
    <h2 style="text-align:left;" class="text-primary-18">Expense Master</h2>
</section>
<section class="content">
    <div class="row">

        <div class="col-md-12">
            <!-- DIRECT CHAT DANGER -->
            <div class="box ">
 <div class="box-body">
               <!-- <div class="box-body">
                    <legend>Expense Type</legend>
                    <div class="box-body" id="div-reportcriterias">

                     <form>

                     </div>
                 </form>
             </div> -->
			 </div>
             <div >
                <table id="expensetable">

                </table>
            </div>
        </div>
    </div>
</div>
</div>
</section>
<script>
 // $(document).ready(function () {
     function reloadDatagrid(expense_type_pkey)
     {
        $("#expensetable").datagrid('load',{
            expense_type_pkey:expense_type_pkey
        });
    }
    $(document).ready(function(){

     //   alert("doness");
        var usersoptions = {
        url: function (phrase) {
            return "ExpenseType/search?expense_type_name=" + phrase;
        },
        getValue: "expense_type_name",
        list: {
            onClickEvent: function () {
                var selectedItem = $("#expense_type_name").getSelectedItemData();
                var site_fkey = selectedItem.expense_type_pkey

                reloadDatagrid(site_fkey);

            }
        },
        theme: "plate-dark",
        placeholder: "Search for expense name"
    };

    $('#expense_type_name').easyAutocomplete(usersoptions);




    $('#expensetable').datagrid();
    var expense_type_pkey=$('#expense_type_name').val();
    reloadDatagrid(expense_type_pkey);       
    $('#expense_type_name').on('change',function(){
        var expense_type_pkey=$(this).val();
        reloadDatagrid(expense_type_pkey);
    });

   // grid loading    
   $("#expensetable").datagrid(
   {
    title: "Expense Master",
    rownumbers: true,
    itColumns: true,
    url: 'ExpenseType/listexpense',
    singleSelect: true,
    autoRowHeight: false,
    pagination: true,
    PostsearchFilter:true,
    width: '100%',
    pageSize: 10,
    toolbar : [
    {
     iconCls: 'icon-add',
     text: "New",
     handler: function () {

        var url = 'ExpenseType/expense';
        showModalForm(url);
                     
                    }  
                    
                }, '-',
                {
                  iconCls: 'icon-edit',
                  text: "Edit",
                  handler: function () {
                    var row = $('#expensetable').datagrid('getSelected');
                    if (row) {
                        var expense_type_pkey = row.expense_type_pkey;

                        var url = 'ExpenseType/expense/' + expense_type_pkey;
                        showModalForm(url);
							
                        }
                        else
                        {
                            alert("select a row first");
                        }
                    }
                    
                }, '-',
                {
                    iconCls:'icon-cancel',
                    text: "remove",
                    handler: function () {
                     var row = $('#expensetable').datagrid('getSelected');
                     if (row) {
                      var r=confirm("Do You Want To Remove The Selected Item")
                      if(r==true){
                               

                               var expense_type_pkey = row.expense_type_pkey;
                               $.ajax({
                                 url: 'ExpenseType/delete/' + expense_type_pkey,
                                 success: function (resp) {
                                   $('#expensetable').datagrid('reload');

                               }
                           });
                           }
                           else
                           {
                            $.notify("Cancelled");
                        }

                    }
                     else
                        {
                            alert("select a row first");
                        }
                }
            }, '-',
                        {
                            iconCls: 'icon-edit',
                            text: "Expense Allocation",
                            handler: function () {
                                var row = $('#expensetable').datagrid('getSelected');
                                //console.log(row);
                                if (row) {
                                    var expense_type_pkey = row.expense_type_pkey;
                                    var url = 'ExpenseType/allocate_expense/' + expense_type_pkey;
                                    showModalForm(url);
                                    //$('#modalDiv').load(url, function () {
                                    //    $('#modalDiv').modal('show');
                                    //});
                                } else
                                {
                                    $.notify('Please choose an Expense Name', {
                                        type: 'warning',
                                        allow_dismiss: false
                                    });
                                }
                            }
                        }
            ],
            columns: [[
            {field: 'expense_head_name', title: 'Expense Head', width: "50%",sortable:true,order:'asc'},
            {field: 'expense_type_name', title:'Expense Name', width: "49%",sortable:true,order:'asc'},
           /* {field: 'created_by', title:'Created By', width: "17%",sortable:true,order:'asc'},
            {field: 'creation_date', title:'Created Date', width: "16%",sortable:true,order:'asc'},
            {field: 'modified_by', title:'Modified By', width: "17%",sortable:true,order:'asc'},
            {field: 'modified_date', title:'Modified Date', width:"16%",sortable:true,order:'asc'},*/


            ]]
            ,
            onSearch:function(s){

                $('#expensetable').datagrid('load',{
                    expense_type_name: $('#searchqupo').val()
                });
            }   

        });     




});


</script>
