
<div class="modal-body" style="overflow-y: auto;">
    <legend>Employees Active Now</legend>
    <div class="row">
        <div class="col-md-12">
            <div class="box ">
                <div class="box-body">
                    <fieldset>
			    <table class="table table-bordered todayattandence" id="todays_present_details">
                            <thead>
                              <tr>
                                  <th style="width:10px;">Sl.No.</th>
                                  <th>Employee Name</th>
                              </tr>
                            </thead>
                            <tbody>
                               <?php $i=0; foreach ($results as $val) {
                   $i += 1; 
                   
                  ?>
                                    
                                        <tr>
                                            <td><?php echo $i;?></td>
                                            <td><?php echo $val['present_today']['EmpName']; ?></td>
                                            
                                             <?php
                                            //$fromserver = $val['present_today']['LOGDATE'];
                                            //date_default_timezone_set('UTC');
                                            //$fs = strtotime($fromserver);
                                            //2014-07-02T05:13:45z
                                            //echo $fromserver_formated = date("d-M-Y  h:i A",strtotime($val['present_today']['LOGDATE']));  ?>
                                     
                                        </tr>
                            <?php  } ?>
                            </tbody>
                        </table>
		
                    </fieldset>
                </div>
                 <!-- /.box-body -->
            </div>
        </div>
    </div>    

</div>
<script> 
    $(document).ready(function () {
        $('#todays_present_details').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": true,
            dom: 'Bfrtip',
            buttons: [
                {
                     extend: 'print',
                     messageTop: 'My Payroll Master Employees Active Now Report.',
                     messageBottom: null,
                     title: 'My Payroll Master - Employees Active Now Report'
                },
                {
                     extend: 'pdf',
                     messageTop: 'My Payroll Master Employees Active Now Report.',
                     messageBottom: null,
                     title: 'My Payroll Master - Employees Active Now Report'
                },
                {
                     extend: 'excel',
                    // messageTop: 'My Payroll Master Employees Active Now Report.',
                     messageBottom: null,
                     title: 'My Payroll Master - Employees Active Now Report',
                      //edited by megha on 22_11_2019 heading styles 
                     "customize": function( xlsx ) {
                        var sSh = xlsx.xl['styles.xml'];
                        var lastXfIndex = $('cellXfs xf', sSh).length - 1;
                        var i; var y;
                        //n1, n2 ... are number formats; s1, s2, ... are styles
                        var n1 = '<numFmt formatCode="##0.0000%" numFmtId="300"/>';
                        var s1 = '<xf numFmtId="300" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/>';
                        var s2 = '<xf numFmtId="0" fontId="2" fillId="2" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyAlignment="1">'+
                        '<alignment horizontal="center"/></xf>';
                        //s3 is a combination of built in fonts 64 (2 dec places which has numFmtId="4") AND 2 (bold)
                        //just copied the xf of "two decimal places" and and changed the fontId based on "bold" 
                        var s3 = '<xf numFmtId="4" fontId="2" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/>'
                        var s4 = '<xf numFmtId="0" fontId="2" fillId="2" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyAlignment="1">'+
                        '<alignment horizontal="center" wrapText="1"/></xf>'
                        sSh.childNodes[0].childNodes[0].innerHTML += n1;
                        sSh.childNodes[0].childNodes[5].innerHTML += s1 + s2 + s3 + s4;
 
                        var fourDecPlaces    = lastXfIndex + 1;
                        var greyBoldCentered = lastXfIndex + 2;
                        var twoDecPlacesBold = lastXfIndex + 3;
                        var greyBoldWrapText = lastXfIndex + 4;
                        var sheet = xlsx.xl.worksheets['sheet1.xml'];
                        $('row:eq(0) c', sheet).attr( 's', greyBoldCentered );  //grey background bold and centered, as added above
                        $('row:eq(1) c', sheet).attr( 's', greyBoldWrapText );  //grey background bold, text wrapped
                        //$('row c[r=A2]', sheet).attr( 's', greyBoldCentered );
                        var source = xlsx.xl['workbook.xml'].getElementsByTagName('sheet')[0];
                        source.setAttribute('name','Employee Active Now Report');
                        var col = $('col', sheet);
                        col.each(function () {
                        $(this).attr('width', 50);
                         });
            }
                }
            ]
        });
        $('.buttons-print').ready(function () {
            $('.buttons-print').html('<li class="fa fa-print"></li>').addClass('btn-primary').addClass('btn');
            ;
        });
        $('.buttons-pdf').html('<li class="fa fa-file-pdf-o"></li>').addClass('btn-danger').addClass('btn');
        ;
        $('.buttons-excel').html('<li class="fa fa-file-excel-o"></li>').addClass('btn-success').addClass('btn');
       
        $('#todays_presentall_details').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": true,
            dom: 'Bfrtip',
            buttons: [
                {
                     extend: 'print',
                     messageTop: 'My Payroll Master Employees Present Today All Report.',
                     messageBottom: null,
                     title: 'My Payroll Master - Present Today All Report.'
                },
                {
                     extend: 'pdf',
                     messageTop: 'My Payroll Master Employees Present Today All Report.',
                     messageBottom: null,
                     title: 'My Payroll Master - Present Today All Report.'
                },
                {
                     extend: 'excel',
                     messageTop: 'My Payroll Master Employees Present Today All Report.',
                     messageBottom: null,
                     title: 'My Payroll Master - Present Today All Report.'
                }
            ]
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
