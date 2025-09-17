<h2 class="heading" style="color: #495057;"><?= $emailData['product_name'] ?> License Activity Notification</h2>

<p class="subheading">Dear <?= $emailData['user_name'] ?>,</p>

<p>We're writing to inform you about recent activity related to your <?= $emailData['product_name'] ?> license.</p>
<div class="info-box" style="background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; border-radius: 4px; padding: 15px; margin: 2px !important;">
    <h4 class="box-heading" style="margin-top: 0;">
        <span class="icon icon-info" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #17a2b8;">&#8505;</span> License Activity Details
    </h4>
    <table style="width: 100%; border-collapse: collapse; margin: 20px 0; background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <tr>
            <th style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: left; background-color: #f8f9fa; color: #495057; font-weight: 600; text-transform: uppercase; font-size: 0.9em;">License Key</th>
            <td style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: left;"><?= $emailData['license_key'] ?></td>
        </tr>
        <tr>
            <th style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: left; background-color: #f8f9fa; color: #495057; font-weight: 600; text-transform: uppercase; font-size: 0.9em;">Activity Date</th>
            <td style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: left;"><?= $emailData['date_activity'] ?></td>
        </tr>
        <tr>
            <th style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: left; background-color: #f8f9fa; color: #495057; font-weight: 600; text-transform: uppercase; font-size: 0.9em;">Activity Message</th>
            <td style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: left;"><?= $emailData['message'] ?></td>
        </tr>
        <?php if($emailData['template'] === 'reminder_expiring_license') : ?>
            <tr>
                <th style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: left; background-color: #f8f9fa; color: #495057; font-weight: 600; text-transform: uppercase; font-size: 0.9em;">Expiration Date</th>
                <td style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: left;"><?= $emailData['date_expiry'] ?></td>
            </tr>
        <?php endif; ?>
    </table>
</div>

<div class="warning-box" style="background-color: #fff3cd; border-color: #ffeeba; color: #856404; border-radius: 4px; padding: 15px; margin: 2px !important;">
    <h4 class="box-heading" style="margin-top: 0;"><span class="icon icon-warning" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #ffc107;">&#9888;</span> Important Notice</h4>
    <p class="box-content" style="margin-bottom: 0;">If you did not initiate this activity, please contact our support team immediately at <?= $emailData['support_email'] ?>.</p>
</div>

<div class="notes" style="font-size: 15px; color: #666; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
    <p>For your security, we recommend regularly reviewing your account activity and ensuring that your license key remains confidential.</p>
</div>
