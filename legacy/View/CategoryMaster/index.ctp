<style>
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
        margin-right: 15px;
        color: white;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        border: #1e516e 1px solid;
    }

</style>
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box ">
                <div class="box-body">
                    <!-- <legend class="text-primary-18">Category Master</legend> -->
                      <div class="heading">
                    <h1 class="text-primary-18">Category Master</h1>
                    <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;margin:0 0 10px 0;">
                        <i class="fa" style="font-size:16px;">&#xf104;</i>
                        Back
                    </div>
                    </div>
                    <div>
                        <form class="form-horizontal" method="post" action="" id="importemployeectcform">
                            <div class="row">
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <!--<label class="col-sm-5 control-label" for="employee">Choose Employee</label>-->                        
                                        <div class="col-md-12">
                                            <div class="col-sm-2 control-label" for="employee" style="text-align: left;">Search Category  <span style="padding-left: 60px;">:</span> </div>                        
                                            <div class="col-md-4">
                                            <select id="emp_fkey" class="form-control js-example-basic-single" name="emp_fkey" onchange="filterAttendanceupload(this);" >

                                            </select>
                                            </div>
                                        </div>    
                                    </div>

                                </div>
                            </div>
                        </form>
                        <table id="categorytable">

                        </table>
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
                    placeholder: "Search Category  By Name... ",
                    allowClear: true,
                    ajax: {
                        url: livesite + "CategoryMaster/categoryfilter/",
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

        $('#categorytable').datagrid('load', {
            site: employee
        });
//          filterEmployees();

    }

 $(document).ready(function () {
    
    filterEmployees();
    var employee = $('#attendanceuploadfilter #emp_fkey').val();
     $("#categorytable").datagrid(
             {
             title: "Category Master",
             rownumbers: true,
             itColumns: true,
             url: 'CategoryMaster/listmaster',
            singleSelect: true,
            autoRowHeight: false,
            pagination: true,
            width: '100%',
            pageSize: 10,
            
            toolbar : [
                {
                     iconCls: 'icon-add',
                    text: "New",
                    handler: function () {
                        
                        var url = 'CategoryMaster/category';
						showModalForm(url);
                        //$('#modalDiv').load(url,function(){
                        //    $('#modalDiv').modal('show');
                        //});
                    }  
                }, '-',
                {
                      iconCls: 'icon-edit',
                    text: "Edit",
                    handler: function () {
                        var row = $('#categorytable').datagrid('getSelected');
                        if (row) {
                            var category_pkey = row.category_pkey;
                  
                            var url = 'CategoryMaster/category/' + category_pkey;
                            showModalForm(url);
							//$('#modalDiv').load(url,function(){
							//    $('#modalDiv').modal('show');
							//});
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
                     var row = $('#categorytable').datagrid('getSelected');
                        if (row) {
                                  var r=confirm("Do You Want  To Remove The Selected Item")
                                    if(r==true){
                               //  alert("you pressed ok");
                              
                                                                 var category_pkey = row.category_pkey;
                                                                 $.ajax({
                                                                     url: 'CategoryMaster/delete/' + category_pkey,
                                                                     success: function (resp) {
                                                                               $('#categorytable').datagrid('reload');
                                                        
                                                                     }
                                                                 });
                                                             }
                        else
                        {
                            $.notify("Canceled");
                        }
                        }
                        //edited by megha on 14_5_19 start
                       else
                        {
                            alert("Please Choose A Row");
                        }
                        //edited by megha on 14_5_19 end 
                    }
                }
            ],
            columns: [[
                    {field: 'code', title: 'Name', width: "25%",sortable:true,order:'asc'},
                    {field: 'description', title:'Description', width: "30%",sortable:true,order:'asc'},
                     {field: 'created_by', title:'Created By', width: "25%",sortable:true,order:'asc'},
                      {field: 'created_date', title:'Created Date', width: "20%",sortable:true,order:'asc'},
                            
                        
            ]]
                 
                 
             });     
     
     
     
     
 })
$(".home").on("click", function () {

    $("#container").isLoading({
        text: "Loading",
        position: "overlay",
    });

    let url = "";
    var userGroup = <?php echo json_encode($this->Session->read('user_group')); ?>;

    if (userGroup == "1") {
        url = livesite + "Stockmanagement/index";
    } 
    else if (userGroup == "2") {
        url = livesite + "EmployeeMenu/addon";
    }

    $("#container").load(url, function () {
        isDashboardShown = false;
    });

});


</script>
