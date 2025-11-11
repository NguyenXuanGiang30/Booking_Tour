<?php
/**
 * Admin functions - Handle admin data operations
 */

require_once __DIR__ . '/helper_function.php';

/**
 * Authenticate admin
 */
function authenticate_admin($username, $password) {
    $db = get_db();
    $stmt = $db->prepare("SELECT * FROM admins WHERE (username = :username OR email = :email) AND is_active = 1");
    $stmt->execute([
        ':username' => $username,
        ':email' => $username
    ]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        unset($admin['password']);
        return $admin;
    }

    return false;
}

/**
 * Get admin by ID
 */
function get_admin_by_id($id) {
    $db = get_db();
    $stmt = $db->prepare("SELECT id, username, email, full_name, role, is_active, created_at FROM admins WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}

/**
 * Get all admins
 */
function get_all_admins() {
    $db = get_db();
    $stmt = $db->prepare("SELECT id, username, email, full_name, role, is_active, created_at FROM admins ORDER BY created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Create new admin
 */
function create_admin($username, $email, $password, $fullName, $role = 'admin') {
    $db = get_db();
    $id = generate_uuid();
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $db->prepare("
        INSERT INTO admins (id, username, email, password, full_name, role, is_active) 
        VALUES (:id, :username, :email, :password, :full_name, :role, 1)
    ");
    
    return $stmt->execute([
        ':id' => $id,
        ':username' => $username,
        ':email' => $email,
        ':password' => $hashedPassword,
        ':full_name' => $fullName,
        ':role' => $role
    ]);
}

/**
 * Update admin
 */
function update_admin($id, $data) {
    $db = get_db();
    $fields = [];
    $params = [':id' => $id];

    foreach ($data as $key => $value) {
        if (in_array($key, ['username', 'email', 'full_name', 'role', 'is_active'])) {
            $fields[] = "$key = :$key";
            $params[":$key"] = $value;
        }
    }

    if (empty($fields)) {
        return false;
    }

    $fields[] = "updated_at = NOW()";
    $sql = "UPDATE admins SET " . implode(', ', $fields) . " WHERE id = :id";
    $stmt = $db->prepare($sql);
    return $stmt->execute($params);
}

/**
 * Delete admin
 */
function delete_admin($id) {
    $db = get_db();
    $stmt = $db->prepare("DELETE FROM admins WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}
