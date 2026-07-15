<style>
    .heading {
        display: flex;
        flex-direction: row;
        align-items: end;
        justify-content: space-between;
        /* margin-left: 20px; */
    }

    .home {
        background-color: #ffffffff;
        border-radius: 50px;
        padding: 2px 15px;
        color: #1e516e !important;
        margin-right: 15px;
        color: white;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        border: #1e516e 1px solid;
    }

</style>
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- DIRECT CHAT DANGER -->
            <div class="box ">
                <div class="box-body">
                    
                </div>
                <div class="box-body">
                    <div class="heading">
                        <h1 class="text-primary-18"> Customer / Vendor List</h1>
                        <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;margin:0 0 10px 0;">
                    <i class="fa" style="font-size:16px;">&#xf104;</i>
                    Back
                </div>
                    </div>
                    
                    
                    <div class="box box-body">
                        <form class="form-horizontal" method="post" action="" id="importemployeectcform" name="importemployeectcform">


                            <div class="form-group" style="margin-top: 10px;"> 
<!--                                <div class="col-md-2">
                                 <h3 class="box-title pull-left"> Upload Customer / Vendor </h3>
                                </div>-->
                                <div class="col-md-4">
                                    <label class=" control-label pull-left" for="empctccsv">Choose file</label>
                                    <div class="col-md-7" style="margin-top: 6px;">
                                        <!--                                    <button type="button" id="btn-uploademployeectc" class="btn btn-primary" onclick="uploadEmployeeCTC();">Upload Gross Salary</button>-->
                                        <input id="empctccsv" name="empctccsv" type="file">

                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <button type="button" id="btn-uploademployeectc" class="btn btn-success col-md-5" onclick="uploadEmployeeCTC();">Upload Details</button>
                                    <div class="col-md-7">
                                        <button type="button" id="btn-downloademployeectcform" class="btn btn-danger col-md-12 " onclick="downloadEmployeeCTCForm();">Download Format</button>
                                    </div>
                                </div>
                                 <div class="col-md-4" style="">
                                <label class="col-md-5 control-label" for="relationship">Filter Relationship</label>
                                <div class="col-md-7" style="margin-top: 6px;">
                                    <select id="relationship" name="relationship" class="form-control" onchange="filterRelationship(this);">
                                        <option value="">--All--</option>
                                        <option value="Customer">Customer</option>
                                        <option value="Vendor">Vendor</option>
                                        <option value="Others">Others</option>
                                    </select>
                                </div>
                            </div>
                            </div>
                        </form>
                    </div>
                      <hr style="border-top: 1px solid lightgray;">
                      
                      
                    <div >
                        <table id="contacttable">


                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    
    function downloadEmployeeCTCForm() {
        var ctcuploadtype = $('#ctc_upload_type').val();
        window.open('<?php echo $this->webroot; ?>Vendor/downloadempctcformat/' , '_blank');
    }
    function filterRelationship(obj) {
        var relationship = $('#relationship').val();
       // find_branchemployees();
        $('#contacttable').datagrid('load', {
            emp: $('#searchqupo').val(),
            relationship: $('#relationship').val()
        });
        
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
        xhr.open('POST', livesite + 'Vendor/uploadandsaveempctc/' , true);

        // Set up a handler for when the request finishes.
        xhr.onload = function () {
            if (xhr.status === 200) {
                // File(s) uploaded.
//				$('#empctccsv').val("");
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
                alert("Contacts import failed, please check informations given or try again.");
            }
        };
        //$('#ctc_upload_type').val('');
//        $('#empctccsv').val('');
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
    $(document).ready(function () {

        $('#contacttable').datagrid({
            url: livesite + 'Contacts/listcontacts',
            rownumbers: true,
            title: "Customer/Vendor List",
            fitColumns: true,
            singleSelect: true,
            autoRowHeight: false,
            PostsearchFilter:true,
            pagination: true,
            pageSize: 10,
            toolbar: [{
                    iconCls: 'icon-add',
                    text: "New",
                    handler: function () {
                        //location.href='contacts/addeditcontacts';
                        var url = livesite + 'contacts/addeditcontacts';
                        showModalForm(url);
                        //$('#modalDiv').load(url,function(){
                        //    $('#modalDiv').modal('show');
                        //});
                    }
                }, '-', {
                    iconCls: 'icon-edit',
                    text: "Edit",
                    handler: function () {
                        var row = $('#contacttable').datagrid('getSelected');
                        if (row) {
                            var contact_id = row.contact_id;
                            //location.href='contacts/addeditcontacts/'+contact_id;
                            var url = livesite + 'contacts/addeditcontacts/' + contact_id;
                            showModalForm(url);
                            //$('#modalDiv').load(url,function(){
                            //    $('#modalDiv').modal('show');
                            //});
                        }
                        else{
                            //edited by megha 24/04/2019
                            alert("please select any row to edit details");
                            //edited by megha 24/04/2019
                          }
                    }
                }, '-', {
                    iconCls: 'icon-remove',
                    text: 'Remove',
                    handler: function () {
                        var row = $('#contacttable').datagrid('getSelected');
                        if (row) {
                            var r = confirm("Do You Want  To Remove The Selected Item")
                            if (r == true) {

                                var contact_id = row.contact_id;
                                $.ajax({
                                    url: livesite + 'contacts/deletecontacts/' + contact_id,
                                    success: function (resp) {
                                        $('#contacttable').datagrid('reload');
                                        $.notify($.parseJSON(resp).msg, {
                                            type: 'success',
                                            allow_dismiss: false
                                        });
                                    }
                                });
                            }
                            else
                            {
                                alert("canceled");
                            }
                        }
                        else{
                            //edited by megha 24/04/2019
                            alert("please select any row to delete details");
                            //edited by megha 24/04/2019
                        }
                    }
                }

            ],
            columns: [[
                    {field: 'company_name', title: 'Company Name', width: "20%", sortable: true, order: 'asc'},
                    {field: 'email', title: 'Email', width: "20%", sortable: true, order: 'asc'},
                    {field: 'phone', title: 'Phone Number', width: "10%", sortable: true, order: 'asc'},
                    {field: 'first_name', title: 'Contact Name', width: "20%", sortable: true, order: 'asc'},
                    {field: 'city', title: 'City', width: "15%", sortable: true, order: 'asc'},
                    {field: 'relationship', title: 'Relationship', width: "15%", sortable: true, order: 'asc'},
                ]],
            onSearch:function(s){
                                    
                                    $('#contacttable').datagrid('load',{
                                            emp: $('#searchqupo').val(),
                                            relationship: $('#relationship').val()
                                    });
                                },
        });
    });

 

    $(".home").on("click", function () {

    $("#container").isLoading({
        text: "Loading",
        position: "overlay",
    });

    let url = "";
    var userGroup = <?php echo json_encode($this->Session->read('user_group')); ?>;

    if (userGroup == "1") {
        url = livesite + "Stockmanagement/index";
    } 
    else if (userGroup == "2") {
        url = livesite + "EmployeeMenu/addon";
    }

    $("#container").load(url, function () {
        isDashboardShown = false;
    });

});

</script>