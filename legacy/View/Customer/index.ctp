<div class="box ">
    <div class="box-header with-border">
        <h3 class="text-primary-18"><strong>Customer</strong></h3>
        <div class="box-tools pull-right">
            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
        </div>
    </div><!-- /.box-header -->
    <div class="box-body">
        <table id="customertable" ></table>
    </div>
</div>
<script>
    $(document).ready(function () {

        $('#customertable').datagrid({
            url: 'Customer/listcustomer',
            rownumbers: true,
            title: "Customer",
            fitColumns: true,
            singleSelect: true,
            autoRowHeight: false,
            pagination: true,
            pageSize: 10,
            toolbar: [{
                    iconCls: 'icon-add',
                    text: "New",
                    handler: function () {
                        //location.href='contacts/addeditcontacts';
                        var url = 'Customer/addeditcustomer';
                        $('#modalDiv').load(url, function () {
                            $('#modalDiv').modal('show');
                        });
                    }
                }, '-', {
                    iconCls: 'icon-edit',
                    text: "Edit",
                    handler: function () {
                        var row = $('#customertable').datagrid('getSelected');
                        if (row) {
                            var contact_id = row.contact_id;
                            //location.href='contacts/addeditcontacts/'+contact_id;
                            var url = 'Customer/addeditcustomer/' + contact_id;
                            $('#modalDiv').load(url, function () {
                                $('#modalDiv').modal('show');
                            });
                        } else
                        {
                            $.notify('Please choose a customer', {
                                type: 'warning',
                                allow_dismiss: false
                            });
                        }
                    }
                }, '-', {
                    iconCls: 'icon-remove',
                    text: 'Remove',
                    handler: function () {
                        var row = $('#customertable').datagrid('getSelected');
                        if (row) {
                            var r = confirm("Do You Want  To Remove The Selected Item")
                            if (r == true) {

                                var contact_id = row.contact_id;
                                $.ajax({
                                    url: 'Customer/deletecustomer/' + contact_id,
                                    success: function (resp) {
                                        $('#customertable').datagrid('reload');
                                        $.notify($.parseJSON(resp).msg, {
                                            type: 'danger',
                                            allow_dismiss: false
                                        });
                                    }
                                });
                            } else
                            {
                                //alert("canceled");
                                $.notify('Canceled operation!', {
                                    type: 'warning',
                                    allow_dismiss: false
                                });
                            }
                        } else
                        {
                            $.notify('Please choose a customer', {
                                type: 'warning',
                                allow_dismiss: false
                            });
                        }
                    }
                }

            ],
            columns: [[
                    {field: 'first_name', title: 'First Name', width: 100, sortable: true, order: 'asc'},
                    {field: 'middle_name', title: 'Middle Name', width: 100, sortable: true, order: 'asc'},
                    {field: 'last_name', title: ' Last Name', width: 100, sortable: true, order: 'asc'},
                    {field: 'company_name', title: 'Company Name', width: 100, sortable: true, order: 'asc'},
                    {field: 'email', title: 'Email', width: 100, sortable: true, order: 'asc'},
                    {field: 'phone', title: 'Phone Number', width: 100, sortable: true, order: 'asc'},
                    {field: 'address', title: 'Address', width: 100, sortable: true, order: 'asc'},
                    {field: 'city', title: 'City', width: 100, sortable: true, order: 'asc'},
                    {field: 'state', title: 'State', width: 100, sortable: true, order: 'asc'},
                    {field: 'pincode', title: 'Pincode', width: 100, sortable: true, order: 'asc'},
                    {field: 'bank_branch', title: 'Bank Branch', width: 100, sortable: true, order: 'asc'},
                    {field: 'relationship', title: 'Relationship', width: 100, sortable: true, order: 'asc'},
                ]]
        });
    });

</script>