<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Auth;
use App\Models\User;
use App\Models\Company;


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

    public function getUserById(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id' => 'required|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors()->toJson(), 400);
    }

    $user = User::find($request->id);

    return response()->json([
        'success' => true,
        'user' => $user,
    ]);
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



    public function createCompany(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'CompanyName' => 'required',
            'CompanyDescription' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        // Find the authenticated user
        $user = Auth::user();

        
        // Create a new company
        $company = new Company([
            'name' => $request->input('CompanyName'),
            'description' => $request->input('CompanyDescription'),
        ]);

        // Associate the company with the user
        $user->company()->save($company);

        return response()->json([
            'message' => 'Company successfully created',
            'company' => $company,
        ], 201);
    }



    public function getCompany(Request $request)
{
    // Find the authenticated user
    $user = Auth::user();

    // Check if the user has a company associated with them
    if (!$user->company) {
        return response()->json(['success' => false, 'message' => 'User does not have a company'], 400);
    }

    // Retrieve the company associated with the user
    $company = $user->company;

    return response()->json([
        'success' => true,
        'company' => $company,
    ]);
}

public function getAllServices(Request $request){
    if(Company::count()==0){
        return response()->json(['success' => false, 'message' => "theres no services"], 400);

    }
    else{
        $services = Company::all();
        return response()->json([
            'services'=>$services,

        ]);  
    }
 }

}
