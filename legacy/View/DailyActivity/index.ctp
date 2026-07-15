<style>
    .form-horizontal .control-label{
        text-align: left;
    }
</style>

<section class="content-header">
    <h2 style="text-align:left;">Daily Activity Master</h2>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box ">
                <br>
                <div class="box-body">
                    <!-- Employee import form -->
                    <form class="form-horizontal" method="post" action="" id="dailyactivityform">
                        <div class="row">
                            <div class="form-group">
                                <div class="col-md-12">
                                   <div class="col-sm-2 control-label" for="employee" style="text-align: left;">Search Project  <span style="padding-left: 60px;">:</span> </div>                        
                                                    
                                    <div class="col-md-4">
                                        <select id="project" class="form-control js-example-basic-single" name="project" onchange="filterAttendanceupload(this);"  >

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

<script>
    //filtter using branch 
    function filterEmployees(branch)
    {
         $("#project").select2(
                {
                    //closeOnSelect:false,
                    placeholder: "All",
                    allowClear: true,
                    ajax: {
                        url: livesite + "DailyActivity/filtersite" ,
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
        var project = $('#dailyactivityform #project').val();

        $('#att_table').datagrid('load', {
           // branch: branch,
            project: project
        });
          filterEmployees();

    }



    jQuery(document).ready(function () {
        
        filterEmployees();
        $("#filterby_branch").select2();
        $("#filterby_month").select2();
        
        var project = $('#dailyactivityform #project').val();

        $('#att_table').datagrid({
            url: livesite + "DailyActivity/lists",
            pagination: true,
            singleSelect: true,
            rownumbers: true,
            fitColumns: true,
            //PostsearchFilter:true,
            autoRowHeight: false,
            pageSize: 10,
            pageList: [2, 5, 10, 50, 100],
            queryParams: {
                    project: project
            },
            toolbar: [{
                    text: 'New',
                    iconCls: 'icon-add',
                    handler: function () {
                        showModalForm(livesite + 'DailyActivity/activity_form')
                    }
                },'-',
                {
                    iconCls: 'icon-edit',
                    text: 'Edit',
                    handler: function () {
                        var row = $('#att_table').datagrid('getSelected');
                        if (row) {
				
                       showModalForm(livesite + 'DailyActivity/activity_form/' + row.activity_pkey)  
                        } else
                            $.notify('Please Select a record to Edit ', {
                                type: 'danger',
                                allow_dismiss: false
                            });
                    }
                } ,'-',
                {
                    iconCls:'icon-cancel',
                    text: "remove",
                    handler: function () {
                     var row = $('#att_table').datagrid('getSelected');
                     if (row) {
                      var r=confirm("Do You Want To Remove The Selected Item")
                      if(r==true){
                               
                               var activity_pkey = row.activity_pkey;
                               $.ajax({
                                 url: livesite + 'DailyActivity/delete/' + activity_pkey,
                                 success: function (resp) {
                                   
                                        //if (resp.msg) {
                                            $.notify("Daily Activity Deleted.", {
                                                type: 'success',
                                                allow_dismiss: true

                                            });
                                       // }
					reloadTable('att_table');
					$('#att_table').datagrid('reload');
                               }
                           });
                           }
                           else
                           {
                             $.notify("Cancelled", {
                                                type: 'danger',
                                                allow_dismiss: true

                                            });
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
                    {field: 'site_name', title: 'Project Name', width: "20%"},
                    {field: 'site_id', title: 'Project ID', width: "20%"},
                    {field: 'date', title: 'Working Date', width: "20%"},
		    {field: 'work_detail', title: 'Work Detail', width: "40%"}
		    
                ]],
	    onSearch:function(s){
                $('#att_table').datagrid('load',{
                    site_name: $('#searchqupo').val(),
		    site_id: $('#searchqupo').val(),
                });
            }  
        });
    });
</script>