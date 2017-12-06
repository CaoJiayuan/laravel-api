<?php
/**
 * Created by Cao Jiayuan.
 * Date: 17-1-4
 * Time: 下午2:29
 */

namespace CaoJiayuan\LaravelApi\FileSystem\MimeType;

use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeExtensionGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;

class ExtensionMimeTypeGuesser extends MimeTypeExtensionGuesser implements MimeTypeGuesserInterface
{
    public function guess($path)
    {
        $map = array_flip($this->defaultExtensions);
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return (isset($map[$extension])) ? $map[$extension] : null;
    }
}
