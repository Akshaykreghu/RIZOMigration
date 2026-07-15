<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<style>
    #shiva
{
  width: 100px;
	height: 100px;
	background: red;
	-moz-border-radius: 50px;
	-webkit-border-radius: 50px;
	border-radius: 50px;
  float:left;
  margin:5px;
}
.count
{
  line-height: 100px;
  color:black;

  font-size:84px;
}
.count1
{
  line-height: 100px;
  color:black;
 
  font-size:84px;
}
#talkbubble {
   width: 120px;
   height: 80px;
   background: red;
   position: relative;
   -moz-border-radius:    10px;
   -webkit-border-radius: 10px;
   border-radius:         10px;
  float:left;
  margin:20px;
}
#talkbubble:before {
   content:"";
   position: absolute;
   right: 100%;
   top: 26px;
   width: 0;
   height: 0;
   border-top: 13px solid transparent;
   border-right: 26px solid red;
   border-bottom: 13px solid transparent;
}

.linker
{
  font-size : 20px;
  font-color: black;
}

</style>
<script>
      
    function counter1(s)
    {
      
    
    $('.count1').each(function () {
$(this).text(s);
$(this).prop('Counter',0).animate({
        Counter: $(this).text()
    }, {
        duration: 4000,
        easing: 'swing',
        step: function (now) {
            $(this).text(Math.ceil(now));
        }
    });
});
    }
    
    
    function counter(s)
    {
      
    
    $('.count').each(function () {
$(this).text(s);
$(this).prop('Counter',0).animate({
        Counter: $(this).text()
    }, {
        duration: 4000,
        easing: 'swing',
        step: function (now) {
            $(this).text(Math.ceil(now));
        }
    });
});
    }
  
     $(document).ready(function(){
   $('#load').hide();
    });
    function Calculate()
    {
       var Bonus = $('#Bonuses').val();
       var IR = $('#IR').val();
       var IB = $('#IB').val();
       var OI = $('#OI').val(); 
       var add = Bonus+IR+IB+OI;
       var de = $('#MB').val();
       var LI = $('#LI').val();
       var Rnt =  $('#Rent').val();
       var OE = $('#OE').val(); 
       var other = 0;
       
      // alert(other);
        var ctc = $('#CTC').val();
        var TaxIncome = ctc;
        TaxIncome = Number(TaxIncome) + Number(other);
        //alert(TaxIncome);
        var Tax1 = 0;
        var Tax2 = 0;
        var Tax3 = 0;
        var ctc2 = 0;
        var ctc3 =0;
        var ctc1 = 0;
        var Tax = 0;
        if(TaxIncome > 250000)
            {
               if(TaxIncome >= 500000)
                   {
               ctc1 = 250000;
                   }
                   else
                       {
                        ctc1 = TaxIncome - 250000;   
                       }
                Tax1 = ctc1 * 10 / 100;
               
            }
            if(TaxIncome > 500000)
                {
                    if(TaxIncome >= 1000000)
                        {
                            ctc2 = 500000;
                        }
                        else
                            {
                  ctc2 = TaxIncome - 500000;
                            }
                  Tax2 = ctc2 * 20 / 100; 
                 
                    
                }
                if(TaxIncome > 1000000)
                    {
                        ctc3 = TaxIncome - 1000000;
                        Tax3 = ctc3 * 30 / 100;
                        
                    }
                    Tax = Tax1 + Tax2 + Tax3;
                 $('#load').show();
                 $('#Tax').val(Tax);
                 var monthl = Tax / 12;
                 $('#mTax').val(monthl);
                 counter(Tax);
                  counter1(monthl);
        
    }
</script>

<section class="content">
    <div class="row">
<div class="col-md-12">
<div class="box box-header">
    <h2>Tax Calculation</h2> <p>Calculate taxes from annual CTC.</p>
       <div class="box-body" id="div-reportcriterias">
                     <div class="col-md-3">
                     <input type="hidden" id="emp_pkey">
                <input type="text" id="CTC" class="form-control" name="CTC" placeholder="Enter Annual CTC" />
            </div>
            <div class="col-md-3">
                    
                <input type="text" id="B_P" class="form-control" name="B_P" placeholder="Enter Basic Pay" />
            </div>
              <div class="col-md-3">
                     <input type="hidden" id="emp_pkey">
                <input type="button" class="btn btn-block btn-success btn-sm" value="Calculate" id="Calculate" onclick="Calculate();" name="Calculate">
            </div>
         
                </div>
  
    
    
</div>
    
    
    
    <div class="box-body">
              <div class="box-group" id="accordion">
                <!-- we are adding the .panel class so bootstrap.js collapse plugin detects it -->
                <div class="panel box box-primary">
                  <div class="box-header with-border">
                    <h4 class="box-title">
                      <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
                        show advanced options
                      </a>
                    </h4>
                  </div>
                  <div id="collapseOne" class="panel-collapse collapse">
                    <div class="box-body">
                      <div class="form-group col-md-12">
       <div class="col-md-3">
                     <input type="hidden" id="emp_pkey">
                <input type="text" id="Bonuses" class="form-control" name="Bonuses" placeholder="Enter Bonus Income" />
            </div>
            <div class="col-md-3">
                    
                <input type="text" id="MB" class="form-control" name="MB" placeholder="Medical Bills" />
            </div>
        </div>
        <div class="form-group col-md-12">
       <div class="col-md-3">
                     <input type="hidden" id="emp_pkey">
                <input type="text" id="IR" class="form-control" name="IR" placeholder="Income From Rents" />
            </div>
            <div class="col-md-3">
                    
                <input type="text" id="LI" class="form-control" name="LI" placeholder="Loan Interests" />
            </div>
        </div>
        <div class="form-group col-md-12">
       <div class="col-md-3">
                     <input type="hidden" id="emp_pkey">
                <input type="text" id="IB" class="form-control" name="IB" placeholder="Income From Bank" />
            </div>
            <div class="col-md-3">
                    
                <input type="text" id="Rent" class="form-control" name="Rent" placeholder="Rents" />
            </div>
        </div>
        <div class="form-group col-md-12">
       <div class="col-md-3">
                     <input type="hidden" id="emp_pkey">
                <input type="text" id="OI" class="form-control" name="OI" placeholder="Enter Othe Incomes" />
            </div>
            <div class="col-md-3">
                    
                <input type="text" id="OE" class="form-control" name="OE" placeholder="Enter Othe Expenses" />
            </div>
        </div>
                    </div>
                  </div>
                </div>
                
                
              </div>
            </div>
    <div id="ShowAdvanced" class="easyui-accordion" style="margin:10px 0">
         <div id="accr" title="Show Advanced Options" data-options="iconCls:'icon-reload',selected:false" style="overflow:auto;padding:10px;">
        
    </div> 
    </div>

    </div>
</div>
     <div id="load" class="box box-body" style="margin:10px 0">
         
       <div class="col-md-3">
                <label for="exampleInputEmail1">Annual Tax</label>
            </div>
         <div class="col-md-3">
                     <input type="hidden" id="emp_pkey">
               <div id=""><span class="count"></span></div>
            </div>
         <div class="col-md-3">
                    <label for="exampleInputEmail1">Monthly Tax</label>
            </div>
         <div class="col-md-3">
                     <input type="hidden" id="emp_pkey">
                <div id=""><span class="count1"></span></div>
            </div>
         
    </div>     

</section>
    