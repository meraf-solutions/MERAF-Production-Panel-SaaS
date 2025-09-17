<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\I18n\Time;

class SubscriptionModel extends Model
{
    protected $table = 'subscriptions';
    protected $timezone;

    public function __construct()
    {
        parent::__construct();
        $this->timezone = 'UTC';
    }

    protected function setCreatedField(array $data, $date): array
    {
        if (!empty($this->createdField) && !array_key_exists($this->createdField, $data)) {
            $data[$this->createdField] = Time::now('UTC')->toDateTimeString();
        }
        return $data;
    }

    protected function setUpdatedField(array $data, $date): array
    {
        if (!empty($this->updatedField)) {
            $data[$this->updatedField] = Time::now('UTC')->toDateTimeString();
        }
        return $data;
    }

    protected function setStartDateField(array $data, $date): array
    {
        if (!empty($this->startDateField)) {
            $data[$this->startDateField] = Time::now('UTC')->toDateTimeString();
        }
        return $data;
    }
	
    protected function setEndDateField(array $data, $date): array
    {
        if (!empty($this->endDateField)) {
            $data[$this->endDateField] = Time::now('UTC')->toDateTimeString();
        }
        return $data;
    }
	
	protected function setTrialDateEndField(array $data, $date): array
    {
        if (!empty($this->trialDateEndField)) {
            $data[$this->trialDateEndField] = Time::now('UTC')->toDateTimeString();
        }
        return $data;
    }
	
	protected function setCancelledDateField(array $data, $date): array
    {
        if (!empty($this->cancelledDateField)) {
            $data[$this->cancelledDateField] = Time::now('UTC')->toDateTimeString();
        }
        return $data;
    }
	
	protected function setLastPaymentDateField(array $data, $date): array
    {
        if (!empty($this->lastPaymentDateField)) {
            $data[$this->lastPaymentDateField] = Time::now('UTC')->toDateTimeString();
        }
        return $data;
    }
	
	protected function setNextPaymentDateField(array $data, $date): array
    {
        if (!empty($this->nextPaymentDateField)) {
            $data[$this->nextPaymentDateField] = Time::now('UTC')->toDateTimeString();
        }
        return $data;
    }
	
	protected function setNextRetryDateField(array $data, $date): array
    {
        if (!empty($this->nextRetryDateField)) {
            $data[$this->nextRetryDateField] = Time::now('UTC')->toDateTimeString();
        }
        return $data;
    }
    
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'user_id',
        'package_id',
        'subscription_status',
        'is_reactivated',
        'payment_status',
        'payment_method',
        'transaction_id',
        'transaction_token',
        'subscription_reference',
        'currency',
        'amount_paid',
        'billing_cycle',
        'billing_period',
        'start_date',
        'end_date',
        'trial_ends_at',
        'cancelled_at',
        'cancellation_reason',
        'last_payment_date',
        'next_payment_date',
        'retry_count',
        'next_retry_date',
        'retry_dates',
        'last_payment_failure_reason',
        'sent_expiring_reminder'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $startDateField = 'start_date';
    protected $endDateField = 'end_date';
    protected $trialDateEndField = 'trial_ends_at';
    protected $cancelledDateField = 'cancelled_at';
    protected $lastPaymentDateField = 'last_payment_date';
    protected $nextPaymentDateField = 'next_payment_date';
    protected $nextRetryDateField = 'next_retry_date';

    protected $validationRules = [
        'user_id' => 'permit_empty|integer',
        'package_id' => 'required|integer',
        'subscription_status' => 'required|in_list[active,cancelled,expired,pending,failed,suspended]',
        'payment_status' => 'required|in_list[pending,completed,failed,refunded,partially_refunded]',
        'payment_method' => 'permit_empty|string|max_length[50]',
        'transaction_id' => 'permit_empty|string|max_length[255]',
        'transaction_token' => 'permit_empty|string|max_length[255]',
        'subscription_reference' => 'required|is_unique[subscriptions.subscription_reference,id,{id}]|max_length[100]',
        'currency' => 'required|exact_length[3]',
        'amount_paid' => 'required|numeric',
        'billing_cycle' => 'required|in_list[day,week,month,year,lifetime]',
        'billing_period' => 'required|integer',
        'start_date' => 'required|valid_date',
        'end_date' => 'permit_empty|valid_date',
        'trial_ends_at' => 'permit_empty|valid_date',
        'cancelled_at' => 'permit_empty|valid_date',
        'cancellation_reason' => 'permit_empty|string|max_length[500]',
        'last_payment_date' => 'permit_empty|valid_date',
        'next_payment_date' => 'permit_empty|valid_date',
        'retry_count' => 'permit_empty|integer',
        'next_retry_date' => 'permit_empty|valid_date',
        'retry_dates' => 'permit_empty|valid_json',
        'last_payment_failure_reason' => 'permit_empty|string|max_length[500]',
    ];

