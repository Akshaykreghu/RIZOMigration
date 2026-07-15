<style>
    .lud-ohdo {
        clear: both;
        display: flex;
        margin-bottom: 8px;
        max-width: 500px;
        width: 100%;
    }
    .lud-ohday {
        background-color: #f5f5f5;
        display: inline-block;
        flex-grow: 1;
        flex-grow: 1;
        height: 45px;
        margin: 1px 6px 1px 0px;
        min-width: 38px;
        position: relative;
    }
    label {
        cursor: default;
    }
    .lud-ohdt {
        display: none;
    }
    .g, body, html, input, .std, h1 {
        font-size: small;
        font-family: arial,sans-serif;
    }
/*    input[type="radio" i], input[type="checkbox" i] {
        background-color: initial;
        margin: 3px 0.5ex;
        padding: initial;
        border: initial;
    }*/
    input, textarea, keygen, select, button {
        text-rendering: auto;
        color: initial;
        letter-spacing: normal;
        word-spacing: normal;
        text-transform: none;
        text-indent: 0px;
        text-shadow: none;
        display: inline-block;
        text-align: start;
        margin: 0em 0em 0em 0em;
        font: 13.3333px Arial;
    }
    .fAwjXaCTMo5__content {
        border-radius: 2px;
        border-radius: 2px;
        position: relative;
        display: inline-block;
        z-index: 1060;
        background-color: #fff;
        opacity: 0;
        text-align: left;
        vertical-align: middle;
        white-space: normal;
        overflow: hidden;
        transform: translateZ(0);
        -webkit-box-shadow: 0px 5px 26px 0px rgba(0,0,0,0.22),0px 20px 28px 0px rgba(0,0,0,0.30);
        box-shadow: 0px 5px 26px 0px rgba(0,0,0,0.22),0px 20px 28px 0px rgba(0,0,0,0.30);
    }
    .lud-ohdt:checked~.lud-ohdf {
        color: #fff;
        background-color: #4285f4;
    }
    .lud-ohdf {
        color: black;
        font-size: 14px;
        font-weight: 500;
        line-height: 45px;
        pointer-events: none;
        position: absolute;
        text-align: center;
        text-transform: uppercase;
        -webkit-user-select: none;
        user-select: none;
        width: 100%;
    }
    label {
        cursor: default;
    }


    .lud-ohdo2 {
        clear: both;
        display: flex;
        margin-bottom: 8px;
        max-width: 500px;
        width: 100%;
    }
    .lud-ohday2 {
        background-color: #f5f5f5;
        display: inline-block;
        flex-grow: 1;
        flex-grow: 1;
        height: 25px;
        margin: 1px 6px 1px 0px;
        min-width: 38px;
        position: relative;
    }
    label {
        cursor: default;
    }
    .lud-ohdt2 {
        display: none;
    }
    .g, body, html, input, .std, h1 {
        font-size: small;
        font-family: arial,sans-serif;
    }
    input[type="radio" i], input[type="checkbox" i] {
        background-color: initial;
        margin: 3px 0.5ex;
        padding: initial;
        border: initial;
    }
    input, textarea, keygen, select, button {
        text-rendering: auto;
        color: initial;
        letter-spacing: normal;
        word-spacing: normal;
        text-transform: none;
        text-indent: 0px;
        text-shadow: none;
        display: inline-block;
        text-align: start;
        margin: 0em 0em 0em 0em;
        font: 13.3333px Arial;
    }
    .fAwjXaCTMo5__content {
        border-radius: 2px;
        border-radius: 2px;
        position: relative;
        display: inline-block;
        z-index: 1060;
        background-color: #fff;
        opacity: 0;
        text-align: left;
        vertical-align: middle;
        white-space: normal;
        overflow: hidden;
        transform: translateZ(0);
        -webkit-box-shadow: 0px 5px 26px 0px rgba(0,0,0,0.22),0px 20px 28px 0px rgba(0,0,0,0.30);
        box-shadow: 0px 5px 26px 0px rgba(0,0,0,0.22),0px 20px 28px 0px rgba(0,0,0,0.30);
    }
    .lud-ohdt2:checked~.lud-ohdf2 {
        color: #fff;
        background-color: red;
    }
    .lud-ohdf2 {
        color: black;
        font-size: 14px;
        font-weight: 500;
        line-height: 25px;
        pointer-events: none;
        position: absolute;
        text-align: center;
        text-transform: uppercase;
        -webkit-user-select: none;
        user-select: none;
        width: 100%;
    }
    .material-switch > input[type="checkbox"] {
        display: none;   
    }

    .material-switch > label {
        cursor: pointer;
        height: 0px;
        position: relative; 
        width: 40px;  
    }

    .material-switch > label::before {
        background: rgb(0, 0, 0);
        box-shadow: inset 0px 0px 10px rgba(0, 0, 0, 0.5);
        border-radius: 8px;
        content: '';
        height: 16px;
        margin-top: -8px;
        position:absolute;
        opacity: 0.3;
        transition: all 0.4s ease-in-out;
        width: 40px;
    }
    .material-switch > label::after {
        background: rgb(255, 255, 255);
        border-radius: 16px;
        box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.3);
        content: '';
        height: 24px;
        left: -4px;
        margin-top: -8px;
        position: absolute;
        top: -4px;
        transition: all 0.3s ease-in-out;
        width: 24px;
    }
    .material-switch > input[type="checkbox"]:checked + label::before {
        background: inherit;
        opacity: 0.5;
    }
    .material-switch > input[type="checkbox"]:checked + label::after {
        background: inherit;
        left: 20px;
    }
    .form-control
    {
        height:24px;
    }
    .margin-top{
        margin-top: 12px;
    }
    .multitimes{
        transition: 1s;
    }
    
