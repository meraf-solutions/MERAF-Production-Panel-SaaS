<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\I18n\Time;

class SubscriptionPaymentModel extends Model
{
    protected $table = 'subscription_payments';
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

    protected function setPaymentDateField(array $data, $date): array
    {
        if (!empty($this->paymentDateField)) {
            $data[$this->paymentDateField] = Time::now('UTC')->toDateTimeString();
        }
        return $data;
    }
	
    protected function setRefundDateField(array $data, $date): array
    {
        if (!empty($this->refundDateField)) {
            $data[$this->refundDateField] = Time::now('UTC')->toDateTimeString();
        }
        return $data;
    }
    
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'subscription_id',
        'transaction_id',
        'amount',
        'currency',
        'payment_status',
        'payment_date',
        'refund_id',
        'refund_amount',
        'refund_currency',
        'refund_date',
        'is_partial_refund'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $paymentDateField = 'payment_date';
    protected $refundDateField = 'refund_date';

    protected $validationRules = [
        'subscription_id' => 'required|string|max_length[100]',
        'transaction_id' => 'required|string|max_length[100]|is_unique[subscription_payments.transaction_id,id,{id}]',
        'amount' => 'required|numeric|greater_than_equal_to[0]',
        'currency' => 'required|exact_length[3]|alpha',
        'payment_status' => 'required|in_list[pending,completed,failed,refunded,partially_refunded]',
        'payment_date' => 'permit_empty|valid_date',
        'refund_id' => 'permit_empty|string|max_length[100]',
        'refund_amount' => 'permit_empty|numeric|greater_than_equal_to[0]',
        'refund_currency' => 'permit_empty|exact_length[3]|alpha',
        'refund_date' => 'permit_empty|valid_date',
        'is_partial_refund' => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'subscription_id' => [
            'required' => 'Subscription ID is required',
            'string' => 'Subscription ID must be a string',
            'max_length' => 'Subscription ID cannot exceed 100 characters'
        ],
        'transaction_id' => [
            'required' => 'Transaction ID is required',
            'string' => 'Transaction ID must be a string',
            'max_length' => 'Transaction ID cannot exceed 100 characters',
            'is_unique' => 'Transaction ID must be unique'
        ],
        'amount' => [
            'required' => 'Amount is required',
            'numeric' => 'Amount must be a number',
            'greater_than_equal_to' => 'Amount cannot be negative'
        ],
        'currency' => [
            'required' => 'Currency is required',
            'exact_length' => 'Currency must be a 3-letter code',
            'alpha' => 'Currency must contain only letters'
        ],
        'payment_status' => [
            'required' => 'Payment status is required',
            'in_list' => 'Invalid payment status'
        ],
        'payment_date' => [
            'valid_date' => 'Invalid payment date format'
        ],
        'refund_id' => [
            'string' => 'Refund ID must be a string',
            'max_length' => 'Refund ID cannot exceed 100 characters'
        ],
        'refund_amount' => [
            'numeric' => 'Refund amount must be a number',
            'greater_than_equal_to' => 'Refund amount cannot be negative'
        ],
        'refund_currency' => [
            'exact_length' => 'Refund currency must be a 3-letter code',
            'alpha' => 'Refund currency must contain only letters'
        ],
        'refund_date' => [
            'valid_date' => 'Invalid refund date format'
        ],
        'is_partial_refund' => [
            'in_list' => 'Partial refund flag must be either 0 or 1'
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
     * Get payment by transaction ID
     */
    public function getByTransactionId(string $transactionId)
    {
        return $this->where('transaction_id', $transactionId)->first();
    }

    /**
     * Get all payments for a subscription
     */
    public function getBySubscriptionId(string $subscriptionId)
    {
        return $this->where('subscription_id', $subscriptionId)
            ->orderBy('payment_date', 'DESC')
            ->findAll();
    }

    /**
     * Get latest payment for a subscription
     */
    public function getLatestPayment(string $subscriptionId)
    {
        return $this->where('subscription_id', $subscriptionId)
            ->orderBy('payment_date', 'DESC')
            ->first();
    }

    /**
     * Get payments within date range
     */
    public function getPaymentsInRange(string $startDate, string $endDate)
    {
        // Ensure dates are in UTC
        $startDateTime = Time::parse($startDate, 'UTC');
        $endDateTime = Time::parse($endDate, 'UTC');

        return $this->where('payment_date >=', $startDateTime->format('Y-m-d H:i:s'))
            ->where('payment_date <=', $endDateTime->format('Y-m-d H:i:s'))
            ->orderBy('payment_date', 'DESC')
            ->findAll();
    }

    /**
     * Get total payments amount for a subscription
     */
    public function getTotalPayments(string $subscriptionId)
    {
        $result = $this->selectSum('amount')
            ->where([
                'subscription_id' => $subscriptionId,
                'payment_status' => 'completed' // Only count completed payments
            ])
            ->first();
        
        return $result['amount'] ?? 0;
    }

    /**
     * Get payments by currency
     */
    public function getPaymentsByCurrency(string $currency)
    {
        return $this->where('currency', strtoupper($currency))
            ->orderBy('payment_date', 'DESC')
            ->findAll();
    }

    /**
     * Get total payments amount by currency
     */
    public function getTotalByCurrency(string $currency)
    {
        $result = $this->selectSum('amount')
            ->where([
                'currency' => strtoupper($currency),
                'payment_status' => 'completed' // Only count completed payments
            ])
            ->first();
        
        return $result['amount'] ?? 0;
    }

    /**
     * Get payments summary by date range
     */
    public function getPaymentsSummary(string $startDate, string $endDate)
    {
        // Ensure dates are in UTC
        $startDateTime = Time::parse($startDate, 'UTC');
        $endDateTime = Time::parse($endDate, 'UTC');
    
        // Add one day to the end date to include the full day
        $endDateTime = $endDateTime->addDays(1);
    
        $startDateTimeStr = $startDateTime->format('Y-m-d H:i:s');
        $endDateTimeStr = $endDateTime->format('Y-m-d H:i:s');
    
        log_message('debug', "[SubscriptionPaymentModel] Fetching payments summary from $startDateTimeStr to $endDateTimeStr");
    
        $completedPayments = $this->select('currency, COUNT(*) as count, SUM(amount) as total')
            ->where('payment_date >=', $startDateTimeStr)
            ->where('payment_date <', $endDateTimeStr)
            ->where('payment_status', 'completed')
            ->groupBy('currency')
            ->findAll();
    
        $refundedPayments = $this->select('currency, SUM(refund_amount) as refunded_total')
            ->where('payment_date >=', $startDateTimeStr)
            ->where('payment_date <', $endDateTimeStr)
            ->whereIn('payment_status', ['refunded', 'partially_refunded'])
            ->groupBy('currency')
            ->findAll();
    
        log_message('debug', '[SubscriptionPaymentModel] Completed payments: ' . json_encode($completedPayments));
        log_message('debug', '[SubscriptionPaymentModel] Refunded payments: ' . json_encode($refundedPayments));
    
        $result = [];
        foreach ($completedPayments as $payment) {
            $result[$payment['currency']] = [
                'currency' => $payment['currency'],
                'count' => intval($payment['count']),
                'total' => floatval($payment['total']),
                'refunded_total' => 0,
                'net_revenue' => floatval($payment['total'])
            ];
        }
    
        foreach ($refundedPayments as $refund) {
            if (isset($result[$refund['currency']])) {
                $result[$refund['currency']]['refunded_total'] = floatval($refund['refunded_total']);
                $result[$refund['currency']]['net_revenue'] -= floatval($refund['refunded_total']);
            } else {
                $result[$refund['currency']] = [
                    'currency' => $refund['currency'],
                    'count' => 0,
                    'total' => 0,
                    'refunded_total' => floatval($refund['refunded_total']),
                    'net_revenue' => -floatval($refund['refunded_total'])
                ];
            }
        }
    
        log_message('debug', '[SubscriptionPaymentModel] Final result: ' . json_encode(array_values($result)));
    
        return array_values($result);
    }

    /**
     * Get subscription report by date range
     */
    public function getSubscriptionReport(string $startDate, string $endDate)
    {
        // Ensure dates are in UTC
        $startDateTime = Time::parse($startDate, 'UTC');
        $endDateTime = Time::parse($endDate, 'UTC');
        
        // Add one day to the end date to include the full day
        $endDateTime = $endDateTime->addDays(1);
    
        $startDateTimeStr = $startDateTime->format('Y-m-d H:i:s');
        $endDateTimeStr = $endDateTime->format('Y-m-d H:i:s');
    
        log_message('debug', "[SubscriptionPaymentModel] Fetching subscription report from $startDateTimeStr to $endDateTimeStr");
    
        $result = $this->db->table('subscriptions')
            ->select('subscriptions.created_at, package.package_name, subscriptions.subscription_status, auth_identities.secret as email')
            ->join('package', 'package.id = subscriptions.package_id')
            ->join('users', 'users.id = subscriptions.user_id')
            ->join('auth_identities', 'auth_identities.user_id = users.id')
            ->where('subscriptions.created_at >=', $startDateTimeStr)
            ->where('subscriptions.created_at <', $endDateTimeStr)
            ->where('auth_identities.type', 'email_password')
            ->orderBy('subscriptions.created_at', 'DESC')
            ->get()
            ->getResultArray();
    
        log_message('debug', '[SubscriptionPaymentModel] Subscription report result: ' . json_encode($result));
    
        return $result;
    }

    /**
     * Check if a payment exists for a transaction
     */
    public function paymentExists(string $transactionId): bool
    {
        return $this->where('transaction_id', $transactionId)->countAllResults() > 0;
    }

    /**
     * Get total amount paid in a specific currency for a subscription
     */
    public function getTotalAmountByCurrency(string $subscriptionId, string $currency)
    {
        $result = $this->selectSum('amount')
            ->where([
                'subscription_id' => $subscriptionId,
                'currency' => strtoupper($currency),
                'payment_status' => 'completed' // Only count completed payments
            ])
            ->first();
        
        return $result['amount'] ?? 0;
    }

    /**
     * Get payments count for a subscription
     */
    public function getPaymentsCount(string $subscriptionId): int
    {
        return $this->where([
            'subscription_id' => $subscriptionId,
            'payment_status' => 'completed' // Only count completed payments
        ])->countAllResults();
    }

    /**
     * Update payment status by transaction ID
     */
    public function updatePaymentStatus(string $transactionId, string $status)
    {
        if($status === 'completed') {
            return $this->where('transaction_id', $transactionId)
            ->set([
                'payment_status' => $status,
                'payment_date' => $this->getCurrentUtcTime()
            ])
            ->update();
        }

        return $this->where('transaction_id', $transactionId)
            ->set(['payment_status' => $status])
            ->update();
    }

    /**
     * Update payment status by subscription ID
     */
    public function updatePaymentStatusBySubscriptionId(string $subscriptionId, string $status)
    {
        if($status === 'completed') {
            return $this->where('subscription_id', $subscriptionId)
            ->set([
                'payment_status' => $status,
                'payment_date' => $this->getCurrentUtcTime()
            ])
            ->update();
        }

        return $this->where('subscription_id', $subscriptionId)
            ->set(['payment_status' => $status])
            ->update();
    }
}
