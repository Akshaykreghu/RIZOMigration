
    <style>
 /* <!-- edited by bindhu 19-02-2026 --> */
    .heading {
        display: flex;
        flex-direction: row;
        align-items: end;
        justify-content: space-between;
     margin: 0 0 10px 0;
    }

    .home {
        background-color: #ffffffff;
        border-radius: 50px;
        padding: 2px 15px;
        color: #1e516e !important;
        /* margin-right: 15px; */
        color: white;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        border: #1e516e 1px solid;
    
    }
    .content-header{
        padding: 0;
    }
    /* end */
</style>

<div class="modal-body" style="overflow-y: auto;">
    <!-- <div style="font-size: 30px;text-align: left;">CTC Detail</div>
    <hr style="margin-top: 0px; margin-bottom: 15px;"> -->

     <!-- /* edited by bindhu 19-02-2026 */ -->
<section class="content-header heading">
    <h1 class="text-primary-18"> Cost To Company</h1>
  <div class="text-primary-16 home"
    style="display:flex; align-items:center; gap:10px; cursor:pointer;">
    <i class="fa" style="font-size:16px;">&#xf104;</i>
    Back
  </div>
</section>
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box box-body" style="border: white;">
                    <?php $i = 0;
                    foreach ($arr_salary_for_template as $value) {
                        if (count($value['summary']) !== 0) {
                            $i += 1;
                    ?>
                            <div class="box-body">
                                <fieldset>
                                    <legend> <?php echo isset($value['summary']['0']['0']['first_name']) ? $value['summary']['0']['0']['first_name'] : '';
                                                echo ' ';
                                                echo isset($value['summary']['0']['0']['middile_name']) ? $value['summary']['0']['0']['middile_name'] : '';
                                                echo ' ';
                                                echo  isset($value['summary']['0']['0']['last_name']) ? $value['summary']['0']['0']['last_name'] : ''; ?> </legend>
                                    <div class="row">
                                        <div class="col-md-12">

                                        </div>
                                    </div>
                                    <div class="col-md-6" style="padding-left: 0px;">Employee Id : <span style="font-weight: 700;"><?php echo  isset($value['summary']['0']['0']['emp_company_id']) ? $value['summary']['0']['0']['emp_company_id'] : ''; ?> </span></div>
                                    <div class="col-md-6">Branch : <span style="font-weight: 700;"><?php echo  isset($value['summary']['0']['0']['branch_name']) ? $value['summary']['0']['0']['branch_name'] : ''; ?> </span> </div>
                                    <div class="col-md-6" style="padding-left: 0px;">Designation :<span style="font-weight: 700;"> <?php echo  isset($value['summary']['0']['0']['desig_name']) ? $value['summary']['0']['0']['desig_name'] : ''; ?> </span> </div>
                                    <div class="col-md-6">Department : <span style="font-weight: 700;"><?php echo  isset($value['summary']['0']['0']['dept_name']) ? $value['summary']['0']['0']['dept_name'] : ''; ?> </span> </div>

                                </fieldset>

                                <br>
                                <fieldset>


                                    <table class="table table-bordered">
                                        <thead>
                                            <tr style="background: whitesmoke;font-size: 16px;">

                                                <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->


                                                <th>Salary Components</th>
                                                <th style="text-align: center;">Amount</th>
                                                

                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php $arr_data  = $value['summary'];  ?>
                                            <?php if (count($arr_data) >= 0) {
                                                $sum = 0; ?>
                                                <?php foreach ($arr_data as $val) {
                                                    if ($val['0']['item_part'] != 'Indirect') { ?>
                                                        <tr> <?php $sum = $sum + $val['0']['structure_det_value']; ?>
                                                            <td><?php echo $val['0']['salary_head_item_desc']; ?></td>
                                                            <td style="font-weight: 700;text-align:center;"><?php echo $val['0']['structure_det_value']; ?></td>
                                                            <!--                                            <td><?php echo $val['0']['head_operator']; ?></td>-->

                                                            <!--                                            <td><?php echo $val['0']['item_part']; ?></td>-->
                                                        </tr>
                                                    <?php }
                                                    ?>

                                                <?php } ?>
                                                <tr>
                                                    <th>Gross Salary</h>
                                                    <th style="text-align:center;"><span style="color: red;"><?php echo $sum; ?></span></th>
                                                </tr>
                                                <?php foreach ($arr_data as $val) {
                                                    if ($val['0']['item_part'] == 'Indirect') { ?>
                                                        <tr> <?php $sum = $sum + $val['0']['structure_det_value']; ?>
                                                            <td><?php echo $val['0']['salary_head_item_desc']; ?></td>
                                                            <td style="font-weight: 700;text-align:center;"><?php echo $val['0']['structure_det_value']; ?></td>
                                                            <!--                                            <td><?php echo $val['0']['head_operator']; ?></td>-->

                                                            <!--                                            <td><?php echo $val['0']['item_part']; ?></td>-->
                                                        </tr>
                                                    <?php }
                                                    ?>

                                                <?php } ?>
                                                <tr>
                                                    <th>Cost To Company</h>
                                                    <th style="text-align:center;"><span style="color: red;"><?php echo $sum; ?></span></th>
                                                </tr>
                                            <?php } else { ?>
                                                <tr>
                                                    <td colspan="4">No employees found under this data</td>
                                                </tr>
                                            <?php } ?>


                                        </tbody>
                                    </table>

                                </fieldset>
                                <br>
                            </div>

                    <?php }
                    } ?> <!-- /.box-body -->
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="form-group">
            <div class="col-md-12" align="right">
                <a href="#" class="btn btn-info" style="background: #00659f;" onclick="downloadReport('salarystructure','pdf');"><i class="icon-file"></i>Download As PDF</a>
                <!--<a href="#" class="btn btn-info" style="background: #00659f;" onclick="downloadexcelReport('salarystructure','excel');"><i class="icon-file"></i>Download As Excel</a>-->
            </div>
        </div>
    </div>
</div>
<div class="form-group">
    <form id="form-showreport" method="post" action=""></form>
    <!--        <div class="col-md-12" align="right">
                <a href="#" class="btn btn-default" onclick="downloadReport();" ><i class="icon-file"></i>Download As PDF</a>
                <a href="#" class="btn btn-default" onclick="downloadexcelReport();"><i class="icon-file"></i>Download As Excel</a>
            </div>-->
</div>

<script>
    function downloadReport(type, mode) {
        //   alert("hi");
        $('#form-showreport').attr('action', livesite + 'Empreport/downloads/' + mode);
        $('#form-showreport').submit();

    }

    function downloadexcelReport(type, mode) {

        $('#form-showreport').attr('action', livesite + 'Empreport/downloadexcels/' + mode);
        $('#form-showreport').submit();

    }
     // edited by bindhu 19-02-2026
 $(".home").on("click", function() {
        $("#container").isLoading({
            text: "Loading",
            position: "overlay",
        });

        $("#container").load(livesite + "EmployeeMenu/index", function() {
            isDashboardShown = false;
        });


    });
    //  edited by bindhu 19-02-2026 end
</script>