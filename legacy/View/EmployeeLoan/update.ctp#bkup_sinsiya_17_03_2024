<!--<style>
//commented by arul on 18/02/2020
    .form-horizontal .control-label{

        text-align: left;

    }
</style>-->
<section class="content-header">
    <h1 style="text-align:left; font-size: 2em;"> <?php echo $arr_loan_master['0']['EmployeeInfo']['EmpName']; ?>'s Loan Details</h1>
</section>
<!-- Main content -->
<input type="hidden" value="<?php echo isset($arr_loan_master['0']['EmployeeLoan']['emp_loan_pkey'])?$arr_loan_master['0']['EmployeeLoan']['emp_loan_pkey']:'0'; ?>" id="loan_pkey">
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- DIRECT CHAT DANGER -->
            <div class="box ">
                <table class="table ">
                    <thead>
                            <tr>
                                <th>Employee Name</th>
                                <th>Loan Amount</th>
                                <th>Tenure</th>
                                <th>Interest Rate</th>
                                <th>Started On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <td><?php echo $arr_loan_master['0']['EmployeeInfo']['EmpName']; ?></td>
                            <td><?php echo $arr_loan_master['0']['EmployeeLoan']['loan_amount']; ?></td>
                            <td><?php echo $arr_loan_master['0']['EmployeeLoan']['tenure']; ?></td>
                            <td><?php echo $arr_loan_master['0']['EmployeeLoan']['intrest_rate']; ?></td>
                            <td><?php echo $arr_loan_master['0']['EmployeeLoan']['emi_start_month']; ?></td>
                        </tbody>
                </table>
                <!-- <div class="col-md-12">
                    <h3 id="message" style="color:white;padding:5px 5px 4px 20px; "></h3>
                </div> -->
                <!-- <hr> -->
                <div class="col-md-12" align="left">
                    <!-- <div class="col-md-4">
                        <h3>To be Paid: <?php echo ($balance_amount == null)?'0':$balance_amount; ?></h3>
                    </div> -->
                    <?php
                    if ($balance_amount=='0') {}else{
                    ?>
                    <div class="col-md-6" style="text-align: left; padding-left: 0px;">
                        <input type="text" name="amount_pay" class="form-control" id="amount_pay" placeholder="Amount" style="width: 100px;"><br>
                        <?php
                        if ($balance_amount=='0') {}else{
                        ?>
                            <textarea name="remarks" id="remarks" class="form-control" placeholder="Enter your remarks"></textarea>
                        <?php
                        }
                        ?>
                    </div>
                    <div class="col-md-6" style="text-align: right; padding-right: 0px; padding-top: 30px;">
                        <button class="btn btn-primary" onclick="pay_amount();" style="width: 100%;">Additional Payment for this month</button>
                    </div>
                    <?php
                    }
                    ?>
                </div>
                <!-- <div class="col-md-12">
                    <h6></h6>
                    
                </div> -->
                <div class="col-md-12">
                    <!-- <h6>&nbsp;</h6> -->
                    <hr>
                    <h3 id="Balance">Balance Amount: <?php echo ($balance_amount == null)?'0':$balance_amount; ?> <?php if($balance_amount == '0'){} else { ?> <button class="btn btn-success pull-right" onclick="updatecompleted(this);">Update Loan as completed</button> <?php } ?></h3>
                    <input type="hidden" name="to_be_paid" id="to_be_paid" value="<?php echo ($balance_amount == null)?'0':$balance_amount; ?>">
                </div>
                <div class="col-md-12">
                    <?php
                ///    $totalpaid = $arr_loan_master['0']['EmployeeLoan']['emi_start_month'];
                    //  Edited by sreekanth 30/05/2019
                        // $completions = ceil(($total/$emi_pay)*100);
                    //The checking is the case of loan_amount == 0. By ***ARUL P DAS on 18/11/2019
                    if($arr_loan_master['0']['EmployeeLoan']['loan_amount']!=0){
                        $completions = ceil(($total/$arr_loan_master['0']['EmployeeLoan']['loan_amount'])*100);
                    }else{
                        $completions= ceil(0);
                    }
                    // Ends
                        ?>
                    <h3>Loan Completion<span class="pull-right comple"><?php echo $completions; ?>% Completed</span></h3>
