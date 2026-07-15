<style>
    .form-horizontal .control-label {
        text-align: left;
    }

    /* edited by athira on 16-01-2025 */
    .datagrid-btable tr td:last-child {
        text-align: center;
    }

    /* end */

    /* Edited by bindu 24-10-2025 */
    .heading {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }

    .home {
        background-color: #ffffffff;
        border-radius: 50px;
        padding: 2px 15px;
        color: #1e516e !important;
        /* margin-right: 15px; */
        color: white;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        border: #1e516e 1px solid;
    }

    /* End */
</style>
<script src="<?php echo $this->webroot; ?>plugins/ckeditor4/ckeditor4/ckeditor.js" type="text/javascript"></script>
<!--<script src="<?php echo $this->webroot; ?>plugins/ckeditor1/ckeditor.js" type="text/javascript"></script>-->
<section class="content-header heading">
    <!-- /* edited by bindu 24-10-25 */ -->
    <h1 class="text-primary-18">Document Generator </h1>
    <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
        <i class="fa" style="font-size:16px;">&#xf104;</i>
        Back
    </div>
    <!-- end -->

</section>


<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box ">
                <br>
                <div class="box-body">
                    <div class="box-body">
                        <table id="documents_table" class="table table-bordered table-hover">
                            <tbody>
                            </tbody>
                        </table>
                    </div><!-- /.box-body -->
                </div>

            </div>
        </div>
    </div>
</section>

