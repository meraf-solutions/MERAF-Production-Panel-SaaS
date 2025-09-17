<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\I18n\Time;

class LicenseLogsModel extends Model
{
    protected $table = 'license_logs';
    protected $timezone;

    public function __construct()
    {
        parent::__construct();
        $this->timezone = 'UTC';
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
        'action',
        'time',
        'source',
        'is_valid',
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';

    // Define specific date formats for individual fields
    protected $sentDateField = 'time';
    protected $sentDateFormat = 'datetime';

    // Validation
    protected $validationRules = [
        'owner_id' => 'required|integer',
        'license_key' => 'required',
        'action' => 'required',
        'time' => 'required|valid_date',
        'source' => 'required',
        'is_valid' => 'required',
    ];
    protected $validationMessages = [
        'owner_id' => [
            'required' => 'The owner ID field is required.',
            'integer' => 'The owner ID must be an integer.',
        ],
        'license_key' => [
            'required'              => 'The license key field is required.',
        ],
        'action' => [
            'required' => 'The action field is required.'
        ],
        'time' => [
            'required' => 'The date & time field is required.',
            'valid_date' => 'Must be a valid date.'
        ],
        'source' => [
            'required' => 'The IP source field is required.'
        ],
        'is_valid' => [
            'required' => 'Specify if the request was valid (yes or no)'
        ],
    ];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;
}