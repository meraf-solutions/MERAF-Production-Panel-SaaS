<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\I18n\Time;

class ModuleCategoryModel extends Model
{
    protected $table = 'module_category';
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
        'category_name',
        'description',
        'sort_order',
        'status'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'category_name' => 'required|min_length[3]|max_length[100]|is_unique[module_category.category_name,id,{id}]',
        'description' => 'permit_empty',
        'sort_order' => 'permit_empty|integer|greater_than_equal_to[0]',
        'status' => 'required|in_list[active,inactive]'
    ];

    protected $validationMessages = [
        'category_name' => [
            'required' => 'Category name is required',
            'min_length' => 'Category name must be at least 3 characters long',
            'max_length' => 'Category name cannot exceed 100 characters',
            'is_unique' => 'This category name already exists'
        ],
        'sort_order' => [
            'integer' => 'Sort order must be a whole number',
            'greater_than_equal_to' => 'Sort order cannot be negative'
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Invalid status'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Get active categories ordered by sort order
     */
    public function getActiveCategories()
    {
        return $this->where('status', 'active')
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }

    /**
     * Update category sort order
     */
    public function updateSortOrder($categoryId, $sortOrder)
    {
        return $this->update($categoryId, ['sort_order' => $sortOrder]);
    }

    /**
     * Toggle category status
     */
    public function toggleStatus($categoryId)
    {
        $category = $this->find($categoryId);
        if ($category) {
            $newStatus = ($category['status'] === 'active') ? 'inactive' : 'active';
            return $this->update($categoryId, ['status' => $newStatus]);
        }
        return false;
    }

    /**
     * Get category with its modules
     */
    public function getCategoryWithModules($categoryId)
    {
        $category = $this->find($categoryId);
        if ($category) {
            $packageModulesModel = new PackageModulesModel();
            $category['modules'] = $packageModulesModel->where('module_category_id', $categoryId)
                                                     ->findAll();
            return $category;
        }
        return null;
    }

    /**
     * Get all categories with their modules
     */
    public function getAllCategoriesWithModules()
    {
        $categories = $this->findAll();
        $packageModulesModel = new PackageModulesModel();
        
        foreach ($categories as &$category) {
            $category['modules'] = $packageModulesModel->where('module_category_id', $category['id'])
                                                     ->findAll();
        }
        
        return $categories;
    }

    /**
     * Check if a category has any modules
     */
    public function hasModules($categoryId)
    {
        $packageModulesModel = new PackageModulesModel();
        return $packageModulesModel->where('module_category_id', $categoryId)
                                 ->countAllResults() > 0;
    }

    /**
     * Get default module categories
     */
    public function getDefaultCategories()
    {
        return [
            [
                'category_name' => 'License_Management',
                'description' => 'License management related modules including prefix and suffix controls and etc',
                'sort_order' => 1,
                'status' => 'active'
            ],
            [
                'category_name' => 'Email_Features',
                'description' => 'Email features related modules related to product, license and user-activity email messaging',
                'sort_order' => 1,
                'status' => 'active'
            ],
            [
                'category_name' => 'Digital_Product_Management',
                'description' => 'Digital product management modules including storage, count limits and etc',
                'sort_order' => 2,
                'status' => 'active'
            ]
        ];
    }

    /**
     * Initialize default categories if none exist
     */
    public function initializeDefaultCategories()
    {
        if ($this->countAll() === 0) {
            $defaultCategories = $this->getDefaultCategories();
            foreach ($defaultCategories as $category) {
                $this->insert($category);
            }
            return true;
        }
        return false;
    }
}
