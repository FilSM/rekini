<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\components\rbac;

use yii\db\Query;
use yii\rbac\DbManager;
use yii\rbac\Item;
use common\components\rbac\FilSMPermission;
use common\components\rbac\FilSMRole;

class FilSMDbManager extends DbManager {

    /**
     * @inheritdoc
     */
    protected function getItems($type) {
        $query = (new Query)
            ->from($this->itemTable)
            ->where(['type' => $type])
            ->orderBy('name');

        $items = [];
        foreach ($query->all($this->db) as $row) {
            $items[$row['name']] = $this->populateItem($row);
        }

        return $items;
    }

    /**
     * Populates an auth item with the data fetched from database
     * @param array $row the data from the auth item table
     * @return Item the populated auth item instance (either Role or Permission)
     */
    protected function populateItem($row) {
        $class = $row['type'] == Item::TYPE_PERMISSION ? FilSMPermission::className() : FilSMRole::className();

        if (!isset($row['data']) || ($data = @unserialize($row['data'])) === false) {
            $data = null;
        }

        return new $class([
            'id' => !empty($row['id']) ? $row['id'] : null,
            'name' => $row['name'],
            'type' => $row['type'],
            'description' => $row['description'],
            'ruleName' => $row['rule_name'],
            'data' => $data,
            'createdAt' => $row['created_at'],
            'updatedAt' => $row['updated_at'],
        ]);
    }

    public function getArrRolesByUser($userId)    {
        $roles = [];
        foreach (parent::getRolesByUser($userId) as $name => $role) {
            $roles[] = $name;
        }      
        return $roles;
    }    
}
