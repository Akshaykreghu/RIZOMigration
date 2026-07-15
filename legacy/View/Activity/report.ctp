<style>
    .customized-contents input.form-control, textarea.form-control {
        border-radius: 4px !important;
    }

    .customized-contents input.form-control, textarea.form-control, .select2 .select2-selection {
        -webkit-box-align: center;
        align-items: center;
        background-color: var(--ds-background-subtleNeutral-resting, #F4F5F7);
        border-color: var(--ds-border-neutral, #F4F5F7);
        border-radius: 3px;
        border-style: solid;
        border-width: 2px;
        box-shadow: none;
        cursor: default;
        display: flex;
        flex-wrap: wrap;
        -webkit-box-pack: justify;
        justify-content: space-between;
        min-height: 34px;
        position: relative;
        transition: background-color 200ms ease-in-out 0s, border-color 200ms ease-in-out 0s;
        box-sizing: border-box;
        padding: 0px;
        outline: 0px !important;
        color: #353535;
        font-weight: 600;
        padding-left: 10px;
    }

    .customized-contents #searchqupo {
        width: 100%;
        background: #fff;
        border: 1px solid #ccc;
        margin-top: 5px;
    }

</style>

<!-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.4/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js" type="text/javascript"></script> -->

<!-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js" type="text/javascript"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js" type="text/javascript"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js" type="text/javascript"></script> -->

<section class="content-header" style="margin-left: 20px; margin-right: 20px; ">
    <button onclick="showAddModal(); " class="btn btn-primary pull-right"><li class="fa fa-plus"></li></button>
    <button onclick="loadMenu(this, 'Activity/index'); " style="margin-right: 20px; " class="btn btn-default pull-right">Back to Task & Time</button>
    <h1 style="font-size: 30px;">Task & Time Report</h1>
    <p>View Detailed Report of Tasks and Logs added by Users.</p>
    <hr style="margin-top: 8px;margin-bottom: -2px;">
</section>
<!-- Main content -->
<section class="content customized-contents">



    <div class="col-md-3" style="">
        <div class="row">
            <label class="col-md-3" for="project_id" style="text-align: right; "></label>
            <div class="col-md-9">
                <select id="project_id" onchange="filterbyType(); " name="project_id" class="form-control js-example-basic-single" >
                    <option value="">--Choose Employee--</option>
                    <?php foreach ($employee_list as $key => $value) { ?>                              
                        <option value="<?php echo $value['emp_details']['emp_pkey']; ?>"><?php echo $value['emp_details']['first_name']; ?> <?php echo $value['emp_details']['last_name']; ?></option>
                    <?php } ?>
                </select>
            </div>  
        </div>
    </div>

    <div class="col-md-3" style="">
        <div class="row">
            <label class="col-md-3" for="project_name" style="text-align: right; "></label>
            <div class="col-md-9">
                <select id="project_name" onchange="filterbyType(); " name="project_name" class="form-control js-example-basic-single" >
                    <option value="">--Choose Project--</option>
                    <?php foreach ($project_list as $key => $value) { ?>                              
                        <option value="<?php echo $value['activity_projects']['activity_projects_pkey']; ?>"><?php echo $value['activity_projects']['activity_projects_head']; ?></option>
                    <?php } ?>
                </select>
            </div>  
        </div>
    </div>

    <!-- <div class="col-md-2" style="">
        <div class="row">
            <label class="col-md-3" for="import_emp_branch" style="text-align: right; "></label>
            <div class="col-md-9">
                <select id="activity_type" onchange="filterbyType(); " name="import_emp_branch" class="form-control js-example-basic-single" >
                    <option value="">--Pick a Status--</option>
                    <?php foreach ($activity_status as $key => $value) { ?>                              
                        <option value="<?php echo $value['activity_status']['activity_status_pkey']; ?>"><?php echo $value['activity_status']['activity_status']; ?></option>
                    <?php } ?>
                </select>
            </div>  
        </div>
    </div> -->

    <div class="col-md-2">
        <div class="row">
            <label class="col-md-3" for="due_date" style="text-align: right; "></label>
            <div class="col-md-9">
                <input class="form-control" onchange="filterbyType(); " value="" placeholder="Pick Start Date to Search" id="start_date" />
            </div>  
        </div>
    </div>

    <div class="col-md-2" >
        <div class="row">
            <label class="col-md-3" for="due_date" style="text-align: right; "></label>
            <div class="col-md-9">
                <input class="form-control" onchange="filterbyType(); " value="" placeholder="Pick End Date to Search" id="due_date" />
            </div>  
        </div>
    </div>

    <div class="col-md-12" style="margin-top: 20px; ">
        <br>
        <div class="box box-primary " style="margin-top: 0px;">
            <div class="box-body table-responsive"  id="reportload"  style="margin-top: 0px; padding: 15px; ">
                <div>
                    <h4>No Data Available</h4>
                    <p>Choose your search criteria to generate a report.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>

    $(document).ready(function () { 

        $('#start_date').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            clearBtn: true
        });

        $('#due_date').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            clearBtn: true
        });

        $("#activity_type").select2();
        $("#project_name").select2();
        $("#project_id").select2();

        //   alert("doness");

        // $('#user').easyAutocomplete(usersoptions);
       
        var user_fkey = $('#usr').val();
        
    });

    function filterbyType(obj) {
        var type = $('#activity_type').val();
        var project = $('#project_name').val();
        var start_date = $('#start_date').val();
        var due_date = $('#due_date').val();
        var project_id = $("#project_id").val();
        $("#reportload").html("Loading Report...");

        $("#reportload").load(livesite + 'Activity/laodReoport', { type: type, project: project, due_date: due_date, start_date:start_date, emp_pkey: project_id });

    }
</script>