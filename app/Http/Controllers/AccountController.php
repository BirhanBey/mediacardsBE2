<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Account::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "userName" => "required",
            "password" => "required",
            "email" => "required",
            "url" => "required"
        ]);
        return Account::create($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Account::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $account = Account::findOrFail($id);
        $account->update($request->all());
        return $account;
    }

    /**
     * search for a name
     */
    public function search(string $name)
    {
        return Account::where('userName', 'like', '%' . $name . '%')->get();
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return Account::destroy($id);
    }
}
