<section class="content-header">
    <!-- edited by athira on 06-05-2025 -->
    <h1 style="font-size: 30px;">Self Appraisal Workmen</h1>
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
                    <table id="myreviewtable" class="table table-bordered table-hover">

                    </table>
                </div><!-- /.box-body -->
            </div><!--/.direct-chat -->
        </div><!-- /.col -->
    </div>
</section>
<script>
    jQuery(document).ready(function() {

        $('#myreviewtable').datagrid({
            url: livesite + "HierarchyReview/listreviewsWorkmen",
            pagination: true,
            singleSelect: true,
            rownumbers: true,
            toolbar: [
                //edited by Akshay on 6-6-2025
                {
                    text: 'Document View',
                    handler: function() {
                        var row = $('#myreviewtable').datagrid('getSelected');
                        console.log(row);
                        if (row) {
                            if (row.status_label.toLowerCase() === "reviewing person submitted the appraisal".toLowerCase()) {
                                var url = livesite + 'HierarchyReview/previewDocumentReview/' + row.attr_staff_details_pkey;
                                // window.open(url, '_blank');
                                showLargeModalForm(url);
                            } else {
                                $.notify('The reviewing officer has not submitted the appraisal.', {
                                    type: 'danger'
                                });
                            }

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