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

        $sigleEtab = '';
        $cfgEtab = [];
        if (!class_exists('ModelConfiguration') && file_exists(__DIR__ . '/../models/configurations/ModelConfiguration.php')) {
            require_once __DIR__ . '/../models/configurations/ModelConfiguration.php';
        }
        if (class_exists('ModelConfiguration')) {
            try {
                $cfgEtab = (new ModelConfiguration())->getConfig();
                $sigleEtab = trim((string)($cfgEtab['sigle_etablissement'] ?? ($cfgEtab['nom_etablissement'] ?? '')));
            } catch (Exception $e) {}
        }
        $mpdf->SetTitle($options['title'] ?? ($sigleEtab !== '' ? 'Document - ' . $sigleEtab : 'Document Officiel'));
        $mpdf->SetAuthor($sigleEtab !== '' ? $sigleEtab . ' - Système d\'Information' : 'Système d\'Information');
        $mpdf->showImageErrors = true;

        // Résolution automatique du logo institutionnel (configurations.logo_etablissement) si watermark_logo => true
        $watermarkImagePath = $options['watermark_image'] ?? null;
        if (empty($watermarkImagePath) && !empty($options['watermark_logo']) && !empty($cfgEtab['logo_etablissement'])) {
            $cleanLogo = ltrim((string)$cfgEtab['logo_etablissement'], '/');
            foreach ([__DIR__ . '/../public/' . $cleanLogo, __DIR__ . '/../' . $cleanLogo] as $candLogo) {
                if (file_exists($candLogo) && is_file($candLogo)) {
                    $watermarkImagePath = $candLogo;
                    break;
                }
            }
        }

        if (!empty($watermarkImagePath) && file_exists($watermarkImagePath)) {
            $defaultAlpha = !empty($options['watermark_logo']) ? 0.06 : 0.04;
            $wmAlpha = isset($options['watermark_alpha']) ? (float)$options['watermark_alpha'] : $defaultAlpha;
            $defaultWidth = ($orientation === 'L') ? 160 : 135;
            $wmSize = $options['watermark_size'] ?? (!empty($options['watermark_logo']) ? [$defaultWidth, 0] : [110, 110]);
            if (is_array($wmSize) && count($wmSize) === 2 && (float)$wmSize[0] > 0 && (float)$wmSize[1] <= 0) {
                $imgDim = @getimagesize($watermarkImagePath);
                if ($imgDim && !empty($imgDim[0]) && !empty($imgDim[1])) {
                    $wmSize = [(float)$wmSize[0], round((float)$wmSize[0] * ($imgDim[1] / $imgDim[0]), 2)];
                } else {
                    $wmSize = [(float)$wmSize[0], (float)$wmSize[0]];
                }
            }
            $wmPos = $options['watermark_position'] ?? 'F';
            $mpdf->SetWatermarkImage($watermarkImagePath, $wmAlpha, $wmSize, $wmPos);
            $mpdf->showWatermarkImage = true;
        } elseif ($watermark) {
            $mpdf->SetWatermarkText($watermark);
            $mpdf->showWatermarkText = true;
            $mpdf->watermark_font = 'DejaVuSans';
            $mpdf->watermarkTextAlpha = 0.07;
        }

        $mpdf->WriteHTML($html);

        $dest = $options['destination'] ?? ($download ? \Mpdf\Output\Destination::DOWNLOAD : \Mpdf\Output\Destination::INLINE);
        if ($dest === \Mpdf\Output\Destination::INLINE || $dest === \Mpdf\Output\Destination::DOWNLOAD) {
            $pdfContent = $mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN);
            if (!headers_sent()) {
                $safeFilename = str_replace(['"', "\r", "\n"], '', basename($filename));
                $disposition = ($dest === \Mpdf\Output\Destination::DOWNLOAD) ? 'attachment' : 'inline';
                header('Content-Type: application/pdf');
                header('Content-Disposition: ' . $disposition . '; filename="' . $safeFilename . '"');
                header('Content-Length: ' . strlen($pdfContent));
                header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
                header('Pragma: no-cache');
                header('Expires: 0');
            }
            echo $pdfContent;
            return;
        }
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
