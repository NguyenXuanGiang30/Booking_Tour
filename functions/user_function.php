<?php
/**
 * User functions - Handle user data operations
 */

require_once __DIR__ . '/helper_function.php';

/**
 * Create new user
 */
function create_user($email, $password, $fullName) {
    $db = get_db();
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $id = generate_uuid();

    $stmt = $db->prepare("
        INSERT INTO profiles (id, email, full_name, created_at, updated_at) 
        VALUES (:id, :email, :full_name, NOW(), NOW())
    ");
    $stmt->execute([
        ':id' => $id,
        ':email' => $email,
        ':full_name' => $fullName
    ]);

    // Store password in user_auth table
    $stmt = $db->prepare("
        INSERT INTO user_auth (user_id, email, password, created_at) 
        VALUES (:user_id, :email, :password, NOW())
    ");
    $stmt->execute([
        ':user_id' => $id,
        ':email' => $email,
        ':password' => $hashedPassword
    ]);

    return $id;
}

/**
 * Authenticate user (also checks admin table)
 * Returns user data with 'is_admin' flag if found in admins table
 */
function authenticate_user($email, $password) {
    $db = get_db();
    
    // First, try to authenticate as regular user
    $stmt = $db->prepare("
        SELECT ua.user_id, ua.password, p.* 
        FROM user_auth ua 
        JOIN profiles p ON p.id = ua.user_id 
        WHERE ua.email = :email
    ");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        unset($user['password']);
        $user['is_admin'] = false;
        return $user;
    }

    // If not found as user, check if it's an admin
    $stmt = $db->prepare("
        SELECT id, email, password, username, full_name, role, is_active
        FROM admins 
        WHERE (email = :email OR username = :email) AND is_active = 1
    ");
    $stmt->execute([':email' => $email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        unset($admin['password']);
        // Format admin data to match user structure
        $admin['user_id'] = $admin['id'];
        $admin['is_admin'] = true;
        return $admin;
    }

    return false;
}

/**
 * Get user by ID
 */
function get_user_by_id($id) {
    $db = get_db();
    $stmt = $db->prepare("SELECT * FROM profiles WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}

/**
 * Update user profile
 */
function update_user($id, $data) {
    $db = get_db();
    $fields = [];
    $params = [':id' => $id];

    foreach ($data as $key => $value) {
        if (in_array($key, ['full_name', 'phone', 'address', 'birthday', 'avatar_url'])) {
            // Handle birthday field - empty string should be NULL
            if ($key === 'birthday') {
                $value = trim($value);
                if (empty($value)) {
                    $fields[] = "$key = NULL";
                    // Don't add to params if it's NULL
                    continue;
                }
                // Validate date format
                $date = DateTime::createFromFormat('Y-m-d', $value);
                if ($date === false || $date->format('Y-m-d') !== $value) {
                    // Invalid date format, skip this field
                    continue;
                }
            }
            
            $fields[] = "$key = :$key";
            $params[":$key"] = $value;
        }
    }

    if (empty($fields)) {
        return false;
    }

    $fields[] = "updated_at = NOW()";
    $sql = "UPDATE profiles SET " . implode(', ', $fields) . " WHERE id = :id";
    $stmt = $db->prepare($sql);
    return $stmt->execute($params);
}

/**
 * Update user password
 */
function update_user_password($userId, $currentPassword, $newPassword) {
    $db = get_db();
    
    // Verify current password
    $stmt = $db->prepare("
        SELECT password 
        FROM user_auth 
        WHERE user_id = :user_id
    ");
    $stmt->execute([':user_id' => $userId]);
    $userAuth = $stmt->fetch();
    
    if (!$userAuth || !password_verify($currentPassword, $userAuth['password'])) {
        return false;
    }
    
    // Update password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $db->prepare("
        UPDATE user_auth 
        SET password = :password 
        WHERE user_id = :user_id
    ");
    
    return $stmt->execute([
        ':password' => $hashedPassword,
        ':user_id' => $userId
    ]);
}

/**
 * Get user payment statistics
 */
function get_user_payment_stats($userId) {
    $db = get_db();
    
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) as total_payments,
            COALESCE(SUM(total_price), 0) as total_spent,
            COALESCE(AVG(total_price), 0) as average_payment
        FROM bookings 
        WHERE user_id = :user_id AND status = 'paid'
    ");
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetch();
}
