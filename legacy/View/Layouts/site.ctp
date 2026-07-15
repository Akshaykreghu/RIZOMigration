<!doctype html>
<!--[if lt IE 7]><html lang="en" class="no-js ie6"><![endif]-->
<!--[if IE 7]><html lang="en" class="no-js ie7"><![endif]-->
<!--[if IE 8]><html lang="en" class="no-js ie8"><![endif]-->
<!--[if gt IE 8]><!-->
<html lang="en" class="no-js">
<!--<![endif]-->

<head>
    <meta charset="UTF-8">
    <title>Forsight | Home</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <link rel="shortcut icon" href="favicon.png">

    <link rel="stylesheet" href="<?php echo $this->webroot;?>siteui/css/bootstrap.css">
    
    <link rel="stylesheet" href="<?php echo $this->webroot;?>siteui/site/css/animate.css">
    <link rel="stylesheet" href="<?php echo $this->webroot;?>siteui/site/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo $this->webroot;?>siteui/site/css/slick.css">
    <link rel="stylesheet" href="<?php echo $this->webroot;?>siteui/site/js/rs-plugin/css/settings.css">

    <link rel="stylesheet" href="<?php echo $this->webroot;?>siteui/site/css/freeze.css">
    
    <link rel="stylesheet" href="<?php echo $this->webroot;?>siteui/site/css/bootstrapValidator.css">
    


    <script type="text/javascript" src="<?php echo $this->webroot;?>siteui/site/js/modernizr.custom.32033.js"></script>
     
    

    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <script src="<?php echo $this->webroot;?>siteui/site/js/jquery-1.11.1.min.js"></script>
    
    <script src="<?php echo $this->webroot;?>siteui/site/js/validator/bootstrapValidator.js"></script>
    <script src="<?php echo $this->webroot;?>siteui/js/bootstrap.min.js"></script>
    
   <script src="<?php echo $this->webroot;?>siteui/site/js/slick.min.js"></script>
    <script src="<?php echo $this->webroot;?>siteui/site/js/placeholdem.min.js"></script>
    <script src="<?php echo $this->webroot;?>siteui/site/js/rs-plugin/js/jquery.themepunch.plugins.min.js"></script>
    <script src="<?php echo $this->webroot;?>siteui/site/js/rs-plugin/js/jquery.themepunch.revolution.min.js"></script>
    <script src="<?php echo $this->webroot;?>siteui/site/js/waypoints.min.js"></script>
    <script src="<?php echo $this->webroot;?>siteui/site/js/scripts.js"></script>

    <script>
        $(document).ready(function() {
            appMaster.preLoader();
        });
    </script>
</head>

<body>

    <div class="pre-loader">
        <div class="load-con">
            <img src="site/img/freeze/logo.png" class="animated fadeInDown" alt="">
            <div class="spinner">
              <div class="bounce1"></div>
              <div class="bounce2"></div>
              <div class="bounce3"></div>
            </div>
        </div>
    </div>
   
    <header>
        
        <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
                <div class="container">
                    <!-- Brand and toggle get grouped for better mobile display -->
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                            <span class="fa fa-bars fa-lg"></span>
                        </button>
                        <a class="navbar-brand" href="index.html">
                            <img src="site/img/freeze/logo.png" alt="" class="logo">
                        </a>
                    </div>

                    <!-- Collect the nav links, forms, and other content for toggling -->
                    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">

                        <ul class="nav navbar-nav navbar-right">
                            <li><a href="#about">about</a>
                            </li>
                            <li><a href="#features">features</a>
                            </li>
                            <li><a href="#reviews">reviews</a>
                            </li>
                            <li><a href="#screens">Price</a>
                            </li>
                            <li><a href="#demo">demo</a>
                            </li>
                            <li><a href="#support">support</a>
                            </li>
                        </ul>
                    </div>
                    <!-- /.navbar-collapse -->
                </div>
                <!-- /.container-->
        </nav>
