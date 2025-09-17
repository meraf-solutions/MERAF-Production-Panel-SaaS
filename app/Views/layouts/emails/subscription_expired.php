<h1 class="heading" style="color: #333; font-size: 24px; margin-bottom: 20px;">Your Subscription Has Expired</h1>

<p class="subheading" style="font-size: 18px; color: #555;">Dear <?= $emailData['user_name'] ?>,</p>

<?php if($emailData['is_trial']) : ?>
    <div class="info-box" style="background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; border-radius: 4px; padding: 15px; margin: 2px !important;">
        <h3>
            <span class="icon icon-warning" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #dc3545;">&#9888;</span>
            Your Trial Has Expired
        </h3>
        <p style="margin-top: 10px;">
            Your free trial has ended, and access to premium features is now paused. To continue using the full benefits of our platform, please select a subscription plan.
        </p>
    </div>
    
    <p style="text-align: center; margin-top: 30px;">
        <a href="<?= base_url('subscription/packages') ?>" class="button-primary" style="display: inline-block; padding: 10px 20px; background-color: #dc3545; color: #ffffff !important; text-decoration: none; border-radius: 3px; margin: 20px 0;">
            Upgrade Now
        </a>
    </p>
<?php else : ?>
    <p style="margin-bottom: 20px;">Your subscription has expired due to multiple failed payment attempts or reaching the end of its term without renewal.</p>
    
    <div class="danger-box" style="background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; border-radius: 4px; padding: 15px; margin: 2px !important;">
        <h3><span class="icon icon-danger" style="display: inline-flex; justify-content: center; align-items: center; width: 1.2em; height: 1.2em; font-size: 1.2rem; color: #dc3545;">&#10071;</span> Subscription Details</h3>
        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
            <tr>
                <th>Package</th>
                <td><?= $emailData['package_name'] ?></td>
            </tr>
            <tr>
                <th>Status</th>
                <td><span class="status status-cancelled" style="display: inline-block; padding: 5px 10px; border-radius: 3px; font-weight: bold; background: #dc3545; color: #ffffff;">Expired</span></td>
            </tr>
        </table>
    </div>
    
    <div class="info-box" style="background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; border-radius: 4px; padding: 15px; margin: 2px !important;">
        <h3><span class="icon icon-info" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #17a2b8;">&#8505;</span> What This Means</h3>
        <ul style="margin-top: 10px; padding-left: 20px;">
            <li>Your access to subscription features has been deactivated</li>
            <li>Any automated processes or integrations have been stopped</li>
            <li>Your data will be retained for 30 days from the expiration date</li>
        </ul>
    </div>
    
    <div class="success-box" style="background-color: #d4edda; border-color: #c3e6cb; color: #155724; border-radius: 4px; padding: 15px; margin: 2px !important;">
        <h3><span class="icon icon-success" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #28a745;">&#9989;</span> Reactivate Your Subscription</h3>
        <p style="margin-top: 10px;">You can easily reactivate your subscription by clicking the button below:</p>
        <p style="text-align: center; margin-top: 20px;">
            <a href="<?= $emailData['site_url'] ?>/subscription/my-subscription" class="button-success" style="display: inline-block; padding: 10px 20px; background-color: #28a745; color: #ffffff !important; text-decoration: none; border-radius: 3px; margin: 20px 0;">Reactivate Now</a>
        </p>
        <p style="margin-top: 10px;"><strong>Benefits of reactivating within 30 days:</strong></p>
        <ul style="margin-top: 10px; padding-left: 20px;">
            <li>Maintain access to all your existing data</li>
            <li>Resume your services without interruption</li>
            <li>Keep your previous settings and configurations</li>
        </ul>
    </div>
    
    <?php if (isset($special_offer)): ?>
    <div class="danger-box" style="background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; border-radius: 4px; padding: 15px; margin: 2px !important;">
        <h4><span class="icon icon-danger" style="display: inline-flex; justify-content: center; align-items: center; width: 1.2em; height: 1.2em; font-size: 1.2rem; color: #dc3545;">&#10071;</span> Special Reactivation Offer!</h4>
        <p style="margin-top: 10px;">Reactivate your subscription within the next 7 days and receive:</p>
        <ul style="margin-top: 10px; padding-left: 20px;">
            <li>20% off your first month</li>
            <li>Free upgrade to premium support</li>
            <li>Priority account restoration</li>
        </ul>
        <p style="text-align: center; margin-top: 20px;">
            <a href="<?= $emailData['site_url'] ?>/subscription/special-offer" class="button-primary" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: #ffffff !important; text-decoration: none; border-radius: 3px; margin: 20px 0;">Claim Special Offer</a>
        </p>
    </div>
    <?php endif; ?>
    
    <div class="info-box" style="background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; border-radius: 4px; padding: 15px; margin: 2px !important;">
        <h4><span class="icon icon-info" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #17a2b8;">&#8505;</span> Explore Other Options</h4>
        <p style="margin-top: 10px;">If your previous package didn't meet your needs, we offer several alternatives that might be a better fit:</p>
        <p style="text-align: center; margin-top: 20px;">
            <a href="<?= $emailData['site_url'] ?>/subscription/packages" class="button-primary" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: #ffffff !important; text-decoration: none; border-radius: 3px; margin: 20px 0;">View Available Packages</a>
        </p>
    </div>
<?php endif; ?>

<div class="warning-box" style="background-color: #fff3cd; border-color: #ffeeba; color: #856404; border-radius: 4px; padding: 15px; margin: 2px !important;">
    <h4><span class="icon icon-warning" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #ffc107;">&#9888;</span> Important Notice About Your Data</h4>
    <p style="margin-top: 10px;">To protect your data, please take one of the following actions within the next 30 days:</p>
    <ul style="margin-top: 10px; padding-left: 20px;">
        <li>Reactivate your subscription to maintain full access</li>
        <li>Export your data for backup purposes</li>
        <li>Contact support to discuss data retention options</li>
    </ul>
    <p>After 30 days, your data may be archived or deleted according to our data retention policy.</p>
</div>

<p style="margin-top: 20px;">If you have any questions or need assistance with reactivating your subscription, our support team is here to help:</p>
<ul style="margin-top: 10px; padding-left: 20px;">
    <li>Email: <a href="mailto:<?= $emailData['support_email'] ?>"><?= $emailData['support_email'] ?></a></li>
    <li>Support Hours: Monday - Friday, 9:00 AM - 5:00 PM EST</li>
    <li>Response Time: Within 24 hours</li>
</ul>

<p style="margin-top: 20px;">We value your business and hope to welcome you back soon!</p>

<p class="signature" style="margin-top: 30px; font-style: italic;">
    Best regards,<br>
    The <?= $emailData['app_name'] ?> Team
</p>

<div class="notes" style="font-size: 15px; color: #666; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
    <p>If you believe your subscription expired in error or have already taken action to reactivate, please contact our support team.</p>
</div>
