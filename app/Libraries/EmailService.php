<?php

namespace App\Libraries;

use CodeIgniter\I18n\Time;
use Config\Services;
use Exception;

/***
 Usage Example:

For: License Details
$emailService = new \App\Libraries\EmailService();
$licenseDetailsResult = $emailService->sendLicenseDetails([
    'license_key' => 'a16da9b45ff3e8c88377e8c42457393dfa70d798',
    'recipient_email' => 'paolomedina@gmail.com',
    'email_format' => 'text' // (optional. Default: html)
]);

For: License activity notification
$emailService = new \App\Libraries\EmailService();
$emailService->sendLicenseNotification([
    'license_key' => 'a16da9b45ff3e8c88377e8c42457393dfa70d798',
    'recipient_email' => 'paolomedina@gmail.com',
    'date_activity' => Time::now()->setTimezone('UTC')->format('Y-m-d H:i:s'),
    'email_format' => 'text' // (optional. Default: html)
]);

For: General email
$emailService = new \App\Libraries\EmailService();
$generalEmailResult = $emailService->sendGeneralEmail([
    'template' => 'test_email', // (optional. Default: general_email)
    'userID' => $userID,
    'emailType' => $emailFormat, // (optional. Default: html)
    'recipient_email' => $toEmail,
    'subject' => $emailSubject,
    'message' => $emailBody
]);

For: Subscription email to SaaS user
$userID = $userID;
$templateName = 'payment_refunded';
$emailData = json_decode('{"user_name":"Paolo Medina","refund_date":"2025-01-02 19:06:03","amount":"7","subscription_id":"8","currency":"USD","package_name":"Starter Studio","is_full_refund":false,"cancellation_date":""}', true);

$emailService = new \App\Libraries\EmailService();
$result = $emailService->sendSubscriptionEmail([
    'userID' => $userID,
    'template' => $templateName,
    'data' => $emailData
]);

For: Payment receipt to SaaS user
$emailService = new \App\Libraries\EmailService();
$result = $emailService->sendPaymentReceipt([
    'userID' => $subscription['user_id'],
    'data' => $emailData
]);
 ***/

class EmailService
{
    private $email;
    private $config;
    private $licensesModel;
    private $templateRenderer;
    private $myConfig;
    private $footerAdHTML;
    private $footerAdText;
    private $emailLogModel;

    public function __construct()
    {
        $this->email = Services::email();
        $this->config = config('Email');
        $this->licensesModel = new \App\Models\LicensesModel();
        $this->templateRenderer = new \App\Libraries\SecureTemplateRenderer(config('View'));
        $this->myConfig = getMyConfig('', 0);
        $this->emailLogModel = new \App\Models\EmailLogModel();

        $this->footerAdHTML = str_replace(
            [
                '{app_name}',
                '{company_name}',
                '{company_address}',
                '{app_url}'
            ],
            [
                $this->myConfig['appName'],
                $this->myConfig['companyName'],
                $this->myConfig['companyAddress'],
                base_url()
            ],
            $this->myConfig['htmlEmailFooter'] ?? '<div class="footer" style="background:#f8f9fa;padding:10px;text-align:center;font-size:12px;color:#6c757d;border-radius:0 0 5px 5px;margin-top:10px"><p>Simplify your licensing & digital product management with <strong>{app_name}</strong>, your all-in-one solution for license and digital product management—brought to you by <strong>{company_name}</strong>.</p><p style="text-align:center"><a href="{app_url}" style="display:inline-block;padding:10px 20px;text-decoration:none;border-radius:3px;margin:10px;color:#fff;background-color:#007bff">Discover More</a></p></div>'
        );

        $this->footerAdText = "\n" . str_replace(
            [
                '{app_name}',
                '{company_name}',
                '{company_address}',
                '{app_url}'
            ],
            [
                $this->myConfig['appName'],
                $this->myConfig['companyName'],
                $this->myConfig['companyAddress'],
                base_url()
            ],
            $this->myConfig['textEmailFooter'] ?? "==========\nSimplify your licensing & digital product management with {app_name}, your all-in-one solution for license and digital product managemen—brought to you by {company_name} ({app_url})"
        );
    }

