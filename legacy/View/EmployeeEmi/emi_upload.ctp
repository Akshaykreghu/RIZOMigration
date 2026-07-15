<script>
    $.validate({
        form: '#form-user-master'
    });
    var options = {
        success: function (resp) {
            var response = JSON.parse(resp);
            if(response.success > 0){

            $('#modalForm').modal('hide');
            $('#att_table').datagrid('reload');
            $('#attdatacsv').val('');
            // $("#filterby_branch").select2("val", "");
            $("#emp_fkey").select2("val", "");

            $.notify($.parseJSON(resp).msg, {
                type: 'success',
                allow_dismiss: false
            });
            }else{
                alert(response.msg);
            }


        }  // post-submit callback
    };


    $('#form-user-master').on('submit', function (event) {
        event.preventDefault();
        if (confirm(" Do You Want  To Save The Form")) {
            $('#form-user-master').ajaxSubmit(options)
        }
    });


</script>
<div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title"> Employee EMI Upload</h4>  
        </div>
        <div class="modal-body">
            <!-- Form starts -->

            <form class="form-horizontal" id="form-user-master" action="<?php echo $this->webroot; ?>EmployeeEmi/employeeemiloansave" method="POST">
                 <!-- <form class="form-horizontal" id="form-user-master" action="<?php echo $this->webroot; ?>EmployeeLoan/emi_upload" method="POST"> -->
                <div class="modal-body">
                        <div class="form-group">
                        <div class="col-md-10">
                            <label for="in_date" class="col-sm-4 control-label">Choose month<span class="star">*</span></label>
                            <div class="col-md-7">
                                <select id="emp_month" name="ctc_upload_type " class="form-control js-example-basic-single" onchange="find_employee();">
                                        <option value="">Select Month</option>
                                        <?php
                                        $start_month = strtotime(date('Y-m', strtotime("+1 month", strtotime(date('Y-m')))));

                                        for ($i = 0; $i < 12; $i++) {
                                            $month = date('Y-m', strtotime("-$i month", $start_month));
                                            // if ($month == date('Y-m')) {
                                            //     echo '<option selected="selected" value="' . $month . '">' . date('M-Y', strtotime("-$i month", $start_month)) . '</option>';
                                            // } else {
                                                echo '<option value="' . $month . '">' . date('M-Y', strtotime("-$i month", $start_month)) . '</option>';
                                            // }
                                        }
                                        ?>
                                    </select>                 </div>
                        </div>
                        <!-- <?php debug($value); ?> -->
                    </div>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="in_date" class="col-sm-4 control-label">Choose Employee<span class="star">*</span></label>
                            <div class="col-md-7">
                                <select id="emp_fkey1" class="form-control js-example-basic-single" name="emp_pkey"  onchange="findextingsalary();" required="required">
                                    <!-- <option value=""><input type="text" class="form-control" placeholder="Search"></option> -->
                                    <!-- <option value="">Select </option> -->
                                 
                              

                                </select>                            </div>
                        </div>
                      
                    </div>


                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="out_time" class="col-sm-4 control-label">Select Loan <span class="star">*</span></label>
                            <div class="col-md-7">
                                <select id="emi_amt" class="form-control" name="loan_pkey"  required="required" onchange="findingbalance();">
                                    <option value="">[--Select--]</option>
                                   <!--  <?php foreach ($arr_loan as $key => $val) { ?>

                                      <option value="<?php echo $val['loan']['amount']; ?>"><?php echo $val['loan']['loan_amount']; ?></option>  
                                   <?php } ?> -->
                                </select>
                            </div>
                        <!-- <?php debug($val); ?>  -->
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="out_date" class="col-sm-4 control-label">EMI Amount</label>
                            <div class="col-md-7">
                                <input type="number"  class="form-control" value="" name="loan_emi" id="loan_emi" min="0" pattern="[0-9]">
                            </div> 
                        </div>
                    </div> 
                     <div class="form-group">
                        <div class="col-md-10">
                            <label for="out_date" class="col-sm-4 control-label">Balance Amount</label>
                 <div class="col-md-7">
                                <input type="text" class="form-control" class="form-control" value="" name="balance_amount" id="balance_amount" readonly>
                            </div> 
                                 </div>
                    </div>
 <div class="col-md-7">
                                <input type="hidden" class="form-control" class="form-control" value="" name="loan_amount" id="loan_amount">
                            </div> 
                             <div class="col-md-7">
                                <input type="hidden" class="form-control" class="form-control" value="" name="amount" id="amount">
                            </div> 


                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="out_date" class="col-sm-4 control-label">Remark</label>
                            <div class="col-md-7">
                                <input type="text" class="form-control" class="form-control" value="<?php echo $data['remarks'] ?>" name="remarks" id="remarks">
                            </div> 
                        </div>
                    </div>


                    
                        <div class="modal-footer">
                            <input type="hidden" required="required" class="form-control" id="month_year" value=""name="month_year"      >
                            <input type="hidden" required="required" class="form-control" value="<?php echo $data['emp_loan_pkey'] ?>"name="emp_loan_pkey"      >
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                            <button type="submit"  name="btn-submit" id="btn-submit" class="btn btn-success">Save</button>

                        </div>


                </div>



            </form>
            <!-- Tax Head Detail Form -->





            <!-- form ends-->
        </div>

    </div>

