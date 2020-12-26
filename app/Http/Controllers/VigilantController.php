<?php

namespace App\Http\Controllers;

use App\Models\Establishment;
use App\Models\Scan;
use App\Models\Resto;
use App\Models\User;
use App\Models\Vigilant;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Laravel\Passport\Client;

class VigilantController extends Controller
{
    private $client;
    private const PLUS = "-CROUS-T";
    private const BREAKFAST = [07,11];
    private const LUNCH     = [12,15];
    private const DINER     = [19, 22];
    private const DATE_FORMAT = "o-m-d G:m:s";

    public function __construct(){
        $this->client = Client::find(3);
    }

    public function create(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
			'resto' => 'required'
        ]);

		$resto = Resto::whereCode($request->resto)->first();
		if($resto == null)
			return response()->json([
				'error' => true,
				'message' => 'Veuillez donner un resto existant'
			]);


        do {
            $code = Str::random(8);
            $test = Vigilant::whereCode($code)->get();
        } while ($test->count() != 0);

        $password = Str::random(16);

        $vigilant = new Vigilant();
        $vigilant->first_name = $request->first_name;
        $vigilant->last_name = $request->last_name;
        $vigilant->code = $code;
		$vigilant->resto_id = $resto->id;
		$vigilant->created_by = Auth::id();
		$vigilant->updated_by = Auth::id();
        $vigilant->password = bcrypt($password);
        $vigilant->save();

        return response()->json([
            'error' => false,
            'code' => $code . '@' . $password,
            'message' => 'Le compte de ' . $request->first_name . " " . $request->last_name . " est bien enregistré."
        ], Response::HTTP_OK);
    }

    public function vigils()
    {
        $all = Vigilant::all();
        $data = [];
        foreach ($all as $vigilant) {
            $tmp = [
                'first_name' => $vigilant->first_name,
                'last_name' => $vigilant->last_name,
                'code' => substr($vigilant->code, 0, 8)
            ];
            array_push($data, $tmp);
        }
        return response()->json(['error' => false ,'data' => $data], Response::HTTP_OK);
    }

    public function vigil($code)
    {
        $vigilant = Vigilant::whereCode($code)->first();

        if($vigilant === NULL)
            return response()->json([
                'error' => true,
                'message' => 'Nous n\'avons trouvé aucun vigil avec ce code.'
            ], Response::HTTP_BAD_REQUEST);

        $tmp = [
            'first_name' => $vigilant->first_name,
            'last_name' => $vigilant->last_name,
            'code' => substr($vigilant->code, 0, 8)
        ];

        return response()->json([
            'error' => false,
            'date' => $tmp
        ], Response::HTTP_OK);
    }

    public function update(Request $request)
    {
        $request->validate([
            'code' => 'required|exists:vigilants,code',
            'first_name' => 'required',
            'last_name' => 'required'
        ]);

        DB::table('vigilants')->whereCode($request->code)->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name
        ]);

        return response()->json([
            'error' => false,
            'message' => "La mis à jour c'est terminée avec succés."
        ], Response::HTTP_OK);
    }

    public function remove(Request $request)
    {
        $request->validate(['code' => 'required|exists:vigilants,code']);
        $delete = DB::table('vigilants')->whereCode($request->code)->delete();

        return $delete == 0 ?
            response()->json([
                'error' => true,
                'message' => 'Veuillez vérifier le code du vigil.'
            ], Response::HTTP_BAD_REQUEST)
            : response()->json([
                'error' => false,
                'message' => 'La suppression c\'est terminée avec success.'
            ], Response::HTTP_OK);
    }

    public function login(Request $request){
        $request->validate([
            'code' => 'required|exists:vigilants,code',
            'password' => 'required'
        ]);

        $params = [
            'grant_type' => 'password',
            'client_id' => $this->client->id,
            'client_secret' => $this->client->secret,
            'username' => $request->code,
            'password' => $request->password,
            'scope' => '*',
        ];

        $request->request->add($params);

        $proxy = Request::create('/oauth/token', 'POST');

        return Route::dispatch($proxy);
    }

    public function logout()
    {
        $access_token = Auth::user()->token();

        DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $access_token->id)
            ->update(['revoked' => true]);

        $access_token->revoke();

        return response()->json([], Response::HTTP_NO_CONTENT);
    }

    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required'
        ]);

        $params = [
            'grant_type' => 'refresh_token',
            'client_id' => $this->client->id,
            'client_secret' => $this->client->secret,
            'refresh_token' => $request->refresh_token
        ];

        $request->request->add($params);

        $proxy = Request::create('/oauth/token', 'POST');

        return Route::dispatch($proxy);
    }

    public function scan(Request $request)
    {
        $request->validate(['number' => 'required|exists:users,number']);
        $amount = VigilantController::amount();

        if($amount == -1){
            return response()->json([
                'error' => false,
                'message' => "Il n'est pas encore l'heure",
            ], 409);
        }

        if(self::isPass($request->number)){
            return response()->json([
                'error' => false,
                'message' => "Déjà passé.",
            ], 406);
        }

        $user = User::find($request->number);
        $establishment = Establishment::find($user->establishment_id);

        if($establishment->state === true)
            $amount = 0;

        if($user->pay < $amount){
            return response()->json([
                'error' => true,
                'message' => "Le montant du compte est insuffisant."
            ], 400);
        }

        DB::table("users")->whereNumber($user->number)->update([
            'pay' => $user->pay - $amount,
            ]);

        $resto = Resto::find(Auth::user()->resto_id);

        $scan = new Scan();
        $scan->amount = $amount;
        $scan->scan_date = new DateTime();
        $scan->user_number = $request->number;
        $scan->vigilant_id = Auth::id();
        $scan->resto_id = $resto->id;
        $scan->save();

        return response()->json([
            'error' => false,
            'message' => "Laissez le passer."
        ], Response::HTTP_OK);
    }

    /**
     * @return  int montant à payer suivant l'heure
     */
    private static function amount()
    {
        $date = intval(date("H"));
        $amount = -1;
        if(self::BREAKFAST[0] <= $date && $date <= self::BREAKFAST[1])
            $amount = 50;
        else if((self::LUNCH[0] <= $date && $date <= self::LUNCH[1]) || (self::DINER[0] <= $date && $date <= self::DINER[1]))
            $amount = 100;

        return $amount;
    }

    /**
     * Chercher le dernier passe d'un user
     * @param String $number
     * @return bool si pas encore passer, false sinon
     * @throws \Exception
     */
    public static function isPass(String $number):bool{
        $user = User::find($number);
        $date = DB::select("select scan_date from scans where user_number = ? order by scan_date desc limit 1", [$number]);
        if(count($date) === 0)
            return false;
        $date = $date[0]->scan_date;
        $current_date = date(self::DATE_FORMAT);

        $diff = date_diff(new DateTime($current_date), new DateTime($date));

        if($diff->y === 0 && $diff->m === 0 && $diff->d === 0){
            $current_h = date_parse($current_date)['hour'];
            $last_h = date_parse($date)['hour'];
            return self::checkInterval($last_h, $current_h);
        }

        return false;
    }

    private static function checkInterval($date1, $date2):bool{
        if( (self::LUNCH[0] <= $date1 && $date1 <= self::LUNCH[1]) && (self::LUNCH[0] <= $date2 && $date2 <= self::LUNCH[1])
            || (self::BREAKFAST[0] <= $date1 && $date1 <= self::BREAKFAST[1]) && (self::BREAKFAST[0] <= $date2 && $date2 <= self::BREAKFAST[1])
            || (self::DINER[0] <= $date1 && $date1 <= self::DINER[1]) && (self::DINER[0] <= $date2 && $date2 <= self::DINER[1])
        )
        {
            return true;
        }
        return false;
    }
}
