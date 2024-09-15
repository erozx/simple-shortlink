# Simple Shortlink

**Simple Shortlink** is a basic URL shortening service that allows you to create and manage short URLs that redirect to longer, more complex URLs. This project focuses on simplicity and ease of use, providing a minimal yet functional implementation of a shortlink service.

## Features

- Custom short URL generation.
- URL validation and security against common vulnerabilities (e.g., XSS, SQL injection).
- Simple SQLite database for storing URLs.
- URL redirection to original links via shortlinks.
- Basic management interface for viewing and deleting shortlinks.

## Requirements

- PHP (version 7.4 or higher).
- SQLite extension for PHP.
- Web server (e.g., Apache, Nginx) with PHP support.

## Installation
1.Clone this repository to your web server or localhost root location:
   ```bash
   git clone https://github.com/yourusername/simple-shortlink.git
   ```
2.Setup some config:
 - Open the config.php file, then change the ACCESS_CODE, ADMIN_USER and ADMIN_PASSWORD as you like
   
## Usage
1.Create Shortlink:
 - Navigate to the project URL and enter the original URL you want to shorten.
 - Optionally, you can provide a custom shortlink name.
   
2.Access Shortlink:
 - Use the generated shortlink (e.g., http://yourdomain.com/shortname) to access the original URL.

3.Manage Shortlinks:
 - Access the admin panel (manage.php) to view or manage your shortlinks.
