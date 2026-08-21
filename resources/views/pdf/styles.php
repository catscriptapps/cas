<?php
// /resources/views/pdf/styles.php
//
// The PDF's stylesheet, registered once via $mpdf->WriteHTML(..., HEADER_CSS)
// before any content is written. Most page breaks are plain CSS
// page-break-before (every block after the first); cover image pages
// additionally need a real margin change (0 instead of the normal content
// margin) to bleed to the page edge, which CSS can't do -- those use an
// explicit mPDF <pagebreak> tag instead (see InspectionPdfService::writeBlock()).
?>
<style>
    body, table, td, th, div, p, span, h1, h2, h3, h4 {
        font-family: quicksand;
        color: #1f2937;
    }

    h1, h2, h3, h4 { font-family: quicksandsemibold; }

    .pdf-page-break-before { page-break-before: always; }

    /* -------------------- Cover / full-bleed image pages -------------------- */
    /* Exact width/height (matching the zero-margin page size) is set inline
       per-image by InspectionPdfService, since a percentage here can't be
       trusted to resolve against the full physical page. */
    .pdf-cover-image-page { text-align: center; padding: 0; margin: 0; }
    .pdf-cover-image-page img { display: block; object-fit: cover; }

    /* -------------------- Auto-generated title page -------------------- */
    .pdf-title-page { text-align: center; padding-top: 60mm; }
    .pdf-title-logo { max-height: 30mm; max-width: 90mm; margin-bottom: 8mm; }
    .pdf-title-company { font-family: quicksandsemibold; font-size: 20pt; color: #041a49; margin-bottom: 2mm; }
    .pdf-title-report-label { font-family: quicksandmedium; font-size: 11pt; color: #6b7280; text-transform: uppercase; letter-spacing: 2pt; margin-bottom: 14mm; }
    .pdf-title-address { font-family: quicksandsemibold; font-size: 15pt; color: #1f2937; margin-bottom: 2mm; }
    .pdf-title-subaddress { font-size: 10pt; color: #6b7280; margin-bottom: 14mm; }
    .pdf-title-date { font-size: 9pt; color: #9ca3af; }
    .pdf-title-footer { position: relative; margin-top: 40mm; font-size: 8.5pt; color: #9ca3af; border-top: 0.4pt solid #e5e7eb; padding-top: 4mm; }

    /* -------------------- Standards pages -------------------- */
    .pdf-standards-page h2 { font-size: 15pt; color: #041a49; margin-bottom: 3mm; }
    .pdf-standards-page h3 { font-size: 11.5pt; color: #0c45c0; margin-top: 5mm; margin-bottom: 2mm; }
    .pdf-standards-page p { font-size: 9pt; line-height: 1.5; margin-bottom: 2.5mm; }
    .pdf-standards-page ul { margin: 0 0 3mm 0; padding-left: 5mm; }
    .pdf-standards-page li { font-size: 8.5pt; line-height: 1.45; margin-bottom: 1mm; }

    /* -------------------- Section header -------------------- */
    .pdf-section-header { background: #041a49; padding: 4mm 5mm; margin-bottom: 4mm; }
    .pdf-section-title { font-family: quicksandsemibold; font-size: 14pt; color: #ffffff; text-transform: uppercase; letter-spacing: 0.5pt; }
    .pdf-section-address { font-size: 8.5pt; color: #bcceff; margin-top: 1mm; }

    /* -------------------- Question rows -------------------- */
    .pdf-question { border-bottom: 0.3pt solid #e5e7eb; padding: 2.5mm 0; }
    .pdf-question-title { font-family: quicksand; font-weight: bold; font-size: 9.5pt; color: #1f2937; }
    .pdf-status-badge { display: inline-block; font-family: quicksandsemibold; font-size: 7pt; text-transform: uppercase; letter-spacing: 0.3pt; padding: 0.6mm 2mm; border-radius: 6pt; margin-left: 2mm; }
    .pdf-status-inspected { background: #dcfce7; color: #15803d; }
    .pdf-status-not-inspected { background: #fee2e2; color: #b91c1c; }
    .pdf-status-na { background: #f3f4f6; color: #6b7280; }
    .pdf-mode-badge { display: inline-block; font-family: quicksandsemibold; font-size: 7pt; text-transform: uppercase; letter-spacing: 0.3pt; padding: 0.6mm 2mm; border-radius: 6pt; margin-left: 2mm; background: #f1f5f9; color: #475569; }
    .pdf-mode-perform { background: #eef2ff; color: #4338ca; }
    .pdf-options { font-size: 8.5pt; color: #374151; margin-top: 1mm; }
    .pdf-fields { font-size: 8.5pt; color: #374151; margin-top: 1mm; }
    .pdf-notes { font-size: 8.5pt; color: #4b5563; font-style: italic; margin-top: 1mm; }

    .pdf-conditions-heading { font-family: quicksandsemibold; font-size: 10pt; color: #ea580c; margin-top: 4mm; margin-bottom: 1.5mm; border-top: 0.5pt solid #fed7aa; padding-top: 3mm; }

    .pdf-section-comment { background: #f8fafc; border: 0.4pt solid #e2e8f0; border-radius: 3pt; padding: 3mm 4mm; margin-top: 4mm; font-size: 8.5pt; color: #374151; }
    .pdf-section-comment .label { font-family: quicksandsemibold; font-size: 7.5pt; text-transform: uppercase; color: #6b7280; letter-spacing: 0.4pt; margin-bottom: 1mm; }

    /* -------------------- Section footer (Tasks legend + sign-off) -------------------- */
    /* Mirrors legacy's pdf_body.php footer(): a 3-row legend explaining the
       diamond/square Tasks glyphs and the Inspected/Not Inspected/N-A status
       glyphs, an inspector-initials sign-off, and a copyright line. */
    .pdf-footer-table { width: 100%; margin-top: 5mm; border: 0.4pt solid #d1d5db; border-collapse: collapse; font-family: quicksandsemibold; font-size: 8pt; color: #1f2937; }
    .pdf-footer-table td { padding: 1.6mm 2.5mm; border-bottom: 0.3pt solid #e5e7eb; border-right: 0.3pt solid #e5e7eb; }
    /* font-family here (not just on .pdf-mode-badge/etc) is load-bearing:
       Quicksand has no glyphs for U+2714/U+25C6/U+25A0/U+2044, so without an
       explicit Unicode-capable fallback face these render as a "missing
       glyph" tofu box instead of the actual checkmark/diamond/square/n-a. */
    .pdf-footer-icon-cell { width: 14mm; text-align: center; font-size: 10.5pt; color: #041a49; font-family: sans-serif; }
    .pdf-footer-corner { background: #041a49; border-right: none !important; }
    .pdf-footer-spacer { background: #041a49; padding: 1mm !important; }
    .pdf-footer-copyright { text-align: center; font-size: 7pt; font-family: quicksand; color: #9ca3af; padding: 1.5mm !important; border-right: none !important; }

    /* -------------------- Photo grid -------------------- */
    .pdf-photos-heading { font-family: quicksandsemibold; font-size: 10pt; color: #041a49; margin-top: 6mm; margin-bottom: 3mm; }
    .pdf-photo-cell { width: 48%; padding: 2mm; }
    .pdf-photo-cell img { width: 100%; height: 62mm; object-fit: cover; border: 0.4pt solid #e5e7eb; border-radius: 2pt; }
    .pdf-photo-caption { font-family: quicksandmedium; font-size: 11pt; color: #1f2937; margin-top: 2mm; line-height: 1.4; }

    /* -------------------- Summary page -------------------- */
    .pdf-summary-heading { font-size: 15pt; color: #041a49; margin-bottom: 4mm; }
    .pdf-summary-body { font-size: 9pt; line-height: 1.6; color: #1f2937; white-space: pre-wrap; }

    .pdf-empty-note { font-size: 8.5pt; color: #9ca3af; font-style: italic; }
</style>