<!--                    <div class="progress">

                        <div class="progress-bar progress-bar-green" role="progressbar" aria-valuenow="<?php echo $completions; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $completions; ?>%">
                            <span class="sr-only"><?php echo $completions; ?>% Completed</span>
                        </div>
                    </div>-->
                    <div class="progress progress-xs" style="background:#ff7979; ">
                        <div id="progr" class="progress-bar progress-bar-warning progress-bar-striped" role="progressbar" aria-valuenow="<?php echo $completions; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $completions; ?>%">
                            <span class="sr-only"><?php echo $completions; ?>% Completed</span>
                        </div>
                    </div>
                </div>
                <?php if($completions > 90){ } else { ?>
                <div class="col-md-12">
                    <hr>
                    <h3>Transfer EMI</h3>
                    <!-- <div class="col-md-12">
                        <h4>Transfer an EMI To Another month?</h4>
                    </div> -->
                    <div class="form-group">
                        <!-- <div class="col-md-4">
                            <div class="">
                                <input type="text" class="form-control dateCalender" placeholder="Taken Month" value="" name="specifications" id="from_month" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="">
                                <input type="text" class="form-control dateCalender"  placeholder="Affecting Month" value="" name="specifications" id="allocated_month" autocomplete="off">
                            </div>
                        </div> -->
                        <!-- <?php
                            debug($last_month);
                        ?> -->
                        
                        <!-- The following date format is done by ***ARUL P DAS on 16/11/2019 -->
                        <div class="col-md-4">
                            From
                            <select name="specifications" id="from_month" class="form-control" onchange="checkmonth();">
                                <?php
                                    foreach ($loan_months as $key => $value) {
                                        // echo "<option>".$loan_months[$key]['emp_loan_info']['loan_month']."<option>";
                                        echo "<option>".$value['emp_loan_info']['loan_month']."</option>";
                                    }
                                ?>
                            </select>
                            <input type="hidden" name="hiddencheckdate" id="hiddencheckdate" value="">
                        </div>
                        <div class="col-md-4">
                            To
                            <select name="specifications" id="allocated_month" class="form-control">
                                <?php
                                    foreach ($loan_months_new as $key => $value) {
                                        $month=$value['emp_loan_info']['loan_month'];
                                        echo "<option value='$month'>".$month."</option>";

                                    }
                                    $start_month = (strtotime($last_month[0][0]['last']));//This is to add next month to the dropdown list to change the EMI to the following next month. By ***ARUL P DAS on 16/11/2019***
                                    $month = date('Y-m', strtotime("+1 month", $start_month));
                                    echo "<option value='$month'>".$month."</option>";
                                ?>
                            </select>
                            <?php
                                // debug($arr_last);
                            ?>
                        </div>
                        <div class="col-md-4" >
                            <br>
                            <div class="">
                                <button id="loan_updates" class="btn btn-primary">Change EMI Month </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12" style="height: 100px; overflow-y: scroll;">
                    <table class="table ">
                        <thead>
                            <tr>
                                <th>Sl No  </th>
                                <th style="width: 70px;">Month</th>
                                <!-- <th>Opening Balance</th> -->
                                <th>EMI</th>
                                <!-- <th>Interest</th> -->
                                <!-- <th>Principal</th> -->
                                <!-- <th>Paid Amount</th> -->
                                <!-- <th>Closing Balance</th> -->
                                <th>Monthly Status</th>
                            </tr>
                        </thead>
                    <!-- Calculating opening and closing balances in each month. Deducts the amount_paid from the opening balance amount and makes it as closing balance. Atlast makes the opening balance as the closing balance in each iteration.
                    *********ARUL P DAS on 06/11/2019********* -->
                        <tbody>
                            <?php $i = 0; $opening_balance=0; $closing_balance=0;$count=1;
                            $flag=0; 
                            $zero_case=0;
                             ?>
                            <?php foreach($arr_loan_data as $loan) { ?>
                            <?php
                                if($opening_balance==0){$opening_balance=$arr_loan_master[0]['EmployeeLoan']['loan_amount'];}
                                $closing_balance=$opening_balance-$loan['EmployeeLoanInfo']['amount_paid'];
                                //The below code is used to check wether emi is greater than opening balance. In case if emi is greater than opening balance. We need to subtract the difference of emi and opening balance from emi amount.***By ARUL P DAS on 11-11-2019
                                if($loan['EmployeeLoanInfo']['loan_emi']>$opening_balance){
                                    $diff=$loan['EmployeeLoanInfo']['loan_emi']-$opening_balance;
                                    $emi=$loan['EmployeeLoanInfo']['loan_emi']-$diff;
                                }else{
                                    $emi=$loan['EmployeeLoanInfo']['loan_emi'];
                                }
                                if($opening_balance==0){$flag=1;}
                                $flag2=0;
                                if($loan['EmployeeLoanInfo']['paid_status']=="S"){$flag2=1;}//This is because, The additional paid amount. which always shows in the view.
                            ?>
                            <?php
                                $remarks="";
                                if($loan['EmployeeLoanInfo']['paid_status']=="P"){
                                    if($loan['EmployeeLoanInfo']['emi_transfer']=="Y"){
                                        /*echo '#d3d3d3';*/$zero_case=1;
                                    }else{
                                        $remarks="Paid";
                                        /*echo '#6cd5ae;';*/
                                    }
                                    $zero_case=1;
                                }elseif($loan['EmployeeLoanInfo']['paid_status']=="A" || $flag==1){
                                    /*echo '#6cd5ae';*/
                                }
                                if ($loan['EmployeeLoanInfo']['paid_status']=="S") {
                                    // $zero_case=1;
                                }
                            ?>
                            <?php
                            if($zero_case==1){?>
                            <?php }else{ 
                                if($emi==0){}else{
                                $i+=1;?>
                                <tr>
                                <td><?php echo $i; ?></td>
                                <td><?php echo $loan['EmployeeLoanInfo']['loan_month']; ?></td>
                                <!-- <td><?php if($flag==1){if($flag2){echo $opening_balance;}else{echo "0";}}else{echo $opening_balance;} ?></td> -->
                                <td><?php if($flag==1){if($flag2){echo $emi;}else{echo "0";}}else{echo $emi;} ?></td>
                                <!-- <td><?php if($flag==1){if($flag2){echo $loan['EmployeeLoanInfo']['interest'];}else{echo "0";}}else{echo $loan['EmployeeLoanInfo']['interest'];} ?></td> -->
                                <!-- <td><?php if($flag==1){if($flag2){echo $loan['EmployeeLoanInfo']['principle'];}else{echo "0";}}else{echo $loan['EmployeeLoanInfo']['principle'];} ?></td> -->
                                <!-- <td><?php if($flag==1){if($flag2){echo $loan['EmployeeLoanInfo']['amount_paid'];}else{echo "0";}}else{echo $loan['EmployeeLoanInfo']['amount_paid'];} ?></td> -->
                                <!-- <td><?php if($flag==1){if($flag2){echo $closing_balance;}else{echo "0";}}else{echo $closing_balance;} ?></td> -->
                                <td><?php if($remarks!=NULL || $remarks!=""){echo $remarks;}else{echo $loan['EmployeeLoanInfo']['remarks'];} ?></td>
                                </tr>
                            <?php
                                }
                             } ?>
                            <?php
                                if($opening_balance<=$emi){$flag=1;}
                                $opening_balance=$closing_balance;
                                $zero_case=0;
                                $count++;
                            } ?>
                        </tbody>
                    </table>
                </div>
                <?php } ?>
                
            </div>
        </div>
    </div>
