<?php
class Pages extends Controller{

	public function __construct(){
		$this->userModel = $this->model('User');
		$this->fieldModel = $this->model('Field');
		$this->generalModel = $this->model('General');
		$this->commentModel = $this->model('Comment');
	}

	// Load Homepage
	public function index(){
		if(!isLoggedIn()) {
			redirect("users/login");
		}

		$this->view('pages/index');
	}

	public function about(){
		  //Set Data
		$data = [
			'version' => '1.0.0'
		];

		  // Load about view
		$this->view('pages/about', $data);
	}

	public function not_found(){ 
		  //Set Data


		  // Load about view
		$this->view('pages/notFound');
	}

    public function calendar($year = null) {
        // Redirect to login if not logged in.
        if (!isLoggedIn()) {
            redirect("users/login");
        }
        
        // If no year is provided or the value is invalid, default to the current year.
        if (empty($year) || !is_numeric($year)) {
            $year = date('Y');
        }
        
        // **********************************************
        // 1. Retrieve Bookings Data (for the given year)
        // **********************************************
        // This query retrieves bookings (inchirieri) joined with cars, clients, brands, and categories.
        // We filter by the year of the booking’s start (data_inceput) and by bookins that are NOT overwritten (status 32)
        $bookingQuery = "
            SELECT 
                i.id AS inchiriere_id,
                cl.id AS client_id,
                cl.cnp,
                cl.rating_client,
                b.id AS masina_id,
                b.nume AS license,
                li.nume AS marca,
                ca.nume AS categorie,
                b.model,
                b.clasaPret,
                i.data_inceput,
                i.data_sfarsit,
                i.ora_inceput,
                i.ora_sfarsit,
                cl.nume AS client_name,
                cl.prenume AS client_prenume,
                cl.cnp
            FROM inchirieri i
            JOIN masini b ON i.masina_id = b.id
            JOIN clienti cl ON i.client_id = cl.id
            JOIN marciAuto li ON li.id = b.marca
            JOIN categoriiAuto ca ON ca.id = b.categorie 
            WHERE YEAR(i.data_inceput) = :year
            AND i.status <> 32 
            AND i.status <> 41
            ORDER BY ca.nume, li.nume, b.model
        ";
        $bookingResults = $this->generalModel->executeQueryPdo($bookingQuery, [':year' => $year]);
        

        // Build an array grouping bookings by car.
        // We use a key constructed as: "CAT:[categorie] - [marca] [model] - [license]".
        $bookingsByCar = [];
        foreach ($bookingResults as $row) {
            $carName = "CAT:" . $row->categorie . " - " . $row->marca . " " . $row->model . " - " . $row->license;
            
            // Precompute timestamps and the day-of-year (0-based) for start and end dates.
            $startTimestamp = strtotime($row->data_inceput);
            $endTimestamp   = strtotime($row->data_sfarsit);
            $startDay = date("z", $startTimestamp);
            $endDay   = date("z", $endTimestamp);
            // Duration in days (inclusive)
            $duration = $endDay - $startDay + 1;
            
            // Build the client's full name.
            $clientName = trim($row->client_name . " " . $row->client_prenume);
            
            // Group by car: if not set, initialize the car key with an empty bookings array.
            if (!isset($bookingsByCar[$carName])) {
                $bookingsByCar[$carName] = [
                    'masina_id' => $row->masina_id,
                    'clasaPret' => $row->clasaPret,
                    'license'   => $row->license,
                    'bookings'  => []  // To be grouped by client.
                ];
            }
            
            // Group bookings by client under this car.
            if (!isset($bookingsByCar[$carName]['bookings'][$clientName])) {
                $bookingsByCar[$carName]['bookings'][$clientName] = [
                    'bookings' => [],
                    'rating_client' => $row->rating_client,
                    'cnp' => $row->cnp
                ];
            }
            
            // Use the booking’s start timestamp as key.
            if (!isset($bookingsByCar[$carName]['bookings'][$clientName]['bookings'][$startTimestamp])) {
                $bookingsByCar[$carName]['bookings'][$clientName]['bookings'][$startTimestamp] = [
                    'data_inceput'  => $row->data_inceput,
                    'data_sfarsit'  => $row->data_sfarsit,
                    'ora_inceput'  => $row->ora_inceput,
                    'ora_sfarsit'  => $row->ora_sfarsit,
                    'perioada'      => $duration,
                    'inchiriere_id' => $row->inchiriere_id,
                    'client_id'     => $row->client_id,
                    'masina_id'     => $row->masina_id,
                    "zile"          => []  // This will hold the days (by day-of-year) covered by the booking.
                ];
            }
            
            // Mark each day covered by the booking.
            for ($timestamp = $startTimestamp; $timestamp <= $endTimestamp; $timestamp = strtotime('+1 day', $timestamp)) {
                $dayOfYear = date('z', $timestamp);
                $bookingsByCar[$carName]['bookings'][$clientName]['bookings'][$startTimestamp]["zile"][$dayOfYear] = true;
            }
        }
        
        // **********************************************
        // 2. Retrieve ALL Cars Data
        // **********************************************
        // We now query the database for all cars regardless of bookings.
        $carsQuery = "
            SELECT 
                b.id AS masina_id, 
                b.nume AS license, 
                li.nume AS marca, 
                ca.nume AS categorie, 
                b.model, 
                b.clasaPret 
            FROM masini b
            LEFT JOIN marciAuto li ON li.id = b.marca
            LEFT JOIN categoriiAuto ca ON ca.id = b.categorie
            ORDER BY 
                CASE WHEN b.masina_temp = 29 THEN 1 ELSE 0 END,
                CASE WHEN b.masina_temp = 29 THEN ca.sort ELSE 0 END,
                ca.nume,
                li.nume,
                b.model


        ";
        $cars = $this->generalModel->executeQuery($carsQuery);
        
        // dd($cars);

        // Build an array of all cars, keyed by the same $carName used above.
        $allCarsByName = [];
        foreach ($cars as $car) {
            $carName = "CAT:" . $car->categorie . " - " . $car->marca . " " . $car->model . " - " . $car->license;
            $allCarsByName[$carName] = [
                'masina_id' => $car->masina_id,
                'clasaPret' => $car->clasaPret,
                'license'   => $car->license,
                // Default to an empty bookings array if there are no bookings.
                'bookings'  => []
            ];
        }
        
        // Merge the booking data into the master car list.
        // For each car that has bookings, assign its bookings to the master array.
        foreach ($bookingsByCar as $carName => $bookings) {
            if (isset($allCarsByName[$carName])) {
                $allCarsByName[$carName]['bookings'] = $bookings['bookings'];
            } else {
                // In case a car appears in bookings but not in the cars query (should not happen),
                // add it.
                $allCarsByName[$carName] = $bookings;
            }
        }
        
        // **********************************************
        // 3. Retrieve Offers Data (Filter by year)
        // **********************************************
        // Filter offers so that only those whose start date is in the current year are retrieved.
        $offerQuery = "
            SELECT o.*, c.nume AS nume, c.prenume AS prenume
            FROM oferte o
            JOIN clienti c ON c.id = o.client_id
            WHERE o.status_oferta IN ('16','18') AND YEAR(o.data_inceput) = :year
            ORDER BY o.data_inceput ASC
        ";
        $offerResults = $this->generalModel->executeQueryPdo($offerQuery, [':year' => $year]);
        
        // Preload all price classes to avoid running a query per offer.
        $allPriceClasses = $this->generalModel->executeQuery("SELECT * FROM clasePret");
        $priceClassesById = [];
        foreach ($allPriceClasses as $pc) {
            $priceClassesById[$pc->id] = $pc;
        }
        
        // Process each offer: attach the corresponding price class details.
        foreach ($offerResults as $result) {
            // Use the accepted offer if available; otherwise, use the sent offer.
            $priceClassIds = isset($result->oferta_acceptata) 
                ? explode(",", $result->oferta_acceptata) 
                : explode(",", $result->oferta_trimisa);
            $result->clasePretIds = $priceClassIds;
            $result->clasePretDetalii = [];
            foreach ($priceClassIds as $clasaPretId) {
                if (isset($priceClassesById[$clasaPretId])) {
                    $result->clasePretDetalii[] = $priceClassesById[$clasaPretId];
                }
            }
            // Build a comma‑separated list of price class names.
            $classNames = [];
            foreach ($result->clasePretDetalii as $temp) {
                $classNames[] = $temp->nume;
            }
            $result->clasePretNames = implode(", ", $classNames);
            
            // Format time fields.
            if (isset($result->ora_inceput))
                $result->ora_inceput = date("H:i", strtotime($result->ora_inceput));
            if (isset($result->ora_sfarsit))
                $result->ora_sfarsit = date("H:i", strtotime($result->ora_sfarsit));
        }
        $data['available_offers'] = $offerResults;
        
        // **********************************************
        // 4. Pass Data to the View
        // **********************************************
        // $data['bookings'] now contains all cars (even those without bookings),
        // $data['year'] holds the selected year.
        $data['bookings'] = $allCarsByName;

        $data['year'] = $year;
        $this->view('pages/calendar', $data);
    }


