<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\I18n\Time;

class PackageModulesModel extends Model
{
    protected $table = 'package_modules';
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

    protected $allowedFields = [
        'package_id',
        'module_category_id',
        'module_name',
        'module_description',
        'is_enabled',
        'value',
        'measurement_unit'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'package_id' => 'required|integer',
        'module_category_id' => 'required|integer',
        'module_name' => 'required|max_length[100]',
        'module_description' => 'permit_empty',
        'is_enabled' => 'required|in_list[yes,no]',
        'value' => 'permit_empty',
        'measurement_unit' => 'required|valid_json'
    ];

    protected $validationMessages = [
        'package_id' => [
            'required' => 'Package ID is required',
            'integer' => 'Package ID must be an integer'
        ],
        'module_category_id' => [
            'required' => 'Module category ID is required',
            'integer' => 'Module category ID must be an integer'
        ],
        'module_name' => [
            'required' => 'Module name is required',
            'max_length' => 'Module name cannot exceed 100 characters'
        ],
        'is_enabled' => [
            'required' => 'Module enabled status is required',
            'in_list' => 'Invalid module enabled status'
        ],
        'measurement_unit' => [
            'required' => 'Measurement unit is required',
            'valid_json' => 'Invalid measurement unit format'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Get default measurement unit for a module
     */
    public function getDefaultMeasurementUnit($moduleName=null)
    {
        $measurementUnits = [];
        $packageModules = $this->getPackageModules();

        foreach($packageModules as $packageModule) {
            $measurementUnitData = json_decode($packageModule['measurement_unit'], true);
            $measurementUnits[$packageModule['module_name']] = $measurementUnitData;
        }

        if($moduleName) {
            return $measurementUnits[$moduleName] ?? [
                'type' => 'checkbox',
                'label' => $moduleName,
                'description' => 'Enable/disable ' . $moduleName
            ];
        }

        return $measurementUnits;

    }

    /**
     * Get modules for a package
     */
    public function getPackageModules($packageId=null)
    {
        if($packageId) {
            return $this->where('package_id', $packageId)
            ->orderBy('module_category_id', 'ASC')
            ->findAll();
        }

        return $this->orderBy('module_category_id', 'ASC')->findAll();
    }

    /**
     * Get modules by category
     */
    public function getModulesByCategory($packageId, $categoryId)
    {
        return $this->where('package_id', $packageId)
                    ->where('module_category_id', $categoryId)
                    ->findAll();
    }

    /**
     * Update module value
     */
    public function updateModuleValue($moduleId, $value)
    {
        return $this->update($moduleId, ['value' => $value]);
    }

    /**
     * Toggle module enabled status
     */
    public function toggleModuleStatus($moduleId)
    {
        $module = $this->find($moduleId);
        if ($module) {
            $newStatus = ($module['is_enabled'] === 'yes') ? 'no' : 'yes';
            return $this->update($moduleId, ['is_enabled' => $newStatus]);
        }
        return false;
    }

    /**
     * Delete all modules for a package
     */
    public function deletePackageModules($packageId)
    {
        return $this->where('package_id', $packageId)->delete();
    }

    /**
     * Check if a module exists in a package
     */
    public function moduleExists($packageId, $moduleName)
    {
        return $this->where('package_id', $packageId)
                    ->where('module_name', $moduleName)
                    ->countAllResults() > 0;
    }

    /**
     * Get enabled modules for a package
     */
    public function getEnabledModules($packageId)
    {
        return $this->where('package_id', $packageId)
                    ->where('is_enabled', 'yes')
                    ->findAll();
    }
}