    /**
     * Sends an email with various configuration options
     *
     * @param array $params Email parameters
     * @return bool True if email sent successfully, false otherwise
     */
    public function sendEmail(array $params): bool
    {        
        $userID = $params['userID'] ?? 0;
		
		$myConfig = getMyConfig('', $userID);

        $emailTemplateSet = $myConfig['selectedEmailTemplate'] ?? null;
        $emailType = $emailTemplateSet ? 'html' : 'text';
        $emailType = $params['emailType'] ?? $emailType;

        $validTypes = ['html', 'text'];
        $emailType = in_array($emailType, $validTypes) ? $emailType : 'html';

        $this->email->initialize([
            'mailType'  => $emailType,
            'charset'   => 'utf-8',
            'newline'   => "\r\n",
            'userAgent' => $myConfig['appName'],
        ]);

        // Set email details
        $this->email->setFrom($params['fromEmail'] ?? $myConfig['fromEmail'], $params['fromName'] ?? $myConfig['fromName']);
        $this->email->setReplyTo($params['replyToEmail'] ?? $myConfig['replyToEmail'] ?? $myConfig['fromEmail'], $params['replyToName'] ?? $myConfig['replyToName'] ?? '');
        $this->email->setTo($params['toEmail']);
        $this->email->setHeader('Return-Path', $myConfig['supportEmail']);
        
        if (!empty($params['ccEmail'])) {
            $this->email->setCC($params['ccEmail'], $params['ccName'] ?? '');
        }
        
        if (!empty($params['bccEmail'])) {
            $this->email->setBCC($params['bccEmail'], $params['bccName'] ?? '');
        }

        $this->email->setSubject($params['subject']);

        if ($emailType === 'html') {
            $this->email->setMessage($params['message']);
            $this->email->setAltMessage($params['plain_text_message']);
        } else {
            $this->email->setMessage($params['plain_text_message']);
        }

        try {
            $result = $this->email->send(false);
            // $result = true;
            $debugInfo = $this->email->printDebugger(['headers', 'subject', 'body']);

            // Use Reflection to access the private attachments property
            $listAttachedFiles = [];
            $reflection = new \ReflectionClass($this->email);
            $property = $reflection->getProperty('attachments');
            $property->setAccessible(true);
            $attachments = $property->getValue($this->email);

            // Ensure attachments are processed only if not empty
            if (!empty($attachments)) {
                foreach ($attachments as $attachment) {
                    if (!empty($attachment['name'])) {
                        // Loop through the 'name' array to handle multiple attachments
                        foreach ($attachment['name'] as $file) {
                            if (!empty($file)) {
                                $listAttachedFiles[] = basename($file); // Append basename of file
                            }
                        }
                    }
                }
            }

            // log_message('debug', '[EmailService] Test attachment list: ' . print_r($listAttachedFiles, true));
            
            // Log the email
            $logData = [
                'owner_id' => $userID,
                'to' => $params['toEmail'],
                'from' => $params['fromEmail'] ?? $myConfig['fromEmail'],
                'subject' => $params['subject'],
                'format' => $emailType,
                'body' => $emailType === 'html' ? $params['message'] : $params['plain_text_message'],
                'plain_text_message' => $params['plain_text_message'],
                'headers' => $this->email->printDebugger(['headers']),
                'attachments' => json_encode($listAttachedFiles ?? ''),
                'status' => $result ? 'sent' : 'failed',
                'response' => $result ? 'Email sent successfully' : $debugInfo,
                'source' => $params['source'] ?? 'manual',
            ];
            $this->emailLogModel->insert($logData);

            if ($result) {
                $toName = $params['toName'] ?? 'Recipient';
                log_message('info', "[EmailService] Email sent to {$toName} [{$params['toEmail']}] with the subject '{$params['subject']}'");
                $this->email->clear(); // clear it for next email task
                return true;
            } else {
                log_message('error', "[EmailService] Email sending failed. Debug info: " . $debugInfo);
                return false;
            }
        } catch (Exception $e) {
            log_message('error', "[EmailService] Email sending failed: {$e->getMessage()}");

            // Add notification to the admin
            $notificationMessage = "Email sending failed: {$e->getMessage()}";
            $notificationType = 'email_sending_failed';
            $url = base_url('email-logs');
            $recipientUserId = 1;
            add_notification($notificationMessage, $notificationType, $recipientUserId);
            
            // Log the failed attempt
            $logData = [
                'owner_id' => $userID,
                'to' => $params['toEmail'],
                'from' => $params['fromEmail'] ?? $myConfig['fromEmail'],
                'subject' => $params['subject'],
                'body' => $emailType === 'html' ? $params['message'] : $params['plain_text_message'],
                'plain_text_message' => $params['plain_text_message'],
                'headers' => $this->email->printDebugger(['headers']),
                'attachments' => json_encode($listAttachedFiles ?? ''),
                'status' => 'failed',
                'response' => $e->getMessage(),
                'source' => $params['source'] ?? 'manual',
            ];
            $this->emailLogModel->insert($logData);

            return false;
        }
    }

    /**
     * Sends license details via email
     *
     * @param array $params License details and email parameters
     * @return bool|array True if email sent successfully, error message otherwise
     */
    public function sendLicenseDetails(array $params): bool|array
    {
        // Required parameters check
        $requiredParams = ['license_key', 'recipient_email'];
        foreach ($requiredParams as $param) {
            if (!array_key_exists($param, $params)) {
                log_message('error', "[EmailService] Failed to send license details email: No {$param} parameter provided.");
                return ['success' => false, 'message' => lang('Notifications.missing_required_parameters', ['param' => $param])];
            }
        }
    
        $license = $this->licensesModel->where('license_key', $params['license_key'])->first();
        if (!$license) {
            log_message('error', '[EmailService] Failed to send license details: License not found - ' . $params['license_key']);
            return ['success' => false, 'message' => lang('Notifications.License_not_found')];
        }
    
        $userID = $license['owner_id'];
        $myConfig = getMyConfig('', $userID);
    
        // Get the template file path
        $userDataPath = USER_DATA_PATH . $userID . DIRECTORY_SEPARATOR;
        $emailTemplateSet = $myConfig['selectedEmailTemplate'] ?? null;
        if (!$emailTemplateSet) {
            log_message('error', '[EmailService] No default template selected for user ID: ' . $userID);
            return ['success' => false, 'message' => lang('Notifications.no_template_file_not_found', ['template_name' => $emailTemplateSet])];
        }
        $templateFile = $userDataPath . $myConfig['userEmailTemplatesPath'] . $emailTemplateSet . '/' . 'license_details.php';
        $params['template'] = 'license_details';
        $emailData = $this->prepareLicenseEmailData($license, $params, $myConfig);
    
        $contentHTML = $this->renderLicenseEmailContent($userID, 'license_details', $emailData);
        $contentPlainText = $this->renderPlainTextEmail($userID, $templateFile, $emailData);
    
        // Promotional email footer
        $subscriptionChecker = new \App\Libraries\SubscriptionChecker();
        $isNoPromotionalFooterEnabled = $subscriptionChecker->isFeatureEnabled($userID, 'No_Email_Footer_Message');
        if(!$isNoPromotionalFooterEnabled) {
            $contentHTML = $contentHTML . $this->footerAdHTML;
            $contentPlainText = $contentPlainText . $this->footerAdText;
        }
    
        $emailParams = [
            'userID'    => $userID,
            'toEmail'   => $params['recipient_email'],
            'toName'    => $license['first_name'] . ' ' . $license['last_name'],
            'subject'   => $emailData['subject'],
            'message'   => $contentHTML,
            'plain_text_message' => $contentPlainText,
            'emailType' => $params['email_format'] ?? 'html',
            'template'  => 'license_details',
            'cc_email' => $params['cc_email'] ?? '',
            'bcc_email' => ($params['with_bcc'] ?? false) === true ? $myConfig['bccEmail'] : '',
        ];
    
        log_message('debug', '[EmailService] Sending license details email. Email logo: ' . ($emailData['email_logo'] ?? 'Not set'));
    
        $result = $this->sendEmail($emailParams);
        log_message('info', '[EmailService] License details email sending result: ' . ($result ? 'Success' : 'Failure'));
        return ['success' => $result, 'message' => $result ? lang('Notifications.Email_sent_successfully') : lang('Notifications.Failed_to_send_email')];
    }

