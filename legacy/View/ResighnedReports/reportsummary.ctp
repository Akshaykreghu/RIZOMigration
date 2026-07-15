<div class="modal-body" style="overflow-y: auto;">
    <h3 align="center" style="font-weight:bold; font-size: 30px;">Employee Resignation Report : <?php echo $start_month . " to " . $end_month; ?></h3>
    <?php
        if($criteria_name=="Units"){
            $msg="Branch ";
        }else{
            $msg="Employee ";
        }
    ?>
    <h4 align="center" style="font-weight:bold;">(<?php echo isset($user_id) ? "Belonging to a ".$msg."Report run by " . ($user_id) . " - " . $date_time : ''; ?>)</h4>

    <div class="row">
        <div class="col-md-12">
            <div class="box ">
                <?php
                if (!empty($arr_leavepolicydetails_for_template) || count($arr_leavepolicydetails_for_template) > 0) {
                    ?>
                    <?php
                    $i = 0;
                    foreach ($arr_leavepolicydetails_for_template as $value) {
                        $i += 1;
                        ?>
                        <?php
                        if (isset($value['summary']['0']['EmployeeDetails']['first_name'])) {
                            ?>
                            <div class="box-body">
                                <?php
                                if ($criteria == "Units") {
                                    ?>
                                    <fieldset>
                                        <legend>List of  <?php echo isset($value['summary']['0']['Branch']['branch_name']) ? $value['summary']['0']['Branch']['branch_name'] : ''; ?>  </legend>
                                        <div class="row">
                                            <div class="col-md-12">    
                                            </div>
                                        </div>
                                    </fieldset>
                                    <br>
                                    <?php
                                }
                                ?>
                                <fieldset>
                                    <table  class="table no-margin" id="todayattandence">
                                        <thead>
                                            <tr>
                                                <th width="auto">Sl No</th>
                                                <th width="auto">Company ID</th>
                                                <th width="auto">Employee Name</th>
                                                <th width="auto">Branch</th>
                                                <th width="auto">Department</th>
                                                <th width="auto">Designation</th>
                                                <th width="auto">Reason</th>
                                                <th width="auto">Notice Period</th><!--This field added by ARUL P DAS on 27/1/2020 -->
                                                <th width="auto">Resignation Submitted Date</th>
                                                <th width="auto">Last Applied Date</th><!-- Added by **ARUL P DAS on 5/12/2019 -->
                                                <th width="auto">Last Working Date</th>
                                                <th width="auto">Last Approved Date</th><!-- Added by **ARUL P DAS on 5/12/2019 -->
                                                <!-- <th>Reason Description</th> -->
                                                <th width="auto">Remarks</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $arr_data = $value['summary']; ?>
                                            <?php
                                            if (count($arr_data) >= 0) {
                                                $count = 0;
                                                ?>
                                                    <?php foreach ($arr_data as $val) { ?>
                                                    <tr> 
                    <?php $count = $count + 1; ?>
                                                        <td><?php echo $count; ?></td>
                                                        <td><?php echo $val['EP']['emp_company_id']; ?></td>
                                                        <td><?php echo $val['EmployeeDetails']['first_name'] . ' ' . $val['EmployeeDetails']['last_name']; ?></td>
                                                        <td><?php echo $val['Branch']['branch_name']; ?></td><!--This field added by ARUL P DAS on 16/12/2019-->
                                                        <td><?php echo $val['EI']['designation']; ?></td><!--This field added by ARUL P DAS on 16/12/2019-->
                                                        <td><?php echo $val['EI']['department']; ?></td><!--This field added by ARUL P DAS on 16/12/2019-->
                                                        <td><?php echo $val['Termination']['Reason']; ?></td>
                                                        <td><?php echo $val['Termination']['notice_period']; ?></td><!--This field added by ARUL P DAS on 27/1/2020 -->
                                                        <td><?php echo $val['Termination']['submitted_date']; ?></td>
                                                        <td><?php echo $val['Termination']['last_applied_date']; ?></td><!-- Added by **ARUL P DAS on 5/12/2019 -->
                                                        <td><?php echo $val['Termination']['last_working_date']; ?></td>
                                                        <td><?php echo $val['Termination']['last_approved_working_date']; ?></td><!-- Added by **ARUL P DAS on 5/12/2019 -->
                                                        <!-- <td><?php echo $val['Termination']['Reason_desc']; ?></td> -->
                                                        <td><?php echo $val['Termination']['remarks']; ?></td>

                                                    </tr>
                                                <?php } ?>
            <?php } else { ?>
                                                <tr>
                                                    <td colspan="4">No employees found under this Criteria</td>
                                                </tr>  
            <?php } ?>
                                        </tbody>
                                    </table>
                                </fieldset>
                                <br>
                                <!-- <fieldset>
                                     <legend>Employee List</legend>
                                     <table class="table table-bordered">
                                         <thead>
                                           <tr>
                                               <th>Name</th>
                                               <th>Designation</th>
                                               <th>Branch</th>
                                           </tr>
                                         </thead>
                                         <tbody>
                                <?php $arr_data = $value['employees']; ?>
                                <?php if (count($arr_data) >= 0) { ?>
                <?php foreach ($arr_data as $val) { ?>
                                                                             <tr> 
                                                                                 <td><?php echo $val['EmployeeDetails']['first_name'] . ' ' . $val['EmployeeDetails']['last_name']; ?></td>
                                                                                 <td><?php echo $val['EmployeeProfessionalDetails']['designation']; ?></td>
                                                                                 <td><?php echo $val['Units']['branch_name']; ?></td>
                                                                             </tr>
                                    <?php } ?>
            <?php } else { ?>
                                                                 <tr>
                                                                     <td colspan="4">No employees found under this shift</td>
                                                                 </tr>  
            <?php } ?>
                                         </tbody>
                                     </table>
                                 </fieldset> -->
                            </div>
                            <?php
                        }
                    }
                } else {
                    echo "<h2>No Data Available With The Selected Criteria</h2> ";
                }
                ?> <!-- /.box-body -->
            </div>
        </div>
    </div>
    <!--    <div class="row">
            <div class="form-group">
                <div class="col-md-12" align="right">
                    <a href="#" class="btn btn-default" onclick="downloadReport('Attendance','pdf');" ><i class="icon-file"></i>Download As PDF</a>
                    <a href="#" class="btn btn-default" onclick="downloadReport('Attendance','excel');"><i class="icon-file"></i>Download As Excel</a>
                </div>
            </div>
        </div>-->
