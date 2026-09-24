<?php

require_once __DIR__ . '/../config/const.php';

class PdfService
{
    /**
     * Rendu d'un template PHP/HTML situé dans le dossier templates/pdf/ avec passage de données.
     */
    public static function renderTemplate(string $templateRelativePath, array $data = []): string
    {
        $relativePath = ltrim($templateRelativePath, '/');
        $candidatePaths = [
            __DIR__ . '/../views/templates/pdf/' . $relativePath,
            __DIR__ . '/../templates/pdf/' . $relativePath
        ];

        $fullPath = null;
        foreach ($candidatePaths as $path) {
            if (file_exists($path)) {
                $fullPath = $path;
                break;
            }
        }

        if (!$fullPath) {
            throw new Exception("Template PDF introuvable : " . $templateRelativePath);
        }

        extract($data);
        ob_start();
        include $fullPath;
        return ob_get_clean();
    }

    /**
     * Génère et diffuse le document PDF au navigateur (Affichage inline ou Téléchargement).
     */
    public static function generate(string $html, string $filename = 'document.pdf', array $options = []): void
    {
        if (!class_exists('\Mpdf\Mpdf')) {
            throw new Exception("La bibliothèque mPDF n'est pas chargée. Vérifiez l'installation Composer.");
        }

        $orientation = $options['orientation'] ?? 'P'; // P = Portrait, L = Paysage
        $format = $options['format'] ?? 'A4';
        $download = $options['download'] ?? false;
        $watermark = $options['watermark'] ?? null;

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => $format,
            'orientation' => $orientation,
            'margin_left' => $options['margin_left'] ?? 12,
            'margin_right' => $options['margin_right'] ?? 12,
            'margin_top' => $options['margin_top'] ?? 12,
            'margin_bottom' => $options['margin_bottom'] ?? 12,
            'margin_header' => 5,
            'margin_footer' => 5,
            'tempDir' => sys_get_temp_dir()
        ]);

        $mpdf->SetTitle($options['title'] ?? 'Document GEICG');
        $mpdf->SetAuthor('GEICG - Système d\'Information');
        $mpdf->showImageErrors = true;

        if ($watermark) {
            $mpdf->SetWatermarkText($watermark);
            $mpdf->showWatermarkText = true;
            $mpdf->watermark_font = 'DejaVuSans';
            $mpdf->watermarkTextAlpha = 0.07;
        }

        $mpdf->WriteHTML($html);

        $dest = $download ? \Mpdf\Output\Destination::DOWNLOAD : \Mpdf\Output\Destination::INLINE;
        $mpdf->Output($filename, $dest);
    }

    /**
     * Convertit un montant numérique en lettres en français (ex: 105000 -> "Cent cinq mille francs CFA").
     */
    public static function numberToWordsFrench(float $amount): string
    {
        $amount = (int)round($amount);
        if ($amount <= 0) return 'Zéro franc CFA';

        if (class_exists('NumberFormatter')) {
            $fmt = new NumberFormatter('fr_FR', NumberFormatter::SPELLOUT);
            $words = $fmt->format($amount);
            return ucfirst($words) . ' francs CFA';
        }

        return number_format($amount, 0, ',', ' ') . ' FCFA';
    }
}
