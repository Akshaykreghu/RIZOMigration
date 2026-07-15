<!-- <?php if($assets['0']['allocate']['asset_state'] == 3){
$state= "Not Working";
}?>
<?php if($assets['0']['allocate']['asset_state'] == 1){
$state= "Good";
}?>
<?php if($assets['0']['allocate']['asset_state'] == 0){
$state= "With Minor Damage";
}?>
<?php if($assets['0']['allocate']['asset_state'] == 2){
$state= "Damage But Working";
}?> -->
<!-- <?php debug($assets); ?> -->
<div class="modal-body">
    <!-- Form Name -->

    <legend>View Asset</legend>

        <div class="modal-body">
            <div class="form-group">
                <div class="col-md-3">
                    <label>Asset Name: </label>
                    <?php echo $arr_users['0']['Assets']['name']; ?>
                </div>
                <div class="col-md-3">
                    <label>Specifications: </label>
                    <?php echo $arr_users['0']['Assets']['specifications']; ?>
                </div>
                <div class="col-md-3">
                    <label>Serial Number: </label>
                    <?php echo $arr_users['0']['Assets']['serial_no']; ?>
                </div>
                <div class="col-md-3">
                    <label>Warranty: </label>
                    <?php echo $arr_users['0']['Assets']['warranty']; ?>
                </div>
                <div class="col-md-3">
                    <label>Model: </label>
                    <?php echo $arr_users['0']['Assets']['model']; ?>
                </div>
                <div class="col-md-3">
                    <label>Brand: </label>
                    <?php echo $arr_users['0']['Assets']['brand']; ?>
                </div>
                <div class="col-md-3">
                    <label>Asset Value: </label>
                    <?php echo $arr_users['0']['Assets']['value']; ?>
                </div>
              <!--   <div class="col-md-3">
                    <label>Asset Condition: </label>
                    <?php echo $state; ?>
                </div> -->
            </div>
            <div class="form-group">
                <input type="hidden" name="emp_fkey" id="asset_fkey" value="<?php echo $edit_pkey; ?>">
                <table class="table">
                    <thead><th colspan="4" style="text-align:center; ">Asset History</th></thead>
                    <thead>
                        <th>Employee Name</th>
                        <th>Allocated Date</th>
                        <th>Asset Status</th>
                        <th>Returned Date</th>
                    </thead>
                    <tbody>
                    <?php foreach($assets as $value){ ?>
                        <tr>
                        <td><?php echo $value['EmployeeDetails']['first_name'].' '.$value['EmployeeDetails']['last_name']; ?></td>
                        <td><?php echo $value['allocate']['allocated_date']; ?></td>
                        <td><?php echo $value['allocate']['status']; ?></td>
                        <td><?php if (empty($value['allocate']['retreived_date']) && $value['allocate']['retreived_date'] =='' ){echo "Not Returned";} else{echo $value['allocate']['retreived_date'];} ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>           
        <div class="modal-footer">
            <button type="button" class="btn btn-danger" onclick="$('#modalDetailForm').modal('hide');">Cancel</button>
         <!--    <?php foreach($assets as $value){ if ($value['allocate']['status'] != 'Returned'){?>
                <button type="button" onclick="release();" id="btn-submit" class="btn btn-primary">Release Asset</button>
            <?php } }?> -->
        </div>

</div>
<script type="text/javascript">
    function release()
    {
        var asset = $('#asset_fkey').val();
        $.ajax({
            url: livesite+'Asset/release/' + asset,
            type:"POST",
            success: function(resp){
                alert("Asset Returned from all others");
              $("#modalDetailForm").modal('hide');
            }
        });
    }
</script>