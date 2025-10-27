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
    }

    public function route($uri, $method)
    {
        // Remove base path if needed
        $uri = rtrim($uri, '/');
        if (empty($uri)) {
            $uri = '/';
        }

        // Check authentication
        $isAuthenticated = isset($_SESSION['user']);
        
        // Get flash message and clear it
        $flash = isset($_SESSION['flash']) ? $_SESSION['flash'] : null;
        if ($flash) {
            unset($_SESSION['flash']);
        }

        try {
            switch ($uri) {
                case '/':
                    echo $this->twig->render('landing.twig', ['flash' => $flash]);
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

                case '/logout':
                    session_destroy();
                    header('Location: /');
                    exit;

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

                case (preg_match('/^\/tickets\/(\d+)\/edit$/', $uri, $matches) ? true : false):
                    $this->requireAuth();
                    $ticketId = $matches[1];
                    if ($method === 'POST') {
                        $this->handleTicketUpdate($ticketId);
                    }
                    break;

                case (preg_match('/^\/tickets\/(\d+)\/delete$/', $uri, $matches) ? true : false):
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

    private function requireAuth()
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Please log in to access this page.'];
            header('Location: /login');
            exit;
        }
    }

    private function handleLogin()
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Simple validation (in production, check against database)
        if (!empty($email) && !empty($password) && strlen($password) >= 6) {
            $_SESSION['user'] = [
                'name' => 'Admin User',
                'email' => $email
            ];
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Successfully logged in!'];
            header('Location: /dashboard');
            exit;
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid credentials'];
            header('Location: /login');
            exit;
        }
    }

    private function handleSignup()
    {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Simple validation
        if (!empty($name) && !empty($email) && !empty($password) && strlen($password) >= 6) {
            $_SESSION['user'] = [
                'name' => $name,
                'email' => $email
            ];
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Account created successfully!'];
            header('Location: /dashboard');
            exit;
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Invalid data'];
            header('Location: /signup');
            exit;
        }
    }

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