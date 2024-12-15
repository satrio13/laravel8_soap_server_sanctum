<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Helpers\SoapHelper;

class AuthController extends Controller
{
    function wsdl()
    {
        return response()->file(storage_path('app/public/auth.wsdl'), [
            'Content-Type' => 'application/xml'
        ]);
    }

    function register(Request $request)
    {
        try 
        {
            $xml = simplexml_load_string($request->getContent());
            $data = SoapHelper::parseSoapRequest($xml, ['name', 'email', 'password', 'password_confirmation']);

            $validator = Validator::make($data, [
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8|confirmed'
            ]);

            if($validator->fails()) 
            {
                return SoapHelper::soapFaultResponse('Client', $validator->errors()->first());
            }

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password'])
            ]);

            $xmlResponse = SoapHelper::soapSuccessResponse('registerResponse', [
                'message' => 'Register success',
                'userId' => $user->id
            ]);

            return response()->make($xmlResponse, 201, ['Content-Type' => 'application/soap+xml']);
        }catch(\Exception $e) 
        {
            return SoapHelper::soapFaultResponse('Server', 'Invalid XML format or server error');
        }
    }

    function login(Request $request)
    {
        try 
        {
            $xml = simplexml_load_string($request->getContent());
            $data = SoapHelper::parseSoapRequest($xml, ['email', 'password']);

            $validator = Validator::make($data, [
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if($validator->fails()) 
            {
                return SoapHelper::soapFaultResponse('Client', $validator->errors()->first());
            }

            $user = User::where('email', $data['email'])->first();

            if(!$user OR !Hash::check($data['password'], $user->password)) 
            {
                return SoapHelper::soapFaultResponse('Client', 'Unauthorized: Invalid credentials');
            }

            // Generate token (menggunakan Sanctum)
            $token = $user->createToken('YourAppName')->plainTextToken;

            $xmlResponse = SoapHelper::soapSuccessResponse('loginResponse', [
                'message' => 'Login successful',
                'token' => $token
            ]);

            return response()->make($xmlResponse, 200, ['Content-Type' => 'application/soap+xml']);
        }catch(\Exception $e) 
        {
            return SoapHelper::soapFaultResponse('Server', 'Invalid XML format or server error');
        }
    }

    function logout(Request $request)
    {
        $xml = simplexml_load_string($request->getContent());
        $data = SoapHelper::parseSoapRequest($xml, ['token']);
    
        if(empty($data['token'])) 
        {
            return SoapHelper::soapFaultResponse('Client', 'Token is required for logout');
        }
    
        $token = $data['token'];
    
        // Cari user yang memiliki token tersebut
        $user = User::whereHas('tokens', function ($query) use ($token) {
            $query->where('id', $token); // Sesuaikan dengan cara token disimpan, jika menggunakan token ID
        })->first();
    
        if(!$user) 
        {
            return SoapHelper::soapFaultResponse('Client', 'Invalid token or user not found');
        }
    
        // Menghapus token yang sesuai dengan token yang dikirimkan
        $user->tokens->each(function ($userToken) use ($token) 
        {
            if($userToken->id == $token) 
            {
                $userToken->delete(); // Menghapus token yang sesuai
            }
        });
    
        $xmlResponse = SoapHelper::soapSuccessResponse('logoutResponse', [
            'message' => 'Logout successful'
        ]);
    
        return response()->make($xmlResponse, 200, ['Content-Type' => 'application/soap+xml']);
    }

} 