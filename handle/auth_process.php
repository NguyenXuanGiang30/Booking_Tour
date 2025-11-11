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
        // Redirect if already logged in
        if (Auth::isLoggedIn()) {
            header('Location: ' . url('/'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $error = 'Email and password are required';
                require_once __DIR__ . '/../views/login.php';
                exit;
            }

            $user = authenticate_user($email, $password);

            if ($user) {
                Auth::login($user['user_id'], $user);
                header('Location: ' . url('/'));
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
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $fullName = trim($_POST['full_name'] ?? '');
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Validation
            if (empty($email) || empty($password) || empty($fullName)) {
                $error = 'All fields are required';
                require_once __DIR__ . '/../views/register.php';
                exit;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
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
        Auth::logout();
        header('Location: ' . url('/'));
        exit;
        break;

    default:
        require_once __DIR__ . '/../views/login.php';
        break;
}
