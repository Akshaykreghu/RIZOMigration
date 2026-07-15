<section class="content-header">
    <!-- edited by athira on 06-05-2025 -->
    <h1 class="text-primary-18">Assessment of the Reporting & Reviewing Officer</h1>
    <!-- end -->
    <hr style="margin-top: 6px;margin-bottom: -2px;">
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- My Leave Requests -->
            <!-- DIRECT CHAT DANGER -->
            <div class="box box-primary">
                <!-- /.box-header -->
                <div class="box-body">
                    <table id="myleaverequeststable" class="table table-bordered table-hover">

                    </table>
                </div><!-- /.box-body -->
            </div><!--/.direct-chat -->
        </div><!-- /.col -->
    </div>
</section>
<script>
    jQuery(document).ready(function() {

        $('#myleaverequeststable').datagrid({
            url: livesite + "HierarchyReview/listreviews",
            pagination: true,
            singleSelect: true,
            rownumbers: true,
            toolbar: [
                // {
                //     text: 'New',
                //     iconCls: 'icon-add',
                //     handler: function() {
                //         showLargeModalForm(livesite + 'HierarchyReview/form/', function() {
                //             $('#myleaverequeststable').datagrid('reload');
                //         });
                //     }
                // },
                {
                    iconCls: 'icon-edit',
                    text: 'View/Edit',
                    handler: function() {
                        var row = $('#myleaverequeststable').datagrid('getSelected');
                        if (row) {

                            var isEdit = 1;
                            if (row.status === 2 || row.status === 3) {
                                var isEdit = 0;
                            }
                            // edited by athira on 06-05-2025
                            showLargeModalForm(livesite + 'HierarchyReview/view/' + isEdit + '/' + row.attr_staff_details_pkey + '/' + row.emp_pkey, function() {
                                //end
                                // $('#myleaverequeststable').datagrid('reload');
                            });
                        } else {
                            $.notify("Please Select a Row", {
                                type: 'danger'
                            });
                        }
                    }
                },
                {
                    iconCls: 'icon-remove',
                    text: 'Delete',
                    handler: function() {
                        var row = $('#myleaverequeststable').datagrid('getSelected');
                        if (row) {
                            if (row.status === 1 || row.status === 4 || row.status === 5) {
                                if (confirm('Are you sure you want to delete this entry?')) {
                                    $.ajax({
                                        url: livesite + 'HierarchyReview/deleteHierarchyReview', // Your PHP endpoint
                                        type: 'POST',
                                        dataType: 'json',
                                        data: {
                                            attr_staff_details_pkey: row.attr_staff_details_pkey
                                        },
                                        success: function(response) {
                                            if (response.status === 'success') {
                                                $('#myleaverequeststable').datagrid('reload'); // Refresh the table
                                                $.notify(response.message || 'Deleted successfully.', {
                                                    type: 'success'
                                                });
                                                // $('#myleaverequeststable').datagrid('reload'); // Refresh the table
                                            } else {
                                                $.notify(response.message || 'Failed to delete.', {
                                                    type: 'error'
                                                });
                                            }
                                        },
                                        error: function() {
                                            $.notify('AJAX request failed.', {
                                                type: 'error'
                                            });
                                        }
                                    });
                                }
                            } else {
                                $.notify('Cannot delete a submitted record.', {
                                    type: 'danger'
                                });
                            }

                        } else {
                            $.notify('Please select a row.', {
                                type: 'danger'
                            });
                        }
                    }
                },
                //edited by Akshay on 6-6-2025
                {
                    text: 'Document View',
                    handler: function() {
                        var row = $('#myleaverequeststable').datagrid('getSelected');
                        console.log(row);
                        if (row) {
                            var url = livesite + 'HierarchyReview/previewDocumentReview/' + row.attr_staff_details_pkey;
                            // window.open(url, '_blank');
                            showLargeModalForm(url);
                        } else {
                            $.notify('Please select a valid row to view document.', { // Edited by Akshay on 6-6-2025
                                type: 'danger'
                            });
                        }
                    }
                }
                //end

            ],
            fitColumns: true,
            pageList: [2, 5, 10, 50, 100],
            rowStyler: function(index, row) {
                if (row.LEAVESTATUS == 'Can not Apply') {
                    return 'background-color:#FF6666;color:#fff;font-weight:bold;';
                }
            },
            columns: [
                [{
                        field: 'emp_fkey',
                        title: 'Employee Name',
                        width: "18%"
                    },
                    //edited by athira on 06-05-2025
                    {
                        field: 'reporting_officer',
                        title: 'Reporting Officer',
                        width: "18%"
                    },
                    {
                        field: 'reviewing_officer',
                        title: 'Reviewing Officer',
                        width: "18%"
                    },
                    //end
                    {
                        field: 'created_by',
                        title: 'Created By',
                        width: "18%"
                    },
                    {
                        field: 'created_date',
                        title: 'Created Date & Time',
                        width: "18%"
                    },
                    // {
                    //     field: 'modified_by',
                    //     title: 'Modified By',
                    //     width: "15%"
                    // },
                    // {
                    //     field: 'modified_date',
                    //     title: 'Modified Date',
                    //     width: "15%"
                    // },
                    // {
                    //     field: 'status',
                    //     title: 'Status',
                    //     width: "20%",
                    //     formatter: function(value, row, index) {
                    //         switch (value) {
                    //             case 1:
                    //                 return 'Employee Drafted the Appraisal';
                    //             case 2:
                    //                 return 'Reporting person submitted the Appraisal';
                    //             case 3:
                    //                 return 'Reviewing person submitted the Appraisal';
                    //             case 4:
                    //                 return 'Reviewed person rejected the Appraisal';
                    //             case 5:
                    //                 return 'Staff Appraisal Initiated';
                    //             default:
                    //                 return 'Unknown';
                    //         }
                    //     }
                    // },
                    {
                        field: 'status_label',
                        title: 'Status',
                        width: "25%"
                    }
                ]
            ]

        });
    });
</script>