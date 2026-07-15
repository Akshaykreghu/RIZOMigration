<div class="col-md-12">

            <div class="tabset0">
                <div data-pws-tab="tab1" data-pws-tab-name="Today Attendance" data-pws-tab-icon="fa-refresh fa-spin">
                    <div class="row">
                        <div class="col-md-8">

                            <div class="box box-info">
                                <a onclick="activatess();">Next</a>
                                

                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="box box-danger">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Who Is In</h3>

                                </div>
                                <div class="box-body chart-responsive">
                                    <canvas id="punchChart" height="180"></canvas>
                                </div><!-- /.box-body -->
                            </div>
                        </div>
                    </div>
                </div>
                <div data-pws-tab="tab2" data-pws-tab-name="This Month Attendance" data-pws-tab-icon="fa-refresh fa-spin">
                    
                    <div class="row">
                        <div class="col-md-8">

                            <div class="box box-info">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Employee Check-In/Out Status</h3>
                                    <div class="box-tools pull-right">
                                        <button type="button" onclick="Modal('Latein','Month')"  class="btn btn-linkedin">Late In</button>
                                        <button type="button" onclick="Modal('Earlyin','Month')" class="btn btn-linkedin">Early In</button>
                                        <button type="button" onclick="Modal('Lateout','Month')" class="btn btn-linkedin">Late Out</button>
                                        <button type="button" onclick="Modal('Earlyout','Month')" class="btn btn-linkedin">Early Out</button>
                                        <!--button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                                        <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button-->
                                    </div>
                                </div><!-- /.box-header -->
                                <div class="box-body">
                                    <div class="table-responsive">
                                        <table class="table no-margin" id="thismonthattandence">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Date</th>
                                                    <th>Time</th>
                                                    <th>Check-In/Out</th>
                                                    <th>Location</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div><!-- /.table-responsive -->
                                </div><!-- /.box-body -->

                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="box box-danger">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Statistics</h3>

                                </div>
                                <div class="box-body chart-responsive">
                                    <canvas id="punchChart" height="180"></canvas>
                                </div><!-- /.box-body -->
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div data-pws-tab="tab3" data-pws-tab-name="Last Month Attendance" data-pws-tab-icon="fa-refresh fa-spin">
                    
                    <div class="row">
                        <div class="col-md-8">

                            <div class="box box-info">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Employee Check-In/Out Status</h3>
                                    <div class="box-tools pull-right">
                                        <button type="button" onclick="Modal('Earlyin','LastMonth')"  class="btn btn-linkedin">Early In</button>
                                        <button type="button"  onclick="Modal('Lateout','LastMonth')" class="btn btn-linkedin">Late Out</button>
                                        <button type="button" onclick="Modal('Earlyout','LastMonth')"  class="btn btn-linkedin">Early Out</button>
                                        <!--button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                                        <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button-->
                                    </div>
                                </div><!-- /.box-header -->
                                <div class="box-body">
                                    <div class="table-responsive">
                                        <table class="table no-margin" id="lastmonthattandence">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Date</th>
                                                    <th>Time</th>
                                                    <th>Check-In/Out</th>
                                                    <th>Location</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div><!-- /.table-responsive -->
                                </div><!-- /.box-body -->

                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="box box-danger">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Statistics</h3>

                                </div>
                                <div class="box-body chart-responsive">
                                    <canvas id="punchChart" height="180"></canvas>
                                </div><!-- /.box-body -->
                            </div>
                        </div>
                    </div>
                </div>

                <div data-pws-tab="tab4" data-pws-tab-name="Leave Requests" data-pws-tab-icon="fa-refresh fa-spin">
                    
                    <div class="row">
                        <div class="col-md-8">

                            <div class="box box-info">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Employee Leave Requests</h3>
                                    <div class="box-tools pull-right">
                                        <div class="row">
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                
                                            </div>
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                
                                            </div>
                                        </div>
                                        <!--button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                                        <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button-->
                                    </div>
                                </div><!-- /.box-header -->
                                <div class="box-body">
                                    <div class="table-responsive">
                                        <table class="table no-margin" id="empleaverequests">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Leave type</th>
                                                    <th>Applied date</th>
                                                    <th>From date</th>
                                                    <th>To date</th>
                                                    <th>Leave status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                               
                                            </tbody>
                                        </table>
                                    </div><!-- /.table-responsive -->
                                </div><!-- /.box-body -->

                            </div>
                        </div>
                        
                    </div>
                    
                </div>
                <!--div data-pws-tab="tab6" data-pws-tab-name="To Do" data-pws-tab-icon="fa-refresh fa-spin">


                </div>
                <div data-pws-tab="tab7" data-pws-tab-name="Reminders" data-pws-tab-icon="fa-refresh fa-spin">


                </div>
                <div data-pws-tab="tab8" data-pws-tab-name="Mail Box" data-pws-tab-icon="fa-refresh fa-spin">


                </div-->
                
            </div>

        </div>
<script type="text/javascript">
    function leaves(){
        var url = 'dashboard/leaverequests';
        showModalForm(url)    
    }function activatess(){
//        var currentTab = $('.pws_tabs_container').find('a').removeClass('pws_tab_active');
        var currentTab = $('.pws_tabs_container').find('a[data-tab-id="tab2"]').trigger('click');
//        var currentTab = $('.pws_tabs_container').find('a[data-tab-id="tab2"]').addClass('pws_tab_active');
//        var currentTab = $('.pws_tabs_list').find('div[data-pws-tab="tab2"]').addClass('pws_tabs_slide_left_show');
    }
    $(document).ready(function () {
        $('.tabset0').pwstabs({
            effect: 'slideleft', // You can change effects of your tabs container: scale / slideleft / slideright / slidetop / slidedown / none
            defaultTab: 1, // The tab we want to be opened by default
            containerWidth: '100%', // Set custom container width if not set then 100% is used
            containerHeight:'100%',
            tabsPosition: 'horizontal', // Tabs position: horizontal / vertical
            horizontalPosition: 'top', // Tabs horizontal position: top / bottom
            verticalPosition: 'left', // Tabs vertical position: left / right
            responsive: true, // Make tabs container responsive: true / false - boolean
            theme: '',
            rtl: false                    // Right to left support: true/ false
        });
        
    });
    
</script>    