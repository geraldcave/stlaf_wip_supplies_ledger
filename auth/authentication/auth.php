<?php
include __DIR__ . '/../../sql/config.php';

class User
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function login($usernameOrEmail, $password)
    {
        $usernameOrEmail = trim($usernameOrEmail);
        $password = trim($password);

        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            $dbPassword = $user['password'];
            if (password_verify($password, $dbPassword) || $password === $dbPassword) {
                if (!password_verify($password, $dbPassword)) {
                
                    $hashed = password_hash($dbPassword, PASSWORD_DEFAULT);
                    $updateStmt = $this->conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                    $updateStmt->bind_param("si", $hashed, $user['user_id']);
                    $updateStmt->execute();
                }

                session_start();
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['department'] = $user['department'];

            
                $this->redirectToDashboard($user['department']);
                exit();
            }
        }

        return false; 
    }

    public function loginAsEmployee()
    {
        session_start();
        $_SESSION['show_guest_alert'] = true;
        $_SESSION['user_id'] = 1;  
        $_SESSION['username'] = 'employee'; 
        $_SESSION['department'] = 'employee'; 

        header("Location: backend/guest/guest.php");
        exit();
    }

    private function redirectToDashboard($department)
    {
        switch (strtolower($department)) {
            case 'hr':
                header("Location: backend/hr/hr_dashboard.php");
                break;
            case 'accounting':
                header("Location: backend/accounting/accounting_dashboard.php");
                break;
            case 'admin':
                header("Location: backend/admin/admin_dashboard.php");
                break;
            case 'corporate':
                header("Location: backend/corporate/corporate_dashboard.php");
                break;
            case 'it':
                header("Location: backend/it/it_dashboard.php");
                break;
            case 'litigation':
                header("Location: backend/litigation/litigation_dashboard.php");
                break;
            case 'marketing':
                header("Location: backend/marketing/marketing_dashboard.php");
                break;
            case 'ops':
                header("Location: backend/ops/ops_dashboard.php");
                break;
            default:
                header("Location: index.php");
        }
        exit();
    }
}
