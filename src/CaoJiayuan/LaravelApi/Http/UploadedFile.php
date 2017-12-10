<?php
/**
 * Created by PhpStorm.
 * User: caojiayuan
 * Date: 2017/12/10
 * Time: 下午3:42
 */

namespace CaoJiayuan\LaravelApi\Http;


use Illuminate\Http\UploadedFile as BaseUploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class UploadedFile extends BaseUploadedFile
{

    public function move($directory, $name = null)
    {

        $target = $this->getTargetFile($directory, $name);

        if (!@rename($this->getPathname(), $target)) {
            $error = error_get_last();
            throw new FileException(sprintf('Could not move the file "%s" to "%s" (%s)', $this->getPathname(), $target, strip_tags($error['message'])));
        }

        @chmod($target, 0666 & ~umask());

        return $target;
    }
}