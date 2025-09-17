<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $emailData['subject'] ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 100%;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .header {
            background: #f8f9fa;
            color: #6c757d;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .icon {
            display: inline-block;
            font-size: 1.5em;
            vertical-align: middle;
            margin-right: 10px;
        }
        .icon-success {
            color: #28a745;
        }
        .icon-warning {
            color: #ffc107;
        }
        .icon-info {
            color: #17a2b8;
        }
        .icon-danger {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 1.2em;
            height: 1.2em;
            font-size: 1.2rem;
            color: #dc3545;
        }
        .content {
            padding: 20px;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            border-radius: 0 0 5px 5px;
        }
        .button-primary, .button-success {
            display: inline-block;
            padding: 10px 20px;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 3px;
            margin: 20px 0;
        }
        .button-primary {
            background-color: #007bff;
        }
        .button-success {
            background-color: #28a745;
        }
        .success-box, .danger-box, .info-box, .warning-box {
            border-radius: 4px;
            padding: 15px;
            margin: 2px !important;
        }
        .success-box {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .danger-box {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .info-box {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }
        .warning-box {
            background-color: #fff3cd;
            border-color: #ffeeba;
            color: #856404;
        }
        .notes {
            font-size: 15px;
            color: #666;
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
        }
        .status-active {
            background: #28a745;
            color: #ffffff;
        }
        .status-suspended {
            background: #ffc107;
            color: #000000;
        }
        .status-cancelled {
            background: #dc3545;
            color: #ffffff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px 15px;
            border: 1px solid #dee2e6;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9em;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        tr:hover {
            background-color: #f2f2f2;
        }
        @media screen and (max-width: 600px) {
            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }
    </style>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 0; background-color: #f4f4f4;">
    <div class="container" style="max-width: 100%; margin: 20px auto; background: #ffffff; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <div class="header" style="background: #f8f9fa; color: #6c757d; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;">
            <h2><?= $emailData['app_name'] ?></h2>
        </div>
        <div class="content" style="padding: 20px;">
            <?= $content ?? '' ?>
        </div>
        <div class="footer" style="background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #6c757d; border-radius: 0 0 5px 5px;">
            <p>
                <?= $emailData['app_name'] ?><br>
                <?= $emailData['company_name'] ?><br>
                <?= $emailData['company_address'] ?><br>
                Support: <a href="mailto:<?= $emailData['support_email'] ?>"><?= $emailData['support_email'] ?></a>
            </p>
            <p>
                This email was sent to <?= $emailData['user_name'] ?> (<?= $emailData['user_email'] ?>).<br>
                If you have any questions, please contact our support team.
            </p>
            <p>
                To ensure you receive all our emails, please add <strong><?= $emailData['from_email'] ?></strong> to your email whitelist or safe sender list.
            </p>
        </div>
    </div>
</body>
</html>
