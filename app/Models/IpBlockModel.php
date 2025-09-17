<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\I18n\Time;

class IpBlockModel extends Model
{
    protected $table = 'ip_block';
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

    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'owner_id',
        'ip_address',
        'license_key',
        'created_at'
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $dateFormat = 'datetime';

    // Validation
    protected $validationRules = [
        'owner_id' => 'required|integer',
        'ip_address' => 'required|valid_ip',
        'license_key' => 'required',
        'created_at' => 'required|valid_date'
    ];
    protected $validationMessages = [
        'owner_id' => [
            'required' => 'The owner ID field is required.',
            'integer' => 'The owner ID must be an integer.',
        ],
        'ip_address' => [
            'required' => 'IP address is required.',
            'valid_ip' => 'Must be a valid IP address.'
        ],
        'license_key' => [
            'required'              => 'The license key field is required.',
        ],
        'created_at' => [
            'required' => 'Created date is required.',
            'valid_date' => 'Must be a valid date.'
        ]
    ];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;
}
