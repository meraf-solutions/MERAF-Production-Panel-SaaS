<?php

namespace App\Models;

use CodeIgniter\Model;

class LicenseRegisteredDevicesModel extends Model
{
    protected $table = 'license_registered_devices';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'owner_id',
        'license_key_id',
        'license_key',
        'device_name',
        'item_reference',      
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';

    // Define specific date formats for individual fields
    // protected $sentDateField = 'date_sent';
    // protected $sentDateFormat = 'datetime';

    // Validation
    protected $validationRules = [
        'owner_id' => 'required|integer',
        'license_key' => 'required',
        'device_name' => 'required'
    ];
    protected $validationMessages = [
        'owner_id' => [
            'required' => 'The owner ID field is required.',
            'integer' => 'The owner ID must be an integer.',
        ],
        'license_key' => [
            'required'              => 'The license key field is required.',
        ],
        'device_name' => [
            'required' => 'The registered device field is required.',
        ]
    ];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;
}