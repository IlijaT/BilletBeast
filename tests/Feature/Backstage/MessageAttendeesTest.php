<?php

namespace Tests\Feature\Backstage;

use App\Concert;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MessageAttendeesTest extends TestCase
{

    use RefreshDatabase;

    /** @test */
    public function a_promoter_can_view_message_form_from_their_own_concert()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $user->id]);
        $concert->publish();

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/messages/new");

        $response->assertStatus(200);
        $response->assertViewIs('backstage.concert-messages.create');
        $this->assertTrue($response->data('concert')->is($concert));
    }

    /** @test */
    public function a_promoter_cannot_view_message_form_from_another_concert()
    {

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id' => factory(User::class)->create()
        ]);
        $concert->publish();

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/messages/new");

        $response->assertStatus(404);
    }

    /** @test */
    public function a_guest_cannot_view_message_form_from_any_concert()
    {

        $concert = factory(Concert::class)->create();
        $concert->publish();

        $response = $this->get("/backstage/concerts/{$concert->id}/messages/new");

        $response->assertRedirect('/login');
    }
}
