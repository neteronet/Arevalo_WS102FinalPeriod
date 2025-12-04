<?php
/**
 * User Role Query Helper
 * Contains functions to identify and manage user roles in the database
 */

include 'connection.php';

/**
 * Get user role by user ID
 * 
 * @param int $user_id - The user ID
 * @param mysqli $conn - Database connection
 * @return string|false - Returns the role ('student' or 'supervisor') or false if not found
 */
function getUserRole($user_id, $conn) {
    $sql = "SELECT role FROM users WHERE id = ? AND status = 'active'";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['role'];
    }
    
    $stmt->close();
    return false;
}

/**
 * Get user role by username/email and password
 * 
 * @param string $username_or_email - Username or email
 * @param string $password - User password
 * @param mysqli $conn - Database connection
 * @return array|false - Returns array with id and role, or false if authentication fails
 */
function authenticateUserAndGetRole($username_or_email, $password, $conn) {
    $sql = "SELECT id, role, password FROM users WHERE (username = ? OR email = ?) AND status = 'active'";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param("ss", $username_or_email, $username_or_email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $row['password'])) {
            $stmt->close();
            return array(
                'id' => $row['id'],
                'role' => $row['role']
            );
        }
    }
    
    $stmt->close();
    return false;
}

/**
 * Get all users by role
 * 
 * @param string $role - The role to filter by ('student' or 'supervisor')
 * @param mysqli $conn - Database connection
 * @return array - Returns array of users with that role
 */
function getUsersByRole($role, $conn) {
    $sql = "SELECT id, username, email, role, date_created FROM users WHERE role = ? AND status = 'active' ORDER BY date_created DESC";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return array();
    }
    
    $stmt->bind_param("s", $role);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $users = array();
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    $stmt->close();
    return $users;
}

/**
 * Count users by role
 * 
 * @param string $role - The role to count ('student' or 'supervisor')
 * @param mysqli $conn - Database connection
 * @return int - Number of users with that role
 */
function countUsersByRole($role, $conn) {
    $sql = "SELECT COUNT(*) as count FROM users WHERE role = ? AND status = 'active'";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return 0;
    }
    
    $stmt->bind_param("s", $role);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $stmt->close();
    return $row['count'];
}

/**
 * Check if user is supervisor
 * 
 * @param int $user_id - The user ID
 * @param mysqli $conn - Database connection
 * @return bool - Returns true if user is supervisor
 */
function isSupervisor($user_id, $conn) {
    $role = getUserRole($user_id, $conn);
    return $role === 'supervisor';
}

/**
 * Check if user is student
 * 
 * @param int $user_id - The user ID
 * @param mysqli $conn - Database connection
 * @return bool - Returns true if user is student
 */
function isStudent($user_id, $conn) {
    $role = getUserRole($user_id, $conn);
    return $role === 'student';
}

?>
