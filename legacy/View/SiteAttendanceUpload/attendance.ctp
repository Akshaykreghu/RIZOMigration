  <div class="form-group">
            <div>
                <div class="col-md-12" style="text-align: center;">
                    <button type="button" class="btn btn-primary" onclick="setToAll();">Update In Bulk</button>
                </div>
            </div>
        </div>

        <div class="form-group" id ="dates" >
            <?php
            $i = 0; 
            $input_date = ($month) ? $month : date('F Y');
            $date = date('F Y', strtotime($input_date));
            while (strtotime($date) <= strtotime($month . '-' . date('t', strtotime($month)))) {
                $day_num = date('Y-m-d', strtotime($date)); //Day number
                $day_name = date('D', strtotime($date)); //Day name // l
                $day = "$day_num $day_name";
                $date = date("Y-m-d", strtotime("+1 day", strtotime($date))); //Adds 1 day onto current date
                $i++;
            ?>
                <div class='col-md-6'>
                    <label style="text-align:left;" class="col-md-6 control-label" for="reg-date-<?php echo $i; ?>"><?php echo $day; ?></label>
                    <div class="col-md-6">
                        <select id="reg-date-<?php echo $i; ?>" name="reg-date-<?php echo $i; ?>" class="form-control each_shift_select">
                        </select>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>
        <input type="hidden" id="hid-count-missing" name="hid-count-missing" value="<?php echo $i; ?>" />
</div>