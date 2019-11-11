<?php


namespace App\Helpers;

//Libreria de Jwt
use Firebase\JWT\JWT;
//Libreria de Bd
use Illuminate\Support\Facades\DB;
use App\User;

class JwtAuth
{

    public $key;

    public function __construct()
    {
        $this->key = 'Contraseña secreta';
    }

    public function signup($email, $password, $getToken = null)
    {
        //Validar si existe el usuario
        $user = User::where([
            "email" => $email,
            "password" => $password
        ])->first();


        $signup = false;

        if (is_object($user)) {
            $signup = true;
        }

        if ($signup) {
            //Generar token y devolverlo
            $token = [
                'sub' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'surname' => $user->surname,
                'iat' => time(),  // iat   inicio del token
                'exp' => time() + (7 * 24 * 60 * 60)   // exp fecha de expiracion del tocken
            ];


            /*
             * Usando la libreria para generar el token
             * (array de datos, una contraseña, Algotimo de encriptacion)
             * */
            $jwt = JWT::encode($token, $this->key, 'HS256');

            //Decodeando el token
            /*
             * (token, clave, en un array el tipo de cifrado)
             * */
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);

            //Si viene el token, se lo devolvemos decodeado
            // en caso contrario el nuevo token generado
            if (is_null($getToken)) {
                return $jwt;
            } else {
                return $decoded;
            }


        } else {
            return [
                'status' => 'error',
                'message' => 'Email o contraseña errados !!!'
            ];
        }

    }


    public function checkToken($jwt, $getIdentity = false)
    {

        $auth = false;
        $decoded = '';
        try {
            //Decodeamos el Token
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);

            //Capturamos si algun valor del jwt esta malo
        } catch (\UnexpectedValueException $e) {
            $auth = false;
        } catch (\DomainException $e) {
            $auth = false;
        }

        if (is_object($decoded) && isset($decoded->sub)) {
            $auth = true;
        } else {
            $auth = false;
        }

        if ($getIdentity) {
            return $decoded;
        } else {
            return $auth;
        }


    }

}