    protected $validationMessages = [
        'user_id' => [
            'integer' => 'User ID must be an integer'
        ],
        'package_id' => [
            'required' => 'Package ID is required',
            'integer' => 'Package ID must be an integer'
        ],
        'subscription_status' => [
            'required' => 'Subscription status is required',
            'in_list' => 'Invalid subscription status'
        ],
        'payment_status' => [
            'required' => 'Payment status is required',
            'in_list' => 'Invalid payment status'
        ],
        'subscription_reference' => [
            'required' => 'Subscription reference is required',
            'is_unique' => 'Subscription reference must be unique',
            'max_length' => 'Subscription reference cannot exceed 100 characters'
        ],
        'currency' => [
            'required' => 'Currency is required',
            'exact_length' => 'Currency must be a 3-letter code'
        ],
        'amount_paid' => [
            'required' => 'Amount paid is required',
            'numeric' => 'Amount paid must be a number'
        ],
        'billing_cycle' => [
            'required' => 'Billing cycle is required',
            'in_list' => 'Invalid billing cycle'
        ],
        'billing_period' => [
            'required' => 'Billing period is required',
            'integer' => 'Billing period must be an integer'
        ],
        'start_date' => [
            'required' => 'Start date is required',
            'valid_date' => 'Invalid start date format'
        ],
        'retry_dates' => [
            'valid_json' => 'Valid JSON string is required'
        ]
    ];

    protected $skipValidation = false;

    /**
     * Get current UTC datetime
     */
    private function getCurrentUtcTime(): string
    {
        return Time::now('UTC')->format('Y-m-d H:i:s');
    }

    /**
     * Get UTC datetime for a future date
     */
    private function getFutureUtcTime(string $interval): string
    {
        return Time::now('UTC')->modify($interval)->format('Y-m-d H:i:s');
    }

    /**
     * Custom validation to ensure date sequence is logical
     * 
     * @param string $str
     * @param string $field
     * @param array $data
     * @return bool
     */
    public function validate_date_sequence(string $str, string $field, array $data): bool
    {
        if (empty($str)) {
            return true;
        }

        $startDate = !empty($data['start_date']) ? strtotime($data['start_date']) : null;
        $currentDate = strtotime($str);

        // Ensure current date is after start date
        return $startDate === null || $currentDate >= $startDate;
    }

    /**
     * Cancel subscription with reason
     */
    public function cancelSubscription(string $subscriptionReference, ?string $reason = null, ?bool $noReactivation = false)
    {
        $data = [
            'subscription_status' => 'cancelled',
            'transaction_token' => null,
            'next_payment_date' => null,
            'cancelled_at' => $this->getCurrentUtcTime()
        ];

        // Add reason if provided
        if ($reason) {
            $data['cancellation_reason'] = $reason;
        }

        if($noReactivation) {
            $data['end_date'] = $this->getCurrentUtcTime();
        }

        return $this->where('subscription_reference', $subscriptionReference)
            ->set($data)
            ->update();
    }

    /**
     * Custom validation to ensure next payment date is in the future
     * 
     * @param string $str
     * @return bool
     */
    public function validate_future_date(string $str): bool
    {
        if (empty($str)) {
            return true;
        }

        $datetime = Time::parse($str, 'UTC');
        $now = Time::now('UTC');
        return $datetime > $now;
    }

    /**
     * Get subscription by subscription reference
     */
    public function getBySubscriptionReference(string $subscriptionReference)
    {
        return $this->where('subscription_reference', $subscriptionReference)->first();
    }

    /**
     * Get active subscription by user ID
     */
    public function getActiveByUserId(int $userId)
    {
        return $this->where([
            'user_id' => $userId,
            'subscription_status' => 'active',
        ])->first();
    }

    /**
     * Get all subscriptions by user ID
     */
    public function getAllByUserId(int $userId)
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Update subscription status
     */
    public function updateSubscriptionStatus(string $subscriptionReference, string $status)
    {
        if (in_array($status, ['cancelled', 'expired'])) {
            return $this->where('subscription_reference', $subscriptionReference)
                ->set([
                    'subscription_status' => $status,
                    'transaction_token' => null
                ])
                ->update();
        }

        if (in_array($status, ['active'])) {
            return $this->where('subscription_reference', $subscriptionReference)
                ->set([
                    'subscription_status' => $status,
                    'transaction_token' => null,
                    'start_date' => $this->getCurrentUtcTime()
                ])
                ->update();
        }
    
        return $this->where('subscription_reference', $subscriptionReference)
            ->set(['subscription_status' => $status])
            ->update();
    }

