<?php
/**
 * Create Full Access Role
 */
$admGroupRole = Mage::getModel('saas/user_role')->setData(array(
    'parent_id'     => 0,
    'tree_level'    => 1,
    'sort_order'    => 1,
    'role_type'     => 'G',
    'user_id'       => 0,
    'role_name'     => 'Full Access'
    ))
    ->save();

Mage::getModel('saas/user_rules')->setData(array(
    'role_id'       => $admGroupRole->getId(),
    'resource_id'   => 'all',
    'privileges'    => null,
    'assert_id'     => 0,
    'role_type'     => 'G',
    'permission'    => 'allow'
    ))
    ->save();