	public function liste($list_name) {
		$query = "SELECT nume FROM other_lists WHERE list_name LIKE '$list_name'";
		$results = $this->generalModel->executeQuery($query);

		$data['list_name'] = $list_name;

		$this->view('pages/liste',$data);
	}

	public function predari_retururi($carNr = "") {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // now you know the form was submitted via POST
            if (isset($_POST['carNr'])) {
                $url = "pages/predari-retururi/".$_POST['carNr'];
                redirect($url);
            }
        }
        if($carNr == "" or $carNr == NULL) {
            $queryWhere = "";
        } else {
            $queryWhere = " AND m.nume LIKE '$carNr' ";
        }
	    // --- Fetch “predari” (bookings for handover) ---
	    $query = "SELECT i.*, c.nume as nume_client, m.nume as license, m.km_actuali 
	              FROM inchirieri i
	              JOIN masini m ON i.masina_id = m.id
	              JOIN clienti c ON i.client_id = c.id
	              WHERE i.status LIKE '20' $queryWhere
	              ORDER BY i.data_inceput ASC";

        // dd($query);
	    $predari = $this->generalModel->executeQuery($query);

	    // --- Fetch “retururi” (bookings for return) ---
	    $query = "SELECT i.*, c.nume as nume_client, m.nume as license, m.km_actuali 
	              FROM inchirieri i
	              JOIN masini m ON i.masina_id = m.id
	              JOIN clienti c ON i.client_id = c.id
	              WHERE i.status LIKE '21' $queryWhere
	              ORDER BY i.data_sfarsit ASC";
	    $retururi = $this->generalModel->executeQuery($query);

