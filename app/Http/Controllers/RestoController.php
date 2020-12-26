<?php

namespace App\Http\Controllers;

use App\Models\Resto;
use App\Models\Universite;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Resto::all();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            "name" => "required"
        ]);

        $resto = new Resto();
        $resto->name = $request->name;
        $code = '';

        while(1){
            $code = Str::random(32);
            $test = Resto::whereCode($code)->first();
            if( $test == null)
                break;
        }

        $resto->code = $code;
        $auth_id = Auth::id();
        $resto->created_by = $auth_id;
        $resto->updated_by = $auth_id;
        $resto->save();

        return response()->json([
            'error' => false,
            'message' => 'Traitement reuissi.'
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function show($resto)
    {
        return Resto::find($resto);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Resto  $resto
     * @return \Illuminate\Http\Response
     */
    public function edit(Resto $resto)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Resto  $resto
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Resto $resto)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Resto  $resto
     * @return \Illuminate\Http\Response
     */
    public function destroy(Resto $resto)
    {
        //
    }
}
