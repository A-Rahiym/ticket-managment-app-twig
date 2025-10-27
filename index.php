<?php
// Set session save path to a persistent directory
$sessionPath = __DIR__ . '/data/sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0755, true);
}
session_save_path($sessionPath);

// Initialize Twig
require_once __DIR__ . '/vendor/autoload.php';

use App\Router;
use App\TicketManager;

// Ensure data directory exists
$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
$twig = new \Twig\Environment($loader, [
    'cache' => __DIR__ . '/cache', // Enable cache for production
    'debug' => false, // Disable debug in production
]);

// Initialize services
$ticketManager = new TicketManager();
$router = new Router($twig, $ticketManager);

// Get request URI and method
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Route the request
$router->route($uri, $method);