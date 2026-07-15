
<style>
          /* <!-- edited by bindu 02-12-2025 --> */
     .heading {
        display: flex;
        flex-direction: row;
        align-items: end;
        justify-content: space-between;
        /* margin-left: 25px; */
        /* padding: 15px 0 !important; */
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
     /* <!-- edited by bindu 02-12-2025 --> */
</style>
<!-- edited by bindu 02-12-2025 --> 
<section class="content-header heading">
        <!-- edited by athira on 03-07-2025 -->
    <h1 style="text-align:left;" class="text-primary-18">Expense Type</h1>
    <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
        <i class="fa" style="font-size:16px;">&#xf104;</i>
        Back
    </div>
    <!-- /* edited by bindu 02-12-2025 */ -->
    <!-- end -->
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
            return "ExpenseTypes/search?expense_type_name=" + phrase;
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
    //title: "Expense Master",
    rownumbers: true,
    itColumns: true,
    url: 'ExpenseTypes/listexpense',
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

        var url = 'ExpenseTypes/expense';
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

                        var url = 'ExpenseTypes/expense/' + expense_type_pkey;
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
                                 url: 'ExpenseTypes/delete/' + expense_type_pkey,
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
            }
            ],
            columns: [[
            {field: 'expense_type_code', title: 'Expense Code', width: "50%",sortable:true,order:'asc'},
            {field: 'expense_type_name', title:'Expense Type', width: "49%",sortable:true,order:'asc'},
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

 /* edited by bindu 19-02-26 */
   $(".home").on("click", function () {

    $("#container").isLoading({
        text: "Loading",
        position: "overlay",
    });

    let url = "";
     var userGroup = <?php echo json_encode($this->Session->read('user_group')); ?>

    if (userGroup == "1") {
        url = livesite + "CompanySetup/index";
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
