<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<br>
<div class="col-md-12">
    <div class="col-md-12" id="conis">
        
    </div>
    Active Leave Year is :<?php echo $year; ?>
<?php
    foreach($leaves as $leave){
        ?>
<div class="col-md-12">
        <table class="table table-bordered" style="border:1px solid #3C8DBC">
            <thead>
                <tr style="    background: #00659f;
    color: white;
    font-weight: 800;">
                    <td colspan="6"><?php echo $leave['leavegroup']['leavepolicy_group']['LEAVEPOLICY_GROUP_NAME']; ?></td>
                </tr>
                
                <tr>
                    <td>Leave Policy</td>
                    <td>Leave Allotted Days</td>
                    <td>Carry Forward</td>
                    <td>Allow Negative</td>
                    <td>Is Sandwich</td>
                    <td>Pending Requests</td>
                </tr>
            </thead>
            <tbody>
                <?php
                $leavepolic = $leave['leavepolicy'];
                $total_pendings = 0;
                        foreach ($leavepolic as $l){
                            ?>
                <tr>
                    <td><?php echo $l['leav']['salary_head_items']['item']; ?></td>
                    <td><?php echo $l['leav']['leavepolicy']['alloted_leave_forthe_year'] ; ?></td>
                    <td><?php echo $l['leav']['leavepolicy']['CARRY_FORWARD_LIMIT'];?></td>
                    <td><?php echo $l['leav']['leavepolicy']['ALLOW_NEGETIVE']; ?></td>
                    <td><?PHP echo $l['leav']['leavepolicy']['IS_SANDWICH']; ?></td>
                    <td><?php echo $l['pendings']; if($l['pendings'] > 0){ ?><span class="pull-right"><button title="This will send a mail to all the concerned persons to complete the action" style="padding: 2px;" class="btn btn-primary" onclick="Notify(this,<?php echo $l['leav']['salary_head_items']['salary_head_item_pkey']; ?>)"><li class="fa fa-exclamation-triangle"></li></button></span></div><?php } ?></td>
                </tr>
                <?php
                    $total_pendings += $l['pendings'];
                        }
                ?>
            </tbody>
        </table>
</div>
<div class="progress progress-xxs">
                <div class="progress-bar progress-bar-danger progress-bar-striped" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%">
                  <span class="sr-only">60% Complete (warning)</span>
                </div>
              </div>

<?php
        }
?>
</div>
<div id="actionType" class="col-md-12">
    <div class="col-md-12 box-body">
        
    </div>
    <?php
    if($total_pendings > 0){
        ?>
    <!--<button class="btn btn-primary" onclick="approve();">Auto Approve all Pending Leaves</button>-->Approve All Leaves Or Take any Action 
    <?php
        }
        else{
        ?>
    <button class="btn btn-primary pull-right" onclick="processleave();">Process</button>
    <?php
        }
        ?>
</div>
<div id="modalDetailForm" class="modal fade">
        <div class="modal-dialog modal-md">
            <div class="modal-content" id="modaldetails-content">
              
            </div>
        </div>
    </div>
<script>
    function processleave(){
		var abranch = $('#branch').val();
        $.ajax({
                    url:livesite+'YearEnd/processleave',
                    type:"POST",
                    data:{branch:abranch},
                    success: function(resp){
                        var response = $.parseJSON(resp);
//                        $(th).attr('disabled',false);
//                        $(th).find('li').addClass('fa-check-square').removeClass('fa-spinner fa-spin');
//                        clearInterval(progress);
//                        widths=10;
                        if(response.success == 1){
                            $('#loadprocess').html('<br><div class="col-md-12"><h1 style="text-align:center;">Year End processed Successfully</h1></div>');
//                            $("#modalDetailForm").modal('hide');
                                $.notify("Year End processed Successfully", {
                                    type: 'successs',
                                    allow_dismiss: false
                                });
                        }
                        else{
                           $('#loadprocess').html('<br><div class="col-md-12"><h1 style="text-align:center;">Year End processing Failed</h1></div>');
//                            $("#modalDetailForm").modal('hide');
                                $.notify("Year End processing Failed", {
                                    type: 'danger',
                                    allow_dismiss: false
                                }); 
                        }
                        
                    }
                });
    }
    function Notify(th,salary_head_items){
        var progress = null;
        var abranch = $('#branch').val();

//        var url = livesite+'YearEnd/loaders';
        $(th).attr('disabled','disabled');
        $(th).find('li').addClass('fa-spinner fa-spin').removeClass('fa-exclamation-triangle');
        var container = $("#modalDetailForm #modaldetails-content");
        

                $.ajax({
                    url:livesite+'YearEnd/notice',
                    type:"POST",
                    data:{salary_head_items:salary_head_items,branch:abranch},
                    success: function(resp){
                        $(th).attr('disabled',false);
                        $(th).find('li').addClass('fa-check-square').removeClass('fa-spinner fa-spin');
//                        clearInterval(progress);
//                        widths=10;
                                $.notify("Notice Sent", {
                                    type: 'successs',
                                    allow_dismiss: false
                                });
                        if(resp == true){
                            $('#conis').html('');
//                            $("#modalDetailForm").modal('hide');
                        }
//                        else{
//                            
//                        }
                        
                    }
                });
    }
    function approve(){
            var widths= 10;
            var url = livesite+'YearEnd/loaders';
            var abranch = $('#branch').val();
//            $('#loadprocess').load(url, function() {
//                var progress = setInterval( function () {
//                    widths += 10;
//                    $('#progress').css('width',widths+'%');
//                    if(widths == 100){widths=10;clearInterval(progress);}
//                }, 1200 );
//            });
$('#loadprocess').html('<br><div class="col-md-12"><h1 style="text-align:center;font-size:220px;"><li class="fa fa-spinner fa-spin"></li></h1><div>');
            $.ajax({
                    url:livesite+'YearEnd/approve',
                    type:"POST",
                    data:{branch:abranch},
                    success: function(resp){
                        if(resp == true){
                            $('#loadprocess').fadeOut('slow', function(){
                            $('#loadprocess').load(livesite+'YearEnd/loadprocess/',{id:abranch}, function(){
                                $('#loadprocess').fadeIn(1000);
                            });
                        });
                        }
                    }
                });
    }
</script>