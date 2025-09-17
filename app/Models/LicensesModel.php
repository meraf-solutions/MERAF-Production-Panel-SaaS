<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\I18n\Time;

class LicensesModel extends Model
{
    protected $table = 'licenses';
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

    protected function setActivatedField(array $data, $date): array
    {
        if (!empty($this->activatedField) && !array_key_exists($this->activatedField, $data)) {
            $data[$this->activatedField] = Time::now('UTC')->toDateTimeString();
        }
        return $data;
    }
	
	protected function setRenewedField(array $data, $date): array
    {
        if (!empty($this->renewedField) && !array_key_exists($this->renewedField, $data)) {
            $data[$this->renewedField] = Time::now('UTC')->toDateTimeString();
        }
        return $data;
    }
	
	protected function setExpiryField(array $data, $date): array
    {
        if (!empty($this->expiryField) && !array_key_exists($this->expiryField, $data)) {
            $data[$this->expiryField] = Time::now('UTC')->toDateTimeString();
        }
        return $data;
    }
	
	protected function setReminderField(array $data, $date): array
    {
        if (!empty($this->reminderField) && !array_key_exists($this->reminderField, $data)) {
            $data[$this->reminderField] = Time::now('UTC')->toDateTimeString();
        }
        return $data;
    }

    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'owner_id',
        'license_key',
        'max_allowed_domains',
        'max_allowed_devices',
        'license_status',
        'license_type',
        'first_name',
        'last_name',
        'email',
        'item_reference',
        'company_name',
        'txn_id',
        'manual_reset_count',
        'purchase_id_',
        'date_created',
        'date_activated',
        'date_renewed',
        'date_expiry',
        'reminder_sent',
        'reminder_sent_date',
        'product_ref',
        'until',
        'current_ver',
        'subscr_id',
        'billing_length',
        'billing_interval'
    ];

    protected bool $allowEmptyInserts = true;

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime'; // Set default date format for timestamps

    // Define specific date formats for individual fields
    protected $createdField = 'date_created'; // Use 'createdField' instead of 'createdDateField'
    protected $createdFormat = 'datetime'; // Format for date_created

    protected $activatedField = 'date_activated'; // Use 'activatedField' instead of 'activatedDateField'
    protected $activatedFormat = 'datetime'; // Format for date_activated

    protected $renewedField = 'date_renewed'; // Use 'renewedField' instead of 'renewedDateField'
    protected $renewedFormat = 'datetime'; // Format for date_renewed

    protected $expiryField = 'date_expiry'; // Use 'expiryField' instead of 'expiryDateField'
    protected $expiryFormat = 'datetime'; // Format for date_expiry

    protected $reminderField = 'reminder_sent_date'; // Use 'reminderField' instead of 'reminderDateField'
    protected $reminderFormat = 'datetime'; // Format for reminder_sent_date

    // Validation
    protected $validationRules = [
        'owner_id' => 'required|integer',
        'license_key' => 'required',
        'max_allowed_domains' => 'required|numeric',
        'max_allowed_devices' => 'required|numeric',
        'license_status' => 'required|in_list[pending,active,blocked,expired]',
        'license_type' => 'required|in_list[trial,subscription,lifetime]',
        'first_name' => 'required|regex_match[/^[\p{L}\p{M}\s.-]+$/u]',
        'last_name' => 'required|regex_match[/^[\p{L}\p{M}\s.-]+$/u]',
        'email' => 'required|valid_email',
        'purchase_id_' => 'required|alpha_numeric_punct',
        'txn_id' => 'required|alpha_numeric_punct',
        'product_ref' => 'required|alpha_numeric_punct',
    ];
    protected $validationMessages = [
        'owner_id' => [
            'required' => 'The owner ID field is required.',
            'integer' => 'The owner ID must be an integer.',
        ],
        'license_key' => [
            'required'              => 'The license key field is required.',
        ],
        'max_allowed_domains' => [
            'required'              => 'The max allowed domains field is required.',
            'numeric'                   => 'Please enter a numeric value in the max allowed domains field.',
        ],
        'max_allowed_devices' => [
            'required'              => 'The max allowed devices field is required.',
            'numeric'                   => 'Please enter a numeric value in the max allowed devices field.',
        ],
        'license_status' => [
            'required'              => 'The license status field selection is required.',
            'in_list'               => 'Invalid license status',
        ],
        'license_type' => [
            'required'              => 'The license type field selection is required.',
            'in_list'               => 'Invalid license type',
        ],
        'first_name' => [
            'required'              => 'The first name field is required.',
            'regex_match'           => 'The first name field may only contain letters, spaces, periods, and hyphens.'
        ],
        'last_name' => [
            'required'              => 'The last name field is required.',
            'regex_match'           => 'The last name field may only contain letters, spaces, periods, and hyphens.'
        ],
        'email' => [
            'required'              => 'The email field is required.',
            'valid_email'           => 'Please provide a valid email address.',
        ],
        'purchase_id_' => [
            'required'              => 'The purchase ID field is required.',
            'alpha_numeric_punct'   => 'The purchase ID field should only contains alphanumeric characters.',
        ],
        'txn_id' => [
            'required'              => 'The transaction ID field is required.',
            'alpha_numeric_punct'   => 'The transaction ID field should only contains alphanumeric characters.',
        ],
        'product_ref' => [
            'required'              => 'The product reference field is required.',
            'alpha_numeric_punct'   => 'The product reference field should only contains alphanumeric characters.',
        ],
    ];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Delete all licenses for a given owner_id (user ID).
     *
     * @param int $userID
     * @return bool
     */
    public function deleteByOwnerID(int $userID): bool
    {
        try {
            return $this->where('owner_id', $userID)->delete();
        } catch (\Throwable $e) {
            log_message('error', '[LicensesModel] Failed to delete licenses for user ID ' . $userID . ': ' . $e->getMessage());
            return false;
        }
    }
}