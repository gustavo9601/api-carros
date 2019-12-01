<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\JwtAuth;
use App\Car;

class CarController extends Controller
{
    public function index(Request $request)
    {

        //con el load('modelo') dependiendo de la relacion
        //traera la informacion del usuario con el id en la cabla cars
        $cars = Car::all()->load('user');

        return response()->json([
            'status' => 'success',
            'cars' => $cars
        ], 200);

    }


    //Mostrar por id el detalle
    public function show($id)
    {
        $car = Car::find($id);
        if(is_object($car)){
            $car = Car::find($id)->load('user');
            return response()->json([
                'status' => 'success',
                'car' => $car
            ], 200);
        }else{
            return response()->json([
                'status' => 'error',
                'message' => 'Carro no encontrado'
            ], 200);
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
            if ($validacionData->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validacionData->errors()
                ], 200);
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
                'code' => 200,
                'message' => 'Login Incorrecto'
            ];
        }

        return response()->json($result);
    }


    //Actualizar carro
    public function update($id, Request $request)
    {


        $validateRequest = $this->verificarPeticion($request);
        if ($validateRequest) {

            //Recoger parametros post
            $json = $request->input('json', null);
            $params = json_decode($json);
            $params_array = json_decode($json, true);

            //Validar datos
            $rules = [
                'title' => 'required',
                'description' => 'required',
                'price' => 'required',
                'status' => 'required'
            ];

            //Al validator::make le pasamos un arreglo normal y las reglas que concuerden con los indices
            $validacionData = \Validator::make($params_array, $rules);
            if ($validacionData->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validacionData->errors()
                ], 200);
            }

            //Actualizar registro
            //Busca por id y actualizamos de una vez
            //le pasamos un arreglo normal con el mismo nombre de los indices a los atributos en la tabla
            $car = Car::where('id', $id)->update($params_array);

            $result = [
                'car' => $params,
                'status' => 'success',
                'code' => 200,
                'message' => 'Se actualizo correctamente'
            ];

        } else {
            $result = [
                'status' => 'error',
                'code' => 200,
                'message' => 'Login Incorrecto'
            ];
        }

        return response()->json($result);

    }

    //Eliminar carro
    public function destroy($id, Request $request)
    {

        $validateRequest = $this->verificarPeticion($request);
        if ($validateRequest) {
            //Comprobar el registro
            $car = Car::find($id);

            //Borrarlos
            $car->delete();

            //Devolverlo
            $data = [
                'car' => $car,
                'status' => 'success',
                'code' => 200,
                'message' => 'Carro eliminado correctamente'
            ];

        } else {
            $data = [
                'status' => 'error',
                'message' => 'login incorrecto !!!',
                'code' => 200
            ];
        }

        return response()->json($data, $data['code']);
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
