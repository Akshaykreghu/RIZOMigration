<?php if( $mode == '' ){ ?>
<div class="modal-body" style="overflow-y: auto;">
    <legend>Employee Information Report</legend>
    <table class="table table-bordered">
    <thead>
      <tr>
      <?php foreach($arr_emp_field_headings as $key => $value){
          if(in_array($key,$arr_report_field_headings)){
              echo '<th>'.$value.'</th>';
          }
        } ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach($arr_employee_report_details as $value){ ?>
        <tr>
        <?php foreach($arr_emp_field_names as $key=>$val){ ?>
            <?php foreach($val as $val1){
                if(isset($value[$key][$val1])){
                    echo '<td>'.$value[$key][$val1].'</td>';
                }
            } ?>
        <?php } ?>
        </tr>
      <?php } ?>
    </tbody>
    </table>
    <div class="form-group">
        <div class="col-md-12" align="right">
            <a class="btn btn-default" href="#" onclick="downloadReport('employee','pdf');" ><i class="icon-file"></i>Download As PDF</a>
            <a class="btn btn-default" href="#" onclick="downloadReport('employee','excel');"><i class="icon-file"></i>Download As Excel</a>
        </div>
    </div>
</div>
<?php }else{ ?>
<style>
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
        width: auto;
        text-align: left;
        padding: 8px;
        line-height: 1.42857143;
        vertical-align: top;
        border: 1px solid #B2B2B2;
    }
</style>
<div>
    <h3>Employee Information Report</h3>
    <table>
    <thead>
      <tr>
      <?php foreach($arr_emp_field_headings as $key => $value){
          if(in_array($key,$arr_report_field_headings)){
              echo '<th>'.$value.'</th>';
          }
        } ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach($arr_employee_report_details as $value){ ?>
        <tr>
        <?php foreach($arr_emp_field_names as $key=>$val){ ?>
            <?php foreach($val as $val1){
                if(isset($value[$key][$val1])){
                    echo '<td>'.$value[$key][$val1].'</td>';
                }
            } ?>
        <?php } ?>
        </tr>
      <?php } ?>
    </tbody>
    </table>
</div>
<?php } ?>