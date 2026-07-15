<section class="content-header">
    <h2 style="text-align:left;"> Vehicle Master   </h2>
</section>
<section class="content">
    <div class="row">

        <div class="col-md-12">
            <!-- DIRECT CHAT DANGER -->
            <div class="box ">
                <div class="box-body">
                    <!--    <div class="box-header with-border">
                            <legend>Vehicle Master</legend>
                            <div class="box-body" id="div-reportcriterias">
        
                             <form>
        
                             </div>
                         </form>
                     </div> -->
                </div>
                <div >
                    <table id="vehicletable">

                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</section>
<script>
    // $(document).ready(function () {
    function reloadDatagrid(vehicle_master_pkey)
    {
        $("#vehicletable").datagrid('load', {
            vehicle_master_pkey: vehicle_master_pkey
        });
    }
    $(document).ready(function () {

        //   alert("doness");
        var usersoptions = {
            url: function (phrase) {
                return "Vehicle/search?model_dec=" + phrase;
            },
            getValue: "model_dec",
            list: {
                onClickEvent: function () {
                    var selectedItem = $("#model_dec").getSelectedItemData();
                    var site_fkey = selectedItem.vehicle_master_pkey

                    reloadDatagrid(site_fkey);

                }
            },
            theme: "plate-dark",
            placeholder: "Search for Vehicle name or Reg. No."
        };

        $('#model_dec').easyAutocomplete(usersoptions);




        $('#vehicletable').datagrid();
        var vehicle_master_pkey = $('#model_dec').val();
        reloadDatagrid(vehicle_master_pkey);
        $('#model_dec').on('change', function () {
            var vehicle_master_pkey = $(this).val();
            reloadDatagrid(vehicle_master_pkey);
        });

        // grid loading    
        $("#vehicletable").datagrid(
                {
                    title: "Vehicle Master",
                    rownumbers: true,
                    itColumns: true,
                    url: livesite + 'Vehicle/listvehicle',
                    singleSelect: true,
                    autoRowHeight: false,
                    pagination: true,
                    PostsearchFilter: true,
                    width: '100%',
                    pageSize: 10,
                    toolbar: [
                        {
                            iconCls: 'icon-add',
                            text: "New",
                            handler: function () {

                                var url = 'Vehicle/vehicle';
                                showModalForm(url);

                            }

                        }, '-',
                        {
                            iconCls: 'icon-edit',
                            text: "Edit",
                            handler: function () {
                                var row = $('#vehicletable').datagrid('getSelected');
                                if (row) {
                                    var vehicle_master_pkey = row.vehicle_master_pkey;

                                    var url = 'Vehicle/vehicle/' + vehicle_master_pkey;
                                    showModalForm(url);

                                }
                                else
                                {
                                    alert("select a row first");
                                }
                            }

                        }, '-',
                        {
                            iconCls: 'icon-cancel',
                            text: "remove",
                            handler: function () {
                                var row = $('#vehicletable').datagrid('getSelected');
                                if (row) {
                                    var r = confirm("Do You Want  To Remove The Selected Item")
                                    if (r == true) {


                                        var vehicle_master_pkey = row.vehicle_master_pkey;
                                        $.ajax({
                                            url: livesite + 'Vehicle/delete/' + vehicle_master_pkey,
                                            success: function (resp) {

                                                //if (resp.msg) {
                                                $.notify("Vehicle Details Deleted.", {
                                                    type: 'success',
                                                    allow_dismiss: true

                                                });
                                                // }
                                                $('#vehicletable').datagrid('reload');
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
                            {field: 'reg_number', title: 'Register No.', width: "12%", sortable: true, order: 'asc'},
                            {field: 'model_dec', title: 'Model Name', width: "12%", sortable: true, order: 'asc'},
                            {field: 'make', title: 'Company', width: "12%", sortable: true, order: 'asc'},
                            {field: 'vehicle_type', title: 'Type', width: "16%", sortable: true, order: 'asc'},
                            {field: 'capacity', title: 'Capacity', width: "8%", sortable: true, order: 'asc'},
                            {field: 'rate_per_km', title: 'Rate Per km', width: "8%", sortable: true, order: 'asc'},
                            {field: 'make_year', title: 'Make Year', width: "8%", sortable: true, order: 'asc'},
                            {field: 'fual_type', title: 'Fuel Type', width: "8%", sortable: true, order: 'asc'},
                            {field: 'remarks', title: 'Remarks', width: "16%", sortable: true, order: 'asc'},
                        ]]
                    ,
                    onSearch: function (s) {

                        $('#vehicletable').datagrid('load', {
                            model_dec: $('#searchqupo').val(),
                            reg_number: $('#searchqupo').val(),
                            make: $('#searchqupo').val(),
                            vehicle_type: $('#searchqupo').val(),
                            make_year: $('#searchqupo').val(),
                            fual_type: $('#searchqupo').val(),
                        });
                    }

                });




    });


</script>
