<?php
session_start();

require_once __DIR__ . '/vendor/autoload.php';

use App\Router;
use App\TicketManager;

// Initialize Twig
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
$twig = new \Twig\Environment($loader, [
    'cache' => false, // Disable cache for development
    'debug' => true,
]);

// Initialize services
$ticketManager = new TicketManager();
$router = new Router($twig, $ticketManager);

// Get request URI and method
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Route the request
$router->route($uri, $method);
