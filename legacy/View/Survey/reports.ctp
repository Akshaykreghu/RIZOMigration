<html>
   <head>
      <style>
        #content{
            margin-top:0px;
             top: calc(100% - 100px);
         }
         @page {
            margin:0px;

         }
         @page :first {
            margin-top: 100px;
         }
       

         body {
            margin: 100px 20px 100px 20px;
         }
         #header {
             //position: fixed;
             left: 20px; right: 20px; top: 20px;bottom:10px;
             text-align: center;
             height: 160px;

         }
         #footer {
            position: fixed;
            left: 20px; right: 20px; bottom: 20px;
            text-align: center;
            height: 40px;
         }
      
         table {
             width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            page-break-inside: avoid;
         }

         table, th, td {
           border: .1px solid black;
            font-size: 10px;
         }
         td,th{
            width: 16%;
         }
         .gray{
             background-color:lightgray;
             color:#444444;
         }
         .center{
            text-align: center;
         }
         td.td32{
            width: 29.5% !important;
         }
         td.td10{
            width: 10% !important;
         }
         

      </style>
   </head>
   <body>
      <div id="header" class="header">
         <img class="logo" src="img/pdf/header.png" height="150" width="100%">
      </div>
      <div id="footer" class="footer">
      </div>
      <div id="content">
        <table border="1" cellpadding="1" cellspacing="1" style="width: 100%;">
            <tbody>
                <tr>
                    <td colspan="6" class="gray center">eFSR Details</td>
                </tr>
                <tr>
                    <th>Site Id</th>
                    <td> <?php echo isset($result['efsr_site']) && isset($result['efsr_site']['site_id']) && $result['efsr_site']['site_id'] != ''? $result['efsr_site']['site_id'] : ''; ?> </td>

                    <th>Engine Serial Number</th>
                    <td> <?php echo isset($result['efsr_equipments_master']) && isset($result['efsr_equipments_master']['equipments_sn']) && $result['efsr_equipments_master']['equipments_sn'] != ''? $result['efsr_equipments_master']['equipments_sn'] : ''; ?> </td>

                    <th>eFSR ID</th>
                    <td> <?php echo isset($result['efsr_tickets']) && isset($result['efsr_tickets']['efsr_id']) && $result['efsr_tickets']['efsr_id'] != ''? $result['efsr_tickets']['efsr_id'] : ''; ?> </td>
                </tr>
                <tr>
                    <th>Customer Name</th>
                    <td> <?php echo isset($result['contacts']) && isset($result['contacts']['customer']) && $result['contacts']['customer'] != ''? $result['contacts']['customer'] : ''; ?> </td>

                    <th>Sap Site ID</th>
                    <td> <?php echo isset($result['efsr_site']) && isset($result['efsr_site']['sap_site_id']) && $result['efsr_site']['sap_site_id'] != ''? $result['efsr_site']['sap_site_id'] : ''; ?> </td>

                    <th>Site Name</th>
                    <td> <?php echo isset($result['efsr_site']) && isset($result['efsr_site']['site_name']) && $result['efsr_site']['site_name'] != ''? $result['efsr_site']['site_name'] : ''; ?> </td>
                </tr>
                <tr>
                    <th>Location</th>
                    <td> <?php echo isset($result['efsr_site']) && isset($result['efsr_site']['site_name']) && $result['efsr_site']['site_name'] != ''? $result['efsr_site']['site_name'] : ''; ?> </td>

                    <th>Address</th>
                    <td colspan="3" >
                        <?php echo isset($result['efsr_site']) && isset($result['efsr_site']['address']) && $result['efsr_site']['address'] != ''? $result['efsr_site']['address'] : ''; ?> 
                    </td>
                    

                </tr>
                <tr>
                    <th>Application</th>
                    <td> <?php echo isset($result[0]) && isset($result[0]['application']) && $result[0]['application'] != ''? $result[0]['application'] : ''; ?> </td>

                    <th>DG Supplied By</th>
                    <td> <?php echo isset($result['efsr_equipments_master']) && isset($result['efsr_equipments_master']['supplied_by']) && $result['efsr_equipments_master']['supplied_by'] != ''? $result['efsr_equipments_master']['supplied_by'] : ''; ?> 
                    </td>

                    <th>Lat / Log</th>
                    <td> <?php 
                            $lat =  isset($result['efsr_site']) && isset($result['efsr_site']['latitude']) && $result['efsr_site']['latitude'] != ''? $result['efsr_site']['latitude'] : ''; 
                            $lon =  isset($result['efsr_site']) && isset($result['efsr_site']['longitude']) && $result['efsr_site']['longitude'] != ''? $result['efsr_site']['longitude'] : ''; 
                            echo ($lat == "" && $lon == "" )?"":$lat." / ".$lon;
                            ?> 
                    </td>
                </tr>
                 <tr>
                    <th>Running Hour</th>
                    <td>  </td>

                    <th>Engine Model</th>
                    <td> <?php echo isset($result['efsr_equipments_master']) && isset($result['efsr_equipments_master']['model_no']) && $result['efsr_equipments_master']['model_no'] != ''? $result['efsr_equipments_master']['model_no'] : ''; ?> </td>

                    <th>Engine KVA</th>
                   <td> <?php echo isset($result['efsr_equipments_master']) && isset($result['efsr_equipments_master']['spec']) && $result['efsr_equipments_master']['spec'] != ''? $result['efsr_equipments_master']['spec'] : ''; ?> </td>
                </tr>
            </tbody>
        </table>
        <table border="1" cellpadding="1" cellspacing="1" style="width: 100%;">
            <tbody>
<!--                <tr>
                    <td class="center" > 
                        <img src="img/pdf/engine.png" style="height: 100px;  max-width: 100%;"/> 
                    </td>
                    <td class="center" > 
                        <img src="img/pdf/engine.png"  style="height: 100px;max-width: 100%;"/> 
                    </td>
                    <td class="center" > 
                         <img src="img/pdf/engine.png" style="height: 100px;  max-width: 100%;"/> 
                    </td>
                    
                </tr>-->
<!--                <tr>
                    <th class="center"> 
                        Engine Serial Number
                    </th>
                    <th class="center"> 
                        Site Condition - Pre-Service
                    </th>
                    <th class="center"> 
                        Hour Meter Reading
                    </th>
                    
                </tr>-->
            </tbody>
        </table>

        <table border="1" cellpadding="1" cellspacing="1" style="width: 100%;">
             <tbody>
                <tr>
                    <td colspan="6" class="gray center">Ticket Details</td>
                </tr>            
                <tr>
                    <th>Ticket Number</th>
                    <td> <?php echo isset($result['efsr_tickets']) && isset($result['efsr_tickets']['ticket_no']) && $result['efsr_tickets']['ticket_no'] != ''? $result['efsr_tickets']['ticket_no'] : ''; ?> 
                    </td>

                    <th>Ticket Registered on date</th>
                     <td> <?php echo isset($result['efsr_tickets']) && isset($result['efsr_tickets']['creation_date']) && $result['efsr_tickets']['creation_date'] != ''? $result['efsr_tickets']['creation_date'] : ''; ?> 
                    </td>
                    <th>Type of Service</th>
                    <td></td>
                </tr>
                <tr>
                    <td>Details</td>
                    <td colspan="5" rowspan="1"></td>
                </tr>
            </tbody>
        </table>
        <?php echo $lastServicesHtml;?>
        <?php echo $reportHtml;?>

        
  
        
      </div>
   </body>
</html>