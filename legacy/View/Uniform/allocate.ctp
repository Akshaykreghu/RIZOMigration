<style>
    .form-horizontal .control-label{

        text-align: left;

    }
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

<section class="content-header heading">
    <h1 style="text-align:left; " class="text-primary-18"> Uniform Allocation</h1>
    <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;margin:0;">
                    <i class="fa" style="font-size:16px;">&#xf104;</i>
                    Back
                </div>
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
                                 <!--  <select id="filter_by_empkey" class="form-control js-example-basic-single" name="filter_by_empkey" onchange="filterAttendanceupload(this);"  >

                                        </select> -->
                                    <select id="filter_by_empkey" class ="form-control js example-basic-single"  name="emp_fkey" onchange="filterAttendanceupload(this);">
                                        </select>      
                                            
                                        

                                    </div>    
                                </div>
                                                                    
                            </div>
                            <div class="box box-primary">
                        <br>
                        <input type="hidden" id="rsndempid" value="0" name="resigned">
                        <table id="att_table" class="table table-bordered table-hover">
                            <tbody>
                            </tbody>
                        </table>
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
    function filterEmployees(branch) {
        //edited by athira on 02-04-2025
        var branch = $('#filterby_branch').val();

        $("#filter_by_empkey").select2({
            placeholder: "All",
            allowClear: false,
            ajax: {
                url: livesite + "Uniform/jsons/" + branch,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term, // search term
                        page: params.page
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;

                    let results = data.items;
                    console.log(results, "results");

                    // Store search term
                    let searchTerm = params.term ? params.term.toLowerCase() : "";

                    // Keep "All" only if it matches the search term
                    let allOption = {
                        id: "0",
                        text: "All"
                    };
                    if (!searchTerm || "all".includes(searchTerm)) {
                        results.unshift(allOption);
                    }

                    return {
                        results: results,
                        pagination: {
                            more: (params.page * 30) < data.total_count
                        }
                    };
                    console.log(results, "results");
                }
            },
            escapeMarkup: function(markup) {
                return markup;
            }
        });
        //end
    }
 function setwidth(){

        if ($('#checkrsgnd').length == 0) {
        
            $('.datagrid-toolbar').find('tr').append('<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name"rsgnemp" id="checkrsgnd" onclick="showrsgnd();" >Include Completed Loan </td>');
   
        }
    }

     function showrsgnd(){
        
        var branch = $('#importemployeectcform #filterby_branch').val();
        var employee = $('#importemployeectcform #filter_by_empkey').val()
        
        if($('#checkrsgnd').is(":checked")){
            $('#rsndempid').val('1');
        }else{
            $('#rsndempid').val('0');
        }
        $('#att_table').datagrid('load', {
            branch: branch,
            employee: employee,
            
            name: $('#rsndempid').val(),

        }); 
        
    }
    
    function filterAttendanceupload(obj) {
        var branch = $('#importemployeectcform #filterby_branch').val();
        var employee = $('#importemployeectcform #filter_by_empkey').val();
        var name = $('#importemployeectcform #rsndempid').val()

        $('#att_table').datagrid('load', {
            branch: branch,
            employee: employee,
            name: name
        });
          filterEmployees();

    }




    jQuery(document).ready(function () {
        
         filterEmployees();
        $("#filterby_branch").select2();
        $("#filterby_month").select2();
        // $("#emp_fkey").select2();

        
        var employee = $('#attendanceuploadfilter #filter_by_empkey').val();
        var name = $('#attendanceuploadfilter #rsndempid').val();
// alert(name);
        $('#att_table').datagrid({

            url: livesite + "uniform/lists",
            pagination: true,
            singleSelect: true,
            rownumbers: true,
            queryParams: {
                employee: employee
                // name:name
            },
            //   rowStyler: function (index, row) {
            //     var style = "";
            //     if (row.is_completed == 'Y') {
            //         style += 'background-color:#D5E8EB   ;';
            //     }
             
            //     return style;
            // },

            toolbar: [{
                    text: 'New',
                    iconCls: 'icon-add',
                    handler: function () {
                        
                    	var employee = $('#filter_by_empkey').val();
                       

                        showLargeModalForm(livesite + 'uniform/allocate_form/' + employee);
                        filterEmployees();
                     
                        
                    }


                },'-',
                {
                    iconCls: 'icon-edit',
                    text: 'View Loan Details',
                    handler: function () {
                        var row = $('#att_table').datagrid('getSelected');
                        if (row) {

                            showModalForm(livesite + 'Uniform/view_emi/' + row.loan_fkey)
                        } else
                            $.notify('Please Select A record to View!', {
                                type: 'danger',
                                allow_dismiss: false
                            });
                    }
                },'-',
                {
                    iconCls: 'icon-edit',
                    text: 'Return Items',
                    handler: function () {
                        var row = $('#att_table').datagrid('getSelected');
                        if (row) {

                            showLargeModalForm(livesite + 'uniform/returns/' + row.item_pkey)
                        } else
                            $.notify('Please Select A record to return', {
                                type: 'danger',
                                allow_dismiss: false
                            });
                    }
                },
              

                
                                ],

            fitColumns: true,
            pageList: [2, 5, 10, 50, 100],
            columns: [[
                    //{field: 'emp_expens_pkey', title: '', width: "%"},
                    {field: 'name', title: 'Employee Name', width: "25%"},
                    {field: 'value', title: 'Item Value', width: "25%"},
                    {field: 'date_allocated', title: 'Allocated Date', width: "25%"},
                    {field: 'balance_recover_amt', title: 'Balance Recover Amount', width: "25%"},
                    //  {field: 'is_credited', title: 'Credited Rate', width: '10%'},
                ]],
                onLoadSuccess: function () {
                    
                    setwidth();
                    // filterEmployees();
                }

        }); 
});
 

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