</section>

<script>
function updatecompleted(s){
    var mode= $('#loan_pkey').val();
    var r = confirm("Are you sure to Update Loan as Completed.?");
    if (r == false) {
      return false;
    }
    $(s).html('<li class="fa fa-spin fa-spinner"></li>Updating Loan..... Please Wait');
    $.ajax({
            url:livesite+'EmployeeLoan/completed/' + mode,
            success: function(resp){
                // $('#message').html("Loan Updated Successfully").css("background","green").css("height","32px");
                // $("#progr").css("width","100%").css("background","green");
                // $(".progress-xs").css("background","green");
                // $('#Balance').html("Balance Amount: 0");
                // $(".comple").html("100% Completed");
                alert('Loan Completed Successfully');
                $('#modalForm').modal('hide');
                $('#att_table').datagrid('reload');
            }
        });
}
//The checkmonth function is used to remove the selected start month from the end month dropdown menu. By ***ARUL P DAS on 16/11/2019***
function checkmonth(){
    var month= $('#from_month').val();
    var mode= $('#loan_pkey').val();
    $.ajax({
        url:livesite+'EmployeeLoan/checkmonth/' + month +'/'+mode,
        success: function(resp){

          var response = JSON.parse(resp);
            if(response.success == 1){
               $('#allocated_month').html(response.data);
            }
        }
    });
}

