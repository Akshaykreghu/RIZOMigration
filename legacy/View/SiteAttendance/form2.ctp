<div class="form-group form-group-sm">
    <input type="hidden" id ="transaction_key" value="<?php echo isset($arr_policy['0']['site_transactions']['site_transactions_pkey']) ? $arr_policy['0']['site_transactions']['site_transactions_pkey'] : "";?>">
    <div class="col-sm-6">
        <label class="col-sm-4 control-label" for="item_desc">Select Shift<label style="color:red">*</label></label>
        <div class="col-sm-7">
            <select id="item_specification" name="item_specification" class="form-control">
                <option value="">---select---</option>
               <?php foreach ($arr_shifts as $val) { 
                $selected = ($arr_policy['0']['site_transactions']['day_time_seq_fkey'] == $val['working_day_time_procedures']['day_time_seq']) ? 'selected="selected"' : '';
                 echo '<option value="' . $val['working_day_time_procedures']['day_time_seq'] . '" ' . $selected . '>'.$val['working_day_time_procedures']['day_time_desc'].' </option>';
                 } ?>
                    
            </select>
        </div>
    </div>
    <div class="col-sm-6">
        <label class="col-sm-4 control-label" for="item_desc">Designation<label style="color:red">*</label></label>
        <div class="col-sm-7">
            <select id="desig" name="item_specification" class="form-control">
                <option value="">---select---</option>
                    <?php foreach ($arr_designations as $val) { 
                $selected = ($arr_policy['0']['site_transactions']['designation_id'] == $val['designation']['id']) ? 'selected="selected"' : '';
                 echo '<option value="' . $val['designation']['id'] . '" ' . $selected . '>'.$val['designation']['desig_name'].' </option>';
                 } ?>
            </select>
        </div>
    </div>

</div>
<div class="form-group form-group-sm">
  
    <div class="col-sm-6">
        <label class="col-sm-4 control-label" for="item_desc">Sales Rate<label style="color:red">*</label></label>
        <div class="col-sm-7">
            <input class="form-control" placeholder="Sales Rate" type="number" value='<?php echo isset($arr_policy['0']['site_transactions']['srate']) ? $arr_policy['0']['site_transactions']['srate'] : " "; ?>' name="item_desc" id="sales_price" >
        </div>
    </div>
     <!-- Edited by Akshay on 21-8-2025 -->
    <!-- number of emp -->
    <?php if ($company_code == 'DEMO' || $company_code == 'GLET'|| $company_code == 'ABSG'|| $company_code == 'SCRT') { ?>
        <div class="col-sm-6">
            <label class="col-sm-4 control-label" for="item_desc">Number of Employees<label style="color:red">*</label></label>
            <div class="col-sm-7">
                <input class="form-control" placeholder="Number of Employees" type="number" value='<?php echo isset($arr_policy['0']['site_transactions']['emp_count']) ? $arr_policy['0']['site_transactions']['emp_count'] : " "; ?>' name="item_desc" id="count">
            </div>
        </div>
        <div class="col-sm-12">
            <label class="col-sm-12 control-label" for="item_desc">Expense Rate For:<label style="color:red">*</label></label>
        </div>
    <?php } else { ?>
        <div class="col-sm-6">
            <label class="col-sm-4 control-label" for="item_desc">Expense Rate<label style="color:red">*</label></label>
            <div class="col-sm-7">
                <input class="form-control" placeholder="Expense Rate" type="number" value='<?php echo isset($arr_policy['0']['site_transactions']['eratess']) ? $arr_policy['0']['site_transactions']['eratess'] : ""; ?>' name="item_desc" id="expense_rate">
            </div>
        </div>
    <?php } ?>
    <!-- End -->
    
</div>
    
