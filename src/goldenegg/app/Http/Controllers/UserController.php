<?php

namespace App\Http\Controllers;

use App\Models\Security;
use App\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        //
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function show()
    {
        return view('user.show',[
            'user' => \Auth::user()
        ]); 
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        $user = User::find(\Auth::user()->id);
        if(is_null($user->security)){
            $user->security = new \App\Models\Security();
        }
        return view('user.edit', ['user' => $user]); 
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user, Security $security)
    {
        //dd($security->id);
        //dd($request->get('security'));
        \DB::beginTransaction();
        try{
            $security->fill($request->all()['security']);
            $security->user_id = $user->id;
            $security->save();
            $user->fill($request->get('user'));
            $user->save();
        }catch(Exception $e){
            DB::rollback();
            return back()->withInput();
        }
        \DB::commit();
        return redirect(route('user.show'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }
}
