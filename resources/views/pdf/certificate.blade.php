<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Completion - {{ $course->title }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            width: 297mm;
            height: 210mm;
            margin: 0;
            padding: 0;
            font-family: 'DejaVu Sans', sans-serif;
            background: #fff;
            overflow: hidden;
        }

        .certificate-wrapper {
            width: 297mm;
            height: 210mm;
            padding: 10mm;
        }

        .certificate-border {
            border: 4px double #6366f1;
            border-radius: 6mm;
            padding: 7mm;
            width: 277mm;
            height: 190mm;
            background: #ffffff;
        }

        .certificate-inner {
            border: 1px solid #e0e7ff;
            border-radius: 4mm;
            padding: 8mm 10mm;
            width: 263mm;
            height: 176mm;
        }

        .header {
            text-align: center;
            padding-bottom: 5mm;
            border-bottom: 1px solid #e0e7ff;
        }

        .header .icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 4px;
            color: white;
            font-size: 18px;
        }

        .header h1 {
            font-size: 20px;
            color: #4338ca;
            letter-spacing: 3px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .header p {
            font-size: 10px;
            color: #6b7280;
        }

        .body-content {
            text-align: center;
            padding: 4mm 0;
        }

        .body-content .student-name {
            font-size: 26px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 4px;
            font-family: 'DejaVu Serif', serif;
        }

        .body-content .divider {
            width: 50px;
            height: 2px;
            background: linear-gradient(90deg, #6366f1, #8b5cf6);
            margin: 6px auto;
        }

        .body-content .course-label {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .body-content .course-name {
            font-size: 16px;
            font-weight: bold;
            color: #4338ca;
            margin-bottom: 8px;
        }

        .details-grid {
            text-align: center;
            margin: 6px auto;
        }

        .details-grid table {
            margin: 0 auto;
            border-collapse: collapse;
        }

        .details-grid table td {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 5px 12px;
            text-align: center;
            width: 120px;
        }

        .details-grid .detail-label {
            font-size: 7px;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: bold;
            margin-bottom: 1px;
        }

        .details-grid .detail-value {
            font-size: 10px;
            color: #111827;
            font-weight: bold;
        }

        .details-grid .detail-value.mono {
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 9px;
        }

        .footer-section {
            text-align: center;
            padding-top: 4px;
            border-top: 1px solid #e5e7eb;
        }

        .footer-section table {
            margin: 0 auto;
            border-collapse: collapse;
        }

        .footer-section table td {
            text-align: center;
            padding: 0 20px;
            vertical-align: bottom;
        }

        .footer-section .sig-line {
            width: 100px;
            height: 28px;
            border-bottom: 2px solid #d1d5db;
            margin: 0 auto 2px;
        }

        .footer-section .sig-line img {
            max-height: 24px;
            max-width: 90px;
            opacity: 0.6;
        }

        .footer-section .sig-name {
            font-size: 9px;
            font-weight: bold;
            color: #111827;
        }

        .footer-section .sig-title {
            font-size: 8px;
            color: #6b7280;
        }

        .footer-section .seal-circle {
            width: 40px;
            height: 40px;
            border: 2px solid #6366f1;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2px;
            background: #eef2ff;
        }

        .footer-section .seal-circle .seal-icon {
            font-size: 14px;
            color: #6366f1;
        }

        .footer-section .seal-name {
            font-size: 9px;
            font-weight: bold;
            color: #111827;
        }

        .footer-section .seal-title {
            font-size: 8px;
            color: #6b7280;
        }

        .bottom-bar {
            text-align: center;
            padding-top: 4px;
        }

        .bottom-bar p {
            font-size: 7px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="certificate-wrapper">
        <div class="certificate-border">
            <div class="certificate-inner">
                <!-- Header -->
                <div class="header">
                    <div class="icon">&#9733;</div>
                    <h1>CERTIFICATE OF COMPLETION</h1>
                    <p>This certificate is proudly presented to</p>
                </div>

                <!-- Body -->
                <div class="body-content">
                    <div class="student-name">{{ $user->name }}</div>
                    <div class="divider"></div>
                    <div class="course-label">For successfully completing the course</div>
                    <div class="course-name">{{ $course->title }}</div>

                    <div class="details-grid">
                        <table>
                            <tr>
                                <td>
                                    <div class="detail-label">Completion Date</div>
                                    <div class="detail-value">
                                        {{ $certificate->issued_at ? $certificate->issued_at->format('F d, Y') : date('F d, Y') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="detail-label">Serial Number</div>
                                    <div class="detail-value mono">{{ $certificate->serial_number }}</div>
                                </td>
                                <td>
                                    <div class="detail-label">Certificate ID</div>
                                    <div class="detail-value mono">#{{ str_pad($certificate->id, 6, '0', STR_PAD_LEFT) }}</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Footer with Signature & Seal -->
                <div class="footer-section">
                    <table>
                        <tr>
                            <td>
                                <div class="sig-line">
                                    <img src="{{ $logoPath }}" alt="Signature">
                                </div>
                                <div class="sig-name">Astryx Academy</div>
                                <div class="sig-title">Authorized Signature</div>
                            </td>
                            <td>
                                <div class="seal-circle">
                                    <div class="seal-icon">&#9733;</div>
                                </div>
                                <div class="seal-name">Official Seal</div>
                                <div class="seal-title">Astryx Academy</div>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Bottom Bar -->
                <div class="bottom-bar">
                    <p>
                        This certificate is digitally issued and can be verified using the serial number: 
                        <strong>{{ $certificate->serial_number }}</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>