(function () {
    "use strict";

    const MAKS_FOTO = 2 * 1024 * 1024;
    const TIPE_FOTO = ["image/jpeg", "image/png", "image/webp"];
    const EKSTENSI_FOTO = ["jpg", "jpeg", "png", "webp"];
    const MAKS_TEKS = 255;
    const formatAngka = new Intl.NumberFormat("id-ID");

    function siap(fn) {
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", fn);
        } else {
            fn();
        }
    }

    /* ------------------------------------------------------------------ *
     *  Utilitas umum
     * ------------------------------------------------------------------ */

    function angka(nilai) {
        return formatAngka.format(nilai);
    }

    function denganSatuan(nilai, satuan) {
        return angka(nilai) + (satuan ? " " + satuan : "");
    }

    function ukuranFile(bytes) {
        if (bytes >= 1024 * 1024) {
            return (bytes / (1024 * 1024)).toFixed(1).replace(".", ",") + " MB";
        }
        return Math.max(1, Math.round(bytes / 1024)) + " KB";
    }

    function swal(opsi) {
        if (window.Swal) {
            return window.Swal.fire(
                Object.assign(
                    {
                        confirmButtonText: "Mengerti",
                        customClass: { popup: "fe-swal-compact" },
                    },
                    opsi,
                ),
            );
        }
        window.alert([opsi.title, opsi.text].filter(Boolean).join("\n\n"));
        return Promise.resolve({ isConfirmed: true });
    }

    function wadah(field) {
        return field.closest("[data-tx-field]") || field.parentElement;
    }

    function hapusError(field) {
        const holder = wadah(field);
        holder
            .querySelectorAll(".tx-field-error, .tx-field-hint")
            .forEach((n) => n.remove());
        holder
            .querySelectorAll(".is-invalid")
            .forEach((n) => n.classList.remove("is-invalid"));
        holder
            .querySelectorAll("[aria-invalid]")
            .forEach((n) => n.removeAttribute("aria-invalid"));
        holder.classList.remove("is-invalid-group");
    }

    function pasangError(field, pesan, target) {
        hapusError(field);
        const holder = wadah(field);
        const sasaran = target || field;
        sasaran.classList.add("is-invalid");
        if (sasaran.matches("input, select, textarea")) {
            sasaran.setAttribute("aria-invalid", "true");
        }
        if (holder.matches("fieldset, .tx-check")) {
            holder.classList.add("is-invalid-group");
        }
        const node = document.createElement("div");
        node.className = "tx-field-error";
        node.setAttribute("role", "alert");
        node.textContent = pesan;
        holder.appendChild(node);
    }

    function labelDari(field) {
        return (
            field.dataset.label ||
            wadah(field).dataset.label ||
            field.name ||
            "Kolom ini"
        );
    }

    /**
     * Tampilkan hasil validasi: pesan di bawah tiap field + ringkasan SweetAlert.
     * @param {{field: Element, pesan: string, target?: Element}[]} daftar
     */
    function tampilkanKesalahan(daftar) {
        daftar.forEach((e) => pasangError(e.field, e.pesan, e.target));
        const pertama = daftar[0];
        const kandidat = pertama.target || pertama.field;
        const fokus = kandidat.matches("input, select, textarea, button")
            ? kandidat
            : kandidat.querySelector("input:not([type=hidden]), select, textarea, button");
        if (fokus && typeof fokus.focus === "function") {
            fokus.focus({ preventScroll: true });
        }
        (wadah(pertama.field) || fokus).scrollIntoView({
            block: "center",
            behavior: "smooth",
        });
        const sisa = daftar.length - 1;
        swal({
            icon: "warning",
            title: "Periksa formulir",
            text:
                pertama.pesan +
                (sisa > 0 ? " (+" + sisa + " kolom lain perlu diperbaiki)" : ""),
        });
    }

    /** Cegah klik ganda: kunci tombol submit dan beri label "Menyimpan…". */
    function kunciTombol(form) {
        form.querySelectorAll("[data-tx-submit]").forEach((btn) => {
            btn.dataset.txLabel = btn.innerHTML;
            btn.disabled = true;
            btn.textContent = "Menyimpan…";
        });
    }

    function bukaKunciTombol(form) {
        form.querySelectorAll("[data-tx-submit]").forEach((btn) => {
            if (btn.dataset.txLabel) {
                btn.innerHTML = btn.dataset.txLabel;
                delete btn.dataset.txLabel;
            }
            btn.disabled = false;
        });
    }

    let jamServer = null;

    function siapkanJamServer(page) {
        const serverMs = parseInt(page.dataset.txServerNow || "", 10);
        const offsetMenit = parseInt(page.dataset.txTzOffset || "0", 10);
        const dimuatPada = Date.now();
        jamServer = {
            sekarang: () =>
                (isNaN(serverMs) ? Date.now() : serverMs + (Date.now() - dimuatPada)),
            offsetMenit: isNaN(offsetMenit) ? 0 : offsetMenit,
        };
    }

    function epochDariInput(nilai) {
        const m = /^(\d{4})-(\d{2})-(\d{2})(?:[T ](\d{2}):(\d{2}))?/.exec(
            nilai || "",
        );
        if (!m) return NaN;
        const utc = Date.UTC(+m[1], +m[2] - 1, +m[3], +(m[4] || 0), +(m[5] || 0));
        return utc - jamServer.offsetMenit * 60000;
    }

    function cekTanggal(field) {
        const label = labelDari(field);
        if (!field.value) return label + " wajib diisi.";
        const epoch = epochDariInput(field.value);
        if (isNaN(epoch)) return "Format " + label.toLowerCase() + " tidak valid.";
        if (epoch > jamServer.sekarang() + 60000) {
            return label + " tidak boleh melebihi waktu sekarang.";
        }
        return null;
    }

    /* ------------------------------------------------------------------ *
     *  Validasi kuantitas & stok 
     * ------------------------------------------------------------------ */

    function infoBarang(select) {
        const opsi = select && select.selectedOptions ? select.selectedOptions[0] : null;
        if (!opsi || !opsi.value) return null;
        return {
            id: opsi.value,
            stok: parseInt(opsi.dataset.stok || "0", 10) || 0,
            minimum: parseInt(opsi.dataset.minimum || "0", 10) || 0,
            satuan: opsi.dataset.satuan || "",
            nama: opsi.dataset.nama || opsi.textContent.trim(),
            kode: opsi.dataset.kode || "",
        };
    }

    /** Nilai jumlah sebagai bilangan bulat; NaN bila kosong/tidak valid. */
    function nilaiJumlah(input) {
        if (input.validity && input.validity.badInput) return NaN;
        const teks = String(input.value).trim();
        if (!/^\d+$/.test(teks)) return NaN;
        return parseInt(teks, 10);
    }

    /**
     * @param {HTMLInputElement} input
     * @param {number|null} stokMaks  batas atas (stok baik) atau null bila tidak dibatasi
     */
    function cekJumlah(input, stokMaks, satuan) {
        const label = labelDari(input);
        const teks = String(input.value).trim();
        if (teks === "" && !(input.validity && input.validity.badInput)) {
            return label + " wajib diisi.";
        }
        if (input.validity && input.validity.badInput) {
            return label + " harus berupa angka.";
        }
        if (/^-/.test(teks)) return label + " tidak boleh negatif.";
        if (!/^\d+$/.test(teks)) return label + " harus berupa bilangan bulat.";
        const jumlah = parseInt(teks, 10);
        if (jumlah < 1) return label + " minimal 1.";
        if (stokMaks !== null && jumlah > stokMaks) {
            return (
                "Stok tidak mencukupi. Stok baik tersedia hanya " +
                denganSatuan(stokMaks, satuan) +
                "."
            );
        }
        return null;
    }

    function cekWajib(field, maksKarakter) {
        const label = labelDari(field);
        const nilai = String(field.value || "").trim();
        if (nilai === "") {
            return field.tagName === "SELECT"
                ? label + " wajib dipilih."
                : label + " wajib diisi.";
        }
        if (maksKarakter && nilai.length > maksKarakter) {
            return label + " maksimal " + maksKarakter + " karakter.";
        }
        return null;
    }

    /* ------------------------------------------------------------------ *
     *  Panel cek ketersediaan stok (barang keluar & peminjaman)
     * ------------------------------------------------------------------ */

    function pasangPanelStok(form) {
        const select = form.querySelector("[data-tx-barang]");
        const jumlah = form.querySelector("[data-tx-jumlah]");
        const panel = form.querySelector("[data-tx-stock-panel]");
        if (!select || !jumlah || !panel) return () => {};

        const el = (nama) => panel.querySelector("[data-tx-stock-" + nama + "]");
        const tulis = (nama, teks) => {
            const node = el(nama);
            if (node) node.textContent = teks;
        };

        function perbarui() {
            const info = infoBarang(select);
            panel.classList.remove("is-warning", "is-danger");
            const status = el("status");
            status.className = "fe-badge fe-badge-gray";

            if (!info) {
                jumlah.removeAttribute("max");
                tulis("name", "Belum ada barang dipilih.");
                ["available", "request", "remaining", "minimum"].forEach((n) => tulis(n, "—"));
                el("meter").style.width = "0%";
                status.textContent = "Pilih barang";
                tulis("note", "Jumlah tidak boleh melebihi stok baik yang tersedia.");
                return;
            }

            jumlah.max = String(info.stok);
            const diminta = nilaiJumlah(jumlah);
            const valid = !isNaN(diminta) && diminta >= 1;
            const sisa = valid ? info.stok - diminta : info.stok;

            tulis("name", (info.kode ? info.kode + " · " : "") + info.nama);
            tulis("available", denganSatuan(info.stok, info.satuan));
            tulis("request", valid ? denganSatuan(diminta, info.satuan) : "—");
            tulis("remaining", sisa < 0 ? "Kurang " + denganSatuan(-sisa, info.satuan) : denganSatuan(sisa, info.satuan));
            tulis("minimum", denganSatuan(info.minimum, info.satuan));

            const persen = info.stok > 0 ? Math.min(100, Math.max(0, (sisa / info.stok) * 100)) : 0;
            el("meter").style.width = (valid ? persen : 100) + "%";

            if (valid && sisa < 0) {
                panel.classList.add("is-danger");
                status.className = "fe-badge fe-badge-red";
                status.textContent = "Stok tidak cukup";
                tulis("note", "Jumlah melebihi stok baik. Maksimal " + denganSatuan(info.stok, info.satuan) + ".");
            } else if (valid && sisa === 0) {
                panel.classList.add("is-warning");
                status.className = "fe-badge fe-badge-amber";
                status.textContent = "Stok akan habis";
                tulis("note", "Setelah transaksi ini stok baik barang ini menjadi 0.");
            } else if (valid && info.minimum > 0 && sisa < info.minimum) {
                panel.classList.add("is-warning");
                status.className = "fe-badge fe-badge-amber";
                status.textContent = "Di bawah minimum";
                tulis("note", "Sisa stok akan berada di bawah batas minimum " + denganSatuan(info.minimum, info.satuan) + ". Pertimbangkan restok.");
            } else {
                status.className = "fe-badge fe-badge-green";
                status.textContent = "Stok tersedia";
                tulis("note", valid ? "Stok mencukupi untuk transaksi ini." : "Isi jumlah untuk melihat sisa stok.");
            }
        }

        select.addEventListener("change", perbarui);
        jumlah.addEventListener("input", perbarui);
        perbarui();
        return perbarui;
    }

    /* ------------------------------------------------------------------ *
     *  Upload & preview foto bukti 
     * ------------------------------------------------------------------ */

    function cekFoto(file, label, wajib) {
        if (!file) {
            return wajib ? label + " wajib dilampirkan sebagai bukti." : null;
        }
        const tipe = String(file.type || "").toLowerCase();
        const ekstensi = String(file.name || "").split(".").pop().toLowerCase();
        const tipeOk = tipe ? TIPE_FOTO.includes(tipe) : EKSTENSI_FOTO.includes(ekstensi);
        if (!tipeOk) return label + " harus berformat JPG, PNG, atau WebP.";
        if (file.size > MAKS_FOTO) {
            return "Ukuran " + label.toLowerCase() + " maksimal 2 MB (file ini " + ukuranFile(file.size) + ").";
        }
        return null;
    }

    /** Terjemahkan error validasi FilePond (main/sub) ke pesan yang konsisten. */
    function pesanErrorFilePond(error, label) {
        const teks = String((error && (error.main || error.body)) || "").toLowerCase();
        if (teks.includes("besar")) return "Ukuran " + label.toLowerCase() + " maksimal 2 MB.";
        if (teks.includes("format") || teks.includes("type")) return label + " harus berformat JPG, PNG, atau WebP.";
        return label + " tidak bisa dibaca. Coba pilih file lain.";
    }

    let pluginFilePondTerdaftar = false;

    function daftarPluginFilePond() {
        if (pluginFilePondTerdaftar) return;
        const plugin = [
            window.FilePondPluginFileValidateType,
            window.FilePondPluginFileValidateSize,
            window.FilePondPluginImagePreview,
        ].filter(Boolean);
        if (plugin.length) window.FilePond.registerPlugin.apply(null, plugin);
        pluginFilePondTerdaftar = true;
    }

    function pasangUpload(holder) {
        const input = holder.querySelector("input[type=file][data-tx-upload]");
        if (!input) return;
        const label = holder.dataset.label || "Foto bukti";
        const kotakPreview = holder.querySelector("[data-tx-upload-preview]");

        if (window.FilePond && window.FilePond.supported()) {
            daftarPluginFilePond();
            input.removeAttribute("class");
            input.removeAttribute("required");
            const pond = window.FilePond.create(input, {
                name: input.name,
                storeAsFile: true,
                allowMultiple: false,
                maxFiles: 1,
                credits: false,
                acceptedFileTypes: TIPE_FOTO,
                maxFileSize: MAKS_FOTO,
                imagePreviewHeight: 180,
                labelIdle:
                    'Seret &amp; lepas foto di sini atau <span class="filepond--label-action">pilih file</span>',
                labelFileLoading: "Memuat",
                labelFileLoadError: "Gagal memuat file",
                labelFileTypeNotAllowed: "Format file tidak didukung",
                fileValidateTypeLabelExpectedTypes: "Gunakan JPG, PNG, atau WebP",
                labelMaxFileSizeExceeded: "Ukuran file terlalu besar",
                labelMaxFileSize: "Maksimal 2 MB",
                labelButtonRemoveItem: "Hapus",
                labelButtonAbortItemLoad: "Batal",
                labelButtonRetryItemLoad: "Coba lagi",
                labelTapToCancel: "ketuk untuk batal",
                labelTapToRetry: "ketuk untuk mencoba lagi",
                labelTapToUndo: "ketuk untuk membatalkan",
                labelInvalidField: "Kolom berisi file yang tidak valid",
            });
            if (kotakPreview) kotakPreview.remove();
            const ambilFile = () => {
                const item = pond.getFile();
                return item ? item.file : null;
            };
            pond.on("addfile", (error, item) => {
                const file = item ? item.file : null;
                let pesan = cekFoto(file, label, true);
                if (error && (!file || !pesan)) pesan = pesanErrorFilePond(error, label);
                if (pesan) pasangError(holder, pesan, pond.element);
                else hapusError(holder);
            });
            pond.on("removefile", () => hapusError(holder));

            holder.txUpload = {
                file: ambilFile,
                reset: () => pond.removeFiles(),
                target: () => pond.element,
                pond: pond,
            };
            return;
        }

        const img = kotakPreview ? kotakPreview.querySelector("img") : null;
        let urlPreview = null;

        function sembunyikanPreview() {
            if (urlPreview) URL.revokeObjectURL(urlPreview);
            urlPreview = null;
            if (kotakPreview) kotakPreview.classList.add("d-none");
        }

        input.addEventListener("change", () => {
            sembunyikanPreview();
            const file = input.files && input.files[0];
            if (!file) return;
            const pesan = cekFoto(file, label, true);
            if (pesan) {
                input.value = "";
                pasangError(input, pesan);
                return;
            }
            hapusError(input);
            if (kotakPreview && img) {
                urlPreview = URL.createObjectURL(file);
                img.src = urlPreview;
                kotakPreview.querySelector("[data-tx-upload-name]").textContent = file.name;
                kotakPreview.querySelector("[data-tx-upload-size]").textContent = ukuranFile(file.size);
                kotakPreview.classList.remove("d-none");
            }
        });
        const tombolHapus = kotakPreview && kotakPreview.querySelector("[data-tx-upload-clear]");
        if (tombolHapus) {
            tombolHapus.addEventListener("click", () => {
                input.value = "";
                sembunyikanPreview();
                input.focus();
            });
        }

        holder.txUpload = {
            file: () => (input.files && input.files[0]) || null,
            reset: () => {
                input.value = "";
                sembunyikanPreview();
            },
            target: () => input,
            pond: null,
        };
    }

    function cekSemuaUpload(form, daftar) {
        form.querySelectorAll("[data-tx-upload-field]").forEach((holder) => {
            if (!holder.txUpload) return;
            const pesan = cekFoto(holder.txUpload.file(), holder.dataset.label || "Foto bukti", true);
            if (pesan) daftar.push({ field: holder, pesan: pesan, target: holder.txUpload.target() });
        });
    }

    function resetSemuaUpload(form) {
        form.querySelectorAll("[data-tx-upload-field]").forEach((holder) => {
            if (holder.txUpload) holder.txUpload.reset();
            hapusError(holder);
        });
    }

    /* ------------------------------------------------------------------ *
     *  No. 19 — Multi-item barang masuk
     * ------------------------------------------------------------------ */

    function pasangFormMasuk(form) {
        const tbody = form.querySelector("[data-tx-items]");
        const template = form.querySelector("template[data-tx-item-template]");
        const tombolTambah = form.querySelector("[data-tx-add-row]");
        const maksBaris = parseInt(form.dataset.txMaxRows || "20", 10);
        const penghitung = form.querySelector("[data-tx-row-counter]");
        const totalJenis = form.querySelector("[data-tx-total-jenis]");
        const totalUnit = form.querySelector("[data-tx-total-unit]");

        const semuaBaris = () => Array.from(tbody.querySelectorAll("[data-tx-item-row]"));

        function perbaruiBaris(baris) {
            const select = baris.querySelector("[data-tx-barang]");
            const jumlah = baris.querySelector("[data-tx-jumlah]");
            const info = infoBarang(select);
            const sekarang = baris.querySelector("[data-tx-stok-sekarang]");
            const setelah = baris.querySelector("[data-tx-stok-setelah]");
            const n = nilaiJumlah(jumlah);
            setelah.classList.remove("is-up");
            if (!info) {
                sekarang.textContent = "—";
                setelah.textContent = "—";
                return;
            }
            sekarang.textContent = denganSatuan(info.stok, info.satuan);
            if (!isNaN(n) && n >= 1) {
                setelah.textContent = denganSatuan(info.stok + n, info.satuan);
                setelah.classList.add("is-up");
            } else {
                setelah.textContent = "—";
            }
        }

        /** Barang yang sudah dipilih di baris lain dinonaktifkan agar tidak dobel. */
        function kunciDuplikat() {
            const selects = semuaBaris().map((b) => b.querySelector("[data-tx-barang]"));
            selects.forEach((select) => {
                const dipakaiLain = new Set(
                    selects.filter((s) => s !== select && s.value).map((s) => s.value),
                );
                Array.from(select.options).forEach((opsi) => {
                    if (!opsi.value) return;
                    opsi.disabled = dipakaiLain.has(opsi.value) && opsi.value !== select.value;
                });
            });
        }

        function nomoriUlang() {
            semuaBaris().forEach((baris, i) => {
                baris.querySelectorAll("[name]").forEach((el) => {
                    el.name = el.name.replace(/^items\[[^\]]*\]/, "items[" + i + "]");
                });
                baris.querySelectorAll("[id]").forEach((el) => {
                    el.id = el.id.replace(/^items_[^_]+_/, "items_" + i + "_");
                });
                baris.querySelectorAll("label[for]").forEach((el) => {
                    el.htmlFor = el.htmlFor.replace(/^items_[^_]+_/, "items_" + i + "_");
                    el.textContent = el.textContent.replace(/baris \S+$/, "baris " + (i + 1));
                });
                baris.querySelector("[data-tx-item-no]").textContent = String(i + 1);
                const hapus = baris.querySelector("[data-tx-remove-row]");
                if (hapus) hapus.setAttribute("aria-label", "Hapus baris " + (i + 1));
            });
        }

        function perbaruiRingkasan() {
            const baris = semuaBaris();
            let jenis = 0;
            let unit = 0;
            baris.forEach((b) => {
                const pilih = b.querySelector("[data-tx-barang]").value;
                const n = nilaiJumlah(b.querySelector("[data-tx-jumlah]"));
                if (pilih && !isNaN(n) && n >= 1) {
                    jenis += 1;
                    unit += n;
                }
            });
            totalJenis.textContent = angka(jenis) + " jenis barang";
            totalUnit.textContent = angka(unit) + " unit";
            penghitung.textContent = baris.length + " dari " + maksBaris + " baris";
            if (tombolTambah && !tombolTambah.hasAttribute("data-tx-locked")) {
                tombolTambah.disabled = baris.length >= maksBaris;
            }
        }

        function segarkan() {
            semuaBaris().forEach(perbaruiBaris);
            kunciDuplikat();
            perbaruiRingkasan();
        }

        function tambahBaris() {
            if (semuaBaris().length >= maksBaris) {
                swal({
                    icon: "info",
                    title: "Batas baris tercapai",
                    text: "Satu transaksi maksimal " + maksBaris + " barang. Simpan dulu, lalu buat transaksi baru.",
                });
                return;
            }
            const urutan = semuaBaris().length;
            const html = template.innerHTML
                .replace(/__INDEX__/g, String(urutan))
                .replace(/__NOMOR__/g, String(urutan + 1));
            const wadahSementara = document.createElement("tbody");
            wadahSementara.innerHTML = html.trim();
            const barisBaru = wadahSementara.querySelector("[data-tx-item-row]");
            tbody.appendChild(barisBaru);
            nomoriUlang();
            segarkan();
            barisBaru.querySelector("[data-tx-barang]").focus();
        }

        function hapusBaris(baris) {
            if (semuaBaris().length === 1) {
                baris.querySelector("[data-tx-barang]").value = "";
                baris.querySelector("[data-tx-jumlah]").value = "";
                baris.querySelectorAll("[data-tx-field]").forEach((f) => {
                    const el = f.querySelector("select, input");
                    if (el) hapusError(el);
                });
            } else {
                const fokusBerikut = baris.nextElementSibling || baris.previousElementSibling;
                baris.remove();
                nomoriUlang();
                if (fokusBerikut) fokusBerikut.querySelector("[data-tx-barang]").focus();
            }
            segarkan();
        }

        if (tombolTambah && tombolTambah.disabled) {
            tombolTambah.setAttribute("data-tx-locked", "");
        }
        if (tombolTambah) tombolTambah.addEventListener("click", tambahBaris);
        tbody.addEventListener("click", (e) => {
            const tombol = e.target.closest("[data-tx-remove-row]");
            if (tombol) hapusBaris(tombol.closest("[data-tx-item-row]"));
        });
        tbody.addEventListener("change", segarkan);
        tbody.addEventListener("input", segarkan);
        form.addEventListener("reset", () => {
            setTimeout(() => {
                semuaBaris().slice(1).forEach((b) => b.remove());
                nomoriUlang();
                form.querySelectorAll("[data-tx-field]").forEach((f) => {
                    const el = f.querySelector("select, input");
                    if (el) hapusError(el);
                });
                segarkan();
            }, 0);
        });

        form.addEventListener("submit", (e) => {
            const daftar = [];
            const supplier = form.querySelector("[name=supplier_id]");
            const tanggal = form.querySelector("[data-tx-tanggal]");
            let pesan = cekWajib(supplier);
            if (pesan) daftar.push({ field: supplier, pesan: pesan });
            pesan = cekTanggal(tanggal);
            if (pesan) daftar.push({ field: tanggal, pesan: pesan });

            const baris = semuaBaris();
            if (baris.length === 0) {
                daftar.push({ field: tombolTambah, pesan: "Tambahkan minimal satu barang." });
            }
            if (baris.length > maksBaris) {
                daftar.push({ field: tombolTambah, pesan: "Maksimal " + maksBaris + " barang dalam satu transaksi." });
            }
            const terlihat = new Map();
            baris.forEach((b, i) => {
                const select = b.querySelector("[data-tx-barang]");
                const jumlah = b.querySelector("[data-tx-jumlah]");
                const nomor = " (baris " + (i + 1) + ")";
                if (!select.value) {
                    daftar.push({ field: select, pesan: "Pilih barang" + nomor + "." });
                } else if (terlihat.has(select.value)) {
                    daftar.push({
                        field: select,
                        pesan: "Barang ini sudah dipilih di baris " + terlihat.get(select.value) + ". Gabungkan jumlahnya di satu baris.",
                    });
                } else {
                    terlihat.set(select.value, i + 1);
                }
                const pesanJumlah = cekJumlah(jumlah, null, "");
                if (pesanJumlah) daftar.push({ field: jumlah, pesan: pesanJumlah.replace(/\.$/, "") + nomor + "." });
            });

            if (daftar.length) {
                e.preventDefault();
                tampilkanKesalahan(daftar);
                return;
            }
            kunciTombol(form);
        });

        nomoriUlang();
        segarkan();
    }

    /* ------------------------------------------------------------------ *
     *  No. 20 — Barang keluar: tinjau lalu setujui
     * ------------------------------------------------------------------ */

    function pasangFormKeluar(form) {
        const perbaruiPanel = pasangPanelStok(form);
        const modalEl = document.querySelector(form.dataset.txApproval || "#txApprovalModal");
        const select = form.querySelector("[data-tx-barang]");
        const jumlah = form.querySelector("[data-tx-jumlah]");
        const tujuan = form.querySelector("[name=tujuan]");
        const tanggal = form.querySelector("[data-tx-tanggal]");

        form.addEventListener("reset", () => setTimeout(() => {
            form.querySelectorAll("[data-tx-field]").forEach((f) => {
                const el = f.querySelector("select, input");
                if (el) hapusError(el);
            });
            perbaruiPanel();
        }, 0));

        function validasi() {
            const daftar = [];
            const info = infoBarang(select);
            let pesan = cekWajib(select);
            if (pesan) daftar.push({ field: select, pesan: pesan });
            pesan = cekJumlah(jumlah, info ? info.stok : null, info ? info.satuan : "");
            if (pesan) daftar.push({ field: jumlah, pesan: pesan });
            pesan = cekTanggal(tanggal);
            if (pesan) daftar.push({ field: tanggal, pesan: pesan });
            pesan = cekWajib(tujuan, MAKS_TEKS);
            if (pesan) daftar.push({ field: tujuan, pesan: pesan });
            return daftar;
        }

        function isiModal() {
            const info = infoBarang(select);
            const n = nilaiJumlah(jumlah);
            const sisa = info.stok - n;
            const tulis = (kunci, teks) => {
                const node = modalEl.querySelector('[data-tx-approval="' + kunci + '"]');
                if (node) node.textContent = teks;
            };
            tulis("barang", (info.kode ? info.kode + " · " : "") + info.nama);
            tulis("jumlah", denganSatuan(n, info.satuan));
            tulis("stok", denganSatuan(info.stok, info.satuan));
            tulis("sisa", denganSatuan(sisa, info.satuan));
            tulis("tujuan", tujuan.value.trim());
            const epoch = epochDariInput(tanggal.value);
            const tampil = isNaN(epoch)
                ? tanggal.value
                : tanggal.value.replace(/^(\d{4})-(\d{2})-(\d{2})T?/, "$3/$2/$1 ").trim();
            tulis("tanggal", tampil);

            const peringatan = modalEl.querySelector("[data-tx-approval-warning]");
            let teks = "";
            if (sisa === 0) {
                teks = "Stok baik barang ini akan habis (0) setelah pengeluaran.";
            } else if (info.minimum > 0 && sisa < info.minimum) {
                teks = "Sisa stok akan di bawah batas minimum (" + denganSatuan(info.minimum, info.satuan) + "). Barang ini akan masuk antrean perlu restok.";
            }
            peringatan.textContent = teks;
            peringatan.classList.toggle("d-none", !teks);

            const centang = modalEl.querySelector("[data-tx-approval-check]");
            centang.checked = false;
            hapusError(centang);
        }

        form.addEventListener("submit", (e) => {
            e.preventDefault();
            const daftar = validasi();
            if (daftar.length) {
                tampilkanKesalahan(daftar);
                return;
            }
            if (!modalEl || !window.bootstrap) {
                // tanpa modal Bootstrap: pakai konfirmasi sederhana
                const info = infoBarang(select);
                const ok = window.confirm(
                    "Setujui pengeluaran " + denganSatuan(nilaiJumlah(jumlah), info.satuan) + " " + info.nama + "?",
                );
                if (ok) {
                    kunciTombol(form);
                    form.submit();
                }
                return;
            }
            isiModal();
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        });

        if (modalEl) {
            const tombolSetuju = modalEl.querySelector("[data-tx-approval-confirm]");
            const centang = modalEl.querySelector("[data-tx-approval-check]");
            centang.addEventListener("change", () => hapusError(centang));
            tombolSetuju.addEventListener("click", () => {
                if (!centang.checked) {
                    pasangError(centang, "Centang konfirmasi pemeriksaan sebelum menyetujui.");
                    centang.focus();
                    return;
                }
                // validasi ulang untuk berjaga-jaga bila form berubah setelah modal dibuka
                if (validasi().length) {
                    window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                    tampilkanKesalahan(validasi());
                    return;
                }
                tombolSetuju.disabled = true;
                tombolSetuju.textContent = "Memproses…";
                kunciTombol(form);
                form.submit();
            });
        }
    }

    /* ------------------------------------------------------------------ *
     *  No. 21 & 22 — Peminjaman (input peminjam, tanggal pinjam, foto bukti)
     * ------------------------------------------------------------------ */

    function pasangFormPeminjaman(form) {
        const perbaruiPanel = pasangPanelStok(form);
        const select = form.querySelector("[data-tx-barang]");
        const nama = form.querySelector("[name=peminjam_nama]");
        const jumlah = form.querySelector("[data-tx-jumlah]");
        const tanggal = form.querySelector("[data-tx-tanggal]");

        form.addEventListener("reset", () => setTimeout(() => {
            form.querySelectorAll("[data-tx-field]").forEach((f) => {
                const el = f.querySelector("select, input");
                if (el) hapusError(el);
            });
            resetSemuaUpload(form);
            perbaruiPanel();
        }, 0));

        form.addEventListener("submit", (e) => {
            const daftar = [];
            const info = infoBarang(select);
            let pesan = cekWajib(select);
            if (pesan) daftar.push({ field: select, pesan: pesan });
            pesan = cekWajib(nama, MAKS_TEKS);
            if (pesan) daftar.push({ field: nama, pesan: pesan });
            pesan = cekJumlah(jumlah, info ? info.stok : null, info ? info.satuan : "");
            if (pesan) daftar.push({ field: jumlah, pesan: pesan });
            pesan = cekTanggal(tanggal);
            if (pesan) daftar.push({ field: tanggal, pesan: pesan });
            cekSemuaUpload(form, daftar);

            if (daftar.length) {
                e.preventDefault();
                tampilkanKesalahan(daftar);
                return;
            }
            kunciTombol(form);
        });
    }

    /* ------------------------------------------------------------------ *
     *  No. 21 & 22 — Pengembalian (kondisi + foto bukti kembali)
     * ------------------------------------------------------------------ */

    function pasangFormPengembalian(form) {
        const modalEl = form.closest(".modal");
        const kondisi = form.querySelector("[data-tx-kondisi]");
        const idTersembunyi = form.querySelector("[data-tx-return-id]");
        const foto = form.querySelector("[data-tx-return-photo]");

        function isi(tombol, pertahankanIsian) {
            form.action = tombol.dataset.action;
            idTersembunyi.value = tombol.dataset.id;
            ["nomor", "barang", "peminjam", "jumlah", "tanggal"].forEach((kunci) => {
                const node = form.querySelector('[data-tx-return="' + kunci + '"]');
                if (node) node.textContent = tombol.dataset[kunci] || "—";
            });
            if (foto) {
                foto.classList.toggle("d-none", !tombol.dataset.bukti);
                if (tombol.dataset.bukti) foto.src = tombol.dataset.bukti;
                else foto.removeAttribute("src");
            }
            if (!pertahankanIsian) {
                form.querySelectorAll("[name=kondisi_saat_dikembalikan]").forEach((r) => {
                    r.checked = false;
                });
                hapusError(kondisi.querySelector("input"));
                form.querySelectorAll(".tx-alert").forEach((n) => n.remove());
                resetSemuaUpload(form);
            }
            bukaKunciTombol(form);
        }

        document.querySelectorAll("[data-tx-return-btn]").forEach((tombol) => {
            tombol.addEventListener("click", () => isi(tombol, false));
        });
        kondisi.addEventListener("change", () => hapusError(kondisi.querySelector("input")));

        form.addEventListener("submit", (e) => {
            const daftar = [];
            if (!form.action || /#$/.test(form.getAttribute("action") || "")) {
                e.preventDefault();
                swal({ icon: "error", title: "Pilih peminjaman", text: "Buka formulir ini dari tombol Proses pengembalian di tabel." });
                return;
            }
            if (!form.querySelector("[name=kondisi_saat_dikembalikan]:checked")) {
                const radio = kondisi.querySelector("input");
                daftar.push({ field: radio, pesan: "Kondisi saat dikembalikan wajib dipilih.", target: radio });
            }
            cekSemuaUpload(form, daftar);
            if (daftar.length) {
                e.preventDefault();
                tampilkanKesalahan(daftar);
                return;
            }
            kunciTombol(form);
        });

        // Setelah ditolak server: buka lagi modal untuk peminjaman yang sama.
        const idLama = idTersembunyi.value;
        if (idLama && modalEl && window.bootstrap) {
            const tombol = document.querySelector('[data-tx-return-btn][data-id="' + CSS.escape(idLama) + '"]');
            if (tombol) {
                isi(tombol, true);
                window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
        }
    }

    /* ------------------------------------------------------------------ *
     *  Inisialisasi
     * ------------------------------------------------------------------ */

    siap(function () {
        const page = document.querySelector(".tx-page");
        if (!page) return;
        siapkanJamServer(page);

        // Upload dipasang lebih dulu supaya validasi form bisa membaca file-nya.
        document.querySelectorAll("[data-tx-upload-field]").forEach(pasangUpload);

        document.querySelectorAll("form[data-tx-form]").forEach((form) => {
            // Pesan validasi bawaan browser diganti pesan Bahasa Indonesia milik kita.
            form.noValidate = true;
            form.addEventListener("input", (e) => {
                if (e.target.matches("input:not([type=file]), select, textarea")) hapusError(e.target);
            });
            form.addEventListener("change", (e) => {
                if (e.target.matches("select, input[type=radio], input[type=checkbox], input[type=datetime-local]")) {
                    hapusError(e.target);
                }
            });

            switch (form.dataset.txForm) {
                case "masuk":
                    pasangFormMasuk(form);
                    break;
                case "keluar":
                    pasangFormKeluar(form);
                    break;
                case "peminjaman":
                    pasangFormPeminjaman(form);
                    break;
                case "pengembalian":
                    pasangFormPengembalian(form);
                    break;
            }
        });

        // Tombol kembali browser (bfcache) jangan sampai meninggalkan tombol terkunci.
        window.addEventListener("pageshow", (e) => {
            if (e.persisted) document.querySelectorAll("form[data-tx-form]").forEach(bukaKunciTombol);
        });

        // Notifikasi sukses/gagal dari server (session flash).
        document.querySelectorAll(".tx-flash").forEach((node) => {
            if (window.Swal) {
                window.Swal.fire({
                    toast: true,
                    position: "top-end",
                    icon: node.dataset.type,
                    title: node.dataset.message,
                    showConfirmButton: false,
                    timer: 4200,
                    timerProgressBar: true,
                    customClass: { popup: "fe-swal-toast" },
                });
            }
        });

        // Ada error dari server? Gulir ke kesalahan pertama di halaman.
        const errorPertama = page.querySelector(".tx-field-error");
        if (errorPertama && !errorPertama.closest(".modal")) {
            errorPertama.scrollIntoView({ block: "center" });
        }
    });
})();
