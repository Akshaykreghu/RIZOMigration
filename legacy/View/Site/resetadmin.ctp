<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Reset | Rizo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" href="<?php echo $this->webroot; ?>newlogin/img/rizo.png">
    <link rel="stylesheet" href="<?php echo $this->webroot; ?>newlogin/css/bootstrap.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-blue: #1e88e5;
            --navy-dark: #1e516e;
            --text-muted: #5a6b7d;
        }

        body {
            margin: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f8fafc;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .reset-card {
            background: #fff;
            border-radius: 32px;
            padding: 50px 45px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 40px 90px rgba(30,81,110,0.12);
            animation: fadeIn 0.6s ease;
        }

        .reset-card h3 {
            font-size: 26px;
            font-weight: 800;
            color: var(--navy-dark);
            margin-bottom: 25px;
            text-align: center;
        }

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

        .alert {
            border-radius: 14px;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .form-button {
            display: flex;
            gap: 15px;
            margin-top: 30px;
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

        .ibtn.cancel {
            background: #d0e1ea;
            color: #000;
        }

        .ibtn.reset {
            background: linear-gradient(135deg, var(--navy-dark), #2c6e91);
            color: #fff;
        }

        .ibtn.reset:hover {
            box-shadow: 0 15px 30px rgba(30,81,110,0.25);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body>

<div class="reset-card">

    <h3>Password Reset</h3>

    <form method="post" autocomplete="off">

        <?php if (isset($msg)) { ?>
            <div class="alert alert-danger">
                <?php echo $msg; ?>
            </div>
        <?php } ?>

        <div class="form-group">
            <label>Current Password</label>
            <input
                type="password"
                class="form-control"
                name="oldpassword"
                id="oldpassword"
                placeholder="Current Password"
                data-validation="length"
                data-validation-length="min6">
        </div>

        <div class="form-group">
            <label>New Password</label>
            <input
                type="password"
                class="form-control"
                name="password"
                id="password"
                placeholder="New Password"
                data-validation="length"
                data-validation-length="min6">
        </div>

        <div class="form-group">
            <label>Confirm Password</label>
            <input
                type="password"
                class="form-control"
                name="password_confirm"
                id="password_confirm"
                placeholder="Confirm Password"
                data-validation="confirmation"
                data-validation-confirm="password"
                data-validation-error-msg="Password is not matching">
        </div>

        <div class="form-button">
            <a href="<?php echo $this->webroot; ?>" class="ibtn cancel">Cancel</a>
            <button type="submit" class="ibtn reset">Reset Password</button>
        </div>

    </form>

</div>



<script type="text/javascript">
    $(document).ready(function () {
        $.validate({
            modules: 'location, date, security, file',
        });


    });
    function  chkPassword()
    {
        var oldpassword = $('#oldpassword').val();
        $.ajax({
            url: 'UserCredentials/chekPassword/'+oldpassword,
            success: function (response) {
             //var data = $.parseJSON(response);
             alert(response);
//             if(parseInt(req) > data){
//               //alert("Insufficient Stock");
//               $("#errormsg1").html("You don't have enough stock for this transaction. Please select a lesser quantity and try again!!!");
//               $('#return_qty1').val('');
//             }else{
//               $("#errormsg1").html("");
//             }
            }
        });
    }
</script>

</body>
</html>
