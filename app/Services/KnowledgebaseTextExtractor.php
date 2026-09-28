<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Smalot\PdfParser\Parser;

class KnowledgebaseTextExtractor
{
    /**
     * Pull plain text from an uploaded knowledgebase file.
     */
    public function extract(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'pdf') {
            $pdf = (new Parser)->parseFile($file->getRealPath());

            return trim($pdf->getText());
        }

        return trim((string) file_get_contents($file->getRealPath()));
    }
}
