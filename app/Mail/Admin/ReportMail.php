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

    /**
     * Create a new message instance.
     */
    public function __construct(string $pdfData, string $csvData, string $dateRange, string $pdfFileName, string $csvFileName)
    {
        $this->pdfData = $pdfData;
        $this->csvData = $csvData;
        $this->dateRange = $dateRange;
        $this->pdfFileName = $pdfFileName;
        $this->csvFileName = $csvFileName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[LAPORAN] Transaksi PAM Techno - Periode: ' . $this->dateRange,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            htmlString: '
                <div style="font-family: sans-serif; line-height: 1.6; color: #333;">
                    <p>Yth. Bapak/Ibu Owner PAM Techno,</p>
                    <p>Melalui email ini, kami sampaikan laporan transaksi penjualan untuk periode <strong>' . $this->dateRange . '</strong>.</p>
                    <p>Laporan ini terlampir dalam dua format untuk memudahkan analisis Anda:</p>
                    <ul style="padding-left: 20px;">
                        <li><strong>File PDF</strong>: Ringkasan visual penjualan dan laba rugi.</li>
                        <li><strong>File Excel (CSV)</strong>: Data detail transaksi harian.</li>
                    </ul>
                    <p>Mohon tinjau lampiran terlampir untuk informasi lebih rinci.</p>
                    <br>
                    <p>Hormat kami,</p>
                    <p><strong>Sistem Kasir PAM Techno</strong></p>
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
