<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Testing\File;
use Illuminate\Http\Request;
use App\Models\TemporaryFile;
use Tests\TestCase;

class UploadFileTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithDatabase;

    /** @test */
    public function it_deletes_temporary_files_that_are_older_than_1_hour()
    {
        Storage::fake(env('PUBLIC_DISK_NAME'));

        $old_test_folder = Str::random(15);
        // Create a temporary file that is older than 1 hour
        $old_file = TemporaryFile::factory()->create([
            'id' => 1,
            'created_at' => now()->subHours(2),
            'folder' => $old_test_folder,
        ]);

        // Upload the file to temporary location
        Storage::disk(env('PUBLIC_DISK_NAME'))->putFileAs(
            'uploads/profile-image/tmp/'.$old_test_folder.'/'.$old_file->filename,
            storage_path('app/public/test-files/test-file.txt'),
            'test-file.txt'
        );

        // Create a temporary file that is less than 1 hour old
        $recent_file = TemporaryFile::factory()->create([
            'id' => 2,
            'created_at' => now()->subSeconds(30),
            'folder' => 'test_folder',
        ]);

        // Upload the file to temporary location
        Storage::disk(env('PUBLIC_DISK_NAME'))->putFileAs(
            'uploads/profile-image/tmp/test_folder/'.$recent_file->filename,
            storage_path('app/public/test-files/test-file.txt'),
            'test-file.txt'
        );

        // Call the clearTemporaryFiles method
        $this->artisan('clear:tempfiles');

        // Assert that the recent file was not deleted
        $this->assertDatabaseHas('temporary_files', ['id' => $recent_file->id]);

        // Assert that the old file was deleted
        $this->assertDatabaseMissing('temporary_files', ['folder' => $old_test_folder]);

        // Assert that the directory for the recent file still exists
        Storage::disk(env('PUBLIC_DISK_NAME'))->assertExists('uploads/profile-image/tmp/test_folder');

        // Assert that the directory for the old file was deleted
        Storage::disk(env('PUBLIC_DISK_NAME'))->assertMissing('uploads/profile-image/tmp/'.$old_test_folder);
    }
}
