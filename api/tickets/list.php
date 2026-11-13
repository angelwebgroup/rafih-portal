    <?php
    require_once __DIR__ . '/../helpers.php';
    require_once __DIR__ . '/../db.php';

    // Handle CORS
    ApiResponse::cors();

    RequestHelper::requireMethod('GET');

    try {
        // Require authentication
        $user = RequestHelper::requireAuth();
        
        $db = Database::getInstance()->getConnection();
        
        // Get query parameters
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(50, max(10, (int)($_GET['limit'] ?? 20)));
        $status = $_GET['status'] ?? '';
        $priority = $_GET['priority'] ?? '';
        $search = trim($_GET['search'] ?? '');
        $category = $_GET['category'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $assignedTo = $_GET['assigned_to'] ?? '';
        $sortBy = $_GET['sort_by'] ?? 'dt';
        $sortOrder = $_GET['sort_order'] ?? 'DESC';
        
        $offset = ($page - 1) * $limit;
        
        // Build WHERE clause
        $whereConditions = [];
        $params = [];
        
        // Non-admin users can only see their own tickets
        if (!$user['isadmin']) {
            $whereConditions[] = "email = ?";
            $params[] = $user['email'];
        }
        
        // Status filter
        if ($status !== '') {
            $whereConditions[] = "status = ?";
            $params[] = (int)$status;
        }
        
        // Priority filter
        if ($priority !== '') {
            $whereConditions[] = "priority = ?";
            $params[] = (int)$priority;
        }
        
        // Category filter
        if ($category !== '') {
            $whereConditions[] = "category = ?";
            $params[] = (int)$category;
        }
        
        // Date range filter
        if ($dateFrom !== '') {
            $whereConditions[] = "DATE(dt) >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo !== '') {
            $whereConditions[] = "DATE(dt) <= ?";
            $params[] = $dateTo;
        }
        
        // Assigned to filter
        if ($assignedTo !== '') {
            if ($assignedTo === 'unassigned') {
                $whereConditions[] = "(owner IS NULL OR owner = 0)";
            } elseif ($assignedTo === 'me') {
                $whereConditions[] = "owner = ? AND status NOT IN (3, 6, 8)";
                $params[] = $user['id'] ?? 1; // Default to 1 if user ID not available
            } else {
                $whereConditions[] = "owner = ?";
                $params[] = (int)$assignedTo;
            }
        }
        
        // Search filter
        if ($search !== '') {
            $whereConditions[] = "(subject LIKE ? OR message LIKE ? OR trackid LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        // Get total count for current filters
        $countQuery = "SELECT COUNT(*) FROM hesk_tickets $whereClause";
        $stmt = $db->prepare($countQuery);
        $stmt->execute($params);
        $totalCount = (int)$stmt->fetchColumn();
        
        // Calculate static totals (not affected by current filters) for filter chips
        $staticTotals = [
            'filtered' => [
                'all' => 0,
                'open' => 0,
                'by_status' => [],
                'assigned_to_me' => 0,
                'assigned_to_others' => 0,
                'assigned_to_others_by_me' => 0,
                'unassigned' => 0,
                'due_soon' => 0,
                'overdue' => 0
            ],
            'cancelled' => 0,
            'declined' => 0
        ];
        
        // Build WHERE clause for static totals (only user restriction, no other filters)
        $staticWhereConditions = [];
        $staticParams = [];
        
        // Non-admin users can only see their own tickets
        if (!$user['isadmin']) {
            $staticWhereConditions[] = "email = ?";
            $staticParams[] = $user['email'];
        }
        
        $staticWhereClause = !empty($staticWhereConditions) ? 'WHERE ' . implode(' AND ', $staticWhereConditions) : '';
        
        // Get static totals for filter chips
        $staticTotalsQuery = "
            SELECT 
                status, 
                owner as assigned_to,
                due_date,
                CASE 
                    WHEN due_date IS NOT NULL AND due_date < NOW() THEN 2
                    WHEN due_date IS NOT NULL AND due_date <= DATE_ADD(NOW(), INTERVAL 7 DAY) THEN 1
                    ELSE 0
                END as due,
                COUNT(*) as cnt
            FROM hesk_tickets 
            $staticWhereClause
            GROUP BY status, assigned_to, due
        ";
        
        $stmt = $db->prepare($staticTotalsQuery);
        $stmt->execute($staticParams);
        $staticTotalsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($staticTotalsData as $row) {
            // Total tickets found 
            $staticTotals['filtered']['all'] += $row['cnt']; 
            
            // Total Cancelled 
            if ($row['status'] == 6) { 
                $staticTotals['cancelled'] += $row['cnt']; 
            } 
            
            // Total Declined 
            if ($row['status'] == 8) { 
                $staticTotals['declined'] += $row['cnt']; 
            } 
            
            // Total by status 
            if (isset($staticTotals['filtered']['by_status'][$row['status']])) { 
                $staticTotals['filtered']['by_status'][$row['status']] += $row['cnt']; 
            } else { 
                $staticTotals['filtered']['by_status'][$row['status']] = $row['cnt']; 
            } 
            
            // Count all filtered open tickets 
            if (isset($row['status']) && $row['status'] != 3 && $row['status'] != 6 && $row['status'] != 8) { 
                $staticTotals['filtered']['open'] += $row['cnt']; 
            } 
            
            // Totals by assigned to 
            if (isset($row['assigned_to'])) {
                switch ($row['assigned_to']) { 
                    case 1: 
                        if($row['status'] != 3 && $row['status'] != 6 && $row['status'] != 8) 
                            $staticTotals['filtered']['assigned_to_me'] += $row['cnt']; 
                        break; 
                    case 2: 
                        if($row['status'] != 3 && $row['status'] != 6 && $row['status'] != 8) 
                            $staticTotals['filtered']['assigned_to_others'] += $row['cnt']; 
                        break; 
                    case 3: 
                        $staticTotals['filtered']['assigned_to_others'] += $row['cnt']; 
                        $staticTotals['filtered']['assigned_to_others_by_me'] += $row['cnt']; 
                        break; 
                    default: 
                        $staticTotals['filtered']['unassigned'] += $row['cnt']; 
                } 
            } else {
                $staticTotals['filtered']['unassigned'] += $row['cnt'];
            }
            
            // Total by due date; ignore for Resolved tickets 
            if ($row['status'] != 3) { 
                switch ($row['due']) { 
                    case 1: 
                        $staticTotals['filtered']['due_soon'] += $row['cnt']; 
                        break; 
                    case 2: 
                        $staticTotals['filtered']['overdue'] += $row['cnt']; 
                        break; 
                } 
            } 
        }
        
        // Validate sort parameters
        $allowedSortFields = ['dt', 'lastchange', 'priority', 'status', 'subject', 'trackid'];
        $allowedSortOrders = ['ASC', 'DESC'];
        
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'dt';
        }
        
        if (!in_array(strtoupper($sortOrder), $allowedSortOrders)) {
            $sortOrder = 'DESC';
        }
        
        // Get tickets
        $ticketsQuery = "
            SELECT 
                id, trackid, name, email, subject, message, status, priority, 
                category, dt, lastchange, articles, ip, language, owner, 
                time_worked, due_date, archive, locked, replies, staffreplies
            FROM hesk_tickets 
            $whereClause
            ORDER BY $sortBy $sortOrder 
            LIMIT $limit OFFSET $offset
        ";
        
        $stmt = $db->prepare($ticketsQuery);
        $stmt->execute($params);
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format tickets
        foreach ($tickets as &$ticket) {
            $ticket['id'] = (int)$ticket['id'];
            $ticket['status'] = (int)$ticket['status'];
            $ticket['priority'] = (int)$ticket['priority'];
            $ticket['category'] = (int)$ticket['category'];
            $ticket['replies'] = (int)$ticket['replies'];
            $ticket['staffreplies'] = (int)$ticket['staffreplies'];
            $ticket['locked'] = (bool)$ticket['locked'];
            $ticket['archive'] = (bool)$ticket['archive'];
            
            // Add human-readable status and priority
            $ticket['status_text'] = getStatusText($ticket['status']);
            $ticket['priority_text'] = getPriorityText($ticket['priority']);
            
            // Format dates
            $ticket['created_at'] = $ticket['dt'];
            $ticket['updated_at'] = $ticket['lastchange'];
            
            // Truncate message for list view
            if (strlen($ticket['message']) > 200) {
                $ticket['message_preview'] = substr($ticket['message'], 0, 200) . '...';
            } else {
                $ticket['message_preview'] = $ticket['message'];
            }
        }
        
        // Calculate pagination
        $totalPages = ceil($totalCount / $limit);
        $hasNext = $page < $totalPages;
        $hasPrev = $page > 1;
        
        Logger::info('Tickets list accessed', [
            'user_id' => $user['sub'],
            'page' => $page,
            'limit' => $limit,
            'total_count' => $totalCount
        ]);
        
        ApiResponse::success([
            'tickets' => $tickets,
            'totals' => $staticTotals,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_count' => $totalCount,
                'per_page' => $limit,
                'has_next' => $hasNext,
                'has_prev' => $hasPrev
            ],
            'filters' => [
                'status' => $status,
                'priority' => $priority,
                'search' => $search,
                'category' => $category,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'assigned_to' => $assignedTo,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder
            ]
        ], 'Tickets retrieved successfully');
        
    } catch (PDOException $e) {
        Logger::error('Database error in tickets list', ['error' => $e->getMessage()]);
        ApiResponse::serverError('Database error occurred');
    } catch (Exception $e) {
        Logger::error('Unexpected error in tickets list', ['error' => $e->getMessage()]);
        ApiResponse::serverError('An unexpected error occurred');
    }

    function getStatusText($status) {
        $statusMap = [
            0 => 'New',
            1 => 'Waiting Reply',
            2 => 'Replied',
            3 => 'Resolved',
            4 => 'In Progress',
            5 => 'On Hold',
            6 => 'Cancelled',
            8 => 'Declined'
        ];
        return $statusMap[$status] ?? 'Unknown';
    }

    function getPriorityText($priority) {
        $priorityMap = [
            0 => 'Critical',
            1 => 'High',
            2 => 'Medium',
            3 => 'Low'
        ];
        return $priorityMap[$priority] ?? 'Unknown';
    }
    ?>