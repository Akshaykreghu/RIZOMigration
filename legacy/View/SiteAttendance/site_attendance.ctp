
<section class="content-header">
    <h1 style="text-align:left; font-size: 3em;"> Uniform Allocate</h1>
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
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</section>