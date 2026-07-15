<!-- <div class="col-md-12">
    <div class="col-md-7"> -->

<?php
        $i = 1;
        $array_data = array();
        foreach ($arr_salary_for_template as $val) {
            
            $termination_reason='';
            if(($val['termination'])>0){
                $termination_reason = (isset($val['termination'][0]))?strtoupper($val['termination'][0]['termination']['Reason']): ((isset($val['termination']['Reason']))?strtoupper($val['termination']['Reason']):'');
            }

            if($termination_reason == 'CESSATION (SHORT SERVICE) - ANY OTHER'){
                $reason_code = 'C';
            }elseif($termination_reason == 'CESSATION (SHORT SERVICE) - OTHER CAUSE'){
                $reason_code = 'Y';
            }elseif($termination_reason == 'CESSATION (SHORT SERVICE) - THE CONTRACTION'){
                $reason_code = 'B';
            }elseif($termination_reason == 'CESSATION (SHORT SERVICE) - THE EMPLOYEE ILL'){
                $reason_code = 'H';
            }elseif($termination_reason == 'DEATH'){
                $reason_code = 'A';
            }elseif($termination_reason == 'DEATH IN SERVICE'){
                $reason_code = 'D';
            }elseif($termination_reason == 'PERMANENT DISABILITIES'){
                $reason_code = 'P';
            }elseif($termination_reason == 'RETIREMENT'){
                $reason_code = 'R';
            }elseif($termination_reason == 'SUPERNNUATION'){
                $reason_code = 'S';
            }else{
                $reason_code = '';
            }

            if($val['UAN']!= ''){
                $array_data[] = array(
                    'UAN' => $val['UAN'],
                    'EXITDATE' => ($val['date_resignatio'])? date('d/m/Y',strtotime($val['date_resignatio'])) : '',
                    'EXITRESNCODE' => $reason_code
                );
            }
            
        } 

        $file_name = 'epfupload.txt';
        $handle = fopen($file_name, 'w') or die('Cannot open file:  '.$file_name);
        $data = 'This is the data';
        // fwrite($handle, $data);
        // file_put_contents($file_name, $data);
        foreach($array_data as $data){
            file_put_contents($file_name, $data['UAN'].'#~#'.$data['EXITDATE'].'#~#'.$data['EXITRESNCODE']."\r\n", FILE_APPEND);
        }
        
        ?>
<!-- </div>
</div> -->
<div class="col-md-12">
    <div class="col-md-7">
        <a href="<?php echo $this->webroot; ?>epfupload.txt" download class="btn btn-primary" onclick="hidebut()"><i
                class="icon-file"></i>Click to Download File </a>
    </div>
</div>