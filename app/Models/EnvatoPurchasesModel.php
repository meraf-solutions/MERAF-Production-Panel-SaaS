<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\I18n\Time;

class EnvatoPurchasesModel extends Model
{
    protected $table = 'envato_purchases';
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
        if (!empty($this->updatedField) && !array_key_exists($this->updatedField, $data)) {
            $data[$this->updatedField] = Time::now('UTC')->toDateTimeString();
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
        'purchase_code',
        'item_id',
        'item_name',
        'buyer_username',
        'buyer_email',
        'purchase_date',
        'license_type',
        'support_until',
        'processed',
        'license_created'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $dateFormat = 'datetime';

    protected $validationRules = [
		'owner_id' => 'required|integer',
        'purchase_code' => 'required|is_unique[envato_purchases.purchase_code,id,{id}]',
        'item_id' => 'required',
        'item_name' => 'required'
    ];

    protected $validationMessages = [
		'owner_id' => [
            'required' => 'The owner ID field is required.',
            'integer' => 'The owner ID must be an integer.',
        ],
        'purchase_code' => [
            'required'   => 'The purchase code field is required.',
            'is_unique'  => 'This purchase code has already been used.',
        ],
        'item_id' => [
            'required' => 'The item ID field is required.',
        ],
        'item_name' => [
            'required' => 'The item name field is required.',
        ],
    ];
}