    /**
     * Sends license notification emails
     *
     * @param array $params Notification details and email parameters
     * @return bool|array True if email sent successfully, false otherwise
     */
    public function sendLicenseNotification(array $params): bool|array
    {
        // Required parameters check
        $requiredParams = ['license_key', 'recipient_email', 'date_activity'];
        foreach ($requiredParams as $param) {
            if (!array_key_exists($param, $params)) {
                log_message('error', "[EmailService] Failed to send license details email: No {$param} parameter provided.");
                return ['success' => false, 'message' => lang('Notifications.missing_required_parameters', ['param' => $param])];
            }
        }

        $license = $this->licensesModel->where('license_key', $params['license_key'])->first();
        if (!$license) {
            log_message('error', '[EmailService] Failed to send license notification: License not found - ' . $params['license_key']);
            return ['success' => false, 'message' => lang('Notifications.License_not_found')];
        }

        $userID = $license['owner_id'];
        $myConfig = getMyConfig('', $userID);

        // Get the template file path
        $userDataPath = USER_DATA_PATH . $userID . DIRECTORY_SEPARATOR;
        $emailTemplateSet = $myConfig['selectedEmailTemplate'] ?? null;
        if (!$emailTemplateSet) {
            log_message('error', '[EmailService] No default template selected for user ID: ' . $userID);
            return ['success' => false, 'message' => lang('Notifications.no_template_file_not_found', ['template_name' => $emailTemplateSet])];
        }
        $templateFile = $userDataPath . $myConfig['userEmailTemplatesPath'] . $emailTemplateSet . '/' . 'license_activity_notification.php';

        $emailData = $this->prepareLicenseEmailData($license, $params, $myConfig);
        $content = $this->renderLicenseEmailContent($userID, 'license_activity_notification', $emailData);

        $contentHTML = $this->renderLicenseEmailContent($userID, 'license_activity_notification', $emailData);
        $contentPlainText = $this->renderPlainTextEmail($userID, $templateFile, $emailData);

        // Promotional email footer
        $subscriptionChecker = new \App\Libraries\SubscriptionChecker();
        $isNoPromotionalFooterEnabled = $subscriptionChecker->isFeatureEnabled($userID, 'No_Email_Footer_Message');
        if(!$isNoPromotionalFooterEnabled) {
            $contentHTML = $contentHTML . $this->footerAdHTML;
            $contentPlainText = $contentPlainText . $this->footerAdText;
        }

        // return $contentHTML; // html format
        // return $contentPlainText; // text format

        $emailParams = [
            'userID'    => $userID,
            'toEmail'   => $params['recipient_email'],
            'toName'    => $license['first_name'] . ' ' . $license['last_name'],
            'subject'   => $emailData['subject'],
            'message'   => $contentHTML,
            'plain_text_message' => $contentPlainText,
            'emailType' => $params['email_format'] ?? 'html',
            'template'  => 'license_activity_notification',
            'cc_email' => $params['cc_email'] ?? '',
            'bcc_email' => ($params['with_bcc'] ?? false) === true ? $myConfig['bccEmail'] : '',
        ];

        log_message('debug', '[EmailService] Sending license notification email. Email logo: ' . ($emailData['email_logo'] ?? 'Not set'));

        $result = $this->sendEmail($emailParams);
        log_message('info', '[EmailService] License notification email sending result: ' . ($result ? 'Success' : 'Failure'));
        return ['success' => $result, 'message' => $result ? lang('Notifications.Email_sent_successfully') : lang('Notifications.Failed_to_send_email')];
    }

