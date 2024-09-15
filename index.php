<?php
include('config.php');

$server_protocol = $_SERVER['SERVER_PROTOCOL'];
preg_match("/^([A-Z]+)\/\d\.\d$/", $server_protocol, $matches);
$scheme = strtolower($matches[1]);
$rootUrl = "$scheme://{$_SERVER["HTTP_HOST"]}/";
$access_code = $_ACCESS_CODE;

// SQLite database file path
$db_file = __DIR__ . '/db/links.db';

// Create SQLite database connection
$conn = new PDO("sqlite:" . $db_file);

// Set the PDO error mode to exception
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create the `urls` table if it doesn't exist
$conn->exec("CREATE TABLE IF NOT EXISTS urls (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    original_url TEXT NOT NULL,
    short_name VARCHAR(10) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Get the requested URI and remove the leading slash
$requestUri = trim($_SERVER['REQUEST_URI'], '/');

// Handle root request for shortening URL
if ($requestUri === '') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$shortnames = $_POST['custom_short_name'] ?? '';
		$original_url = trim($_POST['original_url']);
        $custom_short_name = trim($_POST['custom_short_name']);
        $err = null;

		if($access_code !== trim($_POST['access_code'])){
			echo renderHomePage(null,"Invalid access code, shorting url not allowed.");
            exit();
		}

		// Regex to allow only alphanumeric characters, dashes, and underscores
		if (!preg_match('/^[a-zA-Z0-9-_]+$/', $shortnames)) {
			echo renderHomePage(null,"Invalid shortname. Only alphanumeric characters, dashes, and underscores are allowed.");
            exit();
		}



        // Validate the URL
        if (!filter_var($original_url, FILTER_VALIDATE_URL)) {
            echo renderHomePage(null,'Invalid URL. Please enter a valid URL.');
            exit();
        }

        // Sanitize custom shortname to prevent XSS
        $custom_short_name = htmlspecialchars($custom_short_name, ENT_QUOTES, 'UTF-8');

        // If custom shortname is provided, use it, otherwise generate a random one
        if (!empty($custom_short_name)) {
            // Check if the custom shortname already exists
            $stmt = $conn->prepare("SELECT COUNT(*) FROM urls WHERE short_name = ?");
            $stmt->execute([$custom_short_name]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                $err = 'This shortname is already taken. Please choose another one.';
            } else {
                $short_name = $custom_short_name;
            }
        } else {
            // Generate a 6 character short name
            $short_name = substr(md5(uniqid()), 0, 6);
        }
        $short_url = null;
        if (isset($short_name)) {
            // Insert into the database
            $stmt = $conn->prepare("INSERT INTO urls (original_url, short_name) VALUES (?, ?)");
            $stmt->execute([$original_url, $short_name]);

            $short_url = $rootUrl . $short_name;
        }
    }

    echo renderHomePage($short_url, $err);
} else {
    // Handle redirection based on short name
    $short_name = $requestUri;

    // Fetch the original URL from the database
    $stmt = $conn->prepare("SELECT original_url FROM urls WHERE short_name = ?");
    $stmt->execute([$short_name]);
    $original_url = $stmt->fetchColumn();

    if ($original_url) {
        header("Location: " . $original_url);
        exit();
    } else {
        echo "<div class='mt-4 text-red-500'>URL not found!</div>";
    }
}

function renderHomePage($shortUrl = null, $err = null) {
    if($shortUrl != null){
        $url = $shortUrl;
        $shortUrl = "<div class='my-2 text-gray-500'>Shortened URL:</div>";
        $shortUrl .= '<input type="text" readonly value="'.$url.'" class="p-2 border rounded mb-4" placeholder="Your ShortUrl">';
    } else if ($err != null){
        $shortUrl = "<div class='my-2 text-red-500'>$err</div>";
    }

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Short URL Service</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md mx-auto bg-white shadow-lg rounded-lg p-6">
        <h1 class="text-2xl font-bold text-center mb-4">Shorten Your URL</h1>
        <form action="" method="POST" class="flex flex-col space-y-4">
            <input type="url" name="original_url" class="p-2 border rounded w-full" placeholder="Enter your URL" required>
            <input type="text" name="custom_short_name" class="p-2 border rounded w-full" placeholder="Enter custom shortname (optional)">
			<input type="text" name="access_code" class="p-2 border rounded w-full" placeholder="Enter access code" required>
            $shortUrl
            <button type="submit" class="bg-blue-500 text-white p-2 rounded w-full">Shorten URL</button>
        </form>
    </div>
</body>
</html>
HTML;
}
