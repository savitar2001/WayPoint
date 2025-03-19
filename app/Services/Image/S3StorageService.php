<?php

namespace App\Services\Image;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
 
class S3StorageService {
    private $response;

    public function  __construct() {
        $this->response = [
            'success' => false,
            'error' => '',
            'data' => []
        ];
    }

    //檢查文件是否符合MIME類型及大小
    function validateFile ($base64Image) {
        if (preg_match('/data:image\/(.*?);base64,/', $base64Image, $matches)) {
            $imageType = $matches[1];
            $base64Image = preg_replace('/^data:image\/(.*?);base64,/', '', $base64Image);
        }
        
        $imageDecode = base64_decode($base64Image);
      
        if (strlen($imageDecode) > config('filesystems.max_file_size')) {
            $this->response['error'] = '文件大小超過限制';
            return $this->response;
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $imageDecode);
        finfo_close($finfo);
        
        if (!in_array($mimeType, config('filesystems.allow_types'))) {
            $this->response['error'] ='不支持的文件類型';
            return $this->response;
        }

        $this->response['success'] = true;
        $this->response['data'] = [
            'imageDecode' => $imageDecode,
            'mimeType' => $mimeType,
            'extension' => $imageType
        ];
        return $this->response;
    }

    // 上傳圖片
    public function uploadBase64Image($base64Image, $folder) {
        $validation = $this->validateFile($base64Image);
        if ($validation['success'] !== true) {
            return $validation;
        }
        $imageData = $validation['data'];
        $filename = uniqid() . '_' . time() . '.' . $imageData['extension'];
        $path = "{$folder}/{$filename}";
        try {
            $uploadSuccess = Storage::disk('s3')->put($path, $imageData['imageDecode'], 'public');
            
            if (!$uploadSuccess || !Storage::disk('s3')->exists($path)) {
                throw new \Exception('圖片上傳失敗');
            }
    
            return [
                'success' => true,
                'message' => '圖片上傳成功',
                'data' => [
                    'url' => Storage::disk('s3')->url($path),
                    'filename' => $filename,
                    'path' => $path
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }

    // 取得S3預簽名URL
    public function generatePresignedUrl($folder, $filename, $expires = '+1 hour') {
        $path = "{$folder}/{$filename}";
        try {
            $presignedUrl = Storage::disk('s3')->temporaryUrl($path, now()->addHour());
            
            if (!$presignedUrl) {
                throw new \Exception('獲取url失敗');
            }
    
            return [
                'success' => true,
                'message' => '獲取url成功',
                'data' => [
                    'url' => $presignedUrl
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }      
    }

    //刪除s3上的圖片
    public function deleteImage($folder, $filename) {
        $path = "{$folder}/{$filename}";
        try {
            $deleteSuccess = Storage::disk('s3')->delete($path);
            
            if (!$deleteSuccess || Storage::disk('s3')->exists($path)) {
                throw new \Exception('刪除圖片失敗');
            }
    
            return [
                'success' => true,
                'message' => '刪除圖片成功',
                'data' => []
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }      
    }
}