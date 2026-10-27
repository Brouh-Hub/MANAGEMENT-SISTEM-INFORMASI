<?php

/*
| Pengganti sementara untuk ekstensi PHP "fileinfo" di server yang belum memasangnya.
| Dipakai HANYA kalau kelas finfo tidak ada. Begitu fileinfo asli terpasang, file ini
| tidak melakukan apa-apa (aman dibiarkan, atau hapus barisnya dari bootstrap/app.php).
*/

if (! class_exists('finfo', false)) {
    foreach ([
        'FILEINFO_NONE' => 0, 'FILEINFO_SYMLINK' => 2, 'FILEINFO_MIME_TYPE' => 16,
        'FILEINFO_CONTINUE' => 32, 'FILEINFO_RAW' => 256, 'FILEINFO_MIME_ENCODING' => 1024,
        'FILEINFO_MIME' => 1040, 'FILEINFO_EXTENSION' => 2097152,
    ] as $nama => $nilai) {
        if (! defined($nama)) {
            define($nama, $nilai);
        }
    }

    class finfo
    {
        private int $flags;

        private const EKSTENSI = [
            'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif',
            'webp' => 'image/webp', 'svg' => 'image/svg+xml', 'bmp' => 'image/bmp', 'ico' => 'image/x-icon',
            'pdf' => 'application/pdf', 'txt' => 'text/plain', 'csv' => 'text/csv', 'json' => 'application/json',
            'html' => 'text/html', 'css' => 'text/css', 'js' => 'application/javascript', 'xml' => 'application/xml',
            'zip' => 'application/zip',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ];

        public function __construct(int $flags = FILEINFO_NONE, ?string $magic_database = null)
        {
            $this->flags = $flags;
        }

        public function set_flags(int $flags): bool
        {
            $this->flags = $flags;

            return true;
        }

        public function buffer(string $string, int $flags = FILEINFO_NONE, $context = null): string|false
        {
            return $this->format($this->dariIsi($string) ?? 'application/octet-stream');
        }

        public function file(string $filename, int $flags = FILEINFO_NONE, $context = null): string|false
        {
            if (! is_file($filename) || ! is_readable($filename)) {
                return false;
            }
            $isi = (string) @file_get_contents($filename, false, null, 0, 4096);
            $tipe = $this->dariIsi($isi)
                ?? self::EKSTENSI[strtolower(pathinfo($filename, PATHINFO_EXTENSION))]
                ?? ($isi === '' ? 'application/x-empty' : (preg_match('//u', $isi) && ! str_contains($isi, "\0") ? 'text/plain' : 'application/octet-stream'));

            return $this->format($tipe);
        }

        private function dariIsi(string $s): ?string
        {
            return match (true) {
                str_starts_with($s, "\x89PNG\r\n\x1a\n") => 'image/png',
                str_starts_with($s, "\xFF\xD8\xFF") => 'image/jpeg',
                str_starts_with($s, 'GIF87a'), str_starts_with($s, 'GIF89a') => 'image/gif',
                str_starts_with($s, 'BM') && strlen($s) > 14 => 'image/bmp',
                str_starts_with($s, 'RIFF') && substr($s, 8, 4) === 'WEBP' => 'image/webp',
                str_starts_with($s, '%PDF-') => 'application/pdf',
                str_starts_with($s, "PK\x03\x04") => 'application/zip',
                default => null,
            };
        }

        private function format(string $tipe): string
        {
            if (($this->flags & FILEINFO_MIME_ENCODING) && ! ($this->flags & 16)) {
                return 'binary';
            }

            return $this->flags === FILEINFO_MIME ? $tipe . '; charset=binary' : $tipe;
        }
    }

    if (! function_exists('finfo_open')) {
        function finfo_open(int $flags = FILEINFO_NONE, ?string $magic_database = null) { return new finfo($flags, $magic_database); }
        function finfo_close($finfo): bool { return true; }
        function finfo_file($finfo, string $filename, int $flags = FILEINFO_NONE, $context = null) { return $finfo->file($filename); }
        function finfo_buffer($finfo, string $string, int $flags = FILEINFO_NONE, $context = null) { return $finfo->buffer($string); }
    }
}

/* mime_content_type() juga bagian dari ekstensi fileinfo; dipakai oleh RenderSurat. */
if (! function_exists('mime_content_type')) {
    function mime_content_type($filename)
    {
        return (new finfo(FILEINFO_MIME_TYPE))->file($filename);
    }
}
