<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Rol;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Mockery;

class LoginPasswordResetTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
        Rol::create(['descripcion' => 'admin']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_envia_reset_link_con_email_valido(): void
    {
        $usuario = Usuario::factory()->create(['email' => 'test@example.com']);

        $this->mock(AuthService::class, function ($mock) {
            $mock->shouldReceive('sendResetLink')
                ->once()
                ->andReturn(__('passwords.sent'));
        });

        $response = $this->post('/password/reset', ['email' => 'test@example.com']);

        $response->assertRedirect();
        $response->assertSessionHas('status', __('passwords.sent'));
    }

    public function test_falla_si_email_no_existe_en_db(): void
    {
        $response = $this->post('/password/reset', ['email' => 'noexiste@example.com']);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_falla_si_email_tiene_formato_invalido(): void
    {
        $response = $this->post('/password/reset', ['email' => 'esto-no-es-un-email']);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_falla_si_email_esta_vacio(): void
    {
        $response = $this->post('/password/reset', []);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_falla_si_el_servicio_lanza_excepcion(): void
    {
        $usuario = Usuario::factory()->create(['email' => 'test@example.com']);

        $this->mock(AuthService::class, function ($mock) {
            $mock->shouldReceive('sendResetLink')
                ->once()
                ->andThrow(\Illuminate\Validation\ValidationException::withMessages([
                    'email' => [__('passwords.throttled')],
                ]));
        });

        $response = $this->post('/password/reset', ['email' => 'test@example.com']);

        $response->assertSessionHasErrors(['email']);
    }
}