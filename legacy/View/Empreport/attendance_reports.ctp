<!-- <section class="content-header">
<div class="col-md-12">
    <div class="col-md-6" style="font-size: 30px;text-align: left;">Attendance Reports</div>
    <div class="col-md-6" style="text-align: right;">
    <button type="button" style="text-align:left;" class="btn btn-info" onclick="refresh()">Refresh</button>
    </div>

</div>
  <hr style="margin-top: 0px;margin-bottom: -10px;">  
</section> -->
<style>
  /* <!-- edited by bindhu 19-02-2026 --> */
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
<section class="content-header heading">
  <h1 class="text-primary-18">Attendance Reports</h1>

  <div style="display:flex; align-items:center; gap:10px;">

   

    <button type="button" class="btn btn-info" onclick="refresh()">
      Refresh
    </button>
 <div class="text-primary-16 home">
      <i class="fa" style="font-size:16px;">&#xf104;</i>
      Back
    </div>
  </div>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <!-- /.col -->
        <div class="col-md-6" style="margin-top: 9px;">
          <div class="box box-primary" style="">
            <div class="box-body no-padding">
              <!-- THE CALENDAR -->
              <div id="calendar">
                  
              </div>
              <div class="row"style="padding-left: 9px;padding-bottom: 5px; padding-top: 5px;">
                  <div class="col-md-1">
                      <span class="label" style="background-color: red;">Absent</span>
                  </div>
                  <div class="col-md-2" style="padding-left: 30px;">
                      <span class="label" style="background-color: green;">Present</span>
                  </div>
                  <!--<div class="col-md-2">
                      <span class="label" style="background-color: orange;">Half Day</span>
                  </div>-->
                  <div class="col-md-2" style="    margin-left: -20px;">
                      <span class="label" style="background-color: deepskyblue;color: black;">Work From Home</span>
                  </div>
                  <div class="col-md-1" style="padding-left: 30px;">
                      <span class="label" style="background-color: orange;">Leave</span>
                  </div>
                  <div class="col-md-2" style="    padding-left: 53px;">
                      <span class="label" style="background-color: yellow;color: black;">Week Off</span>
                  </div>
                  <div class="col-md-1" style="padding-left: 39px;">
                      <span class="label" style="background-color: blue;color: white;">Holiday</span>
                  </div>
                   <div class="col-md-1" style="padding-left: 39px;">
                      
                  </div>
                 
              </div>
            </div>
          
            <!-- /.box-body -->

          </div>
          <!-- /. box -->
        </div>


        <div class="col-md-6" id="showattendancedetails" style="margin-top: 8px;">
            <!-- DIRECT CHAT DANGER -->
<!--            <div class="box ">
                <div class="box-header with-border">
                 
                    
                </div> /.box-header 
                <div class="box-body">
                     Employee import form 
                    <form class="form-horizontal" method="post" action="" id="importemployeectcform">
                        <div class="row">
                   
                        
                        <div class="form-group">
                         
                
                        <div class="col-md-4">
                            
                            <label class="col-sm-5" for="filterby_month">Choose Month</label>
                            <div class="col-md-7">
                                
                                <input type="text" id="filterby_month" onchange="filterAttendanceupload(this);"  >
                                

                            </div>
                        </div> 
                         
                    </div> 
                  
                  
                            
                        </div>
                        
                     
                    </form>
                     <div class="box-body">
                <br>
                <table id="att_table" class="table table-bordered table-hover">
                    <tbody>
                    </tbody>
                </table>
            </div> /.box-body 
                </div>
                 
            </div>-->
        </div>
