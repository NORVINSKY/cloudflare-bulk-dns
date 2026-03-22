<?php
/**
 * Класс для извлечения публичного суффикса, домена и поддомена
 * 
 * Использует сгенерированный файл suffixes.php
 * Поддерживает IDN, wildcard-правила и исключения
 * 
 * PHP 7.4+
 */

class DomainParser
{
    private $simple;
    private $wildcard;
    private $exceptions;
    
    /**
     * @param string $suffixesFile Путь к файлу suffixes.php
     */
    public function __construct(string $suffixesFile)
    {
        if (!file_exists($suffixesFile)) {
            throw new RuntimeException("Файл суффиксов не найден: {$suffixesFile}");
        }
        
        $data = require $suffixesFile;
        
        $this->simple = $data['simple'] ?? [];
        $this->wildcard = $data['wildcard'] ?? [];
        $this->exceptions = $data['exceptions'] ?? [];
    }
    
    /**
     * Получить публичный суффикс (TLD)
     * 
     * @param string $domain Домен (example.com, sub.example.co.uk)
     * @return string|null Публичный суффикс (com, co.uk) или null
     */
    public function getPublicSuffix(string $domain): ?string
    {
        $domain = $this->normalize($domain);
        
        if ($domain === null) {
            return null;
        }
        
        $parts = explode('.', $domain);
        $partsCount = count($parts);
        
        if ($partsCount === 1) {
            // Один сегмент — это сам TLD
            return $parts[0];
        }
        
        $longestMatch = null;
        $longestMatchLength = 0;
        
        // Проверяем все возможные суффиксы справа налево
        for ($i = 0; $i < $partsCount; $i++) {
            $suffix = implode('.', array_slice($parts, $i));
            $suffixPartsCount = $partsCount - $i;
            
            // Проверяем исключения (!www.ck)
            if (isset($this->exceptions[$suffix])) {
                // Это исключение, не считается публичным суффиксом
                // Берём родительский уровень
                if ($i > 0) {
                    $parentSuffix = implode('.', array_slice($parts, $i - 1));
                    if ($suffixPartsCount + 1 > $longestMatchLength) {
                        $longestMatch = $parentSuffix;
                        $longestMatchLength = $suffixPartsCount + 1;
                    }
                }
                continue;
            }
            
            // Проверяем обычные правила
            if (isset($this->simple[$suffix])) {
                if ($suffixPartsCount > $longestMatchLength) {
                    $longestMatch = $suffix;
                    $longestMatchLength = $suffixPartsCount;
                }
            }
            
            // Проверяем wildcard (*.au означает что любой x.au — публичный суффикс)
            if ($suffixPartsCount >= 2) {
                // Берём родительскую часть суффикса
                $parentSuffix = implode('.', array_slice($parts, $i + 1));
                
                if (isset($this->wildcard[$parentSuffix])) {
                    if ($suffixPartsCount > $longestMatchLength) {
                        $longestMatch = $suffix;
                        $longestMatchLength = $suffixPartsCount;
                    }
                }
            }
        }
        
        // Если нашли совпадение — возвращаем его
        if ($longestMatch !== null) {
            return $longestMatch;
        }
        
        // Если ничего не найдено, возвращаем последний сегмент как TLD
        return $parts[$partsCount - 1];
    }
    
    /**
     * Получить регистрируемый домен (домен 2-го уровня)
     * 
     * @param string $domain Домен
     * @return string|null Домен 2-го уровня (example.com, example.co.uk) или null
     */
    public function getRegistrableDomain(string $domain): ?string
    {
        $domain = $this->normalize($domain);
        
        if ($domain === null) {
            return null;
        }
        
        $publicSuffix = $this->getPublicSuffix($domain);
        
        if ($publicSuffix === null || $publicSuffix === $domain) {
            // Домен совпадает с публичным суффиксом (например, просто "com")
            return null;
        }
        
        $parts = explode('.', $domain);
        $suffixParts = explode('.', $publicSuffix);
        
        $suffixPartsCount = count($suffixParts);
        $domainPartsCount = count($parts);
        
        if ($domainPartsCount <= $suffixPartsCount) {
            // Недостаточно частей для домена 2-го уровня
            return null;
        }
        
        // Берём одну часть перед публичным суффиксом + сам суффикс
        // Например: example.co.uk = example + co.uk
        return implode('.', array_slice($parts, $domainPartsCount - $suffixPartsCount - 1));
    }
    
    /**
     * Получить поддомен (всё, что перед доменом 2-го уровня)
     * 
     * @param string $domain Домен
     * @return string|null Поддомен (www, api.v2) или null
     */
    public function getSubdomain(string $domain): ?string
    {
        $domain = $this->normalize($domain);
        
        if ($domain === null) {
            return null;
        }
        
        $registrable = $this->getRegistrableDomain($domain);
        
        if ($registrable === null || $registrable === $domain) {
            // Нет регистрируемого домена или домен совпадает с ним
            return null;
        }
        
        // Удаляем регистрируемый домен из конца
        $subdomain = substr($domain, 0, strlen($domain) - strlen($registrable) - 1);
        
        return $subdomain !== '' ? $subdomain : null;
    }
    
    /**
     * Разобрать домен на компоненты
     * 
     * @param string $domain Домен
     * @return array{subdomain: string|null, domain: string|null, suffix: string|null}
     */
    public function parse(string $domain): array
    {
        return [
            'subdomain' => $this->getSubdomain($domain),
            'domain' => $this->getRegistrableDomain($domain),
            'suffix' => $this->getPublicSuffix($domain),
        ];
    }
    
    /**
     * Нормализация домена: lowercase + punycode
     * 
     * @param string $domain Домен
     * @return string|null Нормализованный домен или null при ошибке
     */
    private function normalize(string $domain): ?string
    {
        $domain = trim($domain);
        $domain = strtolower($domain);
        
        // Удаляем протокол если есть
        $domain = preg_replace('#^https?://#i', '', $domain);
        
        // Удаляем путь если есть
        $domain = preg_replace('#/.*$#', '', $domain);
        
        // Удаляем порт если есть
        $domain = preg_replace('#:\d+$#', '', $domain);
        
        if ($domain === '') {
            return null;
        }
        
        // Конвертация IDN в punycode
        if (preg_match('/[^\x00-\x7F]/', $domain)) {
            if (function_exists('idn_to_ascii')) {
                $converted = idn_to_ascii(
                    $domain,
                    IDNA_DEFAULT,
                    INTL_IDNA_VARIANT_UTS46
                );
                
                if ($converted === false) {
                    return null;
                }
                
                $domain = $converted;
            }
        }
        
        return $domain;
    }
}

