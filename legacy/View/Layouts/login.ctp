<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rizo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" href="<?php echo $this->webroot; ?>newlogin/img/rizo.png">
    <link rel="stylesheet" href="<?php echo $this->webroot; ?>newlogin/css/bootstrap.min.css">
    

    <style>
        :root {
            --primary-blue: #1e88e5;
            --navy-dark: #1e516e;
            --bg-light: #f8faff;
        }

        html, body {
            height: 100%;
            margin: 0;
            /* font-family: "Segoe UI", Roboto, Helvetica, Arial, sans-serif; */
            font-family: "Plus Jakarta Sans", "Segoe UI", sans-serif !important;
            background-color: var(--bg-light);
        }

        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            /* Creates a soft visual depth */
            background: radial-gradient(circle at top right, #e3f2fd, transparent),
                        radial-gradient(circle at bottom left, #e1f5fe, transparent);
        }

        .login-container {
            display: flex;
            width: 100%;
            max-width: 1260px;
            padding: 20px;
            align-items: center;
            justify-content: space-between;
        }

        /* LEFT SECTION */
        .login-left {
            flex: 1;
            padding-right: 60px;
        }

        .login-left img {
            height: 60px;
            margin-bottom: 40px;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.05));
        }

        .login-left h3 {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary-blue);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 15px;
        }

        .login-left h1 {
            font-size: 48px;
            font-weight: 800;
            color: #1a1a1a;
            line-height: 1.1;
            margin-bottom: 25px;
        }

        .login-left p {
            font-size: 18px;
            color: #555;
            line-height: 1.7;
            max-width: 450px;
        }

        /* RIGHT CARD */
        .login-card-wrapper {
            flex: 0 0 460px;
        }

        .login-card {
            background: #ffffff;
            border-radius: 30px;
            padding: 50px 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.8);
        }

        .login-card h3 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 30px;
            color: #333;
            text-align: left;
        }

        /* FORM ELEMENTS */
        .form-control {
            height: 52px;
            border-radius: 12px;
            border: 1.5px solid #ececec;
            margin-bottom: 20px;
            padding-left: 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 4px rgba(30, 136, 229, 0.1);
            outline: none;
        }

        /* CakePHP Login Button Styling override */
        #formLogin .form-button {
            display: flex;
            justify-content: space-between;
            flex-direction: row-reverse;
            align-items: center;
            margin-top: 20px;
        }

        #formLogin #login {
            padding: 12px 35px;
            border-radius: 12px;
            border: none;
            background-color: var(--navy-dark);
            color: white;
            font-weight: 600;
            transition: transform 0.2s, background 0.2s;
            cursor: pointer;
        }

        #formLogin #login:hover {
            background-color: #143a4f;
            transform: translateY(-2px);
        }

        #formLogin label {
            margin-left: 8px;
            font-size: 14px;
            color: #666;
            cursor: pointer;
        }


        .form-signin .form-button {
            display: flex;
            justify-content: space-between;
            flex-direction: row-reverse;
            align-items: center;
            margin-top: 20px;
        }

        .form-signin #login {
            padding: 12px 35px;
            border-radius: 12px;
            border: none;
            background-color: var(--navy-dark);
            color: white;
            font-weight: 600;
            transition: transform 0.2s, background 0.2s;
            cursor: pointer;
        }

        .form-signin #login:hover {
            background-color: #143a4f;
            transform: translateY(-2px);
        }

        .form-signin label {
            margin-left: 8px;
            font-size: 14px;
            color: #666;
            cursor: pointer;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .login-container {
                flex-direction: column;
                text-align: center;
            }

            .login-left {
                padding-right: 0;
                margin-bottom: 50px;
            }

            .login-left p {
                margin: 0 auto;
            }

            .login-card-wrapper {
                width: 100%;
                max-width: 460px;
            }
        }

        /* Visual embellishment to fill empty space */
        .bg-decoration {
            position: absolute;
            z-index: -1;
            filter: blur(80px);
            opacity: 0.4;
        }
    </style>
</head>

<body>

<div class="login-page">
    
    <div class="login-container">
        
        <!-- LEFT CONTENT -->
        <div class="login-left">
            <img src="<?php echo $this->webroot; ?>newlogin/img/rizonew.png" alt="Rizo">

            <h3>MPM - HR is now Rizo</h3>

            <h1>Welcome back to Rizo.<br>Built for People at Work.</h1>

            <p>
                Your everyday platform for attendance, leaves, payslips & performance updates.
            </p>
        </div>

        <!-- RIGHT LOGIN CARD -->
        <div class="login-card-wrapper">
            <div class="login-card">
                <h3>SIGN IN</h3>

                <!-- CakePHP LOGIN FORM -->
                <div id="content-area">
                    <?php echo $this->fetch('content'); ?>
                </div>
            </div>
        </div>

    </div>

</div>
    <script src="<?php echo $this->webroot; ?>newlogin/js/jquery.min.js"></script>
    <script src="<?php echo $this->webroot; ?>newlogin/js/popper.min.js"></script>
    <script src="<?php echo $this->webroot; ?>newlogin/js/bootstrap.min.js"></script>
    <script src="<?php echo $this->webroot; ?>newlogin/js/main.js"></script>
    <script>
        function submitForm() {
            var form = new FormData();
            var user_id = $("#user_id").val();
            form.append("user_id", user_id);
            form.append("action", "leave_request");
            // event.preventDefault();
            $.ajax({
                // "crossDomain": true,
                url: 'https://mpmapps.mypayrollmaster.online/compnyURL',
                method: "POST",
                "processData": false,
                "contentType": false,
                "mimeType": "multipart/form-data",
                "data": form,
                success: function(res) {
                    var response = JSON.parse(res);
                    console.log(response);
                    $("#formLogin").attr("action", response.data + 'Site/login');
                    setTimeout(() => {
                        $("#formLogin").submit();
                    }, 1000);
                }
            });
        }
    </script>
</body>

</html>