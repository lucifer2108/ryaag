<?php

namespace Thelia\Api\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Symfony\Component\Serializer\Annotation\Groups;
use Thelia\Api\Bridge\Propel\Attribute\Relation;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/admin/order_coupons'
        ),
        new Get(
            uriTemplate: '/admin/order_coupons/{id}',
            normalizationContext: ['groups' => [self::GROUP_READ, self::GROUP_READ_SINGLE]]
        ),
        new Put(
            uriTemplate: '/admin/order_coupons/{id}'
        ),
        new Delete(
            uriTemplate: '/admin/order_coupons/{id}'
        ),
    ],
    normalizationContext: ['groups' => [self::GROUP_READ]],
    denormalizationContext: ['groups' => [self::GROUP_WRITE]]
)]
class OrderCoupon extends AbstractPropelResource
{
    public const GROUP_READ = 'order_coupon:read';
    public const GROUP_READ_SINGLE = 'order_coupon:read:single';
    public const GROUP_WRITE = 'order_coupon:write';

    #[Groups([self::GROUP_READ, Order::GROUP_READ])]
    public ?int $id = null;

    #[Relation(targetResource: Order::class)]
    #[Groups([self::GROUP_READ])]
    public ?Order $order;

    #[Groups([self::GROUP_READ, Order::GROUP_READ])]
    public ?string $code;

    #[Groups([self::GROUP_READ, Order::GROUP_READ])]
    public ?string $type;

    #[Groups([self::GROUP_READ, Order::GROUP_READ])]
    public ?float $amount;

    #[Groups([self::GROUP_READ, Order::GROUP_READ])]
    public ?string $title;

    #[Groups([self::GROUP_READ])]
    public ?string $shortDescription;

    #[Groups([self::GROUP_READ])]
    public ?string $description;

    #[Groups([self::GROUP_READ, Order::GROUP_READ])]
    public ?\DateTime $startDate;

    #[Groups([self::GROUP_READ, Order::GROUP_READ])]
    public ?\DateTime $expirationDate;

    #[Groups([self::GROUP_READ])]
    public ?bool $isCumulative;

    #[Groups([self::GROUP_READ])]
    public ?bool $isRemovingPostage;

    #[Groups([self::GROUP_READ])]
    public ?bool $isAvailableOnSpecialOffers;

    #[Groups([self::GROUP_READ])]
    public ?string $serializedConditions;

    #[Groups([self::GROUP_READ])]
    public ?bool $perCustomerUsageCount;

    #[Groups([self::GROUP_READ])]
    public ?bool $usageCanceled;

    #[Groups([self::GROUP_READ])]
    public ?\DateTime $createdAt;

    #[Groups([self::GROUP_READ])]
    public ?\DateTime $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): OrderCoupon
    {
        $this->id = $id;
        return $this;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): OrderCoupon
    {
        $this->order = $order;
        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): OrderCoupon
    {
        $this->code = $code;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): OrderCoupon
    {
        $this->type = $type;
        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): OrderCoupon
    {
        $this->amount = $amount;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): OrderCoupon
    {
        $this->title = $title;
        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(?string $shortDescription): OrderCoupon
    {
        $this->shortDescription = $shortDescription;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): OrderCoupon
    {
        $this->description = $description;
        return $this;
    }

    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTime $startDate): OrderCoupon
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getExpirationDate(): ?\DateTime
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(?\DateTime $expirationDate): OrderCoupon
    {
        $this->expirationDate = $expirationDate;
        return $this;
    }

    public function getIsCumulative(): ?bool
    {
        return $this->isCumulative;
    }

    public function setIsCumulative(?bool $isCumulative): OrderCoupon
    {
        $this->isCumulative = $isCumulative;
        return $this;
    }

    public function getIsRemovingPostage(): ?bool
    {
        return $this->isRemovingPostage;
    }

    public function setIsRemovingPostage(?bool $isRemovingPostage): OrderCoupon
    {
        $this->isRemovingPostage = $isRemovingPostage;
        return $this;
    }

    public function getIsAvailableOnSpecialOffers(): ?bool
    {
        return $this->isAvailableOnSpecialOffers;
    }

    public function setIsAvailableOnSpecialOffers(?bool $isAvailableOnSpecialOffers): OrderCoupon
    {
        $this->isAvailableOnSpecialOffers = $isAvailableOnSpecialOffers;
        return $this;
    }

    public function getSerializedConditions(): ?string
    {
        return $this->serializedConditions;
    }

    public function setSerializedConditions(?string $serializedConditions): OrderCoupon
    {
        $this->serializedConditions = $serializedConditions;
        return $this;
    }

    public function getPerCustomerUsageCount(): ?bool
    {
        return $this->perCustomerUsageCount;
    }

    public function setPerCustomerUsageCount(?bool $perCustomerUsageCount): OrderCoupon
    {
        $this->perCustomerUsageCount = $perCustomerUsageCount;
        return $this;
    }

    public function getUsageCanceled(): ?bool
    {
        return $this->usageCanceled;
    }

    public function setUsageCanceled(?bool $usageCanceled): OrderCoupon
    {
        $this->usageCanceled = $usageCanceled;
        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTime $createdAt): OrderCoupon
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): OrderCoupon
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public static function getPropelModelClass(): string
    {
        return \Thelia\Model\OrderCoupon::class;
    }
}
