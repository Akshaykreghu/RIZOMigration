<style>
    .form-horizontal .control-label{

        text-align: left;

    }
</style>

<section class="content-header">
    <h1 style="text-align:left; " class="text-primary-18"> Site Master Approval</h1>
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
                                <div class="col-md-12">
                                   <div class="col-sm-2 control-label" for="employee" style="text-align: left;">Search Site  <span style="padding-left: 60px;">:</span> </div>                        
                                                    
                                    <div class="col-md-4">
                                        <select id="site" class="form-control js-example-basic-single" name="site" onchange="filterAttendanceupload(this);"  >

                                        </select>

                                    </div>  
                                    <div class="col-sm-2 control-label" for="employee" style="text-align: left;">Status<span style="padding-left: 60px;">:</span> </div>                        
                                                    
                                    <div class="col-md-3">
                                        <select id="enddate" class="form-control js-example-basic-single" name="enddate" onchange="filterAttendanceupload(this);"  >
                                        <option value="0">All</option>                                        
                                        <option value="2">Approved</option>
                                        <option value="1">Pending</option>
                                        <option value="3">Rejected</option>
                                        </select>

                                    </div>    
                                </div>
                                                                      
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
    function filterEmployees(branch)
    {
         $("#site").select2(
                {
                    //closeOnSelect:false,
                    placeholder: "All",
                    allowClear: true,
                    ajax: {
                        url: livesite + "SiteAttendanceApply/filtersite" ,
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term, // search term
                                page: params.page
                            };
                        },
                        processResults: function (data, params) {
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

        var site = $('#importemployeectcform #site').val();
        var siteenddate = $('#importemployeectcform #enddate').val();
        console.log('Site', site);
        console.log('Status', siteenddate);
        $('#att_table').datagrid('load', {
           // branch: branch,
            site_name: site,
            status : siteenddate
        });
          filterEmployees();

    }


    jQuery(document).ready(function () {
        $('#loader').hide(); 
      
        filterEmployees();
        $("#filterby_branch").select2();
        $("#filterby_month").select2();
        var siteenddate = $('#importemployeectcform #enddate').val();
        var site = $('#importemployeectcform #site').val();

        $('#att_table').datagrid({
            url: livesite + "SiteAttendanceApply/approvallists",
            pagination: true,
            singleSelect: true,
            rownumbers: true,
            fitColumns: true,
            //PostsearchFilter:true,
            autoRowHeight: false,
            pageSize: 10,
            pageList: [2, 5, 10, 50, 100],
            queryParams: {
                    site: site,
                    siteenddate : siteenddate
            },
            toolbar: [
                {
                    iconCls: 'icon-edit',
                    text: 'Details',
                    handler: function () {
                        var row = $('#att_table').datagrid('getSelected');
                        console.log(row);
                        if (row) {
                            showLargeModalForm(livesite + 'SiteAttendanceApply/viewapproval/' + row.site_master_approval_pkey +'/'+ row.status )  
                        } else
                            $.notify('Please Select a record to view details ', {
                                type: 'danger',
                                allow_dismiss: false
                            });
                    }
                },
                //Approve button
                {
                    iconCls: 'icon-edit',
                    text: 'Approve',
                    handler: function () {
                        var row = $('#att_table').datagrid('getSelected');
                        console.log(row);
                        if (row) {
                            if(row.status == "Pending"){ 
                            showModalForm(livesite + 'SiteAttendanceApply/confirmation_modal/' + row.site_master_approval_pkey +'/'+ row.status +'/'+ row.site_pkey +'/'+ 'Approve' )  
                            } else {
                                $.notify('Please Select a pending record ', {
                                type: 'danger',
                                allow_dismiss: false
                            });
                            }
                        } else
                            $.notify('Please Select a record to approve ', {
                                type: 'danger',
                                allow_dismiss: false
                            });
                    }
                },
                //Reject button
                {
                    iconCls: 'icon-edit',
                    text: 'Reject',
                    handler: function () {
                        var row = $('#att_table').datagrid('getSelected');
                        console.log(row);
                        if (row) {
                            if(row.status == "Pending"){ 
                            showModalForm(livesite + 'SiteAttendanceApply/confirmation_modal/' + row.site_master_approval_pkey +'/'+ row.status +'/'+ row.site_pkey +'/'+ 'Reject' )  
                            } else{
                                $.notify('Please Select a pending record ', {
                                type: 'danger',
                                allow_dismiss: false
                            });
                            }
                        } else
                            $.notify('Please Select a record to reject ', {
                                type: 'danger',
                                allow_dismiss: false
                            });
                    }
                },
               
                <?php
                if($user_group!=2){
                ?>
              
                <?php
                }
                ?>
                ],
            
            columns: [[
                    //{field: 'emp_expenses_pkey', title: '', width: "%"},
                    {field: 'site_name', title: 'Site Name', width: "25%"},
                    {field: 'site_id', title: 'Site Code', width: "12%"},
                    {field: 'created_date', title: 'Changes Applied Date & Time', width: "18%"},
                    {field: 'created_by', title: 'Changes Applied By', width: "18%"},
                     {field: 'status', title: 'Status', width: '10%'},
                     {field: 'remarks', title: 'Remarks',width: '25%'}
                ]],
                remoteFilter: true, // Enable remote filtering (filter data on the server side)
                onBeforeLoad: function(param) {

                // Add the 'filter' parameter to the request when filtering is applied
                if (param.filterRules && param.filterRules.length > 0) {
                    param.filter = JSON.stringify(param.filterRules);
                }
                }


        });


        
 
    });
</script>