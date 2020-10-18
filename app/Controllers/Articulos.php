<?php

namespace App\Controllers;

use App\Models\ArticuloModel;
use App\Models\UsuarioModel;
use CodeIgniter\Controller;

class Articulos extends Controller
{
    public function index()
    {
        $request = \Config\Services::request();
        $headers = $request->getHeaders();

        $db    = \Config\Database::connect();
        $pager = \Config\Services::pager();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {

            $token = str_replace('Authorization: ', '', $request->getHeader('Authorization'));

            $user_model = new UsuarioModel();
            $user       = $user_model->where('token', $token)->first();

            if ($user != null) {

                $article_model = new ArticuloModel();
                // $articles      = $article_model->findAll();
                if (isset($_GET['page']) && is_numeric($_GET['page'])) {
                    $cantidad = 10;
                    $pagina   = ($_GET['page'] - 1) * $cantidad;

                    $query = $db->query("SELECT a.id,a.titulo, a.descripcion, a.palabras_claves, a.ruta, a.contenido, a.img, u.name, u.apellidos FROM articulo AS a INNER JOIN users as u ON u.id=a.id_user LIMIT $pagina,$cantidad");
                } else {

                    $query = $db->query("SELECT a.id,a.titulo, a.descripcion, a.palabras_claves, a.ruta, a.contenido, a.img, u.name, u.apellidos FROM articulo AS a INNER JOIN users as u ON u.id=a.id_user");
                }

                $articles = $query->getResult();

                if ($articles != null) {

                    $json = json_encode([
                        'estado'          => 202,
                        'total_registros' => count($articles),
                        'articulos'       => $articles,
                    ], true);
                } else {

                    $json = json_encode([
                        'estado'          => 202,
                        'total_registros' => 0,
                        'mensaje'         => 'No hay articulos para mostrar',
                    ], true);
                }
            } else {
                $json = json_encode([
                    'estado'          => 203,
                    'total_registros' => 0,
                    'mensaje'         => 'Token incorrecto, intente de nuevo',
                ], true);
            }
        } else {
            $json = json_encode([
                'estado'          => 203,
                'total_registros' => 0,
                'mensaje'         => 'Necesita sus credenciales para seguir',
            ], true);
        }

        return $json;
    }

