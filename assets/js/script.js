// small helper: confirm delete
function confirmDelete(url) {
    document.getElementById('linkHapus').href = url;
    new bootstrap.Modal(document.getElementById('modalHapus')).show();
}

// Sidebar toggle
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebarMenu = document.getElementById('sidebar-menu');
    if (sidebarToggle && sidebarMenu) {
        sidebarToggle.addEventListener('click', function() {
            sidebarMenu.classList.toggle('active');
        });
    }
});

// Search functionality
function setupSearch(searchId, tableSelector) {
    const searchInput = document.getElementById(searchId);
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            const table = document.querySelector(tableSelector);
            if (table) {
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    const text = Array.from(cells).map(cell => cell.textContent.toLowerCase()).join(' ');
                    row.style.display = text.includes(filter) ? 'table-row' : 'none';
                });
            }
        });
    }
}

// Initialize search for each page
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('search-barang')) {
        setupSearch('search-barang', '.table');
    }
    if (document.getElementById('search-pembeli')) {
        setupSearch('search-pembeli', '.table');
    }
    if (document.getElementById('search-transaksi')) {
        setupSearch('search-transaksi', '.table');
    }
    if (document.getElementById('search-laporan')) {
        setupSearch('search-laporan', '.table');
    }
});

// Sortable tables
function sortTable(table, column, asc = true) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    rows.sort((a, b) => {
        const aVal = a.children[column].textContent.trim();
        const bVal = b.children[column].textContent.trim();
        if (!isNaN(aVal) && !isNaN(bVal)) {
            return asc ? aVal - bVal : bVal - aVal;
        }
        return asc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
    });
    rows.forEach(row => tbody.appendChild(row));
}

document.addEventListener('DOMContentLoaded', function() {
    const tables = document.querySelectorAll('.table');
    tables.forEach(table => {
        const headers = table.querySelectorAll('th');
        headers.forEach((header, index) => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => {
                sortTable(table, index);
            });
        });
    });
});

// Show receipt
function showReceipt(id, pembeli, barang, jumlah, total, tanggal) {
    const content = `
        <div class="receipt-header">Toko Donny</div>
        <p><strong>ID Transaksi:</strong> ${id}</p>
        <p><strong>Pembeli:</strong> ${pembeli}</p>
        <p><strong>Barang:</strong> ${barang}</p>
        <p><strong>Jumlah:</strong> ${jumlah}</p>
        <p><strong>Total:</strong> Rp ${total.toLocaleString()}</p>
        <p><strong>Tanggal:</strong> ${tanggal}</p>
        <div class="receipt-footer">Terimakasih telah berbelanja</div>
    `;
    document.getElementById('receipt-content').innerHTML = content;
    new bootstrap.Modal(document.getElementById('modalReceipt')).show();
}

