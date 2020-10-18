<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use CodeIgniter\Controller;

class Usuarios extends Controller
{
    public function index()
    {
        $json = json_encode([
            'mensaje' => 'no encontrado',
        ], true);

        return $json;
    }

    public function create()
    {
        $request    = \Config\Services::request();
        $validacion = \Config\Services::validation();
        // $encrypter  = \Config\Services::encrypter();

        $validacion->setRules([
            'nombre'    => 'required|string|min_length[3]|max_length[255]',
            'apellidos' => 'required|string|min_length[3]|max_length[255]',
            'correo'    => 'required|valid_email|is_unique[users.email]',
        ]);

        $validacion->withRequest($this->request)->run();

        if ($validacion->getErrors()) {

            $json = json_encode([
                'estado'  => 202,
                'mensaje' => 'Error en los campos',
                'errores' => $validacion->getErrors(),
            ], true);

        } else {

            $datos = [
                'name'      => $request->getVar('nombre'),
                'apellidos' => $request->getVar('apellidos'),
                'email'     => $request->getVar('correo'),

            ];
            $datos['id_cliente'] = str_replace('$', 'R', crypt($datos['nombre'] . $datos['apellidos'] . $datos['correo'], '$2a$07$kjk8uh8258fdskEOIEN2568ffsEH$'));

            $datos['secret_cliente'] = str_replace('$', 'T', crypt($datos['correo'] . $datos['nombre'] . $datos['apellido'], '$2a$07$kjk8uh8258fdskEOIEN2568ffsEH$'));

            $datos['token']    = 'Basic ' . base64_encode($datos['id_cliente'] . ':' . $datos['secret_cliente']);
            $datos['password'] = 'Basic ' . base64_encode($datos['id_cliente'] . ':' . $datos['secret_cliente']);

            $user = new UsuarioModel();
            if ($user->save($datos) > 0) {
                $json = json_encode([
                    'estado'       => 202,
                    'mensaje'      => 'Registro éxitoso, guarde sus credenciales',
                    'credenciales' => [
                        'id_cliente'     => $datos['id_cliente'],
                        'secret_cliente' => $datos['secret_cliente'],
                    ],
                ], true);
            } else {
                $json = json_encode([
                    'estado'  => 202,
                    'mensaje' => 'Error al registrarse, verifique los campos e intente de nuevo',
                ], true);
            }

        }
        // $plainText = 'This is a plain-text message!';
        // $ciphertext = $encrypter->encrypt($plainText);

        // $datos['hash'] = $ciphertext;
        return $json;

    }
}
