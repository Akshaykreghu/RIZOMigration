
    <div class="col-md-12">
        <div class="box box-body">
            <?php if(!empty($site_data['0']['working_day_time_procedures']['on_dutty1'])){
            echo '<h4>Current Shift Timings is : '.$site_data['0']['working_day_time_procedures']['on_dutty1'].' - '.$site_data['0']['working_day_time_procedures']['off_dutty1'].'</h4>'; }?>
            <div class="col-md-12" style="text-align: center; ">
                <?php if(empty($site_data)){ ?>
                    <h4>No Designation Found </h4>
                <?php }else{ ?>
            <?php  foreach($site_data as $val){ ?>
            <div class="col-md-3" id="<?php echo $val['designation']['id'];?>" >
                <button class="btn bg-maroon btn-flat margin" onclick="set_desig(<?php echo $val['designation']['id']; ?>);" type="button" value="<?php echo $val['designation']['id']; ?>"><?php echo $val['designation']['desig_name']; ?></button>
            </div>
            
                <?php } }?>
        </div>
        </div>
    </div>

    <div class="col-md-12">
        <div id="container_data">

        </div>
    </div>
<!--    <div class="col-md-12">
        <div id="close_shift">
            <?php //if(!empty($siteatt_data)){ ?>
                <button class="btn bg-red btn-flat margin" onclick="close_shift_();" type="button" value="" id="button_close"> Close Shift</button>
         <?php //} ?>
        </div>
    </div>-->