// Print receipt in new window for clean output
function printReceipt(id, pembeli, barang, jumlah, total, tanggal) {
    const printWindow = window.open('', '_blank');
    const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Receipt - Toko Donny</title>
            <style>
                body { 
                    font-family: monospace; 
                    font-size: 12px; 
                    margin: 0; 
                    padding: 0; 
                    width: 100%; 
                    height: 100vh;
                    display: flex;
                    justify-content: center;
                    align-items: flex-start;
                    background: white;
                }
                .receipt-wrapper {
                    width: 80mm;
                    padding: 10px;
                    box-sizing: border-box;
                    border: 1px solid #000;
                    margin-top: 10mm; /* jarak 1 cm dari tepi atas */
                }
                .receipt-header { 
                    font-weight: bold; 
                    font-size: 16px; 
                    text-align: center;
                    margin-bottom: 10px; 
                }
                .receipt-body {
                    text-align: left; 
                    margin-left: 5px;
                }
                p { 
                    margin: 5px 0; 
                    line-height: 1.3; 
                }
                .receipt-footer { 
                    font-size: 10px; 
                    margin-top: 20px; 
                    font-style: italic; 
                    text-align: center; 
                }

                @media print {
                    @page { 
                        margin: 0; 
                        size: A4 portrait; 
                    }
                    html, body {
                        width: 100%;
                        height: 100%;
                        margin: 0;
                        padding: 0;
                        display: flex;
                        justify-content: center;
                        align-items: flex-start;
                        background: white;
                    }
                    .receipt-wrapper {
                        border: 1px solid #000;
                        padding: 10px;
                        width: 80mm;
                        box-sizing: border-box;
                        page-break-inside: avoid;
                        margin-top: 10mm; /* tetap 1 cm dari tepi atas saat print */
                    }
                }
            </style>
        </head>
        <body>
            <div class="receipt-wrapper">
                <div class="receipt-header">Toko Donny</div>

                <div class="receipt-body">
                    <p><strong>ID Transaksi:</strong> ${id}</p>
                    <p><strong>Pembeli:</strong> ${pembeli}</p>
                    <p><strong>Barang:</strong> ${barang}</p>
                    <p><strong>Jumlah:</strong> ${jumlah}</p>
                    <p><strong>Total:</strong> Rp ${total.toLocaleString()}</p>
                    <p><strong>Tanggal:</strong> ${tanggal}</p>
                </div>

                <div class="receipt-footer">Terimakasih telah berbelanja</div>
            </div>
            <script>
                window.onload = function() { 
                    window.print(); 
                    window.close(); 
                }
            </script>
        </body>
        </html>
    `;
    printWindow.document.write(printContent);
    printWindow.document.close();
}

// Print report in new window for clean output (for dashboard)
function printReport() {
  console.log('printReport called');
  try {
    // Collect current data from page with checks
    const title = document.querySelector('h5.card-title');
    if (!title) {
      alert('Elemen laporan tidak ditemukan.');
      return;
    }
    const label = title.textContent.replace('Laporan Penjualan ', '').trim();
    const values = document.querySelectorAll('.card-summary .value');
    if (values.length < 3) {
      alert('Data ringkasan tidak lengkap.');
      return;
    }
    const totalTransaksi = values[0].textContent.trim();
    const totalPendapatan = values[1].textContent.trim();
    const barangTerlaris = values[2].textContent.trim();
    const tbody = document.querySelector('.table tbody');
    if (!tbody) {
      alert('Tabel laporan tidak ditemukan.');
      return;
    }
    const rows = tbody.querySelectorAll('tr');
    if (rows.length === 0) {
      alert('Tidak ada data laporan untuk dicetak. Silakan filter tanggal terlebih dahulu.');
      return;
    }
    const tableRows = Array.from(rows).map(row => row.outerHTML).join('');

    const printWindow = window.open('', '_blank');
    if (!printWindow) {
      alert('Popup diblokir. Silakan izinkan popup untuk situs ini.');
      return;
    }
    const printContent = `
      <!DOCTYPE html>
      <html>
      <head>
        <title>Laporan Penjualan - Toko Donny</title>
        <style>
          body { 
            font-family: monospace; 
            font-size: 12px; 
            margin: 0; 
            padding: 20px; 
            width: 100%; 
            max-width: 210mm; 
            margin: 0 auto; 
          }
          .report-header { font-weight: bold; font-size: 18px; text-align: center; margin-bottom: 20px; }
          .summary { display: flex; justify-content: space-around; margin-bottom: 20px; }
          .summary div { text-align: center; border: 1px solid #000; padding: 10px; width: 30%; }
          table { width: 100%; border-collapse: collapse; margin-top: 20px; }
          th, td { border: 1px solid #000; padding: 5px; text-align: center; }
          th { background-color: #f2f2f2; }
          .report-footer { font-size: 10px; text-align: center; margin-top: 20px; font-style: italic; }
          .period { text-align: center; font-weight: bold; margin-bottom: 10px; }
          @media print {
            body { margin: 0; padding: 10px; }
            @page { margin: 1cm; size: A4 portrait; }
            .summary { page-break-inside: avoid; }
            table { page-break-inside: auto; }
          }
        </style>
      </head>
      <body>
        <div class="report-header">Toko Donny</div>
        <div class="period">Laporan Penjualan ${label}</div>
        <div class="summary">
          <div><strong>Total Transaksi:</strong> ${totalTransaksi}</div>
          <div><strong>Total Pendapatan:</strong> ${totalPendapatan}</div>
          <div><strong>Barang Terlaris:</strong> ${barangTerlaris}</div>
        </div>
        <table>
          <thead><tr><th>ID</th><th>Pembeli</th><th>Barang</th><th>Jumlah</th><th>Total</th><th>Tanggal</th></tr></thead>
          <tbody>${tableRows}</tbody>
        </table>
        <div class="report-footer">Terimakasih telah berbelanja di Toko Donny</div>
        <script>
          window.onload = function() { 
            setTimeout(function() { window.print(); }, 500); 
            setTimeout(function() { window.close(); }, 1000); 
          }
        </script>
      </body>
      </html>
    `;
    printWindow.document.write(printContent);
    printWindow.document.close();
  } catch (error) {
    console.error('Error in printReport:', error);
    alert('Terjadi kesalahan saat mencetak laporan: ' + error.message);
  }
}



