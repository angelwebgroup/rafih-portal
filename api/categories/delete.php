<?php
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../db.php';

// Handle CORS
ApiResponse::cors();

RequestHelper::requireMethod('DELETE');
RequestHelper::checkRateLimit('delete_category', 10);

try {
    // Require authentication and admin privileges
    $user = RequestHelper::requireAuth();
    
    if (!$user['isadmin']) {
        ApiResponse::forbidden('Only administrators can delete categories');
    }
    
    $data = RequestHelper::getJsonBody();
    
    // Validate category ID
    $categoryId = (int)($data['id'] ?? 0);
    if ($categoryId <= 0) {
        ApiResponse::error('Valid category ID is required', 400);
    }
    
    $db = Database::getInstance()->getConnection();
    
    // Check if category exists
    $stmt = $db->prepare("SELECT * FROM hesk_categories WHERE id = ? LIMIT 1");
    $stmt->execute([$categoryId]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$category) {
        ApiResponse::notFound('Category not found');
    }
    
    // Check if category has tickets
    $stmt = $db->prepare("SELECT COUNT(*) FROM hesk_tickets WHERE category = ?");
    $stmt->execute([$categoryId]);
    $ticketCount = (int)$stmt->fetchColumn();
    
    if ($ticketCount > 0) {
        // Check if force delete is requested
        $forceDelete = (bool)($data['force'] ?? false);
        
        if (!$forceDelete) {
            ApiResponse::error(
                "Cannot delete category with {$ticketCount} tickets. Use 'force' parameter to reassign tickets to default category.", 
                409
            );
        }
        
        // Get default category (lowest cat_order)
        $stmt = $db->prepare("SELECT id FROM hesk_categories WHERE id != ? ORDER BY cat_order ASC LIMIT 1");
        $stmt->execute([$categoryId]);
        $defaultCategory = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$defaultCategory) {
            ApiResponse::error('Cannot delete the only remaining category', 409);
        }
        
        // Begin transaction for safe deletion with ticket reassignment
        $db->beginTransaction();
        
        try {
            // Reassign all tickets to default category
            $stmt = $db->prepare("UPDATE hesk_tickets SET category = ? WHERE category = ?");
            $stmt->execute([$defaultCategory['id'], $categoryId]);
            
            // Delete the category
            $stmt = $db->prepare("DELETE FROM hesk_categories WHERE id = ?");
            $stmt->execute([$categoryId]);
            
            // Commit transaction
            $db->commit();
            
            Logger::info('Category deleted with ticket reassignment', [
                'user_id' => $user['sub'],
                'category_id' => $categoryId,
                'category_name' => $category['name'],
                'tickets_reassigned' => $ticketCount,
                'default_category_id' => $defaultCategory['id']
            ]);
            
            ApiResponse::success([
                'deleted_category_id' => $categoryId,
                'tickets_reassigned' => $ticketCount,
                'default_category_id' => $defaultCategory['id']
            ], "Category deleted successfully. {$ticketCount} tickets reassigned to default category.");
            
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
        
    } else {
        // No tickets, safe to delete directly
        
        // Check if this is the last category
        $stmt = $db->prepare("SELECT COUNT(*) FROM hesk_categories");
        $stmt->execute();
        $totalCategories = (int)$stmt->fetchColumn();
        
        if ($totalCategories <= 1) {
            ApiResponse::error('Cannot delete the only remaining category', 409);
        }
        
        // Delete the category
        $stmt = $db->prepare("DELETE FROM hesk_categories WHERE id = ?");
        $stmt->execute([$categoryId]);
        
        Logger::info('Category deleted', [
            'user_id' => $user['sub'],
            
            'category_id' => $categoryId,
            'category_name' => $category['name']
        ]);
        
        ApiResponse::success([
            'deleted_category_id' => $categoryId
        ], 'Category deleted successfully');
    }   
    
} catch (PDOException $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollback();
    }
    Logger::error('Database error in category deletion', ['error' => $e->getMessage()]);
    ApiResponse::serverError('Database error occurred');
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollback();
    }
    Logger::error('Unexpected error in category deletion', ['error' => $e->getMessage()]);
    ApiResponse::serverError('An unexpected error occurred');
}
?>