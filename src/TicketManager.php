<?php

namespace App;

class TicketManager
{
    private $storageFile;

    public function __construct()
    {
        $this->storageFile = __DIR__ . '/../data/tickets.json';
        $this->ensureDataDirectory();
        $this->initializeDefaultTickets();
    }

    private function ensureDataDirectory()
    {
        $dataDir = dirname($this->storageFile);
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }
    }

    private function initializeDefaultTickets()
    {
        if (!file_exists($this->storageFile)) {
            $defaultTickets = [
                [
                    'id' => '1',
                    'title' => 'Fix login authentication bug',
                    'status' => 'open',
                    'description' => 'Users are experiencing issues logging in with valid credentials. Need to investigate the authentication flow and session management.',
                    'priority' => 'high',
                    'assignee' => 'Sarah Johnson',
                    'createdAt' => '2025-01-15T10:30:00Z',
                ],
                [
                    'id' => '2',
                    'title' => 'Update dashboard UI components',
                    'status' => 'in_progress',
                    'description' => 'Modernize the dashboard interface with the new design system. Update colors, spacing, and component styles.',
                    'priority' => 'medium',
                    'assignee' => 'Michael Chen',
                    'createdAt' => '2025-01-14T14:20:00Z',
                ],
                [
                    'id' => '3',
                    'title' => 'Add export functionality for reports',
                    'status' => 'closed',
                    'description' => 'Allow users to export ticket data to CSV and PDF formats. Include filters for date range and status.',
                    'priority' => 'low',
                    'assignee' => 'Emma Williams',
                    'createdAt' => '2025-01-10T09:15:00Z',
                ],
                [
                    'id' => '4',
                    'title' => 'Implement real-time notifications',
                    'status' => 'in_progress',
                    'description' => 'Add WebSocket support for real-time ticket updates and notifications. Show toast messages for new tickets.',
                    'priority' => 'high',
                    'assignee' => 'David Martinez',
                    'createdAt' => '2025-01-16T11:45:00Z',
                ],
                [
                    'id' => '5',
                    'title' => 'Optimize database queries',
                    'status' => 'open',
                    'description' => 'Improve performance of ticket listing and search queries. Add proper indexing and caching.',
                    'priority' => 'medium',
                    'assignee' => 'Lisa Anderson',
                    'createdAt' => '2025-01-17T08:00:00Z',
                ],
            ];
            file_put_contents($this->storageFile, json_encode($defaultTickets, JSON_PRETTY_PRINT));
        }
    }

    public function getAll()
    {
        if (!file_exists($this->storageFile)) {
            return [];
        }
        $content = file_get_contents($this->storageFile);
        return json_decode($content, true) ?: [];
    }

    public function getById($id)
    {
        $tickets = $this->getAll();
        foreach ($tickets as $ticket) {
            if ($ticket['id'] === $id) {
                return $ticket;
            }
        }
        return null;
    }

    public function create($data)
    {
        $tickets = $this->getAll();
        $newTicket = [
            'id' => (string)(count($tickets) + 1),
            'title' => $data['title'],
            'description' => $data['description'],
            'status' => $data['status'],
            'priority' => $data['priority'],
            'assignee' => $data['assignee'],
            'createdAt' => date('c'),
        ];
        array_unshift($tickets, $newTicket);
        file_put_contents($this->storageFile, json_encode($tickets, JSON_PRETTY_PRINT));
        return $newTicket;
    }

    public function update($id, $data)
    {
        $tickets = $this->getAll();
        foreach ($tickets as $key => $ticket) {
            if ($ticket['id'] === $id) {
                $tickets[$key] = array_merge($ticket, [
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'status' => $data['status'],
                    'priority' => $data['priority'],
                    'assignee' => $data['assignee'],
                ]);
                file_put_contents($this->storageFile, json_encode($tickets, JSON_PRETTY_PRINT));
                return $tickets[$key];
            }
        }
        return null;
    }

    public function delete($id)
    {
        $tickets = $this->getAll();
        $tickets = array_filter($tickets, function ($ticket) use ($id) {
            return $ticket['id'] !== $id;
        });
        file_put_contents($this->storageFile, json_encode(array_values($tickets), JSON_PRETTY_PRINT));
    }

    public function getStats()
    {
        $tickets = $this->getAll();
        return [
            'total' => count($tickets),
            'open' => count(array_filter($tickets, fn($t) => $t['status'] === 'open')),
            'in_progress' => count(array_filter($tickets, fn($t) => $t['status'] === 'in_progress')),
            'closed' => count(array_filter($tickets, fn($t) => $t['status'] === 'closed')),
        ];
    }
}
