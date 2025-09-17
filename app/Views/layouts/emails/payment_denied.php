<h1 class="heading" style="color: #333; font-size: 24px; margin-bottom: 20px;">Payment Denied Notice</h1>

<p class="subheading" style="font-size: 18px; color: #555;">Dear <?= $emailData['user_name'] ?>,</p>

<p style="margin-bottom: 20px;">We regret to inform you that your recent payment for your subscription has been denied.</p>

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
            <td><span class="status status-cancelled" style="display: inline-block; padding: 5px 10px; border-radius: 3px; font-weight: bold; background: #dc3545; color: #ffffff;">Denied</span></td>
        </tr>
    </table>
</div>

<div class="info-box" style="background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; border-radius: 4px; padding: 15px; margin: 2px !important;">
    <h3><span class="icon icon-info" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #17a2b8;">&#8505;</span> What This Means</h3>
    <ul style="margin-top: 10px; padding-left: 20px;">
        <li>Your payment was not processed successfully</li>
        <li>Your subscription status may be affected if the issue is not resolved</li>
        <li>We will attempt to process the payment again in the coming days</li>
    </ul>
</div>

<h3 style="color: #333; margin-top: 30px;">Next Steps</h3>
<ol style="margin-top: 10px; padding-left: 20px;">
    <li>Check your payment details in your account</li>
    <li>Ensure you have sufficient funds in your account</li>
    <li>Contact your bank if you believe this is an error</li>
    <li>Update your payment information if necessary</li>
</ol>

<p style="text-align: center; margin-top: 30px;">
    <a href="<?= $emailData['subscription_url'] ?>" class="button-primary" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: #ffffff !important; text-decoration: none; border-radius: 3px; margin: 20px 0;">Update Payment Method</a>
</p>

<p style="margin-top: 20px;">If you need any assistance or have questions, please don't hesitate to contact our support team.</p>

<p style="margin-top: 20px;">We appreciate your prompt attention to this matter.</p>

<p class="signature" style="margin-top: 30px; font-style: italic;">
    Best regards,<br>
    The <?= $emailData['company_name'] ?> Team
</p>
