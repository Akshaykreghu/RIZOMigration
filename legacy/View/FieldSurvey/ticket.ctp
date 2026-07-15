<style>
    .form-horizontal .control-label{

        text-align: left;

    }
</style>

<section class="content-header">
    <h1 style="text-align:left; font-size: 3em;"> Ticket   </h1>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <form class="form-horizontal" method="post" action="" id="importemployeectcform" name="importemployeectcform">


                                <div class="form-group">
                                    <div class="col-sm-6">
                                        <label class="col-sm-3 control-label" for="filterby_branch">Survey Type</label>
                                        <div class="col-md-9" style="padding-top: 4px;">
                                            <select id="equipments_type" style="width: 100%; " name="equipments_type" class="form-control js-example-basic-single" onchange="filter_data(this);" >
                                                <?php foreach ($arr_types as $value) {
                                                    ?>
                                                    <option value="<?php echo $value['survey_type']['type_pkey']; ?>"><?php echo $value['survey_type']['type_name']; ?></option>
                                                <?php }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="col-md-4 control-label" for="empctccsv">Choose file</label>
                                        <div class="col-md-7" style="padding-top: 6px;">
                                            <!--<button type="button" id="btn-uploademployeectc" class="btn btn-primary" onclick="uploadEmployeeCTC();">Upload Gross Salary</button>-->
                                            <input id="empctccsv" name="empctccsv" type="file">

                                        </div>
                                    </div>
                                    <div class="col-md-4" style="text-align: right;">
                                        <!--<div class="col-md-6">-->
                                        <button type="button" id="btn-uploademployeectc" class="btn btn-success " onclick="uploadEmployeeCTC();">Upload Equipment </button>
                                        <!--</div>-->
                                        <!--<div class="col-md-6">-->
                                            <button type="button" id="btn-downloademployeectcform" class="btn btn-danger " onclick="downloadEmployeeCTCForm();">Download Format</button>
                                        <!--</div>-->
                                    </div>
                                </div>
                            </form>
            <div class="box ">
                <br>
                <div class="box-body">
                    <!-- Employee import form -->
                    <form class="form-horizontal"  method="post" action="<?php echo $this->webroot; ?>FieldSurvey/save" id="attendanceuploadtable" name="attendanceuploadtable">
                        <div class="row">
                            <div class="form-group">
                                <div class="col-md-3">
                                    <div class="col-sm-12">
                                        <label class="col-sm-5 control-label" for="filterby_branch">Choose Site</label>
                                        <div class="col-md-12">
                                            <select id="filterby_branch" style="width: 100%; " name="filterby_branch" class="form-control js-example-basic-single" onchange="filter_data(this);" >