</style>

    <input type="hidden" name="day_time_seq" id="day_time_seq" value="<?php echo isset($data['day_time_seq']) ? $data['day_time_seq'] : 0 ?>" />
    <div class="modal-body">
        <div class="row">
            <div class="col-sm-12">
               <div class="col-sm-6">
                <legend style="font-size: 18px;"> <?php echo isset($data['day_time_desc']) ? $data['day_time_desc'] : '' ?></legend>
                </div>
                <div class="col-sm-6">
                 <span style="float: right;    margin-top: 0px; font-size: 18px;">
                      <?php if($data['off_dutty2'] > 0){
                      $off = $data['off_dutty2'];
                      }else{
                      $off = $data['off_dutty1'];
                      }
                     echo isset($data['on_dutty1']) ? $data['on_dutty1'] : ''; ?> - <?php echo isset($off) ? $off : ''; ?>&nbsp;(<?php echo isset($data['minuts_calc_perday']) ? $data['minuts_calc_perday'] : ''; ?><span style="font-size: 17px;">Min</span>)
                    
                    </span>
                </div>
            </div>
        </div>
        
        <?php
                        
        $week_selected = 0;
        $occurances = 0;

        if($data['Sunday_F'] != 'N' && $data['Sunday_F'] != 'Y') {
            //echo '<option value="Sunday">Sunday</option>';
            $week_selected = 'Sunday';
            $occurances = $data['Sunday_F'];
        }
        if($data['Monday_F'] != 'N' && $data['Monday_F'] != 'Y') {
            //echo '<option value="Sunday">Sunday</option>';
            $week_selected = 'Monday';
            $occurances = $data['Monday_F'];
        }
        if($data['Tuesday_F'] != 'N' && $data['Tuesday_F'] != 'Y') {
            //echo '<option value="Sunday">Sunday</option>';
            $week_selected = 'Tuesday';
            $occurances = $data['Tuesday_F'];
        }
        if($data['Wednesday_F'] != 'N' && $data['Wednesday_F'] != 'Y') {
            //echo '<option value="Sunday">Sunday</option>';
            $week_selected = 'Wednesday';
            $occurances = $data['Wednesday_F'];
        }
        if($data['Thursday_F'] != 'N' && $data['Thursday_F'] != 'Y') {
            //echo '<option value="Sunday">Sunday</option>';
            $week_selected = 'Thursday';
            $occurances = $data['Thursday_F'];
        }
        if($data['Friday_F'] != 'N' && $data['Friday_F'] != 'Y') {
            //echo '<option value="Sunday">Sunday</option>';
            $week_selected = 'Friday';
            $occurances = $data['Friday_F'];
        }
        if($data['Saturday_F'] != 'N' && $data['Saturday_F'] != 'Y') {
            //echo '<option value="Sunday">Sunday</option>';
            $week_selected = 'Saturday';
            $occurances = $data['Saturday_F'];
        }


        ?>
        <div class="row " style="margin-top: 0px;">
            <div class="col-md-5 col-sm-12 col-xs-12">
                
                <fieldset data-parsley-multiple="workdays" >
                    <legend>
                        <h4><b>    Working Days</b></h4>
                    </legend>

                    <div class="lud-ohdo">
                        <label class="lud-ohday"><input class="lud-ohdt" disabled="" data-parsley-multiple="workdays" data-parsley-ui-enabled="false" name="Sunday" <?php echo isset($data['Sunday']) && $data['Sunday'] == "Y" ? 'checked="checked"' : '' ?> type="checkbox" value="Y"><span class="lud-ohdf">Sun</span></label>
                        <label class="lud-ohday"><input class="lud-ohdt" disabled="" data-parsley-multiple="workdays" name="Monday" <?php echo isset($data['Monday']) && $data['Monday'] == "Y" ? 'checked="checked"' : '' ?>  type="checkbox" value="Y"><span class="lud-ohdf">Mon</span></label>
                        <label class="lud-ohday"><input class="lud-ohdt" disabled="" data-parsley-multiple="workdays" name="Tuesday" <?php echo isset($data['Tuesday']) && $data['Tuesday'] == "Y" ? 'checked="checked"' : '' ?> type="checkbox" value="Y"><span class="lud-ohdf">Tue</span></label>
                        <label class="lud-ohday"><input class="lud-ohdt" disabled="" data-parsley-multiple="workdays" name="Wednesday" <?php echo isset($data['Wednesday']) && $data['Wednesday'] == "Y" ? 'checked="checked"' : '' ?> type="checkbox" value="Y"><span class="lud-ohdf">Wed</span></label>
                        <label class="lud-ohday"><input class="lud-ohdt" disabled="" data-parsley-multiple="workdays" name="Thursday" <?php echo isset($data['Thursday']) && $data['Thursday'] == "Y" ? 'checked="checked"' : '' ?> type="checkbox" value="Y"><span class="lud-ohdf">Thu</span></label>
                        <label class="lud-ohday"><input class="lud-ohdt" disabled="" data-parsley-multiple="workdays" name="Friday" <?php echo isset($data['Friday']) && $data['Friday'] == "Y" ? 'checked="checked"' : '' ?> type="checkbox" value="Y"><span class="lud-ohdf">Fri</span></label>
                        <label class="lud-ohday"><input class="lud-ohdt" disabled="" data-parsley-multiple="workdays" name="Saturday" <?php echo isset($data['Saturday']) && $data['Saturday'] == "Y" ? 'checked="checked"' : '' ?> type="checkbox" value="Y"><span class="lud-ohdf">Sat</span></label>
                    </div>
                    <div class="lud-ohdo2">
                        <label class="lud-ohday2"><input class="lud-ohdt2" disabled="" data-parsley-multiple="workdays" data-parsley-ui-enabled="false" name="Sunday_F" <?php echo isset($data['Sunday_F']) && $data['Sunday_F'] == "Y" ? 'checked="checked"' : '' ?> type="checkbox" value="Y"><span class="lud-ohdf2">H</span></label>
                        <label class="lud-ohday2"><input class="lud-ohdt2" disabled="" data-parsley-multiple="workdays" name="Monday_F" <?php echo isset($data['Monday_F']) && $data['Monday_F'] == "Y" ? 'checked="checked"' : '' ?>  type="checkbox" value="Y"><span class="lud-ohdf2">H</span></label>
                        <label class="lud-ohday2"><input class="lud-ohdt2" disabled="" data-parsley-multiple="workdays" name="Tuesday_F" <?php echo isset($data['Tuesday_F']) && $data['Tuesday_F'] == "Y" ? 'checked="checked"' : '' ?> type="checkbox" value="Y"><span class="lud-ohdf2">H</span></label>
                        <label class="lud-ohday2"><input class="lud-ohdt2" disabled="" data-parsley-multiple="workdays" name="Wednesday_F" <?php echo isset($data['Wednesday_F']) && $data['Wednesday_F'] == "Y" ? 'checked="checked"' : '' ?> type="checkbox" value="Y"><span class="lud-ohdf2">H</span></label>
                        <label class="lud-ohday2"><input class="lud-ohdt2" disabled="" data-parsley-multiple="workdays" name="Thursday_F" <?php echo isset($data['Thursday_F']) && $data['Thursday_F'] == "Y" ? 'checked="checked"' : '' ?> type="checkbox" value="Y"><span class="lud-ohdf2">H</span></label>
                        <label class="lud-ohday2"><input class="lud-ohdt2" disabled="" data-parsley-multiple="workdays" name="Friday_F" <?php echo isset($data['Friday_F']) && $data['Friday_F'] == "Y" ? 'checked="checked"' : '' ?> type="checkbox" value="Y"><span class="lud-ohdf2">H</span></label>
                        <label class="lud-ohday2"><input class="lud-ohdt2" disabled="" data-parsley-multiple="workdays" name="Saturday_F" <?php echo isset($data['Saturday_F']) && $data['Saturday_F'] == "Y" ? 'checked="checked"' : '' ?> type="checkbox" value="Y"><span class="lud-ohdf2">H</span></label>
                    </div>
                   
                    <div class="pull-left"><span class="label" style="background: red; width: 38px;">&nbsp;Half Day&nbsp;</span>&nbsp;&nbsp;<span class="label" style="background: #4285f4;color: white;">&nbsp;Full Day&nbsp;</span></div>
                    <br><br>
                    
                </fieldset>
            </div>
            <?php $occr_arr = array("1"=>"First Week","2"=>"Second Week","3"=>"Third Week","4"=>"Fourth Week","L"=>"Alternate","A"=>"Every Week"); ?>
          
            
              <div class="col-md-7 col-sm-12 col-xs-12">   
        <fieldset>
            <legend>
                <h4><b>    Shift Times</b></h4>
            </legend>
            <div class="row">
            <div class="col-sm-6">
                   <li class="list-group-item" style="height:30px;">
             <span style="width: 200px"><div style="position:relative;top:-4px;" >Full Day(Min)<span class="star">*</span></span>
             <label>  &ensp;  :&ensp;</label><?php echo isset($data['minuts_calc_perday']) ? $data['minuts_calc_perday'] : '' ?>
             </div>  </li>        
            </div>
              <div class="col-sm-6">
                     <li class="list-group-item" style="height:30px;">
             <span style="width: 200px"><div style="position:relative;top:-4px;" >Half Day(Min)<span class="star">*</span></span>
             <label>   &ensp;:&ensp;</label><?php echo isset($data['minutes_per_half']) ? $data['minutes_per_half'] : '' ?>
             </div>
                     </li></div>
            </div>
            <div class="row">
               <div class="col-sm-6"> 
                   <br>
                      <li class="list-group-item" style="height:30px;">
                   <span> <div style="position:relative;top:-4px;" >Late In - limit<span class="star">*</span><label>&ensp;&ensp;&ensp;:&ensp;</label></span>
                 <?php echo isset($data['minuts_aftr_on_dutty_cal_late']) ? $data['minuts_aftr_on_dutty_cal_late'] : '' ?>
                <br>
                   </div> </li>
               </div>  
                 <div class="col-sm-6"> 
                     <br>
                        <li class="list-group-item" style="height:30px;">
                     <span><div style="position:relative;top:-4px;" >Early Out - limit<span class="star">*</span><label>&ensp;:&ensp;</label></span>
                    <?php echo isset($data['minuts_bfr_off_dutty_cal_early']) ? $data['minuts_bfr_off_dutty_cal_early'] : '' ?>
                 </div> </li></div>
            </div>
             <div class="row">
                   <div class="col-sm-6"> 
                       <br>
                          <li class="list-group-item" style="height:30px;">
                     <div style="position:relative;top:-4px;" >  Monitoring Type<label> &ensp;:&ensp;</label>
                        <?php echo  isset($data['strict_monitorings']) && $data['strict_monitorings'] == "Y" ? ' Strict' : 'Flexible' ?>

                     </div> </li>   
             </div>
                <!-- edited by athira on 08-02-2025 -->
                  <div class="col-sm-6" id="basic-row1"> 
                  <!-- end -->
                      <br>
                      <li class="list-group-item" style="height:30px;">
                        <div style="position:relative;top:-4px;" >  Multi Shift&ensp;<label>:</label>
                    <?php 
                            if($data['is_multiple_days'] == 'Y')
                            {echo "Enabled   ";
                               echo"(";
                            echo $data['no_of_shift_days'];
                             echo")";}
                            else
                            {
                            echo "Disabled";
                            }?>
                        </div>
                     </li>   
                 </div>
              
                 
                  </div>
             </div>
              </div>  
            <!-- edited by athira on 08-02-2025 -->
            <div class="col-sm-6" style="padding-left: 1px;" id="basic-row2"> 
            <!-- end -->
                <br>
                  <li class="list-group-item" style="height:30px; " >
                      
                      <div style="position:relative;top:-4px;" >Overtime - Minimum time after Shift<label>&ensp;: &ensp;</label><?php echo isset($data['min_aftr_off_dutty_cal_ot']) && $data['min_aftr_off_dutty_cal_ot'] != "0" ? 'Enabled' : 'Disabled'; ?>
                      
                   
                        (<?php echo isset($data['min_aftr_off_dutty_cal_ot']) ? $data['min_aftr_off_dutty_cal_ot'] : '' ?>)
                      </div>   </li> </div>
            <!-- edited by athira on 08-02-2025 -->
            <div class="col-sm-6" style="padding-right: 1px;" id="basic-row3">
            <!-- end -->
                <br>
                   <li class="list-group-item" style="height:30px;">
                      
          <div style="position:relative;top:-4px;" >   
              
              
              <?php 
                            if($data['is_multiple_days'] == 'Y')
                            {
                            
                         echo "Balance Consider as Monthly working balance<label>: </label> ";
                         echo isset($data['work_time_day_off_cal_ot']) && $data['work_time_day_off_cal_ot'] != "0" ? 'Enabled' : 'Disabled'; 
                        $working_component_ot = "";
                        switch($data['work_time_day_off_cal_ot']){
                            case '1': $working_component_ot = "OT";
                                break;
                            case '3': $working_component_ot = "Other";
                                break;
                            case '2': $working_component_ot = "Comp Off";
                                break;
                            default : $working_component_ot = "Other";
                                break;
                        }
                        echo"(";
                  echo $working_component_ot; 
                  echo ")";
                            }
                            else
                            {
                             if($plan!='basic'){
                                echo "Working On Off-Day<label> &ensp;: &ensp;</label>"; 
                                echo isset($data['work_time_day_off_cal_ot']) && $data['work_time_day_off_cal_ot'] != "0" ? 'Enabled' : 'Disabled'; 
                                $working_component_ot = "";
                                switch($data['work_time_day_off_cal_ot']){
                                 case '1': $working_component_ot = "OT";
                                     break;
                                
                                 case '2': $working_component_ot = "Comp Off";
                                     break;
                                  default : $working_component_ot = "Other";
                                     break;
                             }
                             echo"(";
                       echo $working_component_ot; 
                       echo ")";
                             }
                           
                            }
                ?>
              
              
               </div>
                   </li>
            </div>
                 
            <!-- edited by athira on 08-02-2025 -->
            <div class="col-md-6 col-sm-12 col-xs-12" style="padding-left: 1px;" id="basic-row4"> 
            <!-- end -->
                <br>
                   <li class="list-group-item" style="height:30px;">
           <div style="position:relative;top:-4px;" >     Overtime - Minimum time before Shift<label>&ensp; :&ensp;</label> <?php echo isset($data['min_bfr_on_dutty_cal_ot']) && $data['min_bfr_on_dutty_cal_ot'] != "0" ? 'Enabled' : 'Disabled'; ?>

                    
                        (<?php echo isset($data['min_bfr_on_dutty_cal_ot']) ? $data['min_bfr_on_dutty_cal_ot'] : '' ?>)
           </div>
                   </li> 
            </div>
                 <!-- edited by athira on 08-02-2025 -->
                  <div class="col-md-6 col-sm-12 col-xs-12" style="padding-right: 1px;" id="basic-row5"> 
                <!-- end -->
                      <br>
                         <li class="list-group-item" style="height:30px; " style="padding-bottom:10px;">
               <span id="dayoffchange"> <div style="position:relative;top:-4px;" >Shift Allowance</span><label>&ensp;:&ensp; </label> 
                    <?php
                        $components_items = "No Components Selected";
                        foreach ($components as $key => $value) {
                             if ($data['shift_allowance'] == $value['salary_head_items']['salary_head_item_pkey']) { $components_items = $value['salary_head_items']['item']; }
                            
                        }
                        
                     if($components_items=="No Components Selected")
                        {echo"Disabled";}
                      else
                        {echo"Enabled";
                        echo"(";
                        echo $components_items;
                        echo")";}?>
                </div> </li>
                </div> 
                 </div>  
           
            <!-- edited by athira on 08-02-2025 -->
            <div class="col-md-6 col-sm-12 col-xs-12" style="padding-left: 13px;" id="basic-row6"> 
            <!-- end -->
                <br>
                   <li class="list-group-item" style="height:30px;" >
          <span id="dayoffchange" ><div style="position:relative;top:-4px;" >Salary Component for OT</span> <label>&ensp;: &ensp;</label>
                    <?php
                        $components_item = "No Components Selected";
                        foreach ($components as $key => $value) {
                             if($data['otcomponents'] == $value['salary_head_items']['salary_head_item_pkey']) { $components_item = $value['salary_head_items']['item'] ; }
                            
                        }
                       if($components_item=="No Components Selected")
                       {  echo"Disabled";
                      }else{
                      echo"Enabled";
                      echo"(";
                         echo $components_item; 
                         echo")";}?>
                   </li>  </div> </div>
               
            <!-- edited by athira on 08-02-2025 -->
            <div class="col-sm-12" style="padding-left: 13px;" id="basic-row7">
            <!-- end -->
            <legend>
                
                <h4><b> Exceptions</b></h4>
            </legend>

            <div id="exceptionList2" style="margin-top:10px;"></div>



                
                    <!-- <?php $occ_text= ''; ?>
                    <?php foreach($occr_arr as $key => $val){
                        if($key == $occurances){
                            $occ_text = $val;
                        }
                    }
                    ?>
                    
               
             
                    <label>    <?php 
                            if(!$week_selected == '0')
                              {?>
                     <div class="row" style="padding-left: 1px;width: 945px;"> 
                         <div class="col-sm-3" style="padding-left: 15px;width: 236px;">
                             <li class="list-group-item" style="height:30px;">
                   <div style="position:relative;top:-4px; " ><span style="font-weight: lighter;"> Exception</span>
        <label>  &ensp;  :&ensp;</label><span style="font-weight: 100;"><?php echo $week_selected;?></span>
                   </div>  </li>  </div>

                 <div class="col-sm-2" style="width: 235px;">
                    <li class="list-group-item" style="height:30px;">
                   <div style="position:relative;top:-4px; " ><span style="font-weight: lighter;">In-Time</span>
         <label>  &ensp;  :&ensp;</label><span style="font-weight: 100;"><?php echo isset($data['on_dutty4']) ? $data['on_dutty4'] : ''; ?></span>
                   </div>  </li>  </div>

                    <div class="col-sm-2"style="width: 219px;">
                           <li class="list-group-item" style="height:30px;" ">
                   <div style="position:relative;top:-4px; " ><span style="font-weight: lighter;">Out-Time</span>
         <label>  &ensp;  :&ensp;</label><span style="font-weight: 100;"><?php echo isset($data['off_dutty4']) ? $data['off_dutty4'] : ''; ?></span>
                   </div>  </li>  </div>
  
                        <div class="col-sm-3" style="width: 254px;">
            <li class="list-group-item" style="height:30px;">
                   <div style="position:relative;top:-4px; " ><span style="font-weight: lighter;">Total Working Time</span>
         <label>  &ensp;  :&ensp;</label><span style="font-weight: 100;"><?php echo round(isset($data['working_time4']) ? $data['working_time4'] : '') ?></span>
                   </div>  </li> </div> 
<div class="col-sm-12" style="padding-top: 15px;width: 100%;">
            <li class="list-group-item" style="height:30px;">
                   <div style="position:relative;top:-4px; " ><span style="font-weight: lighter;">Week Off</span>
         <label>  &ensp;  :&ensp;</label><span style="font-weight: 100;">
            
              <?php 
                 if($occurances == '1')
                 { ?>
             1st &nbsp;<?php echo $week_selected;?> Off. 
                    <?php }
                    else if($occurances == '2')
                    {?>
         2nd&nbsp;<?php echo $week_selected;?> Off. 
                     <?php }
                    else if($occurances == '3')
                    {?>
         3rd&nbsp;<?php echo $week_selected;?> Off.  
                     <?php }
                      else if($occurances == '4')
                    {?>
        4th&nbsp;<?php echo $week_selected;?> Off.  
                     <?php }
                      else if($occurances == 'L')
                    {?>
            Alternate &nbsp;<?php echo $week_selected;?>'s Off. 
                     <?php }
                      else if($occurances == 'A')
                    {?>
        No Week off.  
                     <?php }
              ?>
         </span>
                   </div>  </li> </div> 
           <?php  }
                            else
                            {
                            echo "Disabled";
                            }?></label>
           
                </div> -->

          
        
                
            </div>
       
    
         
            
                 <div class="col-sm-12"> 
                        <div class="pull-right">
