<h1 class="heading" style="color: #333; font-size: 24px; margin-bottom: 20px;">Payment Pending Notice</h1>

<p class="subheading" style="font-size: 18px; color: #555;">Dear <?= $emailData['user_name'] ?>,</p>

<p style="margin-bottom: 20px;">We would like to inform you that you have a pending payment for your subscription.</p>

<div class="warning-box" style="background-color: #fff3cd; border-color: #ffeeba; color: #856404; border-radius: 4px; padding: 15px; margin: 2px !important;">
    <h3><span class="icon icon-warning" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #ffc107;">&#9888;</span> Payment Details</h3>
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <tr>
            <th>Date</th>
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
            <td><span class="status status-suspended" style="display: inline-block; padding: 5px 10px; border-radius: 3px; font-weight: bold; background: #ffc107; color: #000000;">Pending</span></td>
        </tr>
    </table>
</div>

<?php if(!$emailData['payment_instructions']) : ?>
    <p style="margin-top: 20px;">You don't need to take any action at this time. However, if you have any questions or concerns, please don't hesitate to contact our support team at <a href="mailto:<?= $emailData['support_email'] ?>" style="color: #007bff; text-decoration: none;"><?= $emailData['support_email'] ?></a>.</p>
<?php endif; ?>

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
        <h3><span class="icon icon-info" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #17a2b8;">&#8505;</span> What This Means</h3>
        <ul style="margin-top: 10px; padding-left: 20px;">
            <li>Your payment is being processed and may take a few days to complete</li>
            <li>Your subscription will remain active during this time</li>
            <li>We will notify you once the payment has been successfully processed</li>
        </ul>
    </div>
<?php endif; ?>

<p style="margin-top: 20px;">Thank you for your continuous support as always.</p>

<p class="signature" style="margin-top: 30px; font-style: italic;">    
    Best regards,<br>
    The <?= $emailData['company_name'] ?> Team
</p>
