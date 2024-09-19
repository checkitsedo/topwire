<?php
declare(strict_types=1);
namespace Topwire\Context;

use Topwire\Context\Exception\InvalidTopwireContext;

class TopwireHash
{
    private const hashSeparator = '::hash::';
    public string $hashedString;
    public string $secureString;

    public function __construct(string $secureString)
    {
        $this->secureString = $secureString;
        $this->hashedString = $this->secureString
            . self::hashSeparator
            . self::calculateHmac($this->secureString);
    }

    public static function fromUntrustedString(string $untrustedString): self
    {
        if (strpos($untrustedString, self::hashSeparator) === false) {
            throw new InvalidTopwireContext('No hmac submitted', 1671485145);
        }
        [$securedString, $hash] = explode(self::hashSeparator, $untrustedString);
        if (!hash_equals(self::calculateHmac($securedString), $hash)) {
            throw new InvalidTopwireContext('Hmac mismatch', 1671485170);
        }
        return new self($securedString);
    }

    private static function calculateHmac(string $secureString): string
    {
        return hash_hmac(
            'sha1',
            $secureString,
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] . self::class
        );
    }
}