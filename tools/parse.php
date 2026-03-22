<?php
/**
 * Парсер Public Suffix List
 * 
 * Читает файл public_suffix_list.dat и генерирует PHP-массив
 * с поддержкой wildcard-правил, исключений и IDN
 * 
 * PHP 7.4+
 */

class PublicSuffixListParser
{
    private $inputFile;
    private $outputFile;
    
    private $simple = [];
    private $wildcard = [];
    private $exceptions = [];
    
    public function __construct(string $inputFile, string $outputFile)
    {
        $this->inputFile = $inputFile;
        $this->outputFile = $outputFile;
    }
    
    /**
     * Запуск парсинга
     */
    public function parse(): bool
    {
        if (!file_exists($this->inputFile)) {
            echo "Ошибка: файл {$this->inputFile} не найден\n";
            return false;
        }
        
        $lines = file($this->inputFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        if ($lines === false) {
            echo "Ошибка: не удалось прочитать файл\n";
            return false;
        }
        
        $count = 0;
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Пропускаем комментарии и пустые строки
            if ($line === '' || strpos($line, '//') === 0) {
                continue;
            }
            
            // Конвертируем в punycode для IDN
            $line = $this->toPunycode($line);
            
            // Обработка исключений (начинаются с !)
            if (strpos($line, '!') === 0) {
                $suffix = substr($line, 1);
                $this->exceptions[$suffix] = true;
                $count++;
                continue;
            }
            
            // Обработка wildcard (начинаются с *.)
            if (strpos($line, '*.') === 0) {
                $suffix = substr($line, 2);
                $this->wildcard[$suffix] = true;
                $count++;
                continue;
            }
            
            // Обычные правила
            $this->simple[$line] = true;
            $count++;
        }
        
        echo "Обработано правил: {$count}\n";
        echo "- Обычных: " . count($this->simple) . "\n";
        echo "- Wildcard: " . count($this->wildcard) . "\n";
        echo "- Исключений: " . count($this->exceptions) . "\n";
        
        return $this->save();
    }
    
    /**
     * Конвертация IDN в punycode
     */
    private function toPunycode(string $domain): string
    {
        // Проверяем наличие non-ASCII символов
        if (!preg_match('/[^\x00-\x7F]/', $domain)) {
            return strtolower($domain);
        }
        
        // Используем idn_to_ascii для конвертации
        if (function_exists('idn_to_ascii')) {
            // PHP 7.4+ использует INTL_IDNA_VARIANT_UTS46
            $converted = idn_to_ascii(
                $domain,
                IDNA_DEFAULT,
                INTL_IDNA_VARIANT_UTS46
            );
            
            return $converted !== false ? strtolower($converted) : strtolower($domain);
        }
        
        // Fallback: возвращаем как есть (потребуется ext-intl)
        echo "Предупреждение: ext-intl недоступен, IDN не будут конвертированы\n";
        return strtolower($domain);
    }
    
    /**
     * Сохранение в PHP-файл
     */
    private function save(): bool
    {
        $data = [
            'simple' => $this->simple,
            'wildcard' => $this->wildcard,
            'exceptions' => $this->exceptions,
        ];
        
        $export = '<?php' . PHP_EOL . 'return ' . var_export($data, true) . ';';
        
        if (file_put_contents($this->outputFile, $export) === false) {
            echo "Ошибка: не удалось сохранить файл {$this->outputFile}\n";
            return false;
        }
        
        echo "Файл сохранён: {$this->outputFile}\n";
        echo "Размер: " . number_format(filesize($this->outputFile)) . " байт\n";
        
        return true;
    }
}

// --- Использование ---

$inputFile = __DIR__ . '/public_suffix_list.dat';
$outputFile = __DIR__ . '/suffixes.php';

$parser = new PublicSuffixListParser($inputFile, $outputFile);

if ($parser->parse()) {
    echo "\n✓ Парсинг завершён успешно\n";
} else {
    echo "\n✗ Ошибка парсинга\n";
    exit(1);
}