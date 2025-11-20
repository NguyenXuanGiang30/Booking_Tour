<?php
/**
 * Authentication process
 */

require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../functions/user_function.php';
require_once __DIR__ . '/../functions/helper_function.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        // Set no-cache headers to prevent browser caching
        Auth::setNoCacheHeaders();
        
        // Redirect if already logged in
        if (Auth::isAdmin()) {
            header('Location: ' . url('/admin/dashboard'));
            exit;
        }
        if (Auth::isLoggedIn()) {
            header('Location: ' . url('/'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verify CSRF token
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!Auth::verifyCsrfToken($csrfToken)) {
                $error = 'Invalid security token. Please try again.';
                require_once __DIR__ . '/../views/login.php';
                exit;
            }
            
            require_once __DIR__ . '/../functions/helper_function.php';
            // Accept both email and username (for admin login)
            $emailOrUsername = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($emailOrUsername) || empty($password)) {
                $error = 'Email/Username and password are required';
                require_once __DIR__ . '/../views/login.php';
                exit;
            }

            $user = authenticate_user($emailOrUsername, $password);

            if ($user) {
                // Check if user is admin
                if (isset($user['is_admin']) && $user['is_admin'] === true) {
                    // Login as admin
                    Auth::adminLogin($user['user_id'], $user);
                    header('Location: ' . url('/admin/dashboard'));
                } else {
                    // Login as regular user
                    Auth::login($user['user_id'], $user);
                    header('Location: ' . url('/'));
                }
                exit;
            } else {
                $error = 'Invalid email or password';
                require_once __DIR__ . '/../views/login.php';
            }
        } else {
            require_once __DIR__ . '/../views/login.php';
        }
        break;

    case 'register':
        // Redirect if already logged in
        if (Auth::isLoggedIn()) {
            header('Location: ' . url('/'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verify CSRF token
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!Auth::verifyCsrfToken($csrfToken)) {
                $error = 'Invalid security token. Please try again.';
                require_once __DIR__ . '/../views/register.php';
                exit;
            }
            
            require_once __DIR__ . '/../functions/helper_function.php';
            $email = sanitize_email($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $fullName = trim($_POST['full_name'] ?? '');
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Validation
            if (empty($email) || empty($password) || empty($fullName)) {
                $error = 'All fields are required';
                require_once __DIR__ . '/../views/register.php';
                exit;
            }

            if (empty($email)) {
                $error = 'Invalid email format';
                require_once __DIR__ . '/../views/register.php';
                exit;
            }

            if (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters';
                require_once __DIR__ . '/../views/register.php';
                exit;
            }

            if ($password !== $confirmPassword) {
                $error = 'Passwords do not match';
                require_once __DIR__ . '/../views/register.php';
                exit;
            }

            try {
                $userId = create_user($email, $password, $fullName);
                $user = get_user_by_id($userId);
                Auth::login($userId, $user);
                header('Location: ' . url('/'));
                exit;
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'Duplicate entry') !== false || strpos($e->getMessage(), 'UNIQUE') !== false) {
                    $error = 'Email already exists';
                } else {
                    $error = 'Registration failed. Please try again.';
                }
                require_once __DIR__ . '/../views/register.php';
            } catch (Exception $e) {
                $error = 'Registration failed. Please try again.';
                require_once __DIR__ . '/../views/register.php';
            }
        } else {
            require_once __DIR__ . '/../views/register.php';
        }
        break;

    case 'logout':
        // Clear both user and admin sessions
        Auth::start();
        $wasAdmin = Auth::isAdmin();
        Auth::logout();
        // Redirect to appropriate page
        if ($wasAdmin) {
            header('Location: ' . url('/admin/login'));
        } else {
            header('Location: ' . url('/'));
        }
        exit;
        break;

    default:
        require_once __DIR__ . '/../views/login.php';
        break;
}