</div>
<script type="text/javascript">
//number valiadtion
//    function numbevalidation() {
////        var loan = $('#loan_amount').val(); .
////        var rate = $('#intrest_rate').val();
////        var ten = $('#tenure').val();
//            
//        
//    }

    //already  
    function findextingsalary() {
        var month = $('#emp_month').val();
        var emp_fkey = $('#emp_fkey1').val();
        $("#loan_emi").val('');
        // alert(emp_fkey);
        var url = 'EmployeeEmi/getEmi';
        
        // alert(url);
        $('.form-control').attr("disabled",true);
//value passiing ajax   
        $.ajax({
            url: url,
            type: 'post',
            data: {
                month_year: month,
                empid: emp_fkey,
            },
            success: function (resp) {
                var json_obj = $.parseJSON(resp);

               
                if (json_obj.success == 1) {
                    
                    $('#emi_amt').html(json_obj.data);
                    $('#amount').val(json_obj.sum);
                    $('#balance_amount').val(json_obj.sums);
                    $('#loan_amount').val(json_obj.loans);
                    $('.form-control').attr("disabled",false);
                    // $('#balance_amount').attr("disabled",true);
                   
                } else {
                    $('.form-control').attr("disabled",false);
                }
            }
        });
       
    }
        function findingbalance() {
        var month = $('#emp_month').val();
        var emp_fkey = $('#emp_fkey1').val();
        var loan_key = $('#emi_amt').val();
        $("#loan_emi").val('');

        // alert(loan_key);
        var url = 'EmployeeEmi/getBalance';
      
        // alert(url);
        // $('.form-control').attr("disabled",true);
//value passiing ajax   
        $.ajax({
            url: url,
            type: 'post',
            data: {
                month_year: month,
                empid: emp_fkey,
                loan_pkey: loan_key,
            },
            success: function (resp) {
                var json_obj = $.parseJSON(resp);
                // alert(json_obj);

               
                if (json_obj.success == 1) {
                    
                  
                    $('#balance_amount').val(json_obj.sum);
                    // $('#balance_amount').attr("disabled", "disabled");
                    // $('#loan_amount').val(json_obj.loans);
                    // $('.form-control').attr("disabled",false)
                   
                } else {
                    $('.form-control').attr("disabled",false);
                }
            }
        });
    }
        // function find_employee() {
              function find_employee() {
        var month = $('#emp_month').val();
       $("#emp_fkey1").val(1);
       $("#emi_amt").val('');
       $("#balance_amount").val('');
       $("#loan_emi").val('');

      // alert(month);
      
       $("#emp_fkey1").select2(
                {
                    //closeOnSelect:false,
                    placeholder: "All",
                    allowClear: true,
                    ajax: {
                        url: livesite + "EmployeeEmi/getEmployee/" + month,
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
       // $("#emp_fkey1").select2("destroy");
    }
    
    

        //alert(branch);
       

//    function loancal() {
//        var amount = $('#loan_amount').val();
//        var month = $('#tenure').val();
//        var result = amount / month;
//        $("#emi_amount").val(result.toFixed(2));
//
//    };
    $(document).ready(function () {

    $('#month_year').val($('#emp_month').val());
    // find_employee();
// $('#balance_amount').attr("disabled", "disabled");

        // bind to the form's submit event 
        $('#attendanceuploadtable').submit(function () {
            $('#btn-submit').html('<li class="fa fa-spinner fa-spin"></li>Saving').prop("disabled", true);
            $(this).ajaxSubmit(options);
            return false;
        });

    });
</script>