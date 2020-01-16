<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

final class Contact
{
    /**
     * Sender's name.
     *
     * @var string
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * Sender's e-mail address.
     *
     * @var string
     * @Assert\Email()
     */
    private $email;

    /**
     * Sender's message.
     *
     * @var string
     * @Assert\NotBlank()
     */
    private $message;

    /**
     * @param string $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getFrom(): string
    {
        return sprintf('%s <%s>', $this->getName(), $this->getEmail());
    }
}