<button style="background:#1e516e;color:#fff;border:none;padding:8px 20px;border-radius:5px;cursor:pointer;" onclick="editable(<?php echo isset($data['day_time_seq']) ? $data['day_time_seq'] : 0 ?>);">Edit </button>
            <br>   <br>   </div>

                 </div>

    </div>
    
                        <script type="text/javascript">
                            //edited by athira on 08-02-2025
                            var plan = <?php echo json_encode($plan); ?>;
                            if (plan=='basic'){
                                document.getElementById("basic-row1").style.display = "none";
                                document.getElementById("basic-row2").style.display = "none";
                                document.getElementById("basic-row3").style.display = "none";
                                document.getElementById("basic-row4").style.display = "none";
                                document.getElementById("basic-row5").style.display = "none";
                                document.getElementById("basic-row6").style.display = "none";
                                document.getElementById("basic-row7").style.display = "none";
                            }
                            //end

    $.fn.extend({
    animateCss: function (animationName) {
        var animationEnd = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';
        this.addClass('animated ' + animationName).one(animationEnd, function() {
            $(this).removeClass('animated ' + animationName);
        });
    }
});
    
    function editable(id){
        $("#shiftContainer").load(livesite+"DayTimeProcedure/form?id="+id)
    }

    $(document).ready(function () {
        if ($('#shift').is(":checked")) {
            $('#components').show();
        } else {
            $('#components').hide();
            //$('#shift_allowance').val('');
        }
        if ($('#someSwitchOptionSuccess1').is(":checked")) {
            //alert("checked");
            $('#divmulti').css('display','block').animateCss('fadeInRight');
//            $('.hideOnMultishift').animateCss('fadeOutLeft');
            $('.hideOnMultishift').css('display','none');
            $('#dayoffchange').html('Balance Consider as Monthly working balance');         
        } else {
            //alert("hi");
        }
        if ($('#work_time_day_off_cal_ot').val() == '1')
        {

            $('#ot').show();
        } else
        {
            $('#ot').hide();
        }

        var a = $("#minutes_per_half").val();
        var b = $("#minuts_calc_perday").val();
        if (a > b)
        {
            $.notify("Full day Cannot Smaller Than Half Day", {
                type: 'danger',
                allow_dismiss: false
            });
        }
    });

