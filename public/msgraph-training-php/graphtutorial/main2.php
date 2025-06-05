<?php
// Include Composer autoload and GraphHelper
require_once realpath(__DIR__ . '/vendor/autoload.php');
require_once 'GraphHelper.php';


// Load .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required(['CLIENT_ID', 'TENANT_ID', 'GRAPH_USER_SCOPES']);
die("HI");
session_start();
initializeGraph();
greetUser();

$choice = isset($_POST['choice']) ? (int)$_POST['choice'] : -1;
$output = '';

switch ($choice) {
    case 1:
        $output = displayAccessToken();
        break;
    case 2:
        $output = listInbox();
        break;
    case 3:
        $output = sendMail();
        break;
    case 4:
        $output = makeGraphCall();
        break;
    case 0:
    default:
        $output = 'Goodbye...';
}

function initializeGraph(): void {
    GraphHelper::initializeGraphForUserAuth();
}

function greetUser(): void {
    try {
        $user = GraphHelper::getUser();
        $_SESSION['user'] = $user;

        // For Work/school accounts, email is in Mail property
        // Personal accounts, email is in UserPrincipalName
        $email = $user->getMail();
        if (empty($email)) {
            $email = $user->getUserPrincipalName();
        }
        $_SESSION['email'] = $email;
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error getting user: '.$e->getMessage();
    }
}

function displayAccessToken(): string {
    try {
        $token = GraphHelper::getUserToken();
        return 'User token: '.$token;
    } catch (Exception $e) {
        return 'Error getting access token: '.$e->getMessage();
    }
}

function listInbox(): string {
    try {
        $messages = GraphHelper::getInbox();
        $output = '';
        foreach ($messages->getValue() as $message) {
            $output .= 'Message: '.$message->getSubject().'<br>';
            $output .= '  From: '.$message->getFrom()->getEmailAddress()->getName().'<br>';
            $status = $message->getIsRead() ? "Read" : "Unread";
            $output .= '  Status: '.$status.'<br>';
            $output .= '  Received: '.$message->getReceivedDateTime()->format(\DateTimeInterface::RFC2822).'<br><br>';
        }
        $nextLink = $messages->getOdataNextLink();
        $moreAvailable = isset($nextLink) && $nextLink != '' ? 'True' : 'False';
        $output .= 'More messages available? '.$moreAvailable.'<br><br>';
        return $output;
    } catch (Exception $e) {
        return 'Error getting user\'s inbox: '.$e->getMessage();
    }
}

function sendMail(): string {
    try {
        $user = $_SESSION['user'];
        $email = $_SESSION['email'];
        GraphHelper::sendMail('Testing Microsoft Graph', 'Hello world!', $email);
        return 'Mail sent.';
    } catch (Exception $e) {
        return 'Error sending mail: '.$e->getMessage();
    }
}

function makeGraphCall(): string {
    try {
        GraphHelper::makeGraphCall();
        return 'Graph call made.';
    } catch (Exception $e) {
        return 'Error making Graph call: '.$e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Graph Tutorial</title>
</head>
<body>
    <h1>PHP Graph Tutorial</h1>
    <form method="post">
        <p>
            <label><input type="radio" name="choice" value="1" /> Display access token</label><br>
            <label><input type="radio" name="choice" value="2" /> List my inbox</label><br>
            <label><input type="radio" name="choice" value="3" /> Send mail</label><br>
            <label><input type="radio" name="choice" value="4" /> Make a Graph call</label><br>
            <label><input type="radio" name="choice" value="0" /> Exit</label>
        </p>
        <input type="submit" value="Submit" />
    </form>
    <div>
        <?php if (isset($output)) echo '<p>'.$output.'</p>'; ?>
        <?php if (isset($_SESSION['error'])) echo '<p>'.$_SESSION['error'].'</p>'; ?>
    </div>
</body>
</html>
