<?php
namespace App\Models;

use CodeIgniter\Model;

class ArticuloModel extends Model
{
    protected $table         = 'articulo';
    protected $allowedFields = ['titulo', 'id_categoria', 'descripcion', 'palabras_claves', 'ruta', 'contenido', 'img', 'id_user'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
