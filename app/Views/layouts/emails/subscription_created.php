<h1 class="heading" style="color: #333; font-size: 24px; margin-bottom: 20px;">Welcome to <?= $emailData['app_name'] ?>!</h1>

<p class="subheading" style="font-size: 18px; color: #555;">Dear <?= $emailData['user_name'] ?>,</p>

<p style="margin-bottom: 20px;">Thank you for subscribing to our service! Your subscription has been successfully created and is now processing the payment.</p>
<p style="margin-bottom: 20px;">Once the payment is completed, your subscription will become active.</p>

<div class="warning-box" style="background-color: #fff3cd; border-color: #ffeeba; color: #856404; border-radius: 4px; padding: 15px; margin: 2px !important;">
    <h3><span class="icon icon-warning" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #ffc107;">&#9888;</span> Subscription Details</h3>
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <tr>
            <th>Package</th>
            <td><?= $emailData['package_name'] ?></td>
        </tr>
        <tr>
            <th>Status</th>
            <td><span class="status status-suspended" style="display: inline-block; padding: 5px 10px; border-radius: 3px; font-weight: bold; background: #ffc107; color: #000000;">Pending</span></td>
        </tr>
        <tr>
            <th><?= lang('Pages.Start_Date') ?></th>
            <td><?= $emailData['start_date'] ?></td>
        </tr>
        <tr>
            <th>Next Billing</th>
            <td><?= $emailData['next_billing_time'] ?></td>
        </tr>
        <tr>
            <th>Amount</th>
            <td><?= $emailData['amount'] ?></td>
        </tr>
    </table>
</div>

<p style="margin-top: 20px;">You can access all the features included in your subscription package once active. To view your subscription details or manage your account, click the button below:</p>

<p style="text-align: center; margin-top: 30px;">
    <a href="<?= $emailData['subscription_url'] ?>" class="button-primary" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: #ffffff !important; text-decoration: none; border-radius: 3px; margin: 20px 0;">View Subscription Details</a>
</p>

<?php if($emailData['payment_instructions']) : ?>
    <div class="info-box" style="background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; border-radius: 4px; padding: 15px; margin: 2px !important;">
        <h3><span class="icon icon-info" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #17a2b8;">&#8505;</span> Payment Instructions</h3>
        <p style="margin-top: 10px;"><?= nl2br(esc($emailData['payment_instructions'])) ?></p>

        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
            <tr>
                <th>Package</th>
                <td><?= $emailData['package_name'] ?></td>
            </tr>
            <tr>
                <th>Amount Due</th>
                <td><?= $emailData['amount'] ?></td>
            </tr>
            <tr>
                <th>Payment Reference ID</th>
                <td><?= $emailData['payment_reference'] ?></td>
            </tr>
        </table>

        <p style="text-align: center; margin-top: 20px;">
            Once you have completed the payment, please enter the transaction reference for our verification.
        </p>
        <p style="text-align: center; margin-top: 20px;">
            <a href="<?= $emailData['subscription_url'] ?>" class="button-primary" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: #ffffff !important; text-decoration: none; border-radius: 3px; margin: 20px 0;">Complete Payment</a>
        </p>
    </div>
<?php else : ?>
    <div class="info-box" style="background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; border-radius: 4px; padding: 15px; margin: 2px !important;">
        <h3><span class="icon icon-info" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #17a2b8;">&#8505;</span> What's Next?</h3>
        <ul style="margin-top: 10px; padding-left: 20px;">
            <li>Explore all the features available in your subscription package</li>
            <li>Set up your account preferences and settings</li>
            <li>Contact our support team if you need any assistance</li>
        </ul>
    </div>
<?php endif; ?>

<p style="margin-top: 20px;">If you have any questions or need help getting started, our support team is here to help. You can reach us at <a href="mailto:<?= $emailData['support_email'] ?>" style="color: #007bff; text-decoration: none;"><?= $emailData['support_email'] ?></a>.</p>

<p class="signature" style="margin-top: 30px; font-style: italic;">
    Best regards,<br>
    The <?= $emailData['app_name'] ?> Team
</p>

<div class="notes" style="font-size: 15px; color: #666; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
    <p>Your subscription will automatically renew on <?= $emailData['next_billing_time'] ?>. You can cancel or modify your subscription at any time through your account dashboard.</p>
</div>
