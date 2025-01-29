<?php

namespace Tests\Feature\API;

use App\Models\Image;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ImageControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp(); // Call parent setup

        // Create a user and authenticate them using Sanctum
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_user_can_list_images()
    {
        // Create test images in the database
        Image::factory()->count(3)->create();

        // Make a GET request to the index route
        $response = $this->getJson('/api/image');

        // Assert response is successful and has the expected structure
        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['id', 'url', 'label'],
            ]);
    }

    public function test_user_can_upload_image()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test-image.jpg');

        $response = $this->postJson('/api/image', [
            'image' => $file,
            'label' => 'Test Image',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Image uploaded successfully']);

        // Assert image was stored
        Storage::disk('public')->assertExists('images/'.$file->hashName());

        // Assert image exists in database
        $this->assertDatabaseHas('images', [
            'path' => 'images/'.$file->hashName(),
            'label' => 'Test Image',
        ]);
    }

    public function test_user_cannot_upload_invalid_file()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('test.txt', 10); // Invalid file type

        $response = $this->postJson('/api/image', [
            'image' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_user_can_delete_image()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('test-image.jpg');
        $path = $file->store('images', 'public');

        $image = Image::create(['path' => $path, 'label' => 'To be deleted']);

        $response = $this->deleteJson("/api/image/{$image->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Image deleted successfully']);

        // Assert image was deleted from storage
        Storage::disk('public')->assertMissing($path);

        // Assert image was deleted from the database
        $this->assertDatabaseMissing('images', ['id' => $image->id]);
    }
}
