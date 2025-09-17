<?php

namespace App\Models;

use CodeIgniter\Model;

class LicenseRegisteredDomainsModel extends Model
{
    protected $table = 'license_registered_domains';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'owner_id',
        'license_key_id',
        'license_key',
        'domain_name',
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
        'domain_name' => 'required'
    ];
    protected $validationMessages = [
        'owner_id' => [
            'required' => 'The owner ID field is required.',
            'integer' => 'The owner ID must be an integer.',
        ],
        'license_key' => [
            'required'              => 'The license key field is required.',
        ],
        'domain_name' => [
            'required' => 'The registered domain field is required.',
        ]
    ];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    // protected $allowCallbacks = true;
    // protected $beforeInsert = [];
    // protected $afterInsert = [];
    // protected $beforeUpdate = [];
    // protected $afterUpdate = [];
    // protected $beforeFind = [];
    // protected $afterFind = [];
    // protected $beforeDelete = [];
    // protected $afterDelete = [];
}