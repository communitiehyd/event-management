<?php

namespace HiEvents\DomainObjects\Generated;

/**
 * THIS FILE IS AUTOGENERATED - DO NOT EDIT IT DIRECTLY.
 * @package HiEvents\DomainObjects\Generated
 */
abstract class StripeCustomerDomainObjectAbstract extends \HiEvents\DomainObjects\AbstractDomainObject
{
    final public const SINGULAR_NAME = 'stripe_customer';
    final public const PLURAL_NAME = 'stripe_customers';
    final public const ID = 'id';
    final public const NAME = 'name';
    final public const EMAIL = 'email';
    final public const STRIPE_CUSTOMER_ID = 'stripe_customer_id';
    final public const CREATED_AT = 'created_at';
    final public const UPDATED_AT = 'updated_at';
    final public const DELETED_AT = 'deleted_at';
    final public const STRIPE_ACCOUNT_ID = 'stripe_account_id';

    protected int $id;
    protected string $name;
    protected string $email;
    protected string $stripe_customer_id;
    protected ?string $created_at = null;
    protected ?string $updated_at = null;
    protected ?string $deleted_at = null;
    protected ?string $stripe_account_id = null;

    public function toArray(): array
    {
        return [
                    'id' => $this->id ?? null,
                    'name' => $this->name ?? null,
                    'email' => $this->email ?? null,
                    'stripe_customer_id' => $this->stripe_customer_id ?? null,
                    'created_at' => $this->created_at ?? null,
                    'updated_at' => $this->updated_at ?? null,
                    'deleted_at' => $this->deleted_at ?? null,
                    'stripe_account_id' => $this->stripe_account_id ?? null,
                ];
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setStripeCustomerId(string $stripe_customer_id): self
    {
        $this->stripe_customer_id = $stripe_customer_id;
        return $this;
    }

    public function getStripeCustomerId(): string
    {
        return $this->stripe_customer_id;
    }

    public function setCreatedAt(?string $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function setUpdatedAt(?string $updated_at): self
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updated_at;
    }

    public function setDeletedAt(?string $deleted_at): self
    {
        $this->deleted_at = $deleted_at;
        return $this;
    }

    public function getDeletedAt(): ?string
    {
        return $this->deleted_at;
    }

    public function setStripeAccountId(?string $stripe_account_id): self
    {
        $this->stripe_account_id = $stripe_account_id;
        return $this;
    }

    public function getStripeAccountId(): ?string
    {
        return $this->stripe_account_id;
    }
}
