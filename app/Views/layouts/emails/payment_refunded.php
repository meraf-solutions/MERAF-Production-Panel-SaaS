<h1 class="heading" style="color: #333; font-size: 24px; margin-bottom: 20px;">Payment Refund Notice</h1>

<p class="subheading" style="font-size: 18px; color: #555;">Dear <?= $emailData['user_name'] ?>,</p>

<p style="margin-bottom: 20px;">We are writing to confirm that a refund has been processed for your subscription payment.</p>

<div class="success-box" style="background-color: #d4edda; border-color: #c3e6cb; color: #155724; border-radius: 4px; padding: 15px; margin: 2px !important;">
    <h3><span class="icon icon-success" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #28a745;">&#9989;</span> Refund Details</h3>
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <tr>
            <th>Refund Date</th>
            <td><?= $emailData['refund_date'] ?></td>
        </tr>
        <tr>
            <th>Amount Refunded</th>
            <td><?= $emailData['amount'] ?></td>
        </tr>
        <tr>
            <th>Package</th>
            <td><?= $emailData['package_name'] ?></td>
        </tr>
        <tr>
            <th>Refund Type</th>
            <td><?= $emailData['is_full_refund'] ? 'Full Refund' : 'Partial Refund' ?></td>
        </tr>
    </table>
</div>

<div class="info-box" style="background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; border-radius: 4px; padding: 15px; margin: 2px !important;">
    <h3><span class="icon icon-info" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #17a2b8;">&#8505;</span> What This Means</h3>
    <?php if ($emailData['is_full_refund']): ?>
    <p style="margin-top: 10px;">As this is a full refund, your subscription will be cancelled. You will no longer have access to the services provided under this subscription.</p>
    <?php else: ?>
    <p style="margin-top: 10px;">As this is a partial refund, your subscription will remain active and you will continue to have access to the services provided under this subscription.</p>
    <?php endif; ?>
</div>

<p style="margin-top: 20px;">The refunded amount should appear in your account within 5-10 business days, depending on your payment method and financial institution.</p>

<p style="margin-top: 20px;">If you have any questions about this refund or your subscription, please don't hesitate to contact our support team at <a href="mailto:<?= $emailData['support_email'] ?>" style="color: #007bff; text-decoration: none;"><?= $emailData['support_email'] ?></a>.</p>

<p style="text-align: center; margin-top: 30px;">
    <a href="<?= $emailData['subscription_url'] ?>" class="button-primary" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: #ffffff !important; text-decoration: none; border-radius: 3px; margin: 20px 0;">View Subscription Details</a>
</p>

<p style="margin-top: 20px;">Thank you for your understanding.</p>

<p class="signature" style="margin-top: 30px; font-style: italic;">
    Best regards,<br>
    The <?= $emailData['company_name'] ?> Team
</p>
