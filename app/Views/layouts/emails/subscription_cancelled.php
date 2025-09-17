<h1 class="heading" style="color: #333; font-size: 24px; margin-bottom: 20px;">Subscription Cancelled</h1>

<p class="subheading" style="font-size: 18px; color: #555;">Dear <?= $emailData['user_name'] ?>,</p>

<p style="margin-bottom: 20px;">We're sorry to see you go. This email confirms that your subscription has been cancelled as requested.</p>

<div class="danger-box" style="background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; border-radius: 4px; padding: 15px; margin: 2px !important;">
    <h3><span class="icon icon-danger" style="display: inline-flex; justify-content: center; align-items: center; width: 1.2em; height: 1.2em; font-size: 1.2rem; color: #dc3545;">&#10071;</span> Subscription Details</h3>
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <tr>
            <th>Package</th>
            <td><?= $emailData['package_name'] ?></td>
        </tr>
        <tr>
            <th>Status</th>
            <td><span class="status status-cancelled" style="display: inline-block; padding: 5px 10px; border-radius: 3px; font-weight: bold; background: #dc3545; color: #ffffff;">Cancelled</span></td>
        </tr>
    </table>
</div>

<div class="info-box" style="background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; border-radius: 4px; padding: 15px; margin: 2px !important;">
    <h3><span class="icon icon-info" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #17a2b8;">&#8505;</span> What This Means</h3>
    <ul style="margin-top: 10px; padding-left: 20px;">
        <li>Your subscription access will continue until the end of your current billing period</li>
        <li>No further payments will be charged to your account</li>
        <li>Your account data will be retained for 30 days after the end of your subscription</li>
    </ul>
</div>

<div class="success-box" style="background-color: #d4edda; border-color: #c3e6cb; color: #155724; border-radius: 4px; padding: 15px; margin: 2px !important;">
    <h4><span class="icon icon-success" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #28a745;">&#9989;</span> Want to Reactivate?</h4>
    <p style="margin-top: 10px;">If you change your mind, you can reactivate your subscription at any time before the end of your current billing period.</p>
    <p style="text-align: center; margin-top: 20px;">
        <a href="<?= $emailData['subscription_url'] ?>" class="button-success" style="display: inline-block; padding: 10px 20px; background-color: #28a745; color: #ffffff !important; text-decoration: none; border-radius: 3px; margin: 20px 0;">Reactivate Subscription</a>
    </p>
</div>

<p style="margin-top: 20px;">If you cancelled by mistake or have any questions, please contact our support team immediately at <a href="mailto:<?= $emailData['support_email'] ?>" style="color: #007bff; text-decoration: none;"><?= $emailData['support_email'] ?></a>.</p>

<h3 style="color: #333; margin-top: 30px;">Explore Other Options</h3>
<p style="margin-top: 10px;">If our current package didn't meet your needs, we offer several other options that might be a better fit:</p>
<p style="text-align: center; margin-top: 20px;">
    <a href="<?= $emailData['site_url'] ?>/subscription/packages" class="button-primary" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: #ffffff !important; text-decoration: none; border-radius: 3px; margin: 20px 0;">View Available Packages</a>
</p>

<p style="margin-top: 20px;">Thank you for being our customer. We hope to see you again soon!</p>

<p class="signature" style="margin-top: 30px; font-style: italic;">
    Best regards,<br>
    The <?= $emailData['app_name'] ?> Team
</p>

<div class="notes" style="font-size: 15px; color: #666; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
    <p>If you believe this cancellation was made in error or without your authorization, please contact our support team immediately.</p>
</div>
