<?php

namespace App\Http\Controllers;

use DateTime;
use App\Models\User;
use App\Models\Resto;
use App\Models\Emprunt;
use App\Models\Transfer;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Laravel\Passport\Client;
use App\Models\Establishment;
use Illuminate\Http\Response;
use MercurySeries\Flashy\Flashy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{
    private $client;

    public function __construct()
    {
        $this->client = Client::find(1);
    }

    /**
     * Login method for the user.
     * @param Request $request
     * @return
     */
    public function login(Request $request)
    {
        $request->validate([
            "number" => "required|min:11",
            "password" => "required|min:8"
        ]);

        $params = [
            'grant_type' => 'password',
            'client_id' => $this->client->id,
            'client_secret' => $this->client->secret,
            'username' => $request->number,
            'password' => $request->password,
            'scope' => '',
        ];

        $request->request->add($params);

        $proxy = Request::create('/oauth/token', 'POST');

        return Route::dispatch($proxy);
    }

    /**
     * Logout method for the user
     */
    public function logout()
    {
        $access_token = Auth::user()->token();

        DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $access_token->id)
            ->update(['revoked' => true]);

        $access_token->revoke();

        return response()->json([], Response::HTTP_NO_CONTENT);
    }

    /**
     * For refresing the token
     * @param Request $request
     * @return
     */
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

    /**
     * Demande de Transfer d'une somme à un autre étudiant si possible
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function transfert(Request $request)
    {
        $request->validate([
            'recipient' => 'required|exists:users,number',
        ]);

        $recipient = User::find($request->recipient);

        if ($recipient == null) {
            return response()->json([
                'error' => true,
                'message' => "Le number de dossier que vous avez saisi est incorrect."
            ], Response::HTTP_BAD_REQUEST);
        }
        $data = [];
        $data['first_name'] = $recipient->first_name;
        $data['last_name'] = $recipient->last_name;
        $data['error'] = false;

        return response()->json($data, 200);
    }

    /**
     * User Confirm Transfer
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmTransfert(Request $request)
    {
        $request->validate([
            'recipient' => 'required|exists:users,number',
            'amount' => 'required',
        ]);

        return $this->check($request);
    }

    /**
     * Get the tranfer history of user
     */
    public function transferHistory()
    {
        $user = Auth::user();
        $history =  DB::table("transfers")
            ->where('forwarder_number', '=', $user->number)
            ->orWhere('recipient_number', $user->number)
            ->get();
        $data = [];
        foreach ($history as $key => $value) {
            $data[$key] = [];
            $recipient = User::find($value->recipient_number);
            $forwarder = User::find($value->forwarder_number);

            $amount = $recipient->number == $user->number ? $value->amount : -$value->amount;

            $user_full_name = $recipient->number == $user->number ? $forwarder->first_name . " " . $forwarder->last_name : $recipient->first_name . " " . $recipient->last_name;

            $data[$key] = [
                "amount" => $amount,
                "date" => $value->date_transfert,
                "other" => $user_full_name
            ];
        }
        return response($data, 200);
    }

    /**
     * pay ticket with the OM API
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function bay(Request $request)
    {
        $this->validate($request, [
            'phone_number' => 'required',
            'amount' => 'required'
        ]);

        if ($request->amount < 500) {
            return response()->json([
                'error' => true,
                'message' => "Le transaction par Orange Money comment à partir de 500 fcfa."
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function profile()
    {
        return Auth::user();
    }

    public function pay()
    {
        $user = Auth::user();
        return response()->json($user->pay, 200);
    }

    private function check(Request $request)
    {

        if ($request->amount < 50) {
            return response()->json([
                'error' => true,
                'message' => "Les tranferts se font à partir de 50 fcfa."
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = User::find(Auth::user()->number);

        if ($request->recipient == $user->number) {
            return response()->json([
                'error' => true,
                'message' => "Impossible de faire un Transfer dans son propre compte."
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($user->pay < $request->amount) {
            return response()->json([
                'error' => true,
                'message' => 'Votre solde est insuffusant.'
            ], Response::HTTP_NOT_ACCEPTABLE);
        }

        $user_recipient = User::find($request->recipient);

        if ($user_recipient == NULL) {
            return response()->json([
                'error' => true,
                'message' => "Nous ne trouvons pas cette destinataire."
            ], Response::HTTP_BAD_REQUEST);
        }

        $user->pay -= $request->amount;
        $user->save();

        $user_recipient->pay += $request->amount;
        $user_recipient->save();


        $transfer = new Transfer();
        $transfer->amount = $request->amount;
        $transfer->date_transfert = date("yy-m-d h:m:s");
        $transfer->forwarder_number = $user->number;
        $transfer->recipient_number = $request->recipient;
        $transfer->save();

        return response()->json([
            'error' => false,
            'message' => 'Votre tranfert s\'est terminé avec success.'
        ], Response::HTTP_OK);
    }

    public function create(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'number' => 'required|unique:users,number',
            'establishment' => 'required'
        ]);

        $establishment = Establishment::whereName($request->establishment)->first();

        if ($establishment == NULL) {
            return response()->json([
                'error' => true,
                'message' => $request->establishment . " n'existe pas. Veuillez vérifier le nom renseigné."
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->password = bcrypt("bienvenue");
        $user->created_by = Auth::id();
        $user->updated_by = Auth::id();
        $user->number = $request->number;
        $user->establishment_id = $establishment->id;

        // send an email$send =  to the user
        //Mail::to($user->email)->send(new SendMailToNewUser($request));
        $user->save();
        return response()->json([
            'error' => false,
            'message' => "Traitement terminé avec succés."
        ], Response::HTTP_OK);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'new_password' => 'required'
        ]);

        $user = Auth::user();
        $user_number = $user->number;
        $user_password = $user->password;

        if (Hash::check($request->password, $user_password)) {
            DB::table('users')->where('number', $user_number)
                ->update(['password' => bcrypt($request->new_password)]);
            return response()->json(
                [
                    'error' => false,
                    'message' => 'Mot de passe modifié avec succés'
                ],
                200
            );
        }
        return response()->json(
            [
                'error' => true,
                'message' => 'Mot de passe incorrecte.'
            ],
            400
        );
    }

    public function update(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'number' => 'required|exists:users,number',
            'establishment' => 'required|string'
        ]);

        $establishment = Establishment::whereName($request->establishment)->first();

        if ($establishment == NULL)
            return response()->json([
                'error' => true,
                'message' => Str::of($request->establishment)->upper() . " n'existe pas."
            ], Response::HTTP_BAD_REQUEST);

        DB::table('users')->whereNumber($request->number)->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'number' => $request->new_number == NULL ? $request->number : $request->new_number,
            'establishment_id' => $establishment->id
        ]);

        return response()->json([
            'error' => false,
            'message' => 'La mis à jour s\'est terminée avec succés.'
        ], Response::HTTP_OK);
    }

    public function remove(Request $request)
    {
        $request->validate(['number' => 'required|exists:users,number']);
        DB::table('users')->whereNumber($request->number)->delete();
        return response()->json([
            'error' => false,
            'message' => 'La suppression s\'est terminé avec succes.'
        ], Response::HTTP_OK);
    }

    public function user($number)
    {
        $user = User::find($number);
        if ($user == NULL)
            return response()->json([
                'error' => true,
                'message' => "Le numéro de dossier " . $number . " n'existe pas."
            ], Response::HTTP_BAD_REQUEST);

        return response()->json([
            'error' => false,
            'data' => $user
        ], Response::HTTP_OK);
    }

    /** For test */
    public function users()
    {
        return User::all();
    }

    public function resetPassword(Request $request)
    {
        $rule  =  array(
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        );

        $validator = Validator::make($request->all(), $rule);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();

                $user->setRememberToken(Str::random(60));

                event(new PasswordReset($user));
            }
        );
        if ($status == Password::PASSWORD_RESET) {
            Flashy::success('Votre mot de passe a été réinitialisé avec succés.');
            return redirect()->route('welcome')->with('status', __($status));
        }
        return back()->withErrors(['email' => __($status)]);
    }

    public function emprunt(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric'
        ]);

        $max_emprunt = DB::select('select * from params where name = ?', ['max_emprunt']);

        if ($max_emprunt[0]->value < $request->amount) {
            return response()->json([
                'error' => true,
                'message' => 'Les emprunts de plus de ' . $max_emprunt[0]->value . 'FCFA ne sont pas autorisés.'
            ], 406);
        }

        $user = Auth::user();
        $emprunt = Emprunt::whereUserNumber($user->number)->whereState(false)->first();
        if ($emprunt == null) {
            // vous n'avais pas de dette
            DB::insert('insert into table_emprunt (user_number, date_emprunt, amount) values (?, ?, ?)', [
                $user->number,
                new DateTime(),
                $request->amount
            ]);
            DB::table('users')->whereNumber($user->number)->update([
                'pay' => $user->pay + $request->amount
            ]);

            return response()->json([
                'error' => false,
                'message' => 'Vous venez d\'emprunter ' . $request->amount . 'FCFA.'
            ], 200);
        } else {
            // vous avez un dette de $emprunt->amount
            return response()->json([
                'error' => true,
                'message' => 'Vous avez une dette de ' . $emprunt->amount . 'FCFA non remboursée. Vous ne pouvez pas faire un autre emprunt.'
            ], 406);
        }
    }

    public function historiquePassage()
    {
        $scans = DB::select('select * from scans where user_number = ? limit 30', [Auth::user()->number]);

        foreach ($scans as $s) {
            $s->resto =  Resto::find($s->resto_id)->name;
        }

        return response()->json($scans, 200);
    }
}
