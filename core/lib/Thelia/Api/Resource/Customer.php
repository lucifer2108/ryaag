<?php

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Thelia\Api\Resource;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Propel\Runtime\Collection\ArrayCollection;
use Propel\Runtime\Collection\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Thelia\Api\Bridge\Propel\Attribute\Column;
use Thelia\Api\Bridge\Propel\Attribute\Relation;
use Thelia\Api\Bridge\Propel\Filter\SearchFilter;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/admin/customers'
        ),
        new GetCollection(
            uriTemplate: '/admin/customers'
        ),
        new Get(
            uriTemplate: '/admin/customers/{id}',
            normalizationContext:  ['groups' => [self::GROUP_READ, self::GROUP_READ_SINGLE]]
        ),
        new Put(
            uriTemplate: '/admin/customers/{id}'
        ),
        new Delete(
            uriTemplate: '/admin/customers/{id}'
        )
    ],
    normalizationContext: ['groups' => [self::GROUP_READ, I18n::GROUP_READ]],
    denormalizationContext: ['groups' => [self::GROUP_WRITE, I18n::GROUP_WRITE]]
)]
#[ApiFilter(
    filterClass: SearchFilter::class,
    properties: [
        'ref',
        'firstname',
        'lastname'
    ]
)]
class Customer extends AbstractPropelResource
{
    public const GROUP_READ = 'customer:read';
    public const GROUP_READ_SINGLE = 'customer:read:single';
    public const GROUP_WRITE = 'customer:write';

    #[Groups([self::GROUP_READ, Address::GROUP_READ_SINGLE, Order::GROUP_READ])]
    public ?int $id = null;

    #[Relation(targetResource: CustomerTitle::class)]
    #[Groups([self::GROUP_READ_SINGLE, self::GROUP_WRITE, Address::GROUP_READ_SINGLE])]
    public CustomerTitle $customerTitle;

    #[Relation(targetResource: Lang::class)]
    #[Column(propelGetter: "getLangModel")]
    #[Groups([self::GROUP_READ_SINGLE,self::GROUP_WRITE])]
    public ?Lang $lang;

    #[Groups([self::GROUP_READ, Address::GROUP_READ_SINGLE, Order::GROUP_READ])]
    public ?string $ref;

    #[Groups([self::GROUP_READ, self::GROUP_WRITE, Address::GROUP_READ_SINGLE, Order::GROUP_READ,Order::GROUP_READ_SINGLE])]
    public string $firstname;

    #[Groups([self::GROUP_READ, self::GROUP_WRITE, Order::GROUP_READ,Order::GROUP_READ_SINGLE])]
    public string $lastname;

    #[Groups([self::GROUP_READ_SINGLE, self::GROUP_WRITE, Order::GROUP_READ,Order::GROUP_READ_SINGLE])]
    public ?string $email;

    public ?string $password;

    public ?string $algo;

    #[Groups([self::GROUP_READ_SINGLE, self::GROUP_WRITE])]
    public ?bool $reseller;

    public ?string $sponsor;

    #[Groups([self::GROUP_READ_SINGLE, self::GROUP_WRITE])]
    public ?float $discount;

    public ?string $rememberMeToken;

    public ?string $rememberMeSerial;

    public ?bool $enable;

    public ?string $confirmationToken;

    #[Groups([self::GROUP_READ])]
    public ?\DateTime $createdAt;

    #[Groups([self::GROUP_READ_SINGLE])]
    public ?\DateTime $updatedAt;

    public ?int $version;

    public ?\DateTime $versionCreatedAt;

    public ?string $versionCreatedBy;

    #[Relation(targetResource: Address::class)]
    #[Groups([self::GROUP_READ_SINGLE,self::GROUP_WRITE])]
    public Collection $addresses;

    public function __construct()
    {
        $this->addresses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getCustomerTitle(): CustomerTitle
    {
        return $this->customerTitle;
    }

    public function setCustomerTitle(CustomerTitle $customerTitle): void
    {
        $this->customerTitle = $customerTitle;
    }

    public function getLang(): ?Lang
    {
        return $this->lang;
    }

    public function setLang(?Lang $lang): void
    {
        $this->lang = $lang;
    }

    public function getRef(): ?string
    {
        return $this->ref;
    }

    public function setRef(?string $ref): void
    {
        $this->ref = $ref;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    public function getAlgo(): ?string
    {
        return $this->algo;
    }

    public function setAlgo(?string $algo): void
    {
        $this->algo = $algo;
    }

    public function getReseller(): ?bool
    {
        return $this->reseller;
    }

    public function setReseller(?bool $reseller): void
    {
        $this->reseller = $reseller;
    }

    public function getSponsor(): ?string
    {
        return $this->sponsor;
    }

    public function setSponsor(?string $sponsor): void
    {
        $this->sponsor = $sponsor;
    }

    public function getDiscount(): ?float
    {
        return $this->discount;
    }

    public function setDiscount(?float $discount): void
    {
        $this->discount = $discount;
    }

    public function getRememberMeToken(): ?string
    {
        return $this->rememberMeToken;
    }

    public function setRememberMeToken(?string $rememberMeToken): void
    {
        $this->rememberMeToken = $rememberMeToken;
    }

    public function getRememberMeSerial(): ?string
    {
        return $this->rememberMeSerial;
    }

    public function setRememberMeSerial(?string $rememberMeSerial): void
    {
        $this->rememberMeSerial = $rememberMeSerial;
    }

    public function getEnable(): ?bool
    {
        return $this->enable;
    }

    public function setEnable(?bool $enable): void
    {
        $this->enable = $enable;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $confirmationToken): void
    {
        $this->confirmationToken = $confirmationToken;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function setVersion(?int $version): void
    {
        $this->version = $version;
    }

    public function getVersionCreatedAt(): ?\DateTime
    {
        return $this->versionCreatedAt;
    }

    public function setVersionCreatedAt(?\DateTime $versionCreatedAt): void
    {
        $this->versionCreatedAt = $versionCreatedAt;
    }

    public function getVersionCreatedBy(): ?string
    {
        return $this->versionCreatedBy;
    }

    public function setVersionCreatedBy(?string $versionCreatedBy): void
    {
        $this->versionCreatedBy = $versionCreatedBy;
    }

    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    public function setAddresses(Collection $addresses): void
    {
        $this->addresses = $addresses;
    }

    public static function getPropelModelClass(): string
    {
        return \Thelia\Model\Customer::class;
    }
}
