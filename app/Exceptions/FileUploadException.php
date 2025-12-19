<?php

namespace App\Exceptions;

use Exception;

class FileUploadException extends Exception
{
    private string $errorMessage;
    private int $errorCode;

    public function __construct(
        string $message = "File upload gagal",
        int $code = 422,
        ?Exception $previous = null
    ) {
        $this->errorMessage = $message;
        $this->errorCode = $code;
        parent::__construct($message, $code, $previous);
    }

    public function render()
    {
        return response()->json([
            'success' => false,
            'message' => $this->errorMessage,
            'error_code' => 'FILE_UPLOAD_ERROR',
        ], $this->errorCode);
    }
}
