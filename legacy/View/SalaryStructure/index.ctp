    <style>
    #shiftpolicygrouptable_wrapper .DTTT.btn-group ,#shiftpolicytable_wrapper .DTTT.btn-group{
        padding-left: 5px;
    }

/* Edited by bindu 24-10-2025 */
  	.heading {
		display: flex;
		flex-direction: row;
		align-items: end;
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
<section class="content-header heading">
      
        <!-- /* edited by bindu 24-10-25 */ -->
    <h1 class="text-primary-18">Salary Structure</h1>
    <?php if ($plan !='basic'){?>
   <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
        <i class="fa" style="font-size:16px;">&#xf104;</i>
        Back
    </div>
    <?php } ?>
</section>
<hr style="margin-top: 8px;margin-bottom: -2px;margin-right: 15px;margin-left: 15px;">
  <!-- end -->
<section class="content">
    <div class="row">
        <div class="col-md-4">
            <ul id="shiftpolicygrouptable" title="Structure Names" lines="true" style="width:100%; min-height:200px; height:auto">
                    </ul>
<!--            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title">Structure Names</h3>
                    <div class="box-tools"></div>
                </div>
                <div class="box-body no-padding">
                    <table id="shiftpolicygrouptable" class="table table-hover table-striped" cellspacing="0" width="100%">

                    </table>
                </div> /.box-body 
            </div>-->

        </div><!-- /.col -->
        <div class="col-md-8">
            <div class="box box-primary" style="padding: 10px;">
                <div class="box-header with-border">
                    <h3 class="box-title">Define components & calculations  </h3>
                    <div class="box-tools pull-right">
                    </div><!-- /.box-tools -->
                </div><!-- /.box-header -->
                <div class="box-body no-padding" id="shiftContainer">
                </div><!-- /.box-body -->

            </div><!-- /. box -->
        </div><!-- /.col -->
    </div><!-- /.row -->
</section><!-- /.content -->

<script>
    jQuery(document).ready(function() {




        $("#shiftContainer").load(livesite + "SalaryStructure/form")



        $('#shiftpolicygrouptable').datagrid({
            url: livesite + "SalaryStructure/liststructures",
            pagination: true,
            rownumbers: true,
            singleSelect: true,
            onSelect: function(index, row) {

                if (row) {
                    /*$("#shiftContainer").load(livesite + 'SalaryStructure/form/' + row.structure_id,function() {
                      displayMonthysalary();
                    });*/
                    $("#shiftContainer").load(livesite + 'SalaryStructure/view/' + row.structure_id);
                }
            },
            toolbar: [{
                    text: 'New',
                    iconCls: 'icon-add',
                    handler: function() {

                        $("#shiftContainer").load(livesite + "SalaryStructure/form")
                    }
                }, '-', {
                    iconCls: 'icon-remove',
                    text: 'Remove',
                    handler: function() {

                        var row = $('#shiftpolicygrouptable').datagrid('getSelected');

                        if (row) {

                            if (confirm("Are you sure want to delete ")) {
                                var str_ids = row.structure_id;

                                $.ajax({
                                    url: livesite + "SalaryStructure/delete",
                                    data: {
                                        ids: str_ids
                                    },
                                    success: function(response) {
                                         var response = $.parseJSON(response);
                                        if (response.success == true) {
                                            $.notify(response.msg, {
                                                type: 'success',
                                                allow_dismiss: true

                                            });
                                        }
                                        else
                                            {
                                                 $.notify(response.msg, {
                                                type: 'danger',
                                                allow_dismiss: true

                                            });
                                            }
                                        reloadTable('shiftpolicygrouptable');
                                    }
                                });

                            }

                        }else{
                            //edited by megha 23_04_19
                            alert("please select any row to delete structure");
                             //edited by megha 23_04_19
                        }

                    }
                }],
            fitColumns: true,
            pageList: [2, 5, 10, 50, 100],
            columns: [[
                    {field: 'structure_name', title: 'Name', width: "45%"},
                    {field: 'structure_eg_amt', title: 'Min Monthly Gross Salary', width: "55%"},
                ]]
        });










    });
    
function showSalaryStructureForm(){
    var structure_id = $('#view_structure_id').val();
    $("#shiftContainer").load(livesite + 'SalaryStructure/view/' + structure_id);    
}

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