<style>
    .table , td, th,tr {
        border-style: solid;
        border-color: #d4d4de;
    }
    .modal-content {
        width: 125%   !important;
    }
</style>
<?php // debug($arr_audit_for_template);?>
<?php if ($mode == '') { ?>
    <div class="modal-body" style="overflow-y: auto;">
        <h2 align="center"><b>Reports Audit</b></h2>
        <div class="row">
            <div class="col-md-12">
                <?php
                if (count($arr_audit_for_template) == 0) {
                    echo "<h3>No Data Available With The Selected Criteria</h3>";
                } else {
                    ?>
                    <fieldset>
                        <?php
//                        debug($arr_audit_for_template);
                        ?>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 100px;">User Name</th>
                                    <th>Report Type</th>
                                    <th style="width: 150px;">Report Component</th>
                                    <th>Mode</th>
                                    <th style="width: 200px;">Criteria</th>
                                    <th>Report From</th>
                                    <th>Report To</th>
                                    <th>Include Resigned</th>
                                    <th>Include Negative Salary</th>
                                    <th>Accessed Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 1;
                                foreach ($arr_audit_for_template as $val) {
                                    ?>
                                    <tr> 
                                        <td><?php echo $val['user_name']; ?></td>
                                        <td><?php echo $val['report_type']; ?></td>
                                        <td><?php echo isset($val['report_component']) ? $val['report_component'] : ''; ?></td>
                                        <td><?php echo $val['mode']; ?></td>
                                        <td><?php echo $val['criteria_name']; ?></td>
                                        <td><?php echo $val['report_from']; ?></td>
                                        <td><?php echo (isset($val['report_to']) && $val['report_to']!='') ? $val['report_to'] : $val['report_from']; ?></td>
                                        <td><?php echo ($val['include_resigned'] == '1') ? 'Y' : 'N'; ?></td>
                                        <td><?php echo ($val['Include_negative_salary'] == '1') ? 'Y' : 'N'; ?></td>
                                        <td><?php echo $val['creation_date']; ?></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>

                    </fieldset>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
    <?php
} else {
    ?>
    <?php //echo '<style>'.file_get_contents("css/pdfbootstrap.css").'</style>';      ?>
    <style type="text/css">
        body {
            line-height: 2em;
        }
        .block-container {
            width: 95%;
            padding: 20px;
            border: #000000 solid thin;
        }
        .sub-head {
            border-bottom: #000000 solid thin;
        }
        .row {
            height: 32px;
        }
        .col-md-4 {
            width: 33.33%;
            float: left;
        }
        table {
            border: 1px solid #f4f4f4;
            width: 80%;
            max-width: 80%;
            margin-bottom: 20px;
            background-color: transparent;
            border-spacing: 0;
            border-collapse: collapse;
        }
        td, th {
            text-align: left;
            padding: 8px;
            line-height: 1.42857143;
            vertical-align: top;
            border: 1px solid #B2B2B2;
        }
    </style>

    <?php
    echo $this->element('reportadminheader', array(
        'title' => ''));
    ?>
    <h2 align="center"><b>Reports Audit</b></h2>
    <?php
    if (count($arr_audit_for_template) == 0) {
        echo "<h3>No Data Available With The Selected Criteria</h3>";
    } else {
        ?>
        <?php
//                        debug($arr_audit_for_template);
        ?>
        <table class="table table-bordered">
            <tr>
                <th style="width: 100px;">User Name</th>
                <th style="width: 100px;">Report Type</th>
                <th style="width: 150px;">Report Component</th>
                <th style="width: 100px;">Mode</th>
                <th style="width: 200px;">Criteria</th>
                <th style="width: 100px;">Report From</th>
                <th style="width: 100px;">Report To</th>
                <th style="width: 100px;">Include Resigned</th>
                <th style="width: 100px;">Include Negative Salary</th>
                <th style="width: 100px;">Accessed Date</th>
            </tr>
            <?php
            $i = 1;
            foreach ($arr_audit_for_template as $val) {
                ?>
                <tr> 
                    <td><?php echo $val['user_name']; ?></td>
                    <td><?php echo $val['report_type']; ?></td>
                    <td><?php echo isset($val['report_component']) ? $val['report_component'] : ''; ?></td>
                    <td><?php echo $val['mode']; ?></td>
                    <td><?php echo $val['criteria_name']; ?></td>
                    <td><?php echo $val['report_from']; ?></td>
                    <td><?php echo isset($val['report_to']) ? $val['report_to'] : ''; ?></td>
                    <td><?php echo ($val['include_resigned'] == '1') ? 'Y' : 'N'; ?></td>
                    <td><?php echo ($val['Include_negative_salary'] == '1') ? 'Y' : 'N'; ?></td>
                    <td><?php echo $val['creation_date']; ?></td>
                </tr>
                <?php
            }
            ?>
        </table>
        <?php
    }
    ?>
    <?php
}?>