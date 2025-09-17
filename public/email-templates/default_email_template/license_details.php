<h2 class="heading" style="color: #495057;">Your <?= $emailData['product_name'] ?> License Details & Getting Started Guide</h2>

<p class="subheading">Dear <?= $emailData['user_name'] ?>,</p>

<p>Thank you for choosing <?= $emailData['product_name'] ?>! We're thrilled to inform you that your license has been successfully created. Below, you'll find all the details you need to get started.</p>

<div class="success-box" style="background-color: #d4edda; border-color: #c3e6cb; color: #155724; border-radius: 4px; padding: 15px; margin: 2px !important;">
    <h4 class="box-heading" style="margin-top: 0;"><span class="icon icon-success" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #28a745;">&#9989;</span> License Successfully Created!</h4>
    <p class="box-content" style="margin-bottom: 0;">Your license is now ready to use. Enjoy everything <?= $emailData['product_name'] ?> has to offer!</p>
</div>

<table style="width: 100%; border-collapse: collapse; margin: 20px 0; background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <tr>
        <th style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: left; background-color: #f8f9fa; color: #495057; font-weight: 600; text-transform: uppercase; font-size: 0.9em;">License Detail</th>
        <th style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: left; background-color: #f8f9fa; color: #495057; font-weight: 600; text-transform: uppercase; font-size: 0.9em;">Value</th>
    </tr>
    <tr>
        <td style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: left;">License Key</td>
        <td style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: left;"><?= $emailData['license_key'] ?></td>
    </tr>
    <tr>
        <td style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: left;">License Type</td>
        <td style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: left;"><?= ucfirst($emailData['license_type']) ?></td>
    </tr>
    <?php if( ($emailData['license_type'] === 'trial') || ($emailData['license_type'] === 'subscription') ) : ?>
        <tr>
            <td style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: left;">Valid Until</td>
            <td style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: left;"><?= $emailData['date_expiry'] ?></td>
        </tr>
    <?php endif; ?>
    <?php if(!empty($emailData['max_allowed_domains'])) : ?>
        <tr>
            <td style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: left;">Max Domain</td>
            <td style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: left;"><?= $emailData['max_allowed_domains'] ?></td>
        </tr>
    <?php endif; ?>
    <?php if(!empty($emailData['max_allowed_devices'])) : ?>
        <tr>
            <td style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: left;">Max Device</td>
            <td style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: left;"><?= $emailData['max_allowed_devices'] ?></td>
        </tr>
    <?php endif; ?>
    <?php if(!empty($emailData['download_url'])) : ?>
        <tr>
            <td style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: left;">Download</td>
            <td style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: left;"><a href="<?= $emailData['download_url'] ?>" class="button button-primary" style="display: inline-block; padding: 10px 20px; text-decoration: none; border-radius: 3px; margin: 0px; color: #ffffff !important; background-color: #007bff;">Download</a></td>
        </tr>
    <?php endif; ?>

</table>

<div class="warning-box" style="background-color: #fff3cd; border-color: #ffeeba; color: #856404; border-radius: 4px; padding: 15px; margin: 2px !important;">
    <h4 class="box-heading" style="margin-top: 0;"><span class="icon icon-warning" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #ffc107;">&#9888;</span> Important Security Notice</h4>
    <p class="box-content" style="margin-bottom: 0;">Your license key is unique to your organization. Please keep it confidential and store it in a secure location.</p>
</div>

<?php if(!empty($emailData['product_guide'])) : ?>
    <div class="info-box" style="background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; border-radius: 4px; padding: 15px; margin: 2px !important;">
        <h4 class="box-heading" style="margin-top: 0;"><span class="icon icon-info" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #17a2b8;">&#8505;</span> Getting Started</h4>
        <?= $emailData['product_guide'] ?>
    </div>
<?php endif; ?>

<div class="info-box" style="background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; border-radius: 4px; padding: 15px; margin: 2px !important;">
    <h4 class="box-heading" style="margin-top: 0;"><span class="icon icon-info" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #17a2b8;">&#8505;</span> Reset Registered Domain or Device</h4>
    <p class="box-content" style="margin-bottom: 0;">Your license can be registered for up to the allowed limit of domains or devices. If you need to reset or remove a registered domain/device, you can do so through the portal below:</p>
    <p style="text-align: center;">
        <a href="<?= base_url('reset-own-license/?s=' . $emailData['license_key']) ?>" class="button button-primary" style="display: inline-block; padding: 10px 20px; text-decoration: none; border-radius: 3px; margin: 10px; color: #ffffff !important; background-color: #007bff;">Reset License</a>
    </p>
</div>

<div class="danger-box" style="background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; border-radius: 4px; padding: 15px; margin: 2px !important;">
    <h4 class="box-heading" style="margin-top: 0;"><span class="icon icon-danger" style="display: inline-flex; justify-content: center; align-items: center; width: 1.1em; height: 1.1em; font-size: 1.1rem; color: #dc3545;">&#10071;</span> Need Help?</h4>
    <p class="box-content" style="margin-bottom: 0;">Our support team is available 24/7. Feel free to reach out to us at <?= $emailData['support_email'] ?></p>
    <p class="box-content" style="margin-bottom: 0;">For security reasons, please save your license key in a safe location. While we can assist with recovery if needed, keeping it secure will ensure uninterrupted access to our services.</p>
</div>

<div class="notes" style="font-size: 15px; color: #666; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
    <p>For security reasons, please save your license key in a safe location. While we can help you recover it if needed, keeping it secure will ensure uninterrupted access to our services.</p>
</div>
