<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\I18n\Time;

class LicenseEmailListModel extends Model
{
    protected $table = 'license_email_list';
    protected $db;
    protected $timezone;

    public function __construct()
    {
        parent::__construct();
        $this->timezone = 'UTC';
        $this->db = \Config\Database::connect();

        // Check if the index already exists
        $indexExists = $this->db->query("SHOW INDEX FROM {$this->table} WHERE Key_name = 'unique_license_email'")->getNumRows() > 0;

        if (!$indexExists) {
            try {
                $this->db->query("CREATE UNIQUE INDEX unique_license_email ON {$this->table} (license_key, sent_to)");
            } catch (\Exception $e) {
                // Log the error, but don't throw an exception
                log_message('error', 'Error creating unique index: ' . $e->getMessage());
            }
        }
    }

    protected function setSentDateField(array $data, $date): array
    {
        if (!empty($this->sentDateField) && !array_key_exists($this->sentDateField, $data)) {
            $data[$this->sentDateField] = Time::now('UTC')->toDateTimeString();
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
        'sent_to',
        'status',
        'sent',
        'date_sent',
        'disable_notifications'        
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';

    // Define specific date formats for individual fields
    protected $sentDateField = 'date_sent';
    protected $sentDateFormat = 'datetime';

    // Validation
    protected $validationRules = [
        'owner_id' => 'required|integer',
        'license_key' => 'required',
        'sent_to' => 'required|valid_email',
        'date_sent' => 'valid_date'
    ];
    protected $validationMessages = [
        'owner_id' => [
            'required' => 'The owner ID field is required.',
            'integer' => 'The owner ID must be an integer.',
        ],
        'license_key' => [
            'required' => 'The license key field is required.',
        ],
        'sent_to' => [
            'required' => 'The email field is required.',
            'valid_email' => 'Please provide a valid email address.'
        ],
        'date_sent' => [
            'valid_date' => 'Must be a valid date.'
        ]
    ];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    public function insertOrUpdate(array $data)
    {
        try {
            $existingRecord = $this->where('owner_id', $data['owner_id'])
                                    ->where('license_key', $data['license_key'])
                                   ->where('sent_to', $data['sent_to'])
                                   ->first();

            if ($existingRecord) {
                // Update existing record
                $this->update($existingRecord['id'], $data);
                return $existingRecord['id'];
            } else {
                // Insert new record
                return $this->insert($data);
            }
        } catch (DatabaseException $e) {
            // Handle unique constraint violation
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                log_message('error', 'Attempted to insert duplicate license_key and sent_to combination: ' . json_encode($data));
                return false;
            }
            throw $e;
        }
    }
}