<h1 class="heading" style="color: #333; font-size: 24px; margin-bottom: 20px;">Your Subscription is Expiring Soon</h1>

<p class="subheading" style="font-size: 18px; color: #555;">Dear <?= $emailData['user_name'] ?>,</p>

<?php if($emailData['is_trial']) : ?>
    <p style="margin-bottom: 20px;">This is a friendly reminder that your trial subscription will be ending soon.</p>
<?php else : ?>
    <p style="margin-bottom: 20px;">This is a friendly reminder that your subscription will be automatically renewed soon.</p>
<?php endif; ?>

<div class="warning-box" style="background-color: #fff3cd; border-color: #ffeeba; color: #856404; border-radius: 4px; padding: 15px; margin: 2px !important;">
    <h3><span class="icon icon-warning" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #ffc107;">&#9888;</span> Subscription Details</h3>
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <tr>
            <th>Package</th>
            <td><?= $emailData['package_name'] ?></td>
        </tr>
        <tr>
            <th>Status</th>
            <td><span class="status status-active" style="display: inline-block; padding: 5px 10px; border-radius: 3px; font-weight: bold; background: #28a745; color: #ffffff;">Active</span></td>
        </tr>
        <?php if(!$emailData['is_trial']) : ?>
            <tr>
                <th>Next Billing Date</th>
                <td><?= $emailData['next_billing_time'] ?></td>
            </tr>
        <?php endif; ?>
    </table>
</div>

<?php if($emailData['is_trial']) : ?>
    <div class="info-box" style="background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; border-radius: 4px; padding: 15px; margin: 2px !important;">
        <h3>
            <span class="icon icon-info" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #17a2b8;">&#8505;</span>
            Choose Your Subscription Plan
        </h3>
        <p style="margin-top: 10px;">
            To avoid any interruption in service, please select a paid subscription plan that best fits your needs.
        </p>
    </div>
    
    <p style="text-align: center; margin-top: 30px;">
        <a href="<?= base_url('subscription/packages') ?>" class="button-primary" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: #ffffff !important; text-decoration: none; border-radius: 3px; margin: 20px 0;">
            View Plans
        </a>
    </p>
<?php else : ?>
    <div class="info-box" style="background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; border-radius: 4px; padding: 15px; margin: 2px !important;">
        <h3><span class="icon icon-info" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #17a2b8;">&#8505;</span> Automatic Renewal</h3>
        <p style="margin-top: 10px;">Your subscription will be automatically renewed on <?= $emailData['next_billing_time'] ?>. The renewal amount of <?= $emailData['amount'] ?> will be charged to your payment method on file.</p>
    </div>
    
    <h3 style="color: #333; margin-top: 30px;">Payment Method</h3>
    <div class="info-box" style="background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; border-radius: 4px; padding: 15px; margin: 2px !important;">
        <p><span class="icon icon-info" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #17a2b8;">&#8505;</span> Your subscription will be renewed using the same payment method used in the initial subscription.</p>
    </div>
    
    <div class="info-box" style="background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; border-radius: 4px; padding: 15px; margin: 2px !important;">
        <h4><span class="icon icon-info" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #17a2b8;">&#8505;</span> Options Available to You</h4>
        <ul style="margin-top: 10px; padding-left: 20px;">
            <li>Continue with automatic renewal - No action required</li>
            <li><a href="<?= $emailData['site_url'] ?>/subscription/packages">Update your subscription package</a></li>
            <li><a href="<?= $emailData['site_url'] ?>/subscription/my-subscription">Cancel automatic renewal</a></li>
        </ul>
    </div>
    
    <?php if (isset($upgrade_available) && $upgrade_available): ?>
    <div class="success-box" style="background-color: #d4edda; border-color: #c3e6cb; color: #155724; border-radius: 4px; padding: 15px; margin: 2px !important;">
        <h4><span class="icon icon-success" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #28a745;">&#9989;</span> Special Upgrade Offer!</h4>
        <p style="margin-top: 10px;">Upgrade to our premium package and get 20% off for the first 3 months!</p>
        <p style="text-align: center; margin-top: 20px;">
            <a href="<?= $emailData['site_url'] ?>/subscription/packages" class="button-success" style="display: inline-block; padding: 10px 20px; background-color: #28a745; color: #ffffff !important; text-decoration: none; border-radius: 3px; margin: 20px 0;">Upgrade Now</a>
        </p>
    </div>
    <?php endif; ?>

    <p style="margin-top: 20px;">If you have any questions about your subscription or need assistance, please don't hesitate to contact our support team at <a href="mailto:<?= $emailData['support_email'] ?>" style="color: #007bff; text-decoration: none;"><?= $emailData['support_email'] ?></a>.</p>
    
    <div class="info-box" style="background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; border-radius: 4px; padding: 15px; margin: 2px !important;">
        <h4><span class="icon icon-info" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #17a2b8;">&#8505;</span> Important Information</h4>
        <ul style="margin-top: 10px; padding-left: 20px;">
            <li>To ensure uninterrupted service, please make sure your payment method is up to date</li>
            <li>You can review your subscription details and billing history at any time in your account dashboard</li>
            <li>If you wish to cancel automatic renewal, please do so at least 24 hours before the renewal date</li>
        </ul>
    </div>
<?php endif; ?>

<p style="margin-top: 20px;">Thank you for your continued support!</p>

<p class="signature" style="margin-top: 30px; font-style: italic;">
    Best regards,<br>
    The <?= $emailData['app_name'] ?> Team
</p>

<div class="notes" style="font-size: 15px; color: #666; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
    <p>This is an automated reminder. If you've already taken action regarding your subscription, please disregard this email.</p>
</div>