    public function sendGeneralEmail(array $params): array
    {
        $validTemplates = ['test_email', 'general_email'];
        $defaultTemplate = 'general_email';
        
        // Set and validate template
        $params['template'] = in_array($params['template'] ?? $defaultTemplate, $validTemplates) 
            ? ($params['template'] ?? $defaultTemplate) 
            : $defaultTemplate;
    
        // Required parameters check
        $requiredParams = ['subject', 'message', 'userID', 'recipient_email'];
        foreach ($requiredParams as $param) {
            if (!array_key_exists($param, $params)) {
                log_message('error', "[EmailService] Failed to send general email: No {$param} parameter provided.");
                return ['success' => false, 'message' => lang('Notifications.missing_required_parameters', ['param' => $param])];
            }
        }
    
        $userID = $params['userID'];
        $this->myConfig = getMyConfig('', $userID);

        // Get the template file path
        $userDataPath = USER_DATA_PATH . $userID . DIRECTORY_SEPARATOR;
        $emailTemplateSet = $this->myConfig['selectedEmailTemplate'] ?? null;
        if (!$emailTemplateSet) {
            log_message('error', '[EmailService] No default template selected for user ID: ' . $userID);
            return ['success' => false, 'message' => lang('Notifications.no_template_file_not_found', ['template_name' => $emailTemplateSet])];
        }
        $templateFile = $userDataPath . $this->myConfig['userEmailTemplatesPath'] . $emailTemplateSet . '/' . $params['template'] . '.php';

        // Search for the user's logo
        $checkEmailLogo = $this->myConfig['emailLogoFile'] ?? null;
        $email_logo = ($checkEmailLogo && $params['email_format'] === 'html') ? 
            $this->getEmailLogo($userID, $checkEmailLogo) : 
            null;

        $emailData = [
            'subject' => $params['subject'],
            'message' => $params['message'],
            'from_email' => $this->myConfig['fromEmail'],
            'company_name' => $this->myConfig['userCompanyName'] ?? '',
            'company_address' => $this->myConfig['userCompanyAddress'] ?? '',
            'email_logo' => $email_logo ? 'cid:' . $email_logo : '',
        ];
    
        $contentHTML = $this->renderLicenseEmailContent($userID, $params['template'], $emailData);
        $contentPlainText = $this->renderPlainTextEmail($userID, $templateFile, $emailData);

        // Promotional email footer
        $subscriptionChecker = new \App\Libraries\SubscriptionChecker();
        $isNoPromotionalFooterEnabled = $subscriptionChecker->isFeatureEnabled($userID, 'No_Email_Footer_Message');
        if(!$isNoPromotionalFooterEnabled) {
            $contentHTML = $contentHTML . $this->footerAdHTML;
            $contentPlainText = $contentPlainText . $this->footerAdText;
        }

        // return $contentHTML; // html format
        // return $contentPlainText; // text format

        $emailParams = [
            'userID'    => $userID,
            'toEmail'   => $params['recipient_email'],
            'subject'   => $emailData['subject'],
            'message'   => $contentHTML,
            'plain_text_message' => $contentPlainText,
            'emailType' => $params['email_format'] ?? 'html',
            'template'  => $params['template']
        ];
    
        $result = $this->sendEmail($emailParams);
        
        log_message('info', '[EmailService] General email sending result:: ' . ($result ? 'Success' : 'Failure'));
        return ['success' => $result, 'message' => $result ? lang('Notifications.Email_sent_successfully') : lang('Notifications.Failed_to_send_email')];
    }

    public function getEmailLogo(int $userID, ?string $emailLogoFile = null): ?string
    {
        // If no file provided, return null instead of false to match return type
        if (!$emailLogoFile) {
            return null;
        }
    
        $logoFile = pathinfo($emailLogoFile, PATHINFO_BASENAME);
        $fileExtension = pathinfo($logoFile, PATHINFO_EXTENSION);
        $logoFile = USER_DATA_PATH . $userID . '/' . $logoFile;
    
        if (is_file($logoFile)) {
            log_message('info', '[EmailService] User Email Logo Found: ' . $logoFile);
            $this->email->attach($logoFile, 'inline', 'logo.' . $fileExtension);
            return $this->email->setAttachmentCID('logo.' . $fileExtension);
        }
    
        log_message('error', '[EmailService] User Email Logo Cannot Be Found: ' . $logoFile);

        return null;
    
    }

    private function prepareLicenseEmailData(array $license, array $params, array $myConfig): array
    {
        // Calculate product basename once to avoid repetition
        $productBasename = ($license['product_ref'] && $license['owner_id']) 
            ? productBasename($license['product_ref'], $license['owner_id']) 
            : '';
    
        // Get product guide once to avoid duplicate function calls
        $productGuide = $productBasename 
            ? getProductGuide($productBasename, $license['owner_id']) 
            : '';
    
        // Get the full name once to avoid repetition
        $fullName = ($license['first_name'] ?? '') . ' ' . ($license['last_name'] ?? '');
    
        // Replace placeholders in product guide if it exists
        if (!empty($productGuide)) {
            $productGuide = str_replace(
                [
                    '{clientFullName}',
                    '{licenseKey}',
                    '{productName}'
                ],
                [
                    $fullName,
                    $license['license_key'] ?? '',
                    $productBasename
                ],
                $productGuide
            );
        }
    
        // Search for the user's logo
        $checkEmailLogo = $myConfig['emailLogoFile'] ?? null;
        $email_logo = ($checkEmailLogo && $params['email_format'] === 'html') ? 
            $this->getEmailLogo($license['owner_id'], $checkEmailLogo) : 
            null;

        return [
            'user_name' => $fullName,
            'user_email' => $params['recipient_email'],
            'license_key' => $license['license_key'] ?? '',
            'license_type' => $license['license_type'] ?? '',
            'license_status' => $license['license_status'] ?? '',
            'product_name' => $license['product_ref'],
            'date_created' => formatDate($license['date_created'] ?? '', $myConfig),
            'date_expiry' => $license['date_expiry'] ? formatDate($license['date_expiry'], $myConfig) : 'N/A',
            'company_name' => $license['company_name'] ?? '',
            'max_allowed_domains' => $license['max_allowed_domains'] ?? '',
            'max_allowed_devices' => $license['max_allowed_devices'] ?? '',
            'download_url' => productDetails($license['product_ref'], $license['owner_id'])['url'] ?? '',
            'date_activity' => formatDate($params['date_activity'] ?? '', $myConfig), 
            'message' => $this->getLicenseEmailMessage($params['template'] ?? 'license_details', $license['owner_id']),
            'site_url' => rtrim(base_url(), '/'),
            'support_email' => $myConfig['replyToEmail'],
            'app_name' => $myConfig['appName'] ?? '',
            'from_email' => $myConfig['fromEmail'],
            'email_logo' => $email_logo ? 'cid:' . $email_logo : '',
            'company_name' => $myConfig['userCompanyName'] ?? '',
            'company_address' => $myConfig['userCompanyAddress'] ?? '',
            'subject' => $this->getLicenseEmailSubject($params['template'] ?? 'license_details', $license['owner_id']),
            'product_guide' => empty($productGuide) ? '' : html_entity_decode($productGuide),
            'template' => $params['template']
        ];
    }
	
