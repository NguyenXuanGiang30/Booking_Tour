<?php
/**
 * Admin process
 */

require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../functions/admin_function.php';
require_once __DIR__ . '/../functions/tour_function.php';
require_once __DIR__ . '/../functions/helper_function.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        // Set no-cache headers to prevent browser caching
        Auth::setNoCacheHeaders();
        
        if (Auth::isAdmin()) {
            header('Location: ' . url('/admin/dashboard'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verify CSRF token
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!Auth::verifyCsrfToken($csrfToken)) {
                $error = 'Invalid security token. Please try again.';
                require_once __DIR__ . '/../views/admin/login.php';
                exit;
            }
            
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $error = 'Username and password are required';
                require_once __DIR__ . '/../views/admin/login.php';
                exit;
            }

            $admin = authenticate_admin($username, $password);

            if ($admin) {
                Auth::adminLogin($admin['id'], $admin);
                header('Location: ' . url('/admin/dashboard'));
                exit;
            } else {
                $error = 'Invalid username or password';
                require_once __DIR__ . '/../views/admin/login.php';
            }
        } else {
            require_once __DIR__ . '/../views/admin/login.php';
        }
        break;

    case 'logout':
        Auth::start();
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin']);
        unset($_SESSION['is_admin']);
        header('Location: ' . url('/admin/login'));
        exit;
        break;

    case 'dashboard':
        // Set no-cache headers (requireAdmin already sets them, but explicit for clarity)
        Auth::setNoCacheHeaders();
        Auth::requireAdmin();

        $db = Database::getInstance()->getConnection();

        // Statistics - Use prepared statements for security
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM tours");
        $stmt->execute();
        $totalTours = intval($stmt->fetch()['count'] ?? 0);
        
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM profiles");
        $stmt->execute();
        $totalUsers = intval($stmt->fetch()['count'] ?? 0);
        
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM bookings");
        $stmt->execute();
        $totalBookings = intval($stmt->fetch()['count'] ?? 0);
        
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM bookings WHERE status = :status");
        $stmt->execute([':status' => 'pending']);
        $pendingBookings = intval($stmt->fetch()['count'] ?? 0);
        
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM bookings WHERE status = :status");
        $stmt->execute([':status' => 'paid']);
        $paidBookings = intval($stmt->fetch()['count'] ?? 0);
        
        $stmt = $db->prepare("SELECT COALESCE(SUM(total_price), 0) as total FROM bookings WHERE status = :status");
        $stmt->execute([':status' => 'paid']);
        $totalRevenue = floatval($stmt->fetch()['total'] ?? 0);
        
        $stats = [
            'total_tours' => $totalTours,
            'total_users' => $totalUsers,
            'total_bookings' => $totalBookings,
            'pending_bookings' => $pendingBookings,
            'paid_bookings' => $paidBookings,
            'total_revenue' => $totalRevenue,
        ];

        // Bookings by month (last 6 months)
        $bookingsByMonth = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $stmt = $db->prepare("
                SELECT COUNT(*) as count 
                FROM bookings 
                WHERE DATE_FORMAT(created_at, '%Y-%m') = :month
            ");
            $stmt->execute([':month' => $month]);
            $bookingsByMonth[] = [
                'month' => date('M Y', strtotime("-$i months")),
                'count' => $stmt->fetch()['count']
            ];
        }

        // Revenue by month (last 6 months)
        $revenueByMonth = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $stmt = $db->prepare("
                SELECT COALESCE(SUM(total_price), 0) as total 
                FROM bookings 
                WHERE DATE_FORMAT(created_at, '%Y-%m') = :month 
                AND status = 'paid'
            ");
            $stmt->execute([':month' => $month]);
            $revenueByMonth[] = [
                'month' => date('M Y', strtotime("-$i months")),
                'total' => floatval($stmt->fetch()['total'])
            ];
        }

        // Bookings by status
        $stmt = $db->prepare("
            SELECT status, COUNT(*) as count 
            FROM bookings 
            GROUP BY status
        ");
        $stmt->execute();
        $bookingsByStatus = $stmt->fetchAll();

        // Recent bookings
        $stmt = $db->prepare("
            SELECT b.*, t.title as tour_title, p.full_name as user_name 
            FROM bookings b 
            JOIN tours t ON t.id = b.tour_id 
            JOIN profiles p ON p.id = b.user_id 
            ORDER BY b.created_at DESC 
            LIMIT 10
        ");
        $stmt->execute();
        $recentBookings = $stmt->fetchAll();

        require_once __DIR__ . '/../views/admin/dashboard.php';
        break;

    case 'tours':
        Auth::requireAdmin();
        require_once __DIR__ . '/../functions/pagination_function.php';
        require_once __DIR__ . '/../functions/tour_function.php';
        
        $search = trim($_GET['search'] ?? '');
        $filters = [];
        
        if (!empty($search)) {
            $filters['search'] = $search;
        }
        
        $itemsPerPage = 15;
        $currentPage = max(1, intval($_GET['page'] ?? 1));
        
        $db = Database::getInstance()->getConnection();
        $countSql = "SELECT COUNT(*) as total FROM tours WHERE 1=1";
        $countParams = [];
        
        if (!empty($search)) {
            $countSql .= " AND (title LIKE :search OR location LIKE :search OR description LIKE :search)";
            $countParams[':search'] = '%' . $search . '%';
        }
        
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($countParams);
        $totalTours = intval($countStmt->fetch()['total'] ?? 0);
        
        $pagination = get_pagination_info($totalTours, $currentPage, $itemsPerPage);
        
        $sql = "SELECT * FROM tours WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (title LIKE :search OR location LIKE :search OR description LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $pagination['items_per_page'];
        $params[':offset'] = $pagination['offset'];
        
        $stmt = $db->prepare($sql);
        foreach ($params as $key => $value) {
            if ($key === ':limit' || $key === ':offset') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        $stmt->execute();
        $tours = $stmt->fetchAll();
        
        require_once __DIR__ . '/../views/admin/tours.php';
        break;

    case 'tour-create':
        Auth::requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verify CSRF token
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!Auth::verifyCsrfToken($csrfToken)) {
                $error = 'Invalid security token. Please try again.';
                require_once __DIR__ . '/../views/admin/tour-form.php';
                exit;
            }
            
            $id = generate_uuid();
            
            // Validate and sanitize input
            $images = array_filter(array_map('trim', explode(',', $_POST['images'] ?? '')));
            $included = array_filter(array_map('trim', explode("\n", $_POST['included'] ?? '')));
            $excluded = array_filter(array_map('trim', explode("\n", $_POST['excluded'] ?? '')));
            
            $data = [
                'id' => $id,
                'title' => trim($_POST['title'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'location' => trim($_POST['location'] ?? ''),
                'price' => floatval($_POST['price'] ?? 0),
                'duration' => intval($_POST['duration'] ?? 1),
                'max_guests' => intval($_POST['max_guests'] ?? 10),
                'images' => json_encode($images),
                'itinerary' => json_encode([]), // Itinerary can be added later if needed
                'included' => json_encode($included),
                'excluded' => json_encode($excluded),
                'featured' => isset($_POST['featured']) ? 1 : 0,
                'category' => $_POST['category'] ?? 'adventure',
            ];
            
            // Validation
            if (empty($data['title']) || empty($data['location']) || $data['price'] <= 0) {
                $error = 'Title, location, and price are required';
            } else {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("
                    INSERT INTO tours (id, title, description, location, price, duration, max_guests, images, itinerary, included, excluded, featured, category) 
                    VALUES (:id, :title, :description, :location, :price, :duration, :max_guests, :images, :itinerary, :included, :excluded, :featured, :category)
                ");
                
                if ($stmt->execute($data)) {
                    header('Location: ' . url('/admin/tours?success=Tour created successfully'));
                    exit;
                } else {
                    $error = 'Failed to create tour';
                }
            }
        }
        
        require_once __DIR__ . '/../views/admin/tour-form.php';
        break;

    case 'tour-edit':
        Auth::requireAdmin();
        
        $id = $_GET['id'] ?? '';
        $tour = get_tour_by_id($id);
        
        if (!$tour) {
            header('Location: ' . url('/admin/tours'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verify CSRF token
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!Auth::verifyCsrfToken($csrfToken)) {
                $error = 'Invalid security token. Please try again.';
                require_once __DIR__ . '/../views/admin/tour-form.php';
                exit;
            }
            
            // Validate and sanitize input
            $images = array_filter(array_map('trim', explode(',', $_POST['images'] ?? '')));
            $included = array_filter(array_map('trim', explode("\n", $_POST['included'] ?? '')));
            $excluded = array_filter(array_map('trim', explode("\n", $_POST['excluded'] ?? '')));
            
            // Validation
            if (empty(trim($_POST['title'] ?? '')) || empty(trim($_POST['location'] ?? '')) || floatval($_POST['price'] ?? 0) <= 0) {
                $error = 'Title, location, and price are required';
            } else {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("
                    UPDATE tours SET 
                        title = :title,
                        description = :description,
                        location = :location,
                        price = :price,
                        duration = :duration,
                        max_guests = :max_guests,
                        images = :images,
                        itinerary = :itinerary,
                        included = :included,
                        excluded = :excluded,
                        featured = :featured,
                        category = :category,
                        updated_at = NOW()
                    WHERE id = :id
                ");
                
                $result = $stmt->execute([
                    ':id' => $id,
                    ':title' => trim($_POST['title'] ?? ''),
                    ':description' => trim($_POST['description'] ?? ''),
                    ':location' => trim($_POST['location'] ?? ''),
                    ':price' => floatval($_POST['price'] ?? 0),
                    ':duration' => intval($_POST['duration'] ?? 1),
                    ':max_guests' => intval($_POST['max_guests'] ?? 10),
                    ':images' => json_encode($images),
                    ':itinerary' => json_encode([]), // Itinerary can be added later if needed
                    ':included' => json_encode($included),
                    ':excluded' => json_encode($excluded),
                    ':featured' => isset($_POST['featured']) ? 1 : 0,
                    ':category' => $_POST['category'] ?? 'adventure',
                ]);

                if ($result) {
                    header('Location: ' . url('/admin/tours?success=Tour updated successfully'));
                    exit;
                } else {
                    $error = 'Failed to update tour';
                }
            }
        }
        
        require_once __DIR__ . '/../views/admin/tour-form.php';
        break;

    case 'tour-delete':
        Auth::requireAdmin();
        
        $id = $_GET['id'] ?? '';
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM tours WHERE id = :id");
        
        if ($stmt->execute([':id' => $id])) {
            header('Location: ' . url('/admin/tours?success=Tour deleted successfully'));
        } else {
            header('Location: ' . url('/admin/tours?error=Failed to delete tour'));
        }
        exit;
        break;

    case 'users':
        Auth::requireAdmin();
        require_once __DIR__ . '/../functions/pagination_function.php';
        
        $search = trim($_GET['search'] ?? '');
        
        $itemsPerPage = 15;
        $currentPage = max(1, intval($_GET['page'] ?? 1));
        
        $db = Database::getInstance()->getConnection();
        $countSql = "SELECT COUNT(*) as total FROM profiles WHERE 1=1";
        $countParams = [];
        
        if (!empty($search)) {
            $countSql .= " AND (full_name LIKE :search OR email LIKE :search OR phone LIKE :search)";
            $countParams[':search'] = '%' . $search . '%';
        }
        
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($countParams);
        $totalUsers = intval($countStmt->fetch()['total'] ?? 0);
        
        $pagination = get_pagination_info($totalUsers, $currentPage, $itemsPerPage);
        
        $sql = "SELECT * FROM profiles WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (full_name LIKE :search OR email LIKE :search OR phone LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $pagination['items_per_page'];
        $params[':offset'] = $pagination['offset'];
        
        $stmt = $db->prepare($sql);
        foreach ($params as $key => $value) {
            if ($key === ':limit' || $key === ':offset') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        require_once __DIR__ . '/../views/admin/users.php';
        break;

    case 'user-create':
        Auth::requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verify CSRF token
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!Auth::verifyCsrfToken($csrfToken)) {
                $error = 'Invalid security token. Please try again.';
                require_once __DIR__ . '/../views/admin/user-form.php';
                exit;
            }
            
            require_once __DIR__ . '/../functions/helper_function.php';
            require_once __DIR__ . '/../functions/user_function.php';
            
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $fullName = trim($_POST['full_name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $birthday = !empty($_POST['birthday']) ? $_POST['birthday'] : null;
            
            // Validation
            if (empty($email) || empty($password) || empty($fullName)) {
                $error = 'Email, password, and full name are required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Invalid email format';
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters';
            } else {
                // Check if email already exists
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT id FROM profiles WHERE email = :email");
                $stmt->execute([':email' => $email]);
                if ($stmt->fetch()) {
                    $error = 'Email already exists';
                } else {
                    // Create user
                    $userId = create_user($email, $password, $fullName);
                    
                    // Update additional fields
                    if ($userId) {
                        $updateData = [];
                        if (!empty($phone)) $updateData['phone'] = $phone;
                        if (!empty($address)) $updateData['address'] = $address;
                        if (!empty($birthday)) $updateData['birthday'] = $birthday;
                        
                        if (!empty($updateData)) {
                            update_user($userId, $updateData);
                        }
                        
                        header('Location: ' . url('/admin/users?success=User created successfully'));
                        exit;
                    } else {
                        $error = 'Failed to create user';
                    }
                }
            }
        }
        
        require_once __DIR__ . '/../views/admin/user-form.php';
        break;

    case 'user-edit':
        Auth::requireAdmin();
        
        require_once __DIR__ . '/../functions/user_function.php';
        
        $id = $_GET['id'] ?? '';
        $user = get_user_by_id($id);
        
        if (!$user) {
            header('Location: ' . url('/admin/users'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verify CSRF token
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!Auth::verifyCsrfToken($csrfToken)) {
                $error = 'Invalid security token. Please try again.';
                require_once __DIR__ . '/../views/admin/user-form.php';
                exit;
            }
            
            $email = trim($_POST['email'] ?? '');
            $fullName = trim($_POST['full_name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $birthday = !empty($_POST['birthday']) ? $_POST['birthday'] : null;
            $password = trim($_POST['password'] ?? '');
            
            // Validation
            if (empty($email) || empty($fullName)) {
                $error = 'Email and full name are required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Invalid email format';
            } else {
                $db = Database::getInstance()->getConnection();
                
                // Check if email is changed and already exists
                if ($email !== $user['email']) {
                    $stmt = $db->prepare("SELECT id FROM profiles WHERE email = :email AND id != :id");
                    $stmt->execute([':email' => $email, ':id' => $id]);
                    if ($stmt->fetch()) {
                        $error = 'Email already exists';
                    } else {
                        // Update email in both profiles and user_auth
                        $stmt = $db->prepare("UPDATE profiles SET email = :email WHERE id = :id");
                        $stmt->execute([':email' => $email, ':id' => $id]);
                        
                        $stmt = $db->prepare("UPDATE user_auth SET email = :email WHERE user_id = :id");
                        $stmt->execute([':email' => $email, ':id' => $id]);
                    }
                }
                
                if (!isset($error)) {
                    // Update profile
                    $updateData = [
                        'full_name' => $fullName,
                        'phone' => $phone,
                        'address' => $address,
                        'birthday' => $birthday
                    ];
                    
                    if (update_user($id, $updateData)) {
                        // Update password if provided
                        if (!empty($password)) {
                            if (strlen($password) < 6) {
                                $error = 'Password must be at least 6 characters';
                            } else {
                                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                                $stmt = $db->prepare("UPDATE user_auth SET password = :password WHERE user_id = :id");
                                $stmt->execute([':password' => $hashedPassword, ':id' => $id]);
                            }
                        }
                        
                        if (!isset($error)) {
                            header('Location: ' . url('/admin/users?success=User updated successfully'));
                            exit;
                        }
                    } else {
                        $error = 'Failed to update user';
                    }
                }
            }
        }
        
        require_once __DIR__ . '/../views/admin/user-form.php';
        break;

    case 'user-delete':
        Auth::requireAdmin();
        
        $id = $_GET['id'] ?? '';
        
        // Prevent deleting admin users (check if exists in admins table)
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id FROM admins WHERE id = :id");
        $stmt->execute([':id' => $id]);
        if ($stmt->fetch()) {
            header('Location: ' . url('/admin/users?error=Cannot delete admin user'));
            exit;
        }
        
        // Check if user has bookings
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM bookings WHERE user_id = :id");
        $stmt->execute([':id' => $id]);
        $bookingCount = intval($stmt->fetch()['count'] ?? 0);
        
        if ($bookingCount > 0) {
            header('Location: ' . url('/admin/users?error=Cannot delete user with existing bookings'));
            exit;
        }
        
        // Delete user (CASCADE will handle user_auth, bookings, etc.)
        $stmt = $db->prepare("DELETE FROM profiles WHERE id = :id");
        
        if ($stmt->execute([':id' => $id])) {
            header('Location: ' . url('/admin/users?success=User deleted successfully'));
        } else {
            header('Location: ' . url('/admin/users?error=Failed to delete user'));
        }
        exit;
        break;

    case 'bookings':
        Auth::requireAdmin();
        require_once __DIR__ . '/../functions/pagination_function.php';
        
        $search = trim($_GET['search'] ?? '');
        $statusFilter = $_GET['status'] ?? '';
        
        $itemsPerPage = 15;
        $currentPage = max(1, intval($_GET['page'] ?? 1));
        
        $db = Database::getInstance()->getConnection();
        $countSql = "
            SELECT COUNT(*) as total
            FROM bookings b 
            JOIN tours t ON t.id = b.tour_id 
            JOIN profiles p ON p.id = b.user_id 
            WHERE 1=1
        ";
        $countParams = [];
        
        if (!empty($search)) {
            $countSql .= " AND (t.title LIKE :search OR p.full_name LIKE :search OR p.email LIKE :search)";
            $countParams[':search'] = '%' . $search . '%';
        }
        
        if (!empty($statusFilter)) {
            $countSql .= " AND b.status = :status";
            $countParams[':status'] = $statusFilter;
        }
        
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($countParams);
        $totalBookings = intval($countStmt->fetch()['total'] ?? 0);
        
        $pagination = get_pagination_info($totalBookings, $currentPage, $itemsPerPage);
        
        $sql = "
            SELECT b.*, t.title as tour_title, p.full_name as user_name, p.email as user_email 
            FROM bookings b 
            JOIN tours t ON t.id = b.tour_id 
            JOIN profiles p ON p.id = b.user_id 
            WHERE 1=1
        ";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (t.title LIKE :search OR p.full_name LIKE :search OR p.email LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        
        if (!empty($statusFilter)) {
            $sql .= " AND b.status = :status";
            $params[':status'] = $statusFilter;
        }
        
        $sql .= " ORDER BY b.created_at DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $pagination['items_per_page'];
        $params[':offset'] = $pagination['offset'];
        
        $stmt = $db->prepare($sql);
        foreach ($params as $key => $value) {
            if ($key === ':limit' || $key === ':offset') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        $stmt->execute();
        $bookings = $stmt->fetchAll();

        foreach ($bookings as &$booking) {
            $booking['traveler_info'] = json_decode($booking['traveler_info'] ?? '{}', true);
        }
        
        require_once __DIR__ . '/../views/admin/bookings.php';
        break;

    case 'update-booking-status':
        Auth::requireAdmin();
        
        $id = $_POST['id'] ?? '';
        $status = $_POST['status'] ?? '';
        
        // Get old status before update
        require_once __DIR__ . '/../functions/booking_function.php';
        $oldBooking = get_booking_by_id($id);
        $oldStatus = $oldBooking['status'] ?? 'pending';
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE bookings SET status = :status, updated_at = NOW() WHERE id = :id");
        
        header('Content-Type: application/json');
        if ($stmt->execute([':status' => $status, ':id' => $id])) {
            // Send email notification if status changed
            if ($oldStatus !== $status && $oldBooking) {
                require_once __DIR__ . '/../includes/Email.php';
                require_once __DIR__ . '/../functions/user_function.php';
                require_once __DIR__ . '/../functions/tour_function.php';
                
                $booking = get_booking_by_id($id);
                if ($booking) {
                    $user = get_user_by_id($booking['user_id']);
                    $tour = get_tour_by_id($booking['tour_id']);
                    if ($user && $tour) {
                        Email::sendBookingStatusUpdate($booking, $user, $tour, $oldStatus, $status);
                    }
                }
            }
            
            echo json_encode(['success' => true]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Failed to update status']);
        }
        exit;
        break;

    case 'coupons':
        Auth::requireAdmin();
        require_once __DIR__ . '/../functions/pagination_function.php';
        require_once __DIR__ . '/../functions/coupon_function.php';
        
        $search = trim($_GET['search'] ?? '');
        $statusFilter = $_GET['status'] ?? '';
        $filters = [];
        
        if (!empty($search)) {
            $filters['search'] = $search;
        }
        
        if (!empty($statusFilter)) {
            $filters['status'] = $statusFilter;
        }
        
        $itemsPerPage = 15;
        $currentPage = max(1, intval($_GET['page'] ?? 1));
        
        $totalCoupons = count_coupons($filters);
        $pagination = get_pagination_info($totalCoupons, $currentPage, $itemsPerPage);
        
        $coupons = get_all_coupons($filters, $itemsPerPage, $pagination['offset']);
        
        require_once __DIR__ . '/../views/admin/coupons.php';
        break;

    case 'coupon-create':
        Auth::requireAdmin();
        require_once __DIR__ . '/../functions/coupon_function.php';
        require_once __DIR__ . '/../functions/tour_function.php';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'code' => trim($_POST['code'] ?? ''),
                'status' => $_POST['status'] ?? 'active',
                'discount_type' => $_POST['discount_type'] ?? 'percentage',
                'discount_value' => $_POST['discount_value'] ?? 0,
                'max_discount' => !empty($_POST['max_discount']) ? $_POST['max_discount'] : null,
                'min_amount' => $_POST['min_amount'] ?? 0,
                'usage_limit' => !empty($_POST['usage_limit']) ? intval($_POST['usage_limit']) : null,
                'valid_from' => $_POST['valid_from'] ?? '',
                'valid_to' => $_POST['valid_to'] ?? '',
                'applicable_tours' => !empty($_POST['applicable_tours']) ? $_POST['applicable_tours'] : [],
                'description' => $_POST['description'] ?? ''
            ];
            
            // Validate
            if (empty($data['code'])) {
                $error = 'Coupon code is required';
                $tours = get_all_tours();
                require_once __DIR__ . '/../views/admin/coupon-form.php';
                exit;
            }
            
            // Check if code already exists
            $existing = get_coupon_by_code($data['code']);
            if ($existing) {
                $error = 'Coupon code already exists';
                $tours = get_all_tours();
                require_once __DIR__ . '/../views/admin/coupon-form.php';
                exit;
            }
            
            $result = create_coupon($data);
            if ($result) {
                header('Location: ' . url('/admin/coupons?success=Coupon created successfully'));
                exit;
            } else {
                $error = 'Failed to create coupon';
                $tours = get_all_tours();
                require_once __DIR__ . '/../views/admin/coupon-form.php';
            }
        } else {
            $tours = get_all_tours();
            require_once __DIR__ . '/../views/admin/coupon-form.php';
        }
        break;

    case 'coupon-edit':
        Auth::requireAdmin();
        require_once __DIR__ . '/../functions/coupon_function.php';
        require_once __DIR__ . '/../functions/tour_function.php';
        
        $id = $_GET['id'] ?? '';
        $coupon = get_coupon_by_id($id);
        
        if (!$coupon) {
            header('Location: ' . url('/admin/coupons?error=Coupon not found'));
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'code' => trim($_POST['code'] ?? ''),
                'status' => $_POST['status'] ?? 'active',
                'discount_type' => $_POST['discount_type'] ?? 'percentage',
                'discount_value' => $_POST['discount_value'] ?? 0,
                'max_discount' => !empty($_POST['max_discount']) ? $_POST['max_discount'] : null,
                'min_amount' => $_POST['min_amount'] ?? 0,
                'usage_limit' => !empty($_POST['usage_limit']) ? intval($_POST['usage_limit']) : null,
                'valid_from' => $_POST['valid_from'] ?? '',
                'valid_to' => $_POST['valid_to'] ?? '',
                'applicable_tours' => !empty($_POST['applicable_tours']) ? $_POST['applicable_tours'] : [],
                'description' => $_POST['description'] ?? ''
            ];
            
            // Validate
            if (empty($data['code'])) {
                $error = 'Coupon code is required';
                $tours = get_all_tours();
                require_once __DIR__ . '/../views/admin/coupon-form.php';
                exit;
            }
            
            // Check if code already exists (except current coupon)
            $existing = get_coupon_by_code($data['code']);
            if ($existing && $existing['id'] !== $id) {
                $error = 'Coupon code already exists';
                $tours = get_all_tours();
                require_once __DIR__ . '/../views/admin/coupon-form.php';
                exit;
            }
            
            $result = update_coupon($id, $data);
            if ($result) {
                header('Location: ' . url('/admin/coupons?success=Coupon updated successfully'));
                exit;
            } else {
                $error = 'Failed to update coupon';
                $tours = get_all_tours();
                require_once __DIR__ . '/../views/admin/coupon-form.php';
            }
        } else {
            $tours = get_all_tours();
            require_once __DIR__ . '/../views/admin/coupon-form.php';
        }
        break;

    case 'coupon-delete':
        Auth::requireAdmin();
        require_once __DIR__ . '/../functions/coupon_function.php';
        
        $id = $_GET['id'] ?? '';
        $coupon = get_coupon_by_id($id);
        
        if (!$coupon) {
            header('Location: ' . url('/admin/coupons?error=Coupon not found'));
            exit;
        }
        
        $result = delete_coupon($id);
        if ($result) {
            header('Location: ' . url('/admin/coupons?success=Coupon deleted successfully'));
        } else {
            header('Location: ' . url('/admin/coupons?error=Failed to delete coupon'));
        }
        exit;
        break;

    case 'coupon-statistics':
        Auth::requireAdmin();
        require_once __DIR__ . '/../functions/coupon_function.php';
        
        $stats = get_coupon_statistics();
        
        require_once __DIR__ . '/../views/admin/coupon-statistics.php';
        break;

    case 'coupon-import':
        Auth::requireAdmin();
        require_once __DIR__ . '/../functions/coupon_function.php';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
            $file = $_FILES['file'];
            
            if ($file['error'] !== UPLOAD_ERR_OK) {
                header('Location: ' . url('/admin/coupons?error=File upload failed'));
                exit;
            }
            
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if ($extension === 'csv') {
                $successCount = 0;
                $errorCount = 0;
                $errors = [];
                
                $handle = fopen($file['tmp_name'], 'r');
                $firstLine = true;
                
                while (($row = fgetcsv($handle)) !== false) {
                    if ($firstLine) {
                        $firstLine = false;
                        continue; // Skip header
                    }
                    
                    if (count($row) < 7) continue; // Minimum required columns
                    
                    $data = [
                        'code' => trim($row[0] ?? ''),
                        'status' => !empty($row[1]) ? trim($row[1]) : 'active',
                        'discount_type' => !empty($row[2]) ? trim($row[2]) : 'percentage',
                        'discount_value' => floatval($row[3] ?? 0),
                        'max_discount' => !empty($row[4]) ? floatval($row[4]) : null,
                        'min_amount' => floatval($row[5] ?? 0),
                        'usage_limit' => !empty($row[6]) ? intval($row[6]) : null,
                        'valid_from' => $row[7] ?? date('Y-m-d H:i:s'),
                        'valid_to' => $row[8] ?? date('Y-m-d H:i:s', strtotime('+1 year')),
                        'applicable_tours' => !empty($row[9]) ? explode(',', trim($row[9])) : [],
                        'description' => $row[10] ?? ''
                    ];
                    
                    if (empty($data['code'])) {
                        $errorCount++;
                        continue;
                    }
                    
                    // Check if code already exists
                    $existing = get_coupon_by_code($data['code']);
                    if ($existing) {
                        $errorCount++;
                        $errors[] = "Coupon code {$data['code']} already exists";
                        continue;
                    }
                    
                    $result = create_coupon($data);
                    if ($result) {
                        $successCount++;
                    } else {
                        $errorCount++;
                    }
                }
                
                fclose($handle);
                
                $message = "Import completed: $successCount successful, $errorCount failed";
                if (!empty($errors)) {
                    $message .= ". Errors: " . implode(", ", array_slice($errors, 0, 5));
                }
                
                header('Location: ' . url('/admin/coupons?success=' . urlencode($message)));
            } else {
                header('Location: ' . url('/admin/coupons?error=Only CSV files are supported'));
            }
        } else {
            header('Location: ' . url('/admin/coupons?error=No file uploaded'));
        }
        exit;
        break;

    default:
        if (Auth::isAdmin()) {
            header('Location: ' . url('/admin/dashboard'));
        } else {
            header('Location: ' . url('/admin/login'));
        }
        exit;
        break;
}
