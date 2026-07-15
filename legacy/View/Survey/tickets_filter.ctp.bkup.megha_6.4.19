<style>
    .form-horizontal .control-label{

        text-align: left;

    }
    .tickets_tabs.pws_tabs_list{
        min-height:600px !important;
    }
</style>
<section class="content-header">
    <h2 style="text-align:left; font-size: 2em;"> Tickets Details   </h2>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="tickets_tabs">
                <div data-pws-tab="tobeapproved" data-pws-tab-name="To Be Approved">
                        <div class="box ">
                                <br>
                                <div class="box-body">
                                    <form class="form-horizontal" method="post" action=""  id="tickets_form_to_be_approved" name="tickets_form_to_be_approved"> 
                                        <div class="row">
                                            <div class="form-group">
                                                <div class="col-sm-4">
                                                    <label class="col-sm-5 control-label" for="sites">Site</label>
                                                    <div class="col-md-7">
                                                        <select id="sites" name="sites" class="form-control js-example-basic-single" onchange="fillFilters(this,'#tickets_form_to_be_approved');" >
                                                            <option value="">All</option>
                                                            <?php foreach ($sites as $key => $value) { 
                                                            ?>                              
                                                                <option  value="<?php echo $value['efsr_site']['efsr_site_pkey']; ?>">
                                                                    <?php echo $value['efsr_site']['site_name']; ?>
                                                                </option>
                                                            <?php 
                                                                } 
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <label class="col-sm-5 control-label" for="equipment">Equipments</label>
                                                    <div class="col-md-7">
                                                        <select id="equipment" name="equipment" class="form-control js-example-basic-single" onchange="filterTickets('#tickets_form_to_be_approved');" >
                                                            <option value="">All</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <label class="col-sm-5 control-label" for="Technician">Technician</label>
                                                    <div class="col-md-7">
                                                        <select id="technician" name="technician" class="form-control js-example-basic-single" onchange="filterTickets('#tickets_form_to_be_approved');" >
                                                            <option value="">All</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                
                                                                                    
                                            </div>
                                        </div>
                                    </form>
                                    <div class="box-body">
                                        <table id="tickets_table_to_be_approved" class="table table-bordered table-hover">
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div><!-- /.box-body -->
                                </div>

                            </div>
                        </div>
                <div data-pws-tab="approved" data-pws-tab-name="Approved">
                    <div class="box ">
                                    <br>
                                    <div class="box-body">
                                        <form class="form-horizontal" method="post" action=""  id="tickets_form_approved" name="tickets_form_approved"> 
                                            <div class="row">
                                                <div class="form-group">
                                                    <div class="col-sm-4">
                                                        <label class="col-sm-5 control-label" for="sites">Site</label>
                                                        <div class="col-md-7">
                                                            <select id="sites" name="sites" class="form-control js-example-basic-single" onchange="fillFilters(this,'#tickets_form_approved');" >
                                                                <option value="">All</option>
                                                                <?php foreach ($sites as $key => $value) { 
                                                                ?>                              
                                                                    <option  value="<?php echo $value['efsr_site']['efsr_site_pkey']; ?>">
                                                                        <?php echo $value['efsr_site']['site_name']; ?>
                                                                    </option>
                                                                <?php 
                                                                    } 
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <label class="col-sm-5 control-label" for="equipment">Equipments</label>
                                                        <div class="col-md-7">
                                                            <select id="equipment" name="equipment" class="form-control js-example-basic-single" onchange="filterTickets('#tickets_form_approved');" >
                                                                <option value="">All</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <label class="col-sm-5 control-label" for="Technician">Technician</label>
                                                        <div class="col-md-7">
                                                            <select id="technician" name="technician" class="form-control js-example-basic-single" onchange="filterTickets('#tickets_form_approved');" >
                                                                <option value="">All</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                                                        
                                                </div>
                                            </div>
                                        </form>
                                        <div class="box-body">
                                            <table id="tickets_table_approved" class="table table-bordered table-hover">
                                                <tbody>
                                                </tbody>
                                            </table>
                                        </div><!-- /.box-body -->
                                    </div>

                                </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    function fillFilters(obj,form){
        var form = form;
        var site_id     = $(form +' #sites').val();
         $.ajax({
         url:'Survey/getEquipments',
           type: 'POST',
           data: {
               site_id: site_id
           },
           success: function (resp)
           {
                var data = $.parseJSON(resp);
                var equipments = data.equipments;
                var technicians = data.technicians;

                var equipmentsHtml = '<option value="" selected="selected">All</option>';
                var techniciansHtml = '<option value="" selected="selected">All</option>';
                if(equipments.length > 0){
                    for (i in equipments){
                        equipmentsHtml +="<option  value="+equipments[i].equipment_pkey+">"+equipments[i].equipments_name +" | "+  equipments[i].manufacturer +"</option>";
                    }
                }
                if(technicians.length > 0){
                    for (i in technicians){
                        techniciansHtml +="<option  value="+technicians[i].emp_pkey+">"+technicians[i].first_name +"</option>";
                    }
                }
                
                $(form+' #equipment').html(equipmentsHtml);
                $(form+' #technician').html(techniciansHtml);
      
                filterTickets(form);
           }
       });
    }
    function filterTickets(form) {
        
        var site          = $(form +' #sites').val();
        var equipment     = $(form +' #equipment').val();
        var technician    = $(form +' #technician').val();

        var table = (form =='#tickets_form_to_be_approved')?'#tickets_table_to_be_approved':'#tickets_table_approved';

        $(table).datagrid('load', {
            site_id: site,
            equipment: equipment,
            technician: technician
        });
    }

    function activateTabs(){
        if(toBeApprovedTableLoaded && approvedTableLoaded ){
            $('.tickets_tabs').pwstabs({
                effect: 'scale',
                defaultTab: 1,
                containerWidth: '100%'
            });
        }else{
            setTimeout(activateTabs, 100);
        }
    }
    var toBeApprovedTableLoaded = false;
    var approvedTableLoaded = false;
    jQuery(document).ready(function () {
        
        var site          = $('#tickets_form_to_be_approved #sites').val();
        var equipment     = $('#tickets_form_to_be_approved #equipment').val();
        var technician    = $('#tickets_form_to_be_approved #technician').val();

        $('#tickets_table_to_be_approved').datagrid({
            url: livesite + "Survey/getTickets/2",
            queryParams: {
                site_id: site,
                equipment: equipment,
                technician: technician
            },
            pagination: true,
            singleSelect: true,
            rownumbers: true,
            fitColumns: true,
            pageList: [2, 5, 10, 50, 100],
            columns: [[
                    {field: 'ticket_no', title: 'Ticket No', width: "10%"},
                    {field: 'site_name', title: 'Site Name', width: "10%"},
                    {field: 'type_name', title: 'Survey Type Name', width: "30%"},
                    {field: 'equipments_name', title: 'Equipments Name', width: "30%"},
                    {field: 'first_name', title: 'Technician', width: "10%"},
                    {field: 'approved_by', title: 'Approved By', width: "10%"},
                    
                ]],
            onDblClickRow: function(index,row){
                showLargeModalForm(livesite + 'Survey/preview/'+row.ticket_no+'/2');  
            },
            onLoadSuccess:function(){
                toBeApprovedTableLoaded = true;
            }
            
        });

        var site          = $('#tickets_form_approved #sites').val();
        var equipment     = $('#tickets_form_approved #equipment').val();
        var technician    = $('#tickets_form_approved #technician').val();
        $('#tickets_table_approved').datagrid({
            url: livesite + "Survey/getTickets/3",
            queryParams: {
                site_id: site,
                equipment: equipment,
                technician: technician
            },
            pagination: true,
            singleSelect: true,
            rownumbers: true,
            fitColumns: true,
            pageList: [2, 5, 10, 50, 100],
            columns: [[
                    {field: 'ticket_no', title: 'Ticket No', width: "10%"},
                    {field: 'site_name', title: 'Site Name', width: "10%"},
                    {field: 'type_name', title: 'Survey Type Name', width: "30%"},
                    {field: 'equipments_name', title: 'Equipments Name', width: "30%"},
                    {field: 'first_name', title: 'Technician', width: "10%"},
                    {field: 'approved_by', title: 'Approved By', width: "10%"},
                    
                ]],
            onDblClickRow: function(index,row){
                showLargeModalForm(livesite + 'Survey/preview/'+row.ticket_no+'/3');  
            },
            onLoadSuccess:function(){
                approvedTableLoaded = true;
            }
        });
        setTimeout(activateTabs, 100);
    });

</script>