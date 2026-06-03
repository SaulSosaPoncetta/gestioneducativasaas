<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Services\AuthService;
use App\Models\Usuario;
use Mockery\MockInterface;

class LoginTest extends TestCase
{
    public function test_login_valida_y_redirecciona_correctamente()
{
    $this->mock(AuthService::class, function (MockInterface $mock) {
        $mock->shouldReceive('login')
             ->once()
             ->withAnyArgs()
             ->andReturn(true);
    });

    $response = $this->post('/login', [
        'identificador' => '12345678',
        'password' => '123456',
    ]);

     $response->assertRedirect(route('dashboard'));
}

public function test_login_falla_si_faltan_campos()
{
    // Sin withoutExceptionHandling() — lo eliminamos
    $response = $this->post('/login', []);
    $response->assertSessionHasErrors(['identificador', 'password']);
}

 

}
