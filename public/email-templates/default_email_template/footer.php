<div class="footer" style="background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #6c757d; border-radius: 0 0 5px 5px;">
    <?php if($emailData['company_name'] || $emailData['company_address']) : ?>
        <p><?= $emailData['company_name'] ?> | <?= $emailData['company_address'] ?></p>
    <?php endif; ?>
    <p>© <?= date('Y') ?> <?= $emailData['company_name'] ?? '' ?> | All rights reserved.</p>
    <p>
        To ensure you receive all our emails, please add <strong><?= $emailData['from_email'] ?></strong> to your email whitelist or safe sender list.
    </p>
</div>
