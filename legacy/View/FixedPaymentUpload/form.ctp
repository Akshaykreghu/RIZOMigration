<style>

    .form-horizontal .control-label{
        text-align: right;


    }
    .form-group{
        height:34px; 
    }
    .modal-content{
        /* new custom width */
        width: 95% !important;
        /* must be half of the width, minus scrollbar on the left (30px) */
    }
</style>
<div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Fixed Payment Upload</h4><!--BY ****ARUL P DAS on 07/11/2019**** -->
        </div>
        <div class="modal-body">
            <form  id="fixedpaymentuploadtable" action="<?php echo $this->webroot; ?>FixedPaymentUpload/FixedPaymentSave" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="emp_fkey1" class="col-sm-4 control-label">Choose Employee<span class="star">*</span></label>
                            <div class="col-sm-8">
                                <!--                          <select id="emp_fkey" class="form-control" name="emp_fkey" onchange="loadExistingCTCInfo();" >-->
                                <select id="emp_fkey1" class="form-control js-example-basic-single" style="width: 100%" name="emp_fkey1">
                                    <option value="">[--Select--]</option>
                                    <?php if(isset($arr_leaveupoload['0']['EmployeeFixedPaymentUpload']['emp_fkey'])){ ?>
                                    <option selected="selected" value="<?php echo $arr_leaveupoload['0']['EmployeeFixedPaymentUpload']['emp_fkey']; ?>"><?php echo $arr_leaveupoload['0']['Employee']['first_name'].' '.$arr_leaveupoload['0']['Employee']['last_name']; ?></option>
                                    <?php } ?>
                                    
                                </select>
                                <span id="error_emp"></span>
                            </div>    
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="month" class="col-sm-4 control-label ">Start Month</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control datepicker" placeholder="Start Month" value="<?php if(isset($arr_leaveupoload['0']['EmployeeFixedPaymentUpload']['start_date_effective'])){
                                         echo date('Y-m', strtotime($arr_leaveupoload['0']['EmployeeFixedPaymentUpload']['start_date_effective'])); //Edited by Akshay on 1-1-2024
                                    }?>" name="start_month" id="start_month" autocomplete="off" required>
                                <span id="error_start_month"></span>
                            </div>
                        </div>
                    </div>
