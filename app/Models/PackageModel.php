<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\I18n\Time;

class PackageModel extends Model
{
    protected $table = 'package';
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

    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'owner_id',
        'package_name',
        'price',
        'validity',
        'validity_duration',
        'visible',
        'highlight',
        'is_default',
        'status',
        'sort_order',
        'package_modules'
    ];

    protected bool $allowEmptyInserts = true;

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'owner_id' => 'required|integer',
        'package_name' => 'required|min_length[3]|max_length[255]',
        'price' => 'required|numeric|greater_than_equal_to[0]',
        'validity' => 'required|integer|greater_than[0]',
        'validity_duration' => 'required|in_list[day,week,month,year,lifetime]',
        'visible' => 'required|in_list[on,off]',
        'highlight' => 'required|in_list[on,off]',
        'is_default' => 'required|in_list[on,off]',
        'status' => 'required|in_list[active,inactive,deleted]',
        'sort_order' => 'permit_empty|integer|greater_than_equal_to[0]'
    ];
    protected $validationMessages = [
        'owner_id' => [
            'required' => 'Owner ID is required',
            'integer' => 'Owner ID must be an integer'
        ],
        'package_name' => [
            'required' => 'Package name is required',
            'min_length' => 'Package name must be at least 3 characters long',
            'max_length' => 'Package name cannot exceed 255 characters'
        ],
        'price' => [
            'required' => 'Price is required',
            'numeric' => 'Price must be a number',
            'greater_than_equal_to' => 'Price cannot be negative'
        ],
        'validity' => [
            'required' => 'Validity period is required',
            'integer' => 'Validity must be a whole number',
            'greater_than' => 'Validity must be greater than 0'
        ],
        'validity_duration' => [
            'required' => 'Validity duration is required',
            'in_list' => 'Invalid validity duration'
        ],
        'visible' => [
            'required' => 'Visibility status is required',
            'in_list' => 'Invalid visibility status'
        ],
        'highlight' => [
            'required' => 'Highlight status is required',
            'in_list' => 'Invalid highlight status'
        ],
        'is_default' => [
            'required' => 'Default status is required',
            'in_list' => 'Invalid default status'
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Invalid status'
        ],
        'sort_order' => [
            'integer' => 'Sort order must be a whole number',
            'greater_than_equal_to' => 'Sort order cannot be negative'
        ]
    ];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Get active packages ordered by sort order
     */
    public function getActivePackages()
    {
        return $this->where('status', 'active')
                    ->where('visible', 'on')
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }

    /**
     * Get default package if exists
     */
    public function getDefaultPackage()
    {
        return $this->where('is_default', 'on')
                    ->where('status', 'active')
                    // ->where('visible', 'on')
                    ->first();
    }

    /**
     * Set a package as default and ensure no other package is default
     */
    public function setDefaultPackage($packageId)
    {
        $this->builder()->where('id !=', $packageId)->update(['is_default' => 'off']);
        return $this->update($packageId, ['is_default' => 'on']);
    }

    /**
     * Update package sort order
     */
    public function updateSortOrder($packageId, $sortOrder)
    {
        return $this->update($packageId, ['sort_order' => $sortOrder]);
    }

    /**
     * Toggle package visibility
     */
    public function toggleVisibility($packageId)
    {
        $package = $this->find($packageId);
        if ($package) {
            $newVisibility = ($package['visible'] === 'on') ? 'off' : 'on';
            return $this->update($packageId, ['visible' => $newVisibility]);
        }
        return false;
    }

    /**
     * Toggle package highlight status
     */
    public function toggleHighlight($packageId)
    {
        $package = $this->find($packageId);
        if ($package) {
            $newHighlight = ($package['highlight'] === 'on') ? 'off' : 'on';
            return $this->update($packageId, ['highlight' => $newHighlight]);
        }
        return false;
    }

    /**
     * Check if a package name already exists for an owner
     */
    public function isPackageNameTaken($packageName, $ownerId, $excludeId = null)
    {
        $builder = $this->where('package_name', $packageName)
                       ->where('owner_id', $ownerId);
        
        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }    
}