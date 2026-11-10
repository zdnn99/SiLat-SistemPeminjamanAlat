@php
    $kelasData = config('kelas.daftar');
    $kelasNilai = $kelasNilai ?? '';
    $kelasAwal = $kelasAwal ?? '';
@endphp

<div id="form-kelas-jurusan">
    <label for="kelas_search" class="block text-sm font-medium text-gray-700 mb-1">Kelas & Jurusan</label>

    <div class="relative" id="kelas-wrapper">
        <!-- Nilai yang benar-benar dikirim ke server -->
        <input type="hidden" id="kelas_jurusan" name="kelas_jurusan" value="{{ $kelasNilai }}" data-awal="{{ $kelasAwal }}">

        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 pointer-events-none">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
            </svg>
        </span>

        <input id="kelas_search" type="text" autocomplete="off" role="combobox"
            aria-expanded="false" aria-controls="kelas-list"
            value="{{ $kelasNilai }}"
            placeholder="Cari atau pilih kelas Anda"
            class="w-full pl-11 pr-10 py-2.5 rounded-xl border {{ $errors->has('kelas_jurusan') ? 'border-red-500' : 'border-gray-300' }} focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition bg-white text-sm">

        <button type="button" id="kelas-toggle" tabindex="-1" aria-label="Buka daftar kelas"
            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
            <svg id="kelas-chevron" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <!-- Panel daftar kelas -->
        <div id="kelas-panel" class="hidden absolute left-0 right-0 top-full mt-2 bg-white rounded-xl shadow-lg border border-gray-200 z-30 overflow-hidden">
            <div id="kelas-chips" class="flex items-center gap-2 px-3 py-2.5 border-b border-gray-100 bg-gray-50">
                <button type="button" data-tingkat="semua">Semua</button>
                <button type="button" data-tingkat="X">Kelas X</button>
                <button type="button" data-tingkat="XI">Kelas XI</button>
                <button type="button" data-tingkat="XII">Kelas XII</button>
            </div>
            <div id="kelas-list" role="listbox" class="relative max-h-60 overflow-y-auto pb-1"></div>
        </div>
    </div>

    @error('kelas_jurusan')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>

