<?php
namespace Enginewerk\Common\Uuid\Adapter;

use Ramsey\Uuid\Uuid;

class RamseyUuidGenerator implements UuidVersion4AdapterInterface
{
    /**
     * @inheritdoc
     */
    public function generateV4()
    {
        return Uuid::uuid4()->toString();
    }
}
