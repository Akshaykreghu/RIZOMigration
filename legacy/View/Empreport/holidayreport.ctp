
<?php if( $mode == '' ){ ?>
<section class="content">
    <div class="box box-body">
    <h1 class="page-header" style="text-align:center"><strong>HOLIDAY REPORTS</strong></h1>

<table class="table table-responsive table-hover" style="background-color: #fff ;">

    <thead>
    <th>Holiday</th>
    <th>Holiday Date</th>
    <th>Holiday Type</th>
    <th>Description</th>

</thead>
<tbody>
    <?php foreach ($arr_holiday as $val) { ?>
        <?php echo'<tr>
               <td>' . $val['holidays']['HOLIDAYNAME'] . '</td>
                <td>' . $val['holidays']['HOLIDAYDATE'] . '</td>
                <td>' . $val['holidays']['DESCRIPTION'] . '</td>
                <td>' . $val['holidays']['HOLIDAYTYPE'] . '</td>
               
     </tr>'; ?>
    <?php } ?>
</tbody>


</table>
    </div>
    
       <div class="row">
        <div class="form-group">
            <div class="col-md-12" align="right">
                <a href="<?php echo $this->webroot; ?>Empreport/holidayreport/pdf" class="btn btn-default" ><i class="icon-file"></i>Download As PDF</a>
                <!--<a href="#" class="btn btn-default" onclick="downloadReport('Salaryslip','excel');"><i class="icon-file"></i>Download As Excel</a>-->
            </div>
        </div>
    </div>
</section>

<?php } else {
echo $this->element('reportempheader',array(
        "emp" => $arr_emp
    ));
?>

 <h3 style="text-align: center;padding-bottom: 10px;padding-top: 10px;">HOLIDAY REPORTS </h3>
<table style="width: 100%;border: 1px;">

    <tr style="font-weight: bold;border: 1px">
    <th style="border: 1px;width:20%">Holiday</th>
    <th style="border: 1px;width:20%">Holiday Date</th>
    <th style="border: 1px;width:20%">Holiday Type</th>
    <th style="border: 1px;width:20%">Description</th>

</tr>

    <?php foreach ($arr_holiday as $val) { ?>
        <?php echo'<tr style="border: 1px">
               <td style="border: 1px;width:20%">' . $val['holidays']['HOLIDAYNAME'] . '</td>
                <td style="border: 1px;width:20%">' . $val['holidays']['HOLIDAYDATE'] . '</td>
                <td style="border: 1px;width:20%">' . $val['holidays']['DESCRIPTION'] . '</td>
                <td style="border: 1px;width:20%">' . $val['holidays']['HOLIDAYTYPE'] . '</td>
               
     </tr>'; ?>
    <?php } ?>



</table>

<?php  }?>