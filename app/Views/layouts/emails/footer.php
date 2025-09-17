<div class="footer" style="background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #6c757d; border-radius: 0 0 5px 5px;">
    <p>
        <?= $emailData['app_name'] ?><br>
        <?= $emailData['company_name'] ?><br>
        <?= $emailData['company_address'] ?><br>
        Support: <a href="mailto:<?= $emailData['support_email'] ?>"><?= $emailData['support_email'] ?></a>
    </p>
    <p>
        This email was sent to <?= $emailData['user_name'] ?> (<?= $emailData['user_email'] ?>).<br>
        If you have any questions, please contact our support team.
    </p>
    <p>
        To ensure you receive all our emails, please add <strong><?= $emailData['from_email'] ?></strong> to your email whitelist or safe sender list.
    </p>
</div>
