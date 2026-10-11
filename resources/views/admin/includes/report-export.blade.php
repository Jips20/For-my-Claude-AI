<script>
    // Pharmacy info pulled from Settings, used to brand exported/printed reports
    const PHARMACY_INFO = {
        name: @json(AppSettings::get('app_name', config('app.name'))),
        branch: @json(AppSettings::get('pharmacy_branch', '')),
        address: @json(AppSettings::get('pharmacy_address', '')),
        contact: @json(AppSettings::get('pharmacy_contact', '')),
        logoUrl: @json(AppSettings::get('logo') ? asset('storage/'.AppSettings::get('logo')) : null),
    };

    // pdfMake can't fetch remote/relative image URLs directly, so we
    // pre-convert the logo to base64 as soon as the page loads.
    let PHARMACY_LOGO_BASE64 = null;
    if (PHARMACY_INFO.logoUrl) {
        (function loadLogo() {
            const img = new Image();
            img.crossOrigin = 'Anonymous';
            img.onload = function () {
                const canvas = document.createElement('canvas');
                canvas.width = img.width;
                canvas.height = img.height;
                canvas.getContext('2d').drawImage(img, 0, 0);
                try {
                    PHARMACY_LOGO_BASE64 = canvas.toDataURL('image/png');
                } catch (e) {
                    PHARMACY_LOGO_BASE64 = null; // tainted canvas, just skip the logo
                }
            };
            img.src = PHARMACY_INFO.logoUrl;
        })();
    }

    /**
     * Builds the minimalist, CENTERED header block for the exported PDF.
     */
    function buildPdfReportHeader(reportTitle, period) {
        const infoStack = [];

        if (PHARMACY_LOGO_BASE64) {
            infoStack.push({
                image: PHARMACY_LOGO_BASE64,
                width: 60,
                alignment: 'center',
                margin: [0, 0, 0, 8],
            });
        }

        infoStack.push({ text: PHARMACY_INFO.name || 'Pharmacy', style: 'pharmacyName', alignment: 'center' });
        if (PHARMACY_INFO.branch) infoStack.push({ text: PHARMACY_INFO.branch, style: 'pharmacyMeta', alignment: 'center' });
        if (PHARMACY_INFO.address) infoStack.push({ text: PHARMACY_INFO.address, style: 'pharmacyMeta', alignment: 'center' });
        if (PHARMACY_INFO.contact) infoStack.push({ text: 'Contact: ' + PHARMACY_INFO.contact, style: 'pharmacyMeta', alignment: 'center' });

        return [
            { stack: infoStack, margin: [0, 0, 0, 10] },
            { canvas: [{ type: 'line', x1: 0, y1: 0, x2: 515, y2: 0, lineWidth: 1, lineColor: '#e0e0e0' }], margin: [0, 0, 0, 10] },
            { text: reportTitle, style: 'reportTitle', alignment: 'center' },
            period ? { text: period, style: 'pharmacyMeta', alignment: 'center', margin: [0, 1, 0, 2] } : null,
            { text: 'Generated on: ' + new Date().toLocaleString(), style: 'pharmacyMeta', alignment: 'center', margin: [0, 0, 0, 14] },
        ].filter(Boolean);
    }

    /**
     * DataTables Buttons "customize" callback for the pdf export button.
     */
    function customizePdfReport(doc, reportTitle, period) {
        doc.pageMargins = [40, 40, 40, 40];
        doc.styles = Object.assign({}, doc.styles, {
            pharmacyName: { fontSize: 15, bold: true, color: '#1a1a1a' },
            pharmacyMeta: { fontSize: 8.5, color: '#6b6b6b', margin: [0, 1, 0, 0] },
            reportTitle: { fontSize: 12, bold: true, color: '#1a1a1a' },
        });
        doc.defaultStyle = Object.assign({}, doc.defaultStyle, { fontSize: 9 });

        // Restyle the auto-generated table: thin lines, center-aligned cells
        const table = doc.content[doc.content.length - 1];
        if (table && table.table) {
            table.layout = {
                hLineWidth: function (i) { return (i === 0 || i === 1) ? 1 : 0.5; },
                vLineWidth: function () { return 0; },
                hLineColor: function () { return '#dddddd'; },
                paddingTop: function () { return 5; },
                paddingBottom: function () { return 5; },
            };

            if (Array.isArray(table.table.body)) {
                table.table.body = table.table.body.map(function (row) {
                    return row.map(function (cell) {
                        if (cell && typeof cell === 'object') {
                            cell.alignment = 'center';
                        }
                        return cell;
                    });
                });
            }
        }

        doc.content = buildPdfReportHeader(reportTitle, period).concat(doc.content);

        doc.footer = function (currentPage, pageCount) {
            return {
                columns: [
                    { text: (PHARMACY_INFO.branch || PHARMACY_INFO.name || ''), style: 'pharmacyMeta', margin: [40, 0, 0, 0] },
                    { text: 'Page ' + currentPage + ' of ' + pageCount, style: 'pharmacyMeta', alignment: 'right', margin: [0, 0, 40, 0] },
                ],
            };
        };
    }

    /**
     * DataTables Buttons "customize" callback for the print button.
     */
    function customizePrintReport(win, reportTitle, period) {
        const doc = win.document;

        const logoHtml = PHARMACY_LOGO_BASE64
            ? '<img src="' + PHARMACY_LOGO_BASE64 + '" style="width:60px;height:auto;display:block;margin:0 auto 8px auto;">'
            : (PHARMACY_INFO.logoUrl ? '<img src="' + PHARMACY_INFO.logoUrl + '" style="width:60px;height:auto;display:block;margin:0 auto 8px auto;">' : '');

        const header = doc.createElement('div');
        header.innerHTML = `
            <div style="text-align:center;font-family:Arial,Helvetica,sans-serif;margin-bottom:14px;">
                ${logoHtml}
                <div style="font-size:16px;font-weight:700;color:#1a1a1a;">${PHARMACY_INFO.name || 'Pharmacy'}</div>
                ${PHARMACY_INFO.branch ? '<div style="font-size:11px;color:#6b6b6b;">' + PHARMACY_INFO.branch + '</div>' : ''}
                ${PHARMACY_INFO.address ? '<div style="font-size:11px;color:#6b6b6b;">' + PHARMACY_INFO.address + '</div>' : ''}
                ${PHARMACY_INFO.contact ? '<div style="font-size:11px;color:#6b6b6b;">Contact: ' + PHARMACY_INFO.contact + '</div>' : ''}
            </div>
            <hr style="border:none;border-top:1px solid #e0e0e0;margin-bottom:10px;">
            <div style="text-align:center;font-size:13px;font-weight:700;color:#1a1a1a;margin-bottom:2px;">${reportTitle}</div>
            ${period ? '<div style="text-align:center;font-size:10.5px;color:#6b6b6b;">' + period + '</div>' : ''}
            <div style="text-align:center;font-size:10.5px;color:#6b6b6b;margin-bottom:16px;">Generated on: ${new Date().toLocaleString()}</div>
        `;
        doc.body.insertBefore(header, doc.body.firstChild);

        const style = doc.createElement('style');
        style.innerHTML = `
            body { font-family: Arial, Helvetica, sans-serif; color: #1a1a1a; }
            table { border-collapse: collapse; width: 100%; margin: 0 auto; }
            table th { text-align: center; font-size: 10.5px; color: #6b6b6b; text-transform: uppercase; letter-spacing: 0.03em; border-bottom: 1px solid #1a1a1a; padding: 6px 8px; }
            table td { text-align: center; font-size: 11px; border-bottom: 1px solid #eeeeee; padding: 6px 8px; }
        `;
        doc.head.appendChild(style);
    }
</script>