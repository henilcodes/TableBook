<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Services\AuthService;

class CustomerAuthController extends Controller
{
    private $authService;
    
    public function __construct()
    {
        $this->authService = new AuthService();
    }
    
    public function showLogin()
    {
        if (!empty($_SESSION['customer_id'])) {
            $this->redirect('/account');
            return;
        }

        $redirect = trim((string)($_GET['redirect'] ?? ''));
        $basePath = defined('BASE_URL') ? BASE_URL : '';
        if ($redirect !== '' && strpos($redirect, '/') === 0) {
            $redirectPath = parse_url($redirect, PHP_URL_PATH) ?: '/';
            $redirectQuery = parse_url($redirect, PHP_URL_QUERY);
            if ($basePath !== '' && strpos($redirectPath, $basePath) === 0) {
                $redirectPath = substr($redirectPath, strlen($basePath));
                if ($redirectPath === '') {
                    $redirectPath = '/';
                }
            }
            $normalized = $redirectPath . ($redirectQuery ? ('?' . $redirectQuery) : '');
            $_SESSION['intended_url'] = $normalized;
        }
        
        $this->view('customer/login', [
            'csrf_token' => $this->generateCsrfToken(),
            'redirect' => $_SESSION['intended_url'] ?? null
        ]);
    }
    
    public function login()
    {
        $this->validateCsrf();
        
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $rememberMe = isset($_POST['remember_me']);
        $redirect = trim((string)($_POST['redirect'] ?? ''));
        $basePath = defined('BASE_URL') ? BASE_URL : '';
        if ($redirect !== '' && strpos($redirect, '/') === 0) {
            $redirectPath = parse_url($redirect, PHP_URL_PATH) ?: '/';
            $redirectQuery = parse_url($redirect, PHP_URL_QUERY);
            if ($basePath !== '' && strpos($redirectPath, $basePath) === 0) {
                $redirectPath = substr($redirectPath, strlen($basePath));
                if ($redirectPath === '') {
                    $redirectPath = '/';
                }
            }
            $_SESSION['intended_url'] = $redirectPath . ($redirectQuery ? ('?' . $redirectQuery) : '');
        }
        
        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Please enter email and password';
            $this->redirect('/login');
            return;
        }
        
        $customer = $this->authService->loginCustomer($email, $password, $rememberMe, session_id());
        
        if ($customer) {
            $_SESSION['success'] = 'Welcome back, ' . $customer['name'] . '!';
            $redirectTo = $_SESSION['intended_url'] ?? '/account';
            unset($_SESSION['intended_url']);
            $this->redirect($redirectTo);
        } else {
            $_SESSION['error'] = 'Invalid email or password';
            $this->redirect('/login');
        }
    }
    
    public function showRegister()
    {
        if (!empty($_SESSION['customer_id'])) {
            $this->redirect('/account');
            return;
        }
        
        $this->view('customer/register', [
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    public function register()
    {
        $this->validateCsrf();
        
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($name) || empty($email) || empty($password)) {
            $_SESSION['error'] = 'Please fill in all required fields';
            $this->redirect('/register');
            return;
        }
        
        if ($password !== $confirmPassword) {
            $_SESSION['error'] = 'Passwords do not match';
            $this->redirect('/register');
            return;
        }
        
        try {
            $customer = $this->authService->registerCustomer([
                'name' => $name,
                'email' => $email,
                'phone' => $phone ?: null,
                'password' => $password
            ]);
            
            // Auto-login after registration
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_regenerate_id(true);
            }
            $_SESSION['customer_id'] = $customer['id'];
            $_SESSION['customer_name'] = $customer['name'];
            $_SESSION['customer_email'] = $customer['email'];
            
            $_SESSION['success'] = 'Account created successfully! Welcome to TableTap!';
            $this->redirect('/account');
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $this->redirect('/register');
        }
    }
    
    public function logout()
    {
        $this->validateCsrf();
        $this->authService->logout();
        $_SESSION['success'] = 'You have been logged out successfully';
        $this->redirect('/');
    }
}