    private function renderLicenseEmailContent(int $userID, string $template, array $emailData): string
    {
        $this->myConfig = getMyConfig('', $userID);
        $emailTemplateSet = $this->myConfig['selectedEmailTemplate'] ?? 'default_email_template';
    
        if (!$template) {
            log_message('error', '[EmailService] ' . lang('Notifications.missing_required_parameters', ['template_name' => $template]));
            return '';
        }
    
        if (!$emailTemplateSet) {
            log_message('error', '[EmailService] No default template selected for user ID: ' . $userID);
            return '';
        }
    
        $emailTemplatePath = USER_DATA_PATH . $userID . '/' . $this->myConfig['userEmailTemplatesPath'] . $emailTemplateSet . '/';
    
        try {
            // Render the content template
            $content = $this->templateRenderer->renderUserTemplate( $emailTemplatePath . $template . '.php', ['emailData' => $emailData]);
            
            // Read the layout template
            $layoutPath = $emailTemplatePath . $emailTemplateSet . '.php';
            if (!file_exists($layoutPath)) {
                log_message('error', '[EmailService] Layout template not found: ' . $layoutPath);
                return $content; // Return content without layout if layout doesn't exist
            }
            
            $layoutContent = file_get_contents($layoutPath);
            
            // Replace the {content} placeholder in the layout with the rendered content
            $finalContent = str_replace('{content}', $content, $layoutContent);
            
            // Render the final content with all variables replaced
            $renderedContent = $this->templateRenderer->renderString($finalContent, [
                'data' => [
                    'emailData' => $emailData,
                    'content' => $content
                ]
            ]);
            
            log_message('info', '[EmailService] User email template content path: ' . $emailTemplatePath . $template . '.php');
            log_message('info', '[EmailService] User email template layout path: ' . $layoutPath);
    
            return $renderedContent;
        } catch (\RuntimeException $e) {
            log_message('error', '[EmailService] Failed to render email template: ' . $e->getMessage());
            return '';
        }
    }
	
    private function renderPlainTextEmail(int $userID, string $templateFile, array $emailData): string
    {
        $this->myConfig = getMyConfig('', $userID);
        $emailTemplateSet = $this->myConfig['selectedEmailTemplate'] ?? null;

        if (!$emailTemplateSet) {
            log_message('error', '[EmailService] No default template selected for user ID: ' . $userID);
            return '';
        }

        $emailTemplatePath = USER_DATA_PATH . $userID . '/' . $this->myConfig['userEmailTemplatesPath'] . $emailTemplateSet . '/';
        $template = basename($templateFile, '.php');

        try {
            // Render the content template
            $htmlContent = $this->templateRenderer->renderUserTemplate( $emailTemplatePath . $template . '.php', ['emailData' => $emailData]);
            
            // Check if footer file exists and render it
            $footerTemplate = $emailTemplatePath . 'footer.php';

            if (file_exists($footerTemplate)) {
                $footerContent = $this->templateRenderer->renderUserTemplate( $footerTemplate, ['emailData' => $emailData]);
                $htmlContent .= $footerContent;
            }

            return $this->convertHtmlToPlainText($htmlContent);
        } catch (\RuntimeException $e) {
            log_message('error', '[EmailService] Failed to render plain text email template: ' . $e->getMessage());
            return '';
        }
    }
    
