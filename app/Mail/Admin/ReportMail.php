<?php

namespace App\Mail\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $pdfData;
    public string $csvData;
    public string $dateRange;
    public string $pdfFileName;
    public string $csvFileName;
    public array $summary;

    /**
     * Create a new message instance.
     */
    public function __construct(string $pdfData, string $csvData, string $dateRange, string $pdfFileName, string $csvFileName, array $summary = [])
    {
        $this->pdfData = $pdfData;
        $this->csvData = $csvData;
        $this->dateRange = $dateRange;
        $this->pdfFileName = $pdfFileName;
        $this->csvFileName = $csvFileName;
        $this->summary = $summary;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Laporan Penjualan PAM Techno - Periode: ' . $this->dateRange,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $omzet = number_format($this->summary['revenue'] ?? 0, 0, ',', '.');
        $laba = number_format($this->summary['profit'] ?? 0, 0, ',', '.');
        $transaksi = number_format($this->summary['transactions'] ?? 0, 0, ',', '.');

        return new Content(
            htmlString: '
                <div style="font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #1e293b; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background-color: #ffffff;">
                    <div style="background-color: #0f172a; padding: 25px; text-align: center;">
                        <h1 style="color: #ffffff; margin: 0; font-size: 20px; letter-spacing: 1px; text-transform: uppercase;">PAM TECHNO POS</h1>
                        <p style="color: #94a3b8; margin: 5px 0 0 0; font-size: 12px;">BUSINESS INTELLIGENCE REPORT</p>
                    </div>
                    
                    <div style="padding: 30px;">
                        <p style="margin-top: 0;">Yth. Bapak/Ibu Owner,</p>
                        <p>Semoga Bapak/Ibu dalam keadaan baik.</p>
                        <p>Melalui email ini, kami menyampaikan <strong>Laporan Penjualan</strong> untuk periode <strong>' . $this->dateRange . '</strong>, yang disusun berdasarkan data transaksi pada sistem Pam Techno POS.</p>
                        
                        <div style="background-color: #f8fafc; border-radius: 10px; padding: 20px; margin: 25px 0; border: 1px solid #f1f5f9;">
                            <p style="margin-top: 0; font-weight: bold; font-size: 13px; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Ringkasan Performa Periode Ini:</p>
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 10px 5px; text-align: center; border-right: 1px solid #e2e8f0; width: 33%;">
                                        <div style="font-size: 10px; color: #64748b; text-transform: uppercase; margin-bottom: 5px;">Total Omzet</div>
                                        <div style="font-size: 15px; font-weight: 800; color: #0f172a;">Rp ' . $omzet . '</div>
                                    </td>
                                    <td style="padding: 10px 5px; text-align: center; border-right: 1px solid #e2e8f0; width: 33%;">
                                        <div style="font-size: 10px; color: #64748b; text-transform: uppercase; margin-bottom: 5px;">Total Laba</div>
                                        <div style="font-size: 15px; font-weight: 800; color: #10b981;">Rp ' . $laba . '</div>
                                    </td>
                                    <td style="padding: 10px 5px; text-align: center; width: 33%;">
                                        <div style="font-size: 10px; color: #64748b; text-transform: uppercase; margin-bottom: 5px;">Transaksi</div>
                                        <div style="font-size: 15px; font-weight: 800; color: #0f172a;">' . $transaksi . ' Nota</div>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <p>Laporan ini mencakup informasi rincian sebagai berikut:</p>
                        <ul style="padding-left: 20px; color: #475569;">
                            <li style="margin-bottom: 5px;">Ringkasan total penjualan harian</li>
                            <li style="margin-bottom: 5px;">Total laba dan volume transaksi</li>
                            <li style="margin-bottom: 5px;">Tren grafik penjualan pada periode berjalan</li>
                            <li style="margin-bottom: 5px;">Distribusi laba berdasarkan kategori produk</li>
                        </ul>
                        
                        <p style="background-color: #eff6ff; padding: 15px; border-radius: 8px; font-size: 13px; color: #1e40af; border-left: 4px solid #3b82f6;">
                            Laporan lengkap kami lampirkan dalam format <strong>PDF</strong> dan <strong>Excel</strong> untuk memudahkan peninjauan lebih lanjut oleh Bapak/Ibu.
                        </p>
                        
                        <p>Apabila diperlukan penjelasan tambahan terkait hasil laporan ini, kami siap untuk menindaklanjuti.</p>
                        
                        <br>
                        <div style="border-top: 1px solid #f1f5f9; padding-top: 20px;">
                            <p style="margin: 0; font-size: 14px;">Hormat kami,</p>
                            <p style="margin: 5px 0 0 0; font-weight: bold; color: #0f172a;">Admin Pam Techno POS</p>
                        </div>
                    </div>
                    
                    <div style="background-color: #f8fafc; padding: 15px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #f1f5f9;">
                        Pesan ini dikirim secara otomatis oleh sistem. Harap tidak membalas email ini secara langsung.
                    </div>
                </div>
            ',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            \Illuminate\Mail\Mailables\Attachment::fromData(fn() => $this->pdfData, $this->pdfFileName)
                ->withMime('application/pdf'),
            \Illuminate\Mail\Mailables\Attachment::fromData(fn() => $this->csvData, $this->csvFileName)
                ->withMime('text/csv'),
        ];
    }
}