<script>
    var placeholders = [];
    CK_TEMPLATE = '';

    function validate() {
        var valid = true;
        var templateObj = $('#document_form #template');
        var employeeObj = $('#document_form #employee');

        if (templateObj.val() == "") {
            templateObj.parents('div .col-sm-12').addClass('has-error')
            templateObj.parents('div .col-sm-10').append('<span class="help-block">Please choose a template.</span>');
            valid = false;
        }

        if (employeeObj.val() == "") {
            employeeObj.parents('div .col-sm-12').addClass('has-error')
            employeeObj.parents('div .col-sm-10').append('<span class="help-block">Please choose a employee.</span>');
            valid = false;
        }
        return valid;

    }

    function validateDocumentForm() {


        var valid = validate();
        var document_nameObj = $('#document_form #document_name');
        if (document_nameObj.val() == "") {
            document_nameObj.parents('div .col-sm-12').addClass('has-error')
            document_nameObj.parents('div .col-sm-10').append('<span class="help-block">Please enter the document name.</span>');
            valid = false;
        }
        return valid;
    }
    temp = '';

    function documentPreview(obj) {
        if (obj.value == "")
            return false;
        if ($('#document_form #template').val() != '') {
            var emp_id = $('#document_form #employee').val() != '' ? $('#document_form #employee').val() : '';
            // edited by bindu 25-10-25
            var emp_join_id = $('#document_form #employee_join').val() != '' ? $('#document_form #employee_join').val() : '';
            var temp_id = $('#document_form #template').val() != '' ? $('#document_form #template').val() : '';
            var company_id = $('#document_form #company').val() != '' ? $('#document_form #company').val() : '';
            var branch_id = $('#document_form #branches').val() != '' ? $('#document_form #branches').val() : '';
            var supplier_id = $('#document_form #suppliers').val() != '' ? $('#document_form #suppliers').val() : '';
            var customer_id = $('#document_form #customers').val() != '' ? $('#document_form #customers').val() : '';
            var others_id = $('#document_form #others').val() != '' ? $('#document_form #others').val() : '';

            $.ajax({
                url: livesite + "DocumentManagers/docPreview/0/3",
                type: 'POST',
                data: {
                    emp_id: emp_id,
                    // edited by bindu 25-10-25
                    emp_join_id: emp_join_id,
                    temp_id: temp_id,
                    company_id: company_id,
                    branch_id: branch_id,
                    supplier_id: supplier_id,
                    customer_id: customer_id,
                    others_id: others_id,
                    placeholders: placeholders,
                    template: CK_TEMPLATE //CKEDITOR.instances['document_editor'].getData()
                },
                success: function (response) {
                    var res = JSON.parse(response);
                    if (res && res.status == true) {
                        console.log('response');
                        CKEDITOR.instances['document_editor'].setData(res.html);
                    }
                }
            });
        }
    }

    function loadPlaceholders() {
        var templateObj = $('#document_form #template');
        if (templateObj.val() != '') {
            $.ajax({
                url: livesite + 'DocumentManagers/getTemplatePlaceholers/' + templateObj.val(),
                type: 'POST',
                success: function (resp) {
                    var result = $.parseJSON(resp);
                    placeholders = result.placeholer;
                    var data = result.data;
                    var template = (result.template).replace(/\{:text_box}/g, ' <input type="text" />');
                    CK_TEMPLATE = template;
                    CKEDITOR.instances['document_editor'].setData(template);
                    jQuery('.placeholdeDropDown').addClass('hide');
                    for (i in placeholders) {
                        switch (placeholders[i]) {
                            case 'emp':
                                var employees = data[placeholders[i]];
                                var employeeHtml = '<option   value="">Choose Employee</option>';
                                for (i in employees) {
                                    employeeHtml += "<option value='" + employees[i].emp_pkey + "'>" + employees[i].emp_name + "</option>";
                                }
                                $('#document_form #employee').html(employeeHtml);
                                $('#emp').removeClass('hide');
                                break;
                            // edited by bindu 25-10-25
                            case 'emp_join':
                                var employees = data[placeholders[i]];
                                console.log(data, 'data');
                                var employeeHtml = '<option   value="">Choose Employee</option>';
                                for (i in employees) {
                                    employeeHtml += "<option value='" + employees[i].emp_join_pkey + "'>" + employees[i].emp_name + "</option>";
                                }
                                $('#document_form #employee_join').html(employeeHtml);
                                $('#emp_join').removeClass('hide');
                                break;
                            case 'branch':
                                var branches = data[placeholders[i]];
                                var branchesHtml = '<option   value="">Choose Branch</option>';
                                for (i in branches) {
                                    branchesHtml += "<option value='" + branches[i].id + "'>" + branches[i].branch_name + "</option>";
                                }
                                $('#document_form #branches').html(branchesHtml);
                                $('#branch').removeClass('hide');
                                break;
                            case 'comp_contact':
                                var companies = data[placeholders[i]];
                                var companiesHtml = '<option   value="">Choose Company</option>';
                                for (i in companies) {
                                    companiesHtml += "<option value='" + companies[i].id + "'>" + companies[i].business_name + "</option>";
                                }
                                $('#document_form #company').html(companiesHtml);
                                $('#comp_contact').removeClass('hide');
                                break;
                            case 'supplier':
                                var suppliers = data[placeholders[i]];
                                var suppliersHtml = '<option   value="">Choose Supplier</option>';
                                for (i in suppliers) {
                                    suppliersHtml += "<option value='" + suppliers[i].contact_id + "'>" + suppliers[i].name + "</option>";
                                }
                                $('#document_form #suppliers').html(suppliersHtml);
                                $('#supplier').removeClass('hide');
                                break;
                            case 'customer':
                                var customers = data[placeholders[i]];
                                var customersHtml = '<option   value="">Choose Customer</option>';
                                for (i in customers) {
                                    customersHtml += "<option value='" + customers[i].contact_id + "'>" + customers[i].name + "</option>";
                                }
                                $('#document_form #customers').html(customersHtml);
                                $('#customer').removeClass('hide');
                                break;
                            case 'other':
                                var others = data[placeholders[i]];
                                var othersHtml = '<option   value="">Choose Other Contact</option>';
                                for (i in others) {
                                    othersHtml += "<option value='" + others[i].contact_id + "'>" + others[i].name + "</option>";
                                }
                                $('#document_form #others').html(othersHtml);
                                $('#other').removeClass('hide');
                                break;



                        }
                    }
                }
            });
        }
    }



    jQuery(document).ready(function () {

        $('#documents_table').datagrid({
            url: livesite + "DocumentManagers/getDocuments",
            pagination: true,
            singleSelect: true,
            rownumbers: true,
            PostsearchFilter: true,
            toolbar: [{
                text: 'New',
                iconCls: 'icon-add',
                handler: function () {
                    showLargeModalForm(livesite + 'DocumentManagers/documentForm')

                }
            },

            {
                iconCls: 'icon-remove',
                text: 'Delete',
                handler: function () {
                    var rows = $('#documents_table').datagrid('getSelected');
                    if (rows) {
                        var document_id = rows.document_pkey;
                        console.log(document_id);
                        if (confirm("Are you sure want to delete ")) {
                            $.ajax({
                                url: livesite + "DocumentManagers/deleteDocument",
                                data: {
                                    ids: document_id
                                },
                                success: function (response) {
                                    var response = $.parseJSON(response);
                                    if (response.msg) {
                                        $.notify(response.msg, {
                                            type: 'success',
                                            allow_dismiss: true
                                        });
                                    }
                                    reloadTable('documents_table')
                                }
                            });
                        }
                    } else {
                        alert("Please select atleast one record to delete")
                    }
                }
            }

            ],
            fitColumns: true,
            pageList: [2, 5, 10, 50, 100],
            columns: [
                [

                    {
                        field: 'document_name',
                        title: 'Document Heading',
                        width: "20%"
                    },
                    {
                        field: 'template_name',
                        title: 'Document Name',
                        width: "18%"
                    },
                    {
                        field: 'doc_id',
                        title: 'Document ID',
                        width: "10%"
                    },
                    {
                        field: 'emp_name',
                        title: 'Employee Name',
                        width: "16%"
                    },
                    {
                        field: 'created_by',
                        title: 'Created By',
                        width: "10%"
                    },
                    //edited by athira on 15-01-2024
                    {
                        field: 'creation_date',
                        title: 'Created Date',
                        width: "14%"
                    },
                    {
                        field: 'download_link',
                        title: 'Download',
                        width: "11%"
                    }
                    //end

                ]
            ],
            onSearch: function (s) {
                $('#documents_table').datagrid('load', {
                    emp: $('#searchqupo').val()
                });
            }
        });
    });


    /* edited by bindu 20-02-26 */
    $(".home").on("click", function () {

        $("#container").isLoading({
            text: "Loading",
            position: "overlay",
        });

        let url = "";
        var userGroup = <?php echo json_encode($userGroup); ?>

        if (userGroup == "1") {
            url = livesite + "EmployeeManage/index";
        }
        else if (userGroup == "2") {
            url = livesite + "EmployeeMenu/addon";
        }

        $("#container").load(url, function () {
            isDashboardShown = false;
        });

    });

    /* edited by bindu 20-02-26 */
</script>