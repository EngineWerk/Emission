<?php
namespace Enginewerk\UserBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManager;
use Enginewerk\UserBundle\Entity\Invitation;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * Transforms an Invitation to an invitation code.
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class InvitationToCodeTransformer implements DataTransformerInterface
{
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function transform($value)
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof Invitation) {
            throw new UnexpectedTypeException($value, Invitation::class);
        }

        return $value->getCode();
    }

    public function reverseTransform($value)
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        return $this->entityManager
            ->getRepository(Invitation::class)
            ->findOneBy([
                'code' => $value,
                'user' => null,
            ]);
    }
}
