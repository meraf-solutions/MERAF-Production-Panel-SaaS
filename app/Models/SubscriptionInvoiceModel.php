<?php
/* Generate an invoice
$invoiceModel = new SubscriptionInvoiceModel();
$invoiceId = $invoiceModel->insert([
    'subscription_id' => $subscriptionId,
    'amount' => 29.99,
    'currency' => 'USD',
    'billing_date' => date('Y-m-d H:i:s'),
    'due_date' => date('Y-m-d H:i:s', strtotime('+7 days'))
]);
*/
namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\I18n\Time;

class SubscriptionInvoiceModel extends Model
{
    protected $table = 'subscription_invoices';
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

    protected function setBillingDateField(array $data, $date): array
    {
        if (!empty($this->billingDateField)) {
            $data[$this->billingDateField] = Time::now('UTC')->toDateTimeString();
        }
        return $data;
    }

    protected function setDueDateField(array $data, $date): array
    {
        if (!empty($this->dueDateField)) {
            $data[$this->dueDateField] = Time::now('UTC')->toDateTimeString();
        }
        return $data;
    }

    protected function setPaidDateField(array $data, $date): array
    {
        if (!empty($this->paidDateField)) {
            $data[$this->paidDateField] = Time::now('UTC')->toDateTimeString();
        }
        return $data;
    }

    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'subscription_id',
        'invoice_number',
        'amount',
        'currency',
        'payment_status',
        'payment_method',
        'transaction_id',
        'billing_date',
        'due_date',
        'paid_date',
        'billing_details'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $billingDateField = 'billing_date';
    protected $dueDateField = 'due_date';
    protected $paidDateField = 'paid_date';


    protected $validationRules = [
        'subscription_id' => 'required|integer|is_natural_no_zero|exists[subscriptions.id]',
        'invoice_number' => 'required|string|max_length[50]|is_unique[subscription_invoices.invoice_number,id,{id}]',
        'amount' => 'required|numeric|greater_than[0]',
        'currency' => 'required|string|max_length[3]',
        'payment_status' => 'required|in_list[pending,paid,failed,refunded]',
        'payment_method' => 'permit_empty|string|max_length[50]',
        'transaction_id' => 'permit_empty|string|max_length[255]',
        'billing_date' => 'required|valid_date',
        'due_date' => 'required|valid_date',
        'paid_date' => 'permit_empty|valid_date',
        'billing_details' => 'permit_empty|valid_json'
    ];

    protected $validationMessages = [
        'subscription_id' => [
            'required' => 'Subscription ID is required',
            'exists' => 'Subscription does not exist'
        ],
        'invoice_number' => [
            'required' => 'Invoice number is required',
            'is_unique' => 'Invoice number must be unique'
        ],
        'amount' => [
            'required' => 'Amount is required',
            'greater_than' => 'Amount must be greater than 0'
        ],
        'payment_status' => [
            'required' => 'Payment status is required',
            'in_list' => 'Invalid payment status'
        ]
    ];

    protected $beforeInsert = ['generateInvoiceNumber'];

    /**
     * Generate a unique invoice number
     */
    protected function generateInvoiceNumber(array $data)
    {
        if (!isset($data['data']['invoice_number'])) {
            $prefix = 'INV';
            $year = date('Y');
            $month = date('m');
            $random = rand(1000, 9999);
            $data['data']['invoice_number'] = "{$prefix}{$year}{$month}-{$random}";
        }
        return $data;
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(int $invoiceId, string $transactionId = null): bool
    {
        $data = [
            'payment_status' => 'paid',
            'paid_date' => date('Y-m-d H:i:s')
        ];

        if ($transactionId) {
            $data['transaction_id'] = $transactionId;
        }

        return $this->update($invoiceId, $data);
    }

    /**
     * Get unpaid invoices
     */
    public function getUnpaidInvoices(int $subscriptionId = null)
    {
        $where = ['payment_status' => 'pending'];
        if ($subscriptionId) {
            $where['subscription_id'] = $subscriptionId;
        }
        
        return $this->where($where)
                   ->where('due_date <=', date('Y-m-d H:i:s'))
                   ->findAll();
    }

    /**
     * Get overdue invoices
     */
    public function getOverdueInvoices(int $daysOverdue = 30)
    {
        $date = date('Y-m-d H:i:s', strtotime("-{$daysOverdue} days"));
        
        return $this->where([
            'payment_status' => 'pending',
            'due_date <=' => $date
        ])->findAll();
    }

    /**
     * Get paid invoices for a subscription
     */
    public function getPaidInvoices(int $subscriptionId)
    {
        return $this->where([
            'subscription_id' => $subscriptionId,
            'payment_status' => 'paid'
        ])->orderBy('paid_date', 'DESC')
          ->findAll();
    }

    /**
     * Get invoice statistics
     */
    public function getInvoiceStats(int $subscriptionId)
    {
        $builder = $this->where('subscription_id', $subscriptionId);
        
        return [
            'total_invoices' => $builder->countAllResults(false),
            'paid_invoices' => $builder->where('payment_status', 'paid')->countAllResults(false),
            'pending_invoices' => $builder->where('payment_status', 'pending')->countAllResults(false),
            'failed_invoices' => $builder->where('payment_status', 'failed')->countAllResults(false)
        ];
    }
}