<div class="box-footer">
            <div class="row">
                <div class="col-md-12">
                    <?php  foreach ($arr_registerentries as $key => $entry) { ?>
                        <div class="col-md-3" style="margin-top: 10px;">
                            <div class="col-md-5" style="color:<?php echo $entry['textColor'] ?>;background-color: <?php echo $entry['color'] ?>"><?php echo $key; ?></div>
                            <div class="col-md-7"><?php echo $entry['label'] ?></div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div><!-- /.box-footer -->
    </div>
</section>

<script>    
    function filterAttendanceupload(obj) {
        var month = $('#importemployeectcform #filterby_month').val();   
        var employee = $('#importemployeectcform #emp_fkey').val()
  
        $('#att_table').datagrid('load', {
            branch: month,
            employee: employee,
           
        }); 
        
    } 
 
   
  
    
 jQuery(document).ready(function () {
        var employee = $('#attendanceuploadfilter #emp_fkey').val();
        
        $('#filterby_month').datepicker({
            format: 'yyyy-mm',
            autoclose: true,
            startView: "months",
            minViewMode: "months",
            endDate: new Date(),

        });

        $('#att_table').datagrid({
             toolbar: [
                                 {
				text:'Download',
				iconCls:'icon-download',
				handler: function(){
                                        var month = $('#importemployeectcform #filterby_month').val();   
                               var url = livesite+'Empreport/attendencereportpdf/'+month;
                               $(location).attr('href',url);  
                                      
			
				}
				}
                                
                            ],
            url: livesite + "Empreport/employeelist",
            pagination: true,
            singleSelect: false,
            queryParams:{
                    employee: employee
                },
                
                   
            fitColumns: true,
            pageList: [2, 5, 10, 50, 100],
            columns: [[
                    {field: 'LOGDATE', title: 'Date', width: "30%"},
                    {field: 'C1', title: 'Direction', width: "30%"},
                    {field:'C3',title:'Location',width:'40%'},
                      ]]
        });
    });
    
    function events()
    {
        $.ajax({
                    url:'Grade/eventsCalender',
                    type: 'post',
                    success: function(resp){
//                        $.notify($.parseJSON(resp).msg,{
//                            type: 'success',
//                            allow_dismiss: false
//                        });
                    }
                });
    }
  $(function () {

    /* initialize the external events
     -----------------------------------------------------------------*/
    function ini_events(ele) {
      ele.each(function () {

        // create an Event Object (http://arshaw.com/fullcalendar/docs/event_data/Event_Object/)
        // it doesn't need to have a start or end
        var eventObject = {
          title: $.trim($(this).text()) // use the element's text as the event title
        };

        // store the Event Object in the DOM element so we can get to it later
        $(this).data('eventObject', eventObject);

        // make the event draggable using jQuery UI
        $(this).draggable({
          zIndex: 1070,
          revert: true, // will cause the event to go back to its
          revertDuration: 0  //  original position after the drag
        });

      });
    }

    ini_events($('#external-events div.external-event'));

    /* initialize the calendar
     -----------------------------------------------------------------*/
    //Date for the calendar events (dummy data)
    var date = new Date();
    var d = date.getDate(),
        m = date.getMonth(),
        y = date.getFullYear();
   
    $('#calendar').fullCalendar({
      eventStartEditable : false,
      showNonCurrentDates : false,
      fixedWeekCount : false,
      header: {
        left: 'prev,next',
        center: 'title',
        right: ' today'
      },            
      buttonText: {
        today: 'today',
        month: 'month',
        week: 'week',
        day: 'day'
      },
      
    eventRender: function(eventObj, $el) {
      $el.popover({
        title: eventObj.leavetype,
        content: eventObj.content,
        trigger: 'hover',
        placement: 'top',
        container: 'body'
      });
    },
      eventClick: function(event) {
      if (event.HOLIDAYID) {
       $("#showattendancedetails").html('<li class="fa fa-spinner fa-spin" style="margin-left: 5em;margin-top: 2em;font-size: 50px;"></li>');
           $("#showattendancedetails").load(livesite+"Empreport/showattendancedetails/"+event.HOLIDAYID);
//         $('#showleavedetails').css({'height':'303px'});
      return false;
    }
  },

        
      //Random default events
      //edited by athira on 24-05-2026
      events: function(start, end, timezone, callback) {
        var date = $('#calendar').fullCalendar('getDate');
        var month = date.format('YYYY-MM');
        $.ajax({
          url: livesite + 'Empreport/getattendancedays',
          data: {
            start: start.format(),
            end: end.format(),
            selected_month: month
          },
          success: function(resp) {
            callback($.parseJSON(resp));
          }
        });
      },
      //ended by athira on 24-05-2026
      editable: true,
      droppable: true, // this allows things to be dropped onto the calendar !!!
      drop: function (date, allDay) { // this function is called when something is dropped

        // retrieve the dropped element's stored Event Object
        var originalEventObject = $(this).data('eventObject');

        // we need to copy it, so that multiple events don't have a reference to the same object
        var copiedEventObject = $.extend({}, originalEventObject);

        // assign it the date that was reported
        copiedEventObject.start = date;
        copiedEventObject.allDay = allDay;
        copiedEventObject.backgroundColor = $(this).css("background-color");
        copiedEventObject.borderColor = $(this).css("border-color");

        // render the event on the calendar
        // the last `true` argument determines if the event "sticks" (http://arshaw.com/fullcalendar/docs/event_rendering/renderEvent/)
        $('#calendar').fullCalendar('renderEvent', copiedEventObject, true);
        //$('#calendar').fullCalendar('removeEvents', 2); //Remove events with the id: 2
        // is the "remove after drop" checkbox checked?
        if ($('#drop-remove').is(':checked')) {
          // if so, remove the element from the "Draggable Events" list
          $(this).remove();
        }
        var d = new Date(date);
        var day =  d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate() ;
        $.ajax({
                    url:'Grade/insert',
                    type: 'post',
                    data: { 
                        date:day,
                        Text:$(this).text(),
                        Background:copiedEventObject.backgroundColor,
                        Border:copiedEventObject.borderColor
                    },
                    success: function(resp){
//                        $.notify($.parseJSON(resp).msg,{
//                            type: 'success',
//                            allow_dismiss: false
//                        });
                    }
                });
      }
    });
     //edited by athira on 24-05-2026
    var startdate = $('#calendar').fullCalendar('getView').start.format();
       var enddate = $('#calendar').fullCalendar('getView').end.format();
       var selected_month = $('#calendar').fullCalendar('getDate').format('YYYY-MM');
       $("#showattendancedetails").html('<li class="fa fa-spinner fa-spin" style="margin-left: 5em;margin-top: 2em;font-size: 50px;"></li>');
       $("#showattendancedetails").load(livesite+"Empreport/showattendancedetails/",{start: startdate,end: enddate, selected_month: selected_month});
     
    $('.fc-prev-button').click(function(){  
       var startdate = $('#calendar').fullCalendar('getView').start.format();
       var enddate = $('#calendar').fullCalendar('getView').end.format();
       var selected_month = $('#calendar').fullCalendar('getDate').format('YYYY-MM');
       $("#showattendancedetails").html('<li class="fa fa-spinner fa-spin" style="margin-left: 5em;margin-top: 2em;font-size: 50px;"></li>');
       $("#showattendancedetails").load(livesite+"Empreport/showattendancedetails/",{start: startdate,end: enddate, selected_month: selected_month});
      // $('#showattendancedetails').css({'height':'200px'});
//      retrn false;
   });
   $('.fc-next-button').click(function(){
        var startdate = $('#calendar').fullCalendar('getView').start.format();
       var enddate = $('#calendar').fullCalendar('getView').end.format();
       var selected_month = $('#calendar').fullCalendar('getDate').format('YYYY-MM');
       $("#showattendancedetails").html('<li class="fa fa-spinner fa-spin" style="margin-left: 5em;margin-top: 2em;font-size: 50px;"></li>');
       $("#showattendancedetails").load(livesite+"Empreport/showattendancedetails/",{start: startdate,end: enddate, selected_month: selected_month});
 //ended by athira on 24-05-2026
});
   
  
    /* ADDING EVENTS */
    var currColor = "#3c8dbc"; //Red by default
    //Color chooser button
    var colorChooser = $("#color-chooser-btn");
    $("#color-chooser > li > a").click(function (e) {
      e.preventDefault();
      //Save color
      currColor = $(this).css("color");
      //Add color effect to button
      $('#add-new-event').css({"background-color": currColor, "border-color": currColor});
    });
    $("#add-new-event").click(function (e) {
      e.preventDefault();
      //Get value and make sure it is not null
      var val = $("#new-event").val();
      if (val.length == 0) {
        return;
      }

      //Create events
      var event = $("<div />");
      event.css({"background-color": currColor, "border-color": currColor, "color": "#fff"}).addClass("external-event");
      event.html(val);
      $('#external-events').prepend(event);

      //Add draggable funtionality
      ini_events(event);

      //Remove event from text input
      $("#new-event").val("");
    });
  });
function refresh(){
  var startdate = $('#calendar').fullCalendar('getView').start.format();
  //$('#btn-cancel').html('<li class="fa fa-spinner fa-spin"></li> Cancelling Leave...').attr('disabled', 'disabled');
  $.ajax({
                url: livesite + 'Empreport/refresh/'+startdate,
                type: 'POST',
                data: {
                        start_date: startdate
                    },
                success: function (resp)
                {
                   $('#loaders').show();
        $('#container').load(livesite+'Empreport/attendanceReports',function(){
        $('#loaders').hide();
        });
                }
        });
    }

       // edited by bindhu 19-02-2026
 $(".home").on("click", function() {
        $("#container").isLoading({
            text: "Loading",
            position: "overlay",
        });

        $("#container").load(livesite + "EmployeeMenu/index", function() {
            isDashboardShown = false;
        });


    });
    //  edited by bindhu 19-02-2026 end
</script>