function loadExceptions(shift_id = null) {
    var day_time_seq = $('#day_time_seq').val();
    console.log(day_time_seq,'hi');
    $.ajax({
        url: '<?= $this->webroot ?>DayTimeProcedure/getExceptions',
        type: 'GET',
        data: { shift_id: day_time_seq },
        dataType: 'json',
        success: function(response) {
            let html = '';
            

            // Week off mapping
            const weekOffLabels = {
                '1': '1st Week',
                '2': '2nd Week',
                '3': '3rd Week',
                '4': '4th Week',
                '5': '5th Week',
                'A': 'All Weeks',
            };

            if (response.length > 0) {
                html += `<table class="table table-bordered" style="margin-top:10px;">
                            <thead>
                                <tr>
                                    <th>Day</th>
                                    <th>In Time</th>
                                    <th>Out Time</th>
                                    <th>Duration (Min)</th>
                                    <th>Week Count</th>
                                </tr>
                            </thead>
                            <tbody>`;

                response.forEach(function(row) {
                    let data = row.ShiftException;
                    html += `<tr data-id="${data.shift_exceptions_pkey}">
                                <td>${data.ex_week_day || ''}</td>
                                <td>${data.in_time || ''}</td>
                                <td>${data.out_time || ''}</td>
                                <td>${data.duration || ''}</td>
                                <td>${weekOffLabels[data.ex_week] || ''}</td>
                             </tr>`;
                });

                html += `</tbody></table>`;
            } else {
                html = '<p>No exceptions found.</p>';
            }

            $('#exceptionList2').html(html);
        },
        error: function() {
            $('#exceptionList2').html('<p style="color:red;">Failed to load exceptions.</p>');
        }
    });
}
$(document).ready(function() {

    var day_time_seq = $('#day_time_seq').val();
    var is_exception=<?php echo json_encode($is_exception); ?>;
    
    if(is_exception==1){
        loadExceptions(day_time_seq);
    }

});
</script>