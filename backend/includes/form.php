<?php
// Pembantu formulir admin: membaca input POST dan mencetak field berlabel. Kesalahan validasi dikumpulkan di $errors (['field' => 'pesan']).
declare(strict_types=1);

function post_str(string $key, int $max = 255): string
{
    $v = $_POST[$key] ?? '';
    $v = is_string($v) ? trim(str_replace("\r\n", "\n", $v)) : '';
    return mb_substr($v, 0, $max);
}

/** String kosong menjadi null (untuk kolom NULL di database). */
function post_nullable(string $key, int $max = 255): ?string
{
    $v = post_str($key, $max);
    return $v === '' ? null : $v;
}

function post_int(string $key, int $default = 0): int
{
    $v = $_POST[$key] ?? '';
    return is_numeric($v) ? (int) $v : $default;
}

function post_nullable_int(string $key): ?int
{
    $v = trim((string) ($_POST[$key] ?? ''));
    $v = str_replace(['.', ' '], '', $v);   // "350.000" → 350000
    return $v === '' || !is_numeric($v) ? null : (int) $v;
}

function post_bool(string $key): int
{
    return !empty($_POST[$key]) ? 1 : 0;
}

/** Ubah teks menjadi slug URL: "Promo Akhir Tahun!" → "promo-akhir-tahun". */
function slugify(string $text): string
{
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    return trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($ascii)), '-');
}

function valid_url(?string $v): bool
{
    return $v === null || (filter_var($v, FILTER_VALIDATE_URL) && preg_match('~^https?://~i', $v));
}

/** Nomor telepon/WA: angka, spasi, +, -, ( ). */
function valid_phone(?string $v): bool
{
    return $v === null || (bool) preg_match('/^\+?[0-9 ()\-]{8,20}$/', $v);
}

function field_error(array $errors, string $name): string
{
    return isset($errors[$name]) ? '<p class="field-error" id="err-' . e($name) . '">' . icon('alert') . e($errors[$name]) . '</p>' : '';
}

function field_attrs(array $errors, string $name, array $opts): string
{
    $a = '';
    foreach (['required', 'readonly', 'disabled', 'autofocus', 'multiple'] as $flag) {
        if (!empty($opts[$flag])) {
            $a .= ' ' . $flag;
        }
    }
    foreach (['maxlength', 'min', 'max', 'step', 'placeholder', 'pattern', 'autocomplete', 'inputmode', 'accept', 'rows'] as $k) {
        if (isset($opts[$k])) {
            $a .= ' ' . $k . '="' . e($opts[$k]) . '"';
        }
    }
    if (isset($errors[$name])) {
        $a .= ' aria-invalid="true" aria-describedby="err-' . e($name) . '"';
    }
    return $a;
}

function field_label(string $id, string $label, array $opts): string
{
    $hint = isset($opts['hint']) ? ' <small>' . e($opts['hint']) . '</small>' : '';
    return '<label for="' . e($id) . '">' . e($label) . (!empty($opts['required']) ? ' <span aria-hidden="true">*</span>' : '') . $hint . '</label>';
}

/** Input satu baris. $opts: type, required, maxlength, hint, placeholder, full (lebar penuh), … */
function field_input(string $name, string $label, mixed $value, array $errors = [], array $opts = []): string
{
    $id = 'f-' . $name;
    $type = $opts['type'] ?? 'text';
    return '<div class="field' . (!empty($opts['full']) ? ' field--full' : '') . '">' . field_label($id, $label, $opts)
        . '<input type="' . e($type) . '" id="' . e($id) . '" name="' . e($name) . '" value="' . e($value) . '"' . field_attrs($errors, $name, $opts) . '>'
        . field_error($errors, $name) . '</div>';
}

function field_textarea(string $name, string $label, mixed $value, array $errors = [], array $opts = []): string
{
    $id = 'f-' . $name;
    $opts += ['rows' => 4];
    return '<div class="field' . (!empty($opts['full']) ? ' field--full' : '') . '">' . field_label($id, $label, $opts)
        . '<textarea id="' . e($id) . '" name="' . e($name) . '"' . field_attrs($errors, $name, $opts) . (!empty($opts['mono']) ? ' class="mono"' : '') . '>' . e($value) . '</textarea>'
        . field_error($errors, $name) . '</div>';
}

