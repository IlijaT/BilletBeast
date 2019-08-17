<?php

namespace Tests\Feature\Backstage;

use App\Concert;
use App\User;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EditConcertTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function promoters_can_view_edit_form_for_their_own_unpublished_concerts()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $user->id]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/ediit");

        $response->assertStatus(200);
        $this->assertTrue($response->data('concert')->is($concert));
    }

    /** @test */
    public function promoters_can_not_view_edit_form_for_their_own_published_concerts()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('published')->create(['user_id' => $user->id]);

        $this->assertTrue($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/ediit");

        // 403 http response forbiden
        $response->assertStatus(403);
    }

    /** @test */
    public function promoters_can_not_view_edit_form_for_other_concerts()
    {
        $user = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        $concert = factory(Concert::class)->states('published')->create(['user_id' => $user2->id]);


        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/ediit");

        // 404 http response - not found 
        // to not leak information wether concert exist or not
        $response->assertStatus(404);
    }

    /** @test */
    public function promoters_viw_404_when_trying_to_se_edit_form_of_concert_that_does_not_exist()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get("/backstage/concerts/999/ediit");

        // 404 http response - not found 
        $response->assertStatus(404);
    }

    /** @test */
    public function guest_are_ask_to_login_when_attempting_to_see_any_edit_concert_form()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('published')->create(['user_id' => $user->id]);

        $response = $this->get("/backstage/$concert->id/ediit");

        // 302 http response - redirect 
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function guest_are_ask_to_login_when_attempting_to_see_edit_form_of_concert_that_does_not_exist()
    {

        $response = $this->get("/backstage/999/ediit");

        // 302 http response - redirect 
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function promoters_can_edit_their_own_unpublished_concerts()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'title' => 'Old Title',
            'subtitle' => 'Old Subtitle',
            'additional_information' => 'Old additional information',
            'date'  => Carbon::parse('2020-01-01 8:00pm'),
            'venue' => 'Old Venue',
            'venue_address'  => 'Old Venue Address',
            'city'  => 'Old City',
            'state' => 'Old State',
            'zip' => '00000',
            'ticket_price'  => 2000,
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->ascingAs($user)->patch("/backstage/concerts/{$concert->id}", [
            'title' => 'New Title',
            'subtitle' => 'New Subtitle',
            'additional_information' => 'New additional information',
            'date'  => '2020-12-12',
            'time'  => '8:00pm',
            'venue' => 'New Venue',
            'venue_address'  => 'New Venue Address',
            'city'  => 'New City',
            'state' => 'New State',
            'zip' => '99999',
            'ticket_price'  => '72.50',
        ]);

        $response->assertRedirect('backstage/concerts');

        tap($concert->fresh(), function ($concert) {
            $this->assertEquals('New Title', $concert->title);
            $this->assertEquals('New Subtitle', $concert->subtitle);
            $this->assertEquals('New additional information', $concert->additional_information);
            $this->assertEquals(Carbon::parse('2020-12-12 8:00pm'), $concert->date);
            $this->assertEquals('New Venue', $concert->venue);
            $this->assertEquals('New Venue Address', $concert->venue_address);
            $this->assertEquals('New City', $concert->city);
            $this->assertEquals('New State', $concert->state);
            $this->assertEquals('99999', $concert->zip);
            $this->assertEquals(7250, $concert->ticket_price);
        });
    }
}
