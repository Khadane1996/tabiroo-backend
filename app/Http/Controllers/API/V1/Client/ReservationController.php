<?php

namespace App\Http\Controllers\API\V1\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Reservation;
use App\Models\Notification;


class ReservationController extends Controller
{

    public function index($user_id)
    {
        $reservations = Reservation::with('menuPrestation.menu','chef')
        ->where('client_id', $user_id)
        ->orderBy('id', 'desc')
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'Liste de mes reservations',
            'data' => $reservations
        ]);
    }

    public function store(Request $request)
    {
        try {

            $validate = Validator::make($request->all(), [
                'menu_prestation_id' => 'required|exists:menu_prestation,id',
                'client_id' => 'required|exists:users,id',
                'chef_id' => 'required|exists:users,id',
                'sous_total' => 'required|string',
                'frais_service' => 'required|string',
                'nombre_convive' => 'required|string',
                'date_prestation' => 'required|string',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validate->errors()
                ], 401);
            }

            $reservation = Reservation::create([
                'menu_prestation_id' => $request->menu_prestation_id,
                'client_id' => $request->client_id,
                'chef_id' => $request->chef_id,
                'sous_total' => $request->sous_total,
                'frais_service' => $request->frais_service,
                'nombre_convive' => $request->nombre_convive,
                'date_prestation' => $request->date_prestation,
                'transaction_detail' => $request->transaction_detail
            ]);

            Notification::notifyReservation($request->chef_id, $reservation->id);
                
            return response()->json([
                'status' => true,
                'message' => 'Réservation créée avec succès',
                'reservation' => $reservation
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $reservation = Reservation::findOrFail($id);

            // Vérifier si le client est bien le propriétaire
            // if ($reservation->client_id !== $request->user()->id) {
            // if ($reservation->client_id) {
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Accès non autorisé'
            //     ], 403);
            // }

            $reservation->status = $request->status;
            if($request->motif){
                $reservation->motif = $request->motif;
            }
            $reservation->save();

            Notification::notifyReservationAnnuler($reservation->chef_id, $reservation->client_id, $reservation->id);

            return response()->json([
                'status' => true,
                'message' => 'Réservation annulée avec succès',
                'reservation' => $reservation
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function getReservationForChef($user_id)
    {
        $reservations = Reservation::with('menuPrestation.prestation.typeDeRepas','client')
        ->where('chef_id', $user_id)
        ->orderBy('id', 'desc')
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'Liste de mes reservations',
            'data' => $reservations
        ]);
    }

    public function getNotication($user_id)
    {
        $notifications = Notification::with('user')
        ->where('user_id', $user_id)
        ->orderBy('id', 'desc')
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'Liste de mes notifications',
            'data' => $notifications
        ]);
    }

}
