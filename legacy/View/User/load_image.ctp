<?php if($user['avatar'] != 'img/picture.jpg'){
?>
    <img id="imgsloader" class="profile-user-img img-responsive" src="<?php echo $this->webroot . $user['avatar']; ?>" alt="User profile picture">
<?php
}else{
    $arr_name = split(" ", $arr_data['0']['INFOs']['EmpName']);
    $chr = '';
    foreach ($arr_name as $val){
        $chr.= isset($val['0'])?$val['0']." ":' ';
        
    }
    function random_color_part() {
        return str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT);
    }

    function random_color() {
        return random_color_part() . random_color_part() . random_color_part();
    }

    $color =  '#'.random_color();
//    debug($chr);
?>
    <h1 style="position: absolute; color: white; margin: 27%;font-weight: 600; font-size: 89px; "><?php echo $chr; ?></h1><img class="profile-user-img img-responsive" style="height: 301px; background-color: <?php echo $color; ?>; " src="" alt="No Image Uploaded this ">
    
<?php
}
?>
    <a style="position: absolute;    top: 17px;    left: 20px;     font-size: 22px; cursor: pointer;  " onclick="openchangeimagemodal();" ><li class="fa fa-camera"></li></a>


<h3 class="profile-username text-center"><?php echo isset($arr_data['0']['INFOs']['EmpName']) ? $arr_data['0']['INFOs']['EmpName'] : ''; ?></h3><?php if($arr_data['0']['EmployeeDetails']['classification'] == 'male'){ ?><li class="pull-right fa fa-male"></li> <?php }else{ ?><li class="pull-right fa fa-female"></li><?php } ?> 
<h4 class="profile-username text-center"><?php echo isset($arr_data['0']['EmployeeProfessionalDetails']['emp_company_id']) ? $arr_data['0']['EmployeeProfessionalDetails']['emp_company_id'] : ''; ?></h4>

<p class="text-muted text-center"><?php echo isset($arr_data['0']['INFOs']['designation']) ? $arr_data['0']['INFOs']['designation'] : ''; ?></p>

<ul class="list-group list-group-unbordered">
    <li class="list-group-item">
        <b>Department</b> <a class="pull-right"><?php echo isset($arr_data['0']['INFOs']['department']) ? $arr_data['0']['INFOs']['department'] : ''; ?></a>
    </li>
    <li class="list-group-item">
        <b>Joining Date</b> <a class="pull-right"><?php echo isset($arr_data['0']['INFOs']['joining_date']) ? $arr_data['0']['INFOs']['joining_date'] : ''; ?></a>
    </li>
    <li class="list-group-item">
        <b>Branch</b> <a class="pull-right"><?php echo isset($arr_data['0']['INFOs']['branch']) ? $arr_data['0']['INFOs']['branch'] : ''; ?></a>
    </li>
</ul>