    public function create()
    {
        $request    = \Config\Services::request();
        $headers    = $request->getHeaders();
        $validacion = \Config\Services::validation();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $token = str_replace('Authorization: ', '', $request->getHeader('Authorization'));

            $user_model = new UsuarioModel();
            $user       = $user_model->where('token', $token)->first();
            if ($user != null) {

                $validacion->setRules([
                    'titulo'          => 'required|string|min_length[3]|max_length[255]|is_unique[articulo.titulo]',
                    'descripcion'     => 'required|string|min_length[3]|max_length[255]',
                    'palabras_claves' => 'required|string',
                    'ruta'            => 'required|string|is_unique[articulo.ruta]',
                    'contenido'       => 'required|string|min_length[3]|max_length[255]',
                    'img'             => 'required|string|min_length[3]|max_length[255]',
                ]);

                $validacion->withRequest($this->request)->run();

                if ($validacion->getErrors()) {

                    $json = json_encode([
                        'estado'  => 202,
                        'mensaje' => 'Error en los campos',
                        'errores' => $validacion->getErrors(),
                    ], true);
                } else {

                    $datos = array(
                        'id_categoria'    => 2,
                        'titulo'          => $request->getVar('titulo') ?? '',
                        'descripcion'     => $request->getVar('descripcion') ?? '',
                        'palabras_claves' => json_encode(explode(',', $request->getVar('palabras_claves')), true),
                        'ruta'            => $request->getVar('ruta'),
                        'contenido'       => $request->getVar('contenido') ?? '',
                        'img'             => $request->getVar('img') ?? '',
                        'id_user'         => $user['id'],
                    );

                    $article = new ArticuloModel();

                    if ($article->save($datos)) {
                        $json = json_encode([
                            'estado'          => 202,
                            'total_registros' => 0,
                            'mensaje'         => 'Articulo creado exitosamente',
                        ], true);
                    } else {

                        $json = json_encode([
                            'estado'          => 202,
                            'total_registros' => 0,
                            'mensaje'         => 'Error al crear un articulo',
                        ], true);
                    }
                }
            } else {
                $json = json_encode([
                    'estado'          => 203,
                    'total_registros' => 0,
                    'mensaje'         => 'Token incorrecto, intente de nuevo',
                ], true);
            }
        } else {
            $json = json_encode([
                'estado'          => 203,
                'total_registros' => 0,
                'mensaje'         => 'Necesita sus credenciales para seguir',
            ], true);
        }
        return $json;
    }

    public function show($id)
    {
        $request    = \Config\Services::request();
        $headers    = $request->getHeaders();
        $validacion = \Config\Services::validation();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $token = str_replace('Authorization: ', '', $request->getHeader('Authorization'));

            $user_model = new UsuarioModel();
            $user       = $user_model->where('token', $token)->first();
            if ($user != null) {
                $article_model = new ArticuloModel();
                $article       = $article_model->find($id);

                if ($article != null) {

                    $json = json_encode([
                        'estado'    => 202,
                        'articulos' => $article,
                    ], true);
                } else {

                    $json = json_encode([
                        'estado'  => 202,
                        'mensaje' => 'No hay articulo para mostrar',
                    ], true);
                }
            } else {

                $json = json_encode([
                    'estado'          => 203,
                    'total_registros' => 0,
                    'mensaje'         => 'Token incorrecto, intente de nuevo',
                ], true);
            }
        } else {
            $json = json_encode([
                'estado'          => 203,
                'total_registros' => 0,
                'mensaje'         => 'Necesita sus credenciales para seguir',
            ], true);
        }

        return $json;
    }
    public function update($id)
    {
        $request    = \Config\Services::request();
        $headers    = $request->getHeaders();
        $validacion = \Config\Services::validation();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $token = str_replace('Authorization: ', '', $request->getHeader('Authorization'));

            $user_model = new UsuarioModel();
            $user       = $user_model->where('token', $token)->first();
            if ($user != null) {
                $article_model = new ArticuloModel();
                $article       = $article_model->where('id', $id)->first();
                if ($article != null) {

                    if ($article['id_user'] === $user['id']) {
                        $validacion->setRules([
                            'titulo'          => 'required|string|min_length[3]|max_length[255]',
                            'descripcion'     => 'required|string|min_length[3]|max_length[255]',
                            'palabras_claves' => 'required|string',
                            'ruta'            => 'required|string',
                            'contenido'       => 'required|string|min_length[3]|max_length[255]',
                            'img'             => 'required|string|min_length[3]|max_length[255]',
                        ]);

                        $validacion->withRequest($this->request)->run();

                        if ($validacion->getErrors()) {

                            $json = json_encode([
                                'estado'  => 202,
                                'mensaje' => 'Error en los campos',
                                'errores' => $validacion->getErrors(),
                            ], true);
                        } else {

                            $datos_request = $this->request->getRawInput();
                            $datos         = [
                                'id_categoria'    => 2,
                                'titulo'          => $datos_request['titulo'],
                                'descripcion'     => $datos_request['descripcion'],
                                'palabras_claves' => json_encode(explode(',', $datos_request['palabras_claves']), true),
                                'ruta'            => $datos_request['ruta'],
                                'contenido'       => $datos_request['contenido'],
                                'img'             => $datos_request['img'],
                            ];

                            if ($article_model->update($id, $datos) > 0) {
                                $json = json_encode([
                                    'estado'          => 202,
                                    'total_registros' => 0,
                                    'mensaje'         => 'Articulo actualizado exitosamente',
                                ], true);
                            } else {

                                $json = json_encode([
                                    'estado'          => 202,
                                    'total_registros' => 0,
                                    'mensaje'         => 'Error al actualizar el articulo',
                                ], true);
                            }
                        }
                    } else {
                        $json = json_encode([
                            'estado'  => 202,
                            'mensaje' => 'No tiene permiso para editar este árticulo',
                        ], true);
                    }
                } else {

                    $json = json_encode([
                        'estado'  => 202,
                        'mensaje' => 'El articulo que quiere editar no existe en la Base de Datos',
                    ], true);
                }
            } else {

                $json = json_encode([
                    'estado'          => 203,
                    'total_registros' => 0,
                    'mensaje'         => 'Token incorrecto, intente de nuevo',
                ], true);
            }
        } else {
            $json = json_encode([
                'estado'          => 203,
                'total_registros' => 0,
                'mensaje'         => 'Necesita sus credenciales para seguir',
            ], true);
        }

        return $json;
    }

    public function delete($id)
    {
        $request    = \Config\Services::request();
        $headers    = $request->getHeaders();
        $validacion = \Config\Services::validation();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $token = str_replace('Authorization: ', '', $request->getHeader('Authorization'));

            $user_model = new UsuarioModel();
            $user       = $user_model->where('token', $token)->first();
            if ($user != null) {
                $article_model = new ArticuloModel();
                $article       = $article_model->where('id', $id)->first();
                if ($article != null) {
                    if ($article['id_user'] === $user['id']) {

                        if ($article_model->delete($id) > 0) {
                            $json = json_encode([
                                'estado'          => 202,
                                'total_registros' => 0,
                                'mensaje'         => 'Articulo se ha eliminado exitosamente',
                            ], true);
                        } else {

                            $json = json_encode([
                                'estado'          => 202,
                                'total_registros' => 0,
                                'mensaje'         => 'Error al eliminar el articulo',
                            ], true);
                        }
                    } else {

                        $json = json_encode([
                            'estado'  => 202,
                            'mensaje' => 'No tiene permiso para eliminar este árticulo',
                        ], true);
                    }
                } else {

                    $json = json_encode([
                        'estado'  => 202,
                        'mensaje' => 'El articulo que quiere eliminar no existe en la Base de Datos',
                    ], true);
                }
            } else {

                $json = json_encode([
                    'estado'          => 203,
                    'total_registros' => 0,
                    'mensaje'         => 'Token incorrecto, intente de nuevo',
                ], true);
            }
        } else {
            $json = json_encode([
                'estado'          => 203,
                'total_registros' => 0,
                'mensaje'         => 'Necesita sus credenciales para seguir',
            ], true);
        }

        return $json;
    }
}
