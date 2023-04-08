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
        $accounts = Account::with('urls')->get()->map(function ($account) {
            $urls = $account->urls->map(function ($url) {
                return [
                    'id' => $url->id,
                    'name' => $url->name,
                    'link' => $url->link,
                    'active' => $url->isActive,
                ];
            });
            return [
                'id_account' => $account->id,
                'userName' => $account->userName,
                'email' => $account->email,
                'img' => $account->img,
                'description' => $account->description,
                'created_at' => $account->created_at,
                'updated_at' => $account->updated_at,
                'url' => $urls,
            ];
        });

        return response()->json($accounts);
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
            "name" => "required",
            "link" => "required",
            "active" => "required"
        ]);

        $url = [
            "name" => $request->name,
            "link" => $request->link,
            "isActive" => $request->active
        ];

        $account = Account::create([
            "userName" => $request->userName,
            "password" => $request->password,
            "email" => $request->email,
            "image" => $request->img,
            "description" => $request->description
        ]);

        $account->urls()->create($url);

        if ($account && $url) {
            return [
                "type" => "success",
                "message" => "Request is success"
            ];
        } else {
            return [
                "type" => "error",
                "message" => "Request is error"
            ];
        }
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

    public function url_update(Request $request, string $id, string $url_id)
    {
        $account = Account::findOrFail($id);
        $url = $account->urls()->find($url_id);

        if (!$url) {
            return response()->json(['message' => 'URL not found'], 404);
        }

        $url->update($request->all());
        return $url;
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

    public function url_destroy($id, $url_id)
    {
        $account = Account::findOrFail($id);
        $url = $account->urls()->find($url_id);
        if (!$url) {
            return response()->json(['message' => 'Url not found'], 404);
        }
        $url->delete();
        return response()->json(['message' => 'Url deleted successfully'], 200);
    }
}
