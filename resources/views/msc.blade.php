<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function uploadChunk(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $chunkNumber = $request->input('chunk');
            $chunkSize = $request->input('chunkSize');
            $totalChunks = $request->input('totalChunks');
            $chunkFolder = 'chunked_files/';

            // Ensure the chunk folder exists
            if (!Storage::exists($chunkFolder)) {
                Storage::makeDirectory($chunkFolder);
            }

            // Store the chunk
            $file->storeAs($chunkFolder, 'chunk_' . $chunkNumber);

            // Check if all chunks have been uploaded
            if ($chunkNumber == $totalChunks) {
                // Concatenate all chunks into the final file
                $finalFilePath = 'uploads/' . $file->getClientOriginalName();
                $this->concatenateChunks($chunkFolder, $finalFilePath, $totalChunks);
                // Remove the chunked folder
                Storage::deleteDirectory($chunkFolder);
                return response()->json(['message' => 'File uploaded successfully']);
            } else {
                return response()->json(['message' => 'Chunk uploaded successfully']);
            }
        } else {
            return response()->json(['error' => 'No file uploaded'], 400);
        }
    }

    // Concatenate all chunks into the final file
    private function concatenateChunks($chunkFolder, $finalFilePath, $totalChunks)
    {
        $finalFile = fopen(storage_path('app/' . $finalFilePath), 'wb');
        for ($i = 1; $i <= $totalChunks; $i++) {
            $chunkFile = fopen(storage_path('app/' . $chunkFolder . 'chunk_' . $i), 'rb');
            stream_copy_to_stream($chunkFile, $finalFile);
            fclose($chunkFile);
        }
        fclose($finalFile);
    }
}
