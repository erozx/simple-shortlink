<?php
include('config.php');

// Basic Authentication credentials
define('USERNAME', $_ADMIN_USER);
define('PASSWORD', $_ADMIN_PASSWORD);

// Check if the user is authenticated
function authenticate() {
    if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] !== USERNAME || $_SERVER['PHP_AUTH_PW'] !== PASSWORD) {
        header('HTTP/1.1 401 Unauthorized');
        header('WWW-Authenticate: Basic realm="Admin Area"');
        echo 'Authentication required.';
        exit();
    }
}

// Call authentication function
authenticate();

// SQLite database file path
$db_file = __DIR__ . '/db/links.db';

// Create SQLite database connection
$conn = new PDO("sqlite:" . $db_file);

// Set the PDO error mode to exception
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Handle delete action
if (isset($_GET['delete'])) {
    $short_name = trim($_GET['delete']);

    // Prepare and execute delete statement
    $stmt = $conn->prepare("DELETE FROM urls WHERE short_name = ?");
    $stmt->execute([$short_name]);

    header("Location: ".$_SERVER["PHP_SELF"]);
    exit();
}

// Fetch all records
$stmt = $conn->query("SELECT id, original_url, short_name, created_at FROM urls");
$urls = $stmt->fetchAll(PDO::FETCH_ASSOC);

function renderAdminPage($urls) {
    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage - Short URL Service</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-6xl mx-auto bg-white shadow-lg rounded-lg p-6">
        <h1 class="text-2xl font-bold text-center mb-4">Manage Short Links</h1>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Original URL</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Short Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
HTML
    . implode('', array_map(function($url) {
        $main = $_SERVER["PHP_SELF"];
        return <<<HTML
                <tr>
                    <td class="px-4 py-2 text-sm font-medium text-gray-900">{$url['id']}</td>
                    <td class="px-4 py-2 text-sm text-gray-500">{$url['original_url']}</td>
                    <td class="px-4 py-2 text-sm text-gray-500">{$url['short_name']}</td>
                    <td class="px-4 py-2 text-sm text-gray-500">{$url['created_at']}</td>
                    <td class="px-4 py-2 text-sm font-medium">
                        <a href="{$main}?delete={$url['short_name']}" class="text-red-600 hover:text-red-900">Delete</a>
                    </td>
                </tr>
HTML;
    }, $urls))
    . <<<HTML
            </tbody>
        </table>
    </div>
</body>
</html>
HTML;
}

// Render the admin page
echo renderAdminPage($urls);