<div class="row home">
                    <div class="col-md-12">
                        <div class="row">
						 <div class="col-lg-8 col-md-8 head-text">
						 <div id="carousel-example-generic" class="carousel slide" data-ride="carousel" style="margin-top: 180px">
  <!-- Indicators -->
  <ol class="carousel-indicators">
    <li data-target="#carousel-example-generic" data-slide-to="0" class="active"></li>
    <li data-target="#carousel-example-generic" data-slide-to="1"></li>
    <li data-target="#carousel-example-generic" data-slide-to="2"></li>
  </ol>

  <!-- Wrapper for slides -->
  <div class="carousel-inner" role="listbox">
    <div class="item active">
      <img src="<?php $this->webroot?>img/freeze/Slides/freeze.png" alt="">
      <div class="carousel-caption text-right">
      	<h4 style="color: #111111">Accessible from anywhere</h4>
        <p>Our true cloud-hosted platform enables access  <br/>from any device on any platform,
        	<br/> be it desktop, tablet or mobile.<br/>
        	No hardware to install or maintain no additional  <br/>software licenses to buy.</p>
      </div>
    </div>
    <div class="item">
      <img src="<?php $this->webroot?>img/freeze/Slides/hand-freeze.png" alt="">
           <div class="carousel-caption">
        <h4 style="color: #111111">Cloud Based Subscription</h4>
        <p>Cloud Based Subscription model Online Time Attendance system with online Payroll software - A complete solution for organisations with Leave Management / Approval, Employee Login , Attendance Managment, PF Retruns, ESIC Returns eTDS Calculations and more. This software is integrated with leading Biometric Attendance Devices.
</p>
      </div>
    </div>
    
    <div class="item">
      <img src="<?php $this->webroot?>img/freeze/Slides/device.png" alt="">
           <div class="carousel-caption">
        <h4 style="color: #111111">Attendance Management</h4>
        <p>Auto sync your attendance devices with Forsight’s payroll software and never make any manual entry for attendance. Alternatively, import attendance data of thousands of employees within minutes with excel import option.An attendance dashboard highlights upwards and downwards trends to help keep a simple overview of your Company punctuality</p>
      </div>
    </div>
    
  </div>

  <!-- Controls -->
  <!-- <a class="left carousel-control" href="#carousel-example-generic" role="button" data-slide="prev">
    <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
    <span class="sr-only">Previous</span>
  </a>
  <a class="right carousel-control" href="#carousel-example-generic" role="button" data-slide="next">
    <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
    <span class="sr-only">Next</span>
  </a> -->
</div>
						 </div>
				<div class="col-lg-3 col-md-3">
				 
				 
				 
<?php echo $this->fetch('content'); ?>
				</div>
						
						</div>
				</div>
		</div>		
               
     

    </header>


    <div class="wrapper">

        

        <section id="about">
            <div class="container">
                
                <div class="section-heading scrollpoint sp-effect3">
                    <h1>About Us</h1>
                    <div class="divider"></div>
                   
                </div>

                <div class="row">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <div class="about-item scrollpoint sp-effect2">
                            <i class="fa fa-download fa-2x"></i>
                            <h3>Forsight Payroll System</h3>
                            <p>Forsigt is a unique subscription based online payroll software system that lets you process monthly payroll calculations, manage statutory compliances, and generate multiple HR, TDS, Statutory & MIS reports, in quick easy steps.

