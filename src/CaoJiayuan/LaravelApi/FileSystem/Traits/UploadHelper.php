<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2017/12/4
 * Time: 上午9:44
 */

namespace CaoJiayuan\LaravelApi\FileSystem\Traits;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait UploadHelper
{
    protected $uploadFilePrefix = '';

    protected $uploadedFile = '';

    public function uploadFile(Request $request, $path = 'file', $fileKey = 'file')
    {
        if ($this->isChunkUpload($request)) {
            $result = $this->chunkUpload($request, $path, $fileKey);

            return $result;
        }
        $file = $request->file($fileKey);
        $filename = $file->hashName();
        $p = $file->storePubliclyAs($path, $this->uploadFilePrefix . $filename, [
            'disk' => $this->getUploadDisk()
        ]);

        $this->uploadedFile = $path . DIRECTORY_SEPARATOR . $this->uploadFilePrefix . $filename;
        return [
            'url'      => Storage::disk($this->getUploadDisk())->url($p),
            'path'     => $p,
            'type'     => $file->getClientMimeType(),
            'filename' => $file->getClientOriginalName()
        ];
    }

    public function isChunkUpload(Request $request)
    {
        return $request->header('X-Uploaded-With') == 'ChunkUpload';
    }

    public function chunkUpload(Request $request, $path, $fileKey = 'file')
    {
        list($fileId, $filename, $chunks, $index, $mimetype) = $this->getChunkUploadFileInfo($request, $fileKey);
        $file = $request->file($fileKey);
        $dir = $this->getChunkTempPath($fileId);
        $count = 0;
        $file->move($dir, $filename . '.part.' . $index);
        file_map($dir, function () use (&$count) {
            $count++;
        });

        if ($count < $chunks) {
            throw new HttpException(201, 'Created, part:' . $count);
        }
        $ext = substr($filename, strrpos($filename, '.'));

        $storeFileName = $this->uploadFilePrefix . md5($fileId) . $ext;
        $storagePath = rtrim($path, '/') . DIRECTORY_SEPARATOR . $storeFileName;
        if (Storage::disk($this->getUploadDisk())->exists($storagePath)) { // Using existing file
            $p = $storagePath;
        } else {
            $resultFile = $dir . DIRECTORY_SEPARATOR . $filename;
            $lockFile = $dir . DIRECTORY_SEPARATOR . $filename . '.lock';
            usleep(1000); // Wait a minute !!
            if (file_exists($lockFile)) { // File merging...
                $p = $storagePath;
            } else {
                file_put_contents($lockFile, 'LOCKED', FILE_APPEND);
                $fp = fopen($resultFile, 'w+r');
                for ($i = 1; $i <= $chunks; $i++) {
                    $part = $resultFile . '.part.' . $i;
                    fwrite($fp, file_get_contents($part));
                }
                fclose($fp);
                $uploadFile = new UploadedFile($resultFile, $filename);

                $p = $uploadFile->storePubliclyAs($path, $storeFileName, [
                    'disk' => $this->getUploadDisk()
                ]);
                $this->rmDir($dir);
            }
        }
        $this->uploadedFile = $path . DIRECTORY_SEPARATOR . $storeFileName;

        return [
            'url'      => Storage::disk($this->getUploadDisk())->url($p),
            'path'     => $p,
            'type'     => $mimetype ? $mimetype : $file->getClientMimeType(),
            'filename' => $filename
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getChunkUploadFileInfo(Request $request, $fileKey)
    {
        $fileId = $request->get('file_id'); // 文件id
        $filename = $request->get('filename'); // 文件名
        $chunks = $request->get('chunks'); // 分块数量
        $index = $request->get('chunk_index'); // 当前分块
        $mimetype = $request->get('mime_type'); // Mimetype

        return [$fileId, $filename, $chunks, $index, $mimetype];
    }

    protected function getChunkTempPath($path)
    {
        return $this->getChunkTempDir() . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }

    protected function getChunkTempDir()
    {
        return storage_path('app/public/chunks');
    }

    protected function getUploadDisk()
    {
        return null;
    }

    public function rmDir($dir)
    {
        if (!file_exists($dir)) {
            return;
        }
        @file_map($dir, function ($file) {
            @unlink($file);
        });
        @rmdir($dir);
    }

    public function getExtensionByName($filename)
    {
        $pos = strrpos($filename, '.');

        return $pos ? substr($filename, $pos + 1) : false;
    }
}
