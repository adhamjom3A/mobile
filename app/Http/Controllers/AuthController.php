<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use validator;
use App\Models\User;

class AuthController extends Controller
{
     public function _construct(){
        $this->middleware('auth:api',['except'=>['login','register']]);
     }

     public function register(Request $request){
        $validator=\Validator::make($request->all(),[
            'CompanyName'=>'required',
            'ContactPersonName'=>'required',
            'CompanyIndustry'=>'required',
            'ContactPersonPhone'=>'required',
            'email'=>'required|string|email|unique:users',
            'CompanyAddress'=>'required',
            'CompanyLocation'=>'required',
            'CompanySize'=>'required',
            'password'=>'required|string|confirmed|min:8',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(),400);
        }
        $user = User::create(array_merge(
            $validator->validated(),
            ['password'=>bcrypt($request->password)]
        ));
        return response()->json([
            'message'=>'User successfully registered',
            'user'=>$user
        ],201);
    }

    public function login(Request $request){
        $validator=\Validator::make($request->all(),[
            'email'=>'required|email',
            'password'=>'required|string|min:8',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(),422);
        }
        if(!$token=auth()->attempt($validator->validated())){
            return response()->json(['error'=>'Unauthorized'],401);
        }
        return $this->createNewToken($token);

    }
    public function createNewToken($token){
        return response()->json([
            'access_token'=>$token,
            'token_type'=>'bearer',
            'expires_in'=>auth()->factory()->getTTL()*60,
            'user'=>auth()->user()
             

        ]);
    }

    public function profile(){
        return response()->json(auth()->user());
    }


    public function logout(){
        auth()->logout();
        return response()->json([
            'message'=>'User logged out'
        ],201);
    }

    public function edit_profile(Request $request){
        if(auth()->user()){
            $validator=\Validator::make($request->all(),[
                'id'=>'required',
                'CompanyName'=>'required',
                'ContactPersonName'=>'required',
                'CompanyIndustry'=>'required',
                'ContactPersonPhone'=>'required',
                'email'=>'required|string|email|unique:users',
                'CompanyAddress'=>'required',
                'CompanyLocation'=>'required',
                'CompanySize'=>'required',
            ]);
            if($validator->fails())
            {
                return Response()->json($validator->errors());
            }
            $user = User::find($request->id);
            $user->CompanyName=$request->CompanyName;
            $user->ContactPersonName=$request->ContactPersonName;
            $user->CompanyIndustry=$request->CompanyIndustry;
            $user->ContactPersonPhone=$request->ContactPersonPhone;
            $user->email=$request->email;
            $user->CompanyAddress=$request->CompanyAddress;
            $user->CompanyLocation=$request->CompanyLocation;
            $user->CompanySize=$request->CompanySize;
            $user->save();
            return response()->json(['success'=>true,'msg'=>'User data','data'=>$user]);

        }else{
            return response()->json(['success'=>false,'message'=>'user is not authenticated']);
        }
    }





}
