<?php


namespace App\Support;

use App\Models\FileSystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Sdtech\FileUploaderLaravel\Service\FileUploadLaravelService;

class FileManager
{
    private $service;

    public function __construct()
    {
        $this->service = new FileUploadLaravelService();
    }

    public static function createFile($data, $userId)
    {
        FileSystem::create([
            'filename' => $data['file_name'],
            'original_name' => $data['original_name'],
            'type' => $data['file_ext_original'],
            'extension' => $data['file_ext'],
            'size' => $data['size'],
            'path' => $data['path'],
            'full_url' => $data['file_url'],
            'dimensions' => isset($data['dimensions'])
                ? $data['dimensions']['width'] . 'x' . $data['dimensions']['height']
                : null,

            'alt_text' => $data['file_name'],
            'title' => $data['file_name'],
            'description' => $data['file_name'],
            'seo_keywords' => $data['file_name'],
            'seo_title' => $data['file_name'],
            'seo_description' => $data['file_name'],
            'uploaded_by' => $userId,
        ]);
    }

    public static function uploadFilePublic(UploadedFile $file, string $folder = 'uploads', ?string $oldFile = null)
    {
        $self = new self();
        $response = $self->service->uploadImageInPublic($file, $folder, $oldFile);
        $user = Auth::user();
        if ($response ['success'] == false) {
            return $response;
        } else {
            $data = $response['data'];
            self::createFile($data, $user->id);
            return sendResponse(true, __('File successfully uploaded.'), $data);
        }
    }

    public static function uploadFileStorage(UploadedFile $file, string $folder = 'uploads', ?string $oldFile = null)
    {
        $self = new self();
        $response = $self->service->uploadImageInStorage($file, $folder, $oldFile);
        $user = Auth::user();
        if ($response ['success'] == false) {
            return $response;
        } else {
            $data = $response['data'];
            self::createFile($data, $user->id);
            return sendResponse(true, __('File successfully uploaded.'), $data);
        }
    }

    public static function list($request): array
    {
        return DataListManager::list(
            request: $request,
            query: FileSystem::query(),

            searchable: [
                'filename',
                'original_name',
            ],

            filters: [
                'uploaded_by' => [
                    'uploaded_by' => $request->user_id
                ]
            ],

            select: [
            ],
            notIn:isset($request->notIn) ? $request->notIn : [],
        );

    }

    public static function deleteFile(string $id,$type='id') {
        if ($type == 'id') {
            $file = FileSystem::find($id);
        } else {
            $file = FileSystem::where('full_url',$id)->first();
        }

        if ($file) {
            $path = explode('/',$file->path);
            $self = new self();
            $self->service->unlinkFile('storage/'.$path[0],$path[1]);
            $file->delete();
            return sendResponse(true, __('File deleted successfully.'));
        } else {
            return sendResponse(false, __('File not found'));
        }
    }
}