    /**
     * Check if a user has an existing active subscription
     * 
     * @param int $userId
     * @return bool
     */
    public function hasActiveSubscription(int $userId): bool
    {
        return $this->where('user_id', $userId)
                    ->where('subscription_status', 'active')
                    ->countAllResults() > 0;
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(string $subscriptionReference, string $status)
    {
        if (in_array($status, ['completed', 'refunded'])) {
            return $this->where('subscription_reference', $subscriptionReference)
                ->set([
                    'payment_status' => $status,
                    'transaction_token' => null
                ])
                ->update();
        }
    
        return $this->where('subscription_reference', $subscriptionReference)
            ->set(['payment_status' => $status])
            ->update();
    }

    /**
     * Update next payment date
     */
    public function updateNextPaymentDate(string $subscriptionReference, string $nextPaymentDate)
    {
        return $this->where('subscription_reference', $subscriptionReference)
            ->set(['next_payment_date' => $nextPaymentDate])
            ->update();
    }

    /**
     * Update last payment date
     */
    public function updateLastPaymentDate(string $subscriptionReference, string $paymentDate)
    {
        return $this->where('subscription_reference', $subscriptionReference)
            ->set(['last_payment_date' => $paymentDate])
            ->update();
    }

    /**
     * Get subscriptions expiring soon
     */
    public function getExpiringSoon(int $daysThreshold = 7)
    {
        $futureDate = $this->getFutureUtcTime("+{$daysThreshold} days");
        return $this->where([
            'subscription_status' => 'active',
            'end_date <=' => $futureDate
        ])->findAll();
    }

    /**
     * Get expired subscriptions
     */
    public function getExpired()
    {
        $currentUtcTime = $this->getCurrentUtcTime();
        return $this->where([
            'subscription_status' => 'active',
            'end_date <=' => $currentUtcTime
        ])->findAll();
    }

    /**
     * Get subscriptions by payment status
     */
    public function getByPaymentStatus(string $paymentStatus)
    {
        return $this->where('payment_status', $paymentStatus)->findAll();
    }

    /**
     * Update subscription end date
     */
    public function updateEndDate(string $subscriptionReference, string $endDate)
    {
        return $this->where('subscription_reference', $subscriptionReference)
            ->set(['end_date' => $endDate])
            ->update();
    }

    /**
     * Get subscriptions by package ID
     */
    public function getByPackageId(int $packageId)
    {
        return $this->where('package_id', $packageId)->findAll();
    }

    /**
     * Cancel active subscription
     */
    public function cancelUserActiveSubscription(int $userId, string $reason = 'User deletion'): bool
    {
        try {
            $activeSubscription = $this->getActiveByUserId($userId);

            if (!$activeSubscription) {
                return true; // Nothing to cancel
            }

            // Load payment method configuration
            $paymentMethodConfig = loadModuleMenu();

            $paymentMethodName = $activeSubscription['payment_method'];
            $paymentMethod = $paymentMethodConfig[$paymentMethodName] ?? $paymentMethodName;
            $paymentServiceName = $paymentMethod['service_name'] ?? $paymentMethodName;

            // Load payment service via ModuleScanner (adjust if you use different service loader)
            $moduleScanner = new \App\Services\ModuleScanner();
            $paymentService = $moduleScanner->loadLibrary(
                $paymentMethod,
                $paymentServiceName
            );

            if ($paymentService === null) {
                log_message('error', '[SubscriptionModel] Unknown payment method: ' . $paymentMethodName);
                return false;
            }

            // Cancel the subscription through payment gateway
            $paymentService->cancelSubscription(
                $activeSubscription['subscription_reference'],
                $reason
            );

            // Update local subscription status
            return $this->cancelSubscription(
                $activeSubscription['subscription_reference'],
                $reason
            );

        } catch (\Throwable $e) {
            log_message('error', '[SubscriptionModel] Subscription cancellation failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if a user has previously subscribed to a trial package (package_id 2)
     * 
     * @param int $userId User ID to check
     * @return bool True if user has previously had a trial, false otherwise
     */
    public function hasHadTrialPackage(int $userId): bool
    {
        
        // Get the default package
        $PackageModel = new \App\Models\PackageModel();
        $defaultPackage = $PackageModel->getDefaultPackage();
        
        if($defaultPackage) {
            return $this->where('user_id', $userId)
                    ->where('package_id', $defaultPackage['id'])
                    ->countAllResults() > 0;
        }
        
        return false;
    }
}
