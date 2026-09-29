<?php

namespace App\Mail;

use App\Models\Mahasiswa;
use App\Models\Pengumuman;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email pengumuman untuk MAHASISWA BERPRESTASI (REVISI 26-09-2026).
 * Dikirim lewat akun Gmail Program Studi (SMTP Gmail di .env) ke email
 * mahasiswa penerima — bukan berdasarkan urutan ranking.
 */
class PengumumanMahasiswaBerprestasi extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Pengumuman $pengumuman,
        public Mahasiswa $mahasiswa,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Pengumuman Prodi TI] '.$this->pengumuman->judul,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.pengumuman');
    }
}
