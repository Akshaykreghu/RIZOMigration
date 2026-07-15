<style>
    /* <!-- edited by bindu 29-11-2025 --> */
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
        /* margin-right: 15px; */
        color: white;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        border: #1e516e 1px solid;
    
    }

</style>
<section class="content-header heading">
     <!-- edited by athira on 03-07-2025 -->
    <h1 class="text-primary-18">Salary & Leave Heads</h1>
 <?php if ($plan !== 'basic') : ?>
    
    <div class="text-primary-16 home"
         style="display:flex; align-items:center; gap:10px; cursor:pointer;">
        <i class="fa" style="font-size:16px;">&#xf104;</i>
        Back
    </div>

<?php endif; ?>

<!-- <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;margin:0;">
                    <i class="fa" style="font-size:16px;">&#xf104;</i>
                    Back
                </div> -->
</section>
    <!-- end -->
    <hr style="margin:8px 15px -2px 15px;">
    <!-- edited by bindu 29-11-2025 end -->
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-sm-4">
         <ul id="sheads" title="Salary/Leave Heads" lines="true" style="width:100%; min-height:200px; height:auto">

            </ul>

        </div>
        <div class="col-sm-8">

            <ul id="shitems" title="Head Items" lines="true" style="width:100%;min-height:200px;height:auto;max-height:500px;">

            </ul>
        </div>
    </div>        	



