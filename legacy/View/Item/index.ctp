<!--<section class="content">-->
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
<div class="row">
    <div class="col-md-12">
        <div class="box ">
            <div class="box-body">
                <div class="heading">
                    <h1 class="text-primary-18"> Item Master</h1>
                    <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;margin:0 0 10px 0;">
                        <i class="fa" style="font-size:16px;">&#xf104;</i>
                        Back
                    </div>
                    </div>
                    <form class="form-horizontal" method="post" action="" id="importemployeectcform">
                        <div class="row">
                            <div class="form-group">
                                <div class="col-md-12">
                                    <!--<label class="col-sm-5 control-label" for="employee">Choose Employee</label>-->
                                    <div class="col-md-12">
                                        <div class="col-sm-2 control-label" for="employee" style="text-align: left;">Search Item <span style="padding-left: 60px;">:</span> </div>
                                        <div class="col-md-4">
                                            <select id="emp_fkey" class="form-control js-example-basic-single" name="emp_fkey" onchange="filterAttendanceupload(this);">

                                            </select>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </form>
                    <table id="acctptable">

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
            function filterEmployees(branch) {

                //        var branch = $('#filterby_branch').val();
                //alert(branch);
                $("#emp_fkey").select2({
                    //closeOnSelect:false,
                    placeholder: "Search Item By Name ... ",
                    allowClear: true,
                    ajax: {
                        url: livesite + "Item/itemfilter/",
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                q: params.term, // search term
                                page: params.page
                            };
                        },
                        processResults: function(data, params) {
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
                    escapeMarkup: function(markup) {
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
            $(document).ready(function() {
                filterEmployees();

                $('#acctptable').datagrid({
                    url: livesite + 'Item/itemlist',
                    rownumbers: true,
                    title: "ITEM MASTER",
                    fitColumns: true,
                    singleSelect: true,
                    autoRowHeight: false,
                    pagination: true,
                    width: '100%',

                    pageSize: 10,
                    toolbar: [{
                        iconCls: 'icon-add',
                        text: "New",
                        handler: function() {

                            var url = livesite + 'Item/form';
                            showModalForm(url);
                            //$('#modalDiv').load(url,function(){
                            //    $('#modalDiv').modal('show');
                            //});
                        }
                    }, {
                        iconCls: 'icon-edit',
                        text: "Edit",
                        handler: function() {
                            var row = $('#acctptable').datagrid('getSelected');
                            //console.log(row);
                            if (row) {
                                var item_master_pkey = row.item_master_pkey;

                                //  alert(item_master_pkey);
                                //location.href='stages/addeditstages/'+stages_pkey;
                                var url = livesite + 'Item/form/' + item_master_pkey;
                                showModalForm(url);
                                //$('#modalDiv').load(url, function () {
                                //    $('#modalDiv').modal('show');
                                //});
                            } else {
                                alert("Please Choose A Row");
                            }
                        }
                    }, {
                        iconCls: 'icon-cancel',
                        text: "Remove",
                        handler: function() {
                            var row = $('#acctptable').datagrid('getSelected');
                            if (row) {
                                var r = confirm("Do You Want  To Remove The Selected Item")
                                if (r == true) {
                                    // alert("you pressed ok");
                                    var item_master_pkey = row.item_master_pkey;
                                    //alert(row.item_master_pkey);
                                    $.ajax({
                                        url: livesite + "Item/itemdelete",
                                        data: {
                                            item_master_pkey: item_master_pkey
                                        },
                                        success: function(response) {
                                            //var text = response.responseText;
                                            // process server response here
                                            $.notify("Deleted", {
                                                type: 'danger'
                                            });
                                            reloadTable('acctptable')
                                        }
                                    });
                                } else {
                                    alert("canceled operation");
                                }
                            }
                            //edited by megha on 14_5_19 start
                            else {
                                alert("Please Choose A Row");
                            }
                            //edited by megha on 14_5_19 end  
                        }
                    }],
                    columns: [
                        [
                            //added by megha on 24_5_19 item_code display
                            {
                                field: 'item_code',
                                title: 'Item code',
                                width: 100,
                                sortable: true,
                                order: 'asc',
                                editor: 'textbox'
                            },
                            {
                                field: 'item_desc',
                                title: 'Item Name',
                                width: 100,
                                sortable: true,
                                order: 'asc',
                                editor: 'textbox'
                            },
                            {
                                field: 'description',
                                title: 'Item Category',
                                width: 100,
                                sortable: true,
                                order: 'asc',
                                editor: 'textbox'
                            },
                            //edited by megha on 24_5_19 item_specification removed
                            //                    {field: 'item_specification', title: 'Item Specification', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 
                            //                    {field: 'created_by', title: 'Created_by', width: 100, sortable: true, order: 'asc', editor: 'textbox'}, 


                        ]
                    ],
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