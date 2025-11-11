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
        if (Auth::isAdmin()) {
            header('Location: ' . url('/admin/dashboard'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        Auth::requireAdmin();

        $db = Database::getInstance()->getConnection();

        // Statistics
        $stats = [
            'total_tours' => $db->query("SELECT COUNT(*) as count FROM tours")->fetch()['count'],
            'total_users' => $db->query("SELECT COUNT(*) as count FROM profiles")->fetch()['count'],
            'total_bookings' => $db->query("SELECT COUNT(*) as count FROM bookings")->fetch()['count'],
            'pending_bookings' => $db->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'")->fetch()['count'],
            'paid_bookings' => $db->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'paid'")->fetch()['count'],
            'total_revenue' => floatval($db->query("SELECT COALESCE(SUM(total_price), 0) as total FROM bookings WHERE status = 'paid'")->fetch()['total'] ?? 0),
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

    default:
        if (Auth::isAdmin()) {
            header('Location: ' . url('/admin/dashboard'));
        } else {
            header('Location: ' . url('/admin/login'));
        }
        exit;
        break;
}
