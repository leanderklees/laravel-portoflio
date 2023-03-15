<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\TemporaryFile;

class UploadController extends Controller
{
    public function fpUpload(Request $request)
    {
        if($request->hasFile('profile-image')){
            $image = $request->file('profile-image');
            $folder = uniqid('post', true);
            $path = 'uploads/profile-image/tmp/'.$folder;

            $image->store($path, env('PUBLIC_DISK_NAME'));

            TemporaryFile::create([
                'folder' => $folder,
                'filename' => $image->hashName(),
            ]);

            return $folder;
        }
    }

    public function fpDelete(Request $request)
    {
        $tmp_file = TemporaryFile::where( 'folder', request()->getContent() )->first();
        if (is_null($tmp_file))
            return response('');

        $this->deleteTemporaryFile($tmp_file);

        return response('');
    }

    public function clearTemporaryFiles()
    {
        $old_files = TemporaryFile::where('created_at', '<', now()->subHour(1))->get();
        
        if ($old_files->isEmpty())
            return;

        foreach ($old_files as $file) {
            $this->deleteTemporaryFile($file);
        }
    }

    protected function deleteTemporaryFile(TemporaryFile $file)
    {
        Storage::disk(env('PUBLIC_DISK_NAME'))->deleteDirectory('uploads/profile-image/tmp/'.$file->folder);
        $file->delete();
    }
}