<!--                                                <option>All</option>-->
                                                <?php foreach ($all_item as $key => $value) { ?>                              
                                                    <option  value="<?php echo $value['SiteWork']['efsr_site_pkey']; ?>"><?php echo $value['SiteWork']['site_name']; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="col-sm-12">
                                        <label class="col-sm-5 control-label" for="filterby_branch">Equipment</label>
                                        <div class="col-md-12">
                                            <select id="equipments" style="width: 100%; " name="filterby_Equipment" class="form-control js-example-basic-single" onchange="filter_data(this);" >
                                                
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="col-sm-12">
                                        <label class="col-sm-5 control-label" for="filterby_branch">Technician</label>
                                        <div class="col-md-12">
                                            <select id="emp_fkey" style="width: 100%; " name="emp_fkey" class="emp_fkey form-control js-example-basic-single" onchange="filter_data(this);" >
                                                
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="col-sm-12">
                                        <label class="col-sm-11 control-label" for="filterby_branch">Approved by</label>
                                        <div class="col-md-12">
                                            <select id="Approved_fkey" style="width: 100%; " name="app_fkey" class="emp_fkey form-control js-example-basic-single" onchange="filter_data(this);" >
                                                
                                            </select>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div><!--
                        -->       
                    </form>           
                    <!--                    <div class="col-md-12">
                                            <div class="box box-body">
                                                <form class="form-horizontal" method="post" action="" id="importemployeectcform" name="importemployeectcform">
                    
                    
                                                    <div class="form-group">  
                                                        <div class="col-md-4">
                                                            <label class="col-md-5 control-label" for="empctccsv">Choose file</label>
                                                            <div class="col-md-7">
                                                                <button type="button" id="btn-uploademployeectc" class="btn btn-primary" onclick="uploadEmployeeCTC();">Upload Gross Salary</button>
                                                                <input id="empctccsv" name="empctccsv" type="file">
                    
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <button type="button" id="btn-uploademployeectc" class="btn btn-success col-md-5" onclick="uploadEmployeeCTC();">Upload Site Details</button>
                                                            <div class="col-md-7">
                                                                <button type="button" id="btn-downloademployeectcform" class="btn btn-danger col-md-12 " onclick="downloadEmployeeCTCForm();">Download Format</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>-->
                    
                </div>
                <div class="box-body">
                        <table id="att_table" class="table table-bordered table-hover">
                            <tbody>
                            </tbody>
                        </table>
                    </div><!-- /.box-body -->
            </div>
        </div>
    </div>
</section>

<script>
    function downloadEmployeeCTCForm() {
        var ctcuploadtype = $('#ctc_upload_type').val();
//focus on text field


        //alert(branch);
        //alert(employee);


        window.open('<?php echo $this->webroot; ?>FieldSurvey/downloadempticketformat/', '_blank');







    }
    function uploadEmployeeCTC() {


        var form = $('#importemployeectcform');
        var fileSelect = document.getElementById('empctccsv');
//        var ctcuploadtype = $('#ctc_upload_type').val();
        var files = fileSelect.files;

        if (files.length == 0) {
            $.notify("please choose any file to upload!", {
                type: 'danger',
                allow_dismiss: false
            });
            return false;
        }

        // The rest of the code will go here...
        var files = fileSelect.files;
        // Create a new FormData object.
        var formData = new FormData();
        // Loop through each of the selected files.
//alert(formData);
        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            // Add the file to the request.
            //alert(file);
            formData.append('empctc[]', file, file.name);
//          /  alert(formData);
        }
        // alert()

        // Set up the request.
        var xhr = new XMLHttpRequest();

        // Open the connection.
        var filterby_branch = $('#filterby_branch').val();
        var ctctuploadtype = $('#equipments_type').val();
        xhr.open('POST', livesite + 'FieldSurvey/uploadandsaveempctc/' + ctctuploadtype + '/' + filterby_branch, true);

        // Set up a handler for when the request finishes.
        xhr.onload = function () {
            if (xhr.status === 200) {
                // File(s) uploaded.
                $('#empctccsv').val("");
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    $('#contacttable').datagrid('reload');
                    $.notify(response.msg, {
                        type: 'success',
                        allow_dismiss: false
                    });
                } else {
                    $('#contacttable').datagrid('reload');
                    $.notify(response.msg, {
                        type: 'error',
                        allow_dismiss: false
                    });
                }
            } else {
                alert("Equipments import failed, please check informations given or try again.");
            }
        };
        //$('#ctc_upload_type').val('');
        $('#empctccsv').val('');
//        $("#filterby_branch").select2("val", "");
//        $("#emp_fkey").select2("val", "");

        //$("#importemployeectcform").resetForm();


        //document.getElementById("importemployeectcform").reset();

        // $('#importemployeectcform').reset();