    private function convertHtmlToPlainText(string $htmlContent): string
    {
        // Convert HTML entities to their corresponding characters
        $plainText = html_entity_decode($htmlContent, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
        // Handle heading classes
        $plainText = preg_replace('/<h[1-6]\s+class="heading"[^>]*>(.*?)<\/h[1-6]>/is', "- $1 -\n", $plainText);
        $plainText = preg_replace('/<p\s+class="subheading"[^>]*>(.*?)<\/p>/is', "$1\n", $plainText);

        // Handle signature
        $plainText = preg_replace('/<p\s+class="signature"[^>]*>(.*?)<\/p>/i', "\n- $1", $plainText);

        // Handle footer
        $plainText = preg_replace('/<div\s+class="footer"[^>]*>(.*?)<\/div>/is', "——————————$1", $plainText);
    
        // Generic headings
        $plainText = preg_replace('/<h[1-6][^>]*>(.*?)<\/h[1-6]>/is', "$1", $plainText);
    
        // Paragraphs
        $plainText = preg_replace('/<p[^>]*>(.*?)<\/p>/is', "$1", $plainText);
    
        // Line breaks
        $plainText = preg_replace('/<br\s*\/?>/i', "\n", $plainText);
    
        // Lists
        $plainText = preg_replace('/<li[^>]*>(.*?)<\/li>/is', "• $1", $plainText);
        $plainText = preg_replace('/\n+(.*? • )/', "$1", $plainText, 1); // no new line before the list
        $plainText = preg_replace('/(• .*?)\n+/', "$1\n", $plainText); // no new line after the list
        $plainText = preg_replace('/<(ul|ol)[^>]*>(.*?)<\/(ul|ol)>/is', "$2", $plainText);
    
        // Handle tables with double lines before and after
        $plainText = preg_replace_callback('/<table[^>]*>.*?<\/table>/is', function($matches) {
            // Extract all rows from the table
            preg_match_all('/<tr>\s*<t[hd][^>]*>(.*?)<\/t[hd]>\s*<t[hd][^>]*>(.*?)<\/t[hd]>\s*<\/tr>/is', $matches[0], $rows);
            
            // Process each row and combine them
            $tableContent = '';
            if (!empty($rows[1]) && !empty($rows[2])) {
                for ($i = 0; $i < count($rows[1]); $i++) {
                    // Strip any HTML tags and trim whitespace
                    $col1 = trim(strip_tags($rows[1][$i]));
                    $col2 = trim(strip_tags($rows[2][$i]));
                    $tableContent .= "\n|| $col1: $col2";
                }
            }
            
            return "——————————" . $tableContent . "\n——————————";
        }, $plainText);
        
        // Special boxes with emojis
        $plainText = preg_replace('/<div\s+class="success-box"[^>]*>.*?<span[^>]*>&#9989;<\/span>\s*(.*?)<\/h4>\s*(.*?)<\/div>/is', "✅ $1$2", $plainText);
        $plainText = preg_replace('/<div\s+class="warning-box"[^>]*>.*?<span[^>]*>&#9888;<\/span>\s*(.*?)<\/h4>\s*(.*?)<\/div>/is', "⚠ $1$2", $plainText);
        $plainText = preg_replace('/<div\s+class="info-box"[^>]*>.*?<span[^>]*>&#8505;<\/span>\s*(.*?)<\/h4>\s*(.*?)<\/div>/is', "ℹ $1$2", $plainText);
        $plainText = preg_replace('/<div\s+class="danger-box"[^>]*>.*?<span[^>]*>&#10071;<\/span>\s*(.*?)<\/h4>\s*(.*?)<\/div>/is', "❗$1$2", $plainText);
        
        // Notes section
        $plainText = preg_replace('/<div\s+class="notes"[^>]*>/i', "Note:", $plainText);

        // Handle the button html
        $plainText = preg_replace('/<a\s+[^>]*href="([^"]+)"[^>]*>([^<]+)<\/a>/i', '$1', $plainText);
        $plainText = preg_replace('/<a\s+href="([^"]+)"\s+class="button-[^"]+">([^<]+)<\/a>/i', '$1', $plainText);
    
        // Remove any remaining HTML tags
        $plainText = strip_tags($plainText);
    
        // Whitespace cleanup
        $plainText = str_replace('&nbsp;', '', $plainText);
        $plainText = preg_replace('/[ \t]+/', ' ', $plainText); // Collapse spaces/tabs
        // $plainText = preg_replace('/\s*\n\s*/', "\n", $plainText); // Trim around newlines
        $plainText = preg_replace('/\n{3,}/', "\n\n", $plainText); // Max 2 consecutive newlines
    
        // Ensure no blank line between emojis and descriptions
        $plainText = preg_replace('/(\n)(✅|⚠|ℹ|❗)/', "$2", $plainText);

        // Add a newline before the footer if not already present
        $plainText = preg_replace('/\n-----------------------/', "\n-----------------------", $plainText);
    
        // Trim final output
        $plainText = trim($plainText);
    
        return $plainText;
    } 

    private function getLicenseEmailSubject(string $template, int $userID): string
    {
        $this->myConfig = getMyConfig('', $userID);
        
        $subjects = [
            'license_details' => $this->myConfig['subject_license_details'] ?? 'Your License Details',
            'license_activation' => $this->myConfig['subject_license_details'] ?? 'Your License Has Been Activated',
            'reminder_expiring_license' => $this->myConfig['reminderEmailSubject'] ?? 'Your License is Expiring Soon',
            'license_expired' => $this->myConfig['expiredLicenseEmailSubject'] ?? 'Your License Has Expired',
            'license_activation_notification' => $this->myConfig['newDomainDeviceEmailSubject'] ?? 'New Domain/Device Activated',
            'license_deactivation_notification' => $this->myConfig['unregisteredDomainDeviceEmailSubject'] ?? 'Domain/Device Deactivated',
            'test_email' => 'This is a test email from ' . $this->myConfig['appName']
        ];

        return $subjects[$template] ?? 'License Update';
    }

    private function getLicenseEmailMessage(string $template, int $userID): string
    {
        $this->myConfig = getMyConfig('', $userID);
        
        $messages = [
            'license_activation' => $this->myConfig['activationEmailMessage'] ?? 'Your license key was activated successfully',
            'reminder_expiring_license' => $this->myConfig['reminderEmailMessage'] ?? 'Your license key is about to expire',
            'license_expired' => $this->myConfig['expiredLicenseEmailMessage'] ?? 'Your license key has expired',
            'license_activation_notification' => $this->myConfig['newDomainDeviceEmailMessage'] ?? 'A new domain/device was registered to your license',
            'license_deactivation_notification' => $this->myConfig['unregisteredDomainDeviceEmailMessage'] ?? 'A domain/device was deactivated from your license',
            'test_email' => 'This is a test email from ' . $this->myConfig['appName']
        ];

        return $messages[$template] ?? '';
    }

    /**
     * Sends subscription-related emails
     *
     * @param array $params Subscription details and email parameters
     * @return bool True if email sent successfully, false otherwise
     */
    public function sendSubscriptionEmail(array $params): bool
    {
        log_message('debug', '[EmailService] sendSubscriptionEmail called. Params: ' . json_encode($params));
        log_message('debug', '[EmailService] Called in context: ' . (is_object($this) ? 'Object' : 'Non-object'));

        if (!array_key_exists('userID', $params)) {
            log_message('error', '[EmailService] Failed to send subscription email: No valid user ID provided.');
            return false;
        }

        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($params['userID']);
        $myConfig =  $this->myConfig;

        if (!$user) {
            log_message('error', '[EmailService] Failed to send subscription email: User not found - ' . $params['userID']);
            return false;
        }

        $templateContent = $this->getSubscriptionEmailTemplate($params['template']);
        if (!$templateContent) {
            log_message('error', '[EmailService] Failed to send subscription email: Template not found - ' . $params['template']);
            return false;
        }

        $params['data']['template'] = $params['template'];
        $emailData = $this->prepareSubscriptionEmailData($user, $params['data'], $myConfig);
        $content = $this->renderSubscriptionEmailContent($params['template'], $emailData);
        $templateFile = APPPATH . 'Views/layouts/emails/' . $params['template'] . '.php';

        $emailParams = [
            'userID'    => $params['userID'],
            'toEmail'   => $user->email,
            'toName'    => $user->first_name . ' ' . $user->last_name,
            'fromEmail' => $myConfig['fromEmail'],
            'fromName'  => $myConfig['fromName'],
            'replyToEmail' => $myConfig['salesEmail'],
            'replyToName'  => $myConfig['salesName'],
            'subject'   => $emailData['subject'],
            'message'   => $content,
            'plain_text_message' => $templateFile ? $this->renderSubcriptionPlainTextEmail(0, $templateFile, $emailData) : '',
            'emailType' => $params['email_format'] ?? 'html'
        ];

        // return $emailParams['message']; // html format
        // return $this->renderSubcriptionPlainTextEmail(0, $templateFile, $emailData); // text format

        // Add notification to the user for subscription message
        $notificationMessage = $emailData['subject'];
        $notificationType = $params['template'];
        $url = base_url('subscription/my-subscription');
        $recipientUserId = $params['userID'];
        add_notification($notificationMessage, $notificationType, $url, $recipientUserId);

        $result = $this->sendEmail($emailParams);
        log_message('info', '[EmailService] Subscription email sending result: ' . ($result ? 'Success' : 'Failure'));
        return $result;
    }

    /**
     * Sends payment receipt email
     *
     * @param array $params Payment details and email parameters
     * @return bool True if email sent successfully, false otherwise
     */
    public function sendPaymentReceipt(array $params): bool
    {
        if (!array_key_exists('userID', $params)) {
            log_message('error', '[EmailService] Failed to send subscription email: No valid user ID provided.');
            return false;
        }

        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($params['userID']);

        if (!$user) {
            log_message('error', '[EmailService] Failed to send payment receipt: User not found - ' . $params['userID']);
            return false;
        }

        $templateContent = $this->getSubscriptionEmailTemplate('payment_receipt');
        if (!$templateContent) {
            log_message('error', '[EmailService] Failed to send payment receipt: Template not found');
            return false;
        }
		
		$myConfig = $this->myConfig;

        $emailData = $this->preparePaymentReceiptData($user, $params['data'], $myConfig);
        $emailData['subject'] = $this->getSubscriptionEmailSubject('payment_receipt');
        $content = $this->renderSubscriptionEmailContent('payment_receipt', $emailData);
        $templateFile = APPPATH . 'Views/layouts/emails/payment_receipt.php';

        // return $content; // html format
        // return $this->renderSubcriptionPlainTextEmail(0, $templateFile, $emailData); // text format

        $emailParams = [
            'userID' => $params['userID'],
            'toEmail' => $user->email,
            'toName' => $user->first_name . ' ' . $user->last_name,
            'fromEmail' => $myConfig['fromEmail'],
            'fromName' => $myConfig['fromName'],
            'replyToEmail' => $myConfig['salesEmail'],
            'replyToName' => $myConfig['salesName'],
            'subject' => $emailData['subject'],
            'message' => $content,
            'plain_text_message' => $templateFile ? $this->renderSubcriptionPlainTextEmail(0, $templateFile, $emailData) : '',
            'emailType' => $params['email_format'] ?? 'html'
        ];

        // Add notification to the user for successful subscription payment
        $notificationMessage = 'Your subscription payment has been successfully processed';
        $notificationType = 'subscription_payment';
        $url = base_url('subscription/my-subscription');
        $recipientUserId = $params['userID'];
        add_notification($notificationMessage, $notificationType, $url, $recipientUserId);
        
        $result = $this->sendEmail($emailParams);
        log_message('info', '[EmailService] Payment receipt email sending result: ' . ($result ? 'Success' : 'Failure'));
        return $result;
    }

    private function getSubscriptionEmailTemplate(string $template): ?string
    {
        $templatePath = APPPATH . 'Views/layouts/emails/' . $template . '.php';
        
        if (!file_exists($templatePath)) {
            return null;
        }

        return file_get_contents($templatePath);
    }

    private function getSubscriptionEmailSubject(string $template): string
    {
        $subjects = [
            'subscription_created' => 'Welcome to Your New Subscription',
            'subscription_activated' => 'Your Subscription Has Been Activated',
            'subscription_suspended' => 'Your Subscription Has Been Suspended',
            'subscription_cancelled' => 'Your Subscription Has Been Cancelled',
            'payment_receipt' => 'Payment Receipt',
            'payment_failed' => 'Payment Failed',
            'payment_denied' => 'Payment Denied Notice',
            'payment_pending' => 'Payment Pending Notice',
            'payment_refunded' => 'Payment Refund Notice',
            'subscription_expiring' => 'Your Subscription is Expiring Soon',
            'subscription_expired' => 'Your Subscription Has Expired'
        ];

        return $subjects[$template] ?? 'Subscription Update';
    }

    private function prepareSubscriptionEmailData($user, array $data, array $myConfig): array
    {
        $emailData = [
            'user_name' => $user->first_name . ' ' . $user->last_name,
            'user_email' => $user->email,
            'subscription_id' => $data['subscription_id'] ?? '',
            'package_name' => $data['package_name'] ?? '',
            'is_trial' => $data['is_trial'] ?? false,
            'cancellation_date' => formatDate($data['cancellation_date'] ?? '', $myConfig),
            'status' => ucfirst($data['status'] ?? ''),
            'start_date' => formatDate($data['start_time'] ?? '', $myConfig),
            'payment_date' => formatDate($data['payment_date'] ?? '', $myConfig),
            'next_billing_time' => formatDate($data['next_billing_time'] ?? '', $myConfig),
            'amount' => $this->formatCurrency($data['amount'] ?? 0, $data['currency'] ?? $myConfig['packageCurrency']),
            'site_url' => rtrim(base_url(), '/'),
            'login_url' => base_url('login'),
            'subscription_url' => base_url('subscription/my-subscription'),
            'support_email' => $myConfig['supportEmail'],
            'app_name' => $myConfig['appName'],
            'from_email' => $myConfig['fromEmail'],
            'company_name' => $myConfig['companyName'],
            'company_address' => $myConfig['companyAddress'],
            'retry_date_1' => formatDate($data['retry_date_1'] ?? '', $myConfig),
            'retry_date_2' => formatDate($data['retry_date_2'] ?? '', $myConfig),
            'retry_date_3' => formatDate($data['retry_date_3'] ?? '', $myConfig),
            'currency' => $data['currency'] ?? $myConfig['packageCurrency'],
            'refund_date' => formatDate($data['refund_date'] ?? '', $myConfig),
            'is_full_refund' => $data['is_full_refund'] ?? false,
            'payment_instructions' => !empty($data['payment_instructions']) ? 
                                        $data['payment_instructions'] : 
                                        (isset($data['payment_method']) && $data['payment_method'] === 'Offline' ? $myConfig['OFFLINE_PAYMENT_INSTRUCTIONS'] : ''),
            'payment_reference' => $data['payment_reference'] ?? '',
        ];

        // Add the subject to the email data
        $emailData['subject'] = $this->getSubscriptionEmailSubject($data['template'] ?? '');

        return $emailData;
    }

    private function preparePaymentReceiptData($user, array $data, array $myConfig): array
    {
        return [
            'user_name' => $user->first_name . ' ' . $user->last_name,
            'user_email' => $user->email,
            'payment_method' => $data['payment_method'] ?? '',
            'transaction_id' => $data['transaction_id'] ?? '',
            'amount' => $this->formatCurrency($data['amount'] ?? 0, $data['currency'] ?? $myConfig['packageCurrency']),
            'payment_date' => formatDate($data['payment_date'] ?? '', $myConfig),
            'subscription_id' => $data['subscription_id'] ?? '',
            'package_name' => $data['package_name'] ?? '',
            'next_billing_time' => formatDate($data['next_billing_time'] ?? '', $myConfig),
            'site_url' => rtrim(base_url(), '/'),
            'login_url' => base_url('login'),
            'subscription_url' => base_url('subscription/my-subscription'),
            'support_email' => $myConfig['supportEmail'],
            'app_name' => $myConfig['appName'],
            'from_email' => $myConfig['fromEmail'],
            'company_name' => $myConfig['companyName'],
            'company_address' => $myConfig['companyAddress'],
            'currency' => $data['currency'] ?? $myConfig['packageCurrency'],
            'subject' => $this->getSubscriptionEmailSubject('payment_receipt'),
        ];
    }

    private function renderSubscriptionEmailContent(string $template, array $emailData): string
    {
        $view = Services::renderer();
        $content = $view->setVar('emailData', $emailData)
                        ->render('layouts/emails/' . $template);

        return $view->setVar('content', $content)
                    ->setVar('emailData', $emailData)
                    ->render('layouts/emails/layout');
    }

    private function renderSubcriptionPlainTextEmail(int $userID, string $templateFile, array $emailData): string
    {
        $emailTemplatePath = APPPATH . 'Views/layouts/emails/';
        $template = basename($templateFile, '.php');

        try {
            // Render the content template
            $htmlContent = $this->templateRenderer->renderUserTemplate( $emailTemplatePath . $template . '.php', ['emailData' => $emailData]);
            
            // Check if footer file exists and render it
            $footerTemplate = $emailTemplatePath . 'footer.php';

            if (file_exists($footerTemplate)) {
                $footerContent = $this->templateRenderer->renderUserTemplate( $footerTemplate, ['emailData' => $emailData]);
                $htmlContent .= $footerContent;
            }

            return $this->convertHtmlToPlainText($htmlContent);
        } catch (\RuntimeException $e) {
            log_message('error', '[EmailService] Failed to render plain text email template: ' . $e->getMessage());
            return '';
        }
    }

    private function formatCurrency(float $amount, string $currency = 'USD'): string
    {
        return $currency . ' ' . number_format($amount, 2);
    }
}
