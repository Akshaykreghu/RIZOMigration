<style>
    .searchb{
        background-color: #cccccc;
    }
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
<script>
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


<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- DIRECT CHAT DANGER -->
            <div class="box ">

                <div class="box-body">
                    <form class="form-horizontal" id="" action="" method="" >
                        <!-- <legend class="text-primary-18">Create Material Requests</legend> -->
                          <div class="heading">
                    <h1 class="text-primary-18">Create Material Requests</h1>
                    <div class="text-primary-16 home" style="display:flex; align-items:center; gap:10px; cursor:pointer;margin:0 0 10px 0;">
                        <i class="fa" style="font-size:16px;">&#xf104;</i>
                        Back
                    </div>
                    </div>
                        <div class="modal-body">
                            <div class="form-group pull-right" style="margin-right:10px;">
<!--                                <div class="col-md-3">
                                    <label style="text-align:left;" class="col-md-4 control-label">Search</label>
                                    <div class="col-md-8">
                                        <input id="bill" name="bill"   class="form-control">
                                    </div>
                                </div>-->
                                
                                <button type="reset" id="btn-submit" onclick="newmode();" class="btn btn-primary">Create New Material Request <li class="fa fa-hand"></li></button>
                                <button style="display: none; " type="button" id="btn-refresh" value="Refresh" onclick="refresh();" accesskey=""class="btn btn-danger" data-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </form>
                    <div id="newreqeuest">
                        
                    </div>
                    
                    <div style="margin-top: 20px; margin-bottom: 20px; ">
                        <table id="materialtable" >

                        </table>
                    </div>
                </div>                 


                
            </div>
        </div>
        </div>
</section>
<script>
    function newmode()
    {
        if($('#mr_code').length){
            var r = confirm("Do You Want  To Proceed with new ?")
            if (r == true) {
                $('#newreqeuest').html('<div class="col-md-12" style="text-align:center; "><li class="fa fa-spinner fa-spin" style="font-size:30px;text-align: center;"></li></div>');
                $('#newreqeuest').load(livesite+'MaterialRequest/loadnew');
            }
        }else{
            $('#newreqeuest').html('<div class="col-md-12" style="text-align:center; "><li class="fa fa-spinner fa-spin" style="font-size:30px;text-align: center;"></li></div>');
            $('#newreqeuest').load(livesite+'MaterialRequest/loadnew');
        }
    }
    function editmr(selectedItem){
        $('#newreqeuest').html('<div class="col-md-12" style="text-align:center; "><li class="fa fa-spinner fa-spin" style="font-size:30px;text-align: center;"></li></div>');
        $('#newreqeuest').load(livesite+'MaterialRequest/loadnew',function(){
                        
                        var id = selectedItem.mr_pkey; //alert(id)
                        $("#table_appnd").html('<li class="fa fa-spinner fa-spin"></li>');
                        loadtable(id, 1);
                        //value pass 
                        $('#btn-refresh').fadeIn();
                        $('#mr_pkey').val(id);
                        //list in all field customer_name,po_type,customer_po_number,att
                        $('#mr_code').val(selectedItem.mr_code);
                        $('#po_type').val(selectedItem.po_type);
                        $('#mr_date').val(selectedItem.mr_date);
                        $('#customer_name').val(selectedItem.customer_name);
                        $('#customer_po_number').val(selectedItem.customer_po_number);
//                        alert(selectedItem.mr_code);
                        $('#site_name').val(selectedItem.location);
//                        $('#site_name option[value="'+selectedItem.location+'"]').attr("selected",true);
//                        alert(selectedItem.store_code);
                        $('#store_code').val(selectedItem.store_fkey);
                            $('#att').val(selectedItem.att);
                            $('#remarks').val(selectedItem.remarks);
                        $('#dispatch').show();
                    });
    }
    $(document).ready(function () {
        
        $('#materialtable').datagrid({
            url: livesite+ 'MaterialRequest/materialtable',
            rownumbers: true,
            title: "Material Request List",
            fitColumns: true,
            singleSelect: true,
            PostsearchFilter:true,
            autoRowHeight: false,
            pagination: true,
            width: '99%',
            pageSize: 10,
            toolbar: [{
                    iconCls: 'icon-add',
                    text: "Edit ",
                    id: "btn",
                    handler: function () {
                        var row = $('#materialtable').datagrid('getSelected');
                        //console.log(row);

                        if (row) {

                            var mr_pkey = row.mr_pkey;
                            editmr(row);
//                            alert(row.store_code);
                            

                        } else
                        {
                            alert("Please Choose A Material Request");
                        }




                    }
                },'-',{
                    iconCls: 'icon-add',
                    text: "Details ",
                    id: "btn",
                    handler: function () {
                        var row = $('#materialtable').datagrid('getSelected');
                        //console.log(row);

                        if (row) {

                            var mr_pkey = row.mr_pkey;
                            showModalForm(livesite+'MaterialRequest/showDetails/'+mr_pkey);
//                            alert(row.store_code);
                            

                        } else
                        {
                            alert("Please Choose A Material Request");
                        }




                    }
                }],
            columns: [[
                    {field: 'mr_code', title: 'MR Number', width: 100, sortable: true, order: 'asc', editor: 'textbox'},
                    {field: 'mr_date', title: 'Required Date', width: 100, sortable: true, order: 'asc', editor: 'textbox'},
                    {field: 'store_code', title: 'Store Name', width: 100, sortable: true, order: 'asc', editor: 'textbox'},
                    {field: 'required', title: 'Total Required', width: 100, sortable: true, order: 'asc', editor: 'textbox'},
                    {field: 'ordered', title: 'Total Ordered', width: 100, sortable: true, order: 'asc', editor: 'textbox'},
                    {field: 'pending', title: 'Total Pending', width: 100, sortable: true, order: 'asc', editor: 'textbox'},
                    {field: 'remarks', title: 'Remark', width: 100, sortable: true, order: 'asc', editor: 'textbox'},
                    {field: 'matstatus', title: 'Status', width: 100, sortable: true, order: 'asc', editor: 'textbox'},
                ]],
//            rowStyler: function (index, row) {
//                                    var style = "";
//                                    if (row.ordered == '0') {
//                                       style += 'background-color:#fff;color:#000;';
//                                    }else{
//                                       style += 'background-color:#cac3c3;color:#fff;';
//                                    }
//                                    return style;
//                                },
            onSelect: function (index, row) {
                
                    //var checkfkeys = row.fkey;
                    if (row.ordered == '0') 
                    {
                                                $('#btn').linkbutton('enable');
                    } else
                    {
                        $('#btn').linkbutton('disable');
                    }
                
            },onSearch:function(s){
                                    
                                    $('#materialtable').datagrid('load',{
                                            emp: $('#searchqupo').val()
                                    });
                                }
        });
        
        
        var billno = {
            url: function (phrase) {
                return livesite+ "MaterialRequest/getautocompletionsmr_code?mr_code=" + phrase;
            },
            getValue: "mr_code",
            list: {
                onSelectItemEvent: function () {
                    
                    var selectedItem = $('#bill').getSelectedItemData();
                    editmr(selectedItem);
                }
            }
        };
        $('#bill').easyAutocomplete(billno);
    });
</script>