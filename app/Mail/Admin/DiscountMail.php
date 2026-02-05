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

    public array $comparison;

    public function __construct(
        private string $pdfData,
        private string $pdfFilename,
        array $comparison = []
    ) {
        $this->comparison = $comparison;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Analisis Efektivitas Diskon - ' . now()->translatedFormat('d F Y'),
        );
    }

    public function content(): Content
    {
        $indicator = ($this->comparison['diff']['total_profit'] ?? 0) >= 0 ? 'kenaikan' : 'penurunan';
        $diff = abs($this->comparison['diff']['total_profit'] ?? 0);
        $statusColor = ($this->comparison['diff']['total_profit'] ?? 0) >= 0 ? '#10b981' : '#ef4444';
        $statusBg = ($this->comparison['diff']['total_profit'] ?? 0) >= 0 ? '#ecfdf5' : '#fef2f2';

        return new Content(
            htmlString: '
                <div style="font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #1e293b; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background-color: #ffffff;">
                    <div style="background-color: #4f46e5; padding: 25px; text-align: center;">
                        <h1 style="color: #ffffff; margin: 0; font-size: 20px; letter-spacing: 1px; text-transform: uppercase;">PAM TECHNO ANALYTICS</h1>
                        <p style="color: #c7d2fe; margin: 5px 0 0 0; font-size: 12px;">DISCOUNT EFFECTIVENESS REPORT</p>
                    </div>
                    
                    <div style="padding: 30px;">
                        <p style="margin-top: 0;">Yth. Bapak/Ibu Owner,</p>
                        <p>Semoga Bapak/Ibu dalam keadaan baik.</p>
                        <p>Bersama email ini, kami sampaikan <strong>Laporan Analisis Efektivitas Diskon</strong> untuk periode <strong>30 hari terakhir</strong>. Laporan ini bertujuan untuk mengevaluasi dampak strategis dari kampanye promosi terhadap profitabilitas toko Anda.</p>
                        
                        <div style="background-color: ' . $statusBg . '; border-radius: 10px; padding: 20px; margin: 25px 0; border: 1px solid ' . $statusColor . '; border-left-width: 6px;">
                            <p style="margin-top: 0; font-weight: bold; font-size: 13px; color: #475569; text-transform: uppercase;">Ringkasan Analisis Strategis:</p>
                            <p style="margin: 10px 0 0 0; font-size: 16px; font-weight: 700; color: ' . $statusColor . ';">
                                "Strategi diskon periode ini berhasil menghasilkan ' . $indicator . ' laba bersih sebesar ' . $diff . '%."
                            </p>
                        </div>

                        <p>Laporan ini mencakup rincian analisis diantaranya:</p>
                        <ul style="padding-left: 20px; color: #475569;">
                            <li style="margin-bottom: 5px;">Analisis perbandingan mendalam (Dengan vs Tanpa Diskon)</li>
                            <li style="margin-bottom: 5px;">Performa ROI (Return on Investment) untuk setiap kampanye</li>
                            <li style="margin-bottom: 5px;">Dampak penggunaan diskon terhadap margin keuntungan</li>
                            <li style="margin-bottom: 5px;">Rekomendasi strategis untuk optimasi promosi mendatang</li>
                        </ul>
                        
                        <p style="font-size: 14px; margin: 20px 0;">
                            Detail analisis selengkapnya telah kami lampirkan dalam format <strong>PDF</strong>. Jika terdapat hal yang ingin didiskusikan terkait efikasi promosi ini, silakan hubungi tim admin.
                        </p>
                        
                        <br>
                        <div style="border-top: 1px solid #f1f5f9; padding-top: 20px;">
                            <p style="margin: 0; font-size: 14px;">Hormat kami,</p>
                            <p style="margin: 5px 0 0 0; font-weight: bold; color: #0f172a;">Admin Pam Techno POS</p>
                        </div>
                    </div>
                    
                    <div style="background-color: #f8fafc; padding: 15px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #f1f5f9;">
                        Pesan ini dikirim secara otomatis oleh sistem Analitik PAM Techno.
                    </div>
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
