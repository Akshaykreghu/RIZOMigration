<style>
    .form-horizontal .control-label{

        text-align: left;

    }
</style>

<section class="content-header">
    <h1 style="text-align:left; " class="text-primary-18"> Site Master   </h1>
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
                                    <div class="col-sm-2 control-label" for="employee" style="text-align: left;">Site End Date<span style="padding-left: 60px;">:</span> </div>                        
                                                    
                                    <div class="col-md-3">
                                        <select id="enddate" class="form-control js-example-basic-single" name="enddate" onchange="filterAttendanceupload(this);"  >
                                        <option value="0">All</option>                                        
                                        <option value="7">Inactive in a week</option>
                                        <option value="31">Inactive in a month</option>
                                        <option value="-31">Inactive</option>
                                        </select>

                                    </div>    
                                </div>
                                                                      
                            </div>
                        </div>
<!--                        <div class="row">
                            <div class="form-group">
                                <div class="col-sm-4">
                                    <label class="col-sm-5 control-label" for="filterby_branch">Choose Branch</label>
                                    <div class="col-md-7">
                                        <select id="filterby_branch" name="filterby_branch" class="form-control js-example-basic-single" onchange="filterAttendanceupload(this);" >
                                            <option value="">All</option>
                                            <?php foreach ($arr_branchs as $key => $value) { ?>                              
                                                <option  value="<?php echo $value['Units']['branch_code']; ?>"><?php echo $value['Units']['branch_name']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="col-sm-5 control-label" for="employee">Choose Employee</label>                        
                                    <div class="col-md-7">
                                        <select id="emp_fkey" class="form-control js-example-basic-single" name="emp_fkey" onchange="filterAttendanceupload(this);"  >

                                        </select>

                                    </div>    
                                </div>
                                                                    
                            </div>
                        </div>-->
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
  <!-- Edited by Akshay on 23-8-2025 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- End -->
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
                        url: livesite + "SiteAttendance/filtersite" ,
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
       //var branch = $('#importemployeectcform #filterby_branch').val();
        var site = $('#importemployeectcform #site').val();
        var siteenddate = $('#importemployeectcform #enddate').val();
        $('#att_table').datagrid('load', {
           // branch: branch,
            site: site,
            siteenddate : siteenddate
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
            url: livesite + "SiteAttendance/lists",
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
            toolbar: [{
                    text: 'New',
                    iconCls: 'icon-add',
                    handler: function () {
                        showLargeModalForm(livesite + 'SiteAttendance/form')
                    }
                },
                {
                    iconCls: 'icon-edit',
                    text: 'Edit',
                    handler: function () {
                        var row = $('#att_table').datagrid('getSelected');
                        if (row) {

                            showLargeModalForm(livesite + 'SiteAttendance/form/' + row.site_pkey)  
                        } else
                            $.notify('Please Select A record to Edit ', {
                                type: 'danger',
                                allow_dismiss: false,
                                z_index: 99999 // Edited by Akshay on 23-8-2025
                            });
                    }
                }
                <?php
                if($user_group!=2){
                ?>
                ,
                {
                    iconCls: 'icon-edit',
                    text: "Allocate site",
                    handler: function () {
                        var row = $('#att_table').datagrid('getSelected');
                        //console.log(row);
                        if (row) {
                            var site_pkey = row.site_pkey;
                            var url = 'SiteAttendance/site_allocate/' + site_pkey;
                            showModalForm(url);
                            //$('#modalDiv').load(url, function () {
                            //    $('#modalDiv').modal('show');
                            //});
                        } else
                        {
                            //alert("Please choose a store");
                            $.notify('Please choose a store', {
                                type: 'warning',
                                allow_dismiss: false,
                                z_index: 99999 // Edited by Akshay on 23-8-2025
                            });
                        }
                    }
                }
                <?php
                }
                ?>
                ],
            
            columns: [[
                    //{field: 'emp_expenses_pkey', title: '', width: "%"},
                    {field: 'site_name', title: 'Site Name', width: "25%"},
                    {field: 'site_id', title: 'Site Code', width: "25%"},
                    {field: 'counts', title: 'Total Allocated', width: "25%"},
                    {field: 'customer_name', title: 'Contact Name', width: "25%"},
                    //  {field: 'is_credited', title: 'Credited Rate', width: '10%'},
                ]]
        });
 
    });
</script>