<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <fieldset>
            <div class="table-responsive">
                <table class="table no-margin" id="location" >
                    <thead>
                        <tr>
                            <th>Sl No</th>
                            <th>Date & Time</th>
                            <th>Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 0; ?>
                        <?php foreach($arr_location as $val){
                        $i += 1; ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td><?php echo $val['mob_user_tracking']['created_time']; ?></td>
                                <td><?php echo $val['mob_user_tracking']['location']; ?></td>
                             </tr>
                            <?php $i++;} ?>
                    </tbody>
                </table>
            </div>
        </fieldset>              
    </div>
</div>
        
<script> 
 $(document).ready(function () {
  $('#location').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": false,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "pageLength": 2,
            dom: 'Bfrtip',
            buttons: [
                {
                     extend: 'print',
                     messageTop: '',
                     messageBottom: null,
                     title: 'Location Tracking Report'
                },
                {
                     extend: 'pdf',
                     messageTop: '',
                     messageBottom: null,
                     title: 'Location Tracking Report'
                },
                {
                     extend: 'excel',
                     messageTop: '',
                     messageBottom: null,
                     title: 'Location Tracking Report'
                }
            ],
            
//            "ajax": livesite + "Dashboard/locationtracking",
            
            
        });
        $('.buttons-print').ready(function () {
        $('.buttons-print').html('<li class="fa fa-print"></li>').addClass('btn-primary btn-xs').addClass('btn');
        ;
        });
        $('.buttons-pdf').html('<li class="fa fa-file-pdf-o"></li>').addClass('btn-danger btn-xs').addClass('btn');
        ;
        $('.buttons-excel').html('<li class="fa fa-file-excel-o"></li>').addClass('btn-success btn-xs').addClass('btn');
   });     
</script>

