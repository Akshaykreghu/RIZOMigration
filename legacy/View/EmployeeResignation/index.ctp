  <style>
   .heading {
        display: flex;
        flex-direction: row;
        align-items: end;
        justify-content: space-between;
        margin-left: 20px;
        /* padding: 15px 0 !important; */
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
     <!-- /* edited by bindu 24-10-25 */ -->
    <h1 class="text-primary-18">Remove Employee</h1>
     <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
        <i class="fa" style="font-size:16px;">&#xf104;</i>
        Back
    </div>
</section>
<!--<section class="content" id="container_scroll">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-header">
                <hr>
                <div class="box-body" id="div-reportcriterias">
                    <div class="col-md-12">
                        <input onclick="Terminatre();" type="button" class="btn btn-danger" value="Separate An Employee">
                    </div>
                </div>
            </div>
        </div>
    </div>-->
<br>
<section class="content">
    <div class="col-md-12">
        <br>
        <div class="box box-primary " style="margin-top: -21px;">
            <div class="box-body" style="    margin-top: -11px;">
                <br>
                <table id="emptable" class="table table-bordered table-hover">

                </table>
                <div class="box box-body">
                    <div id="loadFullandfinal"></div>
                </div>
            </div><!-- /.box-body -->
            <!--/.direct-chat -->
        </div><!-- /.col -->
    </div>
</section>
<div id="empSetupModalForm" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Content will be loaded here from "remote.php" file -->
        </div>
    </div>
</div>
<!-- edited by athira on 13-04-2025 -->
<div id="loader" class="overlay" style="z-index: 9999 !important;display:none;">
    <img src="/img/updateimg.gif" style="position: absolute;top: 14%;right: 39%;width: 10%;">
</div>
<!-- end -->
<script>
    // edited by athira on 13-04-2025
    function showLoader() {
        $('#loader').show();
    }

    function hideLoader() {
        $('#loader').hide();
    }
    // end
    //Edited by Akshay on 11-6-2024
    var company_code = <?php echo json_encode($company_code);?>;
    //End
    //    function Terminatre()
    //    {
    //        showModalForm(livesite + 'EmployeeResignation/form/0');
    //    }
    function filterEmployees(obj) {
        var branch = $('#filterby_branch').val();
        var employee = $('#filterby_employees').val();

        var designation = $('#filterby_Designation').val()

        $('#emptable').datagrid('load', {
            branch: branch,
            employee: employee,
            designation: designation,
        });

    }
    //edited by amal on 2/7/19 start
    function reloadDatagrid(emp) {
        $("#emptable").datagrid('load', {
            emp: emp
        });
    }
    $('#emptable').datagrid();
    var emp_pkey = $('#name').val();
    reloadDatagrid(emp_pkey);
    $('#name').on('change', function() {
        var emp_pkey = $(this).val();
        reloadDatagrid(emp_pkey);
    });
    //edited by amal on 2/7/19 end


    jQuery(document).ready(function() {





        //  $('#filterby_employees').easyAutocomplete(getstages);
        $('#emptable').datagrid({
            url: livesite + "EmployeeResignation/listemployees",
            pagination: true,
            singleSelect: true,
            //edited by amal
            PostsearchFilter: true, //end
            width: '100%',
            rownumbers: true,
            rowStyler: function(index, row) {
                var style = "";
                if (row.statuses == '2') {
                    style += 'color:red;';
                }
                return style;
            },
            toolbar: [{
                    text: 'Separate An Employee',
                    iconCls: 'icon-remove',
                    handler: function() {
                        showModalForm(livesite + 'EmployeeResignation/form/0');
                    }
                }, '-', {
                    text: 'Process Full & Final',
                    iconCls: 'icon-add',
                    handler: function() {

                        var row = $('#emptable').datagrid('getSelected');
                        if (row) {
                            $('#loadFullandfinal').html('<li class="fa fa-spin fa-spinner"></li>');
                            var emp = row.terminate_pkey;
                            $('#loadFullandfinal').load(livesite + "EmployeeResignation/setup/" + emp);
                            $("html, body").animate({
                                scrollTop: $(document).height()
                            }, 1000);
                        } else {
                            alert("Please select a record to edit")
                        }
                    }
                }, '-',
                {
                    text: 'Edit',
                    iconCls: 'icon-edit',
                    id: "btn",
                    handler: function() {

                        var row = $('#emptable').datagrid('getSelected');
                        if (row) {
                            // Edited by Akshay on 5-4-2025
                            if (row.emp_pkey){
                                showModalForm(livesite + 'EmployeeResignation/form/' + row.emp_pkey);
                            } else{
                                showModalForm(livesite + 'EmployeeResignation/form/' + row.emp_fkey);
                            }

                            // End
                        } else {
                            alert("Please select a record to edit")
                        }
                    }
                }, '-',
                {
                    text: 'Delete',
                    id: "del",
                    iconCls: 'icon-remove',
                    handler: function() {
                        var row = $('#emptable').datagrid('getSelected');
                        if (row) {
                            if (confirm("Are you sure want to delete ")) {
                                $.ajax({
                                    //edited by megha delete resignation request from employee login
                                    url: livesite + 'EmployeeResignation/DeleteEmployeeResignation/' + row.terminate_pkey + '/' + row.emp_pkey,
                                    data: {
                                        terminate_pkey: row.terminate_pkey,
                                        emp_pkey: row.emp_pkey
                                    },
                                    success: function(response) {
                                        $.notify($.parseJSON(response).msg, {
                                            type: 'success',
                                            allow_dismiss: true,
                                        });
                                        $('#emptable').datagrid('load');
                                    }
                                });
                            }
                        } else {
                            alert("please select any row");
                        }

                    }
                }, '-',
                {
                    text: 'View Slip',
                    //                    iconCls: 'icon-add',
                    handler: function() {

                        var row = $('#emptable').datagrid('getSelected');
                        if (row) {
                            //                            $('#loadFullandfinal').html('<li class="fa fa-spin fa-spinner"></li>');
                            if (row.statuses == '2') {
                                var emp = row.emp_fkey;
                                showLargeModalForm(livesite + 'EmployeeResignation/Viewslip/' + emp);
                            } else {
                                alert("Please process Full and Final To generate slip !!!");
                            }
                            //                          

                        } else {
                            alert("Please select a record");
                        }
                        //                            alert(emp);
                    }
                }, 
                <?php if ($company_code == 'KWMT' || $company_code == 'DEMO' || $company_code == 'GLET') :?> '-',
                    //edited by athira 
                    {
                        text: 'F&F Email Slip',
                        id: "email_slip",
                        handler: function() {
                            var row = $('#emptable').datagrid('getSelected');
                            if (row) {
                                if (row.statuses == '2') {
                                 // edited by athira on 13-04-2025
                                    showLoader();
                                    //end
                                    $.ajax({
                                        url: livesite + 'EmployeeResignation/sendEmail/',
                                        type: 'POST',
                                        data: {
                                            emp_pkey: row.emp_fkey
                                        },
                                        success: function(response) {
                                         // edited by athira on 13-04-2025
                                            hideLoader();
                                            //end
                                            // Parse response and handle notifications
                                            response = JSON.parse(response);
                                            if (response.status === "success") {
                                                console.log("hi");
                                                $.notify(response.message, {
                                                    type: 'success', // Show success notification
                                                    allow_dismiss: false
                                                });
                                            } else if (response.status === "danger") {
                                                $.notify(response.message, {
                                                    type: 'danger', // Show failure notification
                                                    allow_dismiss: false
                                                });
                                            }
                                            console.log(response); // Keep console log for debugging
                                        },
                                        error: function(xhr, status, error) {
                                         // edited by athira on 13-04-2025
                                            hideLoader();
                                            //end
                                            // Handle AJAX errors
                                            $.notify("An error occurred while sending the email.", {
                                                type: 'danger',
                                                allow_dismiss: false
                                            });
                                            console.error("Error:", error);
                                        }
                                    });
                                }
                            } else {
                                alert("Please select a record"); // Alert user if no row is selected
                            }
                        }
                    }
                    <?php endif; ?>
                //edited by athira end
                //Edited by Akshay o n 10-6-2024
               /* {
                    text: 'Send Wishes',
                    id: "wishes",
                    handler: function() {
                        if(true)
                        if (confirm("Are you sure you want to send wishes?")) {
                            $.ajax({
                                url: livesite + 'Dashboard/automation_load_birthdays',
                                success: function(response) {
                                    if($.parseJSON(response).emp > 0 || $.parseJSON(response).emp1 > 0 ) //Edited by Akshay on 11-6-2024
                                    $.notify($.parseJSON(response).msg, {
                                        type: 'success',
                                        allow_dismiss: true,
                                    });

                                    // Parsing the response and mapping through employees
                                    const parsedResponse = $.parseJSON(response);
                                    
                                },
                                error: function(xhr, status, error) {
                                    $.notify('An error occurred while processing your request.', {
                                        type: 'danger',
                                        allow_dismiss: true,
                                    });
                                }
                            });
                        }
                    }

                }*/
                //End

            ],
            fitColumns: true,
            pageList: [2, 5, 10, 50, 100],
            columns: [
                [{
                        field: 'name',
                        title: 'Full Name',
                        width: "15%",
                        sortable: true
                    },
                    {
                        field: 'branch',
                        title: 'Branch',
                        width: "10%",
                        sortable: true
                    },
                    {
                        field: 'Reason',
                        title: 'Reason',
                        width: "15%",
                        sortable: true
                    },
                    {
                        field: 'submitted_date',
                        title: 'Resignation Submitted',
                        width: "15%",
                        sortable: true
                    },
                    {
                        field: 'last_applied_date',
                        title: 'Last Applied Date',
                        width: "15%",
                        sortable: true
                    },
                    {
                        field: 'last_approved_working_date',
                        title: 'Last Approved',
                        width: "15%",
                        sortable: true
                    },
                    {
                        field: 'remarks',
                        title: 'Remarks',
                        width: "20%",
                        sortable: true
                    }
                ]
            ],
            onSelect: function(s) {
                var row = $('#emptable').datagrid('getSelected');
                if (row.statuses == '2') {
                    $('#btn').linkbutton('disable');
                    $('#del').linkbutton('disable');
                    $('#email_slip').linkbutton('enable'); //edited by athira
                } else {
                    $('#btn').linkbutton('enable');
                    $('#del').linkbutton('enable');
                    $('#email_slip').linkbutton('disable'); //edited by athira
                }
            },
            //edited by amal on 2/7/19 start 
            onSearch: function(s) {
                $('#emptable').datagrid('load', {
                    emp: $('#searchqupo').val()
                });
            }
            //edited by amal on 2//7/19 end
        });


    });
       /* edited by bindu 20-02-26 */
   $(".home").on("click", function () {

    $("#container").isLoading({
        text: "Loading",
        position: "overlay",
    });

    let url = "";
    var userGroup=<?php echo json_encode($user_group); ?>

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

	/* edited by bindu 20-02-26 */
</script>