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

                switch (strtolower($user['department'])) {
                    case 'hr':
                        header("Location: ../../dashboard/hr_dashboard.php");
                        break;
                    case 'accounting':
                        header("Location: backend/accounting/accounting_dashboard.php");
                        break;
                    case 'admin':
                        header("Location: ../../dashboard/admin_dashboard.php");
                        break;
                    case 'corporate':
                        header("Location: ../../dashboard/corporate_dashboard.php");
                        break;
                    case 'it':
                        header("Location: ../../dashboard/it_dashboard.php");
                        break;
                    case 'litigation':
                        header("Location: ../../dashboard/litigation_dashboard.php");
                        break;
                    case 'marketing':
                        header("Location: ../../dashboard/marketing_dashboard.php");
                        break;
                    case 'ops':
                        header("Location: ../../dashboard/ops_dashboard.php");
                        break;
                    default:
                        header("Location: ../../dashboard/default_dashboard.php");
                }
                exit();
            }
        }

        return false; // invalid login
    }
}
