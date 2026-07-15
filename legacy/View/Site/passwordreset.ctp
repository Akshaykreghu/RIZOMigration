<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password | Rizo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Favicon -->
    <link rel="icon" href="<?php echo $this->webroot; ?>newlogin/img/rizo.png">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="<?php echo $this->webroot; ?>newlogin/css/bootstrap.min.css">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-blue: #1e88e5;
            --navy-dark: #1e516e;
            --accent-blue: #e3f2fd;
            --text-muted: #5a6b7d;
        }

        html, body {
            height: 100%;
            margin: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f8fafc;
            overflow-x: hidden;
        }

        /* Background blobs */
        .bg-blobs {
            position: fixed;
            inset: 0;
            z-index: -1;
        }

        .blob {
            position: absolute;
            width: 450px;
            height: 450px;
            background: var(--accent-blue);
            filter: blur(80px);
            border-radius: 50%;
            opacity: 0.5;
            animation: move 20s infinite alternate;
        }

        .blob-1 { top: -10%; left: -10%; }
        .blob-2 { bottom: -10%; right: -10%; background: #d1e9ff; }

        @keyframes move {
            from { transform: translate(0,0) scale(1); }
            to { transform: translate(50px,100px) scale(1.1); }
        }

        /* Layout */
        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .login-container {
            max-width: 1200px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 40px;
        }

        /* Left */
        .login-left {
            flex: 1;
            animation: fadeIn 0.8s ease;
        }

        .login-left img {
            height: 70px;
            margin-bottom: 30px;
        }

        .login-left h1 {
            font-size: 48px;
            font-weight: 800;
            color: var(--navy-dark);
            line-height: 1.1;
        }

        .login-left h1 span {
            color: var(--primary-blue);
        }

        .login-left p {
            font-size: 18px;
            color: var(--text-muted);
            max-width: 450px;
            line-height: 1.6;
        }

        /* Card */
        .login-card-wrapper {
            flex: 0 0 480px;
        }

        .login-card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: 32px;
            padding: 45px;
            box-shadow: 0 40px 100px rgba(30,81,110,0.1);
        }

        .login-card h3 {
            font-size: 24px;
            font-weight: 800;
            color: var(--navy-dark);
        }

        .instruction {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 30px;
        }

        /* Form */
        .form-group label {
            font-size: 13px;
            font-weight: 700;
            color: var(--navy-dark);
            margin-bottom: 6px;
            display: block;
        }

        .form-control {
            height: 52px;
            border-radius: 14px;
            border: 2px solid #f0f3f6;
            background: #f8fafc;
            padding: 0 18px;
            margin-bottom: 18px;
        }

        .form-control:focus {
            background: #fff;
            border-color: var(--primary-blue);
            outline: none;
            box-shadow: 0 10px 20px rgba(30,136,229,0.1);
        }

        /* Buttons */
        .form-button {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }

        .ibtn {
            flex: 1;
            height: 52px;
            border-radius: 14px;
            font-weight: 700;
            font-size: 15px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .ibtn.btn-reset {
            background: linear-gradient(135deg, var(--navy-dark), #2c6e91);
            color: #fff;
        }

        .ibtn.cancel {
            background: #d0e1ea;
            color: #000;
        }

        .alert {
            border-radius: 14px;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .page-links {
            text-align: center;
            margin-top: 25px;
        }

        .page-links a {
            color: var(--primary-blue);
            font-weight: 600;
            text-decoration: none;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive */
        @media (max-width: 992px) {
            .login-container {
                flex-direction: column;
                text-align: center;
            }

            .login-card-wrapper {
                width: 100%;
                max-width: 480px;
            }

            .login-left p {
                margin: 0 auto;
            }
        }
    </style>
</head>

<body>

<div class="bg-blobs">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
</div>

<div class="login-page">
    <div class="login-container">

        <!-- LEFT -->
        <div class="login-left">
            <img src="<?php echo $this->webroot; ?>newlogin/img/rizonew.png" alt="Rizo Logo">
            <h1>Reset your <span>Access.</span></h1>
            <p>
                Enter your registered username and email address.
                We’ll send you a secure link to reset your password.
            </p>
        </div>

        <!-- RIGHT -->
        <div class="login-card-wrapper">
            <div class="login-card">
                <h3>Forgot Password</h3>
                <p class="instruction">
                    To reset your password, enter the details you use to sign in to My Payroll Master.
                </p>

                <form method="post" action="<?php echo $this->webroot; ?>Site/sendtoken" autocomplete="off">

                    <?php if (isset($msg)) { ?>
                        <div class="alert alert-danger">
                            <?php foreach ($msg as $ms) { ?>
                                <div><?php echo $ms; ?></div>
                            <?php } ?>
                        </div>
                    <?php } ?>

                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" placeholder="Username" required>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Email address" required>
                    </div>

                    <div class="form-button">
                        <a href="<?php echo $this->webroot; ?>" class="ibtn cancel">Cancel</a>
                        <button type="submit" class="ibtn btn-reset">Submit</button>
                    </div>

                    <div class="page-links">
                        <a href="<?php echo $this->webroot; ?>">Login to account</a>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>

<script type="text/javascript">
$(document).ready(function() { 
$.validate({
    modules : '  security',
  
  });
 }); 
</script>

</body>
</html>