In fact you can access Forsight anywhere & anytime from any computer which has an internet connection. .</p>
                        </div>
                    </div>
                    
                </div>
            </div>
        </section>

        <section id="features">
            <div class="container">
                <div class="section-heading scrollpoint sp-effect3">
                    <h1>Features</h1>
                    <div class="divider"></div>
                    <p>Learn more about this feature packed App</p>
                </div>
                <div class="row">
                    <div class="col-md-4 col-sm-4 scrollpoint sp-effect1">
                        <div class="media media-left feature">
                            <a class="pull-right" href="#">
                                <i class="fa fa-cogs fa-2x"></i>
                            </a>
                            <div class="media-body">
                                <h3 class="media-heading">Employee Dashboards </h3>
                                Analyze trends in your salaries and income tax deductions with multiple dashboards on your salary structure, and income tax declarations. Make informed decisions and plan your savings in a better way.
                            </div>
                        </div>
                        <div class="media media-left feature">
                            <a class="pull-right" href="#">
                                <i class="fa fa-envelope fa-2x"></i>
                            </a>
                            <div class="media-body">
                                <h3 class="media-heading">Leave Management</h3>
                                Plan, apply, and track your leave status online with Forsight’s robust leave management software enabled with workflows. View complete history of your leaves, apply for desired type of leaves, and get regular updates on approval/ rejection of your leaves on your email.
                            </div>
                        </div>
                        <div class="media media-left feature">
                            <a class="pull-right" href="#">
                                <i class="fa fa-users fa-2x"></i>
                            </a>
                            <div class="media-body">
                                <h3 class="media-heading">Click Payroll Process </h3>
                                Select month and branches for which payroll is to be processed, click on process payroll button, and view payroll outputs of thousands of employees within minutes. It’s that simple! 
                            </div>
                        </div>
                        <!-- <div class="media media-left feature">
                            <a class="pull-right" href="#">
                                <i class="fa fa-comments fa-2x"></i>
                            </a>
                            <div class="media-body">
                                <h3 class="media-heading">Live Chat Messages</h3>
                                Lorem ipsum dolor sit amet.
                            </div>
                        </div>
                        <div class="media media-left feature">
                            <a class="pull-right" href="#">
                                <i class="fa fa-calendar fa-2x"></i>
                            </a>
                            <div class="media-body">
                                <h3 class="media-heading">Calendar / Planner</h3>
                                Lorem ipsum dolor sit amet.
                            </div>
                        </div> -->
                    </div>
                    <div class="col-md-4 col-sm-4" >
                        <img src="img/freeze/iphone-freeze.png" class="img-responsive scrollpoint sp-effect5" alt="">
                    </div>
                    <div class="col-md-4 col-sm-4 scrollpoint sp-effect2">
                        <div class="media feature">
                            <a class="pull-left" href="#">
                                <i class="fa fa-map-marker fa-2x"></i>
                            </a>
                            <div class="media-body">
                                <h3 class="media-heading">Pay Slips</h3>
                               View, print or email your pay slips from anywhere, anytime and make sure that you get it when you need it. Get freedom from requesting and waiting for your pay slips. Choose from standard pay slips templates or design your own pay slip format. 
                            </div>
                        </div>
                        <div class="media feature">
                            <a class="pull-left" href="#">
                                <i class="fa fa-film fa-2x"></i>
                            </a>
                            <div class="media-body">
                                <h3 class="media-heading">Reports</h3>
                                View and compare your annual salary statements and income tax statements for multiple years. Access other reports like digitally signed Form 16 and PF reports within click of a button.
                            </div>
                        </div>
                        <div class="media feature">
                            <a class="pull-left" href="#">
                                <i class="fa fa-compass fa-2x"></i>
                            </a>
                            <div class="media-body">
                                <h3 class="media-heading">Mass Mailing to Employees </h3>
                                Communicate urgent notice or send important documents to all employees of your company by Forsight’s bulk email tool. Alternatively, filter recipients by job type, departments, designations, locations, levels, or cost centers and send mails to only targeted employees
                            </div>
                        </div>
                        <!-- <div class="media feature">
                            <a class="pull-left" href="#">
                                <i class="fa fa-picture-o fa-2x"></i>
                            </a>
                            <div class="media-body">
                                <h3 class="media-heading">Weather on-the-go</h3>
                                Lorem ipsum dolor sit amet.
                            </div>
                        </div>
                        <div class="media active feature">
                            <a class="pull-left" href="#">
                                <i class="fa fa-plus fa-2x"></i>
                            </a>
                            <div class="media-body">
                                <h3 class="media-heading">And much more!</h3>
                                Lorem ipsum dolor sit amet.
                            </div>
                        </div> -->
                    </div>
                </div>
            </div>
        </section>

        <section id="reviews">
            <div class="container">
                <div class="section-heading inverse scrollpoint sp-effect3">
                    <h1>Reviews</h1>
                    <div class="divider"></div>
                    <p>Read What's The People Are Saying About Us</p>
                </div>
                <div class="row">
                    <div class="col-md-10 col-md-push-1 scrollpoint sp-effect3">
                        <div class="review-filtering">
                            <div class="review">
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="review-person">
                                            <img src="http://api.randomuser.me/portraits/women/94.jpg" alt="" class="img-responsive">
                                        </div>
                                    </div>
                                    <div class="col-md-10">
                                        <div class="review-comment">
                                            <h3>“I love Oleose, I highly recommend it, Everyone Try It Now�?</h3>
                                            <p>
                                                - Krin Fox
                                                <span>
                                                    <i class="fa fa-star fa-lg"></i>
                                                    <i class="fa fa-star fa-lg"></i>
                                                    <i class="fa fa-star fa-lg"></i>
                                                    <i class="fa fa-star fa-lg"></i>
                                                    <i class="fa fa-star-o fa-lg"></i>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="review rollitin">
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="review-person">
                                            <img src="http://api.randomuser.me/portraits/men/70.jpg" alt="" class="img-responsive">
                                        </div>
                                    </div>
                                    <div class="col-md-10">
                                        <div class="review-comment">
                                            <h3>“Oleaose Is The Best Stable, Fast App I Have Ever Experienced�?</h3>
                                            <p>
                                                - Theodore Willis
                                                <span>
                                                    <i class="fa fa-star fa-lg"></i>
                                                    <i class="fa fa-star fa-lg"></i>
                                                    <i class="fa fa-star fa-lg"></i>
                                                    <i class="fa fa-star-half-o fa-lg"></i>
                                                    <i class="fa fa-star-o fa-lg"></i>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="review rollitin">
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="review-person">
                                            <img src="http://api.randomuser.me/portraits/men/93.jpg" alt="" class="img-responsive">
                                        </div>
                                    </div>
                                    <div class="col-md-10">
                                        <div class="review-comment">
                                            <h3>“Keep It Up Guys Your Work Rules, Cheers :)�?</h3>
                                            <p>
                                                - Ricky Grant
                                                <span>
                                                    <i class="fa fa-star fa-lg"></i>
                                                    <i class="fa fa-star fa-lg"></i>
                                                    <i class="fa fa-star fa-lg"></i>
                                                    <i class="fa fa-star-half-o fa-lg"></i>
                                                    <i class="fa fa-star-o fa-lg"></i>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- <section id="screens">
            <div class="container">

                <div class="section-heading scrollpoint sp-effect3">
                    <h1>Screens</h1>
                    <div class="divider"></div>
                    <p>See what’s included in the App</p>
                </div>

                <div class="filter scrollpoint sp-effect3">
                    <a href="javascript:void(0)" class="button js-filter-all active">All Screens</a>
                    <a href="javascript:void(0)" class="button js-filter-one">User Access</a>
                    <a href="javascript:void(0)" class="button js-filter-two">Social Network</a>
                    <a href="javascript:void(0)" class="button js-filter-three">Media Players</a>
                </div>
                <div class="slider filtering scrollpoint sp-effect5" >
                    <div class="one">
                        <img src="img/freeze/screens/profile.jpg" alt="">
                        <h4>Profile Page</h4>
                    </div>
                    <div class="two">
                        <img src="img/freeze/screens/menu.jpg" alt="">
                        <h4>Toggel Menu</h4>
                    </div>
                    <div class="three">
                        <img src="img/freeze/screens/weather.jpg" alt="">
                        <h4>Weather Forcast</h4>
                    </div>
                    <div class="one">
                        <img src="img/freeze/screens/signup.jpg" alt="">
                        <h4>Sign Up</h4>
                    </div>
                    <div class="one">
                        <img src="img/freeze/screens/calendar.jpg" alt="">
                        <h4>Event Calendar</h4>
                    </div>
                    <div class="two">
                        <img src="img/freeze/screens/options.jpg" alt="">
                        <h4>Some Options</h4>
                    </div>
                    <div class="three">
                        <img src="img/freeze/screens/sales.jpg" alt="">
                        <h4>Sales Analysis</h4>
                    </div>
                </div>
            </div>
        </section> -->

        <!-- <section id="demo">
            <div class="container">
                <div class="section-heading scrollpoint sp-effect3">
                    <h1>Demo</h1>
                    <div class="divider"></div>
                    <p>Take a closer look in more detail</p>
                </div>
                <div class="row">
                    <div class="col-md-8 col-md-offset-2 scrollpoint sp-effect2">
                        <div class="video-container" >
                            <iframe src="http://player.vimeo.com/video/70984663"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </section> -->

        <section id="getApp">
            <div class="container-fluid">
                <div class="section-heading inverse scrollpoint sp-effect3">
                    <h1>Get App</h1>
                    <div class="divider"></div>
                    <p>Choose your native platform and get started!</p>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="hanging-phone scrollpoint sp-effect2 hidden-xs">
                            <img src="img/freeze/freeze-angled2.png" alt="">
                        </div>
                        <div class="platforms">
                            <a href="#" class="btn btn-primary inverse scrollpoint sp-effect1">
                                <i class="fa fa-android fa-3x pull-left"></i>
                                <span>Download for</span><br>
                                <b>Android</b>
                            </a>
                            
                                <a href="#" class="btn btn-primary inverse scrollpoint sp-effect2">
                                    <i class="fa fa-apple fa-3x pull-left"></i>
                                    <span>Download for</span><br>
                                    <b>Apple IOS</b>
                                </a>
                        </div>

                    </div>
                </div>

                

            </div>
        </section>

        <section id="support" class="doublediagonal">
            <div class="container">
                <div class="section-heading scrollpoint sp-effect3">
                    <h1>Support</h1>
                    <div class="divider"></div>
                    <p>For more info and support, contact us!</p>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-8 col-sm-8 scrollpoint sp-effect1">
                                <form role="form">
                                    <div class="form-group">
                                        <input type="text" class="form-control" placeholder="Your name">
                                    </div>
                                    <div class="form-group">
                                        <input type="email" class="form-control" placeholder="Your email">
                                    </div>
                                    <div class="form-group">
                                        <textarea cols="30" rows="10" class="form-control" placeholder="Your message"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-lg">Submit</button>
                                </form>
                            </div>
                            <div class="col-md-4 col-sm-4 contact-details scrollpoint sp-effect2">
                                <div class="media">
                                    <a class="pull-left" href="#" >
                                        <i class="fa fa-map-marker fa-2x"></i>
                                    </a>
                                    <div class="media-body">
                                        <h4 class="media-heading">4, Some street, California, USA</h4>
                                    </div>
                                </div>
                                <div class="media">
                                    <a class="pull-left" href="#" >
                                        <i class="fa fa-envelope fa-2x"></i>
                                    </a>
                                    <div class="media-body">
                                        <h4 class="media-heading">
                                            <a href="mailto:support@oleose.com">support@oleose.com</a>
                                        </h4>
                                    </div>
                                </div>
                                <div class="media">
                                    <a class="pull-left" href="#" >
                                        <i class="fa fa-phone fa-2x"></i>
                                    </a>
                                    <div class="media-body">
                                        <h4 class="media-heading">+1 234 567890</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <footer>
            <div class="container">
                <a href="#" class="scrollpoint sp-effect3">
                    <!-- <img src="img/freeze/logo.png" alt="" class="logo"> -->
                </a>
                <div class="social">
                    <a href="#" class="scrollpoint sp-effect3"><i class="fa fa-twitter fa-lg"></i></a>
                    <a href="#" class="scrollpoint sp-effect3"><i class="fa fa-google-plus fa-lg"></i></a>
                    <a href="#" class="scrollpoint sp-effect3"><i class="fa fa-facebook fa-lg"></i></a>
                </div>
                <div class="rights">
                    <p>Forsight Copyright &copy; 2014</p>
                    
                </div>
            </div>
        </footer>

    </div>

</body>
<style>
       body::selection {
    background-color: #3498db; /* Change to your desired color */
    color: white; /* Optional: change the text color */
}
    </style>
</html>
