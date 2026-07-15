<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- DIRECT CHAT DANGER -->
            <div class="box ">

                <div class="box-body">
<legend>Item Specification</legend>
<table id="item_specification">
    
</table>
</div>
</div>
</div>
</div>
</section>
<script>
 $(document).ready(function () {
     
     $("#item_specification").datagrid(
             {
                   title: "Item Specificaton",
             rownumbers: true,
             itColumns: true,
             url: 'ItemSpecification/listmaster',
            singleSelect: true,
            autoRowHeight: false,
            pagination: true,
            width: '100%',
            pageSize: 10,
            toolbar : [
                {
                     iconCls: 'icon-add',
                    text: "New",
                    handler: function () {
                        
                        var url = 'ItemSpecification/specification';
						showModalForm(url);
                        //$('#modalDiv').load(url,function(){
                        //    $('#modalDiv').modal('show');
                        //});
                    }  
                }, '-',
                {
                      iconCls: 'icon-edit',
                    text: "Edit",
                    handler: function () {
                        var row = $('#item_specification').datagrid('getSelected');
                        if (row) {
                            var specification_pkey = row.specification_pkey;
                  
                            var url = 'ItemSpecification/specification/' + specification_pkey;
							showModalForm(url);
                            //$('#modalDiv').load(url,function(){
                            //    $('#modalDiv').modal('show');
                            //});
                        }
                        else
                        {
                            alert("select a row first");
                        }
                    }
                    
                }, '-',
                {
                    iconCls:'icon-cancel',
                    text: "remove",
                           handler: function () {
                     var row = $('#item_specification').datagrid('getSelected');
                        if (row) {
                                  var r=confirm("Do You Want  To Remove The Selected Item")
                                    if(r==true){
                               //  alert("you pressed ok");
                              
                                                                 var specification_pkey = row.specification_pkey;
                                                                 $.ajax({
                                                                     url: 'Item_specification/delete/' + specification_pkey,
                                                                     success: function (resp) {
                                                                               $('#item_specification').datagrid('reload');
                                                        
                                                                     }
                                                                 });
                                                             }
                        else
                        {
                            $.notify("Canceled");
                        }
                        }
                    }
                }
            ],
            columns: [[
                    {field: 'category_code', title: 'Category Code', width: 200,sortable:true,order:'asc'},
                    {field: 'description', title:'Description', width: 200,sortable:true,order:'asc'},
                    {field: 'item_specification', title:'Item Specification', width: 200,sortable:true,order:'asc'},
                        
            ]]
                 
                 
             });     
     
     
     
     
 })





</script>
