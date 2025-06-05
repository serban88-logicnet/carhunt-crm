<?php
require_once "../../app/bootstrap.php";

$controller = new Controller;
$controller->generalModel = $controller->model('General');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = $_POST['booking_id'] ?? '';
    if (empty($bookingId)) {
         echo json_encode(['status' => 'error', 'message' => 'Booking ID not provided']);
         exit;
    }
    
    // Query the booking details from the database. Adjust the query as needed.
    $query = "SELECT i.*, c.nume AS client_nume, c.prenume AS client_prenume, b.nume AS license 
              FROM inchirieri i
              JOIN clienti c ON i.client_id = c.id
              JOIN masini b ON i.masina_id = b.id
              WHERE i.id = :bookingId";
    $params = [':bookingId' => $bookingId];
    $result = $controller->generalModel->executeQueryPdo($query, $params);
    
    if ($result) {
         $booking = $result[0];
         // Create HTML content with booking details. Customize as needed.
         $detailsHtml  = '<p><strong>Rezervare:</strong> ' . htmlspecialchars($booking->nume) . '</p>';
         $detailsHtml .= '<p><strong>Client:</strong> ' . htmlspecialchars($booking->client_nume . ' ' . $booking->client_prenume) . '</p>';
         $detailsHtml .= '<p><strong>Inceput:</strong> ' . htmlspecialchars($booking->data_inceput) . ' at ' . htmlspecialchars($booking->ora_inceput) . '</p>';
         $detailsHtml .= '<p><strong>Sfarsit:</strong> ' . htmlspecialchars($booking->data_sfarsit) . ' at ' . htmlspecialchars($booking->ora_sfarsit) . '</p>';
         $detailsHtml .= '<p><strong>Pret:</strong> ' . htmlspecialchars($booking->pret) . ' Euro</p>';
         
         // Return the details as a JSON response.
         echo json_encode(['status' => 'success', 'details' => $detailsHtml]);
    } else {
         echo json_encode(['status' => 'error', 'message' => 'Booking not found']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
