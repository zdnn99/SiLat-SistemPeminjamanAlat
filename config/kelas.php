<?php

// Daftar kelas & jurusan. Ubah di sini kalau ada penambahan atau perubahan kelas.
$jurusanX = ['TKP', 'DPIB A', 'DPIB B', 'PPLG A', 'PPLG B', 'PPLG C', 'TK A', 'TK B', 'TE A', 'TE B', 'TE C', 'TE D', 'TE E', 'TM A', 'TM B', 'TM C', 'TM D', 'TO A', 'TO B', 'TO C', 'TO D'];
$jurusanXiXii = ['TKP', 'DPIB A', 'DPIB B', 'RPL A', 'RPL B', 'RPL C', 'TITL A', 'TITL B', 'TEI A', 'TEI B', 'TEI C', 'TOI A', 'TOI B', 'TP A', 'TP B', 'TP C', 'TP D', 'TKR A', 'TKR B', 'TKR C', 'TSM A', 'TSM B'];

return [
    'daftar' => [
        'X' => array_map(fn ($j) => 'X ' . $j, $jurusanX),
        'XI' => array_map(fn ($j) => 'XI ' . $j, $jurusanXiXii),
        'XII' => array_map(fn ($j) => 'XII ' . $j, $jurusanXiXii),
    ],
];