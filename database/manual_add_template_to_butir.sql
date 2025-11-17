-- Manual SQL untuk menambahkan form template ke butir tertentu
-- Gunakan ini jika Anda ingin langsung apply template tanpa migration

-- =======================================================
-- CARA MENGGUNAKAN:
-- 1. Cek dulu butir_akreditasis yang ada:
--    SELECT id, kode, nama FROM butir_akreditasis;
--
-- 2. Update ID di query dibawah sesuai butir yang ingin ditambahkan template
-- 3. Jalankan query UPDATE yang sesuai
-- =======================================================


-- =======================================================
-- TEMPLATE 1: LUARAN PKM (Table Form)
-- =======================================================
-- Ganti ID=999 dengan ID butir yang sebenarnya
UPDATE butir_akreditasis
SET metadata = JSON_SET(
    COALESCE(metadata, '{}'),
    '$.form_config', JSON_OBJECT(
        'type', 'table',
        'label', 'Data Luaran Pengabdian kepada Masyarakat',
        'help_text', 'Isikan seluruh luaran dari kegiatan PKM (publikasi, produk, HKI, dll) dalam periode akreditasi',
        'columns', JSON_ARRAY(
            JSON_OBJECT(
                'name', 'jenis_luaran',
                'label', 'Jenis Luaran',
                'type', 'select',
                'required', true,
                'width', '15%',
                'options', JSON_OBJECT(
                    'publikasi', 'Publikasi',
                    'produk', 'Produk/Karya',
                    'hki', 'HKI/Paten',
                    'model', 'Model/Prototype',
                    'buku', 'Buku Ajar/Modul'
                )
            ),
            JSON_OBJECT(
                'name', 'judul',
                'label', 'Judul/Nama Luaran',
                'type', 'text',
                'required', true,
                'width', '25%',
                'placeholder', 'Masukkan judul/nama luaran...',
                'max_length', 500
            ),
            JSON_OBJECT(
                'name', 'tahun',
                'label', 'Tahun',
                'type', 'number',
                'required', true,
                'width', '8%',
                'min', 2020,
                'max', 2030
            ),
            JSON_OBJECT(
                'name', 'nama_dosen',
                'label', 'Nama Dosen',
                'type', 'text',
                'required', true,
                'width', '18%'
            ),
            JSON_OBJECT(
                'name', 'kegiatan_pkm',
                'label', 'Kegiatan PKM Terkait',
                'type', 'text',
                'required', true,
                'width', '20%',
                'placeholder', 'Nama kegiatan PKM yang menghasilkan luaran ini'
            ),
            JSON_OBJECT(
                'name', 'url',
                'label', 'Link/URL',
                'type', 'text',
                'required', false,
                'width', '14%',
                'placeholder', 'https://...'
            )
        ),
        'validations', JSON_OBJECT(
            'min_rows', 1,
            'max_rows', 150
        ),
        'options', JSON_OBJECT(
            'allow_add', true,
            'allow_delete', true,
            'show_summary', true
        )
    )
)
WHERE id = 999; -- GANTI DENGAN ID BUTIR YANG SEBENARNYA

-- ATAU gunakan nama butir:
-- WHERE nama LIKE '%Luaran%PKM%';


-- =======================================================
-- TEMPLATE 2: PKM (Table Form)
-- =======================================================
UPDATE butir_akreditasis
SET metadata = JSON_SET(
    COALESCE(metadata, '{}'),
    '$.form_config', JSON_OBJECT(
        'type', 'table',
        'label', 'Data Pengabdian kepada Masyarakat',
        'columns', JSON_ARRAY(
            JSON_OBJECT('name', 'judul', 'label', 'Judul Kegiatan PKM', 'type', 'text', 'required', true, 'width', '30%'),
            JSON_OBJECT('name', 'tahun', 'label', 'Tahun', 'type', 'number', 'required', true, 'width', '10%', 'min', 2020, 'max', 2030),
            JSON_OBJECT('name', 'nama_dosen', 'label', 'Nama Dosen', 'type', 'text', 'required', true, 'width', '20%'),
            JSON_OBJECT('name', 'mitra', 'label', 'Mitra PKM', 'type', 'text', 'required', true, 'width', '20%'),
            JSON_OBJECT('name', 'dana', 'label', 'Dana (Rp)', 'type', 'currency', 'required', false, 'width', '15%'),
            JSON_OBJECT(
                'name', 'status', 'label', 'Status', 'type', 'select', 'required', true, 'width', '10%',
                'options', JSON_OBJECT('selesai', 'Selesai', 'berjalan', 'Sedang Berjalan', 'rencana', 'Rencana')
            )
        ),
        'validations', JSON_OBJECT('min_rows', 1, 'max_rows', 100),
        'options', JSON_OBJECT('allow_add', true, 'allow_delete', true, 'show_summary', true)
    )
)
WHERE nama LIKE '%Pengabdian%Masyarakat%';


-- =======================================================
-- TEMPLATE 3: PUBLIKASI (Table Form)
-- =======================================================
UPDATE butir_akreditasis
SET metadata = JSON_SET(
    COALESCE(metadata, '{}'),
    '$.form_config', JSON_OBJECT(
        'type', 'table',
        'label', 'Data Publikasi Ilmiah Dosen',
        'columns', JSON_ARRAY(
            JSON_OBJECT('name', 'judul_artikel', 'label', 'Judul Artikel', 'type', 'text', 'required', true, 'width', '30%'),
            JSON_OBJECT('name', 'nama_dosen', 'label', 'Nama Dosen', 'type', 'text', 'required', true, 'width', '18%'),
            JSON_OBJECT('name', 'nama_jurnal', 'label', 'Nama Jurnal', 'type', 'text', 'required', true, 'width', '22%'),
            JSON_OBJECT('name', 'tahun', 'label', 'Tahun', 'type', 'number', 'required', true, 'width', '8%', 'min', 2020, 'max', 2030),
            JSON_OBJECT(
                'name', 'tingkat', 'label', 'Tingkat', 'type', 'select', 'required', true, 'width', '15%',
                'options', JSON_OBJECT(
                    'internasional_bereputasi', 'Internasional Bereputasi',
                    'internasional', 'Internasional',
                    'nasional_terakreditasi', 'Nasional Terakreditasi',
                    'nasional', 'Nasional'
                )
            ),
            JSON_OBJECT('name', 'url', 'label', 'Link/DOI', 'type', 'text', 'required', false, 'width', '12%')
        ),
        'validations', JSON_OBJECT('min_rows', 1, 'max_rows', 200),
        'options', JSON_OBJECT('allow_add', true, 'allow_delete', true, 'show_summary', true)
    )
)
WHERE nama LIKE '%Publikasi%';


-- =======================================================
-- CEK HASILNYA
-- =======================================================
-- Lihat butir mana saja yang sudah punya form template
SELECT
    id,
    kode,
    nama,
    JSON_EXTRACT(metadata, '$.form_config.type') as form_type,
    JSON_EXTRACT(metadata, '$.form_config.label') as form_label
FROM butir_akreditasis
WHERE JSON_EXTRACT(metadata, '$.form_config') IS NOT NULL;


-- =======================================================
-- HAPUS TEMPLATE (jika ingin reset)
-- =======================================================
-- UPDATE butir_akreditasis
-- SET metadata = JSON_REMOVE(metadata, '$.form_config')
-- WHERE id = 999; -- ganti dengan ID yang sesuai