</div>
<script>
    $(document).ready(function () {
        $('#todayattandence').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'print',
                    messageTop: '(<?php echo isset($user_id) ? "Belonging to a ".$msg."Report run by " . ($user_id) . " - " . $date_time : ''; ?>)',
                    messageBottom: null,
                    title: 'Resignation Report : <?php echo $start_month . " to " . $end_month; ?>'
                }, {
                    extend: 'pdf',
//                    messageTop: 'My payroll Master Resignation Reports ',
                    messageBottom: null,
                    title: 'Resignation Report : <?php echo $start_month . " to " . $end_month; ?>\n(<?php echo isset($user_id) ? "Belonging to a ".$msg."Report run by " . ($user_id) . " - " . $date_time : ''; ?>)',
                    extend: 'pdfHtml5',
                            orientation: 'landscape',
                    pageSize: 'LEGAL',
                    customize: function (doc) {//This is to align and adjust width of table. By ***ARUL P DAS on 27/12/2019
                        doc.content[1].table.widths = Array(doc.content[1].table.body[0].length + 1).join('*').split('');
                        doc.styles.tableHeader.alignment = 'left';
                    }
                }, {//Made some changes here.**ARUL 19/10/2019
                    extend: 'excel',
                    // text:'Test',
                    // titleAtter:'Test',
                    messageBottom: null,
                    // headerName:'Test',
                    // title: 'My payroll Master - Resignation Reports '/////////*******The title repeats in excel document
                    title: 'Resignation Report : <?php echo $start_month . " - " . $end_month; ?>',
                    messageTop: '(<?php echo isset($user_id) ? "Belonging to a ".$msg."Report run by " . ($user_id) . " - " . $date_time : ''; ?>)',
                    sheetName: 'Resignation Report', ///////Here setting the sheet name
                    "customize": function (xlsx) {
                        var sSh = xlsx.xl['styles.xml'];
                        var lastXfIndex = $('cellXfs xf', sSh).length - 1;
                        var i;
                        var y;
                        //s3 is a combination of built in fonts 64 (2 dec places which has numFmtId="4") AND 2 (bold)
                        //just copied the xf of "two decimal places" and and changed the fontId based on "bold" 
                        var s3 = '<xf numFmtId="4" fontId="2" fillId="0" borderId="0" applyFont="1" applyFill="0" applyBorder="1" xfId="0" applyNumberFormat="1"/>'
                        var s4 = '<xf numFmtId="0" fontId="2" fillId="0" borderId="0" applyFont="1" applyFill="0" applyBorder="1" xfId="0" applyAlignment="1">' +
                                '<alignment horizontal="center" wrapText="1"/></xf>'
                        sSh.childNodes[0].childNodes[5].innerHTML += s3 + s4;

                        var greyBoldCentered = lastXfIndex + 2;
                        var sheet = xlsx.xl.worksheets['sheet1.xml'];
                        $('row:eq(0) c', sheet).attr('s', greyBoldCentered);  //grey background bold and centered, as added above
                        //$('row c[r=A2]', sheet).attr( 's', greyBoldCentered );

                    }
                }],
            "info": true,
            "autoWidth": false,
            "initComplete": function () {
                //actions
            },
//             "createdRow": function ( row, data, index ) {
//                if ( data[5].replace(/[\$,]/g, '') * 1 > 150000 ) {
//                    $('td', row).eq(5).addClass('highlight');
//                }
//            },
            "scrollCollapse": true,
        });
        $('.buttons-print').ready(function () {
            $('.buttons-print').html('<li class="fa fa-print"></li>').addClass('btn-primary').addClass('btn');
            ;
        });
        $('.buttons-pdf').html('<li class="fa fa-file-pdf-o"></li>').addClass('btn-danger').addClass('btn');
        ;
        $('.buttons-excel').html('<li class="fa fa-file-excel-o"></li>').addClass('btn-success').addClass('btn');
    });
</script>