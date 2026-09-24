<?php

namespace App\Domain\Shared;

/**
 * Dilempar ketika sebuah aturan bisnis dilanggar.
 * Pesannya aman ditampilkan langsung ke pengguna.
 */
class BusinessRuleException extends \DomainException
{
}