<!--                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="month" class="col-sm-4 control-label ">End Month</label>
                            <div class="col-md-8">
                                <input type="text" class="form-control dateCalender"  placeholder="Ending Month" value="<?php if(isset($arr_leaveupoload['0']['EmployeeFixedPaymentUpload']['end_date_effective'])){
                                        echo $arr_leaveupoload['0']['EmployeeFixedPaymentUpload']['end_date_effective'];
                                    }?>" name="end_month" id="end_month" autocomplete="off">
                                <span id="error_end_month"></span>
                            </div>
                        </div>
                    </div>-->
                    <div class="form-group">
                        <div class="col-md-10">
                            <label class="col-md-4 control-label" for="first_name">Salary Head Item </label>
                            <div class="col-md-8">
                                <select id="salary_head_item" name="salary_head_item" class="form-control" required>
                                    <option value="">---Select---</option>
                                    <?php foreach ($arr_headitems as $key => $value) {                        
                                        $selected = '';
                                        if($arr_leaveupoload['0']['EmployeeFixedPaymentUpload']['salary_head_item_fkey'] == $value['SalaryHeadItems']['salary_head_item_pkey'])
                                        $selected = 'selected="selected"';
                                        ?>
                                        <option <?php echo $selected; ?> value="<?php echo $value['SalaryHeadItems']['salary_head_item_pkey']; ?>"><?php echo $value['SalaryHeadItems']['item']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <?php
                        //While edit fixed payment of an employee, getting selected occurance from the list of occurance.
                        //Here the occurance key or id is checking using ifelse ladder. By ARUL P DAS 09/11/2019
                        $oc=isset($arr_leaveupoload['0']['EmployeeFixedPaymentUpload']['occurance'])?$arr_leaveupoload['0']['EmployeeFixedPaymentUpload']['occurance']:'';
                        $sel1=$sel2=$sel3=$sel4=$sel5='';
                        if ($oc=='1') {
                            $sel1='selected="selected"';
                        }elseif ($oc=='2') {
                            $sel2='selected="selected"';
                        }elseif ($oc=='3') {
                            $sel3='selected="selected"';
                        }elseif ($oc=='4') {
                            $sel4='selected="selected"';
                        }elseif ($oc=='5') {
                            $sel5='selected="selected"';
                        }
                    ?>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label class="col-md-4 control-label" for="occurance">Occurance </label>
                            <div class="col-md-8">
                                <select id="occurance" class="form-control" name="occurance" required>
                                    <option value="">---Select---</option>
                                    <option <?php if($sel3!='')echo $sel3; ?> value="3">Monthly</option>
                                    <option <?php if($sel4!='')echo $sel4; ?> value="4">Bi-Monthly</option>
                                    <option <?php if($sel5!='')echo $sel5; ?> value="5">Quarterly</option>
                                    <option <?php if($sel2!='')echo $sel2; ?> value="2">Half-Yearly</option>
                                    <option <?php if($sel1!='')echo $sel1; ?> value="1">Yearly</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="in_amt" class="col-sm-4 control-label">Amount<span class="star">*</span></label>
                            <div class="col-sm-8">
                                <input type="number" required class="form-control" value="<?php echo isset($arr_leaveupoload['0']['EmployeeFixedPaymentUpload']['uploaded_amount']) ? $arr_leaveupoload['0']['EmployeeFixedPaymentUpload']['uploaded_amount'] : ''; ?>" name="amount" id="amount" autocomplete='off'>
                                <span id="error_amount"></span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="remarks" class="col-sm-4 control-label">Remark</label>
                            <div class="col-sm-8">
                                <textarea name="remark" class="form-control"  id="remark" ><?php echo isset($arr_leaveupoload['0']['EmployeeFixedPaymentUpload']['remarks']) ? $arr_leaveupoload['0']['EmployeeFixedPaymentUpload']['remarks'] : ''; ?></textarea>
                            </div>
                        </div>
                    </div> 
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="fixed_component_upload_pkey" name="fixed_component_upload_pkey" value="<?php echo isset($arr_leaveupoload['0']['EmployeeFixedPaymentUpload']['emp_fixed_component_upload_pkey'])? $arr_leaveupoload['0']['EmployeeFixedPaymentUpload']['emp_fixed_component_upload_pkey']: 0 ; ?>" >
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="save_btn">Save</button>
                </div>
        </div>
        </form>


    </div>
</div>
<script type="text/javascript">
$(document).ready(function(){
    
    $('#fixedpaymentuploadtable #start_month').datepicker({
        format: 'yyyy-mm',
        // format: 'mm-yyyy',
        autoclose: true,
        startView: "months",
        minViewMode: "months"
    
    });
});
$(document).ready(function(){
    //The below code is to check whether the end month selected before selecting start month.
    //And also check is end month is less than start month. By ***ARUL P DAS on 2/11/2019
    $('#fixedpaymentuploadtable #end_month').change(function(){
        var em=$('#fixedpaymentuploadtable #end_month');
        var sm=$('#fixedpaymentuploadtable #start_month');
        if(sm.val()=='' || sm.val()==null){
            $('#error_end_month').html('Please choose Start Month first').css("color","red");
            em.val('');
            return false;
        }else{
            $('#error_end_month').html('');
        }
        if (em.val()<sm.val()) {
            $('#error_end_month').html('End month should greater than start month').css("color","red");
            em.val('');
            return false;
        }else{
            $('#error_end_month').html('');
        }
    });
    //The below code is to check whether the start month is greater than end month. By ***ARUL P DAS on 3/12/2019
    $('#fixedpaymentuploadtable #start_month').datepicker().on('changeDate', function() {
        var em=$('#fixedpaymentuploadtable #end_month');
        var sm=$('#fixedpaymentuploadtable #start_month');
        var emp_fkey=$('#fixedpaymentuploadtable #emp_fkey1').val();
        //The below ajax function is used to check whether the start date is less than Date of joining. By ***ARUL P DAS on 9/12/2019
        $.ajax({
            url: livesite + "FixedPaymentUpload/checkdoj/"+sm.val()+'/'+emp_fkey ,
            success:function(response){
                 var resp = $.parseJSON(response);
                 // alert(resp.msg);
                 if (resp.success) {
//                    $('#fixedpaymentuploadtable #error_start_month').html(resp.msg).css("color","red");
                     alert(resp.msg);
                    $('#fixedpaymentuploadtable #start_month').val('');
                    return false;
                 }
                 else{
                    $('#fixedpaymentuploadtable #error_start_month').html('').css("color","red");
                 }
            }
        });
        if(em.val()=='' || em.val()==null){   
        }else{
            if (em.val()<sm.val()) {
                $('#fixedpaymentuploadtable #error_start_month').html('Start month should less than End month').css("color","red");
                // em.val('');
                return false;
            }else{
                $('#fixedpaymentuploadtable #error_start_month').html('');
            }
        }
    });
    $('#fixedpaymentuploadtable #save_btn').click(function(){
        var emp=$('#fixedpaymentuploadtable #emp_fkey1').val();
        if(emp=="" || emp==null || emp==0){
            $('#error_emp').html('Please choose an employee.').css("color","red");
            return false;
        }else{
            $('#error_emp').html('');
        }
        var amount=$('#fixedpaymentuploadtable #amount').val();
        //Edited by Akshay on 1-1-2024
        if(amount=="" || amount==null ){
            $('#error_amount').html("Please enter an amount.").css("color","red");
            return false;
        }else if(parseInt(amount)<=0 || parseInt(amount)<='0'){
            $('#error_amount').html('Amount should be greater than 0.').css("color","red");
            return false;
        }else{
            $('#error_amount').html('');
        }
        //The below code is to check whether the start month is greater than end month. By ***ARUL P DAS on 3/12/2019
        var em=$('#fixedpaymentuploadtable #end_month');
        var sm=$('#fixedpaymentuploadtable #start_month');
        if(em.val()=='' || em.val()==null){
        }else{
            if (em.val()<sm.val()) {
                $('#fixedpaymentuploadtable #error_start_month').html('Start month should less than End month').css("color","red");
                // em.val('');
                return false;
            }else{
                $('#fixedpaymentuploadtable #error_start_month').html('');
            }
        }
    });
    $('#fixedpaymentuploadtable #emp_fkey1').change(function(){
        var emp=$('#fixedpaymentuploadtable #emp_fkey1').val();
        if(emp=="" || emp==null || emp==0){
            $('#error_emp').html('Please choose an employee.').css("color","red");
            return false;
        }else{
            $('#error_emp').html('');
        }
    });
    $('#fixedpaymentuploadtable #amount').change(function(){
        var amount=$('#fixedpaymentuploadtable #amount').val();
        if(isNaN(amount)){
            $('#error_amount').html('Please enter valid amount.').css("color","red");
            return false;
        }else{
            $('#error_amount').html('');
        }
        if(parseInt(amount)<=0 || parseInt(amount)<='0'){
            $('#error_amount').html('Amount should be greater than 0.').css("color","red");
            return false;
        }else{
            $('#error_amount').html('');
        }
    });

});
$(document).ready(function () {
    $("#fixedpaymentuploadtable .js-example-basic-single").select2({
        //closeOnSelect:false,
        placeholder: "All",
        allowClear: true,
        ajax: {
            url: livesite + "Attendanceregister/jsons/" ,
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function (data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.items,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            }
        },
        escapeMarkup: function (markup) {
            return markup;
        }
    });

    //$("#pincode").inputmask("999");
    $('#fixedpaymentuploadtable').parsley();
    var options = {
        success: function (responseText, statusText, xhr, $form) {
            var response = JSON.parse(responseText);
            if(response.success == 1){
                alert("Fixed Payment Upload Successfully");
                closeModal('att_table');
            }
            else{
                alert(response.msg);
            }
        }
    };

    // bind to the form's submit event 
    $('#fixedpaymentuploadtable').submit(function () {
        $(this).ajaxSubmit(options);
        return false;
    });
});
</script>