//THe pay_amount() is used to pay amount in current month, Not respect to the TENURE. Created by ARUL P DAS on 06/11/2019
function pay_amount(){
    var amount=parseInt($('#amount_pay').val());
    var remarks=$('#remarks').val();
    var to_be_paid=parseInt($('#to_be_paid').val());
    var mode= $('#loan_pkey').val();
    if ($('#amount_pay').val()=='' || $('#amount_pay').val()==null || amount<=0) {
        alert('Enter a Valid Amount');
        $('#amount_pay').val("");
        return false;
    }
    if (isNaN(amount)) {
        alert('Only Numbers Allowed');
        $('#amount_pay').val("");
        return false;
    }
    if(amount>to_be_paid){
        alert('Amount should not be greater than Balance Amount.');
        $('#amount_pay').val("");
        return false;
    }
    if($('#remarks').val()=='' || $('#remarks').val()==null){
        alert('Enter remarks.');
        return false;
    }
    $.ajax({
        url:livesite+'EmployeeLoan/amount_pay/' + mode +'/'+ amount,
        // data:{taken_month:taken_month,affected_month:affected_month},
        type:'POST',
        data: {remarks:remarks},
        success: function(resp){
          var response = JSON.parse(resp);
            if(response.success == 0){
            // $('#loan_updates').html('Update Again');
            // alert('cannot update');
//            $('#message').html(response.msg).css("background","green").css("height","32px");
                alert(response.msg);
                $('#modalForm').modal('hide');
            }else{
                // $('#loan_updates').html('Updated').attr('disabled',true);
                alert('Updated Successfully');
                $('#modalForm').modal('hide');
                $('#att_table').datagrid('reload');
                // $('#amount_pay').val('');
            }
            // $('#message').html(response.msg).css("background","green").css("height","32px");
        }
    });
}
</script>
<script type="text/javascript">
    // function checkmonth(){
    //     $('#hiddencheckdate').val($('#from_month').val());
    // }