<!-- Edited by Akshay on 21-8-2025 -->
<?php if ($company_code == 'DEMO' || $company_code == 'GLET'|| $company_code == 'ABSG'|| $company_code == 'SCRT') { ?>
    <div class="form-group form-group-sm">


        <div class="col-sm-6">
            <label class="col-sm-4 control-label" for="item_desc">Expense Rate for 28 Days<label style="color:red">*</label></label>
            <div class="col-sm-7">
                <input class="form-control" placeholder="Expense Rate for 28 Days" type="number" value='<?php echo isset($arr_policy['0']['site_transactions']['eratess_28']) ? $arr_policy['0']['site_transactions']['eratess_28'] : " "; ?>' name="item_desc" id="expense_rate_28">
            </div>
        </div>
        <!-- Providing mandatory notation for start date and end date Start -->


        <div class="col-sm-6">
            <label class="col-sm-4 control-label" for="item_desc">Expense Rate for 29 Days<label style="color:red">*</label></label>
            <div class="col-sm-7">
                <input class="form-control" placeholder="Expense Rate for 29 Days" type="number" value='<?php echo isset($arr_policy['0']['site_transactions']['eratess_29']) ? $arr_policy['0']['site_transactions']['eratess_29'] : " "; ?>' name="item_desc" id="expense_rate_29">
            </div>
        </div>
    </div>


    <div class="form-group form-group-sm">


        <div class="col-sm-6">
            <label class="col-sm-4 control-label" for="item_desc">Expense Rate for 30 Days<label style="color:red">*</label></label>
            <div class="col-sm-7">
                <input class="form-control" placeholder="Expense Rate for 30 Days" type="number" value='<?php echo isset($arr_policy['0']['site_transactions']['eratess_30']) ? $arr_policy['0']['site_transactions']['eratess_30'] : " "; ?>' name="item_desc" id="expense_rate_30">
            </div>
        </div>
        <!-- Providing mandatory notation for start date and end date Start -->


        <div class="col-sm-6">
            <label class="col-sm-4 control-label" for="item_desc">Expense Rate for 31 Days<label style="color:red">*</label></label>
            <div class="col-sm-7">
                <input class="form-control" placeholder="Expense Rate for 31 Days" type="number" value='<?php echo isset($arr_policy['0']['site_transactions']['eratess_31']) ? $arr_policy['0']['site_transactions']['eratess_31'] : " "; ?>' name="item_desc" id="expense_rate_31">
            </div>
        </div>
    </div>
<?php } ?>
<!-- End -->
 <div class="form-group form-group-sm">


    <div class="col-sm-6">
        <label class="col-sm-4 control-label" for="item_desc">Start Date <label style="color:red">*</label></label>
        <div class="col-sm-7">
            <input class="form-control" placeholder="Start date" type="text" value='<?php echo isset($arr_policy['0']['site_transactions']['start_date_effective']) ? $arr_policy['0']['site_transactions']['start_date_effective'] : ""; ?>' name="item_desc" id="STDE">
        </div>
    </div>
    <!-- Providing mandatory notation for start date and end date Start -->


    <div class="col-sm-6">
        <label class="col-sm-4 control-label" for="item_desc">End Date <label style="color:red">*</label></label>
        <div class="col-sm-7">
            <input class="form-control" placeholder="End date" type="text" value='<?php echo isset($arr_policy['0']['site_transactions']['end_date_effective']) ? $arr_policy['0']['site_transactions']['end_date_effective'] : ""; ?>' name="item_desc" id="ENDE">
        </div>
    </div>
</div>

<div class="form-group form-group-sm">
    <!-- Edited by Akshay on 21-8-2025 -->
    <?php if ($company_code != 'DEMO' && $company_code != 'GLET' && $company_code != 'ABSG' && $company_code != 'SCRT') { ?>
        <div class="col-sm-6">
            <label class="col-sm-4 control-label" for="item_desc">Number of Employees<label style="color:red">*</label></label>
            <div class="col-sm-7">
                <input class="form-control" placeholder="Number of Employees" type="number" value='<?php echo isset($arr_policy['0']['site_transactions']['emp_count']) ? $arr_policy['0']['site_transactions']['emp_count'] : " "; ?>' name="item_desc" id="count">
            </div>
        </div>
    <?php } ?>
    <!-- End -->

    <!-- Providing mandatory notation for start date and end date End -->

    <div class="col-sm-6">
        <button style="margin-right: 3px;margin-bottom: 14px; margin-right: 50px;" type="button" class="btn btn-success pull-right" onclick="addtolist();" id="saveBtn">Save </button>

    </div>
</div>


</div>

