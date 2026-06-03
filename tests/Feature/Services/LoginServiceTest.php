<?php
namespace Tests\Feature\Services; // O Unit, pero usa RefreshDatabase

use Tests\TestCase;
use App\Models\Usuario;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use App\Models\Rol; 

class LoginServiceTest extends TestCase
{
    use RefreshDatabase; // Importante para limpiar la BD

    public function test_servicio_autentica_usuario_existente()
{
    // 1. Arrange: primero el Rol, luego el Usuario
    $rol = Rol::create(['nombre' => 'admin', 'descripcion' => 'Administrador']); // ajustá los campos a tu migración de roles

    $usuario = Usuario::factory()->create([
        'dni'      => '40555666',
        'password' => Hash::make('123456'),
        'rol_id'   => $rol->id,
    ]);

    $service = new AuthService();

    // 2. Act
    $service->login([
        'identificador' => '40555666',
        'password'      => '123456'
    ], '127.0.0.1');

    // 3. Assert
    $this->assertAuthenticatedAs($usuario);
}
}