</section>
<script>
    $(document).ready(function () {
        $('#sheads').datalist({
            toolbar: [
                //{
                //    text: 'New',
                //    iconCls: 'icon-add',
                //    handler: function () {
                //        showModalForm(livesite + 'SalaryHeads/form')
                //    }
                //},
                {
                    text: 'Edit',
                    iconCls: 'icon-edit',
                    handler: function () {
                        // alert('hlo');
                        var headrow = $('#sheads').datalist('getSelected');
                        if (headrow){
                        showModalForm(livesite + 'SalaryHeads/form?id=' + headrow.key)
                    }else{
                        alert("please select any row");
                    }
                    }
                } 
                //,{
                //    text: 'Delete',
                //    iconCls: 'icon-remove',
                //    handler: function () {
                 //       var headrow = $('#sheads').datalist('getSelected');
                        //alert(headrow.key);
                        //showModalForm(livesite+'SalaryHeads/DeleteHead/'+ headrow.key)
                //        if (headrow){
                //            if (confirm("Are you sure want to delete ")) {
                //        $.ajax({
                //            url: livesite + 'SalaryHeads/DeleteHead/' + headrow.key,
                //            data: {
                //                salary_head: headrow.key
                //           },
                //            success: function (response) {
                //                $.notify($.parseJSON(response).msg, {
                //                    type: 'success',
                //                    allow_dismiss: true,
                //                });
                //            }
                //        });
                //        closeModal('sheads');
                //   }
                //        }else{
                //        alert("please select any row");
                //    }
                //    }
                //}

            ],
            url: livesite + 'SalaryHeads/getSalaryHead',
            rownumbers: true,
            onSelect: function (index, row) {
                var id = row.key;
                //alert(id);
                $('#shitems').datalist('load', {
                    id: id
                });
            },
            line: true
        });


        $('#shitems').datagrid({
            url: livesite + 'SalaryHeads/getSalaryHeadItems',
            checkOnSelect: false,
            //singleSelect: false,
            ctrlSelect:true,
            toolbar: [
                {
                    text: 'New',
                    iconCls: 'icon-add',
                    handler: function () {
                        var rows = $('#shitems').datalist('getChecked');
                        var headrow = $('#sheads').datalist('getSelected');
                        //alert(headrow.key);
                        
                        if (headrow.key != "") {
                            showModalForm(livesite + 'SalaryHeads/form_items/' + headrow.key + '/0/')
                        }
                        
                    }
                },
                {
                    text: 'Edit',
                    iconCls: 'icon-edit',
                    id: "btn",
                    handler: function () {
                        var rows = $('#shitems').datalist('getSelected');
                        var headrow = $('#sheads').datalist('getSelected');
                        //alert(headrow.key);
                        if (headrow){
                        if (headrow.key != "") {
                            var checkfkeys = rows.fkey;
                            if (checkfkeys == 0)
                            {
								
                                showModalForm(livesite + 'SalaryHeads/form_items/' + headrow.key + '/' + rows.key)

                            } else
                            {
                                $('#btn').linkbutton('disable');
                            }
                        }
                        }else{
                        alert("please select any row");
                    }
                    }
                },
                /*{
                    text: 'Save',
                    iconCls: 'icon-save',
                    handler: function () {
                        var rows = $('#shitems').datalist('getChecked');
                        var headrow = $('#sheads').datalist('getSelected');
                        console.log(rows)
                        if (rows) {
                            var str_ids = "";
                            for (var i = 0; i < rows.length; i++) {
                                var data = rows[i];
                                if (str_ids == "") {
                                    str_ids += data.key;
                                } else
                                {
                                    str_ids += "," + data.key;
                                }
                            }

                            $.ajax({
                                url: livesite + "SalaryHeads/saveSalaryHead",
                                data: {
                                    salary_head_item: str_ids,
                                    salary_head: headrow.key
                                },
                                success: function (response) {
                                    $.notify($.parseJSON(response).msg, {
                                        type: 'success',
                                        allow_dismiss: true

                                    });
                                }
                            });

                        }
                    }
                }*/
            ],
            
            onCheck: function (i, rows) {
                var headrow = $('#sheads').datalist('getSelected');
                console.log(rows)
                if (rows) {

                    $.ajax({
                        url: livesite + "SalaryHeads/saveSalaryHead",
                        data: {
                            salary_head_item: rows.key,
                            salary_head: headrow.key
                        },
                        success: function (response) {
                            $.notify($.parseJSON(response).msg, {
                                type: 'success',
                                allow_dismiss: true

                            });
                        }
                    });

                }
            },
            
            onUncheck: function (i, rows) {
                var headrow = $('#sheads').datalist('getSelected');
                console.log(rows)
                if (rows) {

                    $.ajax({
                        url: livesite + "SalaryHeads/removeSalaryHead",
                        data: {
                            salary_head_item: rows.key,
                            salary_head: headrow.key
                        },
                        success: function (response) {
                            $.notify($.parseJSON(response).msg, {
                                type: 'success',
                                allow_dismiss: true

                            });
                        }
                    });

                }
            },
            rownumbers: true,
            onLoadSuccess: function () { //alert('hloooo');
                $('#shitems').datalist('acceptChanges');
            },
            onSelect: function (index, row) {
                var headrow = $('#sheads').datalist('getSelected');
                //alert(headrow.key);
                if (headrow.key != "") {
                    var checkfkeys = row.fkey;
                    if (checkfkeys == 0)
                    {
						$('#btn').linkbutton('enable');
                    } else
                    {
                        $('#btn').linkbutton('disable');
                    }
                }
            },
            fitColumns: true,
            columns: [
                [
                    {checkbox: true, field: 'key'},
                    {field: 'text', title: 'Name', width: "30%"},
                    {field: 'type', title: 'Type', width: "10%"},
//                    {field: 'occurance', title: 'Occurance:', width: "10%"},
//                    {field: 'date', title: 'Date', width: "10%"},
                    {field: 'comments', title: 'Comments', width: "35%"},
                    {field: 'isinslip', title: 'In Salary Slip', width: "12%"},
                    {field: 'part', title: 'Item Part', width: "15%"}
                ]
            ]
        });
    })
    /* edited by bindu 19-02-26 */
   $(".home").on("click", function () {

    $("#container").isLoading({
        text: "Loading",
        position: "overlay",
    });

    let url = "";
    var userGroup = <?php echo json_encode($this->Session->read('user_group')); ?>

    if (userGroup == "1") {
        url = livesite + "CompanySetup/index";
    } 
    else if (userGroup == "2") {
        url = livesite + "EmployeeMenu/addon";
    }

    $("#container").load(url, function () {
        isDashboardShown = false;
    });

});

	/* edited by bindu 19-02-26 */
</script>