<?php
session_start();

// Include configuration and helper
require_once 'config.php';
require_once 'GraphHelper.php';

// Check if the access token exists
if (!isset($_SESSION['accessToken'])) {
    header('Location: index.php'); // Redirect to login if no token
    exit();
}

die(print_r($_SESSION['accessToken']));

// Initialize the Graph client
GraphHelper::initializeGraphForUserAuth();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
            text-align: left;
        }
        td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body>
    <h1>Dashboard</h1>
    <h2>Last 10 Emails</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Sender</th>
                <th>Subject</th>
                <th>Content</th>
            </tr>
        </thead>
        <tbody>
            <?php
            try {
                $emails = GraphHelper::getInbox();
                if ($emails) {
                    foreach ($emails->getValue() as $email) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($email->getReceivedDateTime()) . '</td>';
                        echo '<td>' . htmlspecialchars($email->getSender()->getEmailAddress()->getAddress()) . '</td>';
                        echo '<td>' . htmlspecialchars($email->getSubject()) . '</td>';
                        echo '<td>' . htmlspecialchars(substr($email->getBodyPreview(), 0, 100)) . '...</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="4">No emails found.</td></tr>';
                }
            } catch (Exception $e) {
                echo '<tr><td colspan="4">Error fetching emails: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
            }
            ?>
        </tbody>
    </table>
</body>
</html>
