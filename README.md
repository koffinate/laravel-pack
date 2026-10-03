# Koffinate's Laravel Plugin Pack

Laravel plugin pack by Koffinate. Kumpulan utilitas backend yang dipakai
bersama oleh aplikasi-aplikasi Koffinate (namespace `Kfn\`).

## Modul

| Namespace | Isi |
|---|---|
| `Kfn\Base` | Fondasi: response terstandar (`Response`), exception terstandar (`KfnException`), kontrak response code, request, model, console, listener. |
| `Kfn\UI` | Lapisan UI admin: wrapper DataTable (`HasUI`, `DataTableButtons`), response view/redirect (`UI\Response`), middleware, form-request, view & helper Blade. |
| `Kfn\Database` | Utilitas database: Eloquent model dasar, trait (`HasModel`), blueprint. |
| `Kfn\Util` | Helper umum (`Util/helpers.php`) dan service provider-nya. |

Service provider yang didaftarkan otomatis (lihat `extra.laravel.providers`
di `composer.json`):

- `Kfn\Util\UtilServiceProvider`
- `Kfn\Base\BaseServiceProvider`
- `Kfn\UI\UiServiceProvider`

## Kontrak penting

### 1. Single-send response (wajib)

`Kfn\UI\Response::toResponse()`, `KfnException::toResponse()`, dan
`KfnException::renderException()` **tidak boleh** memanggil `->send()`.
Mereka hanya MEMBANGUN dan MENGEMBALIKAN respons; pengiriman adalah tugas
kernel Laravel, tepat satu kali. `send()` di dalam method ini menyebabkan
double-send (`Cannot modify header information - headers already sent`).

Aturan praktis:

- Di `toResponse()` / `renderException()`: selalu `return $response;`,
  jangan `return $response->send();`.
- Di cabang exception yang me-redirect (`KfnException::toResponse()`):
  `return $redirect->withInput();`, biarkan `abort()` hanya sebagai
  fallback bila redirect gagal dibangun.
- Pengecualian satu-satunya: pemanggil yang memang bertanggung jawab penuh
  atas siklus respons (mis. middleware yang diakhiri `exit()`, atau handler
  `dump()` darurat) — dan itu harus eksplisit, bukan di Responsable.

### 2. Response terstandar

Gunakan `Kfn\Base\Response` / `Kfn\UI\Response` dengan `ResponseCode`
(`Kfn\Base\Enums\ResponseCode`) agar format `rc` / `message` / `data`
konsisten di seluruh API.

### 3. Exception terstandar

Lempar `KfnException` (dengan `ResponseCode` yang tepat) alih-alih
`abort()` / exception generik, supaya `renderable()` bootstrap bisa
memetakannya ke respons JSON atau redirect-back + flash yang benar.

## Berkontribusi

Persyaratan: PHP ^8.3, Composer 2.

```bash
composer install
composer test        # menjalankan vendor/bin/phpunit
```

- Style kode mengikuti `pint.json` (preset Laravel + aturan kustom).
  Jalankan Pint sebelum commit bila mengubah file di `src/`.
- Test memakai [orchestra/testbench](https://packages.tools/testbench)
  + PHPUnit; harness ada di `tests/` (`Kfn\Tests\TestCase`).
- Jangan commit `vendor/` dan hasil test (sudah di `.gitignore`).
