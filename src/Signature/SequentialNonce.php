<?php

namespace mascotgaming\mascot\api\client\Signature;

use phpseclib3\Math\BigInteger;

/**
 * Class SequentialNonce
 *
 * Generates monotonically increasing safe 64-bit (and larger) integer arithmetic on all PHP versions.
 *
 * Nonce is initialized with a starting value and incremented by 1
 * every time {@see SequentialNonce::next()} is called.
 *
 * This generator is suitable for request signing, replay protection,
 * or any place where strictly increasing numeric nonces are required.
 */
class SequentialNonce implements Nonce
{
    private $nonce;

    /**
     * SequentialNonce constructor.
     *
     * @param int|string $start Starting value for the nonce.
     *                          Accepts integers or numeric strings.
     */
    public function __construct($start)
    {
        if (!is_numeric($start)) {
            throw new \InvalidArgumentException("Nonce start value must be numeric.");
        }
        $this->nonce = new BigInteger((string)$start, 10);
    }

    /**
     * Returns the next sequential nonce value.
     *
     * @return string Numeric string representation of the incremented nonce.
     *                A string is returned to avoid integer overflow on 32-bit PHP.
     */
    public function next()
    {
        $this->nonce = $this->nonce->add(new BigInteger('1', 10));
        return $this->nonce->toString();
    }
}
