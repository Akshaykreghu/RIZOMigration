<style>
    div.inflow {}

    div.positioner {
        position: absolute;
        right: 0;
    }

    /*may not be needed: see below*/
    div.fixed {
        overflow: auto;

    }

    /*tr td.fix{
        position:fixed;
    }*/
</style>
<div class="modal-body">
    <legend style="text-align: center; font-weight: bold; ">Employee Check In/Out Logs Report - <?php echo $report_month; ?></legend>
    <div class="row">
        <div class="col-md-12">
            <div class="box ">


                <div class="box-body">
                    <fieldset>


                        <div class="row">
                            <div class="col-md-12">

                            </div>
                        </div>


                    </fieldset>

                    <br>
                    <fieldset>

                        <div class="inflow">
                            <div class="fixed">
                                <div class="table-responsive">
                                    <table class="table no-margin" id="todayattandence">
                                        <thead>
                                            <tr>
                                                <th>Sl No</th>
                                                <th>Employee ID</th>
                                                <th>Employee Name</th>
                                                <th>Branch</th>
                                                <th>Department</th>
                                                <th>Designation</th>
                                                <th>Date</th>
                                                <th>Time</th>
                                                <th>Check-In/Out</th>
                                                <th>Location</th>
                                                <!--Added by megha device id on 20/07/19-->
                                                <th>Punch Type</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $data = $arr_leavepolicydetails_for_template['data'];
                                            $i = 1;
                                            ?>
                                            <?php foreach ($data as $val) {/* debug($val); */ ?>
                                                <tr>
                                                    <td><?php echo $i; ?></td>
                                                    <td><?php echo $val[0]; ?></td>
                                                    <td><?php
                                                        echo $val[1];
                                                        if ($val[10] == 2) {
                                                            echo '(Resigned)';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><?php echo $val[2]; ?></td>
                                                    <td><?php echo $val[3]; ?></td>
                                                    <td><?php echo $val[4]; ?></td>
                                                    <td><?php echo $val[5]; ?></td>
                                                    <td><?php echo $val[6]; ?></td>
                                                    <!--Added by megha device id on 20/07/19-->
                                                    <td><?php echo $val[7]; ?></td>
                                                    <td><?php echo $val[8]; ?></td>
                                                    <td><?php echo $val[9]; ?></td>
                                                </tr>

                                            <?php
                                                $i++;
                                            }
                                            ?>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <br>

                    <!-- /.box-body -->
                </div>
            </div>
        </div>
    </div>
    <?php //echo '<style>'.file_get_contents("css/pdfbootstrap.css").'</style>';     
    ?>
    <script>
        $(document).ready(function() {

            $('#todayattandence').DataTable({
                "paging": true,
                "lengthChange": false,
                "searching": true,
                "ordering": true,
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'print',
                        messageTop: ' Employee Check-in/out logs Report - <?php echo $report_month; ?>',
                        messageBottom: null,
                        title: ' Employee Check-in/out logs Report'
                    },
                    {
                        text: 'Excel',
                        className: 'buttons-excel',
                        action: function(e, dt, node, config) {
                            var jq = window.jQuery || window.$;
                            var params = <?php echo json_encode(array_merge($this->request->query, $this->request->data)); ?>;
                            var form = jq('<form>', {
                                "action": "<?php echo $this->Html->url(array('action' => 'generatereport', 'Dashboard', 'excel')); ?>",
                                "method": "post"
                            });
                            jq.each(params, function(key, value) {
                                if (value !== null && typeof value === 'object') {
                                    jq.each(value, function(i, val) {
                                        jq('<input>').attr({
                                            "type": "hidden",
                                            "name": key + (jq.isArray(value) ? "[]" : "[" + i + "]"),
                                            "value": val
                                        }).appendTo(form);
                                    });
                                } else if (value !== null) {
                                    jq('<input>').attr({
                                        "type": "hidden",
                                        "name": key,
                                        "value": value
                                    }).appendTo(form);
                                }
                            });
                            form.appendTo('body').submit().remove();
                        }
                    }
                ],
                "info": true,
                "autoWidth": false,
                "initComplete": function() {
                    //actions
                },
                //             "createdRow": function ( row, data, index ) {
                //                if ( data[5].replace(/[\$,]/g, '') * 1 > 150000 ) {
                //                    $('td', row).eq(5).addClass('highlight');
                //                }
                //            },
                "scrollCollapse": true,
            });


            $('.buttons-print').ready(function() {
                $('.buttons-print').html('<li class="fa fa-print"></li>').addClass('btn-primary').addClass('btn');;
            });
            $('.buttons-pdf').html('<li class="fa fa-file-pdf-o"></li>').addClass('btn-danger').addClass('btn');;
            $('.buttons-excel').html('<li class="fa fa-file-excel-o"></li>').addClass('btn-success').addClass('btn');
        });
    </script>