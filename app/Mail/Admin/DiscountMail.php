<?php

namespace App\Mail\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DiscountMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private string $pdfData,
        private string $pdfFilename
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Laporan Efektivitas Diskon - ' . now()->translatedFormat('d F Y'),
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: '
                <div style="font-family: Arial, sans-serif; color: #1e293b; max-width: 600px; margin: 0 auto;">
                    <p style="font-size: 14px; margin-bottom: 10px;">Kepada Yth. <strong>Owner</strong>,</p>
                    
                    <p style="font-size: 14px; line-height: 1.6; margin-bottom: 20px;">
                        Terlampir adalah <strong>Laporan Efektivitas Diskon</strong> untuk periode <strong>30 hari terakhir</strong>. Laporan ini berisi ringkasan performa dan tingkat ROI dari setiap diskon yang aktif.
                    </p>
                    
                    <p style="font-size: 14px; line-height: 1.6; margin-bottom: 20px;">
                        Laporan ini dibuat secara otomatis oleh Sistem POS Kasir PAM Techno.
                    </p>
                    
                    <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 30px 0;">
                    
                    <p style="font-size: 12px; color: #64748b; line-height: 1.5;">
                        Hormat kami,<br>
                        <strong>Tim PAM Techno</strong><br>
                        Sistem POS Analytics
                    </p>
                </div>
            '
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfData, $this->pdfFilename)
                ->withMime('application/pdf'),
        ];
    }
}
