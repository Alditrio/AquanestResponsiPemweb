<?php
// Helper tampilan kecil (SVG + daftar pilihan) – tanpa framework.

function logoMark(string $class = ''): string {
    return '<svg class="' . e($class) . '" viewBox="0 0 40 40" role="img" aria-label="Logo AQUANEST">'
         . '<rect width="40" height="40" rx="11" fill="#0D55AD"/>'
         . '<path d="M0 26c5-4 10-4 15 0s10 4 15 0 7-3 10-1v15H0z" fill="#8CC1E9"/>'
         . '<path d="M0 31c5-4 10-4 15 0s10 4 15 0 7-3 10-1v10H0z" fill="#438BC4" opacity=".7"/>'
         . '<ellipse cx="19" cy="15" rx="8" ry="4.6" fill="#FFF8E7"/>'
         . '<path d="M26 15l6-4.5v9z" fill="#FFF8E7"/><circle cx="15" cy="14" r="1.2" fill="#0D55AD"/>'
         . '</svg>';
}

function icoArrow(): string {
    return '<svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>';
}
function icoPlay(): string {
    return '<svg class="ico" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.5v13l11-6.5z"/></svg>';
}

function waveAccent(string $class = ''): string {
    return '<span class="wave-accent ' . e($class) . '" aria-hidden="true">'
         . '<svg viewBox="0 0 54 12" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">'
         . '<path d="M2 5c3.5-3.5 7-3.5 10.5 0s7 3.5 10.5 0 7-3.5 10.5 0 7 3.5 10.5 0 5-2.5 8-1" stroke-width="2.2" stroke-linecap="round"/>'
         . '<path d="M2 9.5c3.5-3.5 7-3.5 10.5 0s7 3.5 10.5 0 7-3.5 10.5 0 7 3.5 10.5 0 5-2.5 8-1" stroke-width="2.2" stroke-linecap="round" opacity="0.5"/>'
         . '</svg></span>';
}

function tipIconPaths(): array {
    return [
        'plastik'   => '<path d="M6 8h12l-1 12H7L6 8z"/><path d="M9 8a3 3 0 016 0"/>',
        'matahari'  => '<circle cx="12" cy="12" r="4"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M5 5l2 2M17 17l2 2M19 5l-2 2M7 17l-2 2"/>',
        'karang'    => '<path d="M12 21v-9M12 12c-3 0-4-3-4-6M12 12c3 0 4-3 4-6M12 16c-2.5 0-4-1.5-4-3.5M12 16c2.5 0 4-1.5 4-3.5"/>',
        'mata'      => '<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
        'ikan'      => '<path d="M3 12c3-5 8-6 12-3l6-3v12l-6-3c-4 3-9 2-12-3z"/><circle cx="8.5" cy="11" r=".8" fill="currentColor"/>',
        'larangan'  => '<circle cx="12" cy="12" r="9"/><path d="M5.6 5.6l12.8 12.8"/>',
        'mangrove'  => '<path d="M12 21v-8M12 13c-4 0-6-3-6-6a6 6 0 0112 0c0 3-2 6-6 6zM8 21l2-3M16 21l-2-3"/>',
        'sampah'    => '<path d="M5 7h14M9 7V4h6v3M7 7l1 13h8l1-13M10 11v6M14 11v6"/>',
        'jangkar'   => '<circle cx="12" cy="5" r="2"/><path d="M12 7v14M6 12H4c0 5 3.5 9 8 9s8-4 8-9h-2M8 11h8"/>',
        'energi'    => '<path d="M13 2L5 14h6l-1 8 8-12h-6l1-8z"/>',
        'telepon'   => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 005 5L15 13l5 2v4a2 2 0 01-2 2A16 16 0 013 6a2 2 0 012-2z"/>',
        'komunitas' => '<circle cx="9" cy="8" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M3 20c0-3.5 2.7-6 6-6s6 2.5 6 6M15 14.5c3 0 6 2 6 5.5"/>',
        'daun'      => '<path d="M5 19C5 10 11 5 20 4c0 9-5 15-14 15zM5 19l8-8"/>',
    ];
}
function tipIcon(string $key, string $class = 'tip-ico'): string {
    $paths = tipIconPaths();
    $p = $paths[$key] ?? $paths['daun'];
    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
}
function tipCategories(): array { return ['Sehari-hari', 'Wisata Bahari', 'Konsumen', 'Pesisir', 'Komunitas']; }
function tipDifficulties(): array { return ['Mudah', 'Sedang', 'Menantang']; }

function statusBadge(string $code, ?string $label = null): string {
    $code = strtoupper(trim($code));
    $cls = 'status-' . strtolower($code);
    $text = $label ? e($code . ' (' . $label . ')') : e($code);
    return '<span class="badge-status ' . $cls . '">' . $text . '</span>';
}

function tipImage(array $t): string {
    $tipImages = [
        1  => 'assets/images/education/edukasi-plastik.jpg',
        2  => 'assets/images/habitats/terumbu-karang.jpg',
        3  => 'assets/images/education/edukasi-terumbu.jpg',
        4  => 'assets/images/species/hiu-paus.jpg',
        5  => 'assets/images/species/ikan-tuna.jpg',
        6  => 'assets/images/species/penyu-sisik.jpg',
        7  => 'assets/images/habitats/hutan-mangrove.jpg',
        8  => 'assets/images/education/edukasi-konservasi.jpg',
        9  => 'assets/images/habitats/padang-lamun.jpg',
        10 => 'assets/images/species/ikan-badut.jpg',
        11 => 'assets/images/education/edukasi-penyu.jpg',
        12 => 'assets/images/species/pari-manta.jpg',
    ];
    $id = (int)($t['id'] ?? 0);
    if (isset($tipImages[$id])) {
        return $tipImages[$id];
    }
    if (!empty($t['species_image'])) {
        return ltrim($t['species_image'], '/');
    }
    if (!empty($t['habitat_image'])) {
        return ltrim($t['habitat_image'], '/');
    }
    return 'assets/images/hero-ocean-fresh.jpg';
}

function icoTitleWave(string $class = 'title-wave'): string {
    return '<svg class="' . e($class) . '" viewBox="0 0 38 14" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true">'
         . '<path d="M2 4.5c3.2-3 6.4-3 9.6 0s6.4 3 9.6 0 6.4-3 9.6 0 4-1.5 5.8-1"/>'
         . '<path d="M2 10.5c3.2-3 6.4-3 9.6 0s6.4 3 9.6 0 6.4-3 9.6 0 4-1.5 5.8-1"/>'
         . '</svg>';
}


