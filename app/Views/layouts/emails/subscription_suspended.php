<h1 class="heading" style="color: #333; font-size: 24px; margin-bottom: 20px;">Subscription Suspended</h1>

<p class="subheading" style="font-size: 18px; color: #555;">Dear <?= $emailData['user_name'] ?>,</p>

<p style="margin-bottom: 20px;">We're writing to inform you that your subscription has been temporarily suspended. This usually occurs due to a payment issue or violation of our terms of service.</p>

<div class="warning-box" style="background-color: #fff3cd; border-color: #ffeeba; color: #856404; border-radius: 4px; padding: 15px; margin: 2px !important;">
    <h3><span class="icon icon-warning" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #ffc107;">&#9888;</span> Subscription Details</h3>
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <tr>
            <th>Package</th>
            <td><?= $emailData['package_name'] ?></td>
        </tr>
        <tr>
            <th>Status</th>
            <td><span class="status status-suspended" style="display: inline-block; padding: 5px 10px; border-radius: 3px; font-weight: bold; background: #ffc107; color: #000000;">Suspended</span></td>
        </tr>
    </table>
</div>

<div class="info-box" style="background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; border-radius: 4px; padding: 15px; margin: 2px !important;">
    <h3><span class="icon icon-info" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #17a2b8;">&#8505;</span> What This Means</h3>
    <ul style="margin-top: 10px; padding-left: 20px;">
        <li>Your access to subscription features has been temporarily suspended</li>
        <li>Any automated processes or integrations will be paused</li>
        <li>Your data is safely stored and will be accessible once the subscription is reactivated</li>
    </ul>
</div>

<h3 style="color: #333; margin-top: 30px;">How to Reactivate Your Subscription</h3>

<?php if (isset($payment_failed) && $payment_failed): ?>
<p style="margin-top: 20px;">Your subscription was suspended due to a failed payment attempt. To reactivate your subscription:</p>
<ol style="margin-top: 10px; padding-left: 20px;">
    <li>Update your payment information in your account settings</li>
    <li>Clear any outstanding payments</li>
    <li>Click the reactivate button below</li>
</ol>
<?php else: ?>
<p style="margin-top: 20px;">To reactivate your subscription, please contact our support team to resolve any issues with your account.</p>
<?php endif; ?>

<div class="info-box" style="background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; border-radius: 4px; padding: 15px; margin: 2px !important;">
    <h4><span class="icon icon-info" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #17a2b8;">&#8505;</span> Need Help?</h4>
    <p style="margin-top: 10px;">Our support team is here to help you resolve any issues and get your subscription reactivated as quickly as possible.</p>
    <ul style="margin-top: 10px; padding-left: 20px;">
        <li>Email: <a href="mailto:<?= $emailData['support_email'] ?>"><?= $emailData['support_email'] ?></a></li>
        <li>Support Hours: Monday - Friday, 9:00 AM - 5:00 PM EST</li>
        <li>Response Time: Within 24 hours</li>
    </ul>
</div>

<p style="text-align: center; margin-top: 30px;">
    <a href="<?= $emailData['subscription_url'] ?>" class="button-primary" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: #ffffff !important; text-decoration: none; border-radius: 3px; margin: 20px 0;">View Subscription Details</a>
</p>

<div class="info-box" style="background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; border-radius: 4px; padding: 15px; margin: 2px !important;">
    <h4><span class="icon icon-info" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #17a2b8;">&#8505;</span> Important Notice</h4>
    <p style="margin-top: 10px;">If your subscription remains suspended for more than 30 days, it may be automatically cancelled. To prevent this, please take action to resolve any outstanding issues as soon as possible.</p>
</div>

<p style="margin-top: 20px;">We value your business and hope to resolve this situation quickly.</p>

<p class="signature" style="margin-top: 30px; font-style: italic;">
    Best regards,<br>
    The <?= $emailData['app_name'] ?> Team
</p>

<div class="notes" style="font-size: 15px; color: #666; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
    <p>If you believe this suspension was made in error, please contact our support team immediately.</p>
</div>
