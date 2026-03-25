<?php

// Referensi Standar WHO Tinggi/Panjang Badan menurut Umur (TB/U)
// Digunakan sesuai standar Kemenkes RI Permenkes No 2 Tahun 2020.
// Data berisi Median dan batas Standar Deviasi (-2SD dan -3SD).
// Karena tabel lengkap memiliki ratusan baris, ini merupakan data keypoints
// setiap 6 bulan yang akan diinterpolasi linear secara akurat pada level Model.

return [
    'key_months' => [0, 6, 12, 24, 36, 48, 60],

    'boys' => [
        0 => ['median' => 49.9, 'sd2neg' => 46.1, 'sd3neg' => 44.2],
        6 => ['median' => 67.6, 'sd2neg' => 63.3, 'sd3neg' => 61.2],
        12 => ['median' => 75.7, 'sd2neg' => 71.0, 'sd3neg' => 68.6],
        24 => ['median' => 87.8, 'sd2neg' => 81.7, 'sd3neg' => 78.7],
        36 => ['median' => 96.1, 'sd2neg' => 88.7, 'sd3neg' => 85.0],
        48 => ['median' => 103.3, 'sd2neg' => 94.9, 'sd3neg' => 90.7],
        60 => ['median' => 110.0, 'sd2neg' => 100.7, 'sd3neg' => 96.1],
    ],

    'girls' => [
        0 => ['median' => 49.1, 'sd2neg' => 45.4, 'sd3neg' => 43.6],
        6 => ['median' => 65.7, 'sd2neg' => 61.2, 'sd3neg' => 58.9],
        12 => ['median' => 74.0, 'sd2neg' => 68.9, 'sd3neg' => 66.3],
        24 => ['median' => 86.4, 'sd2neg' => 80.0, 'sd3neg' => 76.7],
        36 => ['median' => 95.1, 'sd2neg' => 87.4, 'sd3neg' => 83.6],
        48 => ['median' => 102.7, 'sd2neg' => 94.1, 'sd3neg' => 89.8],
        60 => ['median' => 109.4, 'sd2neg' => 99.9, 'sd3neg' => 95.2],
    ],
];
