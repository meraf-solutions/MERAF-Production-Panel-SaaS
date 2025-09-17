<?php
/* Log a package upgrade
$changeModel = new SubscriptionChangeModel();
$changeModel->logChange(
    $subscriptionId,
    $newPackageId,
    'upgrade',
    $oldPackageId,
    'Upgraded to premium package'
);
*/
namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\I18n\Time;

class SubscriptionChangeModel extends Model
{
    protected $table = 'subscription_changes';
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

    protected function setEffectiveDateField(array $data, $date): array
    {
        if (!empty($this->effectiveDateField)) {
            $data[$this->effectiveDateField] = Time::now('UTC')->toDateTimeString();
        }
        return $data;
    }

    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'subscription_id',
        'previous_package_id',
        'new_package_id',
        'change_type',
        'reason',
        'effective_date'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $effectiveDateField = 'effective_date';

    protected $validationRules = [
        'subscription_id' => 'required|integer|is_natural_no_zero|exists[subscriptions.id]',
        'previous_package_id' => 'permit_empty|integer|is_natural_no_zero|exists[package.id]',
        'new_package_id' => 'required|integer|is_natural_no_zero|exists[package.id]',
        'change_type' => 'required|in_list[upgrade,downgrade,renewal,cancellation]',
        'reason' => 'permit_empty|string',
        'effective_date' => 'required|valid_date'
    ];

    protected $validationMessages = [
        'subscription_id' => [
            'required' => 'Subscription ID is required',
            'exists' => 'Subscription does not exist'
        ],
        'new_package_id' => [
            'required' => 'New package ID is required',
            'exists' => 'Package does not exist'
        ],
        'change_type' => [
            'required' => 'Change type is required',
            'in_list' => 'Invalid change type'
        ]
    ];

    /**
     * Log a subscription change
     */
    public function logChange(
        int $subscriptionId, 
        int $newPackageId, 
        string $changeType, 
        ?int $previousPackageId = null,
        ?string $reason = null
    ): bool {
        return $this->insert([
            'subscription_id' => $subscriptionId,
            'previous_package_id' => $previousPackageId,
            'new_package_id' => $newPackageId,
            'change_type' => $changeType,
            'reason' => $reason,
            'effective_date' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get subscription history
     */
    public function getSubscriptionHistory(int $subscriptionId)
    {
        return $this->where('subscription_id', $subscriptionId)
                   ->orderBy('effective_date', 'DESC')
                   ->findAll();
    }

    /**
     * Get recent changes
     */
    public function getRecentChanges(int $limit = 10)
    {
        return $this->orderBy('effective_date', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get changes by type
     */
    public function getChangesByType(string $changeType, ?int $subscriptionId = null)
    {
        $builder = $this->where('change_type', $changeType);
        
        if ($subscriptionId) {
            $builder->where('subscription_id', $subscriptionId);
        }
        
        return $builder->orderBy('effective_date', 'DESC')
                      ->findAll();
    }

    /**
     * Get upgrade history
     */
    public function getUpgradeHistory(int $subscriptionId)
    {
        return $this->where([
            'subscription_id' => $subscriptionId,
            'change_type' => 'upgrade'
        ])->orderBy('effective_date', 'DESC')
          ->findAll();
    }

    /**
     * Get downgrade history
     */
    public function getDowngradeHistory(int $subscriptionId)
    {
        return $this->where([
            'subscription_id' => $subscriptionId,
            'change_type' => 'downgrade'
        ])->orderBy('effective_date', 'DESC')
          ->findAll();
    }

    /**
     * Get change statistics
     */
    public function getChangeStats(int $subscriptionId)
    {
        $builder = $this->where('subscription_id', $subscriptionId);
        
        return [
            'total_changes' => $builder->countAllResults(false),
            'upgrades' => $builder->where('change_type', 'upgrade')->countAllResults(false),
            'downgrades' => $builder->where('change_type', 'downgrade')->countAllResults(false),
            'renewals' => $builder->where('change_type', 'renewal')->countAllResults(false),
            'cancellations' => $builder->where('change_type', 'cancellation')->countAllResults(false)
        ];
    }

    /**
     * Get last change
     */
    public function getLastChange(int $subscriptionId)
    {
        return $this->where('subscription_id', $subscriptionId)
                   ->orderBy('effective_date', 'DESC')
                   ->first();
    }

    /**
     * Check if subscription has been upgraded
     */
    public function hasBeenUpgraded(int $subscriptionId): bool
    {
        return $this->where([
            'subscription_id' => $subscriptionId,
            'change_type' => 'upgrade'
        ])->countAllResults() > 0;
    }

    /**
     * Check if subscription has been downgraded
     */
    public function hasBeenDowngraded(int $subscriptionId): bool
    {
        return $this->where([
            'subscription_id' => $subscriptionId,
            'change_type' => 'downgrade'
        ])->countAllResults() > 0;
    }
}