$(document).ready(function(){
    $('#hiddencheckdate').val($('#from_month').val());
    $('.dateCalender').datepicker({
     format: 'yyyy-mm',
        autoclose: true,
        startView: "months",
        minViewMode: "months"
    
    });


    $('#amount_pay').change(function(){
        var amount=parseInt($('#amount_pay').val());
        var to_be_paid=parseInt($('#to_be_paid').val());
        var loan_pkey= $('#loan_pkey').val();
        var emp_fkey= <?php echo $arr_loan_master[0]['EmployeeInfo']['emp_pkey']; ?>;
//        var emp_fkey=$('#emp_fkey').val();
//        alert(emp_fkey);
//        return false;
        if ($('#amount_pay').val()=='' || $('#amount_pay').val()==null || amount<=0) {
            alert('Enter a Valid Amount');
            $('#amount_pay').val("");
            $('#amount_pay').focus();
            return false;
        }
        if (isNaN(amount)) {
            alert('Only Numbers Allowed');
            $('#amount_pay').val("");
            $('#amount_pay').focus();
            return false;
        }
        if(amount>to_be_paid){
            alert('Amount should not be greater than Balance Amount.');
            $('#amount_pay').val("");
            $('#amount_pay').focus();
            return false;
        }
        $.ajax({
            url:livesite+'EmployeeLoan/payroll_check_amount_pay/' + emp_fkey,
            // data:{taken_month:taken_month,affected_month:affected_month},
            type:'POST',
            // data: {remarks:remarks},
            success: function(resp){
              var response = JSON.parse(resp);
                if(response.success > 0){
                    // $('#loan_updates').html('Update Again');
                    // alert('cannot update');
                    // $('#message').html(response.msg).css("background","green").css("height","32px");
                    alert(response.msg);
                    $('#modalForm').modal('hide');
                }
            }
        });
    });

});

$('#loan_updates').on("click",function(){
    var mode= $('#loan_pkey').val();
    var taken_month = $('#from_month').val();
    var affected_month = $('#allocated_month').val();
    
    if(taken_month == ''){
        alert("Please Select Month");
        return false;
    }
    if(affected_month == ''){
        alert("Please Select Month");
        return false;
    }
    if(taken_month == affected_month){
        alert("Same Month Taken");
        return false;
    }
//    send();
    $(this).html('<li class="fa fa-spin fa-spinner"></li>Updating ..... Please Wait');
    $.ajax({
        url:livesite+'EmployeeLoan/update_transfer/' + mode,
        data:{taken_month:taken_month,affected_month:affected_month},
        type:'POST',
        success: function(resp){
            var response = JSON.parse(resp);
            if(response.success == 0){
            $('#loan_updates').html('Update Again');
            }else{
                // $('#loan_updates').html('Updated').attr('disabled',true);
//                alert('Employee Loan Saved successfully');
                $('#modalForm').modal('hide');
                $('#att_table').datagrid('reload');
            }
//            $('#message').html(response.msg).css("background","green").css("height","32px");
            alert(response.msg);
        }
    });
});
    
    
    function send() {
            var accessToken = "772935e475314a9ebe155af07d926510",
        baseUrl = "https://api.api.ai/v1/",
        $speechInput,
        $recBtn,
        recognition,
        messageRecording = "Recording...",
        messageCouldntHear = "I couldn't hear you, could you say that again?",
        messageInternalError = "Oh no, there has been an internal server error",
        messageSorry = "I'm sorry, I don't have the answer to that yet.";

      var text = 'how old are you?';
      $.ajax({ 
        type: "POST",
        url: baseUrl + "query",
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        headers: {
          "Authorization": "Bearer " + accessToken
        },
        data: JSON.stringify({query: text, lang: "en", sessionId: "runbarry"}),

        success: function(data) {
          prepareResponse(data);
        },
        error: function() {
          respond(messageInternalError);
        }
      });
    }
function prepareResponse(val) {
  var debugJSON = JSON.stringify(val, undefined, 2),
      spokenResponse = val.result.speech;

  respond(spokenResponse);
  debugRespond(debugJSON);
}
function debugRespond(val) {
  alert(val);
}
function respond(val) {
  if (val == "") {
    val = 'sorry';
  }
//  
//  
//    var msg = new SpeechSynthesisUtterance();
//    var voices = window.speechSynthesis.getVoices();
//    msg.voiceURI = "native";
//    msg.text = val;
//    msg.lang = "en-US";
//      window.speechSynthesis.speak(msg);
//
//  $("#spokenResponse").addClass("is-active").find(".spoken-response__text").html(val);
}
</script>