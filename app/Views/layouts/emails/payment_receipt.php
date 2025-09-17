<h1 class="heading" style="color: #333; font-size: 24px; margin-bottom: 20px;">Payment Receipt</h1>

<p class="subheading" style="font-size: 18px; color: #555;">Dear <?= $emailData['user_name'] ?>,</p>

<p style="margin-bottom: 20px;">Thank you for your payment. This email confirms that your payment has been successfully processed.</p>

<div class="success-box" style="background-color: #d4edda; border-color: #c3e6cb; color: #155724; border-radius: 4px; padding: 15px; margin: 2px !important;">
    <h3><span class="icon icon-success" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #28a745;">&#9989;</span> Payment Details</h3>
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <tr>
            <th>Transaction ID</th>
            <td><?= $emailData['transaction_id'] ?></td>
        </tr>
        <tr>
            <th>Amount</th>
            <td><?= $emailData['amount'] ?></td>
        </tr>
        <tr>
            <th>Payment Date</th>
            <td><?= $emailData['payment_date'] ?></td>
        </tr>
        <tr>
            <th>Payment Method</th>
            <td><?= $emailData['payment_method'] ?></td>
        </tr>
    </table>
</div>

<div class="info-box" style="background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; border-radius: 4px; padding: 15px; margin: 2px !important;">
    <h3><span class="icon icon-info" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #17a2b8;">&#8505;</span> Subscription Information</h3>
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <tr>
            <th>Package</th>
            <td><?= $emailData['package_name'] ?></td>
        </tr>
        <?php if($emailData['payment_method'] !== 'Offline') : ?>
            <tr>
                <th>Subscription ID</th>
                <td><?= $emailData['subscription_id'] ?></td>
            </tr>
        <?php endif; ?>
        <tr>
            <th>Next Billing Date</th>
            <td><?= $emailData['next_billing_time'] ?></td>
        </tr>
    </table>
</div>

<p style="text-align: center; margin-top: 30px;">
    <a href="<?= $emailData['subscription_url'] ?>" class="button-primary" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: #ffffff !important; text-decoration: none; border-radius: 3px; margin: 20px 0;">View Subscription Details</a>
</p>

<div class="info-box" style="background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; border-radius: 4px; padding: 15px; margin: 2px !important;">
    <h4><span class="icon icon-info" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #17a2b8;">&#8505;</span> Important Information</h4>
    <ul style="margin-top: 10px; padding-left: 20px;">
        <li>This payment will appear on your statement as "<?= $emailData['app_name'] ?>"</li>
        <?php if($emailData['payment_method'] === 'Offline') : ?>
            <li>Your renewal invoice will be generated three days before your next renewal date, which is <?= $emailData['next_billing_time'] ?>.</li>
        <?php else : ?>
            <li>Your subscription will automatically renew on <?= $emailData['next_billing_time'] ?></li>
        <?php endif; ?>
        <li>You can view your payment history and manage your subscription through your account dashboard</li>
    </ul>
</div>

<p style="margin-top: 20px;">If you have any questions about this payment or your subscription, please don't hesitate to contact our support team at <a href="mailto:<?= $emailData['support_email'] ?>" style="color: #007bff; text-decoration: none;"><?= $emailData['support_email'] ?></a>.</p>

<p class="signature" style="margin-top: 30px; font-style: italic;">
    Best regards,<br>
    The <?= $emailData['app_name'] ?> Team
</p>

<div class="notes" style="font-size: 15px; color: #666; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
    <p>This receipt was automatically generated and sent to <?= $emailData['user_email'] ?>. Please keep this email for your records.</p>
</div>
