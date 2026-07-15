<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Forsight | Log in</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.4 -->
    <link href="<?php echo $this->webroot; ?>css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- Font Awesome Icons -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <!-- Theme style -->
    <link href="css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
    <!-- iCheck -->
    <link href="<?php echo $this->webroot; ?>plugins/iCheck/square/blue.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo $this->webroot; ?>css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <!-- Ionicons 2.0.0 -->
    <link href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css" rel="stylesheet" type="text/css" />
    <!-- Theme style -->
    <link href="<?php echo $this->webroot; ?>css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
    <!-- AdminLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    <link href="<?php echo $this->webroot; ?>css/skins/_all-skins.min.css" rel="stylesheet" type="text/css" />
        <script src="<?php echo $this->webroot; ?>plugins/jQuery/jQuery-2.1.4.min.js"></script>
    <!-- jQuery UI 1.11.4 -->
    <script src="<?php echo $this->webroot; ?>plugins/jquery-ui.min.js" type="text/javascript"></script>
    
    <script src="<?php echo $this->webroot; ?>plugins/form-validator/jquery.form-validator.js" type="text/javascript"></script>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
 
<div class="wrapper" style="background:#0070C0;">
  <header class="main-header skin-blue sidebar-mini">
    <!-- Logo -->
    <a href="#" class="logo" style="color:#ffffff;">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><b>M</b>PM</span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><b>My Payroll Master</b></span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    
  </header>
</div>
  <body class="login-page">
    	<?php echo $this->fetch('content'); ?>
<style>
            .login-page {
                background:#ffffff;
                background-size: cover;
            }
        </style>
    <!-- jQuery 2.1.4 -->
    <script src="<?php echo $this->webroot; ?>plugins/jQuery/jQuery-2.1.4.min.js" type="text/javascript"></script>
    <!-- Bootstrap 3.3.2 JS -->
    <script src="<?php echo $this->webroot; ?>bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <!-- iCheck -->
    <script src="<?php echo $this->webroot; ?>plugins/iCheck/icheck.min.js" type="text/javascript"></script>
    <script>
      $(function () {
        $('input').iCheck({
          checkboxClass: 'icheckbox_square-blue',
          radioClass: 'iradio_square-blue',
          increaseArea: '20%' // optional
        });
      });
    </script>
  </body>
</html>