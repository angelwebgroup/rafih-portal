<?php
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../db.php';

// Handle CORS
ApiResponse::cors();

RequestHelper::requireMethod('DELETE');

try {
    // Require authentication
    $user = RequestHelper::requireAuth();
    
    // Only admin users can delete users
    if (!$user['isadmin']) {
        ApiResponse::forbidden('Admin access required');
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Get user ID from URL
    $userId = $_GET['id'] ?? null;
    if (!$userId || !is_numeric($userId)) {
        ApiResponse::badRequest('Valid user ID is required');
    }
    
    // Check if user exists
    $stmt = $db->prepare("SELECT id, name, email, user FROM hesk_users WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existingUser) {
        ApiResponse::notFound('User not found');
    }
    
    // Prevent self-deletion
    if ($userId == $user['id']) {
        ApiResponse::badRequest('Cannot delete your own account');
    }
    
    // Check if user has any tickets assigned
    $stmt = $db->prepare("SELECT COUNT(*) as ticket_count FROM hesk_tickets WHERE owner = ?");
    $stmt->execute([$userId]);
    $ticketCount = $stmt->fetch(PDO::FETCH_ASSOC)['ticket_count'];
    
    if ($ticketCount > 0) {
        ApiResponse::badRequest('Cannot delete user with assigned tickets. Please reassign tickets first.');
    }
    
    // Begin transaction
    $db->beginTransaction();
    
    try {
        // Delete user categories
        $stmt = $db->prepare("DELETE FROM hesk_users_categories WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        // Delete user replies
        $stmt = $db->prepare("DELETE FROM hesk_replies WHERE staffid = ?");
        $stmt->execute([$userId]);
        
        // Delete user notes
        $stmt = $db->prepare("DELETE FROM hesk_notes WHERE who = ?");
        $stmt->execute([$userId]);
        
        // Delete user
        $stmt = $db->prepare("DELETE FROM hesk_users WHERE id = ?");
        $stmt->execute([$userId]);
        
        // Commit transaction
        $db->commit();
        
        Logger::info('User deleted', [
            'user_id' => $user['id'],
            'deleted_user_id' => $userId,
            'deleted_user_email' => $existingUser['email']
        ]);
        
        ApiResponse::success([
            'deleted_user' => $existingUser
        ], 'User deleted successfully');
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $db->rollback();
        throw $e;
    }
    
} catch (PDOException $e) {
    Logger::error('Database error deleting user', ['error' => $e->getMessage()]);
    ApiResponse::error('Database error occurred');
} catch (Exception $e) {
    Logger::error('Unexpected error deleting user', ['error' => $e->getMessage()]);
    ApiResponse::error('An unexpected error occurred');
}
?>