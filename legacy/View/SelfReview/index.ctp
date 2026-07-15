<style>
    .datagrid-wrap.panel-body.panel-body-noheader {
        width: 100% !important;
        /* max-width: 1400px !important; */
    }

    .datagrid-view {
        width: 100% !important;
        /* max-width: 1400px !important; */
    }

    .datagrid-view1 {
        width: 2% !important;
        /* max-width: 1400px !important; */
    }

    .datagrid-view2 {
        width: 98% !important;
        /* max-width: 1400px !important; */
    }

    .datagrid-header {
        width: 100% !important;
        /* max-width: 1400px !important; */
    }

    .datagrid-body {
        width: 100% !important;
        /* max-width: 1400px !important; */
    }
</style>

<section class="content-header">
    <h1 class="text-primary-18">Self Appraisal</h1>
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
            // url: livesite + "SelfReview/listreviews",
            url: livesite + "SelfReview/listreviews_self_review",
            pagination: true,
            singleSelect: true,
            rownumbers: true,
            toolbar: [{
                    iconCls: 'icon-edit',
                    text: 'View/Edit',
                    handler: function() {
                        var row = $('#myleaverequeststable').datagrid('getSelected');
                        if (row) {
                            console.log('row.status', row.status === 'Applied');

                            var isEdit = 0;
                            if (row.status === 'Draft' || row.status === 'New' || row.status === 'Drafted' || row.status === 'Reporting Person Rejected the Appraisal') {
                                var isEdit = 1;
                            }
                            showLargeModalForm(livesite + 'SelfReview/view/' + isEdit + '/' + row.pkey, function() {
                                // $('#myleaverequeststable').datagrid('reload');
                            });
                        } else {
                            $.notify("Please Select a Row", {
                                type: 'danger'
                            });
                        }
                    }
                },
                //edited by athira on 20-05-2025
                {
                    text: 'Pdf',
                    handler: function() {
                        var row = $('#myleaverequeststable').datagrid('getSelected');
                        if (row && row.emp_pkey) {
                            var url = livesite + 'SelfReview/previewPdfReview/' + row.emp_pkey + '/' + row.pkey;
                            window.open(url, '_blank');
                        } else {
                            $.notify('Please select a valid row to download the PDF.', {
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
                    {
                        field: 'created_by',
                        title: 'Created By',
                        width: "18%"
                    },
                    {
                        field: 'created_date',
                        title: 'Created Date & Time',
                        width: "15%"
                    },
                    //{
                    //    field: 'modified_by',
                    //    title: 'Modified By',
                    //    width: "18%"
                    //},
                    //{
                    //    field: 'modified_date',
                    //    title: 'Modified Date & Time',
                    //    width: "15%"
                    //},
                    // {
                    //     field: 'status',
                    //     title: 'Status',
                    //     width: "14%",
                    //     formatter: function(value, row, index) {
                    //         switch (value) {
                    //             case 'New':
                    //                 return 'Self Appraisal Initiated';
                    //             case 'Drafted':
                    //                 return 'Self Appraisal Drafted';
                    //             case 'Applied':

                    //             default:
                    //                 return value;
                    //         }
                    //     }
                    // },
                    {
                        field: 'status_label',
                        title: 'Status',
                        width: "20%",
                    }
                ]
            ]

        });
    });
</script>