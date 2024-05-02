<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;



class FileController extends Controller
{
    
    public function index() {
        return view('index');
    }

    public function uploadLargeFiles(Request $request) {
        $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));

        if (!$receiver->isUploaded()) {
            // file not uploaded
        }

        $fileReceived = $receiver->receive(); // receive file
        if ($fileReceived->isFinished()) { // file uploading is complete / all chunks are uploaded
            $file = $fileReceived->getFile(); // get file
            $extension = $file->getClientOriginalExtension();
            $fileName = str_replace('.'.$extension, '', $file->getClientOriginalName()); //file name without extenstion
            $fileName .= '_' . md5(time()) . '.' . $extension; // a unique file name

            // $disk = Storage::disk(config('filesystems.default'));
            // $disk = Storage::disk('local');
            // $path = $disk->put('videos', $file, $fileName);
            // $path = $disk->putFileAs('videos', $file, $fileName);

            $file->move('largefiles',$fileName);
            $path = 'largefiles/'. $fileName ;
            
            if(file_exists($file->getPathname())){
                // delete chunked file
                unlink($file->getPathname());
            }
            return [
                // 'path' => asset('storage/' . $path),
                'path' => asset($path),
                'extension'=>$extension,
                'filename' => $fileName
            ];
        }

        // otherwise return percentage informatoin
        $handler = $fileReceived->handler();
        return [
            'done' => $handler->getPercentageDone(),
            'status' => true
        ];
    }
}
