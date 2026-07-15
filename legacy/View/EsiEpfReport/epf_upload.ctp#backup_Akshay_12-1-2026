<?php
$file_name = 'epfupload.txt';
$handle = fopen($file_name, 'w') or die('Cannot open file: ' . $file_name);

foreach ($arr_salary_for_template as $val) {
    if (!empty($val['gross']) && $val['gross'] > 0 && !empty($val['uan'])) {
        $line = implode('#~#', array(
            $val['uan'],                           // UAN
            trim($val['name']),                    // Employee Name
            round($val['gross']),                  // Gross Wages
            round($val['epf']),                    // EPF Wages
            ($val['eps'] > 15000) ? 15000 : round($val['eps']), // EPS Wages
            ($val['edli'] > 15000) ? 15000 : round($val['edli']), // EDLI Wages
            round($val['epf_contr']),              // EPF Contribution
            round($val['eps_contr']),              // EPS Contribution
            round($val['epf_contr'] - $val['eps_contr']), // EPF-EPS Diff
            $val['ncp'],                           // NCP Days
            '0'                                    // Refund
        ));

        fwrite($handle, $line . "\r\n");
    }
}

fclose($handle);
?>
<div class="col-md-12">
    <div class="col-md-7">
        <a href="<?php echo $this->webroot; ?>epfupload.txt" download class="btn btn-primary" onclick="hidebut()"><i class="icon-file"></i>Click to Download File </a>
    </div>
</div>