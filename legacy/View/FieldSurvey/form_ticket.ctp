<script>
    $.validate({
        form: '#form-user-master'
    });
    var options = {
        success: function (resp) {
            $('#modalForm').modal('hide');
            $('#att_table').datagrid('reload');
            $('#attdatacsv').val('');
            $("#filterby_branch").select2("val", "");
            $("#emp_fkey").select2("val", "");

            $.notify($.parseJSON(resp).msg, {
                type: 'success',
                allow_dismiss: false
            });
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
            <h4 class="modal-title"> Equipments Master</h4>  
        </div>
        <div class="modal-body">
            <!-- Form starts -->

            <form class="form-horizontal" id="form-user-master" action="<?php echo $this->webroot; ?>FieldSurvey/save" method="POST">

                <div class="modal-body">

                    
                    <div class="form-group">
                        <div class="col-sm-12">
                            <label class="col-sm-5 control-label" for="filterby_branch">Choose Site</label>
                            <div class="col-md-12">
                                <select id="filterby_branches" style="width: 100%; " name="filterby_branch" class="form-control js-example-basic-single" onchange="filter_datas(this);" >
                                    <?php foreach ($all_item as $key => $value) { ?>                              
                                        <option <?php if(isset($arr_types[0]['efsr_tickets']['site_fkey']) && $arr_types[0]['efsr_tickets']['site_fkey'] == $value['SiteWork']['efsr_site_pkey']){ echo 'selected="selected"'; } ?> value="<?php echo $value['SiteWork']['efsr_site_pkey']; ?>"><?php echo $value['SiteWork']['site_name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-12">
                            <label class="col-sm-5 control-label" for="filterby_branch">Survey Type</label>
                            <div class="col-md-12">
                                <select id="survey_type_fkey" style="width: 100%; " name="survey_type_fkey" class="form-control js-example-basic-single" onchange="filter_datas(this);" >
                                    <?php if(isset($arr_types[0])){ ?>
                                        <option value="<?php echo $arr_types[0]['efsr_tickets']['survey_type_fkey']; ?>"><?php echo $arr_types[0]['survey_type']['type_name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="efsr_tickets_pkey" value="<?php echo isset($arr_types[0]['efsr_tickets']['efsr_tickets_pkey'])?$arr_types[0]['efsr_tickets']['efsr_tickets_pkey']:''; ?>" id="efsr_tickets">
                    <div class="form-group">
                        <div class="col-sm-12">
                            <label class="col-sm-5 control-label" for="filterby_branch">Equipment</label>
                            <div class="col-md-12">
                                <select id="equipment" style="width: 100%; " name="filterby_Equipment" class="form-control js-example-basic-single" onchange="filter_datas(this);" >
                                    <?php if(isset($arr_types[0])){ ?>
                                        <option value="<?php echo $arr_types[0]['efsr_tickets']['efsr_equipments_master_fkey']; ?>"><?php echo $arr_types[0]['0']['equipments_name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-12">
                            <label class="col-sm-5 control-label" for="filterby_branch">Technician</label>
                            <div class="col-md-12">
                                <select id="emp_fkeys" style="width: 100%; " name="emp_fkey" class="emp_fkey form-control js-example-basic-single" onchange="filterAttendanceupload(this);" >
                                    <?php if(isset($arr_types[0])){ ?>
                                        <option value="<?php echo $arr_types[0]['emp_details']['first_name']; ?>"><?php echo $arr_types[0]['emp_details']['first_name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-12">
                            <label class="col-sm-11 control-label" for="filterby_branch">Approved by</label>
                            <div class="col-md-12">
                                <select id="approved_by" style="width: 100%; " name="app_fkey" class="emp_fkey form-control js-example-basic-single" onchange="filterAttendanceupload(this);" >
                                    <?php if(isset($arr_types[0])){ ?>
                                        <option value="<?php echo $arr_types[0]['emp_approved']['approved_by']; ?>"><?php echo $arr_types[0]['emp_approved']['approved_by']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    


                    
                        <div class="modal-footer">
                            <input type="hidden" required="required" class="form-control" id="site_fkey" value="" name="site_fkey" >
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
    
    function filter_datas(){
        filterEmployees();
        filterequipments();
        filtersurveyTypes();
        
    }
    
    function filterEmployees(branch)
    {

        var branch = $('#filterby_branches').val();
        //alert(branch);
        $(".emp_fkey").select2(
                {
                    //closeOnSelect:false,
                    placeholder: "All",
                    allowClear: true,
                    ajax: {
                        url: livesite + "FieldSurvey/jsons/" + branch,
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
    }
    
    function filterequipments(branch)
    {

        var branch = $('#survey_type_fkey').val();
        var site = $('#filterby_branches').val();
        //alert(branch);
        $("#equipment").select2(
                {
                    //closeOnSelect:false,
                    placeholder: "Find a Equipment ",
                    allowClear: true,
                    ajax: {
                        url: livesite + "FieldSurvey/jsons_equipments/" + site + '/' +branch,
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
    }
    function filtersurveyTypes(branch)
    {

        var branch = $('#equipment').val();
        //alert(branch);
        $("#survey_type_fkey").select2(
                {
                    //closeOnSelect:false,
                    placeholder: "All",
                    allowClear: true,
                    ajax: {
                        url: livesite + "FieldSurvey/jsons_Surveys/" + branch,
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
    }
    
//number valiadtion
//    function numbevalidation() {
////        var loan = $('#loan_amount').val(); .
////        var rate = $('#intrest_rate').val();
////        var ten = $('#tenure').val();
//            
//        
//    }

    

//    function loancal() {
//        var amount = $('#loan_amount').val();
//        var month = $('#tenure').val();
//        var result = amount / month;
//        $("#emi_amount").val(result.toFixed(2));
//
//    };
    $(document).ready(function () {

    $(".emp_fkey").select2();
    $("#filterby_branches").select2();
    $("#filterby_month").select2();
    
    $('#site_fkey').val($('#filterby_branch').val());


        // bind to the form's submit event 
        $('#attendanceuploadtable').submit(function () {
            $('#btn-submit').html('<li class="fa fa-spinner fa-spin"></li>Saving').prop("disabled", true);
            $(this).ajaxSubmit(options);
            return false;
        });

    });
</script>