<script>
    (function () {
        var kelasData = @json($kelasData);
        var kelasLabel = { X: 'Kelas X', XI: 'Kelas XI', XII: 'Kelas XII' };
        var kelasUrutan = ['X', 'XI', 'XII'];

        var kelasInput = document.getElementById('kelas_search');
        var kelasValue = document.getElementById('kelas_jurusan');
        var kelasPanel = document.getElementById('kelas-panel');
        var kelasList = document.getElementById('kelas-list');
        var kelasChips = document.getElementById('kelas-chips');
        var kelasChevron = document.getElementById('kelas-chevron');
        var kelasToggle = document.getElementById('kelas-toggle');
        var kelasWrapper = document.getElementById('kelas-wrapper');

        var kataCari = '';
        var tingkat = 'semua';
        var items = [];
        var indeks = -1;

        function terbuka() {
            return !kelasPanel.classList.contains('hidden');
        }

        // Menebalkan bagian nama yang cocok dengan ketikan
        function sorot(teks, kata) {
            if (!kata) return teks;
            var idx = teks.toLowerCase().indexOf(kata.toLowerCase());
            if (idx === -1) return teks;
            return teks.slice(0, idx) +
                '<span class="text-blue-600 font-bold">' + teks.slice(idx, idx + kata.length) + '</span>' +
                teks.slice(idx + kata.length);
        }

        function itemHtml(nama, kata) {
            var aktif = nama === kelasValue.value;
            return '<button type="button" role="option" data-nilai="' + nama + '"' + (aktif ? ' data-aktif="1"' : '') +
                ' class="kelas-item w-full text-left flex items-center justify-between px-4 py-2 text-sm transition hover:bg-blue-50 ' +
                (aktif ? 'text-blue-600 font-semibold' : 'text-gray-700') + '">' +
                '<span>' + sorot(nama, kata) + '</span>' +
                (aktif ? '<svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>' : '') +
                '</button>';
        }

        function perbaruiChip() {
            kelasChips.querySelectorAll('button').forEach(function (chip) {
                var aktif = chip.dataset.tingkat === tingkat;
                chip.className = 'px-3 py-1 rounded-full text-xs font-semibold border transition ' +
                    (aktif
                        ? 'bg-blue-600 text-white border-blue-600'
                        : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300 hover:text-blue-600');
            });
        }

        function setSorotan(i) {
            indeks = i;
            items.forEach(function (el, n) {
                el.classList.toggle('bg-blue-50', n === i);
            });
            if (i >= 0 && items[i]) {
                items[i].scrollIntoView({ block: 'nearest' });
            }
        }

        function render() {
            var kata = kataCari.trim();
            var kataNorm = kata.toLowerCase().replace(/\s+/g, '');
            var html = '';
            var adaHasil = false;

            kelasUrutan.forEach(function (t) {
                if (tingkat !== 'semua' && tingkat !== t) return;

                var hasil = kelasData[t].filter(function (nama) {
                    return !kataNorm || nama.toLowerCase().replace(/\s+/g, '').includes(kataNorm);
                });
                if (hasil.length === 0) return;

                adaHasil = true;
                html += '<div class="sticky top-0 z-10 bg-white px-4 pt-2 pb-1 text-[11px] font-bold text-gray-400 uppercase tracking-wide">' + kelasLabel[t] + '</div>';
                hasil.forEach(function (nama) {
                    html += itemHtml(nama, kata);
                });
            });

            if (!adaHasil) {
                html =
                    '<div class="flex flex-col items-center justify-center text-center py-6 px-4">' +
                        '<p class="text-sm font-semibold text-gray-500">Kelas tidak ditemukan</p>' +
                        '<p class="text-xs text-gray-400 mt-1">Coba ketik jurusan, contoh: TKR atau XI TP</p>' +
                    '</div>';
            }

            kelasList.innerHTML = html;
            items = Array.from(kelasList.querySelectorAll('.kelas-item'));
            setSorotan(kata && items.length ? 0 : -1);
            perbaruiChip();
        }

        function buka() {
            kelasPanel.classList.remove('hidden');
            kelasChevron.classList.add('rotate-180');
            kelasInput.setAttribute('aria-expanded', 'true');
            kelasPanel.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }

        function tutup() {
            kelasPanel.classList.add('hidden');
            kelasChevron.classList.remove('rotate-180');
            kelasInput.setAttribute('aria-expanded', 'false');
            // Mengetik hanya untuk mencari: kembalikan teks ke pilihan yang berlaku
            kelasInput.value = kelasValue.value;
        }

        function bukaBaru() {
            kataCari = '';
            tingkat = 'semua';
            render();
            buka();

            // Geser daftar ke posisi kelas yang sedang terpilih
            var aktif = kelasList.querySelector('[data-aktif="1"]');
            if (aktif) {
                kelasList.scrollTop = Math.max(0, aktif.offsetTop - 60);
            }
            kelasInput.select();
        }

        function pilih(nilai) {
            kelasValue.value = nilai;
            // Beri tahu form bahwa ada perubahan (untuk mengaktifkan tombol simpan)
            kelasValue.dispatchEvent(new Event('input', { bubbles: true }));
            tutup();
        }

        kelasInput.addEventListener('focus', function () {
            if (!terbuka()) bukaBaru();
        });

        kelasInput.addEventListener('click', function () {
            if (!terbuka()) bukaBaru();
        });

        kelasInput.addEventListener('input', function (e) {
            e.stopPropagation(); // ketikan pencarian bukan perubahan data
            kataCari = kelasInput.value;
            if (!terbuka()) buka();
            render();
        });

        kelasInput.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (!terbuka()) bukaBaru();
                if (items.length) setSorotan(Math.min(indeks + 1, items.length - 1));
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (items.length) setSorotan(Math.max(indeks - 1, 0));
            } else if (e.key === 'Enter') {
                if (terbuka()) {
                    e.preventDefault();
                    if (indeks >= 0 && items[indeks]) {
                        pilih(items[indeks].dataset.nilai);
                    } else if (items.length === 1) {
                        pilih(items[0].dataset.nilai);
                    }
                }
            } else if (e.key === 'Escape' || e.key === 'Tab') {
                if (terbuka()) tutup();
            }
        });

        kelasToggle.addEventListener('click', function () {
            if (terbuka()) {
                tutup();
            } else {
                bukaBaru();
                kelasInput.focus();
            }
        });

        kelasChips.addEventListener('click', function (e) {
            var chip = e.target.closest('button');
            if (!chip) return;
            tingkat = chip.dataset.tingkat;
            render();
            kelasInput.focus();
        });

        kelasList.addEventListener('click', function (e) {
            var item = e.target.closest('.kelas-item');
            if (item) pilih(item.dataset.nilai);
        });

        document.addEventListener('click', function (e) {
            if (terbuka() && !kelasWrapper.contains(e.target)) tutup();
        });

        kelasInput.value = kelasValue.value;
        perbaruiChip();
    })();
</script>