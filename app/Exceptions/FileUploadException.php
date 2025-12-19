<?php

namespace App\Exceptions;

use Exception;

class FileUploadException extends Exception
{
    public function __construct(
        string $message = "File upload gagal",
        int $code = 422,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function render()
    {
        return response()->json([
            'success' => false,
            'message' => $this->message,
            'error_code' => 'FILE_UPLOAD_ERROR',
        ], $this->code);
    }
}
