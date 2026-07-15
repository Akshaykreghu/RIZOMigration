<style>
    @keyframes FadeIn {
        from {
            background-color: #7bebbd;
        }

        to {
            background-color: white;
        }
    }

    .FoodConsumptionTable tr {
        background-color: white;
        animation: FadeIn 1.75s ease-in-out forwards;
    }
</style>
<script>
    var options = {
        success: function (resp) {
            $('#largeModalForm').modal('hide');
            $('#att_table').datagrid('reload');
            $.notify("Success", {
                type: 'success',
                allow_dismiss: false
            });
        }  // post-submit callback
    };
    $('#itemform').on('submit', function (event) {
        
        event.preventDefault();
        if (confirm("Do You Want To Save The Form")) {
            $('#itemform').ajaxSubmit(options);
        }
    });
</script>
<div class="modal-dialog" style="width: 100%; ">

    <!-- Modal content-->
    <div class="modal-content">

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title" style="    margin: 5px 28px 0; "><b>Site Attendance</b> </h4>
        </div>
        <div class="modal-body">

            <!-- Form starts -->
            <form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>SiteAttendance/saveSite" id="itemform" name="itemform">
                <div class="form-group form-group-sm">
                <div class="col-md-12">
                    <div class="col-sm-7">
                        <label class="col-sm-4 control-label" for="item_desc">Employee Name<label style="color:red">*</label></label>
                      
                        <div class="col-sm-8">
                            <select id="desig" name="item_specification" class="form-control js-example-basic-single" style="width: 100%; " >
                                <option value="">---Select---</option>
                                <?php foreach ($site_data as $val) { //debug($val);?>
                               
                                    <option value="<?php echo $val['ed']['emp_pkey'] ?>"><?php echo $val['0']['EmpName']; ?></option>
                                <?php } ?>
                            </select> 
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-4 control-label" for="item_desc">In Time<label style="color:red">*</label></label>
                        <div class="col-sm-7">
                               <?php if($shift_data['0']['working_day_time_procedures']['isnextday'] == '1'){ ?>
            <input type="hidden" id="checkin_date"  value="1">
            <input type="hidden" id="checkin_time"  value="<?php echo $shift_data['0']['working_day_time_procedures']['on_dutty1'];?>">
            <?php } ?>
                            <input type="text" id="att_date" value="<?php echo $shift_data['0']['working_day_time_procedures']['on_dutty1']; ?>" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-primary" onclick="oin_punch_form();">Add</button>
                    </div>
                </div>
                    
                    
                    <div class="col-md-12">
                        <div id="container_data">
                            
                        </div>
                    </div>
                </div>
            </form>

    </div>

</div>
</div>
<script>
    function loadform() {
        $('#form2').load(livesite + 'SiteAttendance/form2');
    }
    
    function deletetableold(s){
        $(s).parent().parent().css("display","none");
        $(s).parent().parent().find('.statuses').val('0');
    }
    
    function updatephonenumer(s){
        
        var selected = $(s).find('option:selected');
        var mobile_number = selected.data('foo');
        
        $('#customer_contact_no').val(mobile_number);
    }
    
    
    function oin_punch_form(){
        var site = $('#filterby_branch').val();
        var emp_fkey = $('#desig').val();
        if(emp_fkey){
        //alert(emp_fkey);
        var site_attendance_pkey = '';
        var site_fkey = $('#filterby_branch').val();
        var day_time_seq_fkey = $('#filterby_shift').val();
        var designation_id = $('#designation_code').val();
        var out_time = $('#att_date').val();
//        alert(out_time);
        var date = new Date();
        var att_date = $('#filterby_date').val();
        var att_date1= $('#checkin_date').val();
        if(att_date1 == '1'){
        var att_datein =  date.setDate(att_date1 + 1)
        }else{
        var att_datein =  $('#filterby_date').val();   
        }
        var checkin_time= $('#checkin_time').val();
      
        $.ajax({
                    url: livesite + 'SiteAttendance/mark_attendance/' + emp_fkey,
                    method:'POST',
                    data:{site:site,site_attendance_pkey:site_attendance_pkey,site_fkey:site_fkey,day_time_seq_fkey:day_time_seq_fkey,
                        designation_id:designation_id,out_time:out_time,att_date:att_date,in_date:att_datein,checkin_time:checkin_time},
                    success: function (resp) {
                        var response = JSON.parse(resp);
                        alert(response.message);
                        if(response.success == 0){
                            
                        }else{
//                            $(s).html('IN').addClass('btn-success').removeClass('btn-warning');
                            load_data();
                            $('#largeModalForm').modal('hide');
                        }
                        
                    }
                });
            }
            else{
                alert("You are not selected any Employee!!!");
            }
    }
    
//    function load_data(s){
//        var att_date = $('#att_date').val();
//        var desig = $('#desig').val();
//        var site = $('#filterby_branch').val();
//        var site_fkey = $('#filterby_branch').val();
//        var day_time_seq_fkey = $('#filterby_shift').val();
//        var designation_id = $('#desig').val(); 
//        $('#container_data').load(livesite+'SiteAttendance/data_site',{att_date:att_date,desig:desig,site:site,day_time_seq_fkey:day_time_seq_fkey}); 
//    }
    $(document).ready(function () {
    $('#desig').select2();    
    $('#att_date').timepicker({
//        minuteStep: 1,
//        showInputs: true,
//        disableFocus: true
                format: 'hh:mm:ss',
                showMeridian: false,
                autoclose: true
    });       
     
        

    var usershierarchyoptions = {
        url: function (phrase) {   
            var emp = $('#empsetuppersonal #emp_pkey').val();
            return livesite+"Employee/getautohierarchycompletionsvgfs?username=" +phrase + "&emp="+emp;;
        },
        getValue: "emp_name",
        list: {
            onClickEvent: function () {
                var selectedItem = $('#hierarch').getSelectedItemData();
                var site_pkey = selectedItem.emp_pkey;
                $('#hierarch1').val(site_pkey);
            },
            onKeyEnterEvent: function () {
//                filterAttendanceautocomplete($('#hid_filterby_employees').val());
            },
            onSelectItemEvent: function () {
//                var selectedItem = $('#hierarch').getSelectedItemData();
//                var site_pkey = selectedItem.emp_pkey;
//                $('#hierarch1').val(site_pkey);
            }
        }
    };

    $('#hierarch').easyAutocomplete(usershierarchyoptions);


    $('#hierarch').on('keydown',function(){
          $('#hierarch1').val('');
    });

        $('#start_date').datepicker({
            dateFormat: 'yyyy-mm-dd'
        });
        $('#end_date').datepicker({
            dateFormat: 'yyyy-mm-dd'
        });
        $('#manu_date').datepicker({
            dateFormat: 'yyyy-mm-dd'
        });
    });
</script>