/** Pilihan: $options = [nilai => label]. */
function field_select(string $name, string $label, array $options, mixed $selected, array $errors = [], array $opts = []): string
{
    $id = 'f-' . $name;
    $html = '<div class="field' . (!empty($opts['full']) ? ' field--full' : '') . '">' . field_label($id, $label, $opts)
        . '<select id="' . e($id) . '" name="' . e($name) . '"' . field_attrs($errors, $name, $opts) . '>';
    foreach ($options as $val => $text) {
        $html .= '<option value="' . e($val) . '"' . ((string) $val === (string) $selected ? ' selected' : '') . '>' . e($text) . '</option>';
    }
    return $html . '</select>' . field_error($errors, $name) . '</div>';
}

function field_check(string $name, string $label, mixed $checked, array $opts = []): string
{
    $hint = isset($opts['hint']) ? '<small class="muted"> — ' . e($opts['hint']) . '</small>' : '';
    return '<label class="check"><input type="checkbox" name="' . e($name) . '" value="1"' . ($checked ? ' checked' : '') . '><span>' . e($label) . $hint . '</span></label>';
}

/** Unggah gambar dengan pratinjau gambar sekarang. Nilai lama tetap jika tidak memilih file baru. */
function field_image(string $name, string $label, ?string $current, array $errors = [], array $opts = []): string
{
    $id = 'f-' . $name;
    $preview = $current
        ? '<div class="img-preview">' . img($current, $label, '140px') . '</div>'
        : '<div class="img-preview img-preview--empty">' . icon('image') . '</div>';
    $remove = $current && !empty($opts['removable'])
        ? '<label class="check small"><input type="checkbox" name="' . e($name) . '_remove" value="1"><span>Hapus gambar</span></label>'
        : '';
    return '<div class="field' . (!empty($opts['full']) ? ' field--full' : '') . '">' . field_label($id, $label, $opts)
        . '<div class="img-field">' . $preview . '<div class="img-field-input">'
        . '<input type="file" id="' . e($id) . '" name="' . e($name) . '" accept="image/jpeg,image/png,image/webp"' . field_attrs($errors, $name, []) . '>'
        . '<small class="muted">JPG, PNG, atau WebP, maks. 5 MB. Otomatis diubah ke WebP berbagai ukuran.</small>' . $remove
        . field_error($errors, $name) . '</div></div></div>';
}

/** Ada file baru yang dipilih di input ini? */
function has_upload(string $name): bool
{
    return isset($_FILES[$name]) && ($_FILES[$name]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
}

/**
 * Proses input gambar tunggal: unggahan baru menggantikan yang lama (file lama hasil unggahan dihapus),
 * centang "hapus" mengosongkan. Kesalahan dicatat di $errors.
 */
function handle_image_field(string $name, ?string $current, string $folder, array &$errors): ?string
{
    if (has_upload($name)) {
        try {
            $key = image_store_upload($_FILES[$name], $folder);
            image_delete_upload($current);
            return $key;
        } catch (RuntimeException $e) {
            $errors[$name] = $e->getMessage();
            return $current;
        }
    }
    if (!empty($_POST[$name . '_remove'])) {
        image_delete_upload($current);
        return null;
    }
    return $current;
}

/** Tombol hapus dengan konfirmasi (tanpa dialog browser). */
function delete_button(string $action, string $what, array $hidden = []): string
{
    $inputs = csrf_field();
    foreach ($hidden as $k => $v) {
        $inputs .= '<input type="hidden" name="' . e($k) . '" value="' . e($v) . '">';
    }
    return '<details class="confirm-danger"><summary class="btn btn-ghost btn-sm">' . icon('trash-2') . 'Hapus</summary>'
        . '<form method="post" action="' . e($action) . '">' . $inputs
        . '<p class="small">Hapus ' . e($what) . '? Tindakan ini tidak bisa dibatalkan.</p>'
        . '<button type="submit" class="btn btn-danger btn-sm">Ya, hapus</button></form></details>';
}

/** Pilihan hotel untuk form (dibatasi lingkup admin). */
function hotel_options(bool $withGroup = false): array
{
    $opts = $withGroup && is_super() ? ['' => 'Semua hotel (artikel grup)'] : [];
    foreach (admin_hotels() as $h) {
        $opts[$h['id']] = $h['name'];
    }
    return $opts;
}
