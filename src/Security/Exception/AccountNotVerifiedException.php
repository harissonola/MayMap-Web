<?php
// src/Security/Exception/AccountNotVerifiedException.php
namespace App\Security\Exception;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class AccountNotVerifiedException extends CustomUserMessageAuthenticationException
{
    private ?string $redirectUrl;

    public function __construct(
        string $message = '',
        array $messageData = [],
        ?string $redirectUrl = null
    ) {
        parent::__construct($message, $messageData);
        $this->redirectUrl = $redirectUrl;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }
}