<style>
    .left-inner-addon {
        position: relative;
    }
    .left-inner-addon input {
        padding-left: 30px;    
    }
    .left-inner-addon i {
        position: absolute;
        padding: 10px 12px;
        pointer-events: none;
    }

    .right-inner-addon {
        position: relative;
    }
    .right-inner-addon input {
        padding-right: 30px;    
    }
    .right-inner-addon i {
        position: absolute;
        right: 0px;
        padding: 10px 12px;
        pointer-events: none;
    }
</style>
<section class="content-header">
    <h1> Employee Promotion </h1>
    <?php
    ?>
</section>
<!-- Main content -->
<section class="content">


    <div class="row">
        <div class="col-md-12">
            <!-- Employee List -->
            <!-- DIRECT CHAT DANGER -->

            <div class="box">

                <div class="box box-body">



                    <table id="emptable" class="table table-bordered table-hover">

                    </table>
                </div>
            </div><!-- /.box-body -->

        </div><!--/.direct-chat -->
    </div><!-- /.col -->

</section>
<div id="empSetupModalForm" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Content will be loaded here from "remote.php" file -->
        </div>
    </div>
</div>
<script>

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
    

    jQuery(document).ready(function () {

        


        //  $('#filterby_employees').easyAutocomplete(getstages);
        $('#emptable').datagrid({
            url: livesite + "Promo/listemployees",
            title: "Employee", 
            fitColumns: true,
            singleSelect: true,
            autoRowHeight: false,
            pagination: true,
            PostsearchFilter: true,
            rownumbers: true,
            pageSize: 10,
            width: '100%',
            rowStyler: function (index, row) {
                var style = "";
                if (row.status == '2') {
                    style += 'background-color:#cac3c3;color:#fff;';
                }
                if (row.emp_company_id == null) {
                    style += 'background-color:#551414;color:#fff;';

                }
                return style;
            },
            toolbar: [{
                    iconCls: 'icon-edit',
                    text: 'Approve Promotion',
                    handler: function () {
                        var row = $('#emptable').datagrid('getSelected');
                        if (row) {
                            showLargeModalForm(livesite + 'Promo/approvepromotion/' + row.promotion_pkey);
                        } else {
                            alert("Please select a record to edit")
                        }
                    }
                }
            ],
            fitColumns:true,
                    pageList: [2, 5, 10, 50, 100],
            columns: [
                [
                    {field: 'emp_company_id', title: 'Employee ID', width: "20%", sortable: true},
                    {field: 'name', title: 'Full Name', width: "20%", sortable: true},
                    {field: 'promotion_status', title: 'Promotion Status', width: "20%", sortable: true},
                    {field: 'created_date', title: 'Created On', width: "20%", sortable: true},
                    {field: 'remarks', title: 'Remarks', width: "20%", sortable: true}
                ]
            ],
            onSearch: function (s) {

                $('#emptable').datagrid('load', {
                    emp: $('#searchqupo').val()
                });
            }
        });


    });
</script>