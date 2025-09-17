<h1 class="heading" style="color: #333; font-size: 24px; margin-bottom: 20px;">Payment Failed Notice</h1>

<p class="subheading" style="font-size: 18px; color: #555;">Dear <?= $emailData['user_name'] ?>,</p>

<p style="margin-bottom: 20px;">We were unable to process your subscription payment. This could be due to insufficient funds, an expired card, or other payment-related issues.</p>

<div class="danger-box" style="background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; border-radius: 4px; padding: 15px; margin: 2px !important;">
    <h3><span class="icon icon-danger" style="display: inline-flex; justify-content: center; align-items: center; width: 1.2em; height: 1.2em; font-size: 1.2rem; color: #dc3545;">&#10071;</span> Payment Details</h3>
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <tr>
            <th>Attempted Date</th>
            <td><?= $emailData['payment_date'] ?></td>
        </tr>
        <tr>
            <th>Amount</th>
            <td><?= $emailData['amount'] ?></td>
        </tr>
        <tr>
            <th>Package</th>
            <td><?= $emailData['package_name'] ?></td>
        </tr>
        <tr>
            <th>Status</th>
            <td><span class="status status-cancelled" style="display: inline-block; padding: 5px 10px; border-radius: 3px; font-weight: bold; background: #dc3545; color: #ffffff;">Failed</span></td>
        </tr>
    </table>
</div>

<div class="info-box" style="background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; border-radius: 4px; padding: 15px; margin: 2px !important;">
    <h3><span class="icon icon-info" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #17a2b8;">&#8505;</span> Action Required</h3>
    <p style="margin-top: 10px;">To prevent any interruption in your service, please take one of the following actions:</p>
    <ol style="margin-top: 10px; padding-left: 20px;">
        <li>Update your payment method</li>
        <li>Ensure sufficient funds are available</li>
        <li>Contact your payment provider if you believe this is an error</li>
    </ol>
</div>

<div class="warning-box" style="background-color: #fff3cd; border-color: #ffeeba; color: #856404; border-radius: 4px; padding: 15px; margin: 2px !important;">
    <h4><span class="icon icon-warning" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #ffc107;">&#9888;</span> Important Timeline</h4>
    <ul style="margin-top: 10px; padding-left: 20px;">
        <li>We will attempt to process the payment again eveyday for 3 days</li>
        <li>Your subscription will remain active during this grace period</li>
        <li>If payment fails again, your subscription may be suspended</li>
    </ul>
</div>

<h3 style="color: #333; margin-top: 30px;">Next Steps</h3>
<ol style="margin-top: 10px; padding-left: 20px;">
    <li>Review your payment information in your account settings</li>
    <li>Update your payment method if necessary</li>
    <li>Ensure your billing information is current</li>
    <li>Contact support if you need assistance</li>
</ol>

<div class="info-box" style="background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; border-radius: 4px; padding: 15px; margin: 2px !important;">
    <h4><span class="icon icon-info" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #17a2b8;">&#8505;</span> Need Help?</h4>
    <p style="margin-top: 10px;">Our support team is available to assist you with any payment-related issues:</p>
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
    <h4><span class="icon icon-info" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #17a2b8;">&#8505;</span> Payment Retry Schedule</h4>
    <ul style="margin-top: 10px; padding-left: 20px;">
        <li>1st retry: In 1 day — <?= $emailData['retry_date_1'] ?></li>
        <li>2nd retry: In 2 days — <?= $emailData['retry_date_2'] ?></li>
        <li>Final retry: In 3 days — <?= $emailData['retry_date_3'] ?></li>
    </ul>
    <p>After the final retry attempt, if payment is still unsuccessful, your subscription may be suspended.</p>
</div>

<p style="margin-top: 20px;">We value your business and hope to resolve this payment issue quickly.</p>

<p class="signature" style="margin-top: 30px; font-style: italic;">
    Best regards,<br>
    The <?= $emailData['app_name'] ?> Team
</p>

<div class="notes" style="font-size: 15px; color: #666; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
    <p>If you've already updated your payment information or believe this email was sent in error, please disregard this notice.</p>
</div>

<?php if (isset($alternative_packages) && $alternative_packages): ?>
<div class="info-box" style="background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; border-radius: 4px; padding: 15px; margin: 2px !important;">
    <h4><span class="icon icon-info" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #17a2b8;">&#8505;</span> Alternative Package Options</h4>
    <p style="margin-top: 10px;">If you're experiencing budget constraints, we offer several alternative packages that might better suit your needs:</p>
    <p style="text-align: center; margin-top: 20px;">
        <a href="<?= $emailData['site_url'] ?>/subscription/packages" class="button-primary" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: #ffffff !important; text-decoration: none; border-radius: 3px; margin: 20px 0;">View Package Options</a>
    </p>
</div>
<?php endif; ?>
