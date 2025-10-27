<?php

namespace App;

class Router
{
    private $twig;
    private $ticketManager;

    public function __construct($twig, TicketManager $ticketManager)
    {
        $this->twig = $twig;
        $this->ticketManager = $ticketManager;

        // 🟢 Start session once when Router is initialized
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Main router entry point
     */
    public function route($uri, $method)
    {
        $uri = rtrim($uri, '/');
        if (empty($uri)) $uri = '/';

        // 🟣 Session + flash retrieval for templates
        $isAuthenticated = isset($_SESSION['user']);
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']); // remove after reading

        try {
            switch ($uri) {
                case '/':
                    echo $this->twig->render('landing.twig', [
                        'flash' => $flash,
                        'user' => $_SESSION['user'] ?? null
                    ]);
                    break;

                case '/login':
                    if ($method === 'POST') {
                        $this->handleLogin();
                    } else {
                        if ($isAuthenticated) {
                            header('Location: /dashboard');
                            exit;
                        }
                        echo $this->twig->render('auth/login.twig', ['flash' => $flash]);
                    }
                    break;

                case '/signup':
                    if ($method === 'POST') {
                        $this->handleSignup();
                    } else {
                        if ($isAuthenticated) {
                            header('Location: /dashboard');
                            exit;
                        }
                        echo $this->twig->render('auth/signup.twig', ['flash' => $flash]);
                    }
                    break;

                // 🟡 New endpoint: allows frontend JS to check session state
                case '/session':
                    $this->sendJson([
                        'authenticated' => $isAuthenticated,
                        'user' => $_SESSION['user'] ?? null
                    ]);
                    break;

                case '/logout':
                    $this->handleLogout();
                    break;

                case '/dashboard':
                    $this->requireAuth();
                    $tickets = $this->ticketManager->getAll();
                    $stats = $this->ticketManager->getStats();
                    echo $this->twig->render('dashboard.twig', [
                        'tickets' => $tickets,
                        'stats' => $stats,
                        'user' => $_SESSION['user'],
                        'flash' => $flash
                    ]);
                    break;

                case '/tickets':
                    $this->requireAuth();
                    if ($method === 'POST') {
                        $this->handleTicketCreate();
                    } else {
                        $tickets = $this->ticketManager->getAll();
                        echo $this->twig->render('tickets/index.twig', [
                            'tickets' => $tickets,
                            'user' => $_SESSION['user'],
                            'flash' => $flash
                        ]);
                    }
                    break;

                // 🔵 Edit ticket route
                case (preg_match('/^\\/tickets\\/(\\d+)\\/edit$/', $uri, $matches) ? true : false):
                    $this->requireAuth();
                    $ticketId = $matches[1];
                    if ($method === 'POST') {
                        $this->handleTicketUpdate($ticketId);
                    }
                    break;

                // 🔵 Delete ticket route
                case (preg_match('/^\\/tickets\\/(\\d+)\\/delete$/', $uri, $matches) ? true : false):
                    $this->requireAuth();
                    $ticketId = $matches[1];
                    $this->handleTicketDelete($ticketId);
                    break;

                default:
                    http_response_code(404);
                    echo $this->twig->render('404.twig');
                    break;
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo "Error: " . $e->getMessage();
        }
    }

    /**
     * 🧩 Auth requirement for private pages
     */
    private function requireAuth()
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Please log in to continue.'];
            header('Location: /login');
            exit;
        }
    }

    /**
     * 🧩 Send JSON response utility
     */
    private function sendJson($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Detects if request expects JSON (AJAX/fetch)
     */
    private function expectsJson()
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $xhr = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        return $xhr || stripos($accept, 'application/json') !== false || isset($_GET['json']);
    }

    /**
     * 🟢 Handle Login (server + localStorage sync)
     */
    private function handleLogin()
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (!empty($email) && !empty($password) && strlen($password) >= 6) {
            $_SESSION['user'] = [
                'name' => 'Admin User',
                'email' => $email
            ];
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Login successful!'];

            if ($this->expectsJson()) {
                // Return data for JS to save in localStorage as "ticketapp_session"
                $this->sendJson([
                    'status' => 'success',
                    'user' => $_SESSION['user']
                ]);
            }

            header('Location: /dashboard');
            exit;
        } else {
            if ($this->expectsJson()) {
                $this->sendJson(['status' => 'error', 'message' => 'Invalid credentials']);
            }

            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid credentials'];
            header('Location: /login');
            exit;
        }
    }

    /**
     * 🟢 Handle Signup
     */
    private function handleSignup()
    {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (!empty($name) && !empty($email) && !empty($password) && strlen($password) >= 6) {
            $_SESSION['user'] = [
                'name' => $name,
                'email' => $email
            ];
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Account created successfully!'];

            if ($this->expectsJson()) {
                $this->sendJson(['status' => 'success', 'user' => $_SESSION['user']]);
            }

            header('Location: /dashboard');
            exit;
        } else {
            if ($this->expectsJson()) {
                $this->sendJson(['status' => 'error', 'message' => 'Invalid signup data']);
            }

            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid signup data'];
            header('Location: /signup');
            exit;
        }
    }

    /**
     * 🟠 Handle Logout — clears PHP session and (optionally) localStorage
     */
    private function handleLogout()
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'], $params['secure'], $params['httponly']
            );
        }
        session_destroy();

        if ($this->expectsJson()) {
            $this->sendJson(['status' => 'success']);
        }

        header('Location: /');
        exit;
    }

    // 🎫 Ticket Management Routes
    private function handleTicketCreate()
    {
        $data = [
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'status' => $_POST['status'] ?? 'open',
            'priority' => $_POST['priority'] ?? 'medium',
            'assignee' => $_POST['assignee'] ?? '',
        ];

        $this->ticketManager->create($data);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Ticket created successfully!'];
        header('Location: /tickets');
        exit;
    }

    private function handleTicketUpdate($id)
    {
        $data = [
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'status' => $_POST['status'] ?? 'open',
            'priority' => $_POST['priority'] ?? 'medium',
            'assignee' => $_POST['assignee'] ?? '',
        ];

        $this->ticketManager->update($id, $data);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Ticket updated successfully!'];
        header('Location: /tickets');
        exit;
    }

    private function handleTicketDelete($id)
    {
        $this->ticketManager->delete($id);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Ticket deleted successfully!'];
        header('Location: /tickets');
        exit;
    }
}
