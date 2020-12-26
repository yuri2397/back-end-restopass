<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Establishment;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class EstablishmentController extends Controller
{

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:establishments,name'
        ]);

        $establishment = new Establishment();
        $establishment->name = $request->name;
        $establishment->state = false;
        $code = '';
        while(true){
            $code = Str::random(32);
            $test = Establishment::whereCode($code)->first();
            if($test == null){
            break;
            }
        }
        $establishment->created_by = Auth::id();
        $establishment->updated_by = Auth::id();
        $establishment->code = $code;
        $establishment->save();

        return response()->json([
            'error' => false,
            'message' => $request->name . " a été crée avec succés."
        ], Response::HTTP_OK);
    }

    public function remove(Request $request)
    {
        $request->validate(['name' => 'required|exists:establishments,name']);
        DB::table('establishments')->whereName($request->name)->delete();
        return response()->json([
            'error' => false,
            'message' => 'La suppression s\'est terminé avec succes.'
        ], Response::HTTP_OK);
    }

    public function establishment($name)
    {
        $establishment = Establishment::whereName($name)->first();
        return $establishment == NULL ? response()->json([
            'error' => true,
            'message' => Str::of($name)->upper() . " n'existe pas."
        ], Response::HTTP_BAD_REQUEST) : response()->json([
            'error' => false,
            'data' => $establishment
        ], Response::HTTP_OK);
    }

    public function establishments()
    {
        return Establishment::all();
    }

    /*public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|exists:establishments,name',
            'new_name' => 'required'
        ]);
        DB::table('establishments')->whereName($request->name)->update(
            ['name' => $request->new_name]
        );
        return response()->json([
            'error' => false,
            'message' => 'La mis à jour s\'est terminée avec succés.'
        ], Response::HTTP_OK);
    }*/

    public function changeState(Request $request)
    {
        $request->validate(['name' => 'required|exists:establishments,name']);

        $establishment = Establishment::whereName($request->name)->first();
        if ($establishment == NULL) {
            return response()->json([
                'error' => true,
                'message' => "Veuillez verifier le nom de l'établissement."
            ], Response::HTTP_BAD_REQUEST);
        }
        $establishment->state = !$establishment->state;
        $message = $establishment->state ? "activé" : "desactivé";
        $establishment->save();

        return response()->json([
            'error' => false,
            'message' => "Vous avez " . $message . " le sans ticket pour " . $establishment->name
        ], Response::HTTP_OK);
    }
}
