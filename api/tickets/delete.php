<?php
require_once '../config/database.php';
require_once '../config/auth.php';
require_once '../utils/ApiResponse.php';
require_once '../utils/Logger.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../db.php';

// Handle CORS
ApiResponse::cors();

header('Content-Type: application/json');

// Only allow DELETE method
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    ApiResponse::methodNotAllowed('Only DELETE method is allowed');
}

try {
    // Get and validate JWT token
    $user = Auth::validateToken();
    if (!$user) {
        ApiResponse::unauthorized('Invalid or expired token');
    }

    // Get request body
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id'])) {
        ApiResponse::badRequest('Ticket ID is required');
    }

    $ticketId = (int)$input['id'];
    
    if ($ticketId <= 0) {
        ApiResponse::badRequest('Invalid ticket ID');
    }

    // Connect to database
    $db = Database::getConnection();
    
    // First, check if ticket exists and user has permission to delete it
    $stmt = $db->prepare("
        SELECT id, trackid, name, email, subject, status 
        FROM hesk_tickets 
        WHERE id = ?
    ");
    $stmt->execute([$ticketId]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ticket) {
        ApiResponse::notFound('Ticket not found');
    }

    // Check if user has permission to delete this ticket
    // For now, allow users to delete their own tickets (by email) or if they're admin
    $canDelete = false;
    
    if ($user['isadmin']) {
        $canDelete = true;
    } else {
        // Check if this ticket belongs to the user (by email)
        if (strtolower($ticket['email']) === strtolower($user['email'])) {
            $canDelete = true;
        }
    }
    
    if (!$canDelete) {
        ApiResponse::forbidden('You do not have permission to delete this ticket');
    }

    // Check if ticket is already closed/resolved - prevent deletion of resolved tickets
    if ($ticket['status'] == 3) { // Status 3 = Resolved
        ApiResponse::badRequest('Cannot delete resolved tickets');
    }

    // Begin transaction
    $db->beginTransaction();
    
    try {
        // Delete ticket replies first (foreign key constraint)
        $stmt = $db->prepare("DELETE FROM hesk_replies WHERE replyto = ?");
        $stmt->execute([$ticketId]);
        
        // Delete the ticket
        $stmt = $db->prepare("DELETE FROM hesk_tickets WHERE id = ?");
        $stmt->execute([$ticketId]);
        
        // Check if ticket was actually deleted
        if ($stmt->rowCount() === 0) {
            throw new Exception('Failed to delete ticket');
        }
        
        // Commit transaction
        $db->commit();
        
        Logger::info('Ticket deleted', [
            'ticket_id' => $ticketId,
            'trackid' => $ticket['trackid'],
            'deleted_by' => $user['email'],
            'user_id' => $user['sub']
        ]);
        
        ApiResponse::success([
            'ticket_id' => $ticketId,
            'trackid' => $ticket['trackid']
        ], 'Ticket deleted successfully');
        
    } catch (Exception $e) {
        // Rollback transaction
        $db->rollback();
        throw $e;
    }
    
} catch (PDOException $e) {
    Logger::error('Database error deleting ticket', [
        'error' => $e->getMessage(),
        'ticket_id' => $ticketId ?? null
    ]);
    ApiResponse::serverError('Database error occurred');
} catch (Exception $e) {
    Logger::error('Error deleting ticket', [
        'error' => $e->getMessage(),
        'ticket_id' => $ticketId ?? null
    ]);
    ApiResponse::serverError($e->getMessage());
}
?>