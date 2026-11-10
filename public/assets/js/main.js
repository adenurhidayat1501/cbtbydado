/**
 * GarudaCBT Rebuild — Main JavaScript Framework
 * Handles UI interactions, KaTeX auto-render, DataTables defaults, and TP/SMT switches
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Sidebar Toggle & Persistence
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    if (sidebarToggleBtn) {
        // Restore previous state
        if (localStorage.getItem('gcbt-sidebar-collapsed') === 'true' && window.innerWidth >= 992) {
            document.body.classList.add('sidebar-collapsed');
        }

        sidebarToggleBtn.addEventListener('click', function (e) {
            e.preventDefault();
            if (window.innerWidth < 992) {
                document.body.classList.toggle('sidebar-mobile-show');
            } else {
                document.body.classList.toggle('sidebar-collapsed');
                const isCollapsed = document.body.classList.contains('sidebar-collapsed');
                localStorage.setItem('gcbt-sidebar-collapsed', isCollapsed);
            }
        });
    }

    // 2. KaTeX Math Equation Auto-Render
    if (typeof renderMathInElement === 'function') {
        renderMathInElement(document.body, {
            delimiters: [
                { left: '$$', right: '$$', display: true },
                { left: '$', right: '$', display: false },
                { left: '\\(', right: '\\)', display: false },
                { left: '\\[', right: '\\]', display: true }
            ],
            throwOnError: false
        });
    }

    // 3. DataTables Indonesian Defaults (if jQuery & DataTables loaded)
    if (window.jQuery && jQuery.fn.dataTable) {
        jQuery.extend(true, jQuery.fn.dataTable.defaults, {
            language: {
                search: "Cari data:",
                lengthMenu: "Tampilkan _MENU_ entri",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
                infoFiltered: "(disaring dari _MAX_ entri keseluruhan)",
                zeroRecords: "Tidak ada data yang cocok",
                emptyTable: "Belum ada data tersedia pada tabel",
                paginate: {
                    first: "Pertama",
                    previous: "Sebelumnya",
                    next: "Berikutnya",
                    last: "Terakhir"
                }
            },
            responsive: true,
            pageLength: 25
        });

        jQuery('.datatable').DataTable();
    }

    // 4. Summernote Helper Init (if jQuery & Summernote loaded)
    if (window.jQuery && jQuery.fn.summernote) {
        jQuery('.summernote-editor').summernote({
            height: 220,
            placeholder: 'Tuliskan isi teks, rumus, atau soal di sini...',
            toolbar: [
                ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['picture', 'link', 'table', 'hr']],
                ['view', ['fullscreen', 'codeview']]
            ]
        });
    }
});

// Helper for switching TP/SMT via AJAX or form
function changeActiveTpSmt(idTp, idSmt, isGlobal = 0) {
    const formData = new FormData();
    formData.append('id_tp', idTp);
    formData.append('id_smt', idSmt);
    formData.append('global', isGlobal);

    fetch('/settings/tp-smt/switch', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Gagal mengubah Tahun Pelajaran / Semester.');
        }
    })
    .catch(() => {
        window.location.reload();
    });
}

