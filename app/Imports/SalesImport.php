<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravolt\Indonesia\Facade as Indonesia;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class SalesImport implements ToCollection, WithBatchInserts, WithChunkReading
{
    private const MAX_QUANTITY = 2147483647;
    private const MAX_TOTAL_AMOUNT = 9999999999999.99;

    /** @var array<string, int> */
    private array $columnMap = [];

    public function __construct(private readonly int $year)
    {
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            if (! $row instanceof Collection) {
                $row = collect($row);
            }

            if ($row->filter(fn ($value) => $this->stringValue($value) !== '')->isEmpty()) {
                continue;
            }

            if ($this->isHeaderRow($row)) {
                $this->resolveColumnMap($row);

                continue;
            }

            if ($this->columnMap === []) {
                continue;
            }

            $firstCell = $this->stringValue($row->get(0));
            $month = $this->stringValue($this->getValue($row, 'month') ?? $firstCell);

            if ($month === '') {
                continue;
            }

            $saleDate = $this->buildSaleDate($month, $this->getValue($row, 'day'));
            $rawNumber = $this->stringValue($this->getValue($row, 'number'));
            $saleNumber = $this->buildSaleNumber($month, $rawNumber);

            $customerName = $this->stringValue($this->getValue($row, 'name'));
            $customerPhone = $this->stringValue($this->getValue($row, 'phone'));
            $customerEmail = $this->stringValue($this->getValue($row, 'email'));
            $customerAddress = $this->stringValue($this->getValue($row, 'address'));
            $customerCityName = $this->stringValue($this->getValue($row, 'city'));
            $productName = $this->stringValue($this->getValue($row, 'product'));
            $quantity = $this->parseInteger($this->getValue($row, 'quantity'));
            $totalAmount = $this->parseNumeric($this->getValue($row, 'total'));

            if ($saleDate === null || $saleNumber === null || $customerName === '' || $productName === '') {
                continue;
            }

            DB::transaction(function () use (
                $month,
                $saleDate,
                $saleNumber,
                $customerName,
                $customerEmail,
                $customerPhone,
                $customerAddress,
                $customerCityName,
                $productName,
                $quantity,
                $totalAmount
            ): void {
                $cityId = $this->resolveCityId($customerCityName);

                $customer = $this->findOrCreateCustomer(
                    $customerName,
                    $customerEmail,
                    $customerPhone,
                    $customerAddress,
                    $cityId
                );

                $product = Product::firstOrCreate(
                    ['name' => $productName],
                    ['description' => null, 'price' => null]
                );

                Sale::create([
                    'month' => Str::upper($month),
                    'sale_date' => $saleDate,
                    'sale_number' => $saleNumber,
                    'customer_id' => $customer->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'total_amount' => $totalAmount,
                ]);
            });
        }
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    private function buildSaleDate(string $monthValue, mixed $dayValue): ?Carbon
    {
        $monthNumber = $this->resolveMonthNumber($monthValue);

        if ($monthNumber === null) {
            return null;
        }

        $day = $this->extractDayNumber($dayValue);

        if ($day === null) {
            return null;
        }

        try {
            return Carbon::create($this->year, $monthNumber, $day);
        } catch (\Throwable) {
            return null;
        }
    }

    private function extractDayNumber(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $numeric = (float) $value;

            if ($numeric > 31) {
                try {
                    return Carbon::instance(ExcelDate::excelToDateTimeObject($numeric))->day;
                } catch (\Throwable) {
                    // Fallback to rounding below.
                }
            }

            $day = (int) round($numeric);
        } elseif (is_string($value)) {
            $digits = preg_replace('/\D+/', '', $value);

            if ($digits === '') {
                return null;
            }

            $day = (int) $digits;
        } else {
            return null;
        }

        return ($day >= 1 && $day <= 31) ? $day : null;
    }

    private function resolveMonthNumber(string $value): ?int
    {
        $normalized = Str::upper(trim($value));

        if ($normalized === '') {
            return null;
        }

        if (is_numeric($normalized)) {
            $month = (int) $normalized;

            if ($month >= 1 && $month <= 12) {
                return $month;
            }
        }

        $roman = preg_replace('/[^IVXLCDM]/', '', $normalized);
        $romanMap = [
            'I' => 1,
            'II' => 2,
            'III' => 3,
            'IV' => 4,
            'V' => 5,
            'VI' => 6,
            'VII' => 7,
            'VIII' => 8,
            'IX' => 9,
            'X' => 10,
            'XI' => 11,
            'XII' => 12,
        ];

        if ($roman !== '' && isset($romanMap[$roman])) {
            return $romanMap[$roman];
        }

        $slug = Str::slug($normalized);

        $monthSynonyms = [
            1 => ['jan', 'januari', 'january'],
            2 => ['feb', 'februari', 'february'],
            3 => ['mar', 'maret', 'march'],
            4 => ['apr', 'april'],
            5 => ['mei', 'may'],
            6 => ['jun', 'juni', 'june'],
            7 => ['jul', 'juli', 'july'],
            8 => ['agu', 'agustus', 'august'],
            9 => ['sep', 'sept', 'september'],
            10 => ['okt', 'oktober', 'oct', 'october'],
            11 => ['nov', 'november'],
            12 => ['des', 'desember', 'dec', 'december'],
        ];

        foreach ($monthSynonyms as $monthNumber => $names) {
            if (in_array($slug, $names, true)) {
                return $monthNumber;
            }
        }

        return null;
    }

    private function parseInteger(mixed $value): int
    {
        $normalized = 0;

        if (is_numeric($value)) {
            $normalized = (int) round((float) $value);
        } elseif (is_string($value)) {
            $digits = preg_replace('/\D+/', '', $value);

            if ($digits !== '') {
                $normalized = (int) $digits;
            }
        }

        return (int) min(self::MAX_QUANTITY, max(0, $normalized));
    }

    private function parseNumeric(mixed $value): float
    {
        $numericString = $this->extractNumericString($value);

        if ($numericString === null) {
            return 0.0;
        }

        $normalizedString = $this->normalizeNumericString($numericString);

        if ($normalizedString === null) {
            return 0.0;
        }

        $normalized = (float) $normalizedString;
        $normalized = max(0.0, $normalized);

        return (float) min(self::MAX_TOTAL_AMOUNT, $normalized);
    }

    private function getValue(Collection $row, string $field): mixed
    {
        $index = $this->columnMap[$field] ?? null;

        return $index !== null ? $row->get($index) : null;
    }

    private function isHeaderRow(Collection $row): bool
    {
        return strcasecmp($this->stringValue($row->get(0)), 'BLN') === 0;
    }

    private function resolveColumnMap(Collection $row): void
    {
        $map = [];

        foreach ($row as $index => $header) {
            $slug = Str::slug($this->stringValue($header));

            if ($slug === '') {
                continue;
            }

            foreach ($this->headerSynonyms() as $field => $synonyms) {
                if (isset($map[$field])) {
                    continue;
                }

                if (in_array($slug, $synonyms, true)) {
                    $map[$field] = $index;
                }
            }
        }

        $this->columnMap = $map;
    }

    private function headerSynonyms(): array
    {
        return [
            'month' => ['bln', 'bulan', 'month'],
            'day' => ['tgl', 'tanggal', 'date', 'day'],
            'number' => ['no', 'nomor', 'number'],
            'name' => ['nama', 'name'],
            'phone' => ['no-hp', 'hp', 'telepon', 'phone'],
            'email' => ['email'],
            'address' => ['alamat', 'address'],
            'city' => ['kota', 'kota-kabupaten', 'city'],
            'product' => ['produk', 'product'],
            'quantity' => ['qty', 'jmlh', 'jumlah', 'quantity'],
            'total' => ['total-pembelian', 'total', 'grand-total'],
        ];
    }

    private function extractNumericString(mixed $value): ?string
    {
        if ($value === null || $value === '' || $value === false) {
            return null;
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        if (! is_string($value)) {
            return null;
        }

        if (! preg_match('/\d/', $value)) {
            return null;
        }

        if (preg_match('/\d{1,3}(?:[.,]\d{3})*(?:[.,]\d+)?|\d+(?:[.,]\d+)?/', $value, $matches)) {
            return $matches[0];
        }

        $digitsOnly = preg_replace('/\D+/', '', $value);

        return $digitsOnly !== '' ? $digitsOnly : null;
    }

    private function normalizeNumericString(string $value): ?string
    {
        $value = trim($value);

        $commaPos = strrpos($value, ',');
        $dotPos = strrpos($value, '.');

        if ($commaPos !== false && $dotPos !== false) {
            if ($commaPos > $dotPos) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif ($commaPos !== false) {
            $decimals = strlen($value) - $commaPos - 1;

            if ($decimals > 0 && $decimals <= 2) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif ($dotPos !== false) {
            $decimals = strlen($value) - $dotPos - 1;

            if ($decimals === 0) {
                $value = rtrim($value, '.');
            } elseif ($decimals > 2) {
                $value = str_replace('.', '', $value);
            }
        }

        $value = preg_replace('/[^\d.\-]/', '', $value);

        if ($value === '' || $value === '-' || $value === '.-' || $value === '-.') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        $number = (float) $value;
        $number = round($number, 2);

        $parts = explode('.', number_format($number, 2, '.', ''));
        $integerPart = $parts[0];

        if (strlen(ltrim($integerPart, '-')) > 13) {
            return (string) self::MAX_TOTAL_AMOUNT;
        }

        return number_format($number, 2, '.', '');
    }

    private function resolveCityId(?string $cityName): ?int
    {
        if ($cityName === null || $cityName === '') {
            return null;
        }

        $results = Indonesia::search(Str::lower($cityName))->allCities();

        if ($results === null) {
            return null;
        }

        $first = collect($results)->first();

        if (is_array($first)) {
            return $first['id'] ?? null;
        }

        return $first->id ?? null;
    }

    private function findOrCreateCustomer(
        string $name,
        ?string $email,
        ?string $phone,
        ?string $address,
        ?int $cityId
    ): Customer {
        $customer = null;

        if ($email) {
            $customer = Customer::where('email', $email)->first();
        }

        if (! $customer && $phone) {
            $customer = Customer::where('phone', $phone)->first();
        }

        if (! $customer) {
            $customer = Customer::where('name', $name)->first();
        }

        if (! $customer) {
            $customer = Customer::create([
                'name' => $name,
                'email' => $email ?: null,
                'phone' => $phone ?: null,
                'address' => $address ?: null,
                'city_id' => $cityId,
            ]);

            return $customer;
        }

        $needsSave = false;

        if ($customer->address === null && $address) {
            $customer->address = $address;
            $needsSave = true;
        }

        if ($customer->phone === null && $phone) {
            $customer->phone = $phone;
            $needsSave = true;
        }

        if ($customer->city_id === null && $cityId) {
            $customer->city_id = $cityId;
            $needsSave = true;
        }

        if ($customer->email === null && $email) {
            $customer->email = $email;
            $needsSave = true;
        }

        if ($needsSave) {
            $customer->save();
        }

        return $customer;
    }

    private function stringValue(mixed $value): string
    {
        if (is_string($value)) {
            return preg_replace('/\s+/u', ' ', trim($value));
        }

        return is_numeric($value) ? (string) $value : '';
    }

    private function buildSaleNumber(?string $month, ?string $rawNumber): ?string
    {
        if ($rawNumber === null || $rawNumber === '') {
            return null;
        }

        $normalizedNumber = preg_replace('/\s+/', '', (string) $rawNumber);
        $normalizedMonth = $month ? Str::upper(trim($month)) : null;

        return $normalizedMonth
            ? sprintf('%s-%s', $normalizedMonth, $normalizedNumber)
            : $normalizedNumber;
    }
}
