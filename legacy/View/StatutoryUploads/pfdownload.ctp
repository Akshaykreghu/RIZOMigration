<div class="col-md-12">
    <div class="col-md-7">
        
        <?php
                                $i = 1;
                                $array_data = array();
                                foreach ($arr_salary_for_template as $val) {
                                    //$dir = getcwd();
                                    $empPF = abs($val['epf']);
                                    $finaPF = round(($empPF * 100) / 12);
                                    $finaPF1 = round(($empPF * 8.33) / 100);
                                    $LOP = round($val['lop']);
                                    $wwf = abs($val['wwf']);
                                    $gross = abs($val['gros']);
                                    $empESI = abs($val['esi']);
                                    $finaESI = round(($empESI * 100) / 1.75);
                                    if($val['UAN']!= ''){
                                    $array_data[] = array(
                                        'UAN' => $val['UAN'],
                                        'EMPNAME' => isset($val['data']['0']['employee_info']['EmpName'])?$val['data']['0']['employee_info']['EmpName']:'',
                                        'GROSS' => abs($val['gros']),
                                        'EPF' => $finaPF,
                                        'EPS' => $finaPF,
                                        'EDLIWHAGES' => $finaPF,
                                        'EPFCONTR' => $empPF,
                                        'EPSCONTR' => $finaPF1,
                                        'EPFEPSDIFF' => $empPF - $finaPF1,
                                        'NCP' => $LOP,
                                        'REFUND' => '0'
                                    );
                                    }
                                    
                                } 
                                
                                    $file_name = 'epsupload.txt';
                                    $handle = fopen($file_name, 'w') or die('Cannot open file:  '.$file_name);
                                    $data = 'This is the data';
                                    //fwrite($handle, $data);
                                    //file_put_contents($file_name, $val);
                                    foreach($array_data as $data){
                                        file_put_contents($file_name, $data['UAN'].'#~#'.$data['EMPNAME'].'#~#'.$data['GROSS'].'#~#'.$data['EPF'].'#~#'.$data['EPS'].'#~#'.$data['EDLIWHAGES'].'#~#'.$data['EPFCONTR'].'#~#'.$data['EPSCONTR'].'#~#'.$data['EPFEPSDIFF'].'#~#'.$data['NCP'].'#~#'.$data['REFUND']."\r\n", FILE_APPEND);
                                    }
                                    
                                ?>
    </div>
</div>
<div class="col-md-12">
    <div class="col-md-7">
        <a href="<?php echo $this->webroot; ?>epsupload.txt" download class="btn btn-primary"><i class="icon-file"></i>Click to Download File </a>
    </div>
</div>
