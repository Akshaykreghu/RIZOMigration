<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css">  

<!-- Buttons Extension -->
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>

<!-- Dependencies for PDF Export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

<div class="modal-body" style="overflow-y: auto;">
    <legend>Employees absent this month</legend>
        <div class="row">
            <div class="col-md-12">
                <div class="box ">
                    <div class="box-body">
                        <fieldset>
			                <table class="table table-bordered todayattandence" id="todays_absent_details">
                                <thead>
                                    <tr>
                                        <th style="width:10px;">Sl.No.</th>
                                        <th>Employee Name</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i=0; foreach ($absentData as $value) {
                                        $i += 1; 
                                    ?>
                                    <tr> 
                                        <td><?php echo $i;?></td>
                                        <td><?php echo $value['0']['full_name']; ?></td>
                                    </tr>
                                    <?php  }
                                     ?>
                                </tbody>
                            </table>
		                </fieldset>
                    </div>
                </div>
            </div>
        </div>    
    </div>

<script> 

    $(document).ready(function () {
        $('#modalForm').on('shown.bs.modal', function () {
            if ($.fn.DataTable.isDataTable('#todays_absent_details')) {
                $('#todays_absent_details').DataTable().destroy();
            }
    
            $('#todays_absent_details').DataTable({
                "paging": true,
                "lengthChange": false,
                "searching": true,
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'print',
                        messageTop: 'My Payroll Master Employees Present Today Report.',
                        messageBottom: null,
                        title: 'My Payroll Master - Employees Present Today Report.'
                    },
                    {
                        extend: 'pdf',
                        messageTop: 'My Payroll Master Employees Present Today Report.',
                        messageBottom: null,
                        title: 'My Payroll Master - Employees Present Today Report.'
                    },
                    {
                        extend: 'excel',
                        messageBottom: null,
                        title: 'My Payroll Master Employees Present Today Report.',
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
                            var source = xlsx.xl['workbook.xml'].getElementsByTagName('sheet')[0];
                            source.setAttribute('name','Employee Present Today Report');
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
            });

            $('.buttons-pdf').html('<li class="fa fa-file-pdf-o"></li>').addClass('btn-danger').addClass('btn');
        
            $('.buttons-excel').html('<li class="fa fa-file-excel-o"></li>').addClass('btn-success').addClass('btn');
        });
    });
</script>
