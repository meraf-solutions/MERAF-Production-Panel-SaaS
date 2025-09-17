<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\I18n\Time;

class NotificationModel extends Model
{
    protected $table = 'notifications';
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
    protected $allowedFields = ['user_id', 'type', 'message', 'link', 'is_read'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;

    public function getUnreadNotifications($userId, $limit = 5, $offset = 0)
    {
        $result = $this->where('user_id', $userId)
                       ->where('is_read', false)
                       ->orderBy('created_at', 'DESC')
                       ->findAll($limit, $offset);
        
        // log_message('debug', "NotificationModel::getUnreadNotifications - User: $userId, Limit: $limit, Offset: $offset, Results: " . count($result));
        
        return $result;
    }

    public function markAsRead($id)
    {
        return $this->update($id, ['is_read' => true]);
    }

    public function markAllAsRead($userId)
    {
        return $this->where('user_id', $userId)
                    ->where('is_read', false)
                    ->set('is_read', true)
                    ->update();
    }

    public function getUnreadCount($userId)
    {
        return $this->where('user_id', $userId)
                    ->where('is_read', false)
                    ->countAllResults();
    }
}
