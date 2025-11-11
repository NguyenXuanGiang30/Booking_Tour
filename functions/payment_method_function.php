<?php
/**
 * Payment Method functions - Handle payment method data operations
 */

require_once __DIR__ . '/helper_function.php';

/**
 * Get payment methods by user ID
 */
function get_payment_methods_by_user_id($userId) {
    $db = get_db();
    $stmt = $db->prepare("
        SELECT * FROM payment_methods 
        WHERE user_id = :user_id 
        ORDER BY is_default DESC, created_at DESC
    ");
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetchAll();
}

/**
 * Get default payment method by user ID
 */
function get_default_payment_method($userId) {
    $db = get_db();
    $stmt = $db->prepare("
        SELECT * FROM payment_methods 
        WHERE user_id = :user_id AND is_default = 1
        LIMIT 1
    ");
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetch();
}

/**
 * Create new payment method
 */
function create_payment_method($userId, $type, $name, $lastFour = '', $expiryDate = '', $isDefault = false) {
    $db = get_db();
    $id = generate_uuid();
    
    // If this is set as default, unset other defaults
    if ($isDefault) {
        $stmt = $db->prepare("UPDATE payment_methods SET is_default = 0 WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
    }
    
    $stmt = $db->prepare("
        INSERT INTO payment_methods (id, user_id, type, name, last_four, expiry_date, is_default, created_at, updated_at) 
        VALUES (:id, :user_id, :type, :name, :last_four, :expiry_date, :is_default, NOW(), NOW())
    ");
    
    return $stmt->execute([
        ':id' => $id,
        ':user_id' => $userId,
        ':type' => $type,
        ':name' => $name,
        ':last_four' => $lastFour,
        ':expiry_date' => $expiryDate,
        ':is_default' => $isDefault ? 1 : 0
    ]);
}

/**
 * Update payment method
 */
function update_payment_method($id, $userId, $data) {
    $db = get_db();
    
    // If setting as default, unset other defaults
    if (isset($data['is_default']) && $data['is_default']) {
        $stmt = $db->prepare("UPDATE payment_methods SET is_default = 0 WHERE user_id = :user_id AND id != :id");
        $stmt->execute([':user_id' => $userId, ':id' => $id]);
    }
    
    $fields = [];
    $params = [':id' => $id, ':user_id' => $userId];
    
    foreach ($data as $key => $value) {
        if (in_array($key, ['type', 'name', 'last_four', 'expiry_date', 'is_default'])) {
            $fields[] = "$key = :$key";
            if ($key === 'is_default') {
                $params[":$key"] = $value ? 1 : 0;
            } else {
                $params[":$key"] = $value;
            }
        }
    }
    
    if (empty($fields)) {
        return false;
    }
    
    $fields[] = "updated_at = NOW()";
    $sql = "UPDATE payment_methods SET " . implode(', ', $fields) . " WHERE id = :id AND user_id = :user_id";
    $stmt = $db->prepare($sql);
    return $stmt->execute($params);
}

/**
 * Delete payment method
 */
function delete_payment_method($id, $userId) {
    $db = get_db();
    $stmt = $db->prepare("DELETE FROM payment_methods WHERE id = :id AND user_id = :user_id");
    return $stmt->execute([':id' => $id, ':user_id' => $userId]);
}

/**
 * Set payment method as default
 */
function set_default_payment_method($id, $userId) {
    $db = get_db();
    
    // Unset all other defaults
    $stmt = $db->prepare("UPDATE payment_methods SET is_default = 0 WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $userId]);
    
    // Set this one as default
    $stmt = $db->prepare("UPDATE payment_methods SET is_default = 1, updated_at = NOW() WHERE id = :id AND user_id = :user_id");
    return $stmt->execute([':id' => $id, ':user_id' => $userId]);
}

