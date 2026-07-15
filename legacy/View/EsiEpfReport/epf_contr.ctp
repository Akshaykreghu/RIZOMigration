<!-- <div class="col-md-12">
    <div class="col-md-7"> -->

<?php
$i = 1;
$array_data = array();
//Edited by Akshay on 18-5-2024
function calculateAge($dateOfBirth)
{
    $dob = new DateTime($dateOfBirth);
    $today = new DateTime('today');
    $age = $dob->diff($today)->y;
    return $age;
}
//End

foreach ($arr_salary_for_template as $val) {
    //$dir = getcwd();
    $finaPF = $empPF = abs($val['epf']);
    //            $finaPF = round(($empPF * 100) / 12);


    // $epfContr = round(($finaPF * 12) / 100); // 3.67% EPF // THIS IS actually 12%
    //$finaPF1 = round(($finaPF * 8.33) / 100); // 8.33% EPS
    $LOP = round($val['lop']);
    $wwf = abs($val['wwf']);
    $gross = abs($val['gros']);
    $empESI = abs($val['esi']);
    $finaESI = round(($empESI * 100) / 1.75);
    if ($finaPF > 15000) {
        $edli = 15000;
    } else {
        $edli =  $finaPF;
    }
    $epf_deduction_salary = abs($val['epf_deduction_salary']);
    if ($val['eps'] == 'Y') {
        if ($finaPF > 15000) {
            $eps = 15000;
        } else {
            $eps = $finaPF;
        }
        $finaPF1 = round(($eps * 8.33) / 100); // 8.33% EPS
    } else {
        $eps = 0;
        $finaPF1 = 0;
    }
    //Edited by Akshay on 18-5-2024
    $dateOfBirth = $val['data'][0]['ed']['date_of_birth'];
    $age = calculateAge($dateOfBirth);
    // if (round($epf_deduction_salary) > 15000) {
    //Edited by Akshay on 23-10-2024
    $emp_epf_contr = $val['emp_epf_contr'];
    $subcat = $val['subcat'];
    $actual_epf = $val['actual_epf'];
    if ($emp_epf_contr <= 1800) {
        //End											 
        // $epf = 15000;
        if ($subcat == 'actual'){
            $epf = ($actual_epf > 15000) ? 15000 : round($actual_epf);
        }else{
            $epf = ($epf_deduction_salary > 15000) ? 15000 : round($epf_deduction_salary);
        }

        // debug($val['data']['0']['employee_info']['EmpName']);debug($epf);
    } else {
        //Edited by Akshay on 24-10-2024
        if ($subcat == 'actual') {
            $actual_epf = $val['actual_epf'];
            $epf = round($actual_epf);
        } else {
            $epf = round($epf_deduction_salary);
        }
        //End
    }
    $epf_contr = round($epf * .12);
    if ($age <= 58) {
        if ($val['eps'] == 'Y') {
            $eps = $epf;
        } else {
            $eps = 0;
        }
    } else {
        $eps = 0;
    }


    //Edited by Akshay on 23-10-2024
    $eps_contr = 0;
    if ($epf > 15000) {
        if ($val['eps'] == 'Y') {
            $eps_contr = round(15000 * .0833);
        }
    } else {
        $eps_contr = round($epf * .0833);
    }
    if ($val['eps'] != 'Y') {
        $eps_contr = 0;
    }
    //End
    // $eps_contr = round($eps * .0833);
    $eps_epf_diff_rem = round(($epf * .12) - $eps_contr);

    if ($val['UAN'] != '') {
        $array_data[] = array(
            'UAN' => $val['UAN'],
            'EMPNAME' => isset($val['data']['0']['employee_info']['EmpName']) ? trim($val['data']['0']['employee_info']['EmpName']) : '', //Edited by Akshay on 25-10-2024
            'GROSS' => round($epf_deduction_salary),
            // 'GROSS' => round($gross), //Edited by Akshay on 23-10-2024
            // 'GROSS' => $epf, //Edited by Akshay on 23-10-2024
            'EPF' => $epf,
            'EPS' => ($eps > 15000) ? 15000 : $eps, //Edited by Akshay on 23-10-2024
            'EDLIWHAGES' => ($epf > 15000) ? 15000 : $epf, //Edited by Akshay on 23-10-2024
            'EPFCONTR' => $epf_contr,
            'EPSCONTR' => $eps_contr,
            'EPFEPSDIFF' => $eps_epf_diff_rem,
            'NCP' => $LOP,
            'REFUND' => '0'
        );
    }
    //End
}

$file_name = 'epfupload.txt';
$handle = fopen($file_name, 'w') or die('Cannot open file:  ' . $file_name);
$data = 'This is the data';
// fwrite($handle, $data);
// file_put_contents($file_name, $data);
foreach ($array_data as $data) {
    if ($data['GROSS'] > 0) {
        file_put_contents($file_name, $data['UAN'] . '#~#' . $data['EMPNAME'] . '#~#' . $data['GROSS'] . '#~#' . $data['EPF'] . '#~#' . $data['EPS'] . '#~#' . $data['EDLIWHAGES'] . '#~#' . $data['EPFCONTR'] . '#~#' . $data['EPSCONTR'] . '#~#' . $data['EPFEPSDIFF'] . '#~#' . $data['NCP'] . '#~#' . $data['REFUND'] . "\r\n", FILE_APPEND);
    }
}

?>
<!-- </div>
</div> -->
<div class="col-md-12">
    <div class="col-md-7">
        <a href="<?php echo $this->webroot; ?>epfupload.txt" download class="btn btn-primary" onclick="hidebut()"><i class="icon-file"></i>Click to Download File </a>
    </div>
</div>