//$('form[name=myform]').get(0).reset();
        //  $('importemployeectcform').get(0).reset();
        // $('importemployeectcform').clearForm()
        // Send the Data.
        xhr.send(formData);
    }
    
    function filter_data(){
        filterEmployees();
        filterequipments();
        
        var branch = $('#filterby_branch').val();
        var equipments = $('#equipments').val();
        var emp_fkey = $('#emp_fkey').val();
        var Approved_fkey = $('#Approved_fkey').val();
        
        $('#att_table').datagrid('load', {
            branch: branch,
            equipments: equipments,
            emp_fkey:emp_fkey,
            Approved_fkey:Approved_fkey
        });
    }
    //filtter using branch 
    function filterEmployees(branch)
    {

        var branch = $('#filterby_branch').val();
        //alert(branch);
        $(".emp_fkey").select2(
                {
                    //closeOnSelect:false,
                    //placeholder: "All",
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

        var branch = $('#filterby_branch').val();
        var ctctuploadtype = $('#equipments_type').val();
        //alert(branch);
        $("#equipments").select2(
                {
                    //closeOnSelect:false,
                    //placeholder: "All",
                    allowClear: true,
                    ajax: {
                        url: livesite + "FieldSurvey/jsons_equipments/" + branch +"/"+ ctctuploadtype,
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




    function filterAttendanceupload(obj) {
        var branch = $('#filterby_branch').val();
//        var employee = $('#importemployeectcform #emp_fkey').val()

//        filterEmployees();

    }




    jQuery(document).ready(function () {


        $('#att_table').datagrid({
            url: livesite + "FieldSurvey/lists_site",
            pagination: true,
            singleSelect: true,
            rownumbers: true,
            queryParams: {
                employee: employee
            },
            toolbar: [{
                    text: 'New',
                    iconCls: 'icon-add',
                    handler: function () {
                        showModalForm(livesite + 'FieldSurvey/form_ticket')
                    }
                },
                {
                    iconCls: 'icon-edit',
                    text: 'Edit',
                    handler: function () {
                        var row = $('#att_table').datagrid('getSelected');
                        if (row) {

                            showModalForm(livesite + 'FieldSurvey/form_ticket/' + row.efsr_tickets_pkey)
                        } else
                            $.notify('Please Select A record to Edit ', {
                                type: 'danger',
                                allow_dismiss: false
                            });
                    }
                }

            ],
            fitColumns: true,
            pageList: [2, 5, 10, 50, 100],
            columns: [[
                    {field: 'ticket_no', title: 'Ticket No', width: "10%"},
                    {field: 'survey_type', title: 'Survey Type', width: "10%"},
                    {field: 'first_name', title: 'Technician Name', width: "25%"},
                    {field: 'approved_by', title: 'Approved by', width: "20%"},
                    {field: 'equipments_name', title: 'Equipment', width: "25%"},
                    {field: 'site_name', title: 'Site Name', width: "10%"}
                    //  {field: 'is_credited', title: 'Credited Rate', width: '10%'},
                ]]
        });
        
        
        filterEmployees();
        $("#filterby_branch").select2();
        $("#filterby_month").select2();

        var employee = $('#attendanceuploadfilter #emp_fkey').val();
        
//        $("#equipments_type").select2(
//                {
//                    //closeOnSelect:false,
//                    placeholder: "All",
//                    allowClear: true,
//                    ajax: {
//                        url: livesite + "FieldSurvey/jsons_equipments/" ,
//                        dataType: 'json',
//                        delay: 250,
//                        data: function (params) {
//                            return {
//                                q: params.term, // search term
//                                page: params.page
//                            };
//                        },
//                        processResults: function (data, params) {
//                            // parse the results into the format expected by Select2
//                            // since we are using custom formatting functions we do not need to
//                            // alter the remote JSON data, except to indicate that infinite
//                            // scrolling can be used
//                            params.page = params.page || 1;
//
//                            return {
//                                results: data.items,
//                                pagination: {
//                                    more: (params.page * 30) < data.total_count
//                                }
//                            };
//                        }
//                    },
//                    escapeMarkup: function (markup) {
//                        return markup;
//                    }
//                });
                

    });
</script>
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
    $('#attendanceuploadtable').on('submit', function (event) {

        event.preventDefault();
        if (confirm("Do You Want To Save The Form")) {
            $('#attendanceuploadtable').ajaxSubmit(options);
        }
    });
</script>