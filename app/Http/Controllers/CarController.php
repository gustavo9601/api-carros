<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\JwtAuth;
use App\Car;

class CarController extends Controller
{
    public function index(Request $request)
    {

        //Validacion el request contenga el Authorization
        if ($this->verificarPeticion($request)) {

        } else {

        }

    }

    //Guardar
    public function store(Request $request)
    {

        //Validacion el request contenga el Authorization
        $validateRequest = $this->verificarPeticion($request);
        $result = [];
        if ($validateRequest) {

            $car = new Car();
            //Capturando el usuario decodeado
            $user = $validateRequest['jwtAuth']->checkToken($validateRequest['hash'], true);

            //Decodificando datos enviados por post
            $json = $request->input('json', null);
            $params = json_decode($json);
            $params_array = json_decode($json, true);  //le espeficiamos true para que lo convierta a array


            $rules = [
                'title' => 'required',
                'description' => 'required',
                'price' => 'required',
                'status' => 'required'
            ];


            /*
             * Validacion #1
             *
            //con merge le pasamos un arreglo con los datos a setear, es decir como si desde la peticion inicial hubieramos enviado el params_Array
            $request->merge($params_array);

            try {
                $validacionData = $this->validate($request, $rules);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json([
                    'status' => 'error',
                    'data' => $e->errors()
                ]);
            }
            */

            /*
             * Validacion #2
             * */

            //Al validator::make le pasamos un arreglo normal y las reglas que concuerden con los indices
            $validacionData = \Validator::make($params_array, $rules);
            if($validacionData->fails()){
                return response()->json([
                    'status' => 'error',
                    'errors' => $validacionData->errors()
                ], 400);
            }


            $car->title = $params->title;
            $car->user_id = $user->sub;  //sub ya que el token se decodifica el id en el indice sub
            $car->description = $params->description;
            $car->price = $params->price;
            $car->status = $params->status;

            $car->save();

            $result = [
                'car' => $car,
                'status' => 'success',
                'code' => 200,
                'message' => 'Carro guardado'
            ];

        } else {
            $result = [
                'status' => 'error',
                'code' => 400,
                'message' => 'Login Incorrecto'
            ];
        }

        return response()->json($result);
    }

    public function verificarPeticion(Request $request)
    {

        $jwtAuth = new JwtAuth();
        //Capturamos la cabecera de la peticion
        $hash = $request->header('Authorization', null);
        $chektoken = $jwtAuth->checkToken($hash);
        if ($chektoken) {
            return [
                'jwtAuth' => $jwtAuth,
                'hash' => $hash
            ];
        } else {
            return false;
        }
    }

}
