<?php
namespace Enginewerk\ApplicationBundle\DateTime;

interface DateTimeReadInterface
{
    /**
     * Returns DateTime object with current time
     *
     * @return \DateTime
     */
    public function getCurrentDateTime();

    /**
     * Returns DateTimeImmutable object with current time
     *
     * @return \DateTimeImmutable
     */
    public function getCurrentDateTimeImmutable();
}
