<h4>Holidays Under Selected Policy </h4>
<?php foreach($arr_holidays as $val){ ?>

<button type="button" class="btn bg-maroon btn-flat margin" onclick="move_departments(<?php echo $val['HOLIDAYID']; ?>);"><?php echo $val['HOLIDAYNAME']; ?> <li class="fa fa-plus"></li></button>
                    
<?php } ?>
