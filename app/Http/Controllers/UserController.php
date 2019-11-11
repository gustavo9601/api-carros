<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\DB;


class UserController extends Controller
{

    public function register(Request $request)
    {

        //Recoger las variables por post
        $json = $request->input('json', null);

        //Convertimos la respuesta de json a un array
        $params = json_decode($json);

        $email = (!is_null($json) && isset($params->email)) ? $params->email : null;
        $name = (!is_null($json) && isset($params->name)) ? $params->name : null;
        $surname = (!is_null($json) && isset($params->surname)) ? $params->surname : null;
        $role = 'ROLE_USER';
        $password = (!is_null($json) && isset($params->password)) ? $params->password : null;
        $data = [];

        //Si pasa la validacion creamos el usuario
        if (!is_null($email) && !is_null($password) && !is_null($name)) {
            //Crear el usuario
            $user = new User();
            $user->email = $email;
            $user->name = $name;
            $user->surname = $surname;
            $user->role = $role;

            //Encriptamos el password
            $pwd = hash('sha256', $password);
            $user->password = $pwd;

            //Comprobar usuario duplicado
            $isset_user = User::where('email', '=',  $email)->first(); //veifricamos si ya exite el email

            //Crear el usuario

            if($isset_user == null){

                $user->save();
                $data = [
                    'status' => 'succes',
                    'code' => 200,
                    'message' => 'Usuario creado correctamente'
                ];

            }else{
                //No lo guarda por que ya exite el mauil

                $data = [
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'Usuario duplicado'
                ];
            }

        } else {
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'Usuario no creado'
            ];
        }

        return response()->json($data, $data['code']);



    }

    public function login(Request $request)
    {
        echo "Loginnn Ok";
        die();
    }
}
