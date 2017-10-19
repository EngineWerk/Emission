<?php
namespace Enginewerk\ApplicationBundle\Service;

use Enginewerk\ApplicationBundle\DateTime\DateTimeReadInterface;

final class DateTimeReadService implements DateTimeReadInterface
{
    /**
     * @var \DateTimeZone
     */
    private $dateTimeZone;

    /**
     * @param \DateTimeZone $dateTimeZone
     */
    public function __construct(\DateTimeZone $dateTimeZone)
    {
        $this->dateTimeZone = $dateTimeZone;
    }

    /**
     * Returns DateTimeImmutable object with current time
     *
     * @return \DateTimeImmutable
     */
    public function getCurrentDateTimeImmutable()
    {
        return new \DateTimeImmutable('NOW', $this->dateTimeZone);
    }

    /**
     * Returns DateTime object with current time
     *
     * @return \DateTime
     */
    public function getCurrentDateTime()
    {
        return new \DateTime('NOW', $this->dateTimeZone);
    }
}
