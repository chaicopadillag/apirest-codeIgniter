<?php
namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table         = 'users';
    protected $allowedFields = ['name', 'email', 'apellidos', 'id_cliente', 'secret_cliente', 'password', 'token'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
