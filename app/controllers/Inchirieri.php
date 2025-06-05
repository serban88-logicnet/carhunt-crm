<?php
class Inchirieri extends Controller{

	public function __construct(){
		$this->fieldModel = $this->model('Field');
		$this->generalModel = $this->model('General');
	}

	public function inregistreaza_incasare($id, $val) {
	    // 1. Get the current booking (last in chain)
	    $query = "SELECT pret FROM inchirieri WHERE id = :id";
	    $params = [':id' => $id];
	    $result = $this->generalModel->executeQueryPdo($query, $params);
	    $totalPret = $result[0]->pret;

	    // 2. Gather all bookings in the chain (backwards: last to root)
	    $chainIds = getChainBackwards($id);

	    // 3. Calculate total paid so far in the chain
	    $totalPlatit = 0;
	    foreach ($chainIds as $bid) {
	        $q = "SELECT platit FROM inchirieri WHERE id = :id";
	        $r = $this->generalModel->executeQueryPdo($q, [':id' => $bid]);
	        if (!empty($r)) {
	            $totalPlatit += $r[0]->platit;
	        }
	    }

	    // 4. Add new payment to grand total
	    $newTotalPlatit = $totalPlatit + $val;

	    // 5. If over the chain's total price, show error
	    if ($newTotalPlatit > $totalPret) {
	        flash('notices', "Suma depășește valoarea totală de plată pentru această închiriere!", 'alert alert-danger');
	    } else {
	        // 6. Update ONLY the last booking's platit
	        $q = "SELECT platit FROM inchirieri WHERE id = :id";
	        $r = $this->generalModel->executeQueryPdo($q, [':id' => $id]);
	        $currentLastPlatit = $r[0]->platit;
	        $updatedLastPlatit = $currentLastPlatit + $val;

	        $query = "UPDATE inchirieri SET platit = :newPlatit WHERE id = :id";
	        $params = [
	            ':id' => $id,
	            ':newPlatit' => $updatedLastPlatit,
	        ];
	        $this->generalModel->executeQueryPdo($query, $params);

	        // 7. Update status_plata based on payment completeness
	        $status_plata = ($newTotalPlatit >= $totalPret) ? 35 : 34;
	        $this->generalModel->executeQueryPdo(
	            "UPDATE inchirieri SET status_plata = :sp WHERE id = :id",
	            [':sp' => $status_plata, ':id' => $id]
	        );

	        flash('notices', "Plata de $val a fost înregistrată cu succes.", 'alert alert-success');
	    }

	    // 8. Redirect back to details page
	    redirect("index/detalii/inchirieri/" . $id);
	}



}