        // Add Buffer fetching
        $buffer = $this->generalModel->executeQuery("
            SELECT i.*, c.nume as nume_client, m.nume as license, m.km_actuali 
            FROM inchirieri i
            JOIN masini m ON i.masina_id = m.id
            JOIN clienti c ON i.client_id = c.id
            WHERE i.status = 43 $queryWhere
            ORDER BY i.data_inceput ASC
        ");
        $data['buffer'] = $buffer;


	    // --- Get the list of possible 'marcata_pentru' values ---
	    $query = "SELECT * FROM other_lists WHERE list_name LIKE 'marcata_pentru'";
	    $marcataPentru = $this->generalModel->executeQuery($query);

	    // --- For each booking, calculate the total remaining amount ("restPlata")
	    //     taking into account any extensions in the chain.

        foreach ($predari as $key => $booking) {
             $restPlata = $this->calculateChainRestPlata($booking);
             // Add a new property to the booking object.
             $predari[$key]->restPlata = $restPlata;
        }

	    foreach ($retururi as $key => $booking) {
	         $restPlata = $this->calculateChainRestPlata($booking);
	         // Add a new property to the booking object.
	         $retururi[$key]->restPlata = $restPlata;
	    }

	    // --- Pass the data to the view ---
	    $data['predari'] = $predari;
	    $data['retururi'] = $retururi;
	    $data['marcataPentru'] = $marcataPentru;

	    $this->view('pages/predariRetururi', $data);
	}

	/**
	 * Given a booking object, this method determines the full extension chain and
	 * returns the remaining amount to be paid for the entire chain.
	 *
	 * It does so by:
	 *   1. Finding the root booking (the one that is not an extension).
	 *   2. Traversing forward (since each extension booking stores the ID of the booking it extends)
	 *      to build an array of all bookings in the chain.
	 *   3. Summing up the 'pret' (price) and 'platit' (amount paid) fields from each booking.
	 *   4. Returning the difference: total price minus total paid.
	 *
	 * @param object $booking A booking object from the database.
	 * @return float The remaining amount to be paid.
	 */
    private function calculateChainRestPlata($booking) {
        // 1. Find root
        $current = $booking;
        while (!empty($current->inchiriere_extinsa_de_la_id)) {
            $result = $this->generalModel->executeQueryPdo(
                "SELECT * FROM inchirieri WHERE id = :id",
                [':id' => $current->inchiriere_extinsa_de_la_id]
            );
            if (!empty($result)) {
                $current = $result[0];
            } else {
                break;
            }
        }
        $root = $current;

        // 2. Build the chain (root to last)
        $chain = [];
        $chain[] = $root;
        $currentId = $root->id;
        while (true) {
            $result = $this->generalModel->executeQueryPdo(
                "SELECT * FROM inchirieri WHERE inchiriere_extinsa_de_la_id = :id",
                [':id' => $currentId]
            );
            if (!empty($result)) {
                $child = $result[0];
                $chain[] = $child;
                $currentId = $child->id;
            } else {
                break;
            }
        }

        // 3. Sum platit across the chain
        $totalPlatit = 0;
        foreach ($chain as $b) {
            $totalPlatit += floatval($b->platit);
        }

        // 4. Get 'pret' from the last booking only
        $latestBooking = end($chain); // last element
        $lastPret = floatval($latestBooking->pret);

        // 5. Calculate and return the remainder
        return $lastPret - $totalPlatit;
    }



}