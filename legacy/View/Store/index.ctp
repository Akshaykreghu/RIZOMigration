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
<div class="col-md-12">
    <div class="box ">
       
            <!-- <h3  class="text-primary-18">Store Master</h3> -->
              <div class="heading">
                    <h1 class="text-primary-18">Store Master</h1>
                    <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;margin:0 0 10px 0;">
                        <i class="fa" style="font-size:16px;">&#xf104;</i>
                        Back
                    </div>
                    </div>
            <div class="box-tools pull-right">
                <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
            </div>
      
        <div class="box-body">
            <form class="form-horizontal" method="post" action="" id="importemployeectcform">
                <div class="row">
                    <div class="form-group">
                        <div class="col-md-12">
                            <!--<label class="col-sm-5 control-label" for="employee">Choose Employee</label>-->                        
                            <div class="col-md-12">
                                <div class="col-sm-2 control-label" for="employee" style="text-align: left;">Search Store  <span style="padding-left: 60px;">:</span> </div>                        
                                <div class="col-md-4">
                                    <select id="emp_fkey" class="form-control js-example-basic-single" name="emp_fkey" onchange="filterAttendanceupload(this);" >

                                    </select>
                                </div>
                            </div>    
                        </div>

                    </div>
                </div>
            </form>
            <table id="storetable" ></table>
        </div>
    </div>
</div>
<div id="empSetupModalForm" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Content will be loaded here from "remote.php" file -->
        </div>
    </div>
</div>
<script>
    
    function filterEmployees()
    {
        
//        var branch = $('#filterby_branch').val();
        //alert(branch);
        $("#emp_fkey").select2(
                {
                    //closeOnSelect:false,
                    placeholder: "Search Store  By Name... ",
                    allowClear: true,
                    ajax: {
                        url: livesite + "Store/storefilter/",
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
          var store = $('#emp_fkey').val();
//        var catpkey = $(obj);
//        alert(employee);
          $('#storetable').datagrid('load', {
             item: store
          });
//          filterEmployees();
    }
    
    $(document).ready(function () { 
        filterEmployees();
        $('#storetable').datagrid({
            url: 'store/storeList',
            rownumbers: true,
            title: "Store Master",
            fitColumns: true,
            singleSelect: true,
            autoRowHeight: false,
            pagination: true,
            width: '98%',
            pageSize: 10,
            toolbar: [{
                    iconCls: 'icon-add',
                    text: "New",
                    handler: function () {
                        var url = 'store/form';
                        showModalForm(url);
                        //$('#modalDiv').load(url, function () {
                        //    $('#modalDiv').modal('show');
                        //});
                    }
                }, {
                    iconCls: 'icon-edit',
                    text: "Edit",
                    handler: function () {
                        var row = $('#storetable').datagrid('getSelected');
                        //console.log(row);
                        if (row) {
                            var store_master_pkey = row.store_master_pkey;
                            var url = 'store/form/' + store_master_pkey;
                            showModalForm(url);
                            //$('#modalDiv').load(url, function () {
                            //    $('#modalDiv').modal('show');
                            //});
                        } else
                        {
                            //alert("Please choose a store");
                            $.notify('Please choose a store', {
                                type: 'warning',
                                allow_dismiss: false
                            });
                        }
                    }
                }, {
                    iconCls: 'icon-edit',
                    text: "Allocate",
                    handler: function () {
                        var row = $('#storetable').datagrid('getSelected');
                        //console.log(row);
                        if (row) {
                            var store_master_pkey = row.store_master_pkey;
                            var url = 'store/Storedata/' + store_master_pkey;
                            showModalForm(url);
                            //$('#modalDiv').load(url, function () {
                            //    $('#modalDiv').modal('show');
                            //});
                        } else
                        {
                            //alert("Please choose a store");
                            $.notify('Please choose a store', {
                                type: 'warning',
                                allow_dismiss: false
                            });
                        }
                    }
                }, {
                    iconCls: 'icon-cancel',
                    text: "Remove",
                    handler: function () {
                        var row = $('#storetable').datagrid('getSelected');
                        if (row) {
                            var r = confirm("Do you want to remove the selected item ?")
                            if (r == true) {
                                var store_master_pkey = row.store_master_pkey;
                                $.ajax({
                                    url: "store/storeDelete",
                                    data: {
                                        store_master_pkey: store_master_pkey
                                    },
                                    success: function (response) {
                                        //var text = response.responseText;
                                        // process server response here
                                        $.notify('Group deleted', {
                                            type: 'danger',
                                            allow_dismiss: false
                                        });
                                        $('#storetable').datagrid('reload');
                                    }
                                });
                            } else
                            {
                                //alert("Canceled operation!");
                                $.notify('Canceled operation!', {
                                    type: 'warning',
                                    allow_dismiss: false
                                });
                            }
                        } else {
                            //alert("Please choose a store");
                            $.notify('Please choose a store', {
                                type: 'warning',
                                allow_dismiss: false
                            });
                        }

                    }
                }],
            columns: [[
                    {field: 'store_code', title: 'Store Code', width: '10%', sortable: true, order: 'asc', editor: 'textbox'},
                    {field: 'store_location', title: 'Store Location', width: '12%', sortable: true, order: 'asc', editor: 'textbox'},
             //     {field: 'store_manager', title: 'Store Manager', width: '10%', sortable: true, order: 'asc', editer: 'textbox'},
                    {field: 'address', title: 'Address', width: '18%', sortable: true, order: 'asc', editor: 'textbox'},
                    {field: 'city', title: 'City', width: '10%', sortable: true, order: 'asc', editor: 'textbox'},
                    {field: 'state', title: 'State', width: '10%', sortable: true, order: 'asc', editer: 'textbox'},
                    {field: 'pincode', title: 'Pincode', width: '10%', sortable: true, order: 'asc', editer: 'textbox'},
                    {field: 'created_by', title: 'Created By', width: '10%', sortable: true, order: 'asc', editer: 'textbox'},
                    {field: 'creation_date', title: 'Created Date', width: '10%', sortable: true, order: 'asc', editer: 'textbox'},
                    {field: 'modified_by', title: 'Modified By', width: '9%', sortable: true, order: 'asc', editer: 'textbox'}
                ]],
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