<script>
   $(document).ready(function(){
       
    $('#STDE').datepicker({
         format: 'yyyy-mm-dd',
         autoclose: true
    
    });
    
    $('#ENDE').datepicker({
         format: 'yyyy-mm-dd',
         autoclose: true
    
    });
    
   }); 
    
   $('#STDE').datepicker().on('changeDate', function () {
        var site_transaction_pkey =<?php echo isset($arr_policy['0']['site_transactions']['site_transactions_pkey']) ? $arr_policy['0']['site_transactions']['site_transactions_pkey'] : "" ?> + "";
        if (site_transaction_pkey != 0) {
            $("#saveBtn").prop('disabled', true);
            var siteKey = "<?php echo isset($arr_policy['0']['site_transactions']['site_fkey']) ? $arr_policy['0']['site_transactions']['site_fkey'] : '' ?>" + "";
            var dayTimeSeq = "<?php echo isset($arr_policy['0']['site_transactions']['day_time_seq_fkey']) ? $arr_policy['0']['site_transactions']['day_time_seq_fkey'] : '' ?>" + "";
            $.ajax({
                url: livesite + 'SiteAttendance/check_start_date/' + $(this).val() + '/' + site_transaction_pkey + '/' + siteKey + '/' + dayTimeSeq,
                success: function (responseText, statusText, xhr, $form) {
                    var response = JSON.parse(responseText);
                    if (response.success == true) {
                        alert(response.msg);
                        $('#STDE').val('');
                        $("#saveBtn").prop('disabled', true);
                    } else {
                        $("#saveBtn").prop('disabled', false);
                    }
                }
            });
        }
    });

    $('#ENDE').datepicker().on('changeDate', function () {
        var site_transaction_pkey =<?php echo isset($arr_policy['0']['site_transactions']['site_transactions_pkey']) ? $arr_policy['0']['site_transactions']['site_transactions_pkey'] : "" ?> + "";
        if (site_transaction_pkey != 0) {
            $("#saveBtn").prop('disabled', true);
            var siteKey = "<?php echo isset($arr_policy['0']['site_transactions']['site_fkey']) ? $arr_policy['0']['site_transactions']['site_fkey'] : '' ?>" + "";
            var dayTimeSeq = "<?php echo isset($arr_policy['0']['site_transactions']['day_time_seq_fkey']) ? $arr_policy['0']['site_transactions']['day_time_seq_fkey'] : '' ?>" + "";
            $.ajax({
                url: livesite + 'SiteAttendance/check_end_date/' + $(this).val() + '/' + site_transaction_pkey + '/' + siteKey + '/' + dayTimeSeq,
                success: function (responseText, statusText, xhr, $form) {
                    var response = JSON.parse(responseText);
                    if (response.success == true) {
                        alert(response.msg);
                        $('#ENDE').val('');
                        $("#saveBtn").prop('disabled', true);
                    } else {
                        $("#saveBtn").prop('disabled', false);
                    }
                }
            });
        }
    });
    
    function addtolist(s) {
        var policy = $('#item_specification option:selected').text();
        if (policy == '---select---') {
            alert("Please Select any Shift Policy ");
            return false;
        }

        var policy_fkey = $('#item_specification').val();

        var desig = $('#desig option:selected').text();

        if (desig == '---select---') {
            alert("Please Select any Designation ");
            return false;
        }

        var count = $('#count').val();

        if (count == '') {
            alert("Please Enter Number of Employees ");
            return false;
        }
        var desig_pkey = $('#desig').val();
        //edited by arul on 11/12/2019 included decimal values
        // var sales_price = parseInt($('#sales_price').val());
        var sales_price = parseFloat($('#sales_price').val());

        if ($('#sales_price').val() == '') {
            alert("Please Enter Sales Rate ");
            return false;
        }

        // Edited by Akshay on 19-8-2025
        var companyCode = <?php echo json_encode($company_code); ?>;
        if (companyCode == 'DEMO' || companyCode == 'GLET'|| companyCode == 'ABSG'|| companyCode == 'SCRT') {
            // Take Expense Rate for 31 Days as the main expense_rate
            var expense_rate_28 = parseFloat($('#expense_rate_28').val());
            var expense_rate_29 = parseFloat($('#expense_rate_29').val());
            var expense_rate_30 = parseFloat($('#expense_rate_30').val());
            var expense_rate_31 = parseFloat($('#expense_rate_31').val()); // main field

            if ($('#expense_rate_28').val() == '' || $('#expense_rate_29').val() == '' ||
                $('#expense_rate_30').val() == '' || $('#expense_rate_31').val() == '') {
                alert("Please Enter all Expense Rates");
                return false;
            }

            var expense_rate = expense_rate_31; // use 31-day rate for comparison

            // Compare sales_price with all expense rates
            if (
                sales_price <= expense_rate_28 ||
                sales_price <= expense_rate_29 ||
                sales_price <= expense_rate_30 ||
                sales_price <= expense_rate_31
            ) {
                alert("Sales rate should be greater than all Expense rates.");
                return false;
            }

        } else {
            var expense_rate = parseFloat($('#expense_rate').val());

            if ($('#expense_rate').val() == '') {
                alert("Please Enter Expense Rate ");
                return false;
            }

            if (expense_rate > sales_price) {
                alert("Sales rate should be greater than Expense rate.");
                return false;
            }
        }
        // End

        var stdE = $('#STDE').val();
        var start_date = new Date(stdE);
        var endse = $('#ENDE').val();
        var todate = new Date(endse);
        var transaction_key = $('#transaction_key').val();

        if (stdE == '') {
            alert("Please Enter Start Date ");
            return false;
        }
        if (endse == '') {
            alert("Please Enter End Date ");
            return false;
        }
        //  if(endse!=''){
        if (stdE > endse) {
            alert("Please Choose Correct Date Range ");
            $('#ENDE').val('');
            return false;
        }
        //} 


        $('#form2').find('input').val('');

        var companyCode = <?php echo json_encode($company_code); ?>;
        // Edited by Akshay on 21-8-2025
        if (companyCode == 'DEMO' || companyCode == 'GLET'|| companyCode == 'ABSG'|| companyCode == 'SCRT') {
            if (transaction_key == '') {
                // New row case
                var append = '<tr style="background:#541545;">' +
                    '<input type="hidden" value=" " id="siteid" name="site_transaction_fkey[]">' +
                    '<input type="hidden" value="0" name="editkey[]">' +
                    '<input type="hidden" value="1" name="status[]">'

                    +
                    '<td id="TdDayTime"><input name="TdDayTime[]" type="hidden" value="' + policy_fkey + '" >' + policy + '</td>' +
                    '<td id="TdDesg"><input name="TdDesg[]" type="hidden" value="' + desig_pkey + '" >' + desig + '</td>' +
                    '<td id="TdCount"><input name="TdCount[]" type="hidden" value="' + count + '" >' + count + '</td>' +
                    '<td id="TdSRate"><input name="TdSRate[]" type="hidden" value="' + sales_price + '" >' + sales_price + '</td>'

                    // Expense rates for 28, 29, 30, 31 days
                    +
                    '<td id="Rate28"><input name="Rate28[]" type="hidden" value="' + expense_rate_28 + '" >' + expense_rate_28 + '</td>' +
                    '<td id="Rate29"><input name="Rate29[]" type="hidden" value="' + expense_rate_29 + '" >' + expense_rate_29 + '</td>' +
                    '<td id="Rate30"><input name="Rate30[]" type="hidden" value="' + expense_rate_30 + '" >' + expense_rate_30 + '</td>' +
                    '<td id="Rate31"><input name="Rate31[]" type="hidden" value="' + expense_rate_31 + '" >' + expense_rate_31 + '</td>'

                    +
                    '<td id="StDE"><input name="StDE[]" type="hidden" value="' + stdE + '" >' + stdE + '</td>' +
                    '<td id="EtDE"><input name="EtDE[]" type="hidden" value="' + endse + '" >' + endse + '</td>' +
                    '<td><li onclick="deletetable(this);" class="btn btn-danger btn-icon"><i class="bi bi-trash"></i></li></td>' +
                    '</tr>';

                $('#tables').find('tbody').append(append);

            } else {
                // Edit case
                $("#tables tbody tr ").each(function() {
                    var quantity1 = $(this).find("input").val();
                    if (quantity1 == transaction_key) {
                        var append = '<input type="hidden" value="' + transaction_key + '" id="siteid" name="site_transaction_fkey[]">' +
                            '<input type="hidden" value="1" name="editkey[]">' +
                            '<input type="hidden" value="1" name="status[]">'

                            +
                            '<td id="TdDayTime"><input name="TdDayTime[]" type="hidden" value="' + policy_fkey + '" >' + policy + '</td>' +
                            '<td id="TdDesg"><input name="TdDesg[]" type="hidden" value="' + desig_pkey + '" >' + desig + '</td>' +
                            '<td id="TdCount"><input name="TdCount[]" type="hidden" value="' + count + '" >' + count + '</td>' +
                            '<td id="TdSRate"><input name="TdSRate[]" type="hidden" value="' + sales_price + '" >' + sales_price + '</td>'

                            // Expense rates
                            +
                            '<td id="Rate28"><input name="Rate28[]" type="hidden" value="' + expense_rate_28 + '" >' + expense_rate_28 + '</td>' +
                            '<td id="Rate29"><input name="Rate29[]" type="hidden" value="' + expense_rate_29 + '" >' + expense_rate_29 + '</td>' +
                            '<td id="Rate30"><input name="Rate30[]" type="hidden" value="' + expense_rate_30 + '" >' + expense_rate_30 + '</td>' +
                            '<td id="Rate31"><input name="Rate31[]" type="hidden" value="' + expense_rate_31 + '" >' + expense_rate_31 + '</td>'

                            +
                            '<td id="StDE"><input name="StDE[]" type="hidden" value="' + stdE + '" >' + stdE + '</td>' +
                            '<td id="EtDE"><input name="EtDE[]" type="hidden" value="' + endse + '" >' + endse + '</td>' +
                            '<td><li onclick="deletetable(this);" class="btn btn-danger btn-icon"><i class="bi bi-trash"></i></li>&nbsp;&nbsp;<li onclick="edittable(this);" class="btn btn-info btn-icon"><i class="bi bi-pencil-square"></li></td>';

                        $(this).html(append);
                    }
                });
            }
        } else {
            if (transaction_key == '') {
                //edited by megha on 01/07/2019 edit button removed from add new form
                var append = '<tr style="background:#541545;"><input type="hidden" value=" " id="siteid" name="site_transaction_fkey[]"><input type="hidden" value="0" name="editkey[]"><input type="hidden" value="1" name="status[]" ><td id="TdDayTime"><input name="TdDayTime[]" type="hidden" value="' + policy_fkey + '" >' + policy + '</td><td id="TdDesg"><input name="TdDesg[]" type="hidden" value="' + desig_pkey + '" >' + desig + '</td><td id="TdCount"><input name="TdCount[]" type="hidden" value="' + count + '" >' + count + '</td><td id="TdSRate"><input name="TdSRate[]" type="hidden" value="' + sales_price + '" >' + sales_price + '</td><td id="Rate"><input name="Rate[]" type="hidden" value="' + expense_rate + '" >' + expense_rate + '</td><td id="StDE"><input name="StDE[]" type="hidden" value="' + stdE + '" >' + stdE + '</td><td id="EtDE"><input name="EtDE[]" type="hidden" value="' + endse + '" >' + endse + '</td><td><li onclick="deletetable(this);" class="btn btn-danger btn-icon"><i class="bi bi-trash"></i></li></td></tr>';
                //end
                $('#tables').find('tbody').append(append);
            } else {
                // alert("jio");
                $("#tables tbody tr ").each(function() {
                    var quantity1 = $(this).find("input").val();
                    console.log(quantity1);
                    if (quantity1 == transaction_key) {
                        //console.log(quantity1);
                        var append = '<input type="hidden" value="' + transaction_key + '" id="siteid" name="site_transaction_fkey[]"><input type="hidden" value="1" name="editkey[]"><input type="hidden" value="1" name="status[]" ><td id="TdDayTime"><input name="TdDayTime[]" type="hidden" value="' + policy_fkey + '" >' + policy + '</td><td id="TdDesg"><input name="TdDesg[]" type="hidden" value="' + desig_pkey + '" >' + desig + '</td><td id="TdCount"><input name="TdCount[]" type="hidden" value="' + count + '" >' + count + '</td><td id="TdSRate"><input name="TdSRate[]" type="hidden" value="' + sales_price + '" >' + sales_price + '</td><td id="Rate"><input name="Rate[]" type="hidden" value="' + expense_rate + '" >' + expense_rate + '</td><td id="StDE"><input name="StDE[]" type="hidden" value="' + stdE + '" >' + stdE + '</td><td id="EtDE"><input name="EtDE[]" type="hidden" value="' + endse + '" >' + endse + '</td><td><li onclick="deletetable(this);" class="btn btn-danger btn-icon"><i class="bi bi-trash"></i></li>&nbsp;&nbsp;<li onclick="edittable(this);" class="btn btn-info btn-icon"><i class="bi bi-pencil-square"></li></td>';

                        $(this).html(append);
                    }
                });
            }
        }
        // End
    }
    
    function deletetable(s){
//        alert($(s).parent().parent().attr('class'));
        $(s).parent().parent().remove();
//            $(s).parent()
    }
     function edittable(s){
         var siteid = $(s).parent().siblings('#siteid').val();
        // alert(siteid);
         $('#form2').load(livesite + 'SiteAttendance/form2/'+ siteid);
    }
</script>