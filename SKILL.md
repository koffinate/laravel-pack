# Skill: Bekerja dengan koffinate/laravel-pack

Skill reusable untuk agen AI (dan manusia) yang mengubah kode di package
`koffinate/laravel-pack` (`packages/laravel-pack`, namespace `Kfn\`).

## 1. Peta package

- `src/Base/` — fondasi: `Response`, `KfnException`, kontrak, request,
  model, console. Config: `src/Base/config.php` (`koffinate.base`).
- `src/UI/` — lapisan admin: `Response` (view/redirect), `HasUI`,
  middleware, form-request, view, helper. Config: `src/UI/config/ui.php`
  (`koffinate.ui`, termasuk `exception.*`).
- `src/Database/` — model dasar & trait Eloquent.
- `src/Util/` — helper umum.
- Test: `tests/` (`Kfn\Tests\TestCase`, testbench). Jalankan dari root
  package: `composer test`.

## 2. Aturan keras

### Single-send (paling sering dilanggar!)

`toResponse()` / `renderException()` HANYA membangun + mengembalikan
respons — JANGAN `->send()` di dalamnya. Kernel yang mengirim, sekali saja.
Gejala pelanggaran: `Cannot modify header information - headers already
sent` di log. Test pengunci: `tests/Feature/SingleSendTest.php`
(jalankan tiap menyentuh `UI/Response.php` atau `KfnException.php`).

### Jangan perluas kontrak sembarangan

- `DefaultItem`-like enum / `ResponseCode`: menambah case = menambah
  permission/kontrak API lintas aplikasi. Diskusikan dulu bila case baru
  dibutuhkan (konsumen: seluruh aplikasi Koffinate).
- `helpers.php` (3 file autoload `files`): fungsi global — pastikan nama
  unik berprefix jelas dan bungkus `function_exists`.

### Config

- Opsi baru wajib punya default di `mergeConfigFrom` + didokumentasikan
  di README bila mengubah perilaku (contoh: `koffinate.ui.exception.*`).
- Jangan baca `env()` langsung di luar config file / service provider.

## 3. Workflow standar

1. Baca SKILL ini + README package.
2. Buat/ubah kode di `src/` mengikuti gaya file tetangga
   (jalankan Pint: config `pint.json` di root package).
3. Tambah/pertahankan test di `tests/` (`composer test` harus hijau).
4. Bila mengubah perilaku response/exception: jalankan juga test
   aplikasi konsumen yang relevan (mis. suite DataTable/CRUD admin).
5. Jangan commit `vendor/`, cache phpunit, atau file `.env`.

## 4. Jebakan umum

- `send()` / `exit()` / `echo` / `dump()` di code path request =
  double-send atau respons rusak. Satu-satunya tempat yang boleh:
  middleware yang eksplisit `exit()` setelahnya, dan handler `dump()`
  darurat (lihat `BaseServiceProvider`).
- `setcookie()` / `encrypt()` di CLI (test): `setcookie()` no-op aman,
  tapi `encrypt()` butuh app key — harness test sudah menyetelnya di
  `tests/TestCase.php::defineEnvironment`, jangan dihapus.
- `redirect()->back()` di luar request HTTP butuh referer atau session:
  di test, buat `Request::create()` + header referer, bind ke container
  (`app()->instance('request', $request)`), dan set session array manual.
