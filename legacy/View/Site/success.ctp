<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Link Sent | Rizo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" href="<?php echo $this->webroot; ?>newlogin/img/rizo.png">
    <link rel="stylesheet" href="<?php echo $this->webroot; ?>newlogin/css/bootstrap.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-blue: #1e88e5;
            --navy-dark: #1e516e;
            --success-green: #2ecc71;
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

        .success-card {
            background: #fff;
            border-radius: 32px;
            padding: 60px 45px;
            text-align: center;
            max-width: 460px;
            width: 100%;
            box-shadow: 0 40px 90px rgba(30,81,110,0.12);
            animation: fadeIn 0.6s ease;
        }

        .tick-circle {
            width: 90px;
            height: 90px;
            background: rgba(46,204,113,0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
        }

        .tick-circle::after {
            content: "✓";
            font-size: 42px;
            font-weight: 800;
            color: var(--success-green);
        }

        .success-card h3 {
            font-size: 26px;
            font-weight: 800;
            color: var(--navy-dark);
            margin-bottom: 10px;
        }

        .success-card p {
            font-size: 15px;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .btn-ok {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 50px;
            padding: 0 40px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--navy-dark), #2c6e91);
            color: #fff;
            font-weight: 700;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-ok:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(30,81,110,0.25);
            color: #fff;
            text-decoration: none;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body>

<div class="success-card">
    <div class="tick-circle"></div>

    <h3>Password link sent</h3>

    <p>
        We’ve sent a password reset link to your registered email address.
        Please check your inbox and follow the instructions to reset your password.
    </p>

    <a href="<?php echo $this->webroot; ?>" class="btn-ok">OK</a>
</div